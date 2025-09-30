-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 10:37 AM
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
-- Database: `bgc`
--

-- --------------------------------------------------------

--
-- Table structure for table `bloodline`
--

CREATE TABLE `bloodline` (
  `bloodline_id` int(11) NOT NULL,
  `bloodline_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bloodline`
--

INSERT INTO `bloodline` (`bloodline_id`, `bloodline_name`) VALUES
(1, 'McLean'),
(2, 'Sweater'),
(4, 'kelso');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `branch_name`) VALUES
(1, 'Banao'),
(2, 'Banquerohan'),
(3, 'Bonga');

-- --------------------------------------------------------

--
-- Table structure for table `broodcocks`
--

CREATE TABLE `broodcocks` (
  `cock_id` int(11) NOT NULL,
  `pen_number` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `bloodline_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `marking` varchar(50) DEFAULT NULL,
  `wing_band` varchar(50) DEFAULT NULL,
  `leg_band` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `broodcocks`
--

INSERT INTO `broodcocks` (`cock_id`, `pen_number`, `color`, `bloodline_id`, `name`, `marking`, `wing_band`, `leg_band`, `remarks`) VALUES
(4, NULL, '', 2, '', '', '34', 're', ''),
(5, NULL, '', 2, '', '', '34', 're', ''),
(6, NULL, '', 2, '', '', '34', 're', ''),
(7, '1', '', 2, '', '', '34', 're', ''),
(8, '2', '', 2, '', '', '34', 're', ''),
(9, '3', '', 2, '', '', '34', 're', ''),
(10, '4', '', 2, '', '', '34', 're', ''),
(11, '5', '', 1, '', '', 'uiriu3', 'k7', ''),
(12, '6', '', 1, '', '', 'uiriu3', 'k7', ''),
(13, '7', '', 1, '', '', 'uiriu3', 're', ''),
(14, '8', '', 1, '', '', 'vgyft', 'gtf', ''),
(15, '9', '', 1, '', '', 'jad', 'ahaj', '');

-- --------------------------------------------------------

--
-- Table structure for table `broodhens`
--

CREATE TABLE `broodhens` (
  `hen_id` int(11) NOT NULL,
  `pen_number` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `bloodline_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `marking` varchar(50) DEFAULT NULL,
  `wing_band` varchar(50) DEFAULT NULL,
  `leg_band` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `broodhens`
--

INSERT INTO `broodhens` (`hen_id`, `pen_number`, `color`, `bloodline_id`, `name`, `marking`, `wing_band`, `leg_band`, `remarks`) VALUES
(1, NULL, '', 2, '', '', '0', '0', ''),
(2, NULL, '', 2, '', '', 'er', 'er', ''),
(3, '1', '', 2, '', '', 'er', 'er', ''),
(4, '2', '', 2, '', '', 'er', 'er', ''),
(5, '3', '', 2, '', NULL, NULL, NULL, NULL),
(6, '4', '', 2, '', NULL, NULL, NULL, NULL),
(7, '5', '', 1, '', NULL, NULL, NULL, NULL),
(8, '6', '', 1, '', NULL, NULL, NULL, NULL),
(9, '6', '', 1, '', ' msjd', 'jnsdj', 'mks', 'mks'),
(10, '6', '', 1, '', 'SJNSU', 'JNSU', 'NUAH', 'USBBX'),
(11, '7', '', 1, '', NULL, NULL, NULL, NULL),
(12, '7', '', 1, '', '74376567', 'huyd', 'hgvdgc', 'vgdvct'),
(13, '7', '', 1, '', 'uscsug76', 'yfs76', '6v6', 'c66'),
(14, '7', '', 1, '', 'njdh', 'yy7', '7yb', '7vg'),
(15, '8', '', 1, '', NULL, NULL, NULL, NULL),
(16, '8', '', 1, '', 'hjsdgwy', 'hgq', 'vgc', 'cgft'),
(17, '8', '', 1, '', 'fcfdx', 'fcf', 'fxtr', 'rxrr'),
(18, '9', '', 1, '', 'asnka', 'akjsbj', 'jbas', 'bjash');

-- --------------------------------------------------------

--
-- Table structure for table `disease_records`
--

CREATE TABLE `disease_records` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `date_detected` date NOT NULL,
  `bloodline` varchar(100) NOT NULL,
  `wingband` varchar(50) NOT NULL,
  `legband` varchar(50) NOT NULL,
  `disease_name` varchar(100) NOT NULL,
  `status` enum('Infected','Cured') NOT NULL DEFAULT 'Infected',
  `date_cured` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disease_records`
--

INSERT INTO `disease_records` (`id`, `branch_id`, `date_detected`, `bloodline`, `wingband`, `legband`, `disease_name`, `status`, `date_cured`) VALUES
(0, 1, '2025-09-09', 'ysy', 'ysquy', 'gyt', 'gyt', 'Infected', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `employee_name` varchar(100) NOT NULL,
  `area` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `separation_date` date DEFAULT NULL,
  `monthly_rate` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_name`, `area`, `position`, `address`, `date_of_birth`, `contact_number`, `date_hired`, `separation_date`, `monthly_rate`) VALUES
