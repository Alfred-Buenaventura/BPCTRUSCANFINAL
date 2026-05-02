-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: db5019021856.hosting-data.io
-- Generation Time: May 02, 2026 at 09:13 AM
-- Server version: 10.11.14-MariaDB-log
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbs14972169`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(676, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 13:52:20'),
(677, 75, 'Password Changed', 'User changed password', '::1', '2026-02-22 13:53:02'),
(678, 75, 'User Archived', 'Archived user ID: 44', '::1', '2026-02-22 13:53:18'),
(679, 75, 'User Deleted', 'Permanently deleted user ID: 44', '::1', '2026-02-22 13:53:23'),
(680, 75, 'User Archived', 'Archived user ID: 81', '::1', '2026-02-22 15:07:48'),
(681, 75, 'User Updated', 'Updated user ID: 75', '::1', '2026-02-22 15:09:12'),
(682, 75, 'User Updated', 'Updated user ID: 75', '::1', '2026-02-22 15:09:22'),
(683, 75, 'User Updated', 'Updated user ID: 75', '::1', '2026-02-22 15:12:27'),
(684, 75, 'User Updated', 'Updated user ID: 75', '::1', '2026-02-22 15:12:45'),
(685, 75, 'User Archived', 'Archived user ID: 75', '::1', '2026-02-22 15:12:55'),
(686, 75, 'User Restored', 'Restored user ID: 75', '::1', '2026-02-22 15:18:30'),
(687, 75, 'User Restored', 'Restored user ID: 81', '::1', '2026-02-22 15:18:34'),
(688, 75, 'User Updated', 'Updated user ID: 75', '::1', '2026-02-22 15:18:41'),
(689, 75, 'Logout', 'User logged out', '::1', '2026-02-22 16:04:22'),
(693, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 16:08:43'),
(694, 75, 'Logout', 'User logged out', '::1', '2026-02-22 16:09:33'),
(697, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 16:10:30'),
(698, 75, 'User Archived', 'Archived user ID: 83', '::1', '2026-02-22 16:10:35'),
(699, 75, 'User Deleted', 'Permanently deleted user ID: 83', '::1', '2026-02-22 16:10:40'),
(700, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:11:03'),
(701, 75, 'Logout', 'User logged out', '::1', '2026-02-22 16:11:20'),
(705, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 16:13:25'),
(706, 75, 'User Archived', 'Archived user ID: 84', '::1', '2026-02-22 16:23:20'),
(707, 75, 'User Deleted', 'Permanently deleted user ID: 84', '::1', '2026-02-22 16:23:25'),
(708, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:23:37'),
(709, 75, 'User Archived', 'Archived user ID: 85', '::1', '2026-02-22 16:24:22'),
(710, 75, 'User Deleted', 'Permanently deleted user ID: 85', '::1', '2026-02-22 16:24:27'),
(711, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:24:45'),
(712, 75, 'User Archived', 'Archived user ID: 86', '::1', '2026-02-22 16:28:22'),
(713, 75, 'User Deleted', 'Permanently deleted user ID: 86', '::1', '2026-02-22 16:28:26'),
(714, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:35:52'),
(715, 75, 'User Archived', 'Archived user ID: 87', '::1', '2026-02-22 16:38:42'),
(716, 75, 'User Deleted', 'Permanently deleted user ID: 87', '::1', '2026-02-22 16:38:46'),
(717, 75, 'User Archived', 'Archived user ID: 88', '::1', '2026-02-22 16:39:54'),
(718, 75, 'User Deleted', 'Permanently deleted user ID: 88', '::1', '2026-02-22 16:39:58'),
(719, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:40:38'),
(720, 75, 'User Archived', 'Archived user ID: 89', '::1', '2026-02-22 16:42:01'),
(721, 75, 'User Deleted', 'Permanently deleted user ID: 89', '::1', '2026-02-22 16:42:05'),
(722, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:42:21'),
(723, 75, 'User Archived', 'Archived user ID: 90', '::1', '2026-02-22 16:45:00'),
(724, 75, 'User Deleted', 'Permanently deleted user ID: 90', '::1', '2026-02-22 16:45:04'),
(725, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:45:13'),
(726, 75, 'User Archived', 'Archived user ID: 91', '::1', '2026-02-22 16:50:43'),
(727, 75, 'User Deleted', 'Permanently deleted user ID: 91', '::1', '2026-02-22 16:50:48'),
(728, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:51:13'),
(729, 75, 'User Archived', 'Archived user ID: 92', '::1', '2026-02-22 16:51:40'),
(730, 75, 'User Deleted', 'Permanently deleted user ID: 92', '::1', '2026-02-22 16:51:44'),
(731, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:51:57'),
(732, 75, 'User Archived', 'Archived user ID: 93', '::1', '2026-02-22 16:54:39'),
(733, 75, 'User Deleted', 'Permanently deleted user ID: 93', '::1', '2026-02-22 16:54:43'),
(734, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:54:56'),
(735, 75, 'User Archived', 'Archived user ID: 94', '::1', '2026-02-22 16:58:00'),
(736, 75, 'User Deleted', 'Permanently deleted user ID: 94', '::1', '2026-02-22 16:58:04'),
(737, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:58:16'),
(738, 75, 'User Archived', 'Archived user ID: 95', '::1', '2026-02-22 16:58:51'),
(739, 75, 'User Deleted', 'Permanently deleted user ID: 95', '::1', '2026-02-22 16:59:00'),
(740, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 16:59:10'),
(741, 75, 'User Archived', 'Archived user ID: 96', '::1', '2026-02-22 16:59:51'),
(742, 75, 'User Deleted', 'Permanently deleted user ID: 96', '::1', '2026-02-22 17:00:03'),
(743, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 17:02:59'),
(744, 75, 'User Archived', 'Archived user ID: 97', '::1', '2026-02-22 17:03:38'),
(745, 75, 'User Deleted', 'Permanently deleted user ID: 97', '::1', '2026-02-22 17:03:42'),
(746, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 17:09:05'),
(747, 75, 'User Created', 'Account created for 22-1139-28', '::1', '2026-02-22 17:19:22'),
(748, 75, 'User Archived', 'Archived user ID: 98', '::1', '2026-02-22 17:26:27'),
(749, 75, 'User Deleted', 'Permanently deleted user ID: 98', '::1', '2026-02-22 17:26:32'),
(750, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 17:26:58'),
(751, 75, 'User Archived', 'Archived user ID: 99', '::1', '2026-02-22 17:28:08'),
(752, 75, 'User Deleted', 'Permanently deleted user ID: 99', '::1', '2026-02-22 17:28:13'),
(753, 75, 'User Created', 'Account created for 22-1139-28', '::1', '2026-02-22 17:28:29'),
(754, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 17:29:09'),
(755, 75, 'Logout', 'User logged out', '::1', '2026-02-22 17:30:00'),
(759, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 17:33:29'),
(760, 75, 'Logout', 'User logged out', '::1', '2026-02-22 17:33:45'),
(764, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 17:34:35'),
(765, 75, 'Logout', 'User logged out', '::1', '2026-02-22 17:38:46'),
(769, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 18:01:10'),
(770, 75, 'User Archived', 'Archived user ID: 100', '::1', '2026-02-22 18:01:19'),
(771, 75, 'User Deleted', 'Permanently deleted user ID: 100', '::1', '2026-02-22 18:01:24'),
(772, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 18:01:48'),
(773, 75, 'User Archived', 'Archived user ID: 102', '::1', '2026-02-22 18:03:20'),
(774, 75, 'User Deleted', 'Permanently deleted user ID: 102', '::1', '2026-02-22 18:03:24'),
(775, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 18:03:42'),
(776, 75, 'User Archived', 'Archived user ID: 103', '::1', '2026-02-22 18:05:53'),
(777, 75, 'User Deleted', 'Permanently deleted user ID: 103', '::1', '2026-02-22 18:05:57'),
(778, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 18:06:07'),
(779, 75, 'Logout', 'User logged out', '::1', '2026-02-22 18:10:02'),
(783, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 18:11:06'),
(784, 75, 'User Archived', 'Archived user ID: 104', '::1', '2026-02-22 18:14:59'),
(785, 75, 'User Deleted', 'Permanently deleted user ID: 104', '::1', '2026-02-22 18:15:03'),
(786, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 18:15:46'),
(787, 75, 'User Archived', 'Archived user ID: 105', '::1', '2026-02-22 18:33:16'),
(788, 75, 'User Deleted', 'Permanently deleted user ID: 105', '::1', '2026-02-22 18:33:23'),
(789, 75, 'User Created', 'Account created for 22-0139-28', '::1', '2026-02-22 18:33:34'),
(790, 75, 'User Archived', 'Archived user ID: 107', '::1', '2026-02-22 18:37:34'),
(791, 75, 'User Deleted', 'Permanently deleted user ID: 107', '::1', '2026-02-22 18:37:39'),
(792, 75, 'User Archived', 'Archived user ID: 106', '::1', '2026-02-22 18:44:19'),
(793, 75, 'User Deleted', 'Permanently deleted user ID: 106', '::1', '2026-02-22 18:44:24'),
(794, 75, 'User Archived', 'Archived user ID: 108', '::1', '2026-02-22 18:53:20'),
(795, 75, 'User Deleted', 'Permanently deleted user ID: 108', '::1', '2026-02-22 18:53:24'),
(796, 75, 'User Archived', 'Archived user ID: 109', '::1', '2026-02-22 18:56:40'),
(797, 75, 'User Deleted', 'Permanently deleted user ID: 109', '::1', '2026-02-22 18:56:44'),
(798, 75, 'User Archived', 'Archived user ID: 109', '::1', '2026-02-22 18:57:35'),
(799, 75, 'User Archived', 'Archived user ID: 110', '::1', '2026-02-22 18:57:41'),
(800, 75, 'User Deleted', 'Permanently deleted user ID: 110', '::1', '2026-02-22 18:57:46'),
(801, 75, 'Login', 'User logged in successfully', '::1', '2026-02-22 19:04:03'),
(802, 75, 'User Archived', 'Archived user ID: 111', '::1', '2026-02-22 19:04:09'),
(803, 75, 'User Deleted', 'Permanently deleted user ID: 111', '::1', '2026-02-22 19:04:13'),
(804, 75, 'User Archived', 'Archived user ID: 112', '::1', '2026-02-22 19:05:47'),
(805, 75, 'User Deleted', 'Permanently deleted user ID: 112', '::1', '2026-02-22 19:05:51'),
(806, 75, 'User Archived', 'Archived user ID: 113', '::1', '2026-02-22 19:07:05'),
(807, 75, 'User Deleted', 'Permanently deleted user ID: 113', '::1', '2026-02-22 19:07:09'),
(808, 75, 'Login', 'User logged in successfully', '120.29.87.218', '2026-02-22 20:00:47'),
(809, 75, 'User Archived', 'Archived user ID: 114', '120.29.87.218', '2026-02-22 20:01:04'),
(810, 75, 'User Restored', 'Restored user ID: 114', '120.29.87.218', '2026-02-22 20:05:11'),
(811, 75, 'Login', 'User logged in successfully', '120.29.68.55', '2026-02-22 23:37:34'),
(812, 75, 'Login', 'User logged in successfully', '120.29.68.55', '2026-02-22 23:38:12'),
(813, 75, 'Login', 'User logged in successfully', '120.29.68.55', '2026-02-22 23:39:08'),
(814, 75, 'Login', 'User logged in successfully', '120.29.87.218', '2026-02-23 02:46:40'),
(815, 75, 'User Archived', 'Archived user ID: 114', '120.29.87.218', '2026-02-23 02:47:06'),
(816, 75, 'Logout', 'User logged out', '120.29.87.218', '2026-02-23 02:47:16'),
(820, 75, 'Login', 'User logged in successfully', '120.29.87.218', '2026-02-23 02:56:08'),
(821, 75, 'Logout', 'User logged out', '::1', '2026-02-23 03:46:50'),
(822, 81, 'Login', 'User logged in successfully', '::1', '2026-02-23 03:46:58'),
(823, 81, 'Password Changed', 'User changed password', '::1', '2026-02-23 03:47:17'),
(824, 81, 'Logout', 'User logged out', '::1', '2026-02-23 03:48:10'),
(825, 75, 'Login', 'User logged in successfully', '::1', '2026-02-23 03:48:20'),
(826, 75, 'Logout', 'User logged out', '::1', '2026-02-23 03:51:07'),
(827, 81, 'Login', 'User logged in successfully', '::1', '2026-02-23 03:51:18'),
(828, 75, 'Login', 'User logged in successfully', '::1', '2026-02-23 03:52:38'),
(829, 75, 'Logout', 'User logged out', '::1', '2026-02-23 03:57:01'),
(830, 81, 'Login', 'User logged in successfully', '::1', '2026-02-23 03:57:12'),
(831, 81, 'Schedule Updated', 'Updated session ID: 86', '::1', '2026-02-23 03:57:21'),
(832, 81, 'Schedule Updated', 'Updated session ID: 86', '::1', '2026-02-23 03:57:28'),
(833, 81, 'Logout', 'User logged out', '::1', '2026-02-23 03:58:00'),
(834, 81, 'Login', 'User logged in successfully', '::1', '2026-02-23 03:58:20'),
(835, 81, 'Logout', 'User logged out', '::1', '2026-02-23 03:58:57'),
(836, 75, 'Login', 'User logged in successfully', '::1', '2026-02-23 03:59:09'),
(837, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 03:26:07'),
(838, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 04:22:57'),
(839, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 10:30:22'),
(840, 75, 'Schedule Removed', 'Removed session ID: 86', '::1', '2026-03-06 10:32:38'),
(841, 75, 'Schedule Removed', 'Removed session ID: 87', '::1', '2026-03-06 10:32:43'),
(842, 75, 'Schedule Updated', 'Updated session ID: 88', '::1', '2026-03-06 10:41:35'),
(843, 75, 'Schedule Updated', 'Updated session ID: 90', '::1', '2026-03-06 10:41:43'),
(844, 75, 'Logout', 'User logged out', '::1', '2026-03-06 10:41:50'),
(848, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 11:39:22'),
(849, 75, 'Logout', 'User logged out', '::1', '2026-03-06 11:54:05'),
(853, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 11:55:36'),
(854, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 15:05:12'),
(855, 75, 'Logout', 'User logged out', '::1', '2026-03-06 15:25:37'),
(859, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 15:27:59'),
(860, 75, 'Logout', 'User logged out', '::1', '2026-03-06 15:56:34'),
(864, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 15:57:27'),
(865, 75, 'Logout', 'User logged out', '::1', '2026-03-06 17:19:30'),
(868, 75, 'Login', 'User logged in successfully', '::1', '2026-03-06 18:40:07'),
(869, 75, 'Logout', 'User logged out', '::1', '2026-03-06 18:44:13'),
(870, 81, 'Login', 'User logged in successfully', '::1', '2026-03-06 18:44:22'),
(871, 81, 'Logout', 'User logged out', '::1', '2026-03-06 18:44:58'),
(872, 75, 'Login', 'User logged in successfully', '::1', '2026-03-07 03:15:15'),
(873, 75, 'Login', 'User logged in successfully', '::1', '2026-03-24 05:09:01'),
(874, 75, 'Logout', 'User logged out', '::1', '2026-03-24 05:09:17'),
(877, 75, 'Login', 'User logged in successfully', '::1', '2026-03-24 05:09:42'),
(878, 75, 'Logout', 'User logged out', '::1', '2026-03-24 05:12:28'),
(881, 75, 'Login', 'User logged in successfully', '::1', '2026-03-24 05:12:58'),
(882, 75, 'Login', 'User logged in successfully', '::1', '2026-03-25 09:46:05'),
(883, 75, 'User Deleted', 'Permanently deleted user ID: 114', '120.29.69.134', '2026-03-25 13:58:32'),
(884, 75, 'User Created', 'Account created for 22-0139-28', '120.29.69.134', '2026-03-25 13:58:42'),
(885, 75, 'User Archived', 'Archived user ID: 116', '120.29.69.134', '2026-03-25 15:07:01'),
(886, 75, 'User Deleted', 'Permanently deleted user ID: 116', '120.29.69.134', '2026-03-25 15:07:08'),
(887, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-03-25 15:08:39'),
(891, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-25 15:10:03'),
(892, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-03-25 15:18:54'),
(896, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-25 15:21:27'),
(897, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-03-25 15:22:09'),
(901, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-25 15:29:28'),
(902, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-03-25 15:30:01'),
(903, 81, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-25 15:30:21'),
(904, 81, 'Logout', 'User logged out', '120.29.69.134', '2026-03-25 15:32:18'),
(905, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-25 15:32:46'),
(906, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-03-25 15:33:07'),
(907, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-25 15:33:14'),
(908, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-03-25 15:36:44'),
(911, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-25 15:37:19'),
(912, 75, 'User Created', 'Account created for 22-1239-28', '120.29.69.134', '2026-03-25 15:45:58'),
(913, 75, 'User Archived', 'Archived user ID: 117', '120.29.69.134', '2026-03-25 15:47:21'),
(914, 75, 'User Archived', 'Archived user ID: 115', '120.29.69.134', '2026-03-25 15:47:25'),
(915, 75, 'User Restored', 'Restored user ID: 115', '120.29.69.134', '2026-03-25 15:47:30'),
(916, 75, 'User Deleted', 'Permanently deleted user ID: 117', '120.29.69.134', '2026-03-25 15:47:37'),
(917, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-03-25 15:51:27'),
(920, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-25 15:52:21'),
(921, 75, 'Schedule Updated', 'Updated session ID: 106', '120.29.69.134', '2026-03-25 15:53:12'),
(922, 75, 'User Updated', 'Updated user ID: 115', '120.29.69.134', '2026-03-25 15:55:32'),
(923, 75, 'User Archived', 'Archived user ID: 115', '120.29.69.134', '2026-03-25 16:09:13'),
(924, 75, 'User Deleted', 'Permanently deleted user ID: 115', '120.29.69.134', '2026-03-25 16:09:19'),
(925, 75, 'Login', 'User logged in successfully', '120.29.86.114', '2026-03-25 20:08:52'),
(926, 75, 'Logout', 'User logged out', '120.29.86.114', '2026-03-25 20:13:56'),
(927, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-26 00:10:42'),
(929, 75, 'Login', 'User logged in successfully', '175.176.29.135', '2026-03-26 02:40:02'),
(930, 75, 'Login', 'User logged in successfully', '175.176.29.135', '2026-03-26 02:40:43'),
(931, 75, 'Login', 'User logged in successfully', '110.54.156.181', '2026-03-26 03:16:03'),
(932, 75, 'User Created', 'Account created for 22-1239-28', '110.54.156.181', '2026-03-26 03:20:32'),
(933, 75, 'User Archived', 'Archived user ID: 119', '110.54.156.181', '2026-03-26 03:21:21'),
(934, 75, 'Logout', 'User logged out', '110.54.156.181', '2026-03-26 03:29:00'),
(938, 75, 'Login', 'User logged in successfully', '110.54.156.181', '2026-03-26 03:30:02'),
(939, 75, 'User Deleted', 'Permanently deleted user ID: 119', '110.54.156.181', '2026-03-26 03:32:17'),
(940, 75, 'User Created', 'Account created for 22-1239-28', '110.54.156.181', '2026-03-26 03:32:26'),
(941, 75, 'Logout', 'User logged out', '110.54.156.181', '2026-03-26 03:33:07'),
(945, 75, 'Login', 'User logged in successfully', '110.54.156.181', '2026-03-26 03:36:01'),
(946, 75, 'Logout', 'User logged out', '110.54.156.181', '2026-03-26 03:37:16'),
(949, 75, 'Login', 'User logged in successfully', '110.54.156.181', '2026-03-26 03:37:47'),
(950, 75, 'Logout', 'User logged out', '110.54.156.181', '2026-03-26 03:40:37'),
(951, 75, 'Login', 'User logged in successfully', '110.54.156.181', '2026-03-26 03:40:46'),
(952, 75, 'User Created', 'Account created for 22-1239-30', '110.54.156.181', '2026-03-26 03:47:49'),
(953, 75, 'Logout', 'User logged out', '110.54.156.181', '2026-03-26 03:48:29'),
(957, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-03-30 12:06:30'),
(958, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-03-30 12:07:20'),
(959, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-04-09 16:20:42'),
(960, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-04-09 16:23:50'),
(963, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-04-09 16:24:11'),
(965, 75, 'Logout', 'User logged out', '120.29.69.134', '2026-04-09 16:26:26'),
(966, 75, 'Login', 'User logged in successfully', '120.29.86.114', '2026-04-10 04:10:18'),
(967, 75, 'Logout', 'User logged out', '120.29.86.114', '2026-04-10 04:28:36'),
(968, 75, 'Login', 'User logged in successfully', '120.29.86.114', '2026-04-10 04:30:48'),
(969, 75, 'Logout', 'User logged out', '120.29.86.114', '2026-04-10 04:31:07'),
(972, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-04-10 06:55:03'),
(973, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-04-10 10:16:50'),
(974, 75, 'Login', 'User logged in successfully', '120.29.69.134', '2026-04-15 06:21:09'),
(977, 75, 'Login', 'User logged in successfully', '112.198.121.18', '2026-04-16 06:41:55'),
(978, 75, 'Logout', 'User logged out', '110.54.156.239', '2026-04-16 06:45:46'),
(981, 75, 'Login', 'User logged in successfully', '110.54.156.239', '2026-04-16 06:46:56'),
(982, 75, 'Logout', 'User logged out', '110.54.156.239', '2026-04-16 06:48:36'),
(986, 75, 'Login', 'User logged in successfully', '110.54.156.239', '2026-04-16 06:49:40'),
(987, 75, 'Logout', 'User logged out', '110.54.156.239', '2026-04-16 07:03:41'),
(991, 75, 'Login', 'User logged in successfully', '110.54.156.239', '2026-04-16 07:06:28'),
(992, 75, 'Login', 'User logged in successfully', '120.29.69.212', '2026-05-02 09:11:17'),
(993, 75, 'User Archived', 'Archived user ID: 120', '120.29.69.212', '2026-05-02 09:11:46'),
(994, 75, 'User Archived', 'Archived user ID: 121', '120.29.69.212', '2026-05-02 09:11:50'),
(995, 75, 'User Archived', 'Archived user ID: 101', '120.29.69.212', '2026-05-02 09:11:55'),
(996, 75, 'User Deleted', 'Permanently deleted user ID: 101', '120.29.69.212', '2026-05-02 09:12:01'),
(997, 75, 'User Deleted', 'Permanently deleted user ID: 121', '120.29.69.212', '2026-05-02 09:12:06'),
(998, 75, 'User Deleted', 'Permanently deleted user ID: 120', '120.29.69.212', '2026-05-02 09:12:11');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_feedbacks`
--

