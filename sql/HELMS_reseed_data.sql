-- =====================================================================
-- HELMS RESEED: wipes all data, then inserts fresh dummy data.
-- Run AFTER the schema exists. Parcel statuses covered (one parcel each):
--   PENDING_PICKUP, RECEIVED_AT_WAREHOUSE, DETAILS_CONFIRMED, PACKED,
--   AWAITING_PAYMENT, READY_FOR_DISPATCH, OUT_FOR_DELIVERY, DELIVERED,
--   RETURNED_TO_WAREHOUSE, RETURN_TO_SENDER, UNCLAIMED, RELEASED_TO_SENDER
-- Parcel IDs: HEN-2026-000123 ... HEN-2026-000134
-- Password/token/PIN hashes are placeholders.
-- =====================================================================

USE HELMS;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE User;
TRUNCATE TABLE SystemAdmin;
TRUNCATE TABLE Warehouse;
TRUNCATE TABLE CustomerServiceOfficer;
TRUNCATE TABLE CustomerServiceManager;
TRUNCATE TABLE FinanceOfficer;
TRUNCATE TABLE FinanceManager;
TRUNCATE TABLE TransportManager;
TRUNCATE TABLE Driver;
TRUNCATE TABLE WarehouseOfficer;
TRUNCATE TABLE WarehouseManager;
TRUNCATE TABLE ManagingDirector;
TRUNCATE TABLE Customer;
TRUNCATE TABLE IndividualCustomer;
TRUNCATE TABLE CorporateCustomer;
TRUNCATE TABLE Vehicle;
TRUNCATE TABLE DeliveryManifest;
TRUNCATE TABLE DriverManifest;
TRUNCATE TABLE Pickup;
TRUNCATE TABLE Receiver;
TRUNCATE TABLE Parcel;
TRUNCATE TABLE ReceiverLink;
TRUNCATE TABLE PostDeliveryIssue;
TRUNCATE TABLE Delivery;
TRUNCATE TABLE DeliveryAttempt;
TRUNCATE TABLE DeliveryVerification;
TRUNCATE TABLE TrackingEvent;
TRUNCATE TABLE CostEstimate;
TRUNCATE TABLE PricingRule;
TRUNCATE TABLE ParcelPricingRule;
TRUNCATE TABLE CostEstimatePricingRule;
TRUNCATE TABLE CorporateAgreement;
TRUNCATE TABLE CSManagerViewsAgreement;
TRUNCATE TABLE Invoice;
TRUNCATE TABLE FinanceOfficerViewsInvoice;
TRUNCATE TABLE Payment;
TRUNCATE TABLE Receipt;
TRUNCATE TABLE Refund;
TRUNCATE TABLE Notification;
TRUNCATE TABLE PaymentReport;
TRUNCATE TABLE DeliveryReport;
TRUNCATE TABLE StorageReport;
TRUNCATE TABLE CustomerReport;
TRUNCATE TABLE MDViewsPaymentReport;
TRUNCATE TABLE MDViewsDeliveryReport;
TRUNCATE TABLE MDViewsStorageReport;
TRUNCATE TABLE MDViewsCustomerReport;
TRUNCATE TABLE WarehouseOfficerHandlesParcel;
SET FOREIGN_KEY_CHECKS = 1;

-- Users (adminId filled in after SystemAdmin exists)
INSERT INTO User (userId, username, passwordHash, role, adminId) VALUES
('USR-000001','admin.perera','$2b$10$dummyhash000000000000000000000000000000000000000001','SystemAdmin',NULL),
('USR-000002','md.fernando','$2b$10$dummyhash000000000000000000000000000000000000000002','ManagingDirector',NULL),
('USR-000003','csm.silva','$2b$10$dummyhash000000000000000000000000000000000000000003','CustomerServiceManager',NULL),
('USR-000004','cso.nadeesha','$2b$10$dummyhash000000000000000000000000000000000000000004','CustomerServiceOfficer',NULL),
('USR-000005','cso.kasun','$2b$10$dummyhash000000000000000000000000000000000000000005','CustomerServiceOfficer',NULL),
('USR-000006','fm.jayawardena','$2b$10$dummyhash000000000000000000000000000000000000000006','FinanceManager',NULL),
('USR-000007','fo.dilini','$2b$10$dummyhash000000000000000000000000000000000000000007','FinanceOfficer',NULL),
('USR-000008','tm.bandara','$2b$10$dummyhash000000000000000000000000000000000000000008','TransportManager',NULL),
('USR-000009','drv.sunil','$2b$10$dummyhash000000000000000000000000000000000000000009','Driver',NULL),
('USR-000010','drv.ruwan','$2b$10$dummyhash000000000000000000000000000000000000000010','Driver',NULL),
('USR-000011','wm.gunawardena','$2b$10$dummyhash000000000000000000000000000000000000000011','WarehouseManager',NULL),
('USR-000012','wo.thilina','$2b$10$dummyhash000000000000000000000000000000000000000012','WarehouseOfficer',NULL),
('USR-000013','amal.rathnayake','$2b$10$dummyhash000000000000000000000000000000000000000013','IndividualCustomer',NULL),
('USR-000014','sachini.wijesinghe','$2b$10$dummyhash000000000000000000000000000000000000000014','IndividualCustomer',NULL),
('USR-000015','lankatea.orders','$2b$10$dummyhash000000000000000000000000000000000000000015','CorporateCustomer',NULL);

INSERT INTO SystemAdmin (userId, accessLevel, lastLoginDate) VALUES
('USR-000001','FULL','2026-10-30 08:45:00');

UPDATE User SET adminId = 'USR-000001';

INSERT INTO Warehouse (warehouseId, warehouseName, address, zoneCode, status, capacity, currentLoad) VALUES
('WH-001','Colombo Central Hub','45 Baseline Road, Colombo 09','CMB-01','ACTIVE',5000,1240),
('WH-002','Kandy Regional Depot','12 Peradeniya Road, Kandy','KDY-01','ACTIVE',2000,310);

INSERT INTO CustomerServiceOfficer (userId, deskNumber, shift) VALUES
('USR-000004','D-04','MORNING'),
('USR-000005','D-05','EVENING');

INSERT INTO CustomerServiceManager (userId, region, department) VALUES
('USR-000003','Western Province','Customer Service');

INSERT INTO FinanceOfficer (userId, contactNumber, shift) VALUES
('USR-000007','+94112345007','DAY');

