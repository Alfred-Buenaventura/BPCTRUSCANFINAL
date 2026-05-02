using libzkfpcsharp;
using Microsoft.Data.Sqlite;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Text;
using System.Threading;
using System.Threading.Tasks;
using WebSocketSharp;
using WebSocketSharp.Server;

public class SyncQueue
{
    private static string dbPath = "Data Source=offline_attendance.db";

    public static void Initialize()
    {
        try
        {
            using (var conn = new SqliteConnection(dbPath))
            {
                conn.Open();
                var cmd = conn.CreateCommand();
                cmd.CommandText = @"
                CREATE TABLE IF NOT EXISTS queue (
                    id INTEGER PRIMARY KEY AUTOINCREMENT, 
                    user_id INTEGER, 
                    timestamp TEXT
                )";
                cmd.ExecuteNonQuery();
                Console.WriteLine("[DB] Offline storage verified.");
            }
        }
        catch (Exception ex)
        {
            Console.WriteLine($"[DB] Error: {ex.Message}");
        }
    }

    public static void SaveOffline(int userId)
    {
        try
        {
            using (var conn = new SqliteConnection(dbPath))
            {
                conn.Open();
                var cmd = conn.CreateCommand();
                cmd.CommandText = "INSERT INTO queue (user_id, timestamp) VALUES ($uid, $ts)";
                cmd.Parameters.AddWithValue("$uid", userId);
                cmd.Parameters.AddWithValue("$ts", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss"));
                cmd.ExecuteNonQuery();
                Console.WriteLine($"[OFFLINE] Record saved for ID: {userId}");
            }
        }
        catch (Exception ex)
        {
            Console.WriteLine($"[OFFLINE] Database Write Error: {ex.Message}");
        }
    }

    public static async Task ProcessQueue(string apiUrl)
    {
        using (var client = new HttpClient())
        using (var conn = new SqliteConnection(dbPath))
        {
            try
            {
                conn.Open();
                var selectCmd = conn.CreateCommand();
                selectCmd.CommandText = "SELECT id, user_id, timestamp FROM queue";

                using (var reader = selectCmd.ExecuteReader())
                {
                    while (reader.Read())
                    {
                        int rowId = reader.GetInt32(0);
                        var payload = new { user_id = reader.GetInt32(1), timestamp = reader.GetString(2) };

                        try
                        {
                            var content = new StringContent(JsonConvert.SerializeObject(payload), Encoding.UTF8, "application/json");
                            var response = await client.PostAsync(apiUrl, content);

                            if (response.IsSuccessStatusCode)
                            {
                                var delCmd = conn.CreateCommand();
                                delCmd.CommandText = "DELETE FROM queue WHERE id = " + rowId;
                                delCmd.ExecuteNonQuery();
                                Console.WriteLine($"[SYNC] Uploaded record for ID: {payload.user_id}");
                            }
                        }
                        catch { break; }
                    }
                }
            }
            catch { /* Background fail */ }
        }
    }
}

public class FingerprintService : WebSocketBehavior
{
    private static IntPtr zkTecoDeviceHandle = IntPtr.Zero;
    private static IntPtr mDBHandle = IntPtr.Zero;
    private static bool sdkInitialized = false;
    private static byte[] imgBuffer = null;
    private static int imageWidth = 0;
    private static int imageHeight = 0;
    private static bool lastHardwareStatus = false;

    public static volatile bool isEnrolling = false;
    private static void TryOpenScanner()
    {
        if (zkTecoDeviceHandle != IntPtr.Zero) zkfp2.CloseDevice(zkTecoDeviceHandle);
        zkTecoDeviceHandle = zkfp2.OpenDevice(0);
        lastHardwareStatus = (zkTecoDeviceHandle != IntPtr.Zero);
        Console.WriteLine(lastHardwareStatus ? "[SDK] Scanner Found" : "[SDK] Scanner Not Found");
    }

