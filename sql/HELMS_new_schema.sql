-- Relational schema: Enterprise Logistics Management System (HELMS)
-- Generated from POST_VIVA_-_NEW_RELATIONAL_SCHEMA.drawio
-- Structure plus the existing sample data, migrated to the new schema.
-- Layout follows the phpMyAdmin dump of the previous schema.
--
-- Generation Time: Nov 06, 2026
-- Server version: 10.4.32-MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `HELMS`
--
CREATE DATABASE IF NOT EXISTS `HELMS` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `HELMS`;

-- --------------------------------------------------------


--
-- Table structure for table `CorporateAgreement`
--

CREATE TABLE `CorporateAgreement` (
  `agreementId` varchar(20) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `discountRate` decimal(5,2) DEFAULT NULL,
  `paymentTerms` varchar(100) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `corporateCustomerId` varchar(20) NOT NULL,
  `financeMgrId` varchar(20) DEFAULT NULL,
  `csMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CorporateAgreement`
--

INSERT INTO `CorporateAgreement` (`agreementId`, `startDate`, `endDate`, `terms`, `discountRate`, `paymentTerms`, `status`, `corporateCustomerId`, `financeMgrId`, `csMgrId`) VALUES
('AGR-2026-000001', '2026-01-01', '2026-12-31', 'Volume-based discount; monthly invoicing.', 10.00, 'NET_30', 'ACTIVE', 'USR-000015', 'USR-000006', 'USR-000003');

-- --------------------------------------------------------

--
-- Table structure for table `CorporateCustomer`
--

CREATE TABLE `CorporateCustomer` (
  `userId` varchar(20) NOT NULL,
  `companyName` varchar(150) NOT NULL,
  `contactPerson` varchar(150) DEFAULT NULL,
  `csMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CorporateCustomer`
--

INSERT INTO `CorporateCustomer` (`userId`, `companyName`, `contactPerson`, `csMgrId`) VALUES
('USR-000015', 'Lanka Tea Exporters (Pvt) Ltd', 'Nimal Herath', 'USR-000003');

-- --------------------------------------------------------

--
-- Table structure for table `CostEstimate`
--

CREATE TABLE `CostEstimate` (
  `estimateId` varchar(20) NOT NULL,
  `baseCharge` decimal(12,2) NOT NULL,
  `surcharges` decimal(12,2) NOT NULL DEFAULT 0.00,
  `totalEstimate` decimal(12,2) NOT NULL,
  `calculatedAt` datetime NOT NULL,
  `pickupId` varchar(20) DEFAULT NULL,
  `ruleId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CostEstimate`
--

INSERT INTO `CostEstimate` (`estimateId`, `baseCharge`, `surcharges`, `totalEstimate`, `calculatedAt`, `pickupId`, `ruleId`) VALUES
('EST-000001', 1220.00, 250.00, 1470.00, '2026-10-09 09:05:00', 'PCK-2026-000051', 'PR-002'),
('EST-000002', 450.00, 0.00, 450.00, '2026-10-10 09:05:00', 'PCK-2026-000052', 'PR-001'),
('EST-000003', 1730.00, 0.00, 1730.00, '2026-10-11 09:05:00', 'PCK-2026-000053', 'PR-003'),
('EST-000004', 518.00, 0.00, 518.00, '2026-10-12 09:05:00', 'PCK-2026-000054', 'PR-001'),
('EST-000005', 1110.00, 0.00, 1110.00, '2026-10-15 09:05:00', 'PCK-2026-000055', 'PR-003'),
('EST-000006', 486.00, 0.00, 486.00, '2026-10-17 09:05:00', 'PCK-2026-000056', 'PR-001');

-- --------------------------------------------------------

--
-- Table structure for table `Customer`
--

CREATE TABLE `Customer` (
  `userId` varchar(20) NOT NULL,
  `contactNumber` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Customer`
--

INSERT INTO `Customer` (`userId`, `contactNumber`, `email`, `address`) VALUES
('USR-000013', '+94771234013', 'amal.r@example.com', '23 Galle Road, Dehiwala'),
('USR-000014', '+94771234014', 'sachini.w@example.com', '78 Temple Road, Mount Lavinia'),
('USR-000015', '+94112223015', 'orders@lankatea.example.com', '100 Duke Street, Colombo 01');

-- --------------------------------------------------------

--
-- Table structure for table `CustomerReport`
--

CREATE TABLE `CustomerReport` (
  `reportId` varchar(20) NOT NULL,
  `newCustomers` int(11) DEFAULT NULL,
  `activeCustomers` int(11) DEFAULT NULL,
  `corporateCustomers` int(11) DEFAULT NULL,
  `csMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CustomerReport`
--

INSERT INTO `CustomerReport` (`reportId`, `newCustomers`, `activeCustomers`, `corporateCustomers`, `csMgrId`) VALUES
('RPT-CUS-2026-10', 3, 3, 1, 'USR-000003');

-- --------------------------------------------------------

--
-- Table structure for table `CustomerServiceManager`
--

CREATE TABLE `CustomerServiceManager` (
  `userId` varchar(20) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CustomerServiceManager`
--

INSERT INTO `CustomerServiceManager` (`userId`, `region`, `department`) VALUES
('USR-000003', 'Western Province', 'Customer Service');

-- --------------------------------------------------------

--
-- Table structure for table `CustomerServiceOfficer`
--

CREATE TABLE `CustomerServiceOfficer` (
  `userId` varchar(20) NOT NULL,
  `deskNumber` varchar(20) DEFAULT NULL,
  `shift` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CustomerServiceOfficer`
--

INSERT INTO `CustomerServiceOfficer` (`userId`, `deskNumber`, `shift`) VALUES
('USR-000003', NULL, NULL),
('USR-000004', 'D-04', 'MORNING'),
('USR-000005', 'D-05', 'EVENING');

-- --------------------------------------------------------

--
-- Table structure for table `Delivery`
--

CREATE TABLE `Delivery` (
  `deliveryId` varchar(20) NOT NULL,
  `direction` varchar(30) DEFAULT NULL,
  `deliveryAddress` varchar(255) DEFAULT NULL,
  `deliveryDate` date DEFAULT NULL,
  `estimatedFrom` datetime DEFAULT NULL,
  `estimatedTo` datetime DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `failedAttemptCount` int(11) NOT NULL DEFAULT 0,
  `returnCostBearer` varchar(30) DEFAULT NULL,
  `deliveryManifestId` varchar(20) DEFAULT NULL,
  `driverManifestId` varchar(20) DEFAULT NULL,
  `issueId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Delivery`
--

INSERT INTO `Delivery` (`deliveryId`, `direction`, `deliveryAddress`, `deliveryDate`, `estimatedFrom`, `estimatedTo`, `status`, `failedAttemptCount`, `returnCostBearer`, `deliveryManifestId`, `driverManifestId`, `issueId`) VALUES
('DLV-2026-000201', 'OUTBOUND', '88 Church Road, Jaffna', '2026-11-02', '2026-11-02 09:00:00', '2026-11-02 13:00:00', 'SCHEDULED', 0, NULL, 'DM-2026-000002', 'DRM-2026-000002', NULL),
('DLV-2026-000202', 'OUTBOUND', '15 Lake Road, Kandy', '2026-11-01', '2026-11-01 09:00:00', '2026-11-01 13:00:00', 'IN_TRANSIT', 0, NULL, 'DM-2026-000002', 'DRM-2026-000002', NULL),
('DLV-2026-000203', 'OUTBOUND', '9 Hospital Road, Galle', '2026-10-30', '2026-10-30 09:00:00', '2026-10-30 13:00:00', 'DELIVERED', 0, NULL, 'DM-2026-000001', 'DRM-2026-000001', 'ISS-2026-000001'),
('DLV-2026-000204', 'OUTBOUND', '67 Beach Road, Negombo', '2026-10-30', '2026-10-30 09:00:00', '2026-10-30 13:00:00', 'FAILED', 3, 'CUSTOMER', 'DM-2026-000001', 'DRM-2026-000001', NULL),
('DLV-2026-000205', 'RETURN_TO_SENDER', '23 Galle Road, Dehiwala', '2026-11-01', '2026-11-01 09:00:00', '2026-11-01 13:00:00', 'IN_TRANSIT', 0, 'CUSTOMER', 'DM-2026-000002', 'DRM-2026-000002', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `DeliveryAttempt`
--

CREATE TABLE `DeliveryAttempt` (
  `attemptId` varchar(20) NOT NULL,
  `attemptNumber` int(11) NOT NULL,
  `attemptedAt` datetime DEFAULT NULL,
  `arrivedAt` datetime DEFAULT NULL,
  `outcome` varchar(30) DEFAULT NULL,
  `failureReason` varchar(255) DEFAULT NULL,
  `nextAttemptAt` datetime DEFAULT NULL,
  `deliveryId` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `DeliveryAttempt`
--

INSERT INTO `DeliveryAttempt` (`attemptId`, `attemptNumber`, `attemptedAt`, `arrivedAt`, `outcome`, `failureReason`, `nextAttemptAt`, `deliveryId`) VALUES
('ATT-000001', 1, '2026-10-30 11:20:00', '2026-10-30 11:15:00', 'SUCCESS', NULL, NULL, 'DLV-2026-000203'),
('ATT-000002', 1, '2026-10-28 10:05:00', '2026-10-28 10:00:00', 'FAILED', 'Receiver not available', '2026-10-29 10:00:00', 'DLV-2026-000204'),
('ATT-000003', 2, '2026-10-29 10:10:00', '2026-10-29 10:05:00', 'FAILED', 'Receiver phone unreachable', '2026-10-30 10:00:00', 'DLV-2026-000204'),
('ATT-000004', 3, '2026-10-30 10:12:00', '2026-10-30 10:08:00', 'FAILED', 'Receiver not available', NULL, 'DLV-2026-000204'),
('ATT-000005', 1, NULL, NULL, 'PENDING', NULL, NULL, 'DLV-2026-000202');

-- --------------------------------------------------------

--
-- Table structure for table `DeliveryManifest`
--

CREATE TABLE `DeliveryManifest` (
  `deliveryManifestId` varchar(20) NOT NULL,
  `manifestDate` date NOT NULL,
  `route` varchar(255) DEFAULT NULL,
  `manifestStatus` varchar(30) DEFAULT NULL,
  `transportMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `DeliveryManifest`
--

INSERT INTO `DeliveryManifest` (`deliveryManifestId`, `manifestDate`, `route`, `manifestStatus`, `transportMgrId`) VALUES
('DM-2026-000001', '2026-10-30', 'Colombo Central - Dehiwala - Mount Lavinia', 'COMPLETED', 'USR-000008'),
('DM-2026-000002', '2026-11-01', 'Colombo Central - Kandy', 'IN_PROGRESS', 'USR-000008');

-- --------------------------------------------------------

--
-- Table structure for table `DeliveryReport`
--

CREATE TABLE `DeliveryReport` (
  `reportId` varchar(20) NOT NULL,
  `totalDeliveries` int(11) DEFAULT NULL,
  `failedDeliveries` int(11) DEFAULT NULL,
  `returnedParcels` int(11) DEFAULT NULL,
  `transportMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `DeliveryReport`
--

INSERT INTO `DeliveryReport` (`reportId`, `totalDeliveries`, `failedDeliveries`, `returnedParcels`, `transportMgrId`) VALUES
('RPT-DLV-2026-10', 5, 1, 2, 'USR-000008');

-- --------------------------------------------------------

--
-- Table structure for table `DeliveryVerification`
--

CREATE TABLE `DeliveryVerification` (
  `verificationId` varchar(20) NOT NULL,
  `pinHash` varchar(255) NOT NULL,
  `generatedAt` datetime NOT NULL,
  `expiresAt` datetime NOT NULL,
  `verifiedAt` datetime DEFAULT NULL,
  `attemptCount` int(11) NOT NULL DEFAULT 0,
  `status` varchar(30) DEFAULT NULL,
  `attemptId` varchar(20) DEFAULT NULL,
  `receiverLinkId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `DeliveryVerification`
--

INSERT INTO `DeliveryVerification` (`verificationId`, `pinHash`, `generatedAt`, `expiresAt`, `verifiedAt`, `attemptCount`, `status`, `attemptId`, `receiverLinkId`) VALUES
('VER-000001', '$2b$10$dummypin000000000000000000000000000000000000000000001', '2026-10-30 11:00:00', '2026-10-30 12:00:00', '2026-10-30 11:18:00', 1, 'VERIFIED', 'ATT-000001', 'LNK-000008'),
('VER-000002', '$2b$10$dummypin000000000000000000000000000000000000000000002', '2026-11-01 08:30:00', '2026-11-01 18:00:00', NULL, 0, 'ACTIVE', 'ATT-000005', 'LNK-000007'),
('VER-000003', '$2b$10$dummypin000000000000000000000000000000000000000000003', '2026-10-30 09:50:00', '2026-10-30 10:50:00', NULL, 2, 'EXPIRED', 'ATT-000004', 'LNK-000009');

-- --------------------------------------------------------

--
-- Table structure for table `Driver`
--

CREATE TABLE `Driver` (
  `userId` varchar(20) NOT NULL,
  `licenseNumber` varchar(50) NOT NULL,
  `contactNumber` varchar(30) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `transportMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Driver`
--

INSERT INTO `Driver` (`userId`, `licenseNumber`, `contactNumber`, `status`, `transportMgrId`) VALUES
('USR-000009', 'B1234567', '+94771110009', 'ON_DUTY', 'USR-000008'),
('USR-000010', 'B7654321', '+94771110010', 'AVAILABLE', 'USR-000008');

-- --------------------------------------------------------

--
-- Table structure for table `DriverManifest`
--

CREATE TABLE `DriverManifest` (
  `driverManifestId` varchar(20) NOT NULL,
  `assignedDate` date NOT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `transportMgrId` varchar(20) DEFAULT NULL,
  `driverId` varchar(20) DEFAULT NULL,
  `vehicleId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `DriverManifest`
--

INSERT INTO `DriverManifest` (`driverManifestId`, `assignedDate`, `schedule`, `status`, `transportMgrId`, `driverId`, `vehicleId`) VALUES
('DRM-2026-000001', '2026-10-30', '08:00-16:00', 'COMPLETED', 'USR-000008', 'USR-000009', 'VEH-001'),
('DRM-2026-000002', '2026-11-01', '07:00-17:00', 'IN_PROGRESS', 'USR-000008', 'USR-000010', 'VEH-002');

-- --------------------------------------------------------

--
-- Table structure for table `FinanceManager`
--

CREATE TABLE `FinanceManager` (
  `userId` varchar(20) NOT NULL,
  `approvalLimit` decimal(12,2) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `FinanceManager`
--

INSERT INTO `FinanceManager` (`userId`, `approvalLimit`, `department`) VALUES
('USR-000006', 500000.00, 'Finance');

-- --------------------------------------------------------

--
-- Table structure for table `FinanceOfficer`
--

CREATE TABLE `FinanceOfficer` (
  `userId` varchar(20) NOT NULL,
  `contactNumber` varchar(30) DEFAULT NULL,
  `shift` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `FinanceOfficer`
--

INSERT INTO `FinanceOfficer` (`userId`, `contactNumber`, `shift`) VALUES
('USR-000006', NULL, NULL),
('USR-000007', '+94112345007', 'DAY');

-- --------------------------------------------------------

--
-- Table structure for table `IndividualCustomer`
--

CREATE TABLE `IndividualCustomer` (
  `userId` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `nic` varchar(20) DEFAULT NULL,
  `csMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `IndividualCustomer`
--

INSERT INTO `IndividualCustomer` (`userId`, `name`, `nic`, `csMgrId`) VALUES
('USR-000013', 'Amal Rathnayake', '199012345678', 'USR-000003'),
('USR-000014', 'Sachini Wijesinghe', '199545678912', 'USR-000003');

-- --------------------------------------------------------

--
-- Table structure for table `Invoice`
--

CREATE TABLE `Invoice` (
  `invoiceId` varchar(20) NOT NULL,
  `invoiceDate` date NOT NULL,
  `dueDate` date DEFAULT NULL,
  `sourceType` varchar(50) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `taxAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `totalAmount` decimal(12,2) NOT NULL,
  `status` varchar(30) DEFAULT NULL,
  `sentAt` datetime DEFAULT NULL,
  `customerId` varchar(20) NOT NULL,
  `csoId` varchar(20) DEFAULT NULL,
  `deliveryId` varchar(20) DEFAULT NULL,
  `financeOfficerId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Invoice`
--

INSERT INTO `Invoice` (`invoiceId`, `invoiceDate`, `dueDate`, `sourceType`, `subtotal`, `taxAmount`, `totalAmount`, `status`, `sentAt`, `customerId`, `csoId`, `deliveryId`, `financeOfficerId`) VALUES
('INV-2026-000301', '2026-10-15', '2026-10-29', 'PARCEL', 1278.00, 230.04, 1508.04, 'SENT', '2026-10-15 15:00:00', 'USR-000014', 'USR-000004', NULL, 'USR-000007'),
('INV-2026-000302', '2026-10-16', '2026-10-30', 'PARCEL', 630.00, 113.40, 743.40, 'PAID', '2026-10-16 15:00:00', 'USR-000015', 'USR-000005', 'DLV-2026-000201', 'USR-000007'),
('INV-2026-000303', '2026-10-17', '2026-10-31', 'PARCEL', 1110.00, 199.80, 1309.80, 'PAID', '2026-10-17 15:00:00', 'USR-000013', 'USR-000004', 'DLV-2026-000202', 'USR-000007'),
('INV-2026-000304', '2026-10-18', '2026-11-01', 'PARCEL', 1182.00, 212.76, 1394.76, 'PAID', '2026-10-18 15:00:00', 'USR-000014', 'USR-000005', 'DLV-2026-000203', 'USR-000007'),
('INV-2026-000305', '2026-10-19', '2026-11-02', 'PARCEL', 1086.00, 195.48, 1281.48, 'PAID', '2026-10-19 15:00:00', 'USR-000015', 'USR-000004', 'DLV-2026-000204', 'USR-000007'),
('INV-2026-000306', '2026-10-20', '2026-11-03', 'PARCEL', 450.00, 81.00, 531.00, 'PAID', '2026-10-20 15:00:00', 'USR-000013', 'USR-000005', 'DLV-2026-000205', 'USR-000007'),
('INV-2026-000307', '2026-10-21', '2026-11-04', 'PARCEL', 616.00, 110.88, 726.88, 'PAID', '2026-10-21 15:00:00', 'USR-000014', 'USR-000004', NULL, 'USR-000007'),
('INV-2026-000308', '2026-10-22', '2026-11-05', 'PARCEL', 698.00, 125.64, 823.64, 'PAID', '2026-10-22 15:00:00', 'USR-000015', 'USR-000005', NULL, 'USR-000007');

-- --------------------------------------------------------

--
-- Table structure for table `ManagingDirector`
--

CREATE TABLE `ManagingDirector` (
  `userId` varchar(20) NOT NULL,
  `appointmentDate` date DEFAULT NULL,
  `officeNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ManagingDirector`
--

INSERT INTO `ManagingDirector` (`userId`, `appointmentDate`, `officeNumber`) VALUES
('USR-000002', '2022-01-15', 'MD-001');

-- --------------------------------------------------------

--
-- Table structure for table `Notification`
--

CREATE TABLE `Notification` (
  `notificationId` varchar(20) NOT NULL,
  `notificationType` varchar(50) DEFAULT NULL,
  `channel` varchar(30) DEFAULT NULL,
  `recipientContact` varchar(150) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sentAt` datetime DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `customerId` varchar(20) DEFAULT NULL,
  `receiverLinkId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Notification`
--

INSERT INTO `Notification` (`notificationId`, `notificationType`, `channel`, `recipientContact`, `content`, `sentAt`, `status`, `customerId`, `receiverLinkId`) VALUES
('NTF-000001', 'PICKUP_SCHEDULED', 'EMAIL', 'amal.r@example.com', 'Your pickup PCK-2026-000051 is scheduled for 2 Nov. Parcel HEN-2026-000123.', '2026-10-31 09:05:00', 'SENT', 'USR-000013', NULL),
('NTF-000002', 'PAYMENT_REQUESTED', 'EMAIL', 'sachini.w@example.com', 'Invoice INV-2026-000301 for parcel HEN-2026-000127 is awaiting payment.', '2026-10-18 16:00:00', 'SENT', 'USR-000014', NULL),
('NTF-000003', 'DELIVERY_PIN', 'SMS', '+94712223306', 'Your delivery PIN for HEN-2026-000129 is ready. Open your tracking link.', '2026-11-01 08:30:00', 'SENT', NULL, 'LNK-000007'),
('NTF-000004', 'DELIVERED', 'SMS', '+94712223305', 'Parcel HEN-2026-000130 has been delivered.', '2026-10-30 11:20:00', 'SENT', NULL, 'LNK-000008'),
('NTF-000005', 'DELIVERY_FAILED', 'EMAIL', 'orders@lankatea.example.com', 'Delivery of HEN-2026-000131 failed after 3 attempts. Parcel returned to warehouse.', '2026-10-30 10:30:00', 'SENT', 'USR-000015', NULL),
('NTF-000006', 'UNCLAIMED_WARNING', 'EMAIL', 'sachini.w@example.com', 'Parcel HEN-2026-000133 is unclaimed and the hold period has ended.', '2026-10-31 17:05:00', 'SENT', 'USR-000014', NULL),
('NTF-000007', 'REFUND_EXPIRY', 'EMAIL', 'sachini.w@example.com', 'Your refund RFD-2026-000603 expires on 5 Nov.', '2026-11-01 09:00:00', 'SENT', 'USR-000014', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Parcel`
--

CREATE TABLE `Parcel` (
  `parcelId` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parcelCategory` varchar(50) DEFAULT NULL,
  `isFragile` tinyint(1) NOT NULL DEFAULT 0,
  `serviceType` varchar(50) DEFAULT NULL,
  `intakeMethod` varchar(50) DEFAULT NULL,
  `declaredWeight` decimal(10,2) DEFAULT NULL,
  `confirmedWeight` decimal(10,2) DEFAULT NULL,
  `dimensions` varchar(50) DEFAULT NULL,
  `detailsConfirmed` tinyint(1) NOT NULL DEFAULT 0,
  `parcelReceivedDate` date DEFAULT NULL,
  `storageLocationCode` varchar(30) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `holdExpiresAt` datetime DEFAULT NULL,
  `failureReason` varchar(255) DEFAULT NULL,
  `customerId` varchar(20) NOT NULL,
  `receiverId` varchar(20) NOT NULL,
  `csoId` varchar(20) DEFAULT NULL,
  `pickupId` varchar(20) DEFAULT NULL,
  `warehouseId` varchar(20) DEFAULT NULL,
  `receiverLinkId` varchar(20) DEFAULT NULL,
  `deliveryId` varchar(20) DEFAULT NULL,
  `ruleId` varchar(20) DEFAULT NULL,
  `warehouseOfficerId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Parcel`
--

INSERT INTO `Parcel` (`parcelId`, `description`, `parcelCategory`, `isFragile`, `serviceType`, `intakeMethod`, `declaredWeight`, `confirmedWeight`, `dimensions`, `detailsConfirmed`, `parcelReceivedDate`, `storageLocationCode`, `status`, `holdExpiresAt`, `failureReason`, `customerId`, `receiverId`, `csoId`, `pickupId`, `warehouseId`, `receiverLinkId`, `deliveryId`, `ruleId`, `warehouseOfficerId`) VALUES
('HEN-2026-000123', 'Ceramic tableware set', 'HOUSEHOLD', 1, 'EXPRESS', 'PICKUP', 4.50, NULL, '40x30x25', 0, NULL, NULL, 'PENDING_PICKUP', NULL, NULL, 'USR-000013', 'RCV-000001', 'USR-000004', 'PCK-2026-000051', NULL, 'LNK-000001', NULL, 'PR-002', NULL),
('HEN-2026-000124', 'Legal documents', 'DOCUMENT', 0, 'STANDARD', 'PICKUP', 0.50, NULL, '35x25x5', 0, '2026-10-11', 'A-01-01', 'RECEIVED_AT_WAREHOUSE', NULL, NULL, 'USR-000014', 'RCV-000002', 'USR-000005', 'PCK-2026-000052', 'WH-001', 'LNK-000002', NULL, 'PR-001', 'USR-000012'),
('HEN-2026-000125', 'Tea packs - export samples', 'COMMERCIAL', 0, 'STANDARD', 'PICKUP', 12.00, 12.30, '50x40x30', 1, '2026-10-12', 'C-02-07', 'DETAILS_CONFIRMED', NULL, NULL, 'USR-000015', 'RCV-000003', 'USR-000004', 'PCK-2026-000053', 'WH-001', 'LNK-000003', NULL, 'PR-003', 'USR-000012'),
('HEN-2026-000126', 'Handmade leather bag', 'PERSONAL', 0, 'STANDARD', 'PICKUP', 1.80, 1.85, '38x28x12', 1, '2026-10-13', 'A-03-12', 'PACKED', NULL, NULL, 'USR-000013', 'RCV-000004', 'USR-000005', 'PCK-2026-000054', 'WH-001', 'LNK-000004', NULL, 'PR-001', 'USR-000012'),
('HEN-2026-000127', 'Laptop (boxed)', 'ELECTRONICS', 1, 'EXPRESS', 'WALK_IN', 2.80, 2.90, '45x32x10', 1, '2026-10-14', 'B-01-04', 'AWAITING_PAYMENT', '2026-11-04 17:00:00', NULL, 'USR-000014', 'RCV-000005', 'USR-000004', NULL, 'WH-001', 'LNK-000005', NULL, 'PR-002', 'USR-000012'),
('HEN-2026-000128', 'Spice gift hamper', 'COMMERCIAL', 0, 'STANDARD', 'WALK_IN', 3.20, 3.25, '35x30x20', 1, '2026-10-15', 'B-02-02', 'READY_FOR_DISPATCH', NULL, NULL, 'USR-000015', 'RCV-000006', 'USR-000005', NULL, 'WH-001', 'LNK-000006', 'DLV-2026-000201', 'PR-001', 'USR-000012'),
('HEN-2026-000129', 'School books bundle', 'PERSONAL', 0, 'STANDARD', 'PICKUP', 6.00, 6.10, '40x30x30', 1, '2026-10-16', NULL, 'OUT_FOR_DELIVERY', NULL, NULL, 'USR-000013', 'RCV-000001', 'USR-000004', 'PCK-2026-000055', 'WH-001', 'LNK-000007', 'DLV-2026-000202', 'PR-003', 'USR-000012'),
('HEN-2026-000130', 'Glass vase', 'HOUSEHOLD', 1, 'EXPRESS', 'WALK_IN', 2.00, 2.10, '30x30x35', 1, '2026-10-17', NULL, 'DELIVERED', NULL, NULL, 'USR-000014', 'RCV-000002', 'USR-000005', NULL, 'WH-001', 'LNK-000008', 'DLV-2026-000203', 'PR-002', 'USR-000012'),
('HEN-2026-000131', 'Sports shoes', 'PERSONAL', 1, 'EXPRESS', 'PICKUP', 1.50, 1.45, '35x25x15', 1, '2026-10-18', 'C-01-05', 'RETURNED_TO_WAREHOUSE', NULL, 'Receiver unavailable after 3 attempts', 'USR-000015', 'RCV-000003', 'USR-000004', 'PCK-2026-000056', 'WH-001', 'LNK-000009', 'DLV-2026-000204', 'PR-001', 'USR-000012'),
('HEN-2026-000132', 'Wedding invitations', 'DOCUMENT', 0, 'STANDARD', 'WALK_IN', 0.90, 0.95, '32x24x8', 1, '2026-10-19', NULL, 'RETURN_TO_SENDER', NULL, 'Incorrect receiver address', 'USR-000013', 'RCV-000004', 'USR-000005', NULL, 'WH-001', 'LNK-000010', 'DLV-2026-000205', 'PR-001', 'USR-000012'),
('HEN-2026-000133', 'Wall clock', 'HOUSEHOLD', 1, 'STANDARD', 'WALK_IN', 1.20, 1.20, '33x33x10', 1, '2026-10-20', 'A-02-09', 'UNCLAIMED', '2026-10-31 17:00:00', 'Receiver did not collect within hold period', 'USR-000014', 'RCV-000005', 'USR-000004', NULL, 'WH-001', 'LNK-000011', NULL, 'PR-001', 'USR-000012'),
('HEN-2026-000134', 'Textile samples', 'COMMERCIAL', 0, 'STANDARD', 'WALK_IN', 4.00, 4.10, '45x35x20', 1, '2026-10-21', NULL, 'RELEASED_TO_SENDER', NULL, 'Sender requested release', 'USR-000015', 'RCV-000006', 'USR-000005', NULL, 'WH-001', 'LNK-000012', NULL, 'PR-001', 'USR-000012'),
('HEN-2026-000135', 'test item', 'DOCUMENT', 0, 'EXPRESS', 'WALK_IN', 200.00, NULL, '20x50x60', 0, '2026-11-01', NULL, 'RECEIVED_AT_WAREHOUSE', NULL, NULL, 'USR-000015', 'RCV-000002', NULL, NULL, 'WH-002', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Payment`
--

CREATE TABLE `Payment` (
  `paymentId` varchar(20) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `paymentDate` date NOT NULL,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `transactionRef` varchar(100) DEFAULT NULL,
  `financeOfficerId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Payment`
--

INSERT INTO `Payment` (`paymentId`, `amount`, `paymentDate`, `paymentMethod`, `transactionRef`, `financeOfficerId`) VALUES
('PAY-2026-000401', 743.40, '2026-10-17', 'BANK_TRANSFER', 'TXN-88120128', 'USR-000007'),
('PAY-2026-000402', 1309.80, '2026-10-18', 'CARD', 'TXN-88120129', 'USR-000007'),
('PAY-2026-000403', 1394.76, '2026-10-19', 'CASH', 'TXN-88120130', 'USR-000007'),
('PAY-2026-000404', 1281.48, '2026-10-20', 'BANK_TRANSFER', 'TXN-88120131', 'USR-000007'),
('PAY-2026-000405', 531.00, '2026-10-21', 'CARD', 'TXN-88120132', 'USR-000007'),
('PAY-2026-000406', 726.88, '2026-10-22', 'CASH', 'TXN-88120133', 'USR-000007'),
('PAY-2026-000407', 823.64, '2026-10-23', 'BANK_TRANSFER', 'TXN-88120134', 'USR-000007');

-- --------------------------------------------------------

--
-- Table structure for table `PaymentReport`
--

CREATE TABLE `PaymentReport` (
  `reportId` varchar(20) NOT NULL,
  `totalCollected` decimal(14,2) DEFAULT NULL,
  `totalRefunded` decimal(14,2) DEFAULT NULL,
  `expiredRefundTotal` decimal(14,2) DEFAULT NULL,
  `financeMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `PaymentReport`
--

INSERT INTO `PaymentReport` (`reportId`, `totalCollected`, `totalRefunded`, `expiredRefundTotal`, `financeMgrId`) VALUES
('RPT-PAY-2026-10', 6810.96, 823.64, 0.00, 'USR-000006');

-- --------------------------------------------------------

--
-- Table structure for table `PaymentSettlesInvoice`
--

CREATE TABLE `PaymentSettlesInvoice` (
  `paymentId` varchar(20) NOT NULL,
  `invoiceId` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `PaymentSettlesInvoice`
--

INSERT INTO `PaymentSettlesInvoice` (`paymentId`, `invoiceId`) VALUES
('PAY-2026-000401', 'INV-2026-000302'),
('PAY-2026-000402', 'INV-2026-000303'),
('PAY-2026-000403', 'INV-2026-000304'),
('PAY-2026-000404', 'INV-2026-000305'),
('PAY-2026-000405', 'INV-2026-000306'),
('PAY-2026-000406', 'INV-2026-000307'),
('PAY-2026-000407', 'INV-2026-000308');

-- --------------------------------------------------------

--
-- Table structure for table `Pickup`
--

CREATE TABLE `Pickup` (
  `pickupId` varchar(20) NOT NULL,
  `requestedAt` datetime NOT NULL,
  `pickupDate` date DEFAULT NULL,
  `preferredTimeWindow` varchar(50) DEFAULT NULL,
  `pickupAddress` varchar(255) NOT NULL,
  `status` varchar(30) DEFAULT NULL,
  `rejectionReason` varchar(255) DEFAULT NULL,
  `failureReason` varchar(255) DEFAULT NULL,
  `cancelledAt` datetime DEFAULT NULL,
  `customerId` varchar(20) NOT NULL,
  `csoId` varchar(20) DEFAULT NULL,
  `transportMgrId` varchar(20) DEFAULT NULL,
  `driverManifestId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Pickup`
--

INSERT INTO `Pickup` (`pickupId`, `requestedAt`, `pickupDate`, `preferredTimeWindow`, `pickupAddress`, `status`, `rejectionReason`, `failureReason`, `cancelledAt`, `customerId`, `csoId`, `transportMgrId`, `driverManifestId`) VALUES
('PCK-2026-000051', '2026-10-31 09:00:00', '2026-11-02', '09:00-12:00', '23 Galle Road, Dehiwala', 'SCHEDULED', NULL, NULL, NULL, 'USR-000013', 'USR-000004', 'USR-000008', NULL),
('PCK-2026-000052', '2026-10-10 09:00:00', '2026-10-11', '09:00-12:00', '78 Temple Road, Mount Lavinia', 'COMPLETED', NULL, NULL, NULL, 'USR-000014', 'USR-000005', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000053', '2026-10-11 09:00:00', '2026-10-12', '09:00-12:00', '100 Duke Street, Colombo 01', 'COMPLETED', NULL, NULL, NULL, 'USR-000015', 'USR-000004', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000054', '2026-10-12 09:00:00', '2026-10-13', '09:00-12:00', '23 Galle Road, Dehiwala', 'COMPLETED', NULL, NULL, NULL, 'USR-000013', 'USR-000005', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000055', '2026-10-15 09:00:00', '2026-10-16', '09:00-12:00', '23 Galle Road, Dehiwala', 'COMPLETED', NULL, NULL, NULL, 'USR-000013', 'USR-000004', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000056', '2026-10-17 09:00:00', '2026-10-18', '09:00-12:00', '100 Duke Street, Colombo 01', 'COMPLETED', NULL, NULL, NULL, 'USR-000015', 'USR-000004', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000057', '2026-10-30 16:45:00', NULL, '09:00-12:00', '23 Galle Road, Dehiwala', 'REJECTED', 'Address outside service zone', NULL, NULL, 'USR-000013', 'USR-000005', NULL, NULL),
('PCK-2026-000058', '2026-10-31 11:20:00', NULL, '13:00-16:00', '78 Temple Road, Mount Lavinia', 'CANCELLED', NULL, NULL, '2026-10-31 15:00:00', 'USR-000014', 'USR-000004', NULL, NULL),
('PCK-2026-000059', '2026-10-29 10:00:00', '2026-10-30', '09:00-12:00', '100 Duke Street, Colombo 01', 'FAILED', NULL, 'Customer not at pickup address', NULL, 'USR-000015', 'USR-000005', 'USR-000008', 'DRM-2026-000001');

-- --------------------------------------------------------

--
-- Table structure for table `PostDeliveryIssue`
--

CREATE TABLE `PostDeliveryIssue` (
  `issueId` varchar(20) NOT NULL,
  `issueType` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `submittedAt` datetime NOT NULL,
  `status` varchar(30) DEFAULT NULL,
  `faultParty` varchar(50) DEFAULT NULL,
  `decision` varchar(50) DEFAULT NULL,
  `decisionNotes` text DEFAULT NULL,
  `decidedAt` datetime DEFAULT NULL,
  `receiverLinkId` varchar(20) DEFAULT NULL,
  `csMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `PostDeliveryIssue`
--

INSERT INTO `PostDeliveryIssue` (`issueId`, `issueType`, `description`, `submittedAt`, `status`, `faultParty`, `decision`, `decisionNotes`, `decidedAt`, `receiverLinkId`, `csMgrId`) VALUES
('ISS-2026-000001', 'DAMAGED', 'Vase arrived with a cracked base.', '2026-10-31 10:15:00', 'RESOLVED', 'CARRIER', 'REFUND_APPROVED', 'Packaging photos confirm transit damage.', '2026-10-31 15:40:00', 'LNK-000008', 'USR-000003');

-- --------------------------------------------------------

--
-- Table structure for table `PricingRule`
--

CREATE TABLE `PricingRule` (
  `ruleId` varchar(20) NOT NULL,
  `serviceType` varchar(50) DEFAULT NULL,
  `zoneCode` varchar(20) DEFAULT NULL,
  `minWeight` decimal(10,2) DEFAULT NULL,
  `maxWeight` decimal(10,2) DEFAULT NULL,
  `baseFee` decimal(12,2) DEFAULT NULL,
  `additionalPerKgRate` decimal(12,2) DEFAULT NULL,
  `fragileSurcharge` decimal(12,2) DEFAULT NULL,
  `perAttemptCost` decimal(12,2) DEFAULT NULL,
  `effectiveFrom` date DEFAULT NULL,
  `effectiveTo` date DEFAULT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `PricingRule`
--

INSERT INTO `PricingRule` (`ruleId`, `serviceType`, `zoneCode`, `minWeight`, `maxWeight`, `baseFee`, `additionalPerKgRate`, `fragileSurcharge`, `perAttemptCost`, `effectiveFrom`, `effectiveTo`, `isActive`) VALUES
('PR-001', 'STANDARD', 'ALL', 0.00, 5.00, 450.00, 80.00, 150.00, 200.00, '2026-01-01', NULL, 1),
('PR-002', 'EXPRESS', 'ALL', 0.00, 5.00, 800.00, 120.00, 250.00, 350.00, '2026-01-01', NULL, 1),
('PR-003', 'STANDARD', 'ALL', 5.01, 20.00, 600.00, 100.00, 150.00, 250.00, '2026-01-01', NULL, 1),
('PR-004', 'EXPRESS', 'ALL', 5.01, 20.00, 1100.00, 150.00, 250.00, 400.00, '2026-01-01', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Receipt`
--

CREATE TABLE `Receipt` (
  `receiptId` varchar(20) NOT NULL,
  `issuedDate` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `paymentId` varchar(20) NOT NULL,
  `customerId` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Receipt`
--

INSERT INTO `Receipt` (`receiptId`, `issuedDate`, `amount`, `paymentId`, `customerId`) VALUES
('RCT-2026-000501', '2026-10-17', 743.40, 'PAY-2026-000401', 'USR-000015'),
('RCT-2026-000502', '2026-10-18', 1309.80, 'PAY-2026-000402', 'USR-000013'),
('RCT-2026-000503', '2026-10-19', 1394.76, 'PAY-2026-000403', 'USR-000014'),
('RCT-2026-000504', '2026-10-20', 1281.48, 'PAY-2026-000404', 'USR-000015'),
('RCT-2026-000505', '2026-10-21', 531.00, 'PAY-2026-000405', 'USR-000013'),
('RCT-2026-000506', '2026-10-22', 726.88, 'PAY-2026-000406', 'USR-000014'),
('RCT-2026-000507', '2026-10-23', 823.64, 'PAY-2026-000407', 'USR-000015');

-- --------------------------------------------------------

--
-- Table structure for table `Receiver`
--

CREATE TABLE `Receiver` (
  `receiverId` varchar(20) NOT NULL,
  `receiverName` varchar(150) NOT NULL,
  `receiverPhone` varchar(30) DEFAULT NULL,
  `receiverAddress` varchar(255) DEFAULT NULL,
  `zoneCode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Receiver`
--

INSERT INTO `Receiver` (`receiverId`, `receiverName`, `receiverPhone`, `receiverAddress`, `zoneCode`) VALUES
('RCV-000001', 'Kamal Dissanayake', '+94712223301', '15 Lake Road, Kandy', 'KDY-01'),
('RCV-000002', 'Priya Mendis', '+94712223302', '9 Hospital Road, Galle', 'GLE-01'),
('RCV-000003', 'Ishara Fonseka', '+94712223303', '67 Beach Road, Negombo', 'NGB-01'),
('RCV-000004', 'Tharindu Perera', '+94712223304', '5 Main Street, Matara', 'MTR-01'),
('RCV-000005', 'Shalini Kumar', '+94712223305', '21 Hill Street, Nuwara Eliya', 'NWE-01'),
('RCV-000006', 'Rohan Abeysekera', '+94712223306', '88 Church Road, Jaffna', 'JFN-01');

-- --------------------------------------------------------

--
-- Table structure for table `ReceiverLink`
--

CREATE TABLE `ReceiverLink` (
  `linkId` varchar(20) NOT NULL,
  `tokenHash` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL,
  `expiresAt` datetime NOT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT 1,
  `csoId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ReceiverLink`
--

INSERT INTO `ReceiverLink` (`linkId`, `tokenHash`, `createdAt`, `expiresAt`, `isActive`, `csoId`) VALUES
('LNK-000001', '$2b$10$dummytoken0000000000000000000000000000001', '2026-10-10 10:00:00', '2026-11-09 09:00:00', 1, 'USR-000004'),
('LNK-000002', '$2b$10$dummytoken0000000000000000000000000000002', '2026-10-11 10:00:00', '2026-11-10 09:00:00', 1, 'USR-000005'),
('LNK-000003', '$2b$10$dummytoken0000000000000000000000000000003', '2026-10-12 10:00:00', '2026-11-11 09:00:00', 1, 'USR-000004'),
('LNK-000004', '$2b$10$dummytoken0000000000000000000000000000004', '2026-10-13 10:00:00', '2026-11-12 09:00:00', 1, 'USR-000005'),
('LNK-000005', '$2b$10$dummytoken0000000000000000000000000000005', '2026-10-14 10:00:00', '2026-11-13 09:00:00', 1, 'USR-000004'),
('LNK-000006', '$2b$10$dummytoken0000000000000000000000000000006', '2026-10-15 10:00:00', '2026-11-14 09:00:00', 1, 'USR-000005'),
('LNK-000007', '$2b$10$dummytoken0000000000000000000000000000007', '2026-10-16 10:00:00', '2026-11-15 09:00:00', 1, 'USR-000004'),
('LNK-000008', '$2b$10$dummytoken0000000000000000000000000000008', '2026-10-17 10:00:00', '2026-11-16 09:00:00', 1, 'USR-000005'),
('LNK-000009', '$2b$10$dummytoken0000000000000000000000000000009', '2026-10-18 10:00:00', '2026-11-17 09:00:00', 1, 'USR-000004'),
('LNK-000010', '$2b$10$dummytoken0000000000000000000000000000010', '2026-10-19 10:00:00', '2026-11-18 09:00:00', 1, 'USR-000005'),
('LNK-000011', '$2b$10$dummytoken0000000000000000000000000000011', '2026-10-20 10:00:00', '2026-11-19 09:00:00', 1, 'USR-000004'),
('LNK-000012', '$2b$10$dummytoken0000000000000000000000000000012', '2026-10-21 10:00:00', '2026-11-20 09:00:00', 0, 'USR-000005');

-- --------------------------------------------------------

--
-- Table structure for table `Refund`
--

CREATE TABLE `Refund` (
  `refundId` varchar(20) NOT NULL,
  `refundReason` varchar(255) DEFAULT NULL,
  `originalAmount` decimal(12,2) DEFAULT NULL,
  `attemptCount` int(11) DEFAULT NULL,
  `returnCost` decimal(12,2) DEFAULT NULL,
  `returnOption` varchar(50) DEFAULT NULL,
  `returnDeliveryFee` decimal(12,2) DEFAULT NULL,
  `refundMethod` varchar(50) DEFAULT NULL,
  `collectionBranch` varchar(100) DEFAULT NULL,
  `requestedAt` datetime NOT NULL,
  `expiresAt` datetime DEFAULT NULL,
  `expiryNotifiedAt` datetime DEFAULT NULL,
  `expiryAcknowledged` tinyint(1) NOT NULL DEFAULT 0,
  `processedAt` datetime DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `accountHolderName` varchar(150) DEFAULT NULL,
  `bankName` varchar(100) DEFAULT NULL,
  `branchName` varchar(100) DEFAULT NULL,
  `accountNumber` varchar(50) DEFAULT NULL,
  `customerId` varchar(20) NOT NULL,
  `paymentId` varchar(20) DEFAULT NULL,
  `parcelId` varchar(20) DEFAULT NULL,
  `financeMgrId` varchar(20) DEFAULT NULL,
  `issueId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Refund`
--

INSERT INTO `Refund` (`refundId`, `refundReason`, `originalAmount`, `attemptCount`, `returnCost`, `returnOption`, `returnDeliveryFee`, `refundMethod`, `collectionBranch`, `requestedAt`, `expiresAt`, `expiryNotifiedAt`, `expiryAcknowledged`, `processedAt`, `status`, `accountHolderName`, `bankName`, `branchName`, `accountNumber`, `customerId`, `paymentId`, `parcelId`, `financeMgrId`, `issueId`) VALUES
('RFD-2026-000601', 'Damaged item in transit', 1394.76, 1, 0.00, 'NO_RETURN', 0.00, 'BANK_TRANSFER', NULL, '2026-10-31 15:45:00', '2026-12-31 23:59:59', NULL, 0, NULL, 'PENDING', 'Sachini Wijesinghe', 'Bank of Ceylon', 'Mount Lavinia', '0012345678', 'USR-000014', 'PAY-2026-000403', 'HEN-2026-000130', 'USR-000006', 'ISS-2026-000001'),
('RFD-2026-000602', 'Parcel released to sender', 823.64, 0, 0.00, 'COLLECT_FROM_BRANCH', 0.00, 'CASH', 'Colombo Central Hub', '2026-10-29 09:00:00', '2026-11-28 23:59:59', NULL, 1, '2026-10-30 14:00:00', 'PROCESSED', NULL, NULL, NULL, NULL, 'USR-000015', 'PAY-2026-000407', 'HEN-2026-000134', 'USR-000006', NULL),
('RFD-2026-000603', 'Unclaimed parcel - refund window', 726.88, 0, 0.00, 'COLLECT_FROM_BRANCH', 0.00, 'CASH', 'Colombo Central Hub', '2026-10-31 18:00:00', '2026-11-05 23:59:59', '2026-11-01 09:00:00', 0, NULL, 'PENDING', NULL, NULL, NULL, NULL, 'USR-000014', 'PAY-2026-000406', 'HEN-2026-000133', 'USR-000006', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Report`
--

CREATE TABLE `Report` (
  `reportId` varchar(20) NOT NULL,
  `generatedAt` datetime NOT NULL,
  `periodStart` date NOT NULL,
  `periodEnd` date NOT NULL,
  `directorId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Report`
--

INSERT INTO `Report` (`reportId`, `generatedAt`, `periodStart`, `periodEnd`, `directorId`) VALUES
('RPT-CUS-2026-10', '2026-11-01 06:15:00', '2026-10-01', '2026-10-31', 'USR-000002'),
('RPT-DLV-2026-10', '2026-11-01 06:05:00', '2026-10-01', '2026-10-31', 'USR-000002'),
('RPT-PAY-2026-10', '2026-11-01 06:00:00', '2026-10-01', '2026-10-31', 'USR-000002'),
('RPT-STO-2026-10', '2026-11-01 06:10:00', '2026-10-01', '2026-10-31', 'USR-000002');

-- --------------------------------------------------------

--
-- Table structure for table `StorageReport`
--

CREATE TABLE `StorageReport` (
  `reportId` varchar(20) NOT NULL,
  `parcelsStored` int(11) DEFAULT NULL,
  `unclaimedParcels` int(11) DEFAULT NULL,
  `capacityUtilisation` decimal(5,2) DEFAULT NULL,
  `warehouseMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `StorageReport`
--

INSERT INTO `StorageReport` (`reportId`, `parcelsStored`, `unclaimedParcels`, `capacityUtilisation`, `warehouseMgrId`) VALUES
('RPT-STO-2026-10', 11, 1, 24.80, 'USR-000011');

-- --------------------------------------------------------

--
-- Table structure for table `SystemAdmin`
--

CREATE TABLE `SystemAdmin` (
  `userId` varchar(20) NOT NULL,
  `accessLevel` varchar(50) NOT NULL,
  `lastLoginDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `SystemAdmin`
--

INSERT INTO `SystemAdmin` (`userId`, `accessLevel`, `lastLoginDate`) VALUES
('USR-000001', 'FULL', '2026-11-01 13:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `TrackingEvent`
--

CREATE TABLE `TrackingEvent` (
  `eventId` varchar(20) NOT NULL,
  `eventType` varchar(50) NOT NULL,
  `occurredAt` datetime NOT NULL,
  `locationDescription` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `isVisibleToReceiver` tinyint(1) NOT NULL DEFAULT 1,
  `actorType` varchar(50) DEFAULT NULL,
  `actorId` varchar(20) DEFAULT NULL,
  `parcelId` varchar(20) DEFAULT NULL,
  `receiverLinkId` varchar(20) DEFAULT NULL,
  `deliveryId` varchar(20) DEFAULT NULL,
  `warehouseId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `TrackingEvent`
--

INSERT INTO `TrackingEvent` (`eventId`, `eventType`, `occurredAt`, `locationDescription`, `remarks`, `isVisibleToReceiver`, `actorType`, `actorId`, `parcelId`, `receiverLinkId`, `deliveryId`, `warehouseId`) VALUES
('EVT-000001', 'PENDING_PICKUP', '2026-10-10 09:00:00', 'Customer address', 'Pickup scheduled', 1, 'CSO', 'USR-000004', 'HEN-2026-000123', 'LNK-000001', NULL, NULL),
('EVT-000002', 'PENDING_PICKUP', '2026-10-11 09:00:00', 'Customer address', 'Pickup scheduled', 1, 'CSO', 'USR-000005', 'HEN-2026-000124', 'LNK-000002', NULL, NULL),
('EVT-000003', 'RECEIVED_AT_WAREHOUSE', '2026-10-11 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000124', 'LNK-000002', NULL, 'WH-001'),
('EVT-000004', 'PENDING_PICKUP', '2026-10-12 09:00:00', 'Customer address', 'Pickup scheduled', 1, 'CSO', 'USR-000004', 'HEN-2026-000125', 'LNK-000003', NULL, NULL),
('EVT-000005', 'RECEIVED_AT_WAREHOUSE', '2026-10-12 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000125', 'LNK-000003', NULL, 'WH-001'),
('EVT-000006', 'DETAILS_CONFIRMED', '2026-10-13 01:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000125', 'LNK-000003', NULL, 'WH-001'),
('EVT-000007', 'PENDING_PICKUP', '2026-10-13 09:00:00', 'Customer address', 'Pickup scheduled', 1, 'CSO', 'USR-000005', 'HEN-2026-000126', 'LNK-000004', NULL, NULL),
('EVT-000008', 'RECEIVED_AT_WAREHOUSE', '2026-10-13 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000126', 'LNK-000004', NULL, 'WH-001'),
('EVT-000009', 'DETAILS_CONFIRMED', '2026-10-14 01:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000126', 'LNK-000004', NULL, 'WH-001'),
('EVT-000010', 'PACKED', '2026-10-14 09:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000126', 'LNK-000004', NULL, 'WH-001'),
('EVT-000011', 'RECEIVED_AT_WAREHOUSE', '2026-10-14 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000127', 'LNK-000005', NULL, 'WH-001'),
('EVT-000012', 'DETAILS_CONFIRMED', '2026-10-14 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000127', 'LNK-000005', NULL, 'WH-001'),
('EVT-000013', 'PACKED', '2026-10-15 01:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000127', 'LNK-000005', NULL, 'WH-001'),
('EVT-000014', 'AWAITING_PAYMENT', '2026-10-15 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', 0, 'CSO', 'USR-000004', 'HEN-2026-000127', 'LNK-000005', NULL, NULL),
('EVT-000015', 'RECEIVED_AT_WAREHOUSE', '2026-10-15 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000128', 'LNK-000006', NULL, 'WH-001'),
('EVT-000016', 'DETAILS_CONFIRMED', '2026-10-15 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000128', 'LNK-000006', NULL, 'WH-001'),
('EVT-000017', 'PACKED', '2026-10-16 01:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000128', 'LNK-000006', NULL, 'WH-001'),
('EVT-000018', 'AWAITING_PAYMENT', '2026-10-16 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', 0, 'CSO', 'USR-000005', 'HEN-2026-000128', 'LNK-000006', NULL, NULL),
('EVT-000019', 'READY_FOR_DISPATCH', '2026-10-16 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000128', 'LNK-000006', NULL, 'WH-001'),
('EVT-000020', 'PENDING_PICKUP', '2026-10-16 09:00:00', 'Customer address', 'Pickup scheduled', 1, 'CSO', 'USR-000004', 'HEN-2026-000129', 'LNK-000007', NULL, NULL),
('EVT-000021', 'RECEIVED_AT_WAREHOUSE', '2026-10-16 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000129', 'LNK-000007', NULL, 'WH-001'),
('EVT-000022', 'DETAILS_CONFIRMED', '2026-10-17 01:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000129', 'LNK-000007', NULL, 'WH-001'),
('EVT-000023', 'PACKED', '2026-10-17 09:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000129', 'LNK-000007', NULL, 'WH-001'),
('EVT-000024', 'AWAITING_PAYMENT', '2026-10-17 17:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', 0, 'CSO', 'USR-000004', 'HEN-2026-000129', 'LNK-000007', NULL, NULL),
('EVT-000025', 'READY_FOR_DISPATCH', '2026-10-18 01:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000129', 'LNK-000007', NULL, 'WH-001'),
('EVT-000026', 'OUT_FOR_DELIVERY', '2026-10-18 09:00:00', 'In transit', 'Loaded on vehicle', 1, 'DRIVER', 'USR-000009', 'HEN-2026-000129', 'LNK-000007', 'DLV-2026-000202', NULL),
('EVT-000027', 'RECEIVED_AT_WAREHOUSE', '2026-10-17 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000130', 'LNK-000008', NULL, 'WH-001'),
('EVT-000028', 'DETAILS_CONFIRMED', '2026-10-17 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000130', 'LNK-000008', NULL, 'WH-001'),
('EVT-000029', 'PACKED', '2026-10-18 01:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000130', 'LNK-000008', NULL, 'WH-001'),
('EVT-000030', 'AWAITING_PAYMENT', '2026-10-18 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', 0, 'CSO', 'USR-000005', 'HEN-2026-000130', 'LNK-000008', NULL, NULL),
('EVT-000031', 'READY_FOR_DISPATCH', '2026-10-18 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000130', 'LNK-000008', NULL, 'WH-001'),
('EVT-000032', 'OUT_FOR_DELIVERY', '2026-10-19 01:00:00', 'In transit', 'Loaded on vehicle', 1, 'DRIVER', 'USR-000009', 'HEN-2026-000130', 'LNK-000008', 'DLV-2026-000203', NULL),
('EVT-000033', 'DELIVERED', '2026-10-19 09:00:00', 'Receiver address', 'PIN verified, delivered', 1, 'DRIVER', 'USR-000009', 'HEN-2026-000130', 'LNK-000008', 'DLV-2026-000203', NULL),
('EVT-000034', 'PENDING_PICKUP', '2026-10-18 09:00:00', 'Customer address', 'Pickup scheduled', 1, 'CSO', 'USR-000004', 'HEN-2026-000131', 'LNK-000009', NULL, NULL),
('EVT-000035', 'RECEIVED_AT_WAREHOUSE', '2026-10-18 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000036', 'DETAILS_CONFIRMED', '2026-10-19 01:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000037', 'PACKED', '2026-10-19 09:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000038', 'AWAITING_PAYMENT', '2026-10-19 17:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', 0, 'CSO', 'USR-000004', 'HEN-2026-000131', 'LNK-000009', NULL, NULL),
('EVT-000039', 'READY_FOR_DISPATCH', '2026-10-20 01:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000040', 'OUT_FOR_DELIVERY', '2026-10-20 09:00:00', 'In transit', 'Loaded on vehicle', 1, 'DRIVER', 'USR-000009', 'HEN-2026-000131', 'LNK-000009', 'DLV-2026-000204', NULL),
('EVT-000041', 'DELIVERY_FAILED', '2026-10-20 17:00:00', 'Receiver address', 'Delivery attempts failed', 1, 'DRIVER', 'USR-000009', 'HEN-2026-000131', 'LNK-000009', 'DLV-2026-000204', NULL),
('EVT-000042', 'RETURNED_TO_WAREHOUSE', '2026-10-21 01:00:00', 'Colombo Central Hub', 'Returned after failed delivery', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000043', 'RECEIVED_AT_WAREHOUSE', '2026-10-19 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000044', 'DETAILS_CONFIRMED', '2026-10-19 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000045', 'PACKED', '2026-10-20 01:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000046', 'AWAITING_PAYMENT', '2026-10-20 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', 0, 'CSO', 'USR-000005', 'HEN-2026-000132', 'LNK-000010', NULL, NULL),
('EVT-000047', 'READY_FOR_DISPATCH', '2026-10-20 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000048', 'OUT_FOR_DELIVERY', '2026-10-21 01:00:00', 'In transit', 'Loaded on vehicle', 1, 'DRIVER', 'USR-000009', 'HEN-2026-000132', 'LNK-000010', NULL, NULL),
('EVT-000049', 'DELIVERY_FAILED', '2026-10-21 09:00:00', 'Receiver address', 'Delivery attempts failed', 1, 'DRIVER', 'USR-000009', 'HEN-2026-000132', 'LNK-000010', NULL, NULL),
('EVT-000050', 'RETURNED_TO_WAREHOUSE', '2026-10-21 17:00:00', 'Colombo Central Hub', 'Returned after failed delivery', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000051', 'RETURN_TO_SENDER', '2026-10-22 01:00:00', 'In transit', 'Returning to sender', 1, 'DRIVER', 'USR-000009', 'HEN-2026-000132', 'LNK-000010', 'DLV-2026-000205', NULL),
('EVT-000052', 'RECEIVED_AT_WAREHOUSE', '2026-10-20 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000053', 'DETAILS_CONFIRMED', '2026-10-20 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000054', 'PACKED', '2026-10-21 01:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000055', 'AWAITING_PAYMENT', '2026-10-21 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', 0, 'CSO', 'USR-000004', 'HEN-2026-000133', 'LNK-000011', NULL, NULL),
('EVT-000056', 'READY_FOR_DISPATCH', '2026-10-21 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000057', 'UNCLAIMED', '2026-10-22 01:00:00', 'Colombo Central Hub', 'Hold period expired, unclaimed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000058', 'RECEIVED_AT_WAREHOUSE', '2026-10-21 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000059', 'DETAILS_CONFIRMED', '2026-10-21 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000060', 'PACKED', '2026-10-22 01:00:00', 'Colombo Central Hub', 'Parcel packed', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000061', 'AWAITING_PAYMENT', '2026-10-22 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', 0, 'CSO', 'USR-000005', 'HEN-2026-000134', 'LNK-000012', NULL, NULL),
('EVT-000062', 'READY_FOR_DISPATCH', '2026-10-22 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000063', 'UNCLAIMED', '2026-10-23 01:00:00', 'Colombo Central Hub', 'Hold period expired, unclaimed', 0, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000064', 'RELEASED_TO_SENDER', '2026-10-23 09:00:00', 'Colombo Central Hub', 'Collected by sender', 1, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001');

-- --------------------------------------------------------

--
-- Table structure for table `TransportManager`
--

CREATE TABLE `TransportManager` (
  `userId` varchar(20) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `officeNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `TransportManager`
--

INSERT INTO `TransportManager` (`userId`, `region`, `officeNumber`) VALUES
('USR-000008', 'Western Province', 'T-101');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `userId` varchar(20) NOT NULL,
  `username` varchar(100) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `adminId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`userId`, `username`, `passwordHash`, `role`, `adminId`) VALUES
('USR-000001', 'admin.perera', '$2y$10$eVMA0eYK7eV0UWM1ASPXQ.aV1fUy0Q95xxrgHvey9A4gv1iMxncxq', 'SystemAdmin', 'USR-000001'),
('USR-000002', 'md.fernando', '$2y$10$wJuZTAwCBOzDUT.eytrqDuIM7R51dzUsVTh1QswUckmKYtDmbuyIy', 'ManagingDirector', 'USR-000001'),
('USR-000003', 'csm.silva', '$2y$10$MSH/.m02ezGsCGMcY1PPi.rXFvacH1BljU7q4nTBAS.mVANxPfAIa', 'CustomerServiceManager', 'USR-000001'),
('USR-000004', 'cso.nadeesha', '$2y$10$dKMeqXl5/P8WJMRtKbQjJO3Q4UufudxQ.1tGfGZtgyZzx5z4v9HPW', 'CustomerServiceOfficer', 'USR-000001'),
('USR-000005', 'cso.kasun', '$2y$10$vqmlq3fpBsIoRTuYjhUguO24tI3c3VIu./nFJ3XbWWxf5jWm/Jz96', 'CustomerServiceOfficer', 'USR-000001'),
('USR-000006', 'fm.jayawardena', '$2y$10$6dQoquoPefJQt90qY83oQO6QxDwLCrtzwNYXYzhw6qOizTo4vDyNS', 'FinanceManager', 'USR-000001'),
('USR-000007', 'fo.dilini', '$2y$10$6Wai6TmP8Xs/29fClWJ2.e.2UoLufTzttZDpvZcJ.OKBatBwDHA22', 'FinanceOfficer', 'USR-000001'),
('USR-000008', 'tm.bandara', '$2y$10$rPGmHsuLuRkL/1j/Bv8eg.iP2J8Egz6W/bWFA6gtFW6Rdot.OqfM6', 'TransportManager', 'USR-000001'),
('USR-000009', 'drv.sunil', '$2y$10$mQpKv6nsIW9B8ppKDMIgMO39YJ..SEIheJk3jAPKlfzX6yWB3wSjy', 'Driver', 'USR-000001'),
('USR-000010', 'drv.ruwan', '$2y$10$AS7XT.VBgmV4XYD1dc4Hk.fhyMLgvmEpX.lV56Lha32n3g1kcp/V2', 'Driver', 'USR-000001'),
('USR-000011', 'wm.gunawardena', '$2y$10$QsnP2TeffBXkSbQCGcEYouvQ664qDfjdZKIhtTLYQLKa5afdxVqI.', 'WarehouseManager', 'USR-000001'),
('USR-000012', 'wo.thilina', '$2y$10$auc0TiAvgWQra/APdDk/z.Ju1aVdR8kjWG3ltwTC0eU/ypQXxCSve', 'WarehouseOfficer', 'USR-000001'),
('USR-000013', 'amal.rathnayake', '$2y$10$34jKkmg90d.eOGfdtemo4uW8X2qLoH/lkDL2YupuqHS7jPX2Qvqz.', 'IndividualCustomer', 'USR-000001'),
('USR-000014', 'sachini.wijesinghe', '$2y$10$X3mFLYLrHOOl7CfkVp9gquqPvUPq.Uw9S1cKCY7Wl.JzrqFezb6ee', 'IndividualCustomer', 'USR-000001'),
('USR-000015', 'lankatea.orders', '$2y$10$dHuS64VJ96SgoPjaSuHi0e1gCcG1Cqtm3/5jyEjmXOVoWuD2i6HzS', 'CorporateCustomer', 'USR-000001');

-- --------------------------------------------------------

--
-- Table structure for table `Vehicle`
--

CREATE TABLE `Vehicle` (
  `vehicleId` varchar(20) NOT NULL,
  `registrationNumber` varchar(30) NOT NULL,
  `vehicleType` varchar(50) DEFAULT NULL,
  `capacity` decimal(10,2) DEFAULT NULL,
  `isAvailable` tinyint(1) NOT NULL DEFAULT 1,
  `transportMgrId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Vehicle`
--

INSERT INTO `Vehicle` (`vehicleId`, `registrationNumber`, `vehicleType`, `capacity`, `isAvailable`, `transportMgrId`) VALUES
('VEH-001', 'WP CAB-1234', 'VAN', 800.00, 0, 'USR-000008'),
('VEH-002', 'WP KL-5678', 'LORRY', 3000.00, 1, 'USR-000008'),
('VEH-003', 'WP BDF-9012', 'MOTORBIKE', 50.00, 1, 'USR-000008');

-- --------------------------------------------------------

--
-- Table structure for table `Warehouse`
--

CREATE TABLE `Warehouse` (
  `warehouseId` varchar(20) NOT NULL,
  `warehouseName` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `zoneCode` varchar(20) NOT NULL,
  `status` varchar(30) NOT NULL,
  `capacity` int(11) NOT NULL,
  `currentLoad` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Warehouse`
--

INSERT INTO `Warehouse` (`warehouseId`, `warehouseName`, `address`, `zoneCode`, `status`, `capacity`, `currentLoad`) VALUES
('WH-001', 'Colombo Central Hub', '45 Baseline Road, Colombo 09', 'CMB-01', 'ACTIVE', 5000, 1240),
('WH-002', 'Kandy Regional Depot', '12 Peradeniya Road, Kandy', 'KDY-01', 'ACTIVE', 2000, 310);

-- --------------------------------------------------------

--
-- Table structure for table `WarehouseManager`
--

CREATE TABLE `WarehouseManager` (
  `userId` varchar(20) NOT NULL,
  `officeNumber` varchar(20) DEFAULT NULL,
  `assignedWarehouseId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `WarehouseManager`
--

INSERT INTO `WarehouseManager` (`userId`, `officeNumber`, `assignedWarehouseId`) VALUES
('USR-000011', 'W-201', 'WH-001');

-- --------------------------------------------------------

--
-- Table structure for table `WarehouseOfficer`
--

CREATE TABLE `WarehouseOfficer` (
  `userId` varchar(20) NOT NULL,
  `assignedZone` varchar(20) DEFAULT NULL,
  `shift` varchar(30) DEFAULT NULL,
  `warehouseId` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `WarehouseOfficer`
--

INSERT INTO `WarehouseOfficer` (`userId`, `assignedZone`, `shift`, `warehouseId`) VALUES
('USR-000011', NULL, NULL, NULL),
('USR-000012', 'CMB-01', 'MORNING', 'WH-001');

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `CorporateAgreement`
--
ALTER TABLE `CorporateAgreement`
  ADD PRIMARY KEY (`agreementId`),
  ADD KEY `corporateCustomerId` (`corporateCustomerId`),
  ADD KEY `financeMgrId` (`financeMgrId`),
  ADD KEY `csMgrId` (`csMgrId`);

--
-- Indexes for table `CorporateCustomer`
--
ALTER TABLE `CorporateCustomer`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `csMgrId` (`csMgrId`);

--
-- Indexes for table `CostEstimate`
--
ALTER TABLE `CostEstimate`
  ADD PRIMARY KEY (`estimateId`),
  ADD UNIQUE KEY `pickupId` (`pickupId`),
  ADD KEY `ruleId` (`ruleId`);

--
-- Indexes for table `Customer`
--
ALTER TABLE `Customer`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `CustomerReport`
--
ALTER TABLE `CustomerReport`
  ADD PRIMARY KEY (`reportId`),
  ADD KEY `csMgrId` (`csMgrId`);

--
-- Indexes for table `CustomerServiceManager`
--
ALTER TABLE `CustomerServiceManager`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `CustomerServiceOfficer`
--
ALTER TABLE `CustomerServiceOfficer`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `Delivery`
--
ALTER TABLE `Delivery`
  ADD PRIMARY KEY (`deliveryId`),
  ADD UNIQUE KEY `issueId` (`issueId`),
  ADD KEY `deliveryManifestId` (`deliveryManifestId`),
  ADD KEY `driverManifestId` (`driverManifestId`);

--
-- Indexes for table `DeliveryAttempt`
--
ALTER TABLE `DeliveryAttempt`
  ADD PRIMARY KEY (`attemptId`),
  ADD KEY `deliveryId` (`deliveryId`);

--
-- Indexes for table `DeliveryManifest`
--
ALTER TABLE `DeliveryManifest`
  ADD PRIMARY KEY (`deliveryManifestId`),
  ADD KEY `transportMgrId` (`transportMgrId`);

--
-- Indexes for table `DeliveryReport`
--
ALTER TABLE `DeliveryReport`
  ADD PRIMARY KEY (`reportId`),
  ADD KEY `transportMgrId` (`transportMgrId`);

--
-- Indexes for table `DeliveryVerification`
--
ALTER TABLE `DeliveryVerification`
  ADD PRIMARY KEY (`verificationId`),
  ADD UNIQUE KEY `attemptId` (`attemptId`),
  ADD KEY `receiverLinkId` (`receiverLinkId`);

--
-- Indexes for table `Driver`
--
ALTER TABLE `Driver`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `licenseNumber` (`licenseNumber`),
  ADD KEY `transportMgrId` (`transportMgrId`);

--
-- Indexes for table `DriverManifest`
--
ALTER TABLE `DriverManifest`
  ADD PRIMARY KEY (`driverManifestId`),
  ADD UNIQUE KEY `driverId` (`driverId`),
  ADD KEY `transportMgrId` (`transportMgrId`),
  ADD KEY `vehicleId` (`vehicleId`);

--
-- Indexes for table `FinanceManager`
--
ALTER TABLE `FinanceManager`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `FinanceOfficer`
--
ALTER TABLE `FinanceOfficer`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `IndividualCustomer`
--
ALTER TABLE `IndividualCustomer`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `csMgrId` (`csMgrId`);

--
-- Indexes for table `Invoice`
--
ALTER TABLE `Invoice`
  ADD PRIMARY KEY (`invoiceId`),
  ADD UNIQUE KEY `deliveryId` (`deliveryId`),
  ADD KEY `customerId` (`customerId`),
  ADD KEY `csoId` (`csoId`),
  ADD KEY `financeOfficerId` (`financeOfficerId`);

--
-- Indexes for table `ManagingDirector`
--
ALTER TABLE `ManagingDirector`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `Notification`
--
ALTER TABLE `Notification`
  ADD PRIMARY KEY (`notificationId`),
  ADD KEY `customerId` (`customerId`),
  ADD KEY `receiverLinkId` (`receiverLinkId`);

--
-- Indexes for table `Parcel`
--
ALTER TABLE `Parcel`
  ADD PRIMARY KEY (`parcelId`),
  ADD KEY `customerId` (`customerId`),
  ADD KEY `receiverId` (`receiverId`),
  ADD KEY `csoId` (`csoId`),
  ADD KEY `pickupId` (`pickupId`),
  ADD KEY `warehouseId` (`warehouseId`),
  ADD KEY `receiverLinkId` (`receiverLinkId`),
  ADD KEY `deliveryId` (`deliveryId`),
  ADD KEY `ruleId` (`ruleId`),
  ADD KEY `warehouseOfficerId` (`warehouseOfficerId`);

--
-- Indexes for table `Payment`
--
ALTER TABLE `Payment`
  ADD PRIMARY KEY (`paymentId`),
  ADD KEY `financeOfficerId` (`financeOfficerId`);

--
-- Indexes for table `PaymentReport`
--
ALTER TABLE `PaymentReport`
  ADD PRIMARY KEY (`reportId`),
  ADD KEY `financeMgrId` (`financeMgrId`);

--
-- Indexes for table `PaymentSettlesInvoice`
--
ALTER TABLE `PaymentSettlesInvoice`
  ADD PRIMARY KEY (`paymentId`,`invoiceId`),
  ADD KEY `invoiceId` (`invoiceId`);

--
-- Indexes for table `Pickup`
--
ALTER TABLE `Pickup`
  ADD PRIMARY KEY (`pickupId`),
  ADD KEY `customerId` (`customerId`),
  ADD KEY `csoId` (`csoId`),
  ADD KEY `transportMgrId` (`transportMgrId`),
  ADD KEY `driverManifestId` (`driverManifestId`);

--
-- Indexes for table `PostDeliveryIssue`
--
ALTER TABLE `PostDeliveryIssue`
  ADD PRIMARY KEY (`issueId`),
  ADD UNIQUE KEY `receiverLinkId` (`receiverLinkId`),
  ADD KEY `csMgrId` (`csMgrId`);

--
-- Indexes for table `PricingRule`
--
ALTER TABLE `PricingRule`
  ADD PRIMARY KEY (`ruleId`);

--
-- Indexes for table `Receipt`
--
ALTER TABLE `Receipt`
  ADD PRIMARY KEY (`receiptId`),
  ADD UNIQUE KEY `paymentId` (`paymentId`),
  ADD KEY `customerId` (`customerId`);

--
-- Indexes for table `Receiver`
--
ALTER TABLE `Receiver`
  ADD PRIMARY KEY (`receiverId`);

--
-- Indexes for table `ReceiverLink`
--
ALTER TABLE `ReceiverLink`
  ADD PRIMARY KEY (`linkId`),
  ADD KEY `csoId` (`csoId`);

--
-- Indexes for table `Refund`
--
ALTER TABLE `Refund`
  ADD PRIMARY KEY (`refundId`),
  ADD UNIQUE KEY `paymentId` (`paymentId`),
  ADD UNIQUE KEY `parcelId` (`parcelId`),
  ADD UNIQUE KEY `issueId` (`issueId`),
  ADD KEY `customerId` (`customerId`),
  ADD KEY `financeMgrId` (`financeMgrId`);

--
-- Indexes for table `Report`
--
ALTER TABLE `Report`
  ADD PRIMARY KEY (`reportId`),
  ADD KEY `directorId` (`directorId`);

--
-- Indexes for table `StorageReport`
--
ALTER TABLE `StorageReport`
  ADD PRIMARY KEY (`reportId`),
  ADD KEY `warehouseMgrId` (`warehouseMgrId`);

--
-- Indexes for table `SystemAdmin`
--
ALTER TABLE `SystemAdmin`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `TrackingEvent`
--
ALTER TABLE `TrackingEvent`
  ADD PRIMARY KEY (`eventId`),
  ADD KEY `parcelId` (`parcelId`),
  ADD KEY `receiverLinkId` (`receiverLinkId`),
  ADD KEY `deliveryId` (`deliveryId`),
  ADD KEY `warehouseId` (`warehouseId`);

--
-- Indexes for table `TransportManager`
--
ALTER TABLE `TransportManager`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_admin` (`adminId`);

--
-- Indexes for table `Vehicle`
--
ALTER TABLE `Vehicle`
  ADD PRIMARY KEY (`vehicleId`),
  ADD UNIQUE KEY `registrationNumber` (`registrationNumber`),
  ADD KEY `transportMgrId` (`transportMgrId`);

--
-- Indexes for table `Warehouse`
--
ALTER TABLE `Warehouse`
  ADD PRIMARY KEY (`warehouseId`);

--
-- Indexes for table `WarehouseManager`
--
ALTER TABLE `WarehouseManager`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `assignedWarehouseId` (`assignedWarehouseId`);

--
-- Indexes for table `WarehouseOfficer`
--
ALTER TABLE `WarehouseOfficer`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `warehouseId` (`warehouseId`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `CorporateAgreement`
--
ALTER TABLE `CorporateAgreement`
  ADD CONSTRAINT `CorporateAgreement_ibfk_1` FOREIGN KEY (`corporateCustomerId`) REFERENCES `CorporateCustomer` (`userId`),
  ADD CONSTRAINT `CorporateAgreement_ibfk_2` FOREIGN KEY (`financeMgrId`) REFERENCES `FinanceManager` (`userId`),
  ADD CONSTRAINT `CorporateAgreement_ibfk_3` FOREIGN KEY (`csMgrId`) REFERENCES `CustomerServiceManager` (`userId`);

--
-- Constraints for table `CorporateCustomer`
--
ALTER TABLE `CorporateCustomer`
  ADD CONSTRAINT `CorporateCustomer_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Customer` (`userId`),
  ADD CONSTRAINT `CorporateCustomer_ibfk_2` FOREIGN KEY (`csMgrId`) REFERENCES `CustomerServiceManager` (`userId`);

--
-- Constraints for table `CostEstimate`
--
ALTER TABLE `CostEstimate`
  ADD CONSTRAINT `CostEstimate_ibfk_1` FOREIGN KEY (`pickupId`) REFERENCES `Pickup` (`pickupId`),
  ADD CONSTRAINT `CostEstimate_ibfk_2` FOREIGN KEY (`ruleId`) REFERENCES `PricingRule` (`ruleId`);

--
-- Constraints for table `Customer`
--
ALTER TABLE `Customer`
  ADD CONSTRAINT `Customer_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints for table `CustomerReport`
--
ALTER TABLE `CustomerReport`
  ADD CONSTRAINT `CustomerReport_ibfk_1` FOREIGN KEY (`reportId`) REFERENCES `Report` (`reportId`),
  ADD CONSTRAINT `CustomerReport_ibfk_2` FOREIGN KEY (`csMgrId`) REFERENCES `CustomerServiceManager` (`userId`);

--
-- Constraints for table `CustomerServiceManager`
--
ALTER TABLE `CustomerServiceManager`
  ADD CONSTRAINT `CustomerServiceManager_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `CustomerServiceOfficer` (`userId`);

--
-- Constraints for table `CustomerServiceOfficer`
--
ALTER TABLE `CustomerServiceOfficer`
  ADD CONSTRAINT `CustomerServiceOfficer_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints for table `Delivery`
--
ALTER TABLE `Delivery`
  ADD CONSTRAINT `Delivery_ibfk_1` FOREIGN KEY (`deliveryManifestId`) REFERENCES `DeliveryManifest` (`deliveryManifestId`),
  ADD CONSTRAINT `Delivery_ibfk_2` FOREIGN KEY (`driverManifestId`) REFERENCES `DriverManifest` (`driverManifestId`),
  ADD CONSTRAINT `Delivery_ibfk_3` FOREIGN KEY (`issueId`) REFERENCES `PostDeliveryIssue` (`issueId`);

--
-- Constraints for table `DeliveryAttempt`
--
ALTER TABLE `DeliveryAttempt`
  ADD CONSTRAINT `DeliveryAttempt_ibfk_1` FOREIGN KEY (`deliveryId`) REFERENCES `Delivery` (`deliveryId`);

--
-- Constraints for table `DeliveryManifest`
--
ALTER TABLE `DeliveryManifest`
  ADD CONSTRAINT `DeliveryManifest_ibfk_1` FOREIGN KEY (`transportMgrId`) REFERENCES `TransportManager` (`userId`);

--
-- Constraints for table `DeliveryReport`
--
ALTER TABLE `DeliveryReport`
  ADD CONSTRAINT `DeliveryReport_ibfk_1` FOREIGN KEY (`reportId`) REFERENCES `Report` (`reportId`),
  ADD CONSTRAINT `DeliveryReport_ibfk_2` FOREIGN KEY (`transportMgrId`) REFERENCES `TransportManager` (`userId`);

--
-- Constraints for table `DeliveryVerification`
--
ALTER TABLE `DeliveryVerification`
  ADD CONSTRAINT `DeliveryVerification_ibfk_1` FOREIGN KEY (`attemptId`) REFERENCES `DeliveryAttempt` (`attemptId`),
  ADD CONSTRAINT `DeliveryVerification_ibfk_2` FOREIGN KEY (`receiverLinkId`) REFERENCES `ReceiverLink` (`linkId`);

--
-- Constraints for table `Driver`
--
ALTER TABLE `Driver`
  ADD CONSTRAINT `Driver_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`),
  ADD CONSTRAINT `Driver_ibfk_2` FOREIGN KEY (`transportMgrId`) REFERENCES `TransportManager` (`userId`);

--
-- Constraints for table `DriverManifest`
--
ALTER TABLE `DriverManifest`
  ADD CONSTRAINT `DriverManifest_ibfk_1` FOREIGN KEY (`transportMgrId`) REFERENCES `TransportManager` (`userId`),
  ADD CONSTRAINT `DriverManifest_ibfk_2` FOREIGN KEY (`driverId`) REFERENCES `Driver` (`userId`),
  ADD CONSTRAINT `DriverManifest_ibfk_3` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle` (`vehicleId`);

--
-- Constraints for table `FinanceManager`
--
ALTER TABLE `FinanceManager`
  ADD CONSTRAINT `FinanceManager_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `FinanceOfficer` (`userId`);

--
-- Constraints for table `FinanceOfficer`
--
ALTER TABLE `FinanceOfficer`
  ADD CONSTRAINT `FinanceOfficer_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints for table `IndividualCustomer`
--
ALTER TABLE `IndividualCustomer`
  ADD CONSTRAINT `IndividualCustomer_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Customer` (`userId`),
  ADD CONSTRAINT `IndividualCustomer_ibfk_2` FOREIGN KEY (`csMgrId`) REFERENCES `CustomerServiceManager` (`userId`);

--
-- Constraints for table `Invoice`
--
ALTER TABLE `Invoice`
  ADD CONSTRAINT `Invoice_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `Customer` (`userId`),
  ADD CONSTRAINT `Invoice_ibfk_2` FOREIGN KEY (`csoId`) REFERENCES `CustomerServiceOfficer` (`userId`),
  ADD CONSTRAINT `Invoice_ibfk_3` FOREIGN KEY (`deliveryId`) REFERENCES `Delivery` (`deliveryId`),
  ADD CONSTRAINT `Invoice_ibfk_4` FOREIGN KEY (`financeOfficerId`) REFERENCES `FinanceOfficer` (`userId`);

--
-- Constraints for table `ManagingDirector`
--
ALTER TABLE `ManagingDirector`
  ADD CONSTRAINT `ManagingDirector_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints for table `Notification`
--
ALTER TABLE `Notification`
  ADD CONSTRAINT `Notification_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `Customer` (`userId`),
  ADD CONSTRAINT `Notification_ibfk_2` FOREIGN KEY (`receiverLinkId`) REFERENCES `ReceiverLink` (`linkId`);

--
-- Constraints for table `Parcel`
--
ALTER TABLE `Parcel`
  ADD CONSTRAINT `Parcel_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `Customer` (`userId`),
  ADD CONSTRAINT `Parcel_ibfk_2` FOREIGN KEY (`receiverId`) REFERENCES `Receiver` (`receiverId`),
  ADD CONSTRAINT `Parcel_ibfk_3` FOREIGN KEY (`csoId`) REFERENCES `CustomerServiceOfficer` (`userId`),
  ADD CONSTRAINT `Parcel_ibfk_4` FOREIGN KEY (`pickupId`) REFERENCES `Pickup` (`pickupId`),
  ADD CONSTRAINT `Parcel_ibfk_5` FOREIGN KEY (`warehouseId`) REFERENCES `Warehouse` (`warehouseId`),
  ADD CONSTRAINT `Parcel_ibfk_6` FOREIGN KEY (`receiverLinkId`) REFERENCES `ReceiverLink` (`linkId`),
  ADD CONSTRAINT `Parcel_ibfk_7` FOREIGN KEY (`deliveryId`) REFERENCES `Delivery` (`deliveryId`),
  ADD CONSTRAINT `Parcel_ibfk_8` FOREIGN KEY (`ruleId`) REFERENCES `PricingRule` (`ruleId`),
  ADD CONSTRAINT `Parcel_ibfk_9` FOREIGN KEY (`warehouseOfficerId`) REFERENCES `WarehouseOfficer` (`userId`);

--
-- Constraints for table `Payment`
--
ALTER TABLE `Payment`
  ADD CONSTRAINT `Payment_ibfk_1` FOREIGN KEY (`financeOfficerId`) REFERENCES `FinanceOfficer` (`userId`);

--
-- Constraints for table `PaymentReport`
--
ALTER TABLE `PaymentReport`
  ADD CONSTRAINT `PaymentReport_ibfk_1` FOREIGN KEY (`reportId`) REFERENCES `Report` (`reportId`),
  ADD CONSTRAINT `PaymentReport_ibfk_2` FOREIGN KEY (`financeMgrId`) REFERENCES `FinanceManager` (`userId`);

--
-- Constraints for table `PaymentSettlesInvoice`
--
ALTER TABLE `PaymentSettlesInvoice`
  ADD CONSTRAINT `PaymentSettlesInvoice_ibfk_1` FOREIGN KEY (`paymentId`) REFERENCES `Payment` (`paymentId`),
  ADD CONSTRAINT `PaymentSettlesInvoice_ibfk_2` FOREIGN KEY (`invoiceId`) REFERENCES `Invoice` (`invoiceId`);

--
-- Constraints for table `Pickup`
--
ALTER TABLE `Pickup`
  ADD CONSTRAINT `Pickup_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `Customer` (`userId`),
  ADD CONSTRAINT `Pickup_ibfk_2` FOREIGN KEY (`csoId`) REFERENCES `CustomerServiceOfficer` (`userId`),
  ADD CONSTRAINT `Pickup_ibfk_3` FOREIGN KEY (`transportMgrId`) REFERENCES `TransportManager` (`userId`),
  ADD CONSTRAINT `Pickup_ibfk_4` FOREIGN KEY (`driverManifestId`) REFERENCES `DriverManifest` (`driverManifestId`);

--
-- Constraints for table `PostDeliveryIssue`
--
ALTER TABLE `PostDeliveryIssue`
  ADD CONSTRAINT `PostDeliveryIssue_ibfk_1` FOREIGN KEY (`receiverLinkId`) REFERENCES `ReceiverLink` (`linkId`),
  ADD CONSTRAINT `PostDeliveryIssue_ibfk_2` FOREIGN KEY (`csMgrId`) REFERENCES `CustomerServiceManager` (`userId`);

--
-- Constraints for table `Receipt`
--
ALTER TABLE `Receipt`
  ADD CONSTRAINT `Receipt_ibfk_1` FOREIGN KEY (`paymentId`) REFERENCES `Payment` (`paymentId`),
  ADD CONSTRAINT `Receipt_ibfk_2` FOREIGN KEY (`customerId`) REFERENCES `Customer` (`userId`);

--
-- Constraints for table `ReceiverLink`
--
ALTER TABLE `ReceiverLink`
  ADD CONSTRAINT `ReceiverLink_ibfk_1` FOREIGN KEY (`csoId`) REFERENCES `CustomerServiceOfficer` (`userId`);

--
-- Constraints for table `Refund`
--
ALTER TABLE `Refund`
  ADD CONSTRAINT `Refund_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `Customer` (`userId`),
  ADD CONSTRAINT `Refund_ibfk_2` FOREIGN KEY (`paymentId`) REFERENCES `Payment` (`paymentId`),
  ADD CONSTRAINT `Refund_ibfk_3` FOREIGN KEY (`parcelId`) REFERENCES `Parcel` (`parcelId`),
  ADD CONSTRAINT `Refund_ibfk_4` FOREIGN KEY (`financeMgrId`) REFERENCES `FinanceManager` (`userId`),
  ADD CONSTRAINT `Refund_ibfk_5` FOREIGN KEY (`issueId`) REFERENCES `PostDeliveryIssue` (`issueId`);

--
-- Constraints for table `Report`
--
ALTER TABLE `Report`
  ADD CONSTRAINT `Report_ibfk_1` FOREIGN KEY (`directorId`) REFERENCES `ManagingDirector` (`userId`);

--
-- Constraints for table `StorageReport`
--
ALTER TABLE `StorageReport`
  ADD CONSTRAINT `StorageReport_ibfk_1` FOREIGN KEY (`reportId`) REFERENCES `Report` (`reportId`),
  ADD CONSTRAINT `StorageReport_ibfk_2` FOREIGN KEY (`warehouseMgrId`) REFERENCES `WarehouseManager` (`userId`);

--
-- Constraints for table `SystemAdmin`
--
ALTER TABLE `SystemAdmin`
  ADD CONSTRAINT `SystemAdmin_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints for table `TrackingEvent`
--
ALTER TABLE `TrackingEvent`
  ADD CONSTRAINT `TrackingEvent_ibfk_1` FOREIGN KEY (`parcelId`) REFERENCES `Parcel` (`parcelId`),
  ADD CONSTRAINT `TrackingEvent_ibfk_2` FOREIGN KEY (`receiverLinkId`) REFERENCES `ReceiverLink` (`linkId`),
  ADD CONSTRAINT `TrackingEvent_ibfk_3` FOREIGN KEY (`deliveryId`) REFERENCES `Delivery` (`deliveryId`),
  ADD CONSTRAINT `TrackingEvent_ibfk_4` FOREIGN KEY (`warehouseId`) REFERENCES `Warehouse` (`warehouseId`);

--
-- Constraints for table `TransportManager`
--
ALTER TABLE `TransportManager`
  ADD CONSTRAINT `TransportManager_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints for table `User`
--
ALTER TABLE `User`
  ADD CONSTRAINT `fk_user_admin` FOREIGN KEY (`adminId`) REFERENCES `SystemAdmin` (`userId`);

--
-- Constraints for table `Vehicle`
--
ALTER TABLE `Vehicle`
  ADD CONSTRAINT `Vehicle_ibfk_1` FOREIGN KEY (`transportMgrId`) REFERENCES `TransportManager` (`userId`);

--
-- Constraints for table `WarehouseManager`
--
ALTER TABLE `WarehouseManager`
  ADD CONSTRAINT `WarehouseManager_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `WarehouseOfficer` (`userId`),
  ADD CONSTRAINT `WarehouseManager_ibfk_2` FOREIGN KEY (`assignedWarehouseId`) REFERENCES `Warehouse` (`warehouseId`);

--
-- Constraints for table `WarehouseOfficer`
--
ALTER TABLE `WarehouseOfficer`
  ADD CONSTRAINT `WarehouseOfficer_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`),
  ADD CONSTRAINT `WarehouseOfficer_ibfk_2` FOREIGN KEY (`warehouseId`) REFERENCES `Warehouse` (`warehouseId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
