-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 10:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lab_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CustomerID` varchar(10) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Age` int(11) DEFAULT NULL,
  `Gender` char(1) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `ContactNo` varchar(15) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `Name`, `Age`, `Gender`, `Email`, `ContactNo`, `Address`) VALUES
('CUST001', 'hannah', 23, 'G', 'hanna@gmail', '12334', 'Dhaka bangladesh'),
('CUST002', 'mauni', 21, 'M', 'mauni@gmail.com', '12344556', 'cambodia'),
('CUST003', 'hoho', 14, 'F', 'hoho@gmail', '12344', 'ctg'),
('CUST006', 'channi', 21, 'F', 'chan@gmail.com', '0988234', 'Corner Street 105/Street 276, Phnom Penh'),
('CUST007', 'amy', 58, 'F', 'amy@gmail.com', '8739465', 'myanmar'),
('CUST008', 'amy', 58, 'F', 'amy@gmail.com', '8739465', 'myanmar');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EmployeeID` varchar(10) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Specialization` varchar(50) DEFAULT NULL,
  `HireDate` date DEFAULT NULL,
  `ContactNo` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `garmenttype`
--

CREATE TABLE `garmenttype` (
  `GarmentTypeID` int(11) NOT NULL,
  `Name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement`
--

CREATE TABLE `measurement` (
  `MeasurementID` int(11) NOT NULL,
  `MeasurementDate` date DEFAULT NULL,
  `CustomerID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` varchar(10) DEFAULT NULL,
  `OrderDate` date DEFAULT NULL,
  `OrderStatus` enum('Pending','In Progress','Completed') DEFAULT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`OrderID`, `CustomerID`, `OrderDate`, `OrderStatus`, `TotalAmount`) VALUES
(0, 'CUST002', NULL, 'In Progress', NULL),
(1, 'CUST001', '2025-11-06', 'Pending', 2555.00);

-- --------------------------------------------------------

--
-- Table structure for table `orderitem`
--

CREATE TABLE `orderitem` (
  `OrderItemID` int(11) NOT NULL,
  `OrderID` int(11) DEFAULT NULL,
  `GarmentTypeID` int(11) DEFAULT NULL,
  `MeasurementID` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `UnitPrice` decimal(10,2) DEFAULT NULL,
  `ItemTotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` varchar(10) NOT NULL,
  `OrderID` int(11) DEFAULT NULL,
  `PaymentDate` date DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `PaymentMode` enum('Cash','Card','Online') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EmployeeID`);

--
-- Indexes for table `garmenttype`
--
ALTER TABLE `garmenttype`
  ADD PRIMARY KEY (`GarmentTypeID`);

--
-- Indexes for table `measurement`
--
ALTER TABLE `measurement`
  ADD PRIMARY KEY (`MeasurementID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `GarmentTypeID` (`GarmentTypeID`),
  ADD KEY `MeasurementID` (`MeasurementID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `measurement`
--
ALTER TABLE `measurement`
  ADD CONSTRAINT `measurement_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Constraints for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD CONSTRAINT `orderitem_ibfk_2` FOREIGN KEY (`GarmentTypeID`) REFERENCES `garmenttype` (`GarmentTypeID`),
  ADD CONSTRAINT `orderitem_ibfk_3` FOREIGN KEY (`MeasurementID`) REFERENCES `measurement` (`MeasurementID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