    static FingerprintService()
    {
        Console.WriteLine("[SDK] Initializing ZKFinger SDK...");
        int ret = zkfp2.Init();
        if (ret == 0)
        {
            sdkInitialized = true;
            mDBHandle = zkfp2.DBInit();
            zkTecoDeviceHandle = zkfp2.OpenDevice(0);

            if (zkTecoDeviceHandle == IntPtr.Zero)
            {
                Console.WriteLine("[SDK] Error: Scanner not found.");
                return;
            }

            byte[] paramValue = new byte[4]; int size = 4;
            zkfp2.GetParameters(zkTecoDeviceHandle, 1, paramValue, ref size);
            zkfp2.ByteArray2Int(paramValue, ref imageWidth);
            zkfp2.GetParameters(zkTecoDeviceHandle, 2, paramValue, ref size);
            zkfp2.ByteArray2Int(paramValue, ref imageHeight);
            imgBuffer = new byte[imageWidth * imageHeight];

            Console.WriteLine($"[SDK] Scanner Ready ({imageWidth}x{imageHeight})");
            new Thread(LoadTemplatesFromWebServer).Start();
        }
        else
        {
            Console.WriteLine($"[SDK] Init Failed: {ret}");
        }
    }

    private static void LoadTemplatesFromWebServer()
    {
        string url = "https://bpctruscan.com/api/get_all_templates.php";
        Console.WriteLine("[SYNC] Downloading templates from server...");

        using (HttpClient client = new HttpClient())
        {
            try
            {
                var response = client.GetAsync(url).Result;
                if (response.IsSuccessStatusCode)
                {
                    string json = response.Content.ReadAsStringAsync().Result;
                    dynamic result = JsonConvert.DeserializeObject(json);

                    if (result.success == true)
                    {
                        int count = 0;
                        foreach (var item in result.data)
                        {
                            try
                            {
                                byte[] blob = Convert.FromBase64String((string)item.fingerprint_template);
                                zkfp2.DBAdd(mDBHandle, (int)item.id, blob);
                                count++;
                            }
                            catch { }
                        }
                        Console.WriteLine($"[SYNC] Success: {count} fingerprints cached.");
                    }
                }
            }
            catch (Exception ex) { Console.WriteLine($"[SYNC] Server unreachable: {ex.Message}"); }
        }
    }

    public static void ShutdownSDK()
    {
        if (zkTecoDeviceHandle != IntPtr.Zero) zkfp2.CloseDevice(zkTecoDeviceHandle);
        if (mDBHandle != IntPtr.Zero) zkfp2.DBFree(mDBHandle);
        if (sdkInitialized) zkfp2.Terminate();
        Console.WriteLine("[SDK] Resources released.");
    }

    protected override void OnMessage(MessageEventArgs e)
    {
        try
        {
            // 1. Deserialize the incoming command from the web frontend
            dynamic msg = JsonConvert.DeserializeObject(e.Data);
            if (msg == null || msg.command == null) return;

            string command = msg.command;

            // 2. Command: check_hardware
            // Triggered by onopen in display_view.php and registration_view.php
            if (command == "check_hardware")
            {
                // Force a hardware refresh to see if the USB was recently plugged in
                TryOpenScanner();

                bool physicallyConnected = (zkTecoDeviceHandle != IntPtr.Zero);

                // Send physical status back to the frontend
                var response = new
                {
                    type = "hardware_status",
                    connected = physicallyConnected
                };

                Send(JsonConvert.SerializeObject(response));
                Console.WriteLine($"[Hardware] Status check requested. Result: {(physicallyConnected ? "CONNECTED" : "DISCONNECTED")}");
            }

            // 3. Command: enroll_start
            // Triggered by the "Register" button in registration_view.php
            else if (command == "enroll_start")
            {
                if (isEnrolling)
                {
                    Console.WriteLine("[Enroll] Blocked: Enrollment already in progress.");
                    return;
                }

                // Start the 3-scan enrollment process in a separate thread
                new Thread(() => StartEnrollmentProcess(this)).Start();
            }

            // 4. Command: sync_templates
            // Triggered to refresh the local cache from the bpctruscan.com database
            else if (command == "sync_templates")
            {
                Console.WriteLine("[Sync] Manual template sync initiated...");
                new Thread(LoadTemplatesFromWebServer).Start();
            }

            // 5. Command: verify_start
            // Used by display_view.php to confirm the identification loop is ready
            else if (command == "verify_start")
            {
                Console.WriteLine("[Identify] Verification loop signaled by web client.");
                Send(JsonConvert.SerializeObject(new
                {
                    status = "info",
                    message = "Verification active"
                }));
            }
        }
        catch (Exception ex)
        {
            Console.WriteLine($"[Error] OnMessage Failure: {ex.Message}");
            Send(JsonConvert.SerializeObject(new
            {
                status = "error",
                message = "Bridge failed to process command."
            }));
        }
    }