INSERT INTO FinanceManager (userId, approvalLimit, department) VALUES
('USR-000006',500000.00,'Finance');

INSERT INTO TransportManager (userId, region, officeNumber) VALUES
('USR-000008','Western Province','T-101');

INSERT INTO Driver (userId, licenseNumber, contactNumber, status, transportMgrId) VALUES
('USR-000009','B1234567','+94771110009','ON_DUTY','USR-000008'),
('USR-000010','B7654321','+94771110010','AVAILABLE','USR-000008');

INSERT INTO WarehouseOfficer (userId, assignedZone, shift, warehouseId) VALUES
('USR-000012','CMB-01','MORNING','WH-001');

INSERT INTO WarehouseManager (userId, officeNumber, warehouseId) VALUES
('USR-000011','W-201','WH-001');

INSERT INTO ManagingDirector (userId, appointmentDate, officeNumber) VALUES
('USR-000002','2022-01-15','MD-001');

-- Customers
INSERT INTO Customer (userId, contactNumber, email, address) VALUES
('USR-000013','+94771234013','amal.r@example.com','23 Galle Road, Dehiwala'),
('USR-000014','+94771234014','sachini.w@example.com','78 Temple Road, Mount Lavinia'),
('USR-000015','+94112223015','orders@lankatea.example.com','100 Duke Street, Colombo 01');

INSERT INTO IndividualCustomer (userId, name, nic, csMgrId) VALUES
('USR-000013','Amal Rathnayake','199012345678','USR-000003'),
('USR-000014','Sachini Wijesinghe','199545678912','USR-000003');

INSERT INTO CorporateCustomer (userId, companyName, contactPerson, csMgrId) VALUES
('USR-000015','Lanka Tea Exporters (Pvt) Ltd','Nimal Herath','USR-000003');

-- Transport
INSERT INTO Vehicle (vehicleId, registrationNumber, vehicleType, capacity, isAvailable, transportMgrId) VALUES
('VEH-001','WP CAB-1234','VAN',800.00,FALSE,'USR-000008'),
('VEH-002','WP KL-5678','LORRY',3000.00,TRUE,'USR-000008'),
('VEH-003','WP BDF-9012','MOTORBIKE',50.00,TRUE,'USR-000008');

INSERT INTO DeliveryManifest (deliveryManifestId, manifestDate, route, manifestStatus, transportMgrId) VALUES
('DM-2026-000001','2026-10-30','Colombo Central - Dehiwala - Mount Lavinia','COMPLETED','USR-000008'),
('DM-2026-000002','2026-11-01','Colombo Central - Kandy','IN_PROGRESS','USR-000008');

INSERT INTO DriverManifest (driverManifestId, assignedDate, schedule, status, transportMgrId, driverId, deliveryManifestId, vehicleId) VALUES
('DRM-2026-000001','2026-10-30','08:00-16:00','COMPLETED','USR-000008','USR-000009','DM-2026-000001','VEH-001'),
('DRM-2026-000002','2026-11-01','07:00-17:00','IN_PROGRESS','USR-000008','USR-000010','DM-2026-000002','VEH-002');


-- Pickups
INSERT INTO Pickup (pickupId, requestedAt, pickupDate, preferredTimeWindow, pickupAddress, status, rejectionReason, failureReason, cancelledAt, customerId, csoId, transportMgrId, driverManifestId) VALUES
('PCK-2026-000051', '2026-10-31 09:00:00', '2026-11-02', '09:00-12:00', '23 Galle Road, Dehiwala', 'SCHEDULED', NULL, NULL, NULL, 'USR-000013', 'USR-000004', 'USR-000008', NULL),
('PCK-2026-000052', '2026-10-10 09:00:00', '2026-10-11', '09:00-12:00', '78 Temple Road, Mount Lavinia', 'COMPLETED', NULL, NULL, NULL, 'USR-000014', 'USR-000005', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000053', '2026-10-11 09:00:00', '2026-10-12', '09:00-12:00', '100 Duke Street, Colombo 01', 'COMPLETED', NULL, NULL, NULL, 'USR-000015', 'USR-000004', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000054', '2026-10-12 09:00:00', '2026-10-13', '09:00-12:00', '23 Galle Road, Dehiwala', 'COMPLETED', NULL, NULL, NULL, 'USR-000013', 'USR-000005', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000055', '2026-10-15 09:00:00', '2026-10-16', '09:00-12:00', '23 Galle Road, Dehiwala', 'COMPLETED', NULL, NULL, NULL, 'USR-000013', 'USR-000004', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000056', '2026-10-17 09:00:00', '2026-10-18', '09:00-12:00', '100 Duke Street, Colombo 01', 'COMPLETED', NULL, NULL, NULL, 'USR-000015', 'USR-000004', 'USR-000008', 'DRM-2026-000001'),
('PCK-2026-000057', '2026-10-30 16:45:00', NULL, '09:00-12:00', '23 Galle Road, Dehiwala', 'REJECTED', 'Address outside service zone', NULL, NULL, 'USR-000013', 'USR-000005', NULL, NULL),
('PCK-2026-000058', '2026-10-31 11:20:00', NULL, '13:00-16:00', '78 Temple Road, Mount Lavinia', 'CANCELLED', NULL, NULL, '2026-10-31 15:00:00', 'USR-000014', 'USR-000004', NULL, NULL),
('PCK-2026-000059', '2026-10-29 10:00:00', '2026-10-30', '09:00-12:00', '100 Duke Street, Colombo 01', 'FAILED', NULL, 'Customer not at pickup address', NULL, 'USR-000015', 'USR-000005', 'USR-000008', 'DRM-2026-000001');

-- Receivers
INSERT INTO Receiver (receiverId, receiverName, receiverPhone, receiverAddress, zoneCode) VALUES
('RCV-000001', 'Kamal Dissanayake', '+94712223301', '15 Lake Road, Kandy', 'KDY-01'),
('RCV-000002', 'Priya Mendis', '+94712223302', '9 Hospital Road, Galle', 'GLE-01'),
('RCV-000003', 'Ishara Fonseka', '+94712223303', '67 Beach Road, Negombo', 'NGB-01'),
('RCV-000004', 'Tharindu Perera', '+94712223304', '5 Main Street, Matara', 'MTR-01'),
('RCV-000005', 'Shalini Kumar', '+94712223305', '21 Hill Street, Nuwara Eliya', 'NWE-01'),
('RCV-000006', 'Rohan Abeysekera', '+94712223306', '88 Church Road, Jaffna', 'JFN-01');

