<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../../vendor/PHPMailer.php';
require_once __DIR__ . '/../../vendor/SMTP.php';
require_once __DIR__ . '/../../vendor/Exception.php';

class Mailer {

    private static function setup() {
        $mail = new PHPMailer(true);
        $username = 'bpcattendancemonitoringsystem@gmail.com';
        $password = 'ighozdnuljqpgzip';
        $fromName = 'BPC Attendance Monitoring System';

        if (empty($username) || empty($password)) return null;

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
        $mail->setFrom($username, $fromName);
        return $mail;
    }
    
    public static function send($to, $subject, $message) {
        $mail = new PHPMailer(true);

        // get the login details from the env variables
        $username = getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? null);
        $password = getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? null);
        $fromName = getenv('SMTP_FROM_NAME') ?: 'BPC Attendance System';

        // google app passwords have spaces that need to be cleared out to work
        if ($password) {
            $password = str_replace(' ', '', $password);
        }

        if (empty($username) || empty($password)) {
            self::logError("CRITICAL: SMTP credentials missing in .env file.");
            return false;
        }

        try {
            // set up the smtp settings so the mail actually sends
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // turn off ssl checks for local xampp so it doesn't error out
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom($username, $fromName);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            self::logError("SUCCESS: Email sent to $to.");
            return true;

        } catch (Exception $e) {
            self::logError("MAILER ERROR: " . $mail->ErrorInfo . " | To: $to");
            return false;
        }
    }

    // adding the cid as a 5th parameter here
    public static function sendWithQR($to, $subject, $body, $imagePath, $cid = 'qr_code') {
        $mail = self::setup();
        if (!$mail) {
            self::logError("CRITICAL: Mailer setup failed. Check credentials.");
            return false;
        }
        try {
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            if (!file_exists($imagePath)) {
                self::logError("ATTACHMENT ERROR: File not found at $imagePath");
                return false;
            }

            // use the cid variable that we got from the controller
            $mail->addEmbeddedImage($imagePath, $cid);
            $mail->Body = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            self::logError("MAILER ERROR: " . $mail->ErrorInfo);
            return false;
        }
    }

    private static function logError($msg) {
        // save the mail results to a local log file for debugging
        $logFile = __DIR__ . '/../../email_debug.log';
        $entry = date('Y-m-d H:i:s') . " - " . $msg . "\n";
        file_put_contents($logFile, $entry, FILE_APPEND);
    }
}