    private void StartEnrollmentProcess(FingerprintService socket)
    {
        isEnrolling = true;
        Console.WriteLine("[ENROLL] Registration Active - Attendance Paused.");
        List<byte[]> capturedTemplates = new List<byte[]>();

        try
        {
            for (int i = 1; i <= 3; i++)
            {
                socket.Send(JsonConvert.SerializeObject(new { status = "progress", step = i, message = $"Scan {i} of 3..." }));
                byte[] temp = CaptureFinger();

                if (temp == null)
                {
                    socket.Send(JsonConvert.SerializeObject(new { status = "error", message = "Enrollment timed out." }));
                    return;
                }

                if (capturedTemplates.Count > 0)
                {
                    int score = zkfp2.DBMatch(mDBHandle, temp, capturedTemplates[capturedTemplates.Count - 1]);
                    if (score < 50)
                    {
                        socket.Send(JsonConvert.SerializeObject(new { status = "error", message = "Mismatch." }));
                        return;
                    }
                }
                capturedTemplates.Add(temp);
                socket.Send(JsonConvert.SerializeObject(new { status = "progress", step = i, message = "Lift finger..." }));
                WaitForFingerLift();
                Thread.Sleep(500);
            }

            byte[] regTemp = new byte[2048];
            int cbRegTemp = 2048;
            int ret = zkfp2.DBMerge(mDBHandle, capturedTemplates[0], capturedTemplates[1], capturedTemplates[2], regTemp, ref cbRegTemp);

            if (ret == 0)
            {
                string b64 = Convert.ToBase64String(regTemp, 0, cbRegTemp);
                socket.Send(JsonConvert.SerializeObject(new { status = "success", template = b64 }));
                Console.WriteLine("[ENROLL] Registration template captured.");
            }
            else { socket.Send(JsonConvert.SerializeObject(new { status = "error", message = "Merge failed." })); }
        }
        catch (Exception ex) { socket.Send(JsonConvert.SerializeObject(new { status = "error", message = ex.Message })); }
        finally
        {
            isEnrolling = false;
            Console.WriteLine("[ENROLL] Registration ended - Attendance resumed.");
        }
    }

    private byte[] CaptureFinger()
    {
        byte[] temp = new byte[2048];
        int size = 2048;
        int attempts = 0;
        while (attempts < 100)
        {
            if (zkfp2.AcquireFingerprint(zkTecoDeviceHandle, imgBuffer, temp, ref size) == 0)
            {
                byte[] result = new byte[size];
                Array.Copy(temp, result, size);
                return result;
            }
            Thread.Sleep(200);
            attempts++;
        }
        return null;
    }

    private void WaitForFingerLift()
    {
        byte[] temp = new byte[2048];
        int size = 2048;
        while (zkfp2.AcquireFingerprint(zkTecoDeviceHandle, imgBuffer, temp, ref size) == 0) { Thread.Sleep(100); }
    }

