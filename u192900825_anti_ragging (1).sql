-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 04, 2025 at 08:08 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u192900825_anti_ragging`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`u192900825_ar_admin`@`127.0.0.1` PROCEDURE `RecordReportView` (IN `p_report_id` INT, IN `p_viewer_type` ENUM('admin','university_authority','system','investigator'), IN `p_viewer_name` VARCHAR(255), IN `p_viewer_email` VARCHAR(255), IN `p_action_taken` VARCHAR(255), IN `p_notes` TEXT)   BEGIN
    INSERT INTO report_views (report_id, viewer_type, viewer_name, viewer_email, action_taken, notes)
    VALUES (p_report_id, p_viewer_type, p_viewer_name, p_viewer_email, p_action_taken, p_notes);
END$$

CREATE DEFINER=`u192900825_ar_admin`@`127.0.0.1` PROCEDURE `UpdateReportStatus` (IN `p_report_id` INT, IN `p_new_status` VARCHAR(50), IN `p_changed_by` VARCHAR(255), IN `p_notes` TEXT)   BEGIN
    DECLARE v_old_status VARCHAR(50);
    
    -- Get current status
    SELECT status INTO v_old_status FROM reports WHERE id = p_report_id;
    
    -- Update report status
    UPDATE reports SET status = p_new_status WHERE id = p_report_id;
    
    -- Insert status history
    INSERT INTO status_history (report_id, old_status, new_status, changed_by, notes)
    VALUES (p_report_id, v_old_status, p_new_status, p_changed_by, p_notes);
    
    -- Update process timeline if status matches a step
    UPDATE process_timeline 
    SET status = 'completed', completed_at = NOW()
    WHERE report_id = p_report_id 
    AND step_name = CASE 
        WHEN p_new_status = 'Submitted' THEN 'Report Submission'
        WHEN p_new_status = 'Under Review' THEN 'Initial Review'
        WHEN p_new_status = 'Action Initiated' THEN 'Investigation'
        WHEN p_new_status = 'Resolved' THEN 'Resolution'
        ELSE NULL
    END;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ar_admin`
--