-- Parcels (one per status)
INSERT INTO Parcel (parcelId, description, parcelCategory, isFragile, serviceType, intakeMethod, declaredWeight, confirmedWeight, dimensions, detailsConfirmed, parcelReceivedDate, storageLocationCode, status, holdExpiresAt, failureReason, customerId, receiverId, csoId, pickupId, warehouseId) VALUES
('HEN-2026-000123', 'Ceramic tableware set', 'HOUSEHOLD', TRUE, 'EXPRESS', 'PICKUP', 4.5, NULL, '40x30x25', FALSE, NULL, NULL, 'PENDING_PICKUP', NULL, NULL, 'USR-000013', 'RCV-000001', 'USR-000004', 'PCK-2026-000051', NULL),
('HEN-2026-000124', 'Legal documents', 'DOCUMENT', FALSE, 'STANDARD', 'PICKUP', 0.5, NULL, '35x25x5', FALSE, '2026-10-11', 'A-01-01', 'RECEIVED_AT_WAREHOUSE', NULL, NULL, 'USR-000014', 'RCV-000002', 'USR-000005', 'PCK-2026-000052', 'WH-001'),
('HEN-2026-000125', 'Tea packs - export samples', 'COMMERCIAL', FALSE, 'STANDARD', 'PICKUP', 12.0, 12.3, '50x40x30', TRUE, '2026-10-12', 'C-02-07', 'DETAILS_CONFIRMED', NULL, NULL, 'USR-000015', 'RCV-000003', 'USR-000004', 'PCK-2026-000053', 'WH-001'),
('HEN-2026-000126', 'Handmade leather bag', 'PERSONAL', FALSE, 'STANDARD', 'PICKUP', 1.8, 1.85, '38x28x12', TRUE, '2026-10-13', 'A-03-12', 'PACKED', NULL, NULL, 'USR-000013', 'RCV-000004', 'USR-000005', 'PCK-2026-000054', 'WH-001'),
('HEN-2026-000127', 'Laptop (boxed)', 'ELECTRONICS', TRUE, 'EXPRESS', 'WALK_IN', 2.8, 2.9, '45x32x10', TRUE, '2026-10-14', 'B-01-04', 'AWAITING_PAYMENT', '2026-11-04 17:00:00', NULL, 'USR-000014', 'RCV-000005', 'USR-000004', NULL, 'WH-001'),
('HEN-2026-000128', 'Spice gift hamper', 'COMMERCIAL', FALSE, 'STANDARD', 'WALK_IN', 3.2, 3.25, '35x30x20', TRUE, '2026-10-15', 'B-02-02', 'READY_FOR_DISPATCH', NULL, NULL, 'USR-000015', 'RCV-000006', 'USR-000005', NULL, 'WH-001'),
('HEN-2026-000129', 'School books bundle', 'PERSONAL', FALSE, 'STANDARD', 'PICKUP', 6.0, 6.1, '40x30x30', TRUE, '2026-10-16', NULL, 'OUT_FOR_DELIVERY', NULL, NULL, 'USR-000013', 'RCV-000001', 'USR-000004', 'PCK-2026-000055', 'WH-001'),
('HEN-2026-000130', 'Glass vase', 'HOUSEHOLD', TRUE, 'EXPRESS', 'WALK_IN', 2.0, 2.1, '30x30x35', TRUE, '2026-10-17', NULL, 'DELIVERED', NULL, NULL, 'USR-000014', 'RCV-000002', 'USR-000005', NULL, 'WH-001'),
('HEN-2026-000131', 'Sports shoes', 'PERSONAL', FALSE, 'STANDARD', 'PICKUP', 1.5, 1.45, '35x25x15', TRUE, '2026-10-18', 'C-01-05', 'RETURNED_TO_WAREHOUSE', NULL, 'Receiver unavailable after 3 attempts', 'USR-000015', 'RCV-000003', 'USR-000004', 'PCK-2026-000056', 'WH-001'),
('HEN-2026-000132', 'Wedding invitations', 'DOCUMENT', FALSE, 'STANDARD', 'WALK_IN', 0.9, 0.95, '32x24x8', TRUE, '2026-10-19', NULL, 'RETURN_TO_SENDER', NULL, 'Incorrect receiver address', 'USR-000013', 'RCV-000004', 'USR-000005', NULL, 'WH-001'),
('HEN-2026-000133', 'Wall clock', 'HOUSEHOLD', TRUE, 'STANDARD', 'WALK_IN', 1.2, 1.2, '33x33x10', TRUE, '2026-10-20', 'A-02-09', 'UNCLAIMED', '2026-10-31 17:00:00', 'Receiver did not collect within hold period', 'USR-000014', 'RCV-000005', 'USR-000004', NULL, 'WH-001'),
('HEN-2026-000134', 'Textile samples', 'COMMERCIAL', FALSE, 'STANDARD', 'WALK_IN', 4.0, 4.1, '45x35x20', TRUE, '2026-10-21', NULL, 'RELEASED_TO_SENDER', NULL, 'Sender requested release', 'USR-000015', 'RCV-000006', 'USR-000005', NULL, 'WH-001');

