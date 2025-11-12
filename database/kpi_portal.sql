-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2025 at 08:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kpi_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` varchar(36) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` varchar(36) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` varchar(36) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `department_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `department_code`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
('6d1b267b-799a-4584-b315-c873946b15e1', 'Copywriter', 'CP', 'Copywriter', 1, '2025-10-23 09:32:47', '2025-10-23 09:33:01'),
('dept-hr-001', 'Human Resources', 'HR', 'HR and Admin Team', 1, '2025-10-22 06:48:54', '2025-10-22 06:48:54'),
('dept-tech-001', 'Technology', 'TECH', 'Technology and Development Team', 1, '2025-10-22 06:48:54', '2025-10-22 06:48:54');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `employee_code` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `employee_email` varchar(255) DEFAULT NULL,
  `profile_picture_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `employee_code`, `first_name`, `last_name`, `middle_name`, `phone_number`, `employee_email`, `profile_picture_url`, `created_at`, `updated_at`) VALUES
('3fe2ca25-0685-4433-865b-26b5ac80106e', '49d677f8-c039-4618-8daf-4cfe83e09490', '1440', 'pradeep', 'Kushwaha', NULL, '9689550530', NULL, '', '2025-11-06 07:02:50', '2025-11-06 07:02:50'),
('emp-admin-001', 'user-admin-001', 'ADMIN001', 'System', 'Administrator', '', '+91-0000000000', 'admin@gozoop.com', NULL, '2025-10-23 07:04:24', '2025-10-23 09:56:33'),
('emp-cto-001', 'user-cto-001', 'EMP001', 'Suresh', 'Kumar', '', '+91-9876543210', 'suresh.kumar@gozoop.com', NULL, '2025-10-22 06:48:54', '2025-10-23 09:55:55'),
('emp-hr-001', 'user-hr-001', 'EMP005', 'Anita', 'Singh', '', '+91-9876543214', 'anita.singh@gozoop.com', NULL, '2025-10-22 06:48:54', '2025-10-23 09:54:36'),
('emp-imran-001', 'user-imran-001', 'EMP999', 'Imran', 'Shaikh', NULL, '+91-9999999999', 'imran.shaikh@gozoop.com', NULL, '2025-10-23 05:15:18', '2025-10-23 05:15:18'),
('emp-sd-001', 'user-sd-001', 'EMP003', 'Priya', 'Patel', '', '+91-9876543212', 'priya.patel@gozoop.com', NULL, '2025-10-22 06:48:54', '2025-10-23 09:56:18'),
('emp-tl-001', 'user-tl-001', 'EMP002', 'Vikram', 'Desai', '', '+91-9876543211', 'vikram.desai@gozoop.com', NULL, '2025-10-22 06:48:54', '2025-10-23 09:56:50'),
('emp-wd-001', 'user-wd-001', 'EMP004', 'Rahul', 'Sharma', '', '+91-9876543213', 'rahul.sharma@gozoop.com', NULL, '2025-10-22 06:48:54', '2025-10-23 09:57:17'),
('emp-wd-002', 'user-wd-002', 'EMP006', 'Sneha', 'Reddy', '', '+91-9876543215', 'sneha.reddy@gozoop.com', NULL, '2025-10-22 06:48:54', '2025-10-23 09:55:08');

-- --------------------------------------------------------

--
-- Table structure for table `employee_hr_spokespersons`
--

CREATE TABLE `employee_hr_spokespersons` (
  `id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `hr_id` varchar(36) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `employee_hr_spokespersons`
--