(1, 'Marlon B. Estrada', 'overall', 'Farm Supervisor', 'San Jose ', '1987-02-09', '09123456789', '2015-02-10', '0000-00-00', 20000.00);

-- --------------------------------------------------------

--
-- Table structure for table `markings`
--

CREATE TABLE `markings` (
  `marking_id` int(11) NOT NULL,
  `pen_number` int(11) DEFAULT NULL,
  `cock_id` int(11) DEFAULT NULL,
  `hen_id` int(11) DEFAULT NULL,
  `bloodline_id` int(11) DEFAULT NULL,
  `marking` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `markings`
--

INSERT INTO `markings` (`marking_id`, `pen_number`, `cock_id`, `hen_id`, `bloodline_id`, `marking`) VALUES
(1, NULL, 4, 1, 2, ''),
(2, NULL, 6, 2, 2, '');

-- --------------------------------------------------------

--
-- Table structure for table `mortality`
--

CREATE TABLE `mortality` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `bloodline` varchar(50) DEFAULT NULL,
  `wing_band` varchar(20) DEFAULT NULL,
  `leg_band` varchar(20) DEFAULT NULL,
  `cause_of_death` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mortality`
--

INSERT INTO `mortality` (`id`, `branch_id`, `date`, `bloodline`, `wing_band`, `leg_band`, `cause_of_death`) VALUES
(4, 3, '2023-02-05', '1', '242', '2424', 'talo');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `no_of_days` int(11) DEFAULT NULL,
  `total_monthly_salary` decimal(10,2) DEFAULT 0.00,
  `cash_advance1` decimal(10,2) DEFAULT 0.00,
  `sss` decimal(10,2) DEFAULT 0.00,
  `philhealth` decimal(10,2) DEFAULT 0.00,
  `pagibig` decimal(10,2) DEFAULT 0.00,
  `cash_advance2` decimal(10,2) DEFAULT 0.00,
  `total_amount_received` decimal(10,2) DEFAULT 0.00,
  `payroll_date` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`payroll_id`, `employee_id`, `branch_id`, `no_of_days`, `total_monthly_salary`, `cash_advance1`, `sss`, `philhealth`, `pagibig`, `cash_advance2`, `total_amount_received`, `payroll_date`) VALUES
(1, 1, 1, 26, 48000.00, 1000.00, 900.00, 900.00, 900.00, 0.00, 44300.00, '2025-09-28'),
(2, 1, 1, 26, 30000.00, 1000.00, 1000.00, 1000.00, 1000.00, 1000.00, 25000.00, '2025-09-29'),
(3, 1, 1, 26, 30000.00, 1000.00, 900.00, 900.00, 0.00, 0.00, 27200.00, '2024-07-07'),
(4, 1, 1, 26, 30000.00, 0.00, 900.00, 900.00, 900.00, 0.00, 27300.00, '2024-09-09');

-- --------------------------------------------------------

--
-- Table structure for table `pens`
--

CREATE TABLE `pens` (
  `pen_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pens`
--

INSERT INTO `pens` (`pen_number`) VALUES
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9);

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE `purchase` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `supplier` text DEFAULT NULL,
  `qty` text DEFAULT NULL,
  `unit` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `receipt_number` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase`
--

INSERT INTO `purchase` (`id`, `branch_id`, `date`, `supplier`, `qty`, `unit`, `description`, `amount`, `receipt_number`) VALUES
(1, 1, '2024-11-20', 'PALANCA', '10 bags', 'GMP3 MAINTENANCE', '50 KG', 14950.00, 258310),
(2, 1, '2025-11-20', 'PALANCA', '10 bags', 'T-BIRD BABY BOOSTER ', '24*1', 11180.00, 258309),
(3, 1, '2025-07-17', 'PALANCA', '10 bags', 'T-BIRD BABY BOOSTER ', '24*1', 11180.00, 258309),
(4, 2, '2025-11-20', 'PALANCA', '20 bags', 'T-BIRD BABY BOOSTER ', '24*1', 11180.00, 258309),
(5, 3, '2025-11-20', 'PALANCA', '4 bags', 'T-BIRD BABY BOOSTER ', '24*1', 10800.00, 258310),
(6, 3, '2025-11-20', 'PALANCA', '4 bags', 'T-BIRD BABY BOOSTER ', '24*1', 20800.00, 258367),
(7, 1, '2024-11-20', 'PALANCA', '1o bags', 'T-BIRD BABY BOOSTER ', '24*1', 20800.00, 258367),
(8, 1, '2025-11-21', 'PALANCA', '10 bags', 'GMP3 MAINTENANCE', '50 KG', 14950.00, 258310),
(9, 1, '2025-11-23', 'palanca', '10 kg', 'T-BIRD BABY BOOSTER', '25', 12000.00, 258323),
(10, 3, '2025-02-09', 'PALANCA', '10 bags', 'GMP3 MAINTENANCE', '50 KG', 14950.00, 258310),
(11, 1, '2025-08-07', 'PALANCA', '10 bags', 'T-BIRD BABY BOOSTER ', '25', 20000.00, 258310);

-- --------------------------------------------------------

--
-- Table structure for table `sale`
--

CREATE TABLE `sale` (
  `sale_id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `buyer` varchar(100) NOT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale`
--

INSERT INTO `sale` (`sale_id`, `sale_date`, `buyer`, `amount`, `remarks`) VALUES
(13, '2025-09-01', 'Juan Dela Cruz', 1500.00, 'First-time buyer'),
(14, '2025-09-05', 'Maria Santos', 2750.50, 'Repeat customer'),
(15, '2025-09-07', 'Pedro Ramirez', 3200.00, 'Bought multiple gamefowls'),
(16, '2025-09-12', 'Ana Lopez', 1800.00, 'Special discount'),
(17, '2025-09-15', 'Carlos Mendoza', 2500.00, 'Cash payment'),
(29, '2023-02-07', 'fheu', 20000.00, ''),
(31, '2025-11-12', 'fheu', 20000.00, ''),
(32, '2025-02-10', 'fheu', 20000.00, ''),
(33, '2024-02-10', 'fheu', 20000.00, ''),
(34, '2025-12-10', 'marlon', 14950.00, ''),
(35, '2025-02-11', 'hanna', 123.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `sale_wingbands`
--

CREATE TABLE `sale_wingbands` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `wingband` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_wingbands`
--

INSERT INTO `sale_wingbands` (`id`, `sale_id`, `wingband`) VALUES
(45, 13, 'WB-1001'),
(46, 13, 'WB-1002'),
(47, 14, 'WB-2001'),
(48, 14, 'WB-2002'),
(49, 14, 'WB-2003'),
(50, 15, 'WB-3001'),
(51, 16, 'WB-4001'),
(52, 16, 'WB-4002'),
(53, 17, 'WB-5001'),
(54, 17, 'WB-5002'),
(55, 17, 'WB-5003'),
(80, 29, '7136173'),
(81, 29, '8278246'),
(82, 29, '8236486'),
(83, 29, '183736'),
(93, 32, '7136173'),
(94, 33, '7136173'),
(95, 34, '24245'),
(96, 31, '71361'),
(97, 31, '1211'),
(98, 35, '131');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bloodline`
--
ALTER TABLE `bloodline`
  ADD PRIMARY KEY (`bloodline_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `broodcocks`
--
ALTER TABLE `broodcocks`
  ADD PRIMARY KEY (`cock_id`),
  ADD KEY `pen_number` (`pen_number`),
  ADD KEY `bloodline_id` (`bloodline_id`);

--
-- Indexes for table `broodhens`
--
ALTER TABLE `broodhens`
  ADD PRIMARY KEY (`hen_id`),
  ADD KEY `pen_number` (`pen_number`),
  ADD KEY `bloodline_id` (`bloodline_id`);

--
-- Indexes for table `disease_records`
--
ALTER TABLE `disease_records`
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indexes for table `markings`
--
ALTER TABLE `markings`
  ADD PRIMARY KEY (`marking_id`),
  ADD KEY `pen_number` (`pen_number`),
  ADD KEY `cock_id` (`cock_id`),
  ADD KEY `hen_id` (`hen_id`),
  ADD KEY `bloodline_id` (`bloodline_id`);

--
-- Indexes for table `mortality`
--
ALTER TABLE `mortality`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `pens`
--
ALTER TABLE `pens`
  ADD PRIMARY KEY (`pen_number`);

--
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `sale`
--
ALTER TABLE `sale`
  ADD PRIMARY KEY (`sale_id`);

--
-- Indexes for table `sale_wingbands`
--
ALTER TABLE `sale_wingbands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bloodline`
--
ALTER TABLE `bloodline`
  MODIFY `bloodline_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `broodcocks`
--
ALTER TABLE `broodcocks`
  MODIFY `cock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `broodhens`
--
ALTER TABLE `broodhens`
  MODIFY `hen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `markings`
--
ALTER TABLE `markings`
  MODIFY `marking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mortality`
--
ALTER TABLE `mortality`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pens`
--
ALTER TABLE `pens`
  MODIFY `pen_number` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sale`
--
ALTER TABLE `sale`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `sale_wingbands`
--
ALTER TABLE `sale_wingbands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `broodcocks`
--
ALTER TABLE `broodcocks`
  ADD CONSTRAINT `broodcocks_ibfk_2` FOREIGN KEY (`bloodline_id`) REFERENCES `bloodline` (`bloodline_id`);

--
-- Constraints for table `broodhens`
--
ALTER TABLE `broodhens`
  ADD CONSTRAINT `broodhens_ibfk_2` FOREIGN KEY (`bloodline_id`) REFERENCES `bloodline` (`bloodline_id`);

--
-- Constraints for table `disease_records`
--
ALTER TABLE `disease_records`
  ADD CONSTRAINT `disease_records_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`);

--
-- Constraints for table `markings`
--
ALTER TABLE `markings`
  ADD CONSTRAINT `markings_ibfk_1` FOREIGN KEY (`pen_number`) REFERENCES `pens` (`pen_number`),
  ADD CONSTRAINT `markings_ibfk_2` FOREIGN KEY (`cock_id`) REFERENCES `broodcocks` (`cock_id`),
  ADD CONSTRAINT `markings_ibfk_3` FOREIGN KEY (`hen_id`) REFERENCES `broodhens` (`hen_id`),
  ADD CONSTRAINT `markings_ibfk_4` FOREIGN KEY (`bloodline_id`) REFERENCES `bloodline` (`bloodline_id`);

--
-- Constraints for table `mortality`
--
ALTER TABLE `mortality`
  ADD CONSTRAINT `mortality_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`);

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase`
--
ALTER TABLE `purchase`
  ADD CONSTRAINT `purchase_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`);

--
-- Constraints for table `sale_wingbands`
--
ALTER TABLE `sale_wingbands`
  ADD CONSTRAINT `sale_wingbands_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sale` (`sale_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