-- Receiver links
INSERT INTO ReceiverLink (linkId, tokenHash, createdAt, expiresAt, isActive, parcelId, csoId) VALUES
('LNK-000001', '$2b$10$dummytoken0000000000000000000000000000001', '2026-10-10 10:00:00', '2026-11-09 09:00:00', TRUE, 'HEN-2026-000123', 'USR-000004'),
('LNK-000002', '$2b$10$dummytoken0000000000000000000000000000002', '2026-10-11 10:00:00', '2026-11-10 09:00:00', TRUE, 'HEN-2026-000124', 'USR-000005'),
('LNK-000003', '$2b$10$dummytoken0000000000000000000000000000003', '2026-10-12 10:00:00', '2026-11-11 09:00:00', TRUE, 'HEN-2026-000125', 'USR-000004'),
('LNK-000004', '$2b$10$dummytoken0000000000000000000000000000004', '2026-10-13 10:00:00', '2026-11-12 09:00:00', TRUE, 'HEN-2026-000126', 'USR-000005'),
('LNK-000005', '$2b$10$dummytoken0000000000000000000000000000005', '2026-10-14 10:00:00', '2026-11-13 09:00:00', TRUE, 'HEN-2026-000127', 'USR-000004'),
('LNK-000006', '$2b$10$dummytoken0000000000000000000000000000006', '2026-10-15 10:00:00', '2026-11-14 09:00:00', TRUE, 'HEN-2026-000128', 'USR-000005'),
('LNK-000007', '$2b$10$dummytoken0000000000000000000000000000007', '2026-10-16 10:00:00', '2026-11-15 09:00:00', TRUE, 'HEN-2026-000129', 'USR-000004'),
('LNK-000008', '$2b$10$dummytoken0000000000000000000000000000008', '2026-10-17 10:00:00', '2026-11-16 09:00:00', TRUE, 'HEN-2026-000130', 'USR-000005'),
('LNK-000009', '$2b$10$dummytoken0000000000000000000000000000009', '2026-10-18 10:00:00', '2026-11-17 09:00:00', TRUE, 'HEN-2026-000131', 'USR-000004'),
('LNK-000010', '$2b$10$dummytoken0000000000000000000000000000010', '2026-10-19 10:00:00', '2026-11-18 09:00:00', TRUE, 'HEN-2026-000132', 'USR-000005'),
('LNK-000011', '$2b$10$dummytoken0000000000000000000000000000011', '2026-10-20 10:00:00', '2026-11-19 09:00:00', TRUE, 'HEN-2026-000133', 'USR-000004'),
('LNK-000012', '$2b$10$dummytoken0000000000000000000000000000012', '2026-10-21 10:00:00', '2026-11-20 09:00:00', FALSE, 'HEN-2026-000134', 'USR-000005');

-- Post-delivery issue
INSERT INTO PostDeliveryIssue (issueId, issueType, description, submittedAt, status, faultParty, decision, decisionNotes, decidedAt, receiverLinkId, csMgrId) VALUES
('ISS-2026-000001', 'DAMAGED', 'Vase arrived with a cracked base.', '2026-10-31 10:15:00', 'RESOLVED', 'CARRIER', 'REFUND_APPROVED', 'Packaging photos confirm transit damage.', '2026-10-31 15:40:00', 'LNK-000008', 'USR-000003');

-- Deliveries
INSERT INTO Delivery (deliveryId, direction, deliveryAddress, deliveryDate, estimatedFrom, estimatedTo, status, failedAttemptCount, returnCostBearer, parcelId, deliveryManifestId, driverManifestId, issueId) VALUES
('DLV-2026-000201', 'OUTBOUND', '88 Church Road, Jaffna', '2026-11-02', '2026-11-02 09:00:00', '2026-11-02 13:00:00', 'SCHEDULED', 0, NULL, 'HEN-2026-000128', 'DM-2026-000002', 'DRM-2026-000002', NULL),
('DLV-2026-000202', 'OUTBOUND', '15 Lake Road, Kandy', '2026-11-01', '2026-11-01 09:00:00', '2026-11-01 13:00:00', 'IN_TRANSIT', 0, NULL, 'HEN-2026-000129', 'DM-2026-000002', 'DRM-2026-000002', NULL),
('DLV-2026-000203', 'OUTBOUND', '9 Hospital Road, Galle', '2026-10-30', '2026-10-30 09:00:00', '2026-10-30 13:00:00', 'DELIVERED', 0, NULL, 'HEN-2026-000130', 'DM-2026-000001', 'DRM-2026-000001', 'ISS-2026-000001'),
('DLV-2026-000204', 'OUTBOUND', '67 Beach Road, Negombo', '2026-10-30', '2026-10-30 09:00:00', '2026-10-30 13:00:00', 'FAILED', 3, 'CUSTOMER', 'HEN-2026-000131', 'DM-2026-000001', 'DRM-2026-000001', NULL),
('DLV-2026-000205', 'RETURN_TO_SENDER', '23 Galle Road, Dehiwala', '2026-11-01', '2026-11-01 09:00:00', '2026-11-01 13:00:00', 'IN_TRANSIT', 0, 'CUSTOMER', 'HEN-2026-000132', 'DM-2026-000002', 'DRM-2026-000002', NULL);

-- Delivery attempts
INSERT INTO DeliveryAttempt (attemptId, attemptNumber, attemptedAt, arrivedAt, outcome, failureReason, nextAttemptAt, deliveryId) VALUES
('ATT-000001', 1, '2026-10-30 11:20:00', '2026-10-30 11:15:00', 'SUCCESS', NULL, NULL, 'DLV-2026-000203'),
('ATT-000002', 1, '2026-10-28 10:05:00', '2026-10-28 10:00:00', 'FAILED', 'Receiver not available', '2026-10-29 10:00:00', 'DLV-2026-000204'),
('ATT-000003', 2, '2026-10-29 10:10:00', '2026-10-29 10:05:00', 'FAILED', 'Receiver phone unreachable', '2026-10-30 10:00:00', 'DLV-2026-000204'),
('ATT-000004', 3, '2026-10-30 10:12:00', '2026-10-30 10:08:00', 'FAILED', 'Receiver not available', NULL, 'DLV-2026-000204'),
('ATT-000005', 1, NULL, NULL, 'PENDING', NULL, NULL, 'DLV-2026-000202');

-- Delivery verifications
INSERT INTO DeliveryVerification (verificationId, pinHash, generatedAt, expiresAt, verifiedAt, attemptCount, status, attemptId, receiverLinkId) VALUES
('VER-000001', '$2b$10$dummypin000000000000000000000000000000000000000000001', '2026-10-30 11:00:00', '2026-10-30 12:00:00', '2026-10-30 11:18:00', 1, 'VERIFIED', 'ATT-000001', 'LNK-000008'),
('VER-000002', '$2b$10$dummypin000000000000000000000000000000000000000000002', '2026-11-01 08:30:00', '2026-11-01 18:00:00', NULL, 0, 'ACTIVE', 'ATT-000005', 'LNK-000007'),
('VER-000003', '$2b$10$dummypin000000000000000000000000000000000000000000003', '2026-10-30 09:50:00', '2026-10-30 10:50:00', NULL, 2, 'EXPIRED', 'ATT-000004', 'LNK-000009');