CREATE TABLE `ar_admin` (
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ar_admin`
--

INSERT INTO `ar_admin` (`username`, `password`, `full_name`, `email`, `role`, `is_active`, `created_at`, `last_login`) VALUES
('admin', '$2y$10$Z2/9jMtfjmvVAkHz4WjrO.F4HKCHNkmAV/TJhPYULnXlWMfWjdhNy', 'Samunda De Silva', 'admin@ar.com', 'admin', 1, '2025-08-31 07:32:45', '2025-09-04 07:43:41'),
('admin1', '$2y$10$H9Ctc.SlQ3.72FP6jmvvkeJUQMIQ/OGksOgLYHHa7ZTWq3gbOL2qG', 'AR Admin', 'admin@antiragging.xyz', 'admin', 1, '2025-09-04 08:52:38', '2025-09-04 11:17:27');

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attachments`
--

INSERT INTO `attachments` (`id`, `report_id`, `original_name`, `stored_name`, `mime_type`, `size_bytes`, `uploaded_at`) VALUES
(1, 1, 'logo.png', 'AR22694783_bc16d0c46c0f011d.png', 'image/png', 698, '2025-08-22 09:38:36'),
(2, 2, 'Are you Looking for a so.png', 'AR49967980_d14a55a3e2e092f0.png', 'image/png', 1366793, '2025-08-22 09:56:08'),
(3, 3, '000000dfLV1.drawio.png', 'AR87470504_92a88139a53a2fd9.png', 'image/png', 135445, '2025-08-30 22:59:07'),
(4, 3, '000000dfdlv0.drawio.png', 'AR87470504_4a6a87a3298019a2.png', 'image/png', 54749, '2025-08-30 22:59:07'),
(5, 3, '000000dfdlv1.drawio.png', 'AR87470504_bd4ee19630a50136.png', 'image/png', 132863, '2025-08-30 22:59:07'),
(6, 4, 'WhatsApp Video 2025-08-29 at 23.02.34_37694cc4.mp4', 'AR97650828_6f49be2eee4e96e4.mp4', 'video/mp4', 1988596, '2025-08-31 00:35:16'),
(7, 4, 'Eminem - Lose Yourself [HD].mp3', 'AR97650828_4d9665885aa8cf06.mp3', 'audio/mpeg', 7758765, '2025-08-31 00:35:16'),
(8, 0, 'structured english ii.png', 'AR67105273_590731b602302aa8.png', 'image/png', 41817, '2025-08-31 05:51:00'),
(9, 0, '000000dfLV1.drawio.png', 'AR17563033_38f3ba458f08fa7a.png', 'image/png', 135445, '2025-08-31 06:01:39'),
(10, 0, 'Eminem - Lose Yourself [HD].mp3', 'AR42284067_a54bd7792ea4f02c.mp3', 'audio/mpeg', 7758765, '2025-08-31 06:54:04'),
(11, 0, 'seaqunce_Samu.drawio.png', 'AR25459773_ef01f9d64da0e26d.png', 'image/png', 358666, '2025-08-31 07:09:00'),
(12, 0, 'file_2025-08-31_07.24.34.png', 'AR91209267_155ccc220078f144.png', 'image/png', 787915, '2025-08-31 08:25:02'),
(13, 0, 'twilio_2FA_recovery_code.txt', 'AR27895431_46e0e24c07367c77.txt', 'text/plain', 24, '2025-08-31 08:40:06'),
(14, 0, 'report_AR97650828.pdf', 'AR87925863_aa3f9f7a106cb77b.pdf', 'application/pdf', 26304, '2025-08-31 08:46:36'),
(15, 0, 'Eminem - Lose Yourself [HD].mp3', 'AR82403883_ea5f5c984718ecac.mp3', 'audio/mpeg', 7758765, '2025-08-31 08:58:11'),
(16, 0, 'file_2025-08-31_07.24.34.png', 'AR53289453_0fb2c485b8b9e4eb.png', 'image/png', 787915, '2025-08-31 09:05:30'),
(17, 0, 'report_AR97650828.pdf', 'AR26971808_6f04fed9211f8edf.pdf', 'application/pdf', 26304, '2025-08-31 09:09:14'),
(18, 21, 'young-man-student-study-at-home-using-laptop-and-learning-online-CAVF64908.jpg', 'AR60806777_36fca6e4c23f501d.jpg', 'image/jpeg', 67516, '2025-08-31 12:08:30'),
(19, 23, 'seaqunce_Samu.drawio.png', 'AR48199254_64270df323d35eb5.png', 'image/png', 358666, '2025-08-31 14:04:59'),
(20, 24, 'screencapture-antiragging-xyz-admin-authorities-add-php-2025-09-02-16_45_24.png', 'AR25959237_92b2cc01a275d0ea.png', 'image/png', 220419, '2025-09-02 11:19:40'),
(21, 26, 'screencapture-localhost-EduConnectSL-Peradeniya-programs-php-2025-09-02-10_31_11.png', 'AR22530504_5a675c8fc6b03591.png', 'image/png', 895531, '2025-09-02 17:13:51'),
(22, 27, 'IMG-20250902-WA0032.jpg', 'AR33526399_7636b2e734e57140.jpg', 'image/jpeg', 26366, '2025-09-03 08:06:05'),
(23, 28, 'IMG-20250902-WA0032.jpg', 'AR95954006_42e6c96a85aefb3e.jpg', 'image/jpeg', 26366, '2025-09-03 08:09:20'),
(24, 29, 'IMG-20250902-WA0021.jpg', 'AR12552319_04d8493f7f3fc554.jpg', 'image/jpeg', 56353, '2025-09-03 08:15:32'),
(25, 30, 'Screen Recording 2025-05-20 184806.mp4', 'AR90641894_9541d0fde344b4b9.mp4', 'video/mp4', 8768988, '2025-09-03 08:34:23'),
(33, 31, 'Recording 2025-07-14 201553.mp4', 'AR30725035_993984bb7c067829.mp4', 'video/mp4', 2073435, '2025-09-03 09:59:03'),
(34, 32, 'Screenshot 2025-09-01 084620.png', 'AR27306396_644d32f506eaecf8.png', 'image/png', 33279, '2025-09-03 10:06:16'),
(35, 33, 'pexels-manfred-schnell-18420813.jpg', 'AR42864640_e5f98eebb5fda061.jpg', 'image/jpeg', 568719, '2025-09-03 10:18:07'),
(36, 34, 'structured english ii.png', 'AR60443563_995fa943d34ea02d.png', 'image/png', 41817, '2025-09-03 10:19:36'),
(37, 35, '000000dfLV1.drawio.png', 'AR87897078_6e5a930424760bea.png', 'image/png', 135445, '2025-09-03 10:24:37'),
(38, 36, '1E175F97-2760-419E-BDBE-1BC75989F287.jpeg', 'AR74976726_346df98374434ba9.jpeg', 'image/jpeg', 31192, '2025-09-03 10:34:11'),
(39, 36, '3713623F-EB73-40F8-B4E8-DC64CB88B3D1.jpeg', 'AR74976726_45e5f3062b2f6146.jpeg', 'image/jpeg', 21580, '2025-09-03 10:34:11'),
(40, 36, '373C2667-713E-467D-A225-0CABE8CE7F90.jpeg', 'AR74976726_0970aab81652f212.jpeg', 'image/jpeg', 24486, '2025-09-03 10:34:11'),
(41, 36, '1138CF08-9CB9-4C5F-9298-DEEA130BD7F3.jpeg', 'AR74976726_48b296f3e794a713.jpeg', 'image/jpeg', 21058, '2025-09-03 10:34:11'),
(42, 37, '17BCA8E7-A390-48A0-95AC-3F4E1C4F7AC3.jpeg', 'AR80119087_b4c481a8348a70d3.jpeg', 'image/jpeg', 16043, '2025-09-03 10:36:30'),
(43, 37, 'E6E290A9-300C-4390-B0B9-6F0847841F93.jpeg', 'AR80119087_bbf1631b94aabe6c.jpeg', 'image/jpeg', 33103, '2025-09-03 10:36:30'),
(44, 37, '92F0CA50-09E2-420D-ACD5-5B2A74CE2DD4.jpeg', 'AR80119087_2f0a10cc8116453b.jpeg', 'image/jpeg', 25251, '2025-09-03 10:36:30'),
(45, 38, '56CACB75-3365-469D-9FBF-910027EE6798.jpeg', 'AR22746514_40548b84deb2f907.jpeg', 'image/jpeg', 26076, '2025-09-03 10:48:58'),
(46, 38, 'BFC10250-5F27-422A-B163-0C7BA70E0A91.jpeg', 'AR22746514_1152bf17147c1f67.jpeg', 'image/jpeg', 34877, '2025-09-03 10:48:58'),
(47, 41, 'WhatsApp Video 2025-08-29 at 23.02.34_37694cc4.mp4', 'AR51065728_0ca59f3798ad4da9.mp4', 'video/mp4', 1988596, '2025-09-03 15:53:44'),
(48, 42, 'dataset-cover.jpg', 'AR79003923_539643bbdd60d73e.jpg', 'image/jpeg', 65052, '2025-09-04 03:44:37');

-- --------------------------------------------------------

--
-- Table structure for table `process_timeline`
--

CREATE TABLE `process_timeline` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `step_name` varchar(100) DEFAULT NULL,
  `step_description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `assigned_to` varchar(255) DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `process_timeline`
--

INSERT INTO `process_timeline` (`id`, `report_id`, `step_name`, `step_description`, `status`, `assigned_to`, `started_at`, `completed_at`, `created_at`, `updated_at`, `notes`) VALUES
(1, 1, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-22 09:38:36', '2025-08-22 09:38:36', '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(2, 1, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(3, 1, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(4, 1, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(5, 1, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(6, 1, 'Resolution', 'Case resolved and closed', 'completed', 'System', NULL, '2025-08-25 08:19:23', '2025-08-22 09:38:36', '2025-08-25 08:19:23', NULL),
(7, 1, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-22 09:38:36', '2025-08-22 09:38:36', '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(8, 1, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-22 09:38:36', NULL, '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(9, 1, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-22 09:38:36', NULL, '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(10, 1, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-22 09:38:36', NULL, '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(11, 1, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-22 09:38:36', NULL, '2025-08-22 09:38:36', '2025-08-22 09:38:36', NULL),
(12, 1, 'Resolution', 'Case resolved and closed', 'completed', 'System', '2025-08-22 09:38:36', '2025-08-25 08:19:23', '2025-08-22 09:38:36', '2025-08-25 08:19:23', NULL),
(13, 2, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-22 09:56:08', '2025-08-22 09:56:08', '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(14, 2, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', NULL, '2025-08-25 08:00:27', '2025-08-22 09:56:08', '2025-08-25 08:00:27', NULL),
(15, 2, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(16, 2, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(17, 2, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(18, 2, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(19, 2, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-22 09:56:08', '2025-08-22 09:56:08', '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(20, 2, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', '2025-08-22 09:56:08', '2025-08-25 08:00:27', '2025-08-22 09:56:08', '2025-08-25 08:00:27', NULL),
(21, 2, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-22 09:56:08', NULL, '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(22, 2, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-22 09:56:08', NULL, '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(23, 2, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-22 09:56:08', NULL, '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(24, 2, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-22 09:56:08', NULL, '2025-08-22 09:56:08', '2025-08-22 09:56:08', NULL),
(25, 3, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-30 22:59:07', '2025-08-30 22:59:07', '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(26, 3, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(27, 3, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(28, 3, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(29, 3, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(30, 3, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(31, 3, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-30 22:59:07', '2025-08-30 22:59:07', '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(32, 3, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-30 22:59:07', NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(33, 3, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-30 22:59:07', NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(34, 3, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-30 22:59:07', NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(35, 3, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-30 22:59:07', NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(36, 3, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-30 22:59:07', NULL, '2025-08-30 22:59:07', '2025-08-30 22:59:07', NULL),
(37, 4, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 00:35:16', '2025-08-31 00:35:16', '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(38, 4, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', NULL, '2025-08-31 00:39:45', '2025-08-31 00:35:16', '2025-08-31 00:39:45', NULL),
(39, 4, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(40, 4, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(41, 4, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(42, 4, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(43, 4, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 00:35:16', '2025-08-31 00:35:16', '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(44, 4, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', '2025-08-31 00:35:16', '2025-08-31 00:39:45', '2025-08-31 00:35:16', '2025-08-31 00:39:45', NULL),
(45, 4, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 00:35:16', NULL, '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(46, 4, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 00:35:16', NULL, '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(47, 4, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 00:35:16', NULL, '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(48, 4, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 00:35:16', NULL, '2025-08-31 00:35:16', '2025-08-31 00:35:16', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 05:51:00', '2025-08-31 09:09:14', '2025-08-31 05:51:00', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 05:51:00', '2025-08-31 09:09:14', '2025-08-31 05:51:00', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 05:51:00', NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 05:51:00', NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 05:51:00', NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 05:51:00', NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 05:51:00', NULL, '2025-08-31 05:51:00', '2025-08-31 05:51:00', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 06:01:39', '2025-08-31 09:09:14', '2025-08-31 06:01:39', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 06:01:39', '2025-08-31 09:09:14', '2025-08-31 06:01:39', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 06:01:39', NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 06:01:39', NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 06:01:39', NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 06:01:39', NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 06:01:39', NULL, '2025-08-31 06:01:39', '2025-08-31 06:01:39', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 06:54:04', '2025-08-31 09:09:14', '2025-08-31 06:54:04', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 06:54:04', '2025-08-31 09:09:14', '2025-08-31 06:54:04', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 06:54:04', NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 06:54:04', NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 06:54:04', NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 06:54:04', NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 06:54:04', NULL, '2025-08-31 06:54:04', '2025-08-31 06:54:04', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 07:09:00', '2025-08-31 09:09:14', '2025-08-31 07:09:00', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 07:09:00', '2025-08-31 09:09:14', '2025-08-31 07:09:00', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 07:09:00', NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 07:09:00', NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 07:09:00', NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 07:09:00', NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 07:09:00', NULL, '2025-08-31 07:09:00', '2025-08-31 07:09:00', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 08:25:02', '2025-08-31 09:09:14', '2025-08-31 08:25:02', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 08:25:02', '2025-08-31 09:09:14', '2025-08-31 08:25:02', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 08:25:02', NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 08:25:02', NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 08:25:02', NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 08:25:02', NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 08:25:02', NULL, '2025-08-31 08:25:02', '2025-08-31 08:25:02', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 08:40:06', '2025-08-31 09:09:14', '2025-08-31 08:40:06', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 08:40:06', '2025-08-31 09:09:14', '2025-08-31 08:40:06', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 08:40:06', NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 08:40:06', NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 08:40:06', NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 08:40:06', NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 08:40:06', NULL, '2025-08-31 08:40:06', '2025-08-31 08:40:06', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 08:46:36', '2025-08-31 09:09:14', '2025-08-31 08:46:36', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 08:46:36', '2025-08-31 09:09:14', '2025-08-31 08:46:36', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 08:46:36', NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 08:46:36', NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 08:46:36', NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 08:46:36', NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 08:46:36', NULL, '2025-08-31 08:46:36', '2025-08-31 08:46:36', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 08:58:11', '2025-08-31 09:09:14', '2025-08-31 08:58:11', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 08:58:11', '2025-08-31 09:09:14', '2025-08-31 08:58:11', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 08:58:11', NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 08:58:11', NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 08:58:11', NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 08:58:11', NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 08:58:11', NULL, '2025-08-31 08:58:11', '2025-08-31 08:58:11', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 09:05:30', '2025-08-31 09:09:14', '2025-08-31 09:05:30', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 09:05:30', '2025-08-31 09:09:14', '2025-08-31 09:05:30', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 09:05:30', NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 09:05:30', NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 09:05:30', NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 09:05:30', NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 09:05:30', NULL, '2025-08-31 09:05:30', '2025-08-31 09:05:30', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 09:09:14', '2025-08-31 09:09:14', '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 09:09:14', '2025-08-31 09:09:14', '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 09:09:14', NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 09:09:14', NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 09:09:14', NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 09:09:14', NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 0, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 09:09:14', NULL, '2025-08-31 09:09:14', '2025-08-31 09:09:14', NULL),
(0, 19, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 09:45:10', '2025-08-31 09:45:10', '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 09:45:10', '2025-08-31 09:45:10', '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 09:45:10', NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 09:45:10', NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 09:45:10', NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 09:45:10', NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 19, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 09:45:10', NULL, '2025-08-31 09:45:10', '2025-08-31 09:45:10', NULL),
(0, 20, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 11:40:12', '2025-08-31 11:40:12', '2025-08-31 11:40:12', '2025-08-31 11:40:12', NULL),
(0, 20, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', NULL, '2025-08-31 11:54:10', '2025-08-31 11:40:12', '2025-08-31 11:54:10', NULL),
(0, 20, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 11:40:12', '2025-08-31 11:40:12', NULL),
(0, 20, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', NULL, '2025-08-31 12:01:40', '2025-08-31 11:40:12', '2025-08-31 12:01:40', NULL),
(0, 20, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 11:40:12', '2025-08-31 11:40:12', NULL),
(0, 20, 'Resolution', 'Case resolved and closed', 'completed', 'System', NULL, '2025-08-31 13:42:53', '2025-08-31 11:40:12', '2025-08-31 13:42:53', NULL),
(0, 20, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 11:40:12', '2025-08-31 11:40:12', '2025-08-31 11:40:12', '2025-08-31 11:40:12', NULL),
(0, 20, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', '2025-08-31 11:40:12', '2025-08-31 11:54:10', '2025-08-31 11:40:12', '2025-08-31 11:54:10', NULL),
(0, 20, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 11:40:12', NULL, '2025-08-31 11:40:12', '2025-08-31 11:40:12', NULL),
(0, 20, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', '2025-08-31 11:40:12', '2025-08-31 12:01:40', '2025-08-31 11:40:12', '2025-08-31 12:01:40', NULL),
(0, 20, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 11:40:12', NULL, '2025-08-31 11:40:12', '2025-08-31 11:40:12', NULL),
(0, 20, 'Resolution', 'Case resolved and closed', 'completed', 'System', '2025-08-31 11:40:12', '2025-08-31 13:42:53', '2025-08-31 11:40:12', '2025-08-31 13:42:53', NULL),
(0, 21, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 12:08:30', '2025-08-31 12:08:30', '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 12:08:30', '2025-08-31 12:08:30', '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 12:08:30', NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 12:08:30', NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 12:08:30', NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 12:08:30', NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 21, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 12:08:30', NULL, '2025-08-31 12:08:30', '2025-08-31 12:08:30', NULL),
(0, 22, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 13:45:53', '2025-08-31 13:45:53', '2025-08-31 13:45:53', '2025-08-31 13:45:53', NULL),
(0, 22, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 13:45:53', '2025-08-31 13:45:53', NULL),
(0, 22, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-04 19:09:19', '2025-08-31 13:45:53', '2025-09-04 19:09:19', NULL),
(0, 22, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', NULL, '2025-09-04 19:24:09', '2025-08-31 13:45:53', '2025-09-04 19:24:09', NULL),
(0, 22, 'Action Plan', 'bla bla bladf oiasjxc', 'in_progress', 'University Authorities', '2025-09-04 19:21:34', NULL, '2025-08-31 13:45:53', '2025-09-04 19:21:34', NULL),
(0, 22, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 13:45:53', '2025-08-31 13:45:53', NULL),
(0, 22, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 13:45:53', '2025-08-31 13:45:53', '2025-08-31 13:45:53', '2025-08-31 13:45:53', NULL),
(0, 22, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 13:45:53', NULL, '2025-08-31 13:45:53', '2025-08-31 13:45:53', NULL),
(0, 22, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-08-31 13:45:53', '2025-09-04 19:09:19', '2025-08-31 13:45:53', '2025-09-04 19:09:19', NULL),
(0, 22, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', '2025-08-31 13:45:53', '2025-09-04 19:24:09', '2025-08-31 13:45:53', '2025-09-04 19:24:09', NULL),
(0, 22, 'Action Plan', 'bla bla bladf oiasjxc', 'in_progress', 'University Authorities', '2025-08-31 13:45:53', NULL, '2025-08-31 13:45:53', '2025-09-04 19:21:34', NULL),
(0, 22, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 13:45:53', NULL, '2025-08-31 13:45:53', '2025-08-31 13:45:53', NULL),
(0, 23, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 14:04:59', '2025-08-31 14:04:59', '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-08-31 14:04:59', '2025-08-31 14:04:59', '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-08-31 14:04:59', NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-08-31 14:04:59', NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-08-31 14:04:59', NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-08-31 14:04:59', NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 23, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-08-31 14:04:59', NULL, '2025-08-31 14:04:59', '2025-08-31 14:04:59', NULL),
(0, 24, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-02 11:19:40', '2025-09-02 11:19:40', '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-02 11:19:40', '2025-09-02 11:19:40', '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-02 11:19:40', NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-09-02 11:19:40', NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-02 11:19:40', NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-02 11:19:40', NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 24, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-02 11:19:40', NULL, '2025-09-02 11:19:40', '2025-09-02 11:19:40', NULL),
(0, 25, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-02 16:49:35', '2025-09-02 16:49:35', '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-02 16:49:35', '2025-09-02 16:49:35', '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-02 16:49:35', NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-09-02 16:49:35', NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-02 16:49:35', NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-02 16:49:35', NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 25, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-02 16:49:35', NULL, '2025-09-02 16:49:35', '2025-09-02 16:49:35', NULL),
(0, 26, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-02 17:13:51', '2025-09-02 17:13:51', '2025-09-02 17:13:51', '2025-09-02 17:13:51', NULL),
(0, 26, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', NULL, '2025-09-03 10:38:46', '2025-09-02 17:13:51', '2025-09-03 10:38:46', NULL),
(0, 26, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 10:40:17', '2025-09-02 17:13:51', '2025-09-03 10:40:17', NULL),
(0, 26, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', NULL, '2025-09-03 10:39:36', '2025-09-02 17:13:51', '2025-09-03 10:39:36', NULL),
(0, 26, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-02 17:13:51', '2025-09-02 17:13:51', NULL),
(0, 26, 'Resolution', 'Case resolved and closed', 'completed', 'System', NULL, '2025-09-03 10:43:45', '2025-09-02 17:13:51', '2025-09-03 10:43:45', NULL),
(0, 26, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-02 17:13:51', '2025-09-02 17:13:51', '2025-09-02 17:13:51', '2025-09-02 17:13:51', NULL),
(0, 26, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', '2025-09-02 17:13:51', '2025-09-03 10:38:46', '2025-09-02 17:13:51', '2025-09-03 10:38:46', NULL),
(0, 26, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-02 17:13:51', '2025-09-03 10:40:17', '2025-09-02 17:13:51', '2025-09-03 10:40:17', NULL),
(0, 26, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', '2025-09-02 17:13:51', '2025-09-03 10:39:36', '2025-09-02 17:13:51', '2025-09-03 10:39:36', NULL),
(0, 26, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-02 17:13:51', NULL, '2025-09-02 17:13:51', '2025-09-02 17:13:51', NULL),
(0, 26, 'Resolution', 'Case resolved and closed', 'completed', 'System', '2025-09-02 17:13:51', '2025-09-03 10:43:45', '2025-09-02 17:13:51', '2025-09-03 10:43:45', NULL),
(0, 27, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 08:06:05', '2025-09-03 08:06:05', '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 08:06:05', '2025-09-03 08:06:05', '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 08:06:05', NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-09-03 08:06:05', NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 08:06:05', NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 08:06:05', NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 27, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 08:06:05', NULL, '2025-09-03 08:06:05', '2025-09-03 08:06:05', NULL),
(0, 28, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 08:09:20', '2025-09-03 08:09:20', '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 08:09:20', '2025-09-03 08:09:20', '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 08:09:20', NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-09-03 08:09:20', NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 08:09:20', NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 08:09:20', NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 28, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 08:09:20', NULL, '2025-09-03 08:09:20', '2025-09-03 08:09:20', NULL),
(0, 29, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 08:15:32', '2025-09-03 08:15:32', '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL);
INSERT INTO `process_timeline` (`id`, `report_id`, `step_name`, `step_description`, `status`, `assigned_to`, `started_at`, `completed_at`, `created_at`, `updated_at`, `notes`) VALUES
(0, 29, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 08:15:32', '2025-09-03 08:15:32', '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 08:15:32', NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-09-03 08:15:32', NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 08:15:32', NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 08:15:32', NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 29, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 08:15:32', NULL, '2025-09-03 08:15:32', '2025-09-03 08:15:32', NULL),
(0, 30, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 08:34:23', '2025-09-03 08:34:23', '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 08:34:23', '2025-09-03 08:34:23', '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 08:34:23', NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', '2025-09-03 08:34:23', NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 08:34:23', NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 08:34:23', NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 30, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 08:34:23', NULL, '2025-09-03 08:34:23', '2025-09-03 08:34:23', NULL),
(0, 31, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 09:59:03', '2025-09-03 09:59:03', '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 09:59:10', '2025-09-03 09:59:03', '2025-09-03 09:59:10', NULL),
(0, 31, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 09:59:03', '2025-09-03 09:59:03', '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 09:59:03', NULL, '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 09:59:03', '2025-09-03 09:59:10', '2025-09-03 09:59:03', '2025-09-03 09:59:10', NULL),
(0, 31, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 09:59:03', NULL, '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 09:59:03', NULL, '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 31, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 09:59:03', NULL, '2025-09-03 09:59:03', '2025-09-03 09:59:03', NULL),
(0, 32, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:06:16', '2025-09-03 10:06:16', '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 10:06:20', '2025-09-03 10:06:16', '2025-09-03 10:06:20', NULL),
(0, 32, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:06:16', '2025-09-03 10:06:16', '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 10:06:16', NULL, '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 10:06:16', '2025-09-03 10:06:20', '2025-09-03 10:06:16', '2025-09-03 10:06:20', NULL),
(0, 32, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 10:06:16', NULL, '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 10:06:16', NULL, '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 32, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 10:06:16', NULL, '2025-09-03 10:06:16', '2025-09-03 10:06:16', NULL),
(0, 33, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:18:07', '2025-09-03 10:18:07', '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 10:18:19', '2025-09-03 10:18:07', '2025-09-03 10:18:19', NULL),
(0, 33, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:18:07', '2025-09-03 10:18:07', '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 10:18:07', NULL, '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 10:18:07', '2025-09-03 10:18:19', '2025-09-03 10:18:07', '2025-09-03 10:18:19', NULL),
(0, 33, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 10:18:07', NULL, '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 10:18:07', NULL, '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 33, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 10:18:07', NULL, '2025-09-03 10:18:07', '2025-09-03 10:18:07', NULL),
(0, 34, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:19:36', '2025-09-03 10:19:36', '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 10:19:48', '2025-09-03 10:19:36', '2025-09-03 10:19:48', NULL),
(0, 34, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:19:36', '2025-09-03 10:19:36', '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 10:19:36', NULL, '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 10:19:36', '2025-09-03 10:19:48', '2025-09-03 10:19:36', '2025-09-03 10:19:48', NULL),
(0, 34, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 10:19:36', NULL, '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 10:19:36', NULL, '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 34, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 10:19:36', NULL, '2025-09-03 10:19:36', '2025-09-03 10:19:36', NULL),
(0, 35, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:24:37', '2025-09-03 10:24:37', '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 10:24:41', '2025-09-03 10:24:37', '2025-09-03 10:24:41', NULL),
(0, 35, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:24:37', '2025-09-03 10:24:37', '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 10:24:37', NULL, '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 10:24:37', '2025-09-03 10:24:41', '2025-09-03 10:24:37', '2025-09-03 10:24:41', NULL),
(0, 35, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 10:24:37', NULL, '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 10:24:37', NULL, '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 35, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 10:24:37', NULL, '2025-09-03 10:24:37', '2025-09-03 10:24:37', NULL),
(0, 36, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:34:11', '2025-09-03 10:34:11', '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 10:34:14', '2025-09-03 10:34:11', '2025-09-03 10:34:14', NULL),
(0, 36, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:34:11', '2025-09-03 10:34:11', '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 10:34:11', NULL, '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 10:34:11', '2025-09-03 10:34:14', '2025-09-03 10:34:11', '2025-09-03 10:34:14', NULL),
(0, 36, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 10:34:11', NULL, '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 10:34:11', NULL, '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 36, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 10:34:11', NULL, '2025-09-03 10:34:11', '2025-09-03 10:34:11', NULL),
(0, 37, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:36:30', '2025-09-03 10:36:30', '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 10:36:34', '2025-09-03 10:36:30', '2025-09-03 10:36:34', NULL),
(0, 37, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:36:30', '2025-09-03 10:36:30', '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 10:36:30', NULL, '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 10:36:30', '2025-09-03 10:36:34', '2025-09-03 10:36:30', '2025-09-03 10:36:34', NULL),
(0, 37, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 10:36:30', NULL, '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 10:36:30', NULL, '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 37, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 10:36:30', NULL, '2025-09-03 10:36:30', '2025-09-03 10:36:30', NULL),
(0, 38, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:48:58', '2025-09-03 10:48:58', '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 10:49:02', '2025-09-03 10:48:58', '2025-09-03 10:49:02', NULL),
(0, 38, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 10:48:58', '2025-09-03 10:48:58', '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 10:48:58', NULL, '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 10:48:58', '2025-09-03 10:49:02', '2025-09-03 10:48:58', '2025-09-03 10:49:02', NULL),
(0, 38, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 10:48:58', NULL, '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 10:48:58', NULL, '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 38, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 10:48:58', NULL, '2025-09-03 10:48:58', '2025-09-03 10:48:58', NULL),
(0, 39, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 14:51:50', '2025-09-03 14:51:50', '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 14:51:54', '2025-09-03 14:51:50', '2025-09-03 14:51:54', NULL),
(0, 39, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 14:51:50', '2025-09-03 14:51:50', '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 14:51:50', NULL, '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 14:51:50', '2025-09-03 14:51:54', '2025-09-03 14:51:50', '2025-09-03 14:51:54', NULL),
(0, 39, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 14:51:50', NULL, '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 14:51:50', NULL, '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 39, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 14:51:50', NULL, '2025-09-03 14:51:50', '2025-09-03 14:51:50', NULL),
(0, 40, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 14:53:27', '2025-09-03 14:53:27', '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 14:53:31', '2025-09-03 14:53:27', '2025-09-03 14:53:31', NULL),
(0, 40, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 14:53:27', '2025-09-03 14:53:27', '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 14:53:27', NULL, '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 14:53:27', '2025-09-03 14:53:31', '2025-09-03 14:53:27', '2025-09-03 14:53:31', NULL),
(0, 40, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-03 14:53:27', NULL, '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-03 14:53:27', NULL, '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 40, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 14:53:27', NULL, '2025-09-03 14:53:27', '2025-09-03 14:53:27', NULL),
(0, 41, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 15:53:44', '2025-09-04 19:28:00', '2025-09-03 15:53:44', '2025-09-04 19:28:00', NULL),
(0, 41, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL, NULL, '2025-09-03 15:53:44', '2025-09-03 15:53:44', NULL),
(0, 41, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-03 15:53:48', '2025-09-03 15:53:44', '2025-09-03 15:53:48', NULL),
(0, 41, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', NULL, '2025-09-04 19:31:46', '2025-09-03 15:53:44', '2025-09-04 19:31:46', NULL),
(0, 41, 'Action Plan', 'investigation completed', 'completed', 'University Authorities', '2025-09-04 19:28:12', '2025-09-04 19:31:37', '2025-09-03 15:53:44', '2025-09-04 19:31:37', NULL),
(0, 41, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-03 15:53:44', '2025-09-03 15:53:44', NULL),
(0, 41, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-03 15:53:44', '2025-09-04 19:28:00', '2025-09-03 15:53:44', '2025-09-04 19:28:00', NULL),
(0, 41, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', '2025-09-03 15:53:44', NULL, '2025-09-03 15:53:44', '2025-09-03 15:53:44', NULL),
(0, 41, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-03 15:53:44', '2025-09-03 15:53:48', '2025-09-03 15:53:44', '2025-09-03 15:53:48', NULL),
(0, 41, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', '2025-09-03 15:53:44', '2025-09-04 19:31:46', '2025-09-03 15:53:44', '2025-09-04 19:31:46', NULL),
(0, 41, 'Action Plan', 'investigation completed', 'completed', 'University Authorities', '2025-09-03 15:53:44', '2025-09-04 19:31:37', '2025-09-03 15:53:44', '2025-09-04 19:31:37', NULL),
(0, 41, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-03 15:53:44', NULL, '2025-09-03 15:53:44', '2025-09-03 15:53:44', NULL),
(0, 42, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-04 03:44:37', '2025-09-04 03:44:37', '2025-09-04 03:44:37', '2025-09-04 03:44:37', NULL),
(0, 42, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', NULL, '2025-09-04 03:55:43', '2025-09-04 03:44:37', '2025-09-04 03:55:43', NULL),
(0, 42, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-04 03:44:41', '2025-09-04 03:44:37', '2025-09-04 03:44:41', NULL),
(0, 42, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL, NULL, '2025-09-04 03:44:37', '2025-09-04 03:44:37', NULL),
(0, 42, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-04 03:44:37', '2025-09-04 03:44:37', NULL),
(0, 42, 'Resolution', 'Case resolved and closed', 'completed', 'System', NULL, '2025-09-04 04:09:39', '2025-09-04 03:44:37', '2025-09-04 04:09:39', NULL),
(0, 42, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-04 03:44:37', '2025-09-04 03:44:37', '2025-09-04 03:44:37', '2025-09-04 03:44:37', NULL),
(0, 42, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', '2025-09-04 03:44:37', '2025-09-04 03:55:43', '2025-09-04 03:44:37', '2025-09-04 03:55:43', NULL),
(0, 42, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-04 03:44:37', '2025-09-04 03:44:41', '2025-09-04 03:44:37', '2025-09-04 03:44:41', NULL),
(0, 42, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', '2025-09-04 03:44:37', NULL, '2025-09-04 03:44:37', '2025-09-04 03:44:37', NULL),
(0, 42, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-04 03:44:37', NULL, '2025-09-04 03:44:37', '2025-09-04 03:44:37', NULL),
(0, 42, 'Resolution', 'Case resolved and closed', 'completed', 'System', '2025-09-04 03:44:37', '2025-09-04 04:09:39', '2025-09-04 03:44:37', '2025-09-04 04:09:39', NULL),
(0, 43, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-04 05:07:24', '2025-09-04 05:07:24', '2025-09-04 05:07:24', '2025-09-04 05:07:24', NULL),
(0, 43, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', NULL, '2025-09-04 09:17:05', '2025-09-04 05:07:24', '2025-09-04 09:17:05', NULL),
(0, 43, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-04 05:07:29', '2025-09-04 05:07:24', '2025-09-04 05:07:29', NULL),
(0, 43, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', NULL, '2025-09-04 09:26:04', '2025-09-04 05:07:24', '2025-09-04 09:26:04', NULL),
(0, 43, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-04 05:07:24', '2025-09-04 05:07:24', NULL),
(0, 43, 'Resolution', 'Case resolved and closed', 'completed', 'System', NULL, '2025-09-04 09:25:33', '2025-09-04 05:07:24', '2025-09-04 09:25:33', NULL),
(0, 43, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-04 05:07:24', '2025-09-04 05:07:24', '2025-09-04 05:07:24', '2025-09-04 05:07:24', NULL),
(0, 43, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', '2025-09-04 05:07:24', '2025-09-04 09:17:05', '2025-09-04 05:07:24', '2025-09-04 09:17:05', NULL),
(0, 43, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-04 05:07:24', '2025-09-04 05:07:29', '2025-09-04 05:07:24', '2025-09-04 05:07:29', NULL),
(0, 43, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', '2025-09-04 05:07:24', '2025-09-04 09:26:04', '2025-09-04 05:07:24', '2025-09-04 09:26:04', NULL),
(0, 43, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-04 05:07:24', NULL, '2025-09-04 05:07:24', '2025-09-04 05:07:24', NULL),
(0, 43, 'Resolution', 'Case resolved and closed', 'completed', 'System', '2025-09-04 05:07:24', '2025-09-04 09:25:33', '2025-09-04 05:07:24', '2025-09-04 09:25:33', NULL),
(0, 44, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-04 10:58:40', '2025-09-04 10:58:40', '2025-09-04 10:58:40', '2025-09-04 10:58:40', NULL),
(0, 44, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', NULL, '2025-09-04 11:04:22', '2025-09-04 10:58:40', '2025-09-04 11:04:22', NULL),
(0, 44, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', NULL, '2025-09-04 10:58:52', '2025-09-04 10:58:40', '2025-09-04 10:58:52', NULL),
(0, 44, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', NULL, '2025-09-04 11:16:36', '2025-09-04 10:58:40', '2025-09-04 11:16:36', NULL),
(0, 44, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL, NULL, '2025-09-04 10:58:40', '2025-09-04 10:58:40', NULL),
(0, 44, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL, NULL, '2025-09-04 10:58:40', '2025-09-04 10:58:40', NULL),
(0, 44, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', '2025-09-04 10:58:40', '2025-09-04 10:58:40', '2025-09-04 10:58:40', '2025-09-04 10:58:40', NULL),
(0, 44, 'Initial Review', 'Report reviewed by admin team', 'completed', 'Admin Team', '2025-09-04 10:58:40', '2025-09-04 11:04:22', '2025-09-04 10:58:40', '2025-09-04 11:04:22', NULL),
(0, 44, 'University Notification', 'Report forwarded to university authorities', 'completed', 'University Authorities', '2025-09-04 10:58:40', '2025-09-04 10:58:52', '2025-09-04 10:58:40', '2025-09-04 10:58:52', NULL),
(0, 44, 'Investigation', 'Investigation initiated by university', 'completed', 'Investigation Team', '2025-09-04 10:58:40', '2025-09-04 11:16:36', '2025-09-04 10:58:40', '2025-09-04 11:16:36', NULL),
(0, 44, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', '2025-09-04 10:58:40', NULL, '2025-09-04 10:58:40', '2025-09-04 10:58:40', NULL),
(0, 44, 'Resolution', 'Case resolved and closed', 'pending', 'System', '2025-09-04 10:58:40', NULL, '2025-09-04 10:58:40', '2025-09-04 10:58:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `report_code` varchar(12) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `incident_datetime` datetime NOT NULL,
  `details` text NOT NULL,
  `status` enum('Submitted','Under Review','Action Initiated','Resolved','Rejected') NOT NULL DEFAULT 'Submitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `university_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `reporter_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `report_code`, `incident_type`, `department`, `incident_datetime`, `details`, `status`, `created_at`, `university_id`, `location`, `reporter_email`) VALUES
(1, 'AR22694783', 'bullying', 'technology', '2025-08-15 15:08:00', 'zxc adfc adfkbca asfj icasj cas jc', 'Resolved', '2025-08-22 09:38:36', 1, 'hvbjnk', '2022t01364@stu.cmb.ac.lk'),
(2, 'AR49967980', 'verbal_harassment', 'engineering', '2025-08-19 19:25:00', 'asdj kasj das dkjas jasko djas kjdasdzx cp', 'Under Review', '2025-08-22 09:56:08', 1, 'hostel', '2022t01364@stu.cmb.ac.lk'),
(3, 'AR87470504', 'verbal_harassment', 'management', '2025-08-22 09:28:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem I', 'Submitted', '2025-08-30 22:59:07', 9, '205, hostel room, girls', '12312480Ts@rsu.ac.lk'),
(4, 'AR97650828', 'bullying', 'engineering', '2025-08-06 06:04:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem I', 'Under Review', '2025-08-31 00:35:16', 3, 'study area', '231234@sjp.ac.lk'),
(5, 'AR67105273', 'physical_harassment', 'engineering', '2025-08-14 02:20:00', 'fasckgzvb dsihfc uasd zxfiuhsdi vwsdfhcjh sdxhf iajszofcj aszfc oiufxcja szxfciazgxfc azxfc zxifhbc szxhfcsojz xcwsdgzxfcigazjxfhcaispj qahizxcsdbzv', 'Submitted', '2025-08-31 05:51:00', 1, 'hasvg', '2022t01364@stu.cmb.ac.lk'),
(6, 'AR17563033', 'cyber_ragging', 'management', '2025-08-18 11:30:00', 'ajzhcx uisadf chjds fichashf uas cajsh chgaodufcau dvhsidasiufciudfxcu dsaxcv sdfuichsauidfic wisdfiucasdyufc asd fsdifhuc iasdvwsd fusa diuasdsaid uv sdiuhxv isud', 'Submitted', '2025-08-31 06:01:39', 3, NULL, 'dax@gmail.com'),
(7, 'AR42284067', 'verbal_harassment', 'management', '2025-08-06 12:22:00', 'sanm asjd  asjd ajkdqia xd aijxd dja dj aoj  sjoaf  sdfaj sojfda sdfokasf sjdf ajsdfjsdif assanm asjd  asjd ajkdqia xd aijxd dja dj aoj  sjoaf  sdfaj sojfda sdfokasf sjdf ajsdfjsdif assanm asjd  asjd ajkdqia xd aijxd dja dj aoj  sjoaf  sdfaj sojfda sdfokasf sjdf ajsdfjsdif as', 'Submitted', '2025-08-31 06:54:04', 10, 'fd', 'tr567976@sabaragamuwa.ac.lk'),
(8, 'AR25459773', 'bullying', 'engineering', '2025-08-26 15:37:00', 'esdrftgyhuijkl dcfvghbnj rdfghbnj errdfvgh', 'Submitted', '2025-08-31 07:09:00', 2, 'at Hostel room', 'samu@pdn.ac.lk'),
(9, 'AR91209267', 'bullying', 'engineering', '2025-08-20 13:52:00', 'ygrsa dfuwahds uig asygdqhas djashgd qasd jaos dkjas hc asbfqash d asduashfc', 'Submitted', '2025-08-31 08:25:02', 15, 'dfhgh', '68q54bas@uvpa.ac.lk'),
(10, 'AR27895431', 'verbal_harassment', 'medicine', '2025-07-22 14:09:00', 'asjj sajd akjs dj asoj ocjsazxjcas ozxcjkaohxz casojhxzc qxcqx axvax avx azx   axoahdx jkx jzx zoxij iax', 'Submitted', '2025-08-31 08:40:06', 5, 'ds', 'saay879@uom.lk'),
(11, 'AR87925863', 'physical_harassment', 'arts', '2025-08-11 14:15:00', 'asjc hdf jkcqasj pkas cpaskfcoj', 'Submitted', '2025-08-31 08:46:36', 6, 'hasvg', '7989809@jfn.ac.lk'),
(12, 'AR82403883', 'bullying', 'science', '2025-08-06 14:26:00', 'sfzngv snfxgncvos xgcvdzpcgjv zc gvpadzcov anzdpxfv', 'Submitted', '2025-08-31 08:58:11', 3, 'asdsf', 'afsdgw4@sjp.ac.lk'),
(13, 'AR53289453', 'cyber_ragging', 'science', '2025-08-19 14:34:00', 'afdh eha dfh hdgr ea g daj dghsag ihdg asriwy9r sasga asgduvahbv zbvxc ajashdgg', 'Submitted', '2025-08-31 09:05:30', 4, 'zhall', '234ebn@kln.ac.lk'),
(14, 'AR26971808', 'verbal_harassment', 'management', '2025-08-05 14:38:00', 'sdjf owijas doasas fcjaso zxfcjqsakjz xc', 'Submitted', '2025-08-31 09:09:14', 5, 'sdzml', 'jszxc9@uom.lk'),
(19, 'AR41676171', 'verbal_harassment', 'engineering', '2025-08-29 15:14:00', 'I didn’t realize how much words could hurt until I became the target of them. It wasn’t physical, but every insult, every sarcastic comment, every degrading remark felt like a wound that no one else could see. At first, I told myself, “It’s just words. Ignore it.” But over time, those words started echoing in my head long after they were spoken.\r\n\r\nIt wasn’t just the obvious insults — sometimes it was the constant criticism, the mocking of my abilities, or the way they twisted my mistakes into proof that I was worthless. Other times it was shouting, name-calling, or being belittled in front of others. The worst part was how it chipped away at my confidence. Slowly, I began to believe the things they said.', 'Submitted', '2025-08-31 09:45:10', 10, 'Hostel room 24', 'erer@gmail.com'),
(20, 'AR59242235', 'verbal_harassment', 'engineering', '2025-08-31 17:03:00', 'lorem loerecfcd cc ddscdcvdlopre3r  kloterfv', 'Resolved', '2025-08-31 11:40:12', 2, 'Kandy', 'samundasilva23@gmail.com'),
(21, 'AR60806777', 'physical_harassment', 'medicine', '2025-08-25 17:36:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially', 'Submitted', '2025-08-31 12:08:30', 2, 'at university library', 'samundasilva23@gmail.com'),
(22, 'AR59885128', 'cyber_ragging', 'science', '2025-08-19 19:14:00', 'fjivbfvbn edcfgvhbjn m edrtfvghbjn medrtfvghb jnm', 'Action Initiated', '2025-08-31 13:45:53', 2, 'fgbgbgfbn', 'samundasilva23@gmail.com'),
(23, 'AR48199254', 'cyber_ragging', 'management', '2025-08-19 19:34:00', '1ibjgvrf nmc49owirjkgbfs nvm04wgo9rsjibkfn mifj', 'Submitted', '2025-08-31 14:04:59', 19, 'Kandy', 'samundasilva23@gmail.com'),
(24, 'AR25959237', 'cyber_ragging', 'management', '2025-09-01 16:47:00', 'jjjjjjjjjjjj hhhhhhhhhhhhhhhhh tttttttttttttttttttt', 'Submitted', '2025-09-02 11:19:40', 1, 'hostel', 'nethmianushka250@gmail.com'),
(25, 'AR64320759', 'physical_harassment', 'science', '2025-09-01 22:17:00', 'ewsfdrcgtvhbjnkml azsxdfc gvhnjkmits been 24 hours no responf tom surperman', 'Submitted', '2025-09-02 16:49:35', 5, 'lankawe', 'trevanking2009@gmail.com'),
(26, 'AR22530504', 'cyber_ragging', 'engineering', '2025-09-01 23:42:00', 'gggggggggggggggggggggg vvvvvvvvvvvvvvvvvvv ttttttttttt', 'Resolved', '2025-09-02 17:13:51', 2, 'hostel', 'nethmianushka250@gmail.com'),
(27, 'AR33526399', 'verbal_harassment', 'technology', '2025-09-02 10:30:00', '\"I’m a first-year student. Most nights, seniors come to the hostel rooms. They force us to do things like sing loudly, dance in embarrassing ways, or perform physical exercises late at night. Sometimes they even make us say things that humiliate ourselves. At first, they say it is ‘just for fun,’ but when we refuse, they threaten us with worse treatment or social isolation.\r\n\r\nBecause of this, we feel constant fear in the hostel. We can’t study peacefully, we lose sleep, and some of my batchmates even avoid meals just to escape these encounters. Ragging is not just a harmless joke—it affects our dignity, mental health, and our right to live in a safe environment. We came here to learn and grow, not to be broken down.', 'Submitted', '2025-09-03 08:06:05', 6, 'Hostel rooms', 'dilshan006@gmail.com'),
(28, 'AR95954006', 'physical_harassment', 'arts', '2025-08-31 09:32:00', 'Sometimes it goes beyond mental pressure. Seniors have forced us to do continuous push-ups until we collapse, or to stand in the corridor for hours late at night without food or water. A few of us were slapped when we refused to follow instructions. They even make us kneel on the hard floor as ‘punishment,’ saying it is part of discipline.\r\n\r\nThese acts may look small to outsiders, but to us they are humiliating and physically exhausting. Some students develop body pain, injuries, and even fevers because of this treatment. The fear of being physically harassed has made many of us afraid to stay in our own hostel rooms at night.\r\n\r\nRagging in this form is not just a prank—it is physical abuse. It damages our health, confidence, and sense of safety. No student should ever be made to suffer like this in the name of tradition', 'Submitted', '2025-09-03 08:09:20', 8, 'Hostel rooms', 'samith@gmail.com'),
(29, 'AR12552319', 'bullying', 'management', '2025-09-03 11:39:00', 'Seniors often surround us when we go to eat. They take away our food trays, force us to pay for their meals, or make us sit separately from everyone else. Sometimes they mock the way we eat, spill drinks on purpose, or shout at us in front of the crowd just to humiliate us.\r\n\r\nBecause of this, many juniors avoid the canteen altogether, skipping meals or sneaking food back to the hostel. What should be a normal and friendly space for students turns into a place of fear and embarrassment.\r\n3rd year Seniors who are involved with this are hasitha perera, tharusha Fernando and \r\nTharuka kumara', 'Submitted', '2025-09-03 08:15:32', 11, 'Campus canteen', 'wow@gmail.com'),
(30, 'AR90641894', 'physical_harassment', 'management', '2025-08-05 03:58:00', 'We were studying in study area and some seniors came and told us to leave. Then we asked why then they told that area was prohibitted for first year and beat us. after that at hostel they came again with some other seniors and they point us and told them. then they removed our clothes and beat us with sticks. \r\n\r\nthier names were Bhanuka from Accounting, Kapila business management, and thir room mates.', 'Submitted', '2025-09-03 08:34:23', 5, 'Study area', 'johnwick@pdn.ac.lk'),
(31, 'AR30725035', 'cyber_ragging', 'science', '2025-09-01 04:09:00', 'Our senior batch boys are video calling continuously for girls in our batch and forcing them to answer. \r\n\r\npeople doing this - Thilina - 0786523120\r\nRuchira - 0778513024\r\nDilshan - 0745231604\r\n\r\nThere are more than 20 girls in our batch has victim for this incident.', 'Submitted', '2025-09-03 09:59:03', 16, NULL, 'dasun.anupiya@gmail.com'),
(32, 'AR27306396', 'bullying', 'arts', '2025-08-19 15:33:00', 'On 19th August 2025, at around 03:33 PM, I was walking through the North Block hallway near Lecture Hall 2 when I was stopped by three senior students: Ravi Perera, Sandun Silva, and Dilshan Fernando. They blocked my way and started mocking me about my appearance and the way I was carrying my books.\r\n\r\nThey ordered me to sing loudly in the hallway while other students were passing by. When I hesitated, Ravi pushed me lightly against the wall and Sandun snatched my notebook, refusing to give it back. Dilshan kept calling me insulting names in front of everyone, which was very humiliating.\r\n\r\nThe incident attracted the attention of several other students, and I felt extremely uncomfortable and intimidated. I managed to leave only after another lecturer walked into the corridor. This was a clear case of ragging and bullying, which made me feel unsafe in the university premises.\r\n\r\nNames of those involved:\r\n\r\nRavi Perera (Senior, 3rd Year)\r\n\r\nSandun Silva (Senior, 3rd Year)', 'Submitted', '2025-09-03 10:06:16', 13, 'Hallway', 'aaa@gmail.com'),
(33, 'AR42864640', 'physical_harassment', 'arts', '2025-08-08 16:46:00', 'I was physically harassed by three seniors: Nimal Jayawardena, Roshan Silva, and Kavindu Perera. They blocked my path and demanded I carry their bags. When I refused, Roshan shoved me against the wall, and Nimal forcefully pulled my tie, choking me for a few seconds. Kavindu slapped my shoulder hard and threatened that worse would happen if I reported them. Other students nearby witnessed the incident, but I was too afraid to speak up. The behavior was degrading, intimidating, and caused me both physical pain and mental distress. This was clearly an act of ragging and physical harassment that made me feel unsafe on campus.', 'Submitted', '2025-09-03 10:18:07', 8, 'South Block hallway near Room 104', '2009678@seu.ac.lk'),
(34, 'AR60443563', 'verbal_harassment', 'medicine', '2025-09-02 15:48:00', 'sdfcxzxc fzxckhabfdafzcx', 'Submitted', '2025-09-03 10:19:36', 3, NULL, 'abc@gmail.com'),
(35, 'AR87897078', 'physical_harassment', 'technology', '2025-09-01 16:53:00', 'On 2nd September 2025, at around 10:30 AM, while passing the South Block hallway near Room 104, I was physically harassed by three seniors: Nimal Jayawardena, Roshan Silva, and Kavindu Perera. They blocked my path and demanded I carry their bags. When I refused, Roshan shoved me against the wall, and Nimal forcefully pulled my tie, choking me for a few seconds. Kavindu slapped my shoulder hard and threatened that worse would happen if I reported them. Other students nearby witnessed the incident, but I was too afraid to speak up. The behavior was degrading, intimidating, and caused me both physical pain and mental distress. This was clearly an act of ragging and physical harassment that made me feel unsafe on campus.', 'Submitted', '2025-09-03 10:24:37', 13, 'hi', '7788@example.com'),
(36, 'AR74976726', 'verbal_harassment', 'engineering', '2025-08-30 10:00:00', 'While walking through the main hallway near the lecture rooms, I was confronted by three senior students: Ruwan Senanayake, Thisara Silva, and Chaminda Perera. They surrounded me and began mocking my accent and the way I dressed. Ruwan shouted insults loudly so that others passing by could hear, while Thisara repeatedly called me offensive names. Chaminda made humiliating remarks about my background and threatened that I would not be accepted on campus unless I obeyed their demands. The continuous verbal abuse was embarrassing, intimidating, and deeply distressing, making me feel isolated and unsafe within the university environment.', 'Submitted', '2025-09-03 10:34:11', 4, 'main hallway near the lecture rooms', 'as@gmail.com'),
(37, 'AR80119087', 'cyber_ragging', 'arts', '2025-09-01 16:05:00', 'I have been experiencing cyber ragging through a private group created on social media by a few senior students: Kasun Fernando, Pradeep Silva, and Amila Perera. They uploaded my photo without consent, added insulting captions, and encouraged others to post mocking comments. Kasun sent me repeated harassing messages, while Pradeep threatened to spread edited images if I refused to follow their instructions. Amila publicly tagged me in posts ridiculing my appearance, which quickly spread among other students. This online harassment has caused humiliation, anxiety, and fear, as my reputation is being attacked digitally and the abuse continues beyond the university premises.', 'Submitted', '2025-09-03 10:36:30', 12, NULL, 'aaa@gmail.com'),
(38, 'AR22746514', 'physical_harassment', 'science', '2025-09-01 19:17:00', 'Inside the girls’ hostel, I was physically harassed by a group of senior students: Tharushi Perera, Ishara Silva, and Nadeesha Fernando. They entered my room without permission and demanded that I do their laundry. When I refused, Tharushi forcefully pulled my arm and pushed me onto the bed. Ishara twisted my wrist while laughing, and Nadeesha slapped me on the back and threatened that if I reported them, I would not be allowed to stay peacefully in the hostel. The incident left me shaken, embarrassed, and afraid to use shared facilities. Their aggressive behavior created an unsafe environment in what should be a secure place for female students.', 'Submitted', '2025-09-03 10:48:58', 14, 'Hostel room 404', '34@gmail.com'),
(39, 'AR30155024', 'bullying', 'engineering', '2025-09-03 11:13:00', 'I was bullied by a group of seniors in the common study area. Sahan Perera, Dinuka Silva, and Malith Fernando surrounded me while I was reading and began mocking my voice and the way I answered questions in class. Sahan snatched my notebook and tore a few pages, while Dinuka kept making loud jokes about me so that others would laugh. Malith threatened that I would be excluded from group activities if I didn’t follow their orders. The constant humiliation, intimidation, and destruction of my belongings made me feel powerless, anxious, and unsafe within the university premises.', 'Submitted', '2025-09-03 14:51:50', 14, 'dfhgh', '123978@vau.ac.lk'),
(40, 'AR74825501', 'physical_harassment', 'science', '2025-09-03 10:22:00', 'ghvb woijfjw fj wjejf wijadijc ajspjk asojS', 'Submitted', '2025-09-03 14:53:27', 14, 'm.', 'admin@antiragging.xyz'),
(41, 'AR51065728', 'bullying', 'technology', '2025-09-02 23:23:00', 'At the university cafeteria, I was bullied by three seniors: Ravindu Silva, Chathura Perera, and Lakshan Fernando. While I was having lunch, they came to my table and began mocking the food I was eating, calling me names in front of other students. Ravindu grabbed my plate and threw part of the food onto the floor. Chathura forced me to stand up and sing loudly in front of everyone, while Lakshan blocked me from leaving and warned that I would face “worse treatment” if I refused. The public humiliation and intimidation caused me embarrassment, fear, and distress, making the cafeteria feel unsafe.', 'Action Initiated', '2025-09-03 15:53:44', 28, 'Cafeteria', 'KD-BSCSD-19-28@student.icbtcampus.edu.lk'),
(42, 'AR79003923', 'physical_harassment', 'technology', '2025-09-03 09:09:00', 'qwertyuiop xcvbnm, dfghjkl sdfghjkl sdfghjkl', 'Resolved', '2025-09-04 03:44:37', 28, 'cafeteria', 'KD-BSCSD-19-28@student.icbtcampus.edu.lk'),
(43, 'AR71271936', 'physical_harassment', 'management', '2025-09-01 10:35:00', 'asdfxcgnvnb adsfvghbnjm awsedfvgb', 'Action Initiated', '2025-09-04 05:07:24', 28, 'study area', 'KD-BSCSD-19-29@student.icbtcampus.edu.lk'),
(44, 'AR70882292', 'bullying', 'science', '2025-09-03 16:27:00', 'qwertyuio fmnnb fjbn bhjgj', 'Action Initiated', '2025-09-04 10:58:40', 28, 'cafeteria', 'KD-BSCSD-19-28@student.icbtcampus.edu.lk');

--
-- Triggers `reports`
--
DELIMITER $$
CREATE TRIGGER `after_report_insert` AFTER INSERT ON `reports` FOR EACH ROW BEGIN
    -- Insert initial status history
    INSERT INTO status_history (report_id, old_status, new_status, changed_by)
    VALUES (NEW.id, NULL, NEW.status, 'System');
    
    -- Insert default process timeline
    INSERT INTO process_timeline (report_id, step_name, step_description, status, assigned_to, started_at) VALUES
    (NEW.id, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System', NOW()),
    (NEW.id, 'Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team', NULL),
    (NEW.id, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities', NULL),
    (NEW.id, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team', NULL),
    (NEW.id, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities', NULL),
    (NEW.id, 'Resolution', 'Case resolved and closed', 'pending', 'System', NULL);
    
    -- Mark first step as completed
    UPDATE process_timeline 
    SET status = 'completed', completed_at = NOW()
    WHERE report_id = NEW.id AND step_name = 'Report Submission';
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `report_summary`
--

CREATE TABLE `report_summary` (
  `id` int(11) DEFAULT NULL,
  `report_code` varchar(12) DEFAULT NULL,
  `incident_type` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `incident_datetime` datetime DEFAULT NULL,
  `status` enum('Submitted','Under Review','Action Initiated','Resolved','Rejected') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `reporter_email` varchar(255) DEFAULT NULL,
  `uni_name` varchar(255) DEFAULT NULL,
  `uni_type` enum('government','private') DEFAULT NULL,
  `view_count` bigint(21) DEFAULT NULL,
  `process_steps` bigint(21) DEFAULT NULL,
  `status_changes` bigint(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_views`
--

CREATE TABLE `report_views` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `viewer_type` enum('admin','university_authority','system','investigator') NOT NULL,
  `viewer_name` varchar(255) DEFAULT NULL,
  `viewer_email` varchar(255) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_taken` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `session_duration` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_views`
--

INSERT INTO `report_views` (`id`, `report_id`, `viewer_type`, `viewer_name`, `viewer_email`, `viewed_at`, `action_taken`, `notes`, `session_duration`) VALUES
(1, 1, '', 'Reporter', NULL, '2025-08-31 08:27:07', 'Viewed status', NULL, NULL),
(2, 1, '', 'Reporter', NULL, '2025-08-31 08:29:27', 'Viewed status', NULL, NULL),
(3, 0, '', 'Reporter', NULL, '2025-08-31 09:09:37', 'Viewed status', NULL, NULL),
(4, 19, '', 'Reporter', NULL, '2025-08-31 09:45:27', 'Viewed status', NULL, NULL),
(5, 20, '', 'Reporter', NULL, '2025-08-31 11:41:48', 'Viewed status', NULL, NULL),
(6, 20, '', 'Reporter', NULL, '2025-08-31 11:56:54', 'Viewed status', NULL, NULL),
(7, 20, '', 'Reporter', NULL, '2025-08-31 12:03:49', 'Viewed status', NULL, NULL),
(8, 21, '', 'Reporter', NULL, '2025-08-31 12:09:19', 'Viewed status', NULL, NULL),
(9, 20, '', 'Reporter', NULL, '2025-08-31 13:38:15', 'Viewed status', NULL, NULL),
(10, 20, '', 'Reporter', NULL, '2025-08-31 13:40:34', 'Viewed status', NULL, NULL),
(11, 20, '', 'Reporter', NULL, '2025-08-31 13:40:40', 'Viewed status', NULL, NULL),
(12, 20, '', 'Reporter', NULL, '2025-08-31 13:43:05', 'Viewed status', NULL, NULL),
(13, 23, '', 'Reporter', NULL, '2025-08-31 14:05:31', 'Viewed status', NULL, NULL),
(14, 1, '', 'Reporter', NULL, '2025-09-01 18:19:48', 'Viewed status', NULL, NULL),
(15, 24, '', 'Reporter', NULL, '2025-09-02 11:19:57', 'Viewed status', NULL, NULL),
(16, 24, '', 'Reporter', NULL, '2025-09-02 11:24:42', 'Viewed status', NULL, NULL),
(17, 25, '', 'Reporter', NULL, '2025-09-02 16:50:41', 'Viewed status', NULL, NULL),
(18, 25, '', 'Reporter', NULL, '2025-09-02 16:54:20', 'Viewed status', NULL, NULL),
(19, 25, '', 'Reporter', NULL, '2025-09-02 16:55:06', 'Viewed status', NULL, NULL),
(20, 24, '', 'Reporter', NULL, '2025-09-02 17:02:23', 'Viewed status', NULL, NULL),
(21, 26, '', 'Reporter', NULL, '2025-09-02 17:14:49', 'Viewed status', NULL, NULL),
(22, 24, '', 'Reporter', NULL, '2025-09-02 17:22:53', 'Viewed status', NULL, NULL),
(23, 26, '', 'Reporter', NULL, '2025-09-02 17:23:33', 'Viewed status', NULL, NULL),
(24, 1, '', 'Reporter', NULL, '2025-09-03 08:38:18', 'Viewed status', NULL, NULL),
(25, 20, '', 'Reporter', NULL, '2025-09-03 09:10:27', 'Viewed status', NULL, NULL),
(26, 26, '', 'Reporter', NULL, '2025-09-03 10:38:58', 'Viewed status', NULL, NULL),
(27, 26, '', 'Reporter', NULL, '2025-09-03 10:39:43', 'Viewed status', NULL, NULL),
(28, 26, '', 'Reporter', NULL, '2025-09-03 10:40:25', 'Viewed status', NULL, NULL),
(29, 26, '', 'Reporter', NULL, '2025-09-03 10:41:46', 'Viewed status', NULL, NULL),
(30, 26, '', 'Reporter', NULL, '2025-09-03 10:42:58', 'Viewed status', NULL, NULL),
(31, 41, '', 'Reporter', NULL, '2025-09-03 15:54:18', 'Viewed status', NULL, NULL),
(32, 42, '', 'Reporter', NULL, '2025-09-04 03:46:00', 'Viewed status', NULL, NULL),
(33, 42, '', 'Reporter', NULL, '2025-09-04 04:05:18', 'Viewed status', NULL, NULL),
(34, 42, '', 'Reporter', NULL, '2025-09-04 04:17:28', 'Viewed status', NULL, NULL),
(35, 42, '', 'Reporter', NULL, '2025-09-04 04:38:50', 'Viewed status', NULL, NULL),
(36, 41, '', 'Reporter', NULL, '2025-09-04 04:50:46', 'Viewed status', NULL, NULL),
(37, 43, '', 'Reporter', NULL, '2025-09-04 05:08:05', 'Viewed status', NULL, NULL),
(38, 43, '', 'Reporter', NULL, '2025-09-04 09:17:20', 'Viewed status', NULL, NULL),
(39, 43, '', 'Reporter', NULL, '2025-09-04 09:20:34', 'Viewed status', NULL, NULL),
(40, 43, '', 'Reporter', NULL, '2025-09-04 09:26:16', 'Viewed status', NULL, NULL),
(41, 44, '', 'Reporter', NULL, '2025-09-04 10:59:41', 'Viewed status', NULL, NULL),
(42, 44, '', 'Reporter', NULL, '2025-09-04 11:05:33', 'Viewed status', NULL, NULL),
(43, 44, '', 'Reporter', NULL, '2025-09-04 11:14:10', 'Viewed status', NULL, NULL),
(44, 44, '', 'Reporter', NULL, '2025-09-04 11:16:48', 'Viewed status', NULL, NULL),
(45, 22, '', 'Reporter', NULL, '2025-09-04 19:22:54', 'Viewed status', NULL, NULL),
(46, 22, '', 'Reporter', NULL, '2025-09-04 19:24:19', 'Viewed status', NULL, NULL),
(47, 41, '', 'Reporter', NULL, '2025-09-04 19:28:28', 'Viewed status', NULL, NULL),
(48, 41, '', 'Reporter', NULL, '2025-09-04 19:30:14', 'Viewed status', NULL, NULL),
(49, 41, '', 'Reporter', NULL, '2025-09-04 19:31:55', 'Viewed status', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `status_history`
--

CREATE TABLE `status_history` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_by` varchar(255) DEFAULT 'System',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_history`
--

INSERT INTO `status_history` (`id`, `report_id`, `old_status`, `new_status`, `changed_at`, `changed_by`, `notes`) VALUES
(1, 1, NULL, 'Submitted', '2025-08-22 09:38:36', 'System', NULL),
(2, 1, '', 'Submitted', '2025-08-22 09:38:36', 'System', NULL),
(3, 2, NULL, 'Submitted', '2025-08-22 09:56:08', 'System', NULL),
(4, 2, '', 'Submitted', '2025-08-22 09:56:08', 'System', NULL),
(5, 2, 'Submitted', 'Under Review', '2025-08-25 08:00:27', 'Dr. John Smith (dean@university.edu)', ''),
(6, 1, 'Submitted', 'Resolved', '2025-08-25 08:19:23', 'Dr. John Smith (dean@university.edu)', ''),
(7, 3, NULL, 'Submitted', '2025-08-30 22:59:07', 'System', NULL),
(8, 3, '', 'Submitted', '2025-08-30 22:59:07', 'System', NULL),
(9, 4, NULL, 'Submitted', '2025-08-31 00:35:16', 'System', NULL),
(10, 4, '', 'Submitted', '2025-08-31 00:35:16', 'System', NULL),
(11, 4, 'Submitted', 'Under Review', '2025-08-31 00:37:30', 'Mr. Iraj Weerarathne (iraj@ugc.ac.lk)', 'ugc will take action against these dinosours'),
(12, 4, 'Under Review', 'Under Review', '2025-08-31 00:39:45', 'Mr. Iraj Weerarathne (iraj@ugc.ac.lk)', 'bla bla bla'),
(13, 0, NULL, 'Submitted', '2025-08-31 05:51:00', 'System', NULL),
(14, 0, '', 'Submitted', '2025-08-31 05:51:00', 'System', NULL),
(15, 0, NULL, 'Submitted', '2025-08-31 06:01:39', 'System', NULL),
(16, 0, '', 'Submitted', '2025-08-31 06:01:39', 'System', NULL),
(17, 0, NULL, 'Submitted', '2025-08-31 06:54:04', 'System', NULL),
(18, 0, '', 'Submitted', '2025-08-31 06:54:04', 'System', NULL),
(19, 0, NULL, 'Submitted', '2025-08-31 07:09:00', 'System', NULL),
(20, 0, '', 'Submitted', '2025-08-31 07:09:00', 'System', NULL),
(21, 0, NULL, 'Submitted', '2025-08-31 08:25:02', 'System', NULL),
(22, 0, '', 'Submitted', '2025-08-31 08:25:02', 'System', NULL),
(23, 0, NULL, 'Submitted', '2025-08-31 08:40:06', 'System', NULL),
(24, 0, '', 'Submitted', '2025-08-31 08:40:06', 'System', NULL),
(25, 0, NULL, 'Submitted', '2025-08-31 08:46:36', 'System', NULL),
(26, 0, '', 'Submitted', '2025-08-31 08:46:36', 'System', NULL),
(27, 0, NULL, 'Submitted', '2025-08-31 08:58:11', 'System', NULL),
(28, 0, '', 'Submitted', '2025-08-31 08:58:11', 'System', NULL),
(29, 0, NULL, 'Submitted', '2025-08-31 09:05:30', 'System', NULL),
(30, 0, '', 'Submitted', '2025-08-31 09:05:30', 'System', NULL),
(31, 0, NULL, 'Submitted', '2025-08-31 09:09:14', 'System', NULL),
(32, 0, '', 'Submitted', '2025-08-31 09:09:14', 'System', NULL),
(33, 19, NULL, 'Submitted', '2025-08-31 09:45:10', 'System', NULL),
(34, 19, '', 'Submitted', '2025-08-31 09:45:10', 'System', NULL),
(35, 20, NULL, 'Submitted', '2025-08-31 11:40:12', 'System', NULL),
(36, 20, '', 'Submitted', '2025-08-31 11:40:12', 'System', NULL),
(37, 20, 'Submitted', 'Under Review', '2025-08-31 11:54:10', 'SAMUNDA LAKSHITHA DE SILVA (KD-BSCSD-19-28@student.icbtcampus.edu.lk)', 'we will get back to you,  Thanks'),
(38, 20, 'Under Review', 'Action Initiated', '2025-08-31 12:01:40', 'SAMUNDA LAKSHITHA DE SILVA (KD-BSCSD-19-28@student.icbtcampus.edu.lk)', 'Now we had to submit your report with UGC for further escalations. Thank you'),
(39, 21, NULL, 'Submitted', '2025-08-31 12:08:30', 'System', NULL),
(40, 21, '', 'Submitted', '2025-08-31 12:08:30', 'System', NULL),
(41, 20, 'Action Initiated', 'Resolved', '2025-08-31 13:42:53', 'SAMUNDA LAKSHITHA DE SILVA (KD-BSCSD-19-28@student.icbtcampus.edu.lk)', 'Hi This case has been now resolved.'),
(42, 22, NULL, 'Submitted', '2025-08-31 13:45:53', 'System', NULL),
(43, 22, '', 'Submitted', '2025-08-31 13:45:53', 'System', NULL),
(44, 23, NULL, 'Submitted', '2025-08-31 14:04:59', 'System', NULL),
(45, 23, '', 'Submitted', '2025-08-31 14:04:59', 'System', NULL),
(46, 24, NULL, 'Submitted', '2025-09-02 11:19:40', 'System', NULL),
(47, 24, '', 'Submitted', '2025-09-02 11:19:40', 'System', NULL),
(48, 25, NULL, 'Submitted', '2025-09-02 16:49:35', 'System', NULL),
(49, 25, '', 'Submitted', '2025-09-02 16:49:35', 'System', NULL),
(50, 26, NULL, 'Submitted', '2025-09-02 17:13:51', 'System', NULL),
(51, 26, '', 'Submitted', '2025-09-02 17:13:51', 'System', NULL),
(52, 26, 'Submitted', 'Under Review', '2025-09-02 17:18:24', 'Nethmi Suraweeraarachchi (nethmianushka250@gmail.com)', 'huiiiiiiiiiiiiiiiii hiiiiiiiiiiiiiiiiiiiiiiiii'),
(53, 26, 'Under Review', 'Action Initiated', '2025-09-02 17:18:44', 'Nethmi Suraweeraarachchi (nethmianushka250@gmail.com)', 'hiii hii'),
(54, 26, 'Action Initiated', 'Resolved', '2025-09-02 17:18:51', 'Nethmi Suraweeraarachchi (nethmianushka250@gmail.com)', 'hii'),
(55, 27, NULL, 'Submitted', '2025-09-03 08:06:05', 'System', NULL),
(56, 27, '', 'Submitted', '2025-09-03 08:06:05', 'System', NULL),
(57, 28, NULL, 'Submitted', '2025-09-03 08:09:20', 'System', NULL),
(58, 28, '', 'Submitted', '2025-09-03 08:09:20', 'System', NULL),
(59, 29, NULL, 'Submitted', '2025-09-03 08:15:32', 'System', NULL),
(60, 29, '', 'Submitted', '2025-09-03 08:15:32', 'System', NULL),
(61, 30, NULL, 'Submitted', '2025-09-03 08:34:23', 'System', NULL),
(62, 30, '', 'Submitted', '2025-09-03 08:34:23', 'System', NULL),
(63, 31, NULL, 'Submitted', '2025-09-03 09:59:03', 'System', NULL),
(64, 31, '', 'Submitted', '2025-09-03 09:59:03', 'System', NULL),
(65, 32, NULL, 'Submitted', '2025-09-03 10:06:16', 'System', NULL),
(66, 32, '', 'Submitted', '2025-09-03 10:06:16', 'System', NULL),
(79, 33, NULL, 'Submitted', '2025-09-03 10:18:07', 'System', NULL),
(80, 33, '', 'Submitted', '2025-09-03 10:18:07', 'System', NULL),
(81, 34, NULL, 'Submitted', '2025-09-03 10:19:36', 'System', NULL),
(82, 34, '', 'Submitted', '2025-09-03 10:19:36', 'System', NULL),
(83, 35, NULL, 'Submitted', '2025-09-03 10:24:37', 'System', NULL),
(84, 35, '', 'Submitted', '2025-09-03 10:24:37', 'System', NULL),
(85, 36, NULL, 'Submitted', '2025-09-03 10:34:11', 'System', NULL),
(86, 36, '', 'Submitted', '2025-09-03 10:34:11', 'System', NULL),
(87, 37, NULL, 'Submitted', '2025-09-03 10:36:30', 'System', NULL),
(88, 37, '', 'Submitted', '2025-09-03 10:36:30', 'System', NULL),
(89, 26, 'Resolved', 'Under Review', '2025-09-03 10:38:46', 'Prof. John Wick (johnwick@pdn.ac.lk)', ''),
(90, 26, 'Under Review', 'Action Initiated', '2025-09-03 10:39:36', 'Prof. John Wick (johnwick@pdn.ac.lk)', 'identified students.'),
(91, 26, 'Action Initiated', 'Action Initiated', '2025-09-03 10:40:17', 'Prof. John Wick (johnwick@pdn.ac.lk)', 'Marked University Notification as completed.'),
(92, 26, 'Action Initiated', 'Resolved', '2025-09-03 10:43:45', 'Prof. John Wick (johnwick@pdn.ac.lk)', ''),
(93, 38, NULL, 'Submitted', '2025-09-03 10:48:58', 'System', NULL),
(94, 38, '', 'Submitted', '2025-09-03 10:48:58', 'System', NULL),
(95, 39, NULL, 'Submitted', '2025-09-03 14:51:50', 'System', NULL),
(96, 39, '', 'Submitted', '2025-09-03 14:51:50', 'System', NULL),
(97, 40, NULL, 'Submitted', '2025-09-03 14:53:27', 'System', NULL),
(98, 40, '', 'Submitted', '2025-09-03 14:53:27', 'System', NULL),
(99, 41, NULL, 'Submitted', '2025-09-03 15:53:44', 'System', NULL),
(100, 41, '', 'Submitted', '2025-09-03 15:53:44', 'System', NULL),
(101, 42, NULL, 'Submitted', '2025-09-04 03:44:37', 'System', NULL),
(102, 42, '', 'Submitted', '2025-09-04 03:44:37', 'System', NULL),
(103, 42, 'Submitted', 'Under Review', '2025-09-04 03:55:43', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'we will get back to you ASAP'),
(104, 42, 'Under Review', 'Resolved', '2025-09-04 04:09:39', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'asdfghjk dfghj  tyffgfhhbv gghvbnghffcbnb'),
(105, 43, NULL, 'Submitted', '2025-09-04 05:07:24', 'System', NULL),
(106, 43, '', 'Submitted', '2025-09-04 05:07:24', 'System', NULL),
(107, 43, 'Submitted', 'Under Review', '2025-09-04 09:17:05', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'dsbcbdc hcs c hiiiii hiii'),
(108, 43, 'Under Review', 'Action Initiated', '2025-09-04 09:20:15', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'dfghjklcvbnm sfdghh'),
(109, 43, 'Action Initiated', 'Resolved', '2025-09-04 09:25:33', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', ''),
(110, 43, 'Resolved', 'Action Initiated', '2025-09-04 09:26:04', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', ''),
(111, 44, NULL, 'Submitted', '2025-09-04 10:58:40', 'System', NULL),
(112, 44, '', 'Submitted', '2025-09-04 10:58:40', 'System', NULL),
(113, 44, 'Submitted', 'Under Review', '2025-09-04 11:04:22', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'hi there....'),
(114, 44, 'Under Review', 'Action Initiated', '2025-09-04 11:16:36', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'dfghjkl rdtfyghujkl dtfghj'),
(115, 22, 'Submitted', 'Submitted', '2025-09-04 19:09:19', 'Prof. John Wick (johnwick@pdn.ac.lk)', 'Marked University Notification as completed.'),
(116, 22, 'Submitted', 'Submitted', '2025-09-04 19:21:34', 'Prof. John Wick (johnwick@pdn.ac.lk)', 'Updated Action Plan (in progress).'),
(117, 22, 'Submitted', 'Action Initiated', '2025-09-04 19:24:09', 'Prof. John Wick (johnwick@pdn.ac.lk)', ''),
(118, 41, 'Submitted', 'Submitted', '2025-09-04 19:28:00', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'dfgngfng rtjghnmghgnmgh'),
(119, 41, 'Submitted', 'Submitted', '2025-09-04 19:28:12', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'Updated Action Plan (completed).'),
(120, 41, 'Submitted', 'Submitted', '2025-09-04 19:29:57', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'Updated Action Plan (in progress).'),
(121, 41, 'Submitted', 'Submitted', '2025-09-04 19:31:37', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', 'Updated Action Plan (completed).'),
(122, 41, 'Submitted', 'Action Initiated', '2025-09-04 19:31:46', 'SAMUNDA LAKSHITHA DE SILVA (samundasilva23@gmail.com)', '');

-- --------------------------------------------------------

--
-- Table structure for table `university`
--

CREATE TABLE `university` (
  `uni_id` int(11) NOT NULL,
  `uni_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `uni_type` enum('government','private') NOT NULL,
  `uni_email_domain` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `university`
--

INSERT INTO `university` (`uni_id`, `uni_name`, `location`, `uni_type`, `uni_email_domain`) VALUES
(1, 'University of Colombo', 'Colombo', 'government', '@stu.cmb.ac.lk'),
(2, 'University of Peradeniya', 'Peradeniya', 'government', '@pdn.ac.lk'),
(3, 'University of Sri Jayewardenepura', 'Sri Jayawardenepura Kotte', 'government', '@sjp.ac.lk'),
(4, 'University of Kelaniya', 'Kelaniya', 'government', '@kln.ac.lk'),
(5, 'University of Moratuwa', 'Moratuwa', 'government', '@uom.lk'),
(6, 'University of Jaffna', 'Jaffna', 'government', '@jfn.ac.lk'),
(7, 'Eastern University, Sri Lanka', 'Batticaloa', 'government', '@esn.ac.lk'),
(8, 'South Eastern University of Sri Lanka', 'Oluvil', 'government', '@seu.ac.lk'),
(9, 'Rajarata University of Sri Lanka', 'Anuradhapura', 'government', '@rsu.ac.lk'),
(10, 'Sabaragamuwa University of Sri Lanka', 'Belihuloya', 'government', '@sabaragamuwa.ac.lk'),
(11, 'Wayamba University of Sri Lanka', 'Kuliyapitiya', 'government', '@wyb.ac.lk'),
(12, 'Uva Wellassa University', 'Badulla', 'government', '@uwu.ac.lk'),
(13, 'Gampaha Wickramarachchi University of Indigenous Medicine', 'Yakkala', 'government', '@gwuim.ac.lk'),
(14, 'University of Vavuniya', 'Vavuniya', 'government', '@vau.ac.lk'),
(15, 'University of the Visual & Performing Arts', 'Colombo', 'government', '@uvpa.ac.lk'),
(16, 'Open University of Sri Lanka', 'Colombo', 'government', '@ou.ac.lk'),
(17, 'University of Vocational Technology', 'Ratmalana', 'government', '@uvt.ac.lk'),
(18, 'General Sir John Kotelawala Defence University', 'Ratmalana', 'government', '@kdu.ac.lk'),
(19, 'Sri Lanka Institute of Information Technology', 'Malabe', 'private', '@sliit.lk'),
(20, 'NSBM Green University', 'Homagama', 'private', '@nsbm.lk'),
(21, 'South Asia Institute of Technology and Medicine', 'Malabe', 'private', '@saitm.edu.lk'),
(22, 'American University of Sri Lanka', 'Colombo', 'private', '@ausl.lk'),
(23, 'INSEEC University Sri Lanka', 'Colombo', 'private', '@inseec.edu.lk'),
(24, 'Horizon Campus', 'Malabe', 'private', '@horizoncampus.edu.lk'),
(25, 'The British College', 'Colombo', 'private', '@britishcollege.lk'),
(26, 'The Open University of Colombo', 'Colombo', 'private', '@ouc.lk'),
(27, 'University Grant Commission', '7, 20 Ward Pl, Colombo 00700', 'government', 'ugc.ac.lk'),
(28, 'International College of Business and Technology', 'Bambalapitiya', 'private', '@student.icbtcampus.edu.lk');

-- --------------------------------------------------------

--
-- Table structure for table `university_authorities`
--

CREATE TABLE `university_authorities` (
  `id` int(11) NOT NULL,
  `university_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password_hash` varchar(255) NOT NULL,
  `is_password_changed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `university_authorities`
--

INSERT INTO `university_authorities` (`id`, `university_id`, `name`, `email`, `position`, `department`, `phone`, `is_active`, `created_at`, `updated_at`, `password_hash`, `is_password_changed`) VALUES
(11, 6, 'Prof. Wanasinghe W. M. D. A.', 'dean@jfn.ac.lk', 'Dean', 'Faculty of Technology', '0718578632', 1, '2025-08-25 09:28:52', '2025-08-29 08:31:54', '$2y$10$Sx5fmScxR74cdqMXjFCZiuxv6W0yeKIfG.REqYkz3bk6s9h8SBJoe', 0),
(12, 1, 'Dasun Anupiya', '2022t01364@stu.cmb.ac.lk', 'HOD', 'Technology', '015335645', 1, '2025-08-29 08:38:17', '2025-08-29 08:38:17', '$2y$10$pE7PDaaxop0lObGFBkRDmOfj6tm7gFpKFckRrLSQTCf4EupSgmxNW', 0),
(13, 9, 'a. a. er', 'daxsp877@gmail.com', 'Dean', 'Faculty of Technology', '0718578632', 1, '2025-08-30 14:54:11', '2025-08-30 14:54:11', '$2y$10$0N/UH0YOLtaZ.7FXWY/xyOgYXXrujOnhFV.aH5rDjaRjh.d.aEG7m', 0),
(14, 7, 'Dr. Sanjaya Thilakarathne', 'sanjaya@esn.ac.lk', 'HOD', 'Technology', '0784512254', 1, '2025-08-30 23:19:25', '2025-08-30 23:32:27', '$2y$10$8NMmAXHU4NLjGXrtOQPL4.0nM7eXRbbQS14DcZGtmgtbPczoxw34u', 1),
(18, 27, 'Mr. Iraj Weerarathne', 'iraj@ugc.ac.lk', 'Head', 'Anti Ragging Department UGC', '', 1, '2025-08-31 00:01:35', '2025-08-31 14:03:10', '$2y$10$qJEIwgRc.2AlJM6n/lkfKO3wAgqFkN/j1GLUR.3AVusyASPSl5ki6', 1),
(19, 2, 'SAMUNDA LAKSHITHA DE SILVA', 'KD-BSCSD-19-28@student.icbtcampus.edu.lk', 'Dean', 'Anti Ragging Department', '0763256609', 1, '2025-08-31 11:50:01', '2025-08-31 11:50:01', '$2y$10$0XcHEH5fybO6Liuu9zcU4uUVU.5/8l0EyQS2DAHGch6qyWD2YeNnC', 0),
(20, 19, 'Nethmi Anushka', 'sl23photography@gmail.com', 'Dean', 'Anti Ragging Department ICBT', '0763256609', 1, '2025-08-31 14:12:25', '2025-08-31 14:12:25', '$2y$10$v9JO1gnX.k2R9gxCEbc6wOwPrW4UP54vvwZXDaOQwQk7Dd43xOs6.', 0),
(21, 2, 'Prof. John Wick', 'johnwick@pdn.ac.lk', 'HOD', 'Anti Ragging Department', '0704546162', 1, '2025-09-01 02:45:13', '2025-09-01 02:45:13', '$2y$10$QGD9DA2PCcjTlQ8cRQOWg.xiSo39Pmh.cdzO/JMN0VI78U1EHDXEC', 0),
(22, 2, 'Nethmi Suraweeraarachchi', 'nethmianushka250@gmail.com', 'Anti ragging supervisor', 'Anti ragging', '0789037326', 1, '2025-09-02 11:13:35', '2025-09-02 11:13:35', '$2y$10$fnfaFoBXjU6rJRBf8/zlAOpd8DN9KWh5Drizitme/4AxjiGl7BosK', 0),
(28, 28, 'SAMUNDA LAKSHITHA DE SILVA', 'samundasilva23@gmail.com', 'Head', 'Anti Ragging Department ICBT', '0763256609', 1, '2025-09-04 03:53:50', '2025-09-04 07:48:18', '$2y$10$HQBcijy9vTSgxDIi8i7QrelAFuGwTSO2X122fGM5yatXPsIt1aYOi', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report_views`
--
ALTER TABLE `report_views`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_history`
--
ALTER TABLE `status_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `university`
--
ALTER TABLE `university`
  ADD PRIMARY KEY (`uni_id`);

--
-- Indexes for table `university_authorities`
--
ALTER TABLE `university_authorities`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `report_views`
--
ALTER TABLE `report_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `status_history`
--
ALTER TABLE `status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `university`
--
ALTER TABLE `university`
  MODIFY `uni_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `university_authorities`
--
ALTER TABLE `university_authorities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