CREATE TABLE `attendance_feedbacks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `target_date` date NOT NULL,
  `message` text NOT NULL,
  `status` enum('Pending','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `am_in` time DEFAULT NULL,
  `am_out` time DEFAULT NULL,
  `pm_in` time DEFAULT NULL,
  `pm_out` time DEFAULT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Present',
  `method` varchar(20) DEFAULT 'Manual',
  `edited_by` int(11) DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `working_hours` decimal(10,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_schedules`
--

CREATE TABLE `class_schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('Class','Office') NOT NULL DEFAULT 'Class',
  `day_of_week` varchar(15) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `holiday_date` date NOT NULL,
  `description` varchar(100) NOT NULL,
  `type` enum('Regular','Special') DEFAULT 'Regular'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `holiday_date`, `description`, `type`) VALUES
(24, '2026-01-01', 'New Years Day', 'Regular'),
(25, '2026-12-25', 'Christmas Day', 'Regular'),
(26, '2026-03-20', 'Eid\'l Fitr', 'Regular'),
(27, '2026-04-02', 'Maundy Thursday', 'Regular'),
(28, '2026-04-03', 'Good Friday', 'Regular'),
(29, '2026-04-09', 'Araw ng Kagitingan (Day of Valor)', 'Regular'),
(30, '2026-05-01', 'Labor Day', 'Regular'),
(31, '2026-06-12', 'Independence Day', 'Regular'),
(32, '2026-08-31', 'National Heroes Day', 'Regular'),
(33, '2026-11-30', 'Bonifacio Day', 'Regular'),
(34, '2026-12-30', 'Rizal Day', 'Regular'),
(35, '2026-05-27', 'Eid\'l Adha (Estimated Date)', 'Regular');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`ip_address`, `attempts`, `last_attempt`) VALUES
('103.234.126.4', 5, '2026-04-11 03:54:37'),
('103.59.142.98', 3, '2026-03-26 10:29:23'),
('119.2.125.177', 4, '2026-04-13 16:16:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `type` varchar(20) DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`) VALUES
(35, 'Cafeteria'),
(32, 'Clinic'),
(22, 'Com-Lab 1'),
(23, 'Com-Lab 2'),
(24, 'Com-Lab 3'),
(25, 'Com-Lab 4'),
(31, 'Guidance Office'),
(36, 'Library'),
(34, 'Lobby/Gate Entrance'),
(27, 'MIS Office'),
(21, 'NTT Lab'),
(28, 'OVPA'),
(29, 'Registrar'),
(1, 'Room 101'),
(2, 'Room 102'),
(3, 'Room 103'),
(4, 'Room 104'),
(5, 'Room 105'),
(6, 'Room 106'),
(7, 'Room 107'),
(8, 'Room 108'),
(9, 'Room 109'),
(10, 'Room 110'),
(11, 'Room 201'),
(12, 'Room 202'),
(13, 'Room 203'),
(14, 'Room 204'),
(15, 'Room 205'),
(16, 'Room 206'),
(17, 'Room 207'),
(18, 'Room 208'),
(19, 'Room 209'),
(20, 'Room 210'),
(30, 'Scholarship Office'),
(26, 'Science Lab'),
(33, 'Student Government');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'system_name', 'BPC Attendance System', 'System name', '2025-10-22 02:13:09'),
(2, 'office_start_time', '07:00:00', 'Office start time', '2026-01-23 19:14:03'),
(3, 'office_end_time', '18:00:00', 'Office end time', '2026-01-23 19:14:19'),
(4, 'late_threshold_minutes', '15', 'Minutes after start to mark late', '2025-10-22 02:13:09'),
(7, 'dtr_in_charge_name', 'Mr. Mel Oliver Balagtas', NULL, '2026-03-25 20:11:32'),
(8, 'dtr_in_charge_title', 'OIC - College President', NULL, '2026-03-25 20:11:32'),
(17, 'qr_interface_pin', '1111', NULL, '2026-04-10 04:11:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `faculty_id` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `fingerprint_registered` tinyint(1) DEFAULT 0,
  `fingerprint_registered_at` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `force_password_change` tinyint(1) DEFAULT 1,
  `qr_token` varchar(255) DEFAULT NULL,
  `reset_otp_hash` varchar(255) DEFAULT NULL,
  `reset_otp_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `email_notifications_enabled` tinyint(1) DEFAULT 1,
  `weekly_summary_enabled` tinyint(1) DEFAULT 1,
  `profile_image` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `faculty_id`, `username`, `password`, `first_name`, `last_name`, `middle_name`, `email`, `phone`, `role`, `fingerprint_registered`, `fingerprint_registered_at`, `status`, `force_password_change`, `qr_token`, `reset_otp_hash`, `reset_otp_expires`, `created_at`, `updated_at`, `profile_picture`, `email_notifications_enabled`, `weekly_summary_enabled`, `profile_image`, `remember_token`, `otp_code`, `otp_expiry`) VALUES
(75, 'BPCMA001', 'bpcma001', '$2y$10$op9tejAlaQ6841vip9/ssOMMVQ..wwoMWLcDriR9y06/ng11pTlG2', 'BPC', 'ADMINISTRATOR', '', 'BPC.MA001@gmail.com', '+63', 'Admin', 0, NULL, 'active', 0, '241c4f66b7044ab18a1dd2c79c0f6ff6', NULL, NULL, '2026-02-22 12:20:34', '2026-05-02 09:13:15', NULL, 1, 1, '', NULL, NULL, NULL),
(81, 'BPCSA001', 'bpcsa001', '$2y$10$CJtcRX9VVp1GV10KepSkc.iuaPM96aW5iHrcWmdgWAepFo.KS7kc2', 'BPC SCHEDULE', 'ADMINISTRATOR', NULL, 'BPC.SA001@gmail.com', NULL, 'Schedule Admin', 0, NULL, 'active', 0, '2c9cb01f5f026d4ffa2cec9648c0d036', NULL, NULL, '2026-02-22 12:57:05', '2026-02-23 03:47:17', NULL, 1, 1, NULL, NULL, NULL, NULL),
(118, 'FAC001', 'jdelacruz', '$2y$10$l0F7BmDwTDH.XfwraFabqeoZAQcKoTvepMmDe/1va4WeLd7irYP1u', 'Juan', 'Dela Cruz', 'P.', 'juan@bpc.edu.ph', '9123456789', 'Teacher', 0, NULL, 'active', 1, '862ee2b05744d316132ee49b347a5cd1', NULL, NULL, '2026-03-26 03:19:58', '2026-03-26 03:19:58', NULL, 1, 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_fingerprints`
--

CREATE TABLE `user_fingerprints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `finger_name` varchar(50) DEFAULT 'Unknown',
  `fingerprint_data` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `attendance_feedbacks`
--
ALTER TABLE `attendance_feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`date`),
  ADD KEY `date` (`date`),
  ADD KEY `fk_attendance_schedule` (`schedule_id`),
  ADD KEY `idx_attendance_date_user` (`date`,`user_id`);

--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_idx` (`user_id`),
  ADD KEY `idx_schedule_user_day` (`user_id`,`day_of_week`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`ip_address`),
  ADD KEY `ip_address` (`ip_address`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_idx` (`user_id`,`is_read`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_id` (`faculty_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `faculty_id_2` (`faculty_id`),
  ADD KEY `username_2` (`username`),
  ADD KEY `status` (`status`),
  ADD KEY `idx_faculty_id` (`faculty_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_remember_token` (`remember_token`);

--
-- Indexes for table `user_fingerprints`
--
ALTER TABLE `user_fingerprints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=999;

--
-- AUTO_INCREMENT for table `attendance_feedbacks`
--
ALTER TABLE `attendance_feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `class_schedules`
--
ALTER TABLE `class_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `user_fingerprints`
--
ALTER TABLE `user_fingerprints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance_feedbacks`
--
ALTER TABLE `attendance_feedbacks`
  ADD CONSTRAINT `attendance_feedbacks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_fingerprints`
--
ALTER TABLE `user_fingerprints`
  ADD CONSTRAINT `user_fingerprints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