-- Tracking events (actorId is polymorphic, no FK)
INSERT INTO TrackingEvent (eventId, eventType, occurredAt, locationDescription, remarks, isVisibleToReceiver, actorType, actorId, parcelId, receiverLinkId, deliveryId, warehouseId) VALUES
('EVT-000001', 'PENDING_PICKUP', '2026-10-10 09:00:00', 'Customer address', 'Pickup scheduled', TRUE, 'CSO', 'USR-000004', 'HEN-2026-000123', 'LNK-000001', NULL, NULL),
('EVT-000002', 'PENDING_PICKUP', '2026-10-11 09:00:00', 'Customer address', 'Pickup scheduled', TRUE, 'CSO', 'USR-000005', 'HEN-2026-000124', 'LNK-000002', NULL, NULL),
('EVT-000003', 'RECEIVED_AT_WAREHOUSE', '2026-10-11 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000124', 'LNK-000002', NULL, 'WH-001'),
('EVT-000004', 'PENDING_PICKUP', '2026-10-12 09:00:00', 'Customer address', 'Pickup scheduled', TRUE, 'CSO', 'USR-000004', 'HEN-2026-000125', 'LNK-000003', NULL, NULL),
('EVT-000005', 'RECEIVED_AT_WAREHOUSE', '2026-10-12 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000125', 'LNK-000003', NULL, 'WH-001'),
('EVT-000006', 'DETAILS_CONFIRMED', '2026-10-13 01:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000125', 'LNK-000003', NULL, 'WH-001'),
('EVT-000007', 'PENDING_PICKUP', '2026-10-13 09:00:00', 'Customer address', 'Pickup scheduled', TRUE, 'CSO', 'USR-000005', 'HEN-2026-000126', 'LNK-000004', NULL, NULL),
('EVT-000008', 'RECEIVED_AT_WAREHOUSE', '2026-10-13 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000126', 'LNK-000004', NULL, 'WH-001'),
('EVT-000009', 'DETAILS_CONFIRMED', '2026-10-14 01:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000126', 'LNK-000004', NULL, 'WH-001'),
('EVT-000010', 'PACKED', '2026-10-14 09:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000126', 'LNK-000004', NULL, 'WH-001'),
('EVT-000011', 'RECEIVED_AT_WAREHOUSE', '2026-10-14 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000127', 'LNK-000005', NULL, 'WH-001'),
('EVT-000012', 'DETAILS_CONFIRMED', '2026-10-14 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000127', 'LNK-000005', NULL, 'WH-001'),
('EVT-000013', 'PACKED', '2026-10-15 01:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000127', 'LNK-000005', NULL, 'WH-001'),
('EVT-000014', 'AWAITING_PAYMENT', '2026-10-15 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', FALSE, 'CSO', 'USR-000004', 'HEN-2026-000127', 'LNK-000005', NULL, NULL),
('EVT-000015', 'RECEIVED_AT_WAREHOUSE', '2026-10-15 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000128', 'LNK-000006', NULL, 'WH-001'),
('EVT-000016', 'DETAILS_CONFIRMED', '2026-10-15 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000128', 'LNK-000006', NULL, 'WH-001'),
('EVT-000017', 'PACKED', '2026-10-16 01:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000128', 'LNK-000006', NULL, 'WH-001'),
('EVT-000018', 'AWAITING_PAYMENT', '2026-10-16 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', FALSE, 'CSO', 'USR-000005', 'HEN-2026-000128', 'LNK-000006', NULL, NULL),
('EVT-000019', 'READY_FOR_DISPATCH', '2026-10-16 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000128', 'LNK-000006', NULL, 'WH-001'),
('EVT-000020', 'PENDING_PICKUP', '2026-10-16 09:00:00', 'Customer address', 'Pickup scheduled', TRUE, 'CSO', 'USR-000004', 'HEN-2026-000129', 'LNK-000007', NULL, NULL),
('EVT-000021', 'RECEIVED_AT_WAREHOUSE', '2026-10-16 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000129', 'LNK-000007', NULL, 'WH-001'),
('EVT-000022', 'DETAILS_CONFIRMED', '2026-10-17 01:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000129', 'LNK-000007', NULL, 'WH-001'),
('EVT-000023', 'PACKED', '2026-10-17 09:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000129', 'LNK-000007', NULL, 'WH-001'),
('EVT-000024', 'AWAITING_PAYMENT', '2026-10-17 17:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', FALSE, 'CSO', 'USR-000004', 'HEN-2026-000129', 'LNK-000007', NULL, NULL),
('EVT-000025', 'READY_FOR_DISPATCH', '2026-10-18 01:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000129', 'LNK-000007', NULL, 'WH-001'),
('EVT-000026', 'OUT_FOR_DELIVERY', '2026-10-18 09:00:00', 'In transit', 'Loaded on vehicle', TRUE, 'DRIVER', 'USR-000009', 'HEN-2026-000129', 'LNK-000007', 'DLV-2026-000202', NULL),
('EVT-000027', 'RECEIVED_AT_WAREHOUSE', '2026-10-17 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000130', 'LNK-000008', NULL, 'WH-001'),
('EVT-000028', 'DETAILS_CONFIRMED', '2026-10-17 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000130', 'LNK-000008', NULL, 'WH-001'),
('EVT-000029', 'PACKED', '2026-10-18 01:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000130', 'LNK-000008', NULL, 'WH-001'),
('EVT-000030', 'AWAITING_PAYMENT', '2026-10-18 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', FALSE, 'CSO', 'USR-000005', 'HEN-2026-000130', 'LNK-000008', NULL, NULL),
('EVT-000031', 'READY_FOR_DISPATCH', '2026-10-18 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000130', 'LNK-000008', NULL, 'WH-001'),
('EVT-000032', 'OUT_FOR_DELIVERY', '2026-10-19 01:00:00', 'In transit', 'Loaded on vehicle', TRUE, 'DRIVER', 'USR-000009', 'HEN-2026-000130', 'LNK-000008', 'DLV-2026-000203', NULL),
('EVT-000033', 'DELIVERED', '2026-10-19 09:00:00', 'Receiver address', 'PIN verified, delivered', TRUE, 'DRIVER', 'USR-000009', 'HEN-2026-000130', 'LNK-000008', 'DLV-2026-000203', NULL),
('EVT-000034', 'PENDING_PICKUP', '2026-10-18 09:00:00', 'Customer address', 'Pickup scheduled', TRUE, 'CSO', 'USR-000004', 'HEN-2026-000131', 'LNK-000009', NULL, NULL),
('EVT-000035', 'RECEIVED_AT_WAREHOUSE', '2026-10-18 17:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000036', 'DETAILS_CONFIRMED', '2026-10-19 01:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000037', 'PACKED', '2026-10-19 09:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000038', 'AWAITING_PAYMENT', '2026-10-19 17:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', FALSE, 'CSO', 'USR-000004', 'HEN-2026-000131', 'LNK-000009', NULL, NULL),
('EVT-000039', 'READY_FOR_DISPATCH', '2026-10-20 01:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000040', 'OUT_FOR_DELIVERY', '2026-10-20 09:00:00', 'In transit', 'Loaded on vehicle', TRUE, 'DRIVER', 'USR-000009', 'HEN-2026-000131', 'LNK-000009', 'DLV-2026-000204', NULL),
('EVT-000041', 'DELIVERY_FAILED', '2026-10-20 17:00:00', 'Receiver address', 'Delivery attempts failed', TRUE, 'DRIVER', 'USR-000009', 'HEN-2026-000131', 'LNK-000009', 'DLV-2026-000204', NULL),
('EVT-000042', 'RETURNED_TO_WAREHOUSE', '2026-10-21 01:00:00', 'Colombo Central Hub', 'Returned after failed delivery', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000131', 'LNK-000009', NULL, 'WH-001'),
('EVT-000043', 'RECEIVED_AT_WAREHOUSE', '2026-10-19 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000044', 'DETAILS_CONFIRMED', '2026-10-19 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000045', 'PACKED', '2026-10-20 01:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000046', 'AWAITING_PAYMENT', '2026-10-20 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', FALSE, 'CSO', 'USR-000005', 'HEN-2026-000132', 'LNK-000010', NULL, NULL),
('EVT-000047', 'READY_FOR_DISPATCH', '2026-10-20 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000048', 'OUT_FOR_DELIVERY', '2026-10-21 01:00:00', 'In transit', 'Loaded on vehicle', TRUE, 'DRIVER', 'USR-000009', 'HEN-2026-000132', 'LNK-000010', NULL, NULL),
('EVT-000049', 'DELIVERY_FAILED', '2026-10-21 09:00:00', 'Receiver address', 'Delivery attempts failed', TRUE, 'DRIVER', 'USR-000009', 'HEN-2026-000132', 'LNK-000010', NULL, NULL),
('EVT-000050', 'RETURNED_TO_WAREHOUSE', '2026-10-21 17:00:00', 'Colombo Central Hub', 'Returned after failed delivery', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000132', 'LNK-000010', NULL, 'WH-001'),
('EVT-000051', 'RETURN_TO_SENDER', '2026-10-22 01:00:00', 'In transit', 'Returning to sender', TRUE, 'DRIVER', 'USR-000009', 'HEN-2026-000132', 'LNK-000010', 'DLV-2026-000205', NULL),
('EVT-000052', 'RECEIVED_AT_WAREHOUSE', '2026-10-20 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000053', 'DETAILS_CONFIRMED', '2026-10-20 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000054', 'PACKED', '2026-10-21 01:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000055', 'AWAITING_PAYMENT', '2026-10-21 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', FALSE, 'CSO', 'USR-000004', 'HEN-2026-000133', 'LNK-000011', NULL, NULL),
('EVT-000056', 'READY_FOR_DISPATCH', '2026-10-21 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000057', 'UNCLAIMED', '2026-10-22 01:00:00', 'Colombo Central Hub', 'Hold period expired, unclaimed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000133', 'LNK-000011', NULL, 'WH-001'),
('EVT-000058', 'RECEIVED_AT_WAREHOUSE', '2026-10-21 09:00:00', 'Colombo Central Hub', 'Parcel received at warehouse', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000059', 'DETAILS_CONFIRMED', '2026-10-21 17:00:00', 'Colombo Central Hub', 'Weight and dimensions confirmed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000060', 'PACKED', '2026-10-22 01:00:00', 'Colombo Central Hub', 'Parcel packed', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000061', 'AWAITING_PAYMENT', '2026-10-22 09:00:00', 'Colombo Central Hub', 'Invoice sent, awaiting payment', FALSE, 'CSO', 'USR-000005', 'HEN-2026-000134', 'LNK-000012', NULL, NULL),
('EVT-000062', 'READY_FOR_DISPATCH', '2026-10-22 17:00:00', 'Colombo Central Hub', 'Payment received, ready for dispatch', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000063', 'UNCLAIMED', '2026-10-23 01:00:00', 'Colombo Central Hub', 'Hold period expired, unclaimed', FALSE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001'),
('EVT-000064', 'RELEASED_TO_SENDER', '2026-10-23 09:00:00', 'Colombo Central Hub', 'Collected by sender', TRUE, 'WAREHOUSE_OFFICER', 'USR-000012', 'HEN-2026-000134', 'LNK-000012', NULL, 'WH-001');

-- Pricing rules
INSERT INTO PricingRule (ruleId, serviceType, zoneCode, minWeight, maxWeight, baseFee, additionalPerKgRate, fragileSurcharge, perAttemptCost, effectiveFrom, effectiveTo, isActive) VALUES
('PR-001', 'STANDARD', 'ALL', 0.0, 5.0, 450.0, 80.0, 150.0, 200.0, '2026-01-01', NULL, TRUE),
('PR-002', 'EXPRESS', 'ALL', 0.0, 5.0, 800.0, 120.0, 250.0, 350.0, '2026-01-01', NULL, TRUE),
('PR-003', 'STANDARD', 'ALL', 5.01, 20.0, 600.0, 100.0, 150.0, 250.0, '2026-01-01', NULL, TRUE),
('PR-004', 'EXPRESS', 'ALL', 5.01, 20.0, 1100.0, 150.0, 250.0, 400.0, '2026-01-01', NULL, TRUE);

-- Parcel <-> pricing rule
INSERT INTO ParcelPricingRule (parcelId, ruleId) VALUES
('HEN-2026-000123', 'PR-002'),
('HEN-2026-000124', 'PR-001'),
('HEN-2026-000125', 'PR-003'),
('HEN-2026-000126', 'PR-001'),
('HEN-2026-000127', 'PR-002'),
('HEN-2026-000128', 'PR-001'),
('HEN-2026-000129', 'PR-003'),
('HEN-2026-000130', 'PR-002'),
('HEN-2026-000131', 'PR-001'),
('HEN-2026-000132', 'PR-001'),
('HEN-2026-000133', 'PR-001'),
('HEN-2026-000134', 'PR-001');

-- Cost estimates
INSERT INTO CostEstimate (estimateId, baseCharge, surcharges, totalEstimate, calculatedAt, pickupId) VALUES
('EST-000001', 1220.0, 250.0, 1470.0, '2026-10-09 09:05:00', 'PCK-2026-000051'),
('EST-000002', 450.0, 0, 450.0, '2026-10-10 09:05:00', 'PCK-2026-000052'),
('EST-000003', 1730.0, 0, 1730.0, '2026-10-11 09:05:00', 'PCK-2026-000053'),
('EST-000004', 518.0, 0, 518.0, '2026-10-12 09:05:00', 'PCK-2026-000054'),
('EST-000005', 1110.0, 0, 1110.0, '2026-10-15 09:05:00', 'PCK-2026-000055'),
('EST-000006', 486.0, 0, 486.0, '2026-10-17 09:05:00', 'PCK-2026-000056');

INSERT INTO CostEstimatePricingRule (estimateId, ruleId) VALUES
('EST-000001', 'PR-002'),
('EST-000002', 'PR-001'),
('EST-000003', 'PR-003'),
('EST-000004', 'PR-001'),
('EST-000005', 'PR-003'),
('EST-000006', 'PR-001');

-- Corporate agreement
INSERT INTO CorporateAgreement (agreementId, startDate, endDate, terms, discountRate, paymentTerms, status, corporateCustomerId, financeMgrId) VALUES
('AGR-2026-000001', '2026-01-01', '2026-12-31', 'Volume-based discount; monthly invoicing.', 10.0, 'NET_30', 'ACTIVE', 'USR-000015', 'USR-000006');

INSERT INTO CSManagerViewsAgreement (csManagerId, agreementId) VALUES
('USR-000003', 'AGR-2026-000001');

-- Billing
INSERT INTO Invoice (invoiceId, invoiceDate, dueDate, sourceType, subtotal, taxAmount, totalAmount, status, sentAt, customerId, csoId, deliveryId) VALUES
('INV-2026-000301', '2026-10-15', '2026-10-29', 'PARCEL', 1278.0, 230.04, 1508.04, 'SENT', '2026-10-15 15:00:00', 'USR-000014', 'USR-000004', NULL),
('INV-2026-000302', '2026-10-16', '2026-10-30', 'PARCEL', 630.0, 113.4, 743.4, 'PAID', '2026-10-16 15:00:00', 'USR-000015', 'USR-000005', 'DLV-2026-000201'),
('INV-2026-000303', '2026-10-17', '2026-10-31', 'PARCEL', 1110.0, 199.8, 1309.8, 'PAID', '2026-10-17 15:00:00', 'USR-000013', 'USR-000004', 'DLV-2026-000202'),
('INV-2026-000304', '2026-10-18', '2026-11-01', 'PARCEL', 1182.0, 212.76, 1394.76, 'PAID', '2026-10-18 15:00:00', 'USR-000014', 'USR-000005', 'DLV-2026-000203'),
('INV-2026-000305', '2026-10-19', '2026-11-02', 'PARCEL', 1086.0, 195.48, 1281.48, 'PAID', '2026-10-19 15:00:00', 'USR-000015', 'USR-000004', 'DLV-2026-000204'),
('INV-2026-000306', '2026-10-20', '2026-11-03', 'PARCEL', 450.0, 81.0, 531.0, 'PAID', '2026-10-20 15:00:00', 'USR-000013', 'USR-000005', 'DLV-2026-000205'),
('INV-2026-000307', '2026-10-21', '2026-11-04', 'PARCEL', 616.0, 110.88, 726.88, 'PAID', '2026-10-21 15:00:00', 'USR-000014', 'USR-000004', NULL),
('INV-2026-000308', '2026-10-22', '2026-11-05', 'PARCEL', 698.0, 125.64, 823.64, 'PAID', '2026-10-22 15:00:00', 'USR-000015', 'USR-000005', NULL);

INSERT INTO FinanceOfficerViewsInvoice (financeOfficerId, invoiceId) VALUES
('USR-000007', 'INV-2026-000301'),
('USR-000007', 'INV-2026-000302'),
('USR-000007', 'INV-2026-000303'),
('USR-000007', 'INV-2026-000304'),
('USR-000007', 'INV-2026-000305'),
('USR-000007', 'INV-2026-000306'),
('USR-000007', 'INV-2026-000307'),
('USR-000007', 'INV-2026-000308');

INSERT INTO Payment (paymentId, amount, paymentDate, paymentMethod, transactionRef, invoiceId, financeOfficerId) VALUES
('PAY-2026-000401', 743.4, '2026-10-17', 'BANK_TRANSFER', 'TXN-88120128', 'INV-2026-000302', 'USR-000007'),
('PAY-2026-000402', 1309.8, '2026-10-18', 'CARD', 'TXN-88120129', 'INV-2026-000303', 'USR-000007'),
('PAY-2026-000403', 1394.76, '2026-10-19', 'CASH', 'TXN-88120130', 'INV-2026-000304', 'USR-000007'),
('PAY-2026-000404', 1281.48, '2026-10-20', 'BANK_TRANSFER', 'TXN-88120131', 'INV-2026-000305', 'USR-000007'),
('PAY-2026-000405', 531.0, '2026-10-21', 'CARD', 'TXN-88120132', 'INV-2026-000306', 'USR-000007'),
('PAY-2026-000406', 726.88, '2026-10-22', 'CASH', 'TXN-88120133', 'INV-2026-000307', 'USR-000007'),
('PAY-2026-000407', 823.64, '2026-10-23', 'BANK_TRANSFER', 'TXN-88120134', 'INV-2026-000308', 'USR-000007');

INSERT INTO Receipt (receiptId, issuedDate, amount, paymentId, customerId) VALUES
('RCT-2026-000501', '2026-10-17', 743.4, 'PAY-2026-000401', 'USR-000015'),
('RCT-2026-000502', '2026-10-18', 1309.8, 'PAY-2026-000402', 'USR-000013'),
('RCT-2026-000503', '2026-10-19', 1394.76, 'PAY-2026-000403', 'USR-000014'),
('RCT-2026-000504', '2026-10-20', 1281.48, 'PAY-2026-000404', 'USR-000015'),
('RCT-2026-000505', '2026-10-21', 531.0, 'PAY-2026-000405', 'USR-000013'),
('RCT-2026-000506', '2026-10-22', 726.88, 'PAY-2026-000406', 'USR-000014'),
('RCT-2026-000507', '2026-10-23', 823.64, 'PAY-2026-000407', 'USR-000015');

-- Refunds
INSERT INTO Refund (refundId, refundReason, originalAmount, attemptCount, returnCost, returnOption, returnDeliveryFee, refundMethod, collectionBranch, requestedAt, expiresAt, expiryNotifiedAt, expiryAcknowledged, processedAt, status, accountHolderName, bankName, branchName, accountNumber, customerId, paymentId, parcelId, financeMgrId, issueId) VALUES
('RFD-2026-000601', 'Damaged item in transit', 1394.76, 1, 0.0, 'NO_RETURN', 0.0, 'BANK_TRANSFER', NULL, '2026-10-31 15:45:00', '2026-12-31 23:59:59', NULL, FALSE, NULL, 'PENDING', 'Sachini Wijesinghe', 'Bank of Ceylon', 'Mount Lavinia', '0012345678', 'USR-000014', 'PAY-2026-000403', 'HEN-2026-000130', 'USR-000006', 'ISS-2026-000001'),
('RFD-2026-000602', 'Parcel released to sender', 823.64, 0, 0.0, 'COLLECT_FROM_BRANCH', 0.0, 'CASH', 'Colombo Central Hub', '2026-10-29 09:00:00', '2026-11-28 23:59:59', NULL, TRUE, '2026-10-30 14:00:00', 'PROCESSED', NULL, NULL, NULL, NULL, 'USR-000015', 'PAY-2026-000407', 'HEN-2026-000134', 'USR-000006', NULL),
('RFD-2026-000603', 'Unclaimed parcel - refund window', 726.88, 0, 0.0, 'COLLECT_FROM_BRANCH', 0.0, 'CASH', 'Colombo Central Hub', '2026-10-31 18:00:00', '2026-11-05 23:59:59', '2026-11-01 09:00:00', FALSE, NULL, 'PENDING', NULL, NULL, NULL, NULL, 'USR-000014', 'PAY-2026-000406', 'HEN-2026-000133', 'USR-000006', NULL);

-- Notifications
INSERT INTO Notification (notificationId, notificationType, channel, recipientContact, content, sentAt, status, customerId, receiverLinkId) VALUES
('NTF-000001', 'PICKUP_SCHEDULED', 'EMAIL', 'amal.r@example.com', 'Your pickup PCK-2026-000051 is scheduled for 2 Nov. Parcel HEN-2026-000123.', '2026-10-31 09:05:00', 'SENT', 'USR-000013', NULL),
('NTF-000002', 'PAYMENT_REQUESTED', 'EMAIL', 'sachini.w@example.com', 'Invoice INV-2026-000301 for parcel HEN-2026-000127 is awaiting payment.', '2026-10-18 16:00:00', 'SENT', 'USR-000014', NULL),
('NTF-000003', 'DELIVERY_PIN', 'SMS', '+94712223306', 'Your delivery PIN for HEN-2026-000129 is ready. Open your tracking link.', '2026-11-01 08:30:00', 'SENT', NULL, 'LNK-000007'),
('NTF-000004', 'DELIVERED', 'SMS', '+94712223305', 'Parcel HEN-2026-000130 has been delivered.', '2026-10-30 11:20:00', 'SENT', NULL, 'LNK-000008'),
('NTF-000005', 'DELIVERY_FAILED', 'EMAIL', 'orders@lankatea.example.com', 'Delivery of HEN-2026-000131 failed after 3 attempts. Parcel returned to warehouse.', '2026-10-30 10:30:00', 'SENT', 'USR-000015', NULL),
('NTF-000006', 'UNCLAIMED_WARNING', 'EMAIL', 'sachini.w@example.com', 'Parcel HEN-2026-000133 is unclaimed and the hold period has ended.', '2026-10-31 17:05:00', 'SENT', 'USR-000014', NULL),
('NTF-000007', 'REFUND_EXPIRY', 'EMAIL', 'sachini.w@example.com', 'Your refund RFD-2026-000603 expires on 5 Nov.', '2026-11-01 09:00:00', 'SENT', 'USR-000014', NULL);

-- Reports
INSERT INTO PaymentReport (reportId, generatedAt, periodStart, periodEnd, totalCollected, totalRefunded, expiredRefundTotal, financeMgrId) VALUES
('RPT-PAY-2026-10', '2026-11-01 06:00:00', '2026-10-01', '2026-10-31', 6810.96, 823.64, 0.0, 'USR-000006');

INSERT INTO DeliveryReport (reportId, generatedAt, periodStart, periodEnd, totalDeliveries, failedDeliveries, returnedParcels, transportMgrId) VALUES
('RPT-DLV-2026-10', '2026-11-01 06:05:00', '2026-10-01', '2026-10-31', 5, 1, 2, 'USR-000008');

INSERT INTO StorageReport (reportId, generatedAt, periodStart, periodEnd, parcelsStored, unclaimedParcels, capacityUtilisation, warehouseMgrId) VALUES
('RPT-STO-2026-10', '2026-11-01 06:10:00', '2026-10-01', '2026-10-31', 11, 1, 24.8, 'USR-000011');

INSERT INTO CustomerReport (reportId, generatedAt, periodStart, periodEnd, newCustomers, activeCustomers, corporateCustomers, csMgrId) VALUES
('RPT-CUS-2026-10', '2026-11-01 06:15:00', '2026-10-01', '2026-10-31', 3, 3, 1, 'USR-000003');

INSERT INTO MDViewsPaymentReport (directorId, reportId) VALUES
('USR-000002', 'RPT-PAY-2026-10');

INSERT INTO MDViewsDeliveryReport (directorId, reportId) VALUES
('USR-000002', 'RPT-DLV-2026-10');

INSERT INTO MDViewsStorageReport (directorId, reportId) VALUES
('USR-000002', 'RPT-STO-2026-10');

INSERT INTO MDViewsCustomerReport (directorId, reportId) VALUES
('USR-000002', 'RPT-CUS-2026-10');

-- Warehouse officer handles parcel
INSERT INTO WarehouseOfficerHandlesParcel (warehouseOfficerId, parcelId) VALUES
('USR-000012', 'HEN-2026-000124'),
('USR-000012', 'HEN-2026-000125'),
('USR-000012', 'HEN-2026-000126'),
('USR-000012', 'HEN-2026-000127'),
('USR-000012', 'HEN-2026-000128'),
('USR-000012', 'HEN-2026-000129'),
('USR-000012', 'HEN-2026-000130'),
('USR-000012', 'HEN-2026-000131'),
('USR-000012', 'HEN-2026-000132'),
('USR-000012', 'HEN-2026-000133'),
('USR-000012', 'HEN-2026-000134');