    public static void StartIdentificationLoop(WebSocketServer wssv)
    {
        string apiURL = "https://bpctruscan.com/api/record_attendance.php";

        var syncTimer = new System.Timers.Timer(10000);
        syncTimer.Elapsed += async (s, e) => await SyncQueue.ProcessQueue(apiURL);
        syncTimer.AutoReset = true;
        syncTimer.Enabled = true;
        syncTimer.Start();
        Console.WriteLine("[SYNC] Offline sync engine started (10s interval).");

        byte[] temp = new byte[2048];
        int size = 2048;
        int score = 0, ret = 0, tid = 0;

        while (true)
        {
            // 1. Physical Connectivity Check
            if (zkTecoDeviceHandle == IntPtr.Zero)
            {
                if (lastHardwareStatus == true)
                {
                    Console.WriteLine("[Hardware] SCANNER DISCONNECTED!");
                    wssv.WebSocketServices["/"].Sessions.Broadcast(JsonConvert.SerializeObject(new
                    {
                        type = "hardware_status",
                        connected = false
                    }));
                    lastHardwareStatus = false;
                }

                zkTecoDeviceHandle = zkfp2.OpenDevice(0);
                Thread.Sleep(5000);
                continue;
            }

            // 2. Physical Reconnection Check
            // We use 'FingerprintService.lastHardwareStatus' to be explicitly clear for the compiler
            if (FingerprintService.lastHardwareStatus == false)
            {
                Console.WriteLine("[Hardware] SCANNER RECONNECTED!");
                wssv.WebSocketServices["/"].Sessions.Broadcast(JsonConvert.SerializeObject(new
                {
                    type = "hardware_status",
                    connected = true
                }));
                FingerprintService.lastHardwareStatus = true;
            }

            if (isEnrolling || !sdkInitialized) { Thread.Sleep(1000); continue; }

            size = 2048;
            ret = zkfp2.AcquireFingerprint(zkTecoDeviceHandle, imgBuffer, temp, ref size);

            if (ret == -1)
            {
                zkTecoDeviceHandle = IntPtr.Zero;
                continue;
            }

            if (ret == 0)
            {
                ret = zkfp2.DBIdentify(mDBHandle, temp, ref tid, ref score);
                if (ret == 0)
                {
                    Console.WriteLine($"[SCAN] Identified: User ID {tid}");

                    // We wrap the API call in Task.Run and pass apiURL to it.
                    // This fixes the "Variable not used" warning.
                    string targetUrl = apiURL;
                    Task.Run(async () => {
                        try
                        {
                            using (var client = new HttpClient())
                            {
                                var payload = JsonConvert.SerializeObject(new { user_id = tid });
                                var content = new StringContent(payload, Encoding.UTF8, "application/json");
                                var response = await client.PostAsync(targetUrl, content);
                                if (!response.IsSuccessStatusCode) SyncQueue.SaveOffline(tid);
                            }
                        }
                        catch { SyncQueue.SaveOffline(tid); }
                    });

                    wssv.WebSocketServices["/"].Sessions.Broadcast(JsonConvert.SerializeObject(new { type = "verification_success", user_id = tid }));
                    while (zkfp2.AcquireFingerprint(zkTecoDeviceHandle, imgBuffer, temp, ref size) == 0) { Thread.Sleep(100); }
                }
            }
            Thread.Sleep(100);
        }
    }
}



    public class Program
{
    public static void Main(string[] args)
    {
        // Fix for Tls13 / Tls12 compatibility on older .NET Frameworks
        System.Net.ServicePointManager.SecurityProtocol = System.Net.SecurityProtocolType.Tls12 | (System.Net.SecurityProtocolType)12288;

        SyncQueue.Initialize();

        var wssv = new WebSocketServer("ws://127.0.0.1:8080");
        wssv.AddWebSocketService<FingerprintService>("/");
        wssv.Start();

        Console.Clear();
        Console.WriteLine("=================================================");
        Console.WriteLine("           BPC TRUSCAN - BRIDGE SERVICE          ");
        Console.WriteLine("=================================================");
        Console.WriteLine($"[INFO] WebSocket: ws://127.0.0.1:8080");
        Console.WriteLine($"[INFO] Target: https://bpctruscan.com");
        Console.WriteLine("-------------------------------------------------");

        Thread attendanceThread = new Thread(() => FingerprintService.StartIdentificationLoop(wssv));
        attendanceThread.IsBackground = true;
        attendanceThread.Start();

        Console.WriteLine("[SYS] Service is running. Press Enter to stop.");
        Console.WriteLine("-------------------------------------------------");
        Console.ReadLine();

        wssv.Stop();
        FingerprintService.ShutdownSDK();
    }
}