INSERT INTO `employee_hr_spokespersons` (`id`, `employee_id`, `hr_id`, `is_primary`, `created_at`) VALUES
('5e3bfb11-bade-11f0-a520-8c16451afe81', 'emp-imran-001', 'emp-hr-001', 1, '2025-11-06 07:00:59'),
('5e3c2550-bade-11f0-a520-8c16451afe81', 'emp-tl-001', 'emp-hr-001', 1, '2025-11-06 07:00:59'),
('5e3c26e5-bade-11f0-a520-8c16451afe81', 'emp-sd-001', 'emp-hr-001', 1, '2025-11-06 07:00:59'),
('5e3c27d3-bade-11f0-a520-8c16451afe81', 'emp-cto-001', 'emp-admin-001', 1, '2025-11-06 07:00:59'),
('5e3c28b3-bade-11f0-a520-8c16451afe81', 'emp-hr-001', 'emp-wd-002', 1, '2025-11-06 07:00:59'),
('5e3c2999-bade-11f0-a520-8c16451afe81', 'emp-wd-002', 'emp-cto-001', 1, '2025-11-06 07:00:59'),
('5e3c2a86-bade-11f0-a520-8c16451afe81', 'emp-admin-001', 'emp-cto-001', 1, '2025-11-06 07:00:59'),
('5e3c2b5f-bade-11f0-a520-8c16451afe81', 'emp-wd-001', 'emp-wd-002', 1, '2025-11-06 07:00:59'),
('6dc48b8d-57a2-462e-b114-f534fe477a1c', '3fe2ca25-0685-4433-865b-26b5ac80106e', 'emp-hr-001', 1, '2025-11-06 07:02:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_kpis`
--

CREATE TABLE `employee_kpis` (
  `employee_kpi_id` varchar(36) NOT NULL,
  `assignment_id` varchar(36) DEFAULT NULL,
  `employee_id` varchar(36) NOT NULL,
  `template_id` varchar(36) NOT NULL,
  `review_period_id` varchar(36) NOT NULL,
  `assigned_by` varchar(36) DEFAULT NULL,
  `assigned_date` date NOT NULL,
  `status` enum('ASSIGNED','IN_PROGRESS','COMPLETED','OVERDUE') DEFAULT 'ASSIGNED',
  `is_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_finalized` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether employee has accepted/finalized this KPI review',
  `finalized_at` timestamp NULL DEFAULT NULL COMMENT 'When the employee accepted/finalized this KPI'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_kpis`
--

INSERT INTO `employee_kpis` (`employee_kpi_id`, `assignment_id`, `employee_id`, `template_id`, `review_period_id`, `assigned_by`, `assigned_date`, `status`, `is_locked`, `created_at`, `updated_at`, `is_finalized`, `finalized_at`) VALUES
('0eba4142-8027-4e0a-89bc-3f4f333cc7bf', NULL, 'emp-cto-001', '9d096d36-0c9e-4f1d-ae15-2cde77faf649', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:19:04', '2025-11-05 11:20:20', 1, '2025-11-05 06:50:20'),
('0f310d21-28c1-4c15-92b4-5c863a1b4ba1', NULL, 'emp-imran-001', 'c6620aaa-c0ce-411e-bb78-ac29ce913cd2', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:18:40', '2025-11-03 08:05:26', 1, '2025-11-03 03:35:26'),
('201ce4ea-708a-4caf-bb2c-0dfa130f11d1', NULL, 'emp-imran-001', 'd467c161-2d45-4bc3-86dd-de30af36eb48', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:18:40', '2025-11-03 08:05:26', 1, '2025-11-03 03:35:26'),
('3d36e39c-87af-4f45-b9f3-00f210df29fa', NULL, 'emp-cto-001', '7fd2e3d7-5d30-4e86-bbf2-d60db4f758a9', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:19:04', '2025-11-05 11:20:20', 1, '2025-11-05 06:50:20'),
('58fc7ae7-e7bb-4c51-adc9-6031a4b94241', NULL, 'emp-cto-001', 'c6620aaa-c0ce-411e-bb78-ac29ce913cd2', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:19:04', '2025-11-05 11:20:20', 1, '2025-11-05 06:50:20'),
('67c633d9-7beb-4ab4-a34a-1113919fa0a5', NULL, 'emp-hr-001', 'c62c5e92-f5bd-42ce-b888-8fa5b1b3d28c', 'period-h2-2025', 'emp-wd-002', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 13:08:45', '2025-10-24 13:08:45', 0, NULL),
('6d5a76f1-7cf5-4138-ac52-320ae7283017', NULL, 'emp-hr-001', '277fea36-e00b-4cf5-91f6-9b5ab20109c3', 'period-h2-2025', 'emp-wd-002', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 13:08:45', '2025-10-24 13:08:45', 0, NULL),
('6e432066-3048-4f4c-a762-8eb07373307d', NULL, 'emp-cto-001', 'd467c161-2d45-4bc3-86dd-de30af36eb48', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:19:04', '2025-11-05 11:20:20', 1, '2025-11-05 06:50:20'),
('8e2f2c1e-2113-4483-99d3-67962a54ce0e', NULL, 'emp-hr-001', '701cbcac-4739-4903-8e41-e92e1ef919ef', 'period-h2-2025', 'emp-wd-002', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 13:08:45', '2025-10-24 13:08:45', 0, NULL),
('bd6a6b90-f99d-4054-91bf-67729301d28d', NULL, 'emp-cto-001', '3caea0bc-c4cc-4841-bb49-680047e50038', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:19:04', '2025-11-05 11:20:20', 1, '2025-11-05 06:50:20'),
('be46a0bb-e749-47bf-88d5-72b4eb6c44e7', NULL, 'emp-imran-001', '9d096d36-0c9e-4f1d-ae15-2cde77faf649', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:18:40', '2025-11-03 08:05:26', 1, '2025-11-03 03:35:26'),
('d5088acd-34e6-4dce-9ef8-c5426b0807f8', NULL, 'emp-hr-001', '66f5b1f4-b52a-49cd-a801-e9178a38f6ea', 'period-h2-2025', 'emp-wd-002', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 13:08:45', '2025-10-24 13:08:45', 0, NULL),
('f58da1b4-3735-464b-84cf-aedbea32aefb', NULL, 'emp-imran-001', '7fd2e3d7-5d30-4e86-bbf2-d60db4f758a9', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:18:40', '2025-11-03 08:05:26', 1, '2025-11-03 03:35:26'),
('fab03ab3-1de7-47a0-91c5-a1918c7e5a57', NULL, 'emp-imran-001', '3caea0bc-c4cc-4841-bb49-680047e50038', 'period-h2-2025', 'emp-sd-001', '2025-10-24', 'ASSIGNED', 0, '2025-10-24 11:18:40', '2025-11-03 08:05:26', 1, '2025-11-03 03:35:26');

-- --------------------------------------------------------

--
-- Table structure for table `employee_kpi_assignments`
--

CREATE TABLE `employee_kpi_assignments` (
  `assignment_id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `review_period_id` varchar(36) NOT NULL,
  `assigned_by` varchar(36) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_at` timestamp NULL DEFAULT NULL,
  `employee_agreement_status` enum('PENDING','AGREED','QUERIED') DEFAULT 'PENDING',
  `employee_agreement_at` timestamp NULL DEFAULT NULL,
  `employee_query` text DEFAULT NULL,
  `employee_query_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_reporting_managers`
--

CREATE TABLE `employee_reporting_managers` (
  `id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `manager_id` varchar(36) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `employee_reporting_managers`
--

INSERT INTO `employee_reporting_managers` (`id`, `employee_id`, `manager_id`, `is_primary`, `created_at`) VALUES
('5dce0f22-bade-11f0-a520-8c16451afe81', 'emp-imran-001', 'emp-sd-001', 1, '2025-11-06 07:00:59'),
('5dce2085-bade-11f0-a520-8c16451afe81', 'emp-tl-001', 'emp-admin-001', 1, '2025-11-06 07:00:59'),
('5dce2254-bade-11f0-a520-8c16451afe81', 'emp-sd-001', 'emp-wd-001', 1, '2025-11-06 07:00:59'),
('5dce2385-bade-11f0-a520-8c16451afe81', 'emp-cto-001', 'emp-sd-001', 1, '2025-11-06 07:00:59'),
('5dce24af-bade-11f0-a520-8c16451afe81', 'emp-hr-001', 'emp-wd-002', 1, '2025-11-06 07:00:59'),
('5dce25e3-bade-11f0-a520-8c16451afe81', 'emp-wd-002', 'emp-hr-001', 1, '2025-11-06 07:00:59'),
('5dce2716-bade-11f0-a520-8c16451afe81', 'emp-admin-001', 'emp-tl-001', 1, '2025-11-06 07:00:59'),
('5dce283a-bade-11f0-a520-8c16451afe81', 'emp-wd-001', 'emp-admin-001', 1, '2025-11-06 07:00:59'),
('8a605817-1ea6-4050-9bc3-503929be0a60', '3fe2ca25-0685-4433-865b-26b5ac80106e', 'emp-sd-001', 1, '2025-11-06 07:02:50'),
('a86fc0ca-16d0-4b45-b5cb-26cff8f948cf', '3fe2ca25-0685-4433-865b-26b5ac80106e', 'emp-cto-001', 0, '2025-11-06 07:02:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_roles`
--

CREATE TABLE `employee_roles` (
  `id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `role_id` varchar(36) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` varchar(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_roles`
--

INSERT INTO `employee_roles` (`id`, `employee_id`, `role_id`, `is_primary`, `assigned_at`, `assigned_by`) VALUES
('0565aa25-de65-4a9d-a244-9671350609f5', 'emp-wd-002', '24f295b9-af13-11f0-8e90-8c16451afe81', 1, '2025-10-23 09:55:17', NULL),
('1e53257f-db16-4b71-85f4-caf12c34f5a1', 'emp-hr-001', '24f296bb-af13-11f0-8e90-8c16451afe81', 1, '2025-10-23 12:51:28', NULL),
('2d09639a-af13-11f0-8e90-8c16451afe81', 'emp-cto-001', '24f28bca-af13-11f0-8e90-8c16451afe81', 1, '2025-10-22 06:48:54', NULL),
('2d0a0a38-af13-11f0-8e90-8c16451afe81', 'emp-cto-001', '24f295b9-af13-11f0-8e90-8c16451afe81', 0, '2025-10-22 06:48:54', NULL),
('2d0a0c87-af13-11f0-8e90-8c16451afe81', 'emp-sd-001', '24f295b9-af13-11f0-8e90-8c16451afe81', 1, '2025-10-22 06:48:54', NULL),
('2d0a0d13-af13-11f0-8e90-8c16451afe81', 'emp-sd-001', '24f296bb-af13-11f0-8e90-8c16451afe81', 0, '2025-10-22 06:48:54', NULL),
('2d0a0d9d-af13-11f0-8e90-8c16451afe81', 'emp-wd-001', '24f296bb-af13-11f0-8e90-8c16451afe81', 1, '2025-10-22 06:48:54', NULL),
('33ae2615-88cd-4aba-96cd-3ce7a0f02a07', 'emp-tl-001', '24f296bb-af13-11f0-8e90-8c16451afe81', 1, '2025-10-23 09:23:02', NULL),
('4f9d5edd-f086-472c-ab67-60e8165e8a25', 'emp-wd-002', '24f296bb-af13-11f0-8e90-8c16451afe81', 0, '2025-10-23 09:55:17', NULL),
('b084f208-406b-4d47-9323-a76dc5f22d72', '3fe2ca25-0685-4433-865b-26b5ac80106e', '24f296bb-af13-11f0-8e90-8c16451afe81', 1, '2025-11-06 07:02:50', NULL),
('f5fcb12a-afd0-11f0-8e90-8c16451afe81', 'emp-imran-001', '24f296bb-af13-11f0-8e90-8c16451afe81', 1, '2025-10-23 05:27:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_work_info`
--

CREATE TABLE `employee_work_info` (
  `id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `department_id` varchar(36) DEFAULT NULL,
  `designation` varchar(100) NOT NULL,
  `reporting_manager_id` varchar(36) NOT NULL,
  `hr_spokesperson_id` varchar(36) NOT NULL,
  `date_of_joining` date NOT NULL,
  `employment_type` varchar(50) NOT NULL DEFAULT 'Full-time',
  `employee_status` varchar(50) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `employee_work_info`
--

INSERT INTO `employee_work_info` (`id`, `employee_id`, `department_id`, `designation`, `reporting_manager_id`, `hr_spokesperson_id`, `date_of_joining`, `employment_type`, `employee_status`, `created_at`, `updated_at`) VALUES
('0d568faa-afdd-11f0-8e90-8c16451afe81', 'emp-imran-001', 'dept-tech-001', 'Software Developer', 'emp-sd-001', 'emp-hr-001', '2024-01-01', 'Full-time', 'ACTIVE', '2025-10-23 06:53:38', '2025-10-24 11:10:06'),
('1e6c1733-1cfd-4d9a-b6ca-44224970aef7', 'emp-tl-001', '6d1b267b-799a-4584-b315-c873946b15e1', 'Employee', 'emp-admin-001', 'emp-hr-001', '2025-10-23', 'Full-time', 'ACTIVE', '2025-10-23 06:22:08', '2025-10-23 09:56:50'),
('25c724d5-0c16-49ce-8d40-d7f960880ca7', 'emp-sd-001', '6d1b267b-799a-4584-b315-c873946b15e1', 'Employee', 'emp-wd-001', 'emp-hr-001', '2025-10-23', 'Full-time', 'ACTIVE', '2025-10-23 06:22:50', '2025-10-23 10:29:58'),
('2f9fb6f7-a9d1-4426-aefd-17d46987da61', '3fe2ca25-0685-4433-865b-26b5ac80106e', 'dept-tech-001', 'Senior Developer', 'emp-sd-001', 'emp-hr-001', '2025-11-07', 'Full-time', 'ACTIVE', '2025-11-06 07:02:50', '2025-11-06 07:02:50'),
('942355cc-e35e-438d-b846-88599582751b', 'emp-cto-001', 'dept-tech-001', 'Country Head', 'emp-sd-001', 'emp-admin-001', '2025-10-23', 'Full-time', 'ACTIVE', '2025-10-23 06:22:26', '2025-10-24 11:10:06'),
('964700a1-fd7f-46d5-94fb-3d81761f53af', 'emp-hr-001', 'dept-hr-001', 'HR', 'emp-wd-002', 'emp-wd-002', '2025-10-23', 'Full-time', 'ACTIVE', '2025-10-23 06:22:35', '2025-10-23 09:55:27'),
('a57b571d-cd32-43b7-9c86-c5e9b6fd38dc', 'emp-wd-002', 'dept-hr-001', 'HR Head', 'emp-hr-001', 'emp-cto-001', '2025-10-23', 'Full-time', 'ACTIVE', '2025-10-23 06:22:28', '2025-10-23 10:29:16'),
('ce6f55f3-d431-466d-8da3-ff8e2c76af4e', 'emp-admin-001', '6d1b267b-799a-4584-b315-c873946b15e1', 'Admin', 'emp-tl-001', 'emp-cto-001', '2025-10-23', 'Full-time', 'ACTIVE', '2025-10-23 06:22:20', '2025-10-23 10:30:25'),
('d56eb95c-23d4-4e91-9320-9c4713b3ef91', 'emp-wd-001', '6d1b267b-799a-4584-b315-c873946b15e1', 'Employee', 'emp-admin-001', 'emp-wd-002', '2025-10-23', 'Full-time', 'ACTIVE', '2025-10-23 06:22:54', '2025-10-23 09:57:18');

-- --------------------------------------------------------

--
-- Table structure for table `kpi_assignment_logs`
--

CREATE TABLE `kpi_assignment_logs` (
  `id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `assignor_id` varchar(36) NOT NULL,
  `assignor_role` varchar(50) NOT NULL COMMENT 'Reporting Manager, HR Spokesperson, Department Head, Super Admin',
  `is_fallback` tinyint(1) DEFAULT 0 COMMENT 'Whether this was a fallback assignment',
  `fallback_reason` varchar(255) DEFAULT NULL COMMENT 'Reason for fallback',
  `period_id` varchar(36) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kpi_categories`
--

CREATE TABLE `kpi_categories` (
  `category_id` varchar(36) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kpi_categories`
--

INSERT INTO `kpi_categories` (`category_id`, `category_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
('251fa2a0-af13-11f0-8e90-8c16451afe81', 'Work KPIs', 'Job-specific tasks and deliverables', 1, '2025-10-22 06:48:41', '2025-10-22 06:48:41'),
('251fa585-af13-11f0-8e90-8c16451afe81', 'Competencies', 'Behavioral and soft skills', 1, '2025-10-22 06:48:41', '2025-10-22 06:48:41'),
('34dba34e-9e15-495b-930f-3ee2be02e3b4', 'Understand servers + create servers(installation) + managing websites with 0 downtime', 'Auto-created from import', 1, '2025-10-23 10:15:53', '2025-10-23 10:15:53'),
('71cdb6ca-0d38-4eeb-9350-167c994f203b', 'Test1', '', 1, '2025-10-23 13:02:49', '2025-10-23 13:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `kpi_edit_requests`
--

CREATE TABLE `kpi_edit_requests` (
  `request_id` varchar(36) NOT NULL,
  `employee_kpi_id` varchar(36) NOT NULL,
  `requested_by` varchar(36) NOT NULL,
  `field_to_change` varchar(50) NOT NULL,
  `current_value` text DEFAULT NULL,
  `requested_value` text NOT NULL,
  `remark` text NOT NULL,
  `request_status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `reviewed_by` varchar(36) DEFAULT NULL,
  `review_comments` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kpi_scores`
--

CREATE TABLE `kpi_scores` (
  `score_id` varchar(36) NOT NULL,
  `employee_kpi_id` varchar(36) NOT NULL,
  `weightage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `score` decimal(3,2) DEFAULT NULL,
  `weighted_score` decimal(5,2) GENERATED ALWAYS AS (`weightage` * ifnull(`score`,0) / 5) STORED,
  `is_editable` tinyint(1) DEFAULT 1,
  `last_edited_by` varchar(36) DEFAULT NULL,
  `last_edited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `kpi_scores`
--

INSERT INTO `kpi_scores` (`score_id`, `employee_kpi_id`, `weightage`, `score`, `is_editable`, `last_edited_by`, `last_edited_at`, `created_at`, `updated_at`) VALUES
('09a2e3fa-ccdd-43a0-82d2-062ef2fb4079', '3d36e39c-87af-4f45-b9f3-00f210df29fa', 20.00, 3.00, 1, 'emp-sd-001', '2025-10-24 07:49:04', '2025-10-24 11:19:04', '2025-10-24 11:19:04'),
('1f7d0296-b95d-4afd-8aca-31187d01ed99', 'be46a0bb-e749-47bf-88d5-72b4eb6c44e7', 20.00, 2.00, 1, 'emp-sd-001', '2025-10-24 07:48:40', '2025-10-24 11:18:40', '2025-10-24 11:18:40'),
('29968148-456d-464f-bf03-f767af15ae50', '201ce4ea-708a-4caf-bb2c-0dfa130f11d1', 20.00, 3.00, 1, 'emp-sd-001', '2025-10-24 07:48:40', '2025-10-24 11:18:40', '2025-10-24 11:18:40'),
('30a38c37-9a6a-413c-b0b0-7c289dbdcdb8', 'fab03ab3-1de7-47a0-91c5-a1918c7e5a57', 20.00, 3.00, 1, 'emp-sd-001', '2025-10-24 07:48:40', '2025-10-24 11:18:40', '2025-10-24 11:18:40'),
('4861a43b-ed97-4282-9584-e089a5e7fa33', '67c633d9-7beb-4ab4-a34a-1113919fa0a5', 20.00, 2.00, 1, 'emp-wd-002', '2025-10-24 09:38:45', '2025-10-24 13:08:45', '2025-10-24 13:08:45'),
('577fe795-3804-462b-9a79-f86c81b58d14', 'bd6a6b90-f99d-4054-91bf-67729301d28d', 20.00, 3.00, 1, 'emp-sd-001', '2025-10-24 07:49:04', '2025-10-24 11:19:04', '2025-10-24 11:19:04'),
('6401c1bf-e1a1-44b1-aa12-06fc9bf43c54', '58fc7ae7-e7bb-4c51-adc9-6031a4b94241', 20.00, 3.50, 1, 'emp-sd-001', '2025-10-24 07:49:04', '2025-10-24 11:19:04', '2025-10-24 11:19:04'),
('8649ccb8-2075-4b79-8957-16fa370a7a49', 'f58da1b4-3735-464b-84cf-aedbea32aefb', 20.00, 3.00, 1, 'emp-sd-001', '2025-10-24 07:48:40', '2025-10-24 11:18:40', '2025-10-24 11:18:40'),
('87e56750-ecb2-4c97-a2a6-6dedcdfeeee4', '6d5a76f1-7cf5-4138-ac52-320ae7283017', 30.00, 3.00, 1, 'emp-wd-002', '2025-10-24 09:38:45', '2025-10-24 13:08:45', '2025-10-24 13:08:45'),
('a453dc7e-2b8c-489f-8fc6-6b15497c4d38', '0f310d21-28c1-4c15-92b4-5c863a1b4ba1', 20.00, 3.50, 1, 'emp-sd-001', '2025-10-24 07:48:40', '2025-10-24 11:18:40', '2025-10-24 11:18:40'),
('bc1512ee-2a55-40fb-ba48-fc7b6e5963b3', 'd5088acd-34e6-4dce-9ef8-c5426b0807f8', 30.00, 3.00, 1, 'emp-wd-002', '2025-10-24 09:38:45', '2025-10-24 13:08:45', '2025-10-24 13:08:45'),
('db0f9936-3fec-4a79-bdf2-162cc7c0ba97', '6e432066-3048-4f4c-a762-8eb07373307d', 20.00, 3.00, 1, 'emp-sd-001', '2025-10-24 07:49:04', '2025-10-24 11:19:04', '2025-10-24 11:19:04'),
('eb419460-4c6a-408b-9cb1-a74d5eb00950', '0eba4142-8027-4e0a-89bc-3f4f333cc7bf', 20.00, 2.00, 1, 'emp-sd-001', '2025-10-24 07:49:04', '2025-10-24 11:19:04', '2025-10-24 11:19:04'),
('efd19d61-03b9-4192-a76b-b63b1dd45755', '8e2f2c1e-2113-4483-99d3-67962a54ce0e', 20.00, 2.00, 1, 'emp-wd-002', '2025-10-24 09:38:45', '2025-10-24 13:08:45', '2025-10-24 13:08:45');

-- --------------------------------------------------------

--
-- Table structure for table `kpi_score_history`
--

CREATE TABLE `kpi_score_history` (
  `history_id` varchar(36) NOT NULL,
  `score_id` varchar(36) NOT NULL,
  `employee_kpi_id` varchar(36) NOT NULL,
  `field_changed` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text NOT NULL,
  `changed_by` varchar(36) DEFAULT NULL,
  `change_reason` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kpi_templates`
--

CREATE TABLE `kpi_templates` (
  `template_id` varchar(36) NOT NULL,
  `template_name` varchar(200) DEFAULT NULL,
  `weightage` decimal(5,2) DEFAULT 0.00 COMMENT 'Percentage weightage of this KPI',
  `category_id` varchar(36) NOT NULL,
  `kpi_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `default_score` decimal(3,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kpi_templates`
--

INSERT INTO `kpi_templates` (`template_id`, `template_name`, `weightage`, `category_id`, `kpi_name`, `description`, `default_score`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
('07be4503-e5d0-4a6d-a3d7-84163cd62fdf', 'Tech Team', 20.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task 1', NULL, 0.00, 0, 'emp-cto-001', '2025-10-24 07:35:37', '2025-10-24 11:07:46'),
('277fea36-e00b-4cf5-91f6-9b5ab20109c3', 'HR Team', 30.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'test1', NULL, 3.00, 1, 'emp-cto-001', '2025-10-24 09:32:28', '2025-10-24 09:32:28'),
('29a61735-8951-45ab-9332-ff47552404bf', 'Account teams', 20.00, '251fa2a0-af13-11f0-8e90-8c16451afe81', 'task 2', NULL, 3.00, 1, 'emp-cto-001', '2025-10-24 07:35:20', '2025-10-24 07:35:20'),
('3caea0bc-c4cc-4841-bb49-680047e50038', 'Tech Team', 20.00, '71cdb6ca-0d38-4eeb-9350-167c994f203b', 'Task2', NULL, 3.00, 1, 'emp-cto-001', '2025-10-24 07:37:46', '2025-10-24 07:37:46'),
('480467a9-7e28-483f-bc49-e33a0453867d', 'Tech Team', 20.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task 2', NULL, 3.00, 0, 'emp-cto-001', '2025-10-24 07:35:37', '2025-10-24 11:07:46'),
('5095a171-36c1-48f1-95be-79e170893c1f', 'Tech Team', 0.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task3', 'Weightage: 0%', NULL, 0, 'emp-cto-001', '2025-10-24 09:47:53', '2025-10-24 11:05:37'),
('6026ebf3-a10d-47bd-893b-aca650ef2eb7', 'Tech Team', 0.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task 1', 'Weightage: 50%', NULL, 0, 'emp-cto-001', '2025-10-24 09:47:53', '2025-10-24 11:05:37'),
('64333ab9-3737-4b23-af4a-3efe93fcb743', 'Account teams', 80.00, '251fa2a0-af13-11f0-8e90-8c16451afe81', 'task 1', NULL, 3.50, 1, 'emp-cto-001', '2025-10-24 07:35:20', '2025-10-24 07:35:20'),
('66f5b1f4-b52a-49cd-a801-e9178a38f6ea', 'HR Team', 30.00, '251fa2a0-af13-11f0-8e90-8c16451afe81', 'test2', NULL, 3.00, 1, 'emp-cto-001', '2025-10-24 09:32:28', '2025-10-24 09:32:28'),
('69ee8960-4898-40c5-9901-4764b48efbb1', 'Tech Team', 0.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'task 1', 'Weightage: 50%', 3.50, 0, 'emp-cto-001', '2025-10-24 10:54:16', '2025-10-24 11:05:37'),
('6d2e4d93-b466-45ff-a79e-34de70d03937', 'Account teams', 0.00, '251fa2a0-af13-11f0-8e90-8c16451afe81', 'task 2', 'Weightage: 50%', 3.00, 0, 'emp-cto-001', '2025-10-24 10:54:50', '2025-10-24 11:05:20'),
('701cbcac-4739-4903-8e41-e92e1ef919ef', 'HR Team', 20.00, '251fa2a0-af13-11f0-8e90-8c16451afe81', 'test4', NULL, 2.00, 1, 'emp-cto-001', '2025-10-24 09:32:28', '2025-10-24 09:32:28'),
('70fdf4b3-8e23-4209-8071-c490f91a1f70', 'Tech Team', 20.00, '71cdb6ca-0d38-4eeb-9350-167c994f203b', 'Task2', NULL, 3.00, 0, 'emp-cto-001', '2025-10-24 07:35:37', '2025-10-24 11:07:46'),
('7fd2e3d7-5d30-4e86-bbf2-d60db4f758a9', 'Tech Team', 20.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task 1', NULL, 3.00, 1, 'emp-cto-001', '2025-10-24 07:37:46', '2025-10-24 07:37:46'),
('9015e180-2201-4f0c-98fc-32dd3b7ed4b3', 'Tech Team', 20.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task3', NULL, 0.00, 0, 'emp-cto-001', '2025-10-24 07:35:37', '2025-10-24 11:07:46'),
('93e8d231-0642-432d-a9a8-898a8d7ea745', 'Account teams', 0.00, '251fa2a0-af13-11f0-8e90-8c16451afe81', 'task 1', 'Weightage: 50%', 3.50, 0, 'emp-cto-001', '2025-10-24 10:54:50', '2025-10-24 11:05:20'),
('9d096d36-0c9e-4f1d-ae15-2cde77faf649', 'Tech Team', 20.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task3', NULL, 2.00, 1, 'emp-cto-001', '2025-10-24 07:37:46', '2025-10-24 07:37:46'),
('a293c6f4-e7f9-4286-883f-cac1ea02ebc9', 'Tech Team', 0.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task2', 'Weightage: 50%', 3.00, 0, 'emp-cto-001', '2025-10-24 09:47:53', '2025-10-24 11:05:37'),
('a326e01b-ff10-4b98-822f-2bbb27f6de27', 'Tech Team', 20.00, '251fa2a0-af13-11f0-8e90-8c16451afe81', 'task 1', NULL, 3.50, 0, 'emp-cto-001', '2025-10-24 07:35:37', '2025-10-24 11:07:46'),
('c62c5e92-f5bd-42ce-b888-8fa5b1b3d28c', 'HR Team', 20.00, '34dba34e-9e15-495b-930f-3ee2be02e3b4', 'test3', NULL, 2.00, 1, 'emp-cto-001', '2025-10-24 09:32:28', '2025-10-24 09:32:28'),
('c6620aaa-c0ce-411e-bb78-ac29ce913cd2', 'Tech Team', 20.00, '251fa2a0-af13-11f0-8e90-8c16451afe81', 'task 1', NULL, 3.50, 1, 'emp-cto-001', '2025-10-24 07:37:46', '2025-10-24 07:37:46'),
('d467c161-2d45-4bc3-86dd-de30af36eb48', 'Tech Team', 20.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task 2', NULL, 3.00, 1, 'emp-cto-001', '2025-10-24 07:37:46', '2025-10-24 07:37:46'),
('e99037e2-bb10-4afc-98fc-fe1a1e926c84', 'Tech Team', 0.00, '251fa585-af13-11f0-8e90-8c16451afe81', 'Task 2', 'Weightage: 50%', 3.00, 0, 'emp-cto-001', '2025-10-24 10:54:16', '2025-10-24 11:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `performance_reviews`
--

CREATE TABLE `performance_reviews` (
  `review_id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `review_period_id` varchar(36) NOT NULL,
  `reviewer_id` varchar(36) DEFAULT NULL,
  `total_work_kpi_score` decimal(5,2) DEFAULT NULL,
  `total_competency_score` decimal(5,2) DEFAULT NULL,
  `final_score` decimal(5,2) DEFAULT NULL,
  `h1_score` decimal(5,2) DEFAULT NULL,
  `h2_score` decimal(5,2) DEFAULT NULL,
  `manager_comments` text DEFAULT NULL,
  `hr_comments` text DEFAULT NULL,
  `employee_comments` text DEFAULT NULL,
  `review_status` enum('DRAFT','MANAGER_REVIEW','HR_REVIEW','COMPLETED','ACKNOWLEDGED') DEFAULT 'DRAFT',
  `review_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_periods`
--

CREATE TABLE `review_periods` (
  `period_id` varchar(36) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `period_type` enum('H1','H2') NOT NULL,
  `cycle_year` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `fiscal_year` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `status` enum('UPCOMING','ACTIVE','CLOSED') DEFAULT 'UPCOMING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `review_periods`
--

INSERT INTO `review_periods` (`period_id`, `period_name`, `period_type`, `cycle_year`, `start_date`, `end_date`, `fiscal_year`, `is_active`, `status`, `created_at`, `updated_at`) VALUES
('period-h1-2025', 'H1 (April-Sept 2025)', 'H1', '2025', '2025-04-01', '2025-09-30', '2025-2026', 1, 'CLOSED', '2025-10-23 05:15:17', '2025-10-23 05:15:17'),
('period-h2-2025', 'H2 (Oct-Mar 2026)', 'H2', '2025', '2025-10-01', '2026-03-31', '2025-2026', 1, 'ACTIVE', '2025-10-23 05:15:17', '2025-10-23 05:15:17');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` varchar(36) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`, `created_at`) VALUES
('24f28bca-af13-11f0-8e90-8c16451afe81', 'SUPER_ADMIN', 'Full system access', '2025-10-22 06:48:40'),
('24f295b9-af13-11f0-8e90-8c16451afe81', 'MANAGER', 'Can manage team and review KPIs', '2025-10-22 06:48:40'),
('24f296bb-af13-11f0-8e90-8c16451afe81', 'EMPLOYEE', 'Regular employee access', '2025-10-22 06:48:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `is_active`, `created_at`, `updated_at`, `last_login`) VALUES
('49d677f8-c039-4618-8daf-4cfe83e09490', 'pradeep@email.gozoop.com', '$2y$10$RWKdNnzGJK0x.0TIE4keouH/sq4tVnYVj8YIqHpi5PwiWG3TLqGKa', 1, '2025-11-06 07:02:50', '2025-11-06 07:02:50', NULL),
('user-admin-001', 'admin@gozoop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-10-23 07:04:24', '2025-10-23 07:04:24', NULL),
('user-cto-001', 'suresh.kumar@gozoop.com', '$2y$10$2N4FCPnHdyq9iAhKCIAUweYVoL/TL.7b6KfGfdPRDS.lvu7bTxTdW', 1, '2025-10-22 06:48:54', '2025-11-06 07:06:38', '2025-11-06 02:36:38'),
('user-hr-001', 'anita.singh@gozoop.com', '$2y$10$Zvuid.9KZdj74gT5V6nRXu7rBaaKWNE78JHXcIBBHPzJNgYr1axAS', 1, '2025-10-22 06:48:54', '2025-10-24 13:10:03', '2025-10-24 09:40:03'),
('user-imran-001', 'imran.shaikh@gozoop.com', '$2y$10$wKDNvF4zNZdtTMmbRD8ngemIB/5e1SbPU7e6/XNhrxWPlgWJ.g1u6', 1, '2025-10-23 05:15:18', '2025-11-05 10:35:27', '2025-11-05 06:05:27'),
('user-sd-001', 'priya.patel@gozoop.com', '$2y$10$NVFu0bz1FsMXQrEi22/jy.lz70WmLun0YRi8HXxRGs1y3tjSWpkDi', 1, '2025-10-22 06:48:54', '2025-11-06 07:05:16', '2025-11-06 02:35:16'),
('user-tl-001', 'vikram.desai@gozoop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-10-22 06:48:54', '2025-10-22 06:48:54', NULL),
('user-wd-001', 'rahul.sharma@gozoop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-10-22 06:48:54', '2025-10-22 06:48:54', NULL),
('user-wd-002', 'sneha.reddy@gozoop.com', '$2y$10$deoTiqiF3zTjUWaSm9BZ4ufYAieLsx3yXAwrTOQrMHHPlC.QOc85m', 1, '2025-10-22 06:48:54', '2025-10-24 13:04:42', '2025-10-24 09:34:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD UNIQUE KEY `department_code` (`department_code`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD KEY `idx_employee_code` (`employee_code`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `employee_hr_spokespersons`
--
ALTER TABLE `employee_hr_spokespersons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_hr` (`employee_id`,`hr_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_hr_id` (`hr_id`);

--
-- Indexes for table `employee_kpis`
--
ALTER TABLE `employee_kpis`
  ADD PRIMARY KEY (`employee_kpi_id`),
  ADD UNIQUE KEY `unique_employee_kpi_period` (`employee_id`,`template_id`,`review_period_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_period_id` (`review_period_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_finalized` (`is_finalized`,`finalized_at`),
  ADD KEY `fk_employee_kpis_assignment` (`assignment_id`);

--
-- Indexes for table `employee_kpi_assignments`
--
ALTER TABLE `employee_kpi_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_employee_period` (`employee_id`,`review_period_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_review_period_id` (`review_period_id`),
  ADD KEY `idx_agreement_status` (`employee_agreement_status`);

--
-- Indexes for table `employee_reporting_managers`
--
ALTER TABLE `employee_reporting_managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_manager` (`employee_id`,`manager_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_manager_id` (`manager_id`);

--
-- Indexes for table `employee_roles`
--
ALTER TABLE `employee_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_role` (`employee_id`,`role_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_role_id` (`role_id`);

--
-- Indexes for table `employee_work_info`
--
ALTER TABLE `employee_work_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_reporting_manager` (`reporting_manager_id`),
  ADD KEY `idx_hr_spokesperson` (`hr_spokesperson_id`),
  ADD KEY `idx_department` (`department_id`);

--
-- Indexes for table `kpi_assignment_logs`
--
ALTER TABLE `kpi_assignment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `period_id` (`period_id`),
  ADD KEY `idx_employee_period` (`employee_id`,`period_id`),
  ADD KEY `idx_assignor` (`assignor_id`),
  ADD KEY `idx_is_fallback` (`is_fallback`),
  ADD KEY `idx_assigned_at` (`assigned_at`);

--
-- Indexes for table `kpi_categories`
--
ALTER TABLE `kpi_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `kpi_edit_requests`
--
ALTER TABLE `kpi_edit_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_employee_kpi_id` (`employee_kpi_id`),
  ADD KEY `idx_requested_by` (`requested_by`),
  ADD KEY `idx_status` (`request_status`);

--
-- Indexes for table `kpi_scores`
--
ALTER TABLE `kpi_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD UNIQUE KEY `employee_kpi_id` (`employee_kpi_id`),
  ADD KEY `last_edited_by` (`last_edited_by`),
  ADD KEY `idx_employee_kpi_id` (`employee_kpi_id`);

--
-- Indexes for table `kpi_score_history`
--
ALTER TABLE `kpi_score_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_score_id` (`score_id`),
  ADD KEY `idx_employee_kpi_id` (`employee_kpi_id`);

--
-- Indexes for table `kpi_templates`
--
ALTER TABLE `kpi_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_template_name` (`template_name`),
  ADD KEY `idx_weightage` (`weightage`);

--
-- Indexes for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_employee_period_review` (`employee_id`,`review_period_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_period_id` (`review_period_id`),
  ADD KEY `idx_status` (`review_status`);

--
-- Indexes for table `review_periods`
--
ALTER TABLE `review_periods`
  ADD PRIMARY KEY (`period_id`),
  ADD KEY `idx_period_type` (`period_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_hr_spokespersons`
--
ALTER TABLE `employee_hr_spokespersons`
  ADD CONSTRAINT `employee_hr_spokespersons_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_hr_spokespersons_ibfk_2` FOREIGN KEY (`hr_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_kpis`
--
ALTER TABLE `employee_kpis`
  ADD CONSTRAINT `employee_kpis_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_kpis_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `kpi_templates` (`template_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_kpis_ibfk_3` FOREIGN KEY (`review_period_id`) REFERENCES `review_periods` (`period_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_kpis_ibfk_4` FOREIGN KEY (`assigned_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_employee_kpis_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `employee_kpi_assignments` (`assignment_id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_kpi_assignments`
--
ALTER TABLE `employee_kpi_assignments`
  ADD CONSTRAINT `employee_kpi_assignments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_kpi_assignments_ibfk_2` FOREIGN KEY (`review_period_id`) REFERENCES `review_periods` (`period_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_kpi_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_reporting_managers`
--
ALTER TABLE `employee_reporting_managers`
  ADD CONSTRAINT `employee_reporting_managers_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_reporting_managers_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_roles`
--
ALTER TABLE `employee_roles`
  ADD CONSTRAINT `employee_roles_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_work_info`
--
ALTER TABLE `employee_work_info`
  ADD CONSTRAINT `employee_work_info_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_work_info_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_work_info_ibfk_3` FOREIGN KEY (`reporting_manager_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_work_info_ibfk_4` FOREIGN KEY (`hr_spokesperson_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `kpi_assignment_logs`
--
ALTER TABLE `kpi_assignment_logs`
  ADD CONSTRAINT `kpi_assignment_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_assignment_logs_ibfk_2` FOREIGN KEY (`assignor_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_assignment_logs_ibfk_3` FOREIGN KEY (`period_id`) REFERENCES `review_periods` (`period_id`) ON DELETE CASCADE;

--
-- Constraints for table `kpi_edit_requests`
--
ALTER TABLE `kpi_edit_requests`
  ADD CONSTRAINT `kpi_edit_requests_ibfk_1` FOREIGN KEY (`employee_kpi_id`) REFERENCES `employee_kpis` (`employee_kpi_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_edit_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_edit_requests_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `kpi_scores`
--
ALTER TABLE `kpi_scores`
  ADD CONSTRAINT `kpi_scores_ibfk_1` FOREIGN KEY (`employee_kpi_id`) REFERENCES `employee_kpis` (`employee_kpi_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_scores_ibfk_2` FOREIGN KEY (`last_edited_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `kpi_score_history`
--
ALTER TABLE `kpi_score_history`
  ADD CONSTRAINT `kpi_score_history_ibfk_1` FOREIGN KEY (`score_id`) REFERENCES `kpi_scores` (`score_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_score_history_ibfk_2` FOREIGN KEY (`employee_kpi_id`) REFERENCES `employee_kpis` (`employee_kpi_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_score_history_ibfk_3` FOREIGN KEY (`changed_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `kpi_templates`
--
ALTER TABLE `kpi_templates`
  ADD CONSTRAINT `kpi_templates_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `kpi_categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_templates_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD CONSTRAINT `performance_reviews_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `performance_reviews_ibfk_2` FOREIGN KEY (`review_period_id`) REFERENCES `review_periods` (`period_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `performance_reviews_ibfk_3` FOREIGN KEY (`reviewer_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
