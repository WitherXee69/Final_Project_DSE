-- HELMS database schema (generated from FINAL_FIXED_RELATIONAL_SCHEMA)

CREATE DATABASE IF NOT EXISTS HELMS;
USE HELMS;

-- 1. USERS AND ROLE TABLES

-- User.adminId -> SystemAdmin.userId is added later via ALTER TABLE
-- (User and SystemAdmin reference each other).
CREATE TABLE User (
    userId        VARCHAR(20) PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    passwordHash  VARCHAR(255) NOT NULL,
    role          VARCHAR(50)  NOT NULL,
    adminId       VARCHAR(20) NULL
);

CREATE TABLE SystemAdmin (
    userId         VARCHAR(20) PRIMARY KEY,
    accessLevel    VARCHAR(50) NOT NULL,
    lastLoginDate  DATETIME NULL,
    FOREIGN KEY (userId) REFERENCES User(userId)
);

ALTER TABLE User
    ADD CONSTRAINT fk_user_admin FOREIGN KEY (adminId) REFERENCES SystemAdmin(userId);

CREATE TABLE Warehouse (
    warehouseId    VARCHAR(20) PRIMARY KEY,
    warehouseName  VARCHAR(150) NOT NULL,
    address        VARCHAR(255) NOT NULL,
    zoneCode       VARCHAR(20)  NOT NULL,
    status         VARCHAR(30)  NOT NULL,
    capacity       INT NOT NULL,
    currentLoad    INT NOT NULL DEFAULT 0
);

CREATE TABLE CustomerServiceOfficer (
    userId      VARCHAR(20) PRIMARY KEY,
    deskNumber  VARCHAR(20),
    shift       VARCHAR(30),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE CustomerServiceManager (
    userId      VARCHAR(20) PRIMARY KEY,
    region      VARCHAR(100),
    department  VARCHAR(100),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE FinanceOfficer (
    userId         VARCHAR(20) PRIMARY KEY,
    contactNumber  VARCHAR(30),
    shift          VARCHAR(30),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE FinanceManager (
    userId         VARCHAR(20) PRIMARY KEY,
    approvalLimit  DECIMAL(12,2),
    department     VARCHAR(100),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE TransportManager (
    userId        VARCHAR(20) PRIMARY KEY,
    region        VARCHAR(100),
    officeNumber  VARCHAR(20),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE Driver (
    userId          VARCHAR(20) PRIMARY KEY,
    licenseNumber   VARCHAR(50) NOT NULL UNIQUE,
    contactNumber   VARCHAR(30),
    status          VARCHAR(30),
    transportMgrId  VARCHAR(20) NULL,
    FOREIGN KEY (userId) REFERENCES User(userId),
    FOREIGN KEY (transportMgrId) REFERENCES TransportManager(userId)
);

CREATE TABLE WarehouseOfficer (
    userId        VARCHAR(20) PRIMARY KEY,
    assignedZone  VARCHAR(20),
    shift         VARCHAR(30),
    warehouseId   VARCHAR(20) NULL,
    FOREIGN KEY (userId) REFERENCES User(userId),
    FOREIGN KEY (warehouseId) REFERENCES Warehouse(warehouseId)
);

CREATE TABLE WarehouseManager (
    userId        VARCHAR(20) PRIMARY KEY,
    officeNumber  VARCHAR(20),
    warehouseId   VARCHAR(20) NULL,
    FOREIGN KEY (userId) REFERENCES User(userId),
    FOREIGN KEY (warehouseId) REFERENCES Warehouse(warehouseId)
);

CREATE TABLE ManagingDirector (
    userId           VARCHAR(20) PRIMARY KEY,
    appointmentDate  DATE,
    officeNumber     VARCHAR(20),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

-- 2. CUSTOMERS

CREATE TABLE Customer (
    userId         VARCHAR(20) PRIMARY KEY,
    contactNumber  VARCHAR(30),
    email          VARCHAR(150),
    address        VARCHAR(255),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE IndividualCustomer (
    userId   VARCHAR(20) PRIMARY KEY,
    name     VARCHAR(150) NOT NULL,
    nic      VARCHAR(20),
    csMgrId  VARCHAR(20) NULL,
    FOREIGN KEY (userId) REFERENCES Customer(userId),
    FOREIGN KEY (csMgrId) REFERENCES CustomerServiceManager(userId)
);

CREATE TABLE CorporateCustomer (
    userId         VARCHAR(20) PRIMARY KEY,
    companyName    VARCHAR(150) NOT NULL,
    contactPerson  VARCHAR(150),
    csMgrId        VARCHAR(20) NULL,
    FOREIGN KEY (userId) REFERENCES Customer(userId),
    FOREIGN KEY (csMgrId) REFERENCES CustomerServiceManager(userId)
);

-- 3. TRANSPORT: VEHICLES AND MANIFESTS

CREATE TABLE Vehicle (
    vehicleId           VARCHAR(20) PRIMARY KEY,
    registrationNumber  VARCHAR(30) NOT NULL UNIQUE,
    vehicleType         VARCHAR(50),
    capacity            DECIMAL(10,2),
    isAvailable         BOOLEAN NOT NULL DEFAULT TRUE,
    transportMgrId      VARCHAR(20) NULL,
    FOREIGN KEY (transportMgrId) REFERENCES TransportManager(userId)
);

CREATE TABLE DeliveryManifest (
    deliveryManifestId  VARCHAR(20) PRIMARY KEY,
    manifestDate        DATE NOT NULL,
    route               VARCHAR(255),
    manifestStatus      VARCHAR(30),
    transportMgrId      VARCHAR(20) NULL,
    FOREIGN KEY (transportMgrId) REFERENCES TransportManager(userId)
);

CREATE TABLE DriverManifest (
    driverManifestId    VARCHAR(20) PRIMARY KEY,
    assignedDate        DATE NOT NULL,
    schedule            VARCHAR(255),
    status              VARCHAR(30),
    transportMgrId      VARCHAR(20) NULL,
    driverId            VARCHAR(20) NULL,
    deliveryManifestId  VARCHAR(20) NULL,
    vehicleId           VARCHAR(20) NULL,
    FOREIGN KEY (transportMgrId)     REFERENCES TransportManager(userId),
    FOREIGN KEY (driverId)           REFERENCES Driver(userId),
    FOREIGN KEY (deliveryManifestId) REFERENCES DeliveryManifest(deliveryManifestId),
    FOREIGN KEY (vehicleId)          REFERENCES Vehicle(vehicleId)
);

-- 4. PICKUPS, RECEIVERS, PARCELS

CREATE TABLE Pickup (
    pickupId             VARCHAR(20) PRIMARY KEY,
    requestedAt          DATETIME NOT NULL,
    pickupDate           DATE,
    preferredTimeWindow  VARCHAR(50),
    pickupAddress        VARCHAR(255) NOT NULL,
    status               VARCHAR(30),
    rejectionReason      VARCHAR(255),
    failureReason        VARCHAR(255),
    cancelledAt          DATETIME NULL,
    customerId           VARCHAR(20) NOT NULL,
    csoId                VARCHAR(20) NULL,
    transportMgrId       VARCHAR(20) NULL,
    driverManifestId     VARCHAR(20) NULL,
    FOREIGN KEY (customerId)       REFERENCES Customer(userId),
    FOREIGN KEY (csoId)            REFERENCES CustomerServiceOfficer(userId),
    FOREIGN KEY (transportMgrId)   REFERENCES TransportManager(userId),
    FOREIGN KEY (driverManifestId) REFERENCES DriverManifest(driverManifestId)
);

CREATE TABLE Receiver (
    receiverId       VARCHAR(20) PRIMARY KEY,
    receiverName     VARCHAR(150) NOT NULL,
    receiverPhone    VARCHAR(30),
    receiverAddress  VARCHAR(255),
    zoneCode         VARCHAR(20)
);

CREATE TABLE Parcel (
    parcelId             VARCHAR(20) PRIMARY KEY,
    description          VARCHAR(255),
    parcelCategory       VARCHAR(50),
    isFragile            BOOLEAN NOT NULL DEFAULT FALSE,
    serviceType          VARCHAR(50),
    intakeMethod         VARCHAR(50),
    declaredWeight       DECIMAL(10,2),
    confirmedWeight      DECIMAL(10,2),
    dimensions           VARCHAR(50),
    detailsConfirmed     BOOLEAN NOT NULL DEFAULT FALSE,
    parcelReceivedDate   DATE,
    storageLocationCode  VARCHAR(30),
    status               VARCHAR(30),
    holdExpiresAt        DATETIME NULL,
    failureReason        VARCHAR(255),
    customerId           VARCHAR(20) NOT NULL,
    receiverId           VARCHAR(20) NOT NULL,
    csoId                VARCHAR(20) NULL,
    pickupId             VARCHAR(20) NULL,
    warehouseId          VARCHAR(20) NULL,
    FOREIGN KEY (customerId)  REFERENCES Customer(userId),
    FOREIGN KEY (receiverId)  REFERENCES Receiver(receiverId),
    FOREIGN KEY (csoId)       REFERENCES CustomerServiceOfficer(userId),
    FOREIGN KEY (pickupId)    REFERENCES Pickup(pickupId),
    FOREIGN KEY (warehouseId) REFERENCES Warehouse(warehouseId)
);

CREATE TABLE ReceiverLink (
    linkId     VARCHAR(20) PRIMARY KEY,
    tokenHash  VARCHAR(255) NOT NULL,
    createdAt  DATETIME NOT NULL,
    expiresAt  DATETIME NOT NULL,
    isActive   BOOLEAN NOT NULL DEFAULT TRUE,
    parcelId   VARCHAR(20) NOT NULL,
    csoId      VARCHAR(20) NULL,
    FOREIGN KEY (parcelId) REFERENCES Parcel(parcelId),
    FOREIGN KEY (csoId)    REFERENCES CustomerServiceOfficer(userId)
);

-- 5. POST-DELIVERY ISSUES, DELIVERIES

CREATE TABLE PostDeliveryIssue (
    issueId         VARCHAR(20) PRIMARY KEY,
    issueType       VARCHAR(50),
    description     TEXT,
    submittedAt     DATETIME NOT NULL,
    status          VARCHAR(30),
    faultParty      VARCHAR(50),
    decision        VARCHAR(50),
    decisionNotes   TEXT,
    decidedAt       DATETIME NULL,
    receiverLinkId  VARCHAR(20) NULL,
    csMgrId         VARCHAR(20) NULL,
    FOREIGN KEY (receiverLinkId) REFERENCES ReceiverLink(linkId),
    FOREIGN KEY (csMgrId)        REFERENCES CustomerServiceManager(userId)
);

CREATE TABLE Delivery (
    deliveryId          VARCHAR(20) PRIMARY KEY,
    direction           VARCHAR(30),
    deliveryAddress     VARCHAR(255),
    deliveryDate        DATE,
    estimatedFrom       DATETIME,
    estimatedTo         DATETIME,
    status              VARCHAR(30),
    failedAttemptCount  INT NOT NULL DEFAULT 0,
    returnCostBearer    VARCHAR(30),
    parcelId            VARCHAR(20) NOT NULL,
    deliveryManifestId  VARCHAR(20) NULL,
    driverManifestId    VARCHAR(20) NULL,
    issueId             VARCHAR(20) NULL,
    FOREIGN KEY (parcelId)           REFERENCES Parcel(parcelId),
    FOREIGN KEY (deliveryManifestId) REFERENCES DeliveryManifest(deliveryManifestId),
    FOREIGN KEY (driverManifestId)   REFERENCES DriverManifest(driverManifestId),
    FOREIGN KEY (issueId)            REFERENCES PostDeliveryIssue(issueId)
);

CREATE TABLE DeliveryAttempt (
    attemptId      VARCHAR(20) PRIMARY KEY,
    attemptNumber  INT NOT NULL,
    attemptedAt    DATETIME,
    arrivedAt      DATETIME,
    outcome        VARCHAR(30),
    failureReason  VARCHAR(255),
    nextAttemptAt  DATETIME NULL,
    deliveryId     VARCHAR(20) NOT NULL,
    FOREIGN KEY (deliveryId) REFERENCES Delivery(deliveryId)
);

CREATE TABLE DeliveryVerification (
    verificationId  VARCHAR(20) PRIMARY KEY,
    pinHash         VARCHAR(255) NOT NULL,
    generatedAt     DATETIME NOT NULL,
    expiresAt       DATETIME NOT NULL,
    verifiedAt      DATETIME NULL,
    attemptCount    INT NOT NULL DEFAULT 0,
    status          VARCHAR(30),
    attemptId       VARCHAR(20) NULL,
    receiverLinkId  VARCHAR(20) NULL,
    FOREIGN KEY (attemptId)      REFERENCES DeliveryAttempt(attemptId),
    FOREIGN KEY (receiverLinkId) REFERENCES ReceiverLink(linkId)
);

-- actorId is polymorphic (depends on actorType), so it has no FK.
CREATE TABLE TrackingEvent (
    eventId              VARCHAR(20) PRIMARY KEY,
    eventType            VARCHAR(50) NOT NULL,
    occurredAt           DATETIME NOT NULL,
    locationDescription  VARCHAR(255),
    remarks              VARCHAR(255),
    isVisibleToReceiver  BOOLEAN NOT NULL DEFAULT TRUE,
    actorType            VARCHAR(50),
    actorId              VARCHAR(20) NULL,
    parcelId             VARCHAR(20) NULL,
    receiverLinkId       VARCHAR(20) NULL,
    deliveryId           VARCHAR(20) NULL,
    warehouseId          VARCHAR(20) NULL,
    FOREIGN KEY (parcelId)       REFERENCES Parcel(parcelId),
    FOREIGN KEY (receiverLinkId) REFERENCES ReceiverLink(linkId),
    FOREIGN KEY (deliveryId)     REFERENCES Delivery(deliveryId),
    FOREIGN KEY (warehouseId)    REFERENCES Warehouse(warehouseId)
);

-- 6. PRICING

CREATE TABLE CostEstimate (
    estimateId     VARCHAR(20) PRIMARY KEY,
    baseCharge     DECIMAL(12,2) NOT NULL,
    surcharges     DECIMAL(12,2) NOT NULL DEFAULT 0,
    totalEstimate  DECIMAL(12,2) NOT NULL,
    calculatedAt   DATETIME NOT NULL,
    pickupId       VARCHAR(20) NULL,
    FOREIGN KEY (pickupId) REFERENCES Pickup(pickupId)
);

CREATE TABLE PricingRule (
    ruleId              VARCHAR(20) PRIMARY KEY,
    serviceType         VARCHAR(50),
    zoneCode            VARCHAR(20),
    minWeight           DECIMAL(10,2),
    maxWeight           DECIMAL(10,2),
    baseFee             DECIMAL(12,2),
    additionalPerKgRate DECIMAL(12,2),
    fragileSurcharge    DECIMAL(12,2),
    perAttemptCost      DECIMAL(12,2),
    effectiveFrom       DATE,
    effectiveTo         DATE NULL,
    isActive            BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE ParcelPricingRule (
    parcelId  VARCHAR(20) NOT NULL,
    ruleId    VARCHAR(20) NOT NULL,
    PRIMARY KEY (parcelId, ruleId),
    FOREIGN KEY (parcelId) REFERENCES Parcel(parcelId),
    FOREIGN KEY (ruleId)   REFERENCES PricingRule(ruleId)
);

CREATE TABLE CostEstimatePricingRule (
    estimateId  VARCHAR(20) NOT NULL,
    ruleId      VARCHAR(20) NOT NULL,
    PRIMARY KEY (estimateId, ruleId),
    FOREIGN KEY (estimateId) REFERENCES CostEstimate(estimateId),
    FOREIGN KEY (ruleId)     REFERENCES PricingRule(ruleId)
);

-- 7. CORPORATE AGREEMENTS

CREATE TABLE CorporateAgreement (
    agreementId          VARCHAR(20) PRIMARY KEY,
    startDate            DATE NOT NULL,
    endDate              DATE,
    terms                TEXT,
    discountRate         DECIMAL(5,2),
    paymentTerms         VARCHAR(100),
    status               VARCHAR(30),
    corporateCustomerId  VARCHAR(20) NOT NULL,
    financeMgrId         VARCHAR(20) NULL,
    FOREIGN KEY (corporateCustomerId) REFERENCES CorporateCustomer(userId),
    FOREIGN KEY (financeMgrId)        REFERENCES FinanceManager(userId)
);

CREATE TABLE CSManagerViewsAgreement (
    csManagerId  VARCHAR(20) NOT NULL,
    agreementId  VARCHAR(20) NOT NULL,
    PRIMARY KEY (csManagerId, agreementId),
    FOREIGN KEY (csManagerId) REFERENCES CustomerServiceManager(userId),
    FOREIGN KEY (agreementId) REFERENCES CorporateAgreement(agreementId)
);

-- 8. BILLING: INVOICES, PAYMENTS, RECEIPTS, REFUNDS

CREATE TABLE Invoice (
    invoiceId    VARCHAR(20) PRIMARY KEY,
    invoiceDate  DATE NOT NULL,
    dueDate      DATE,
    sourceType   VARCHAR(50),
    subtotal     DECIMAL(12,2) NOT NULL,
    taxAmount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    totalAmount  DECIMAL(12,2) NOT NULL,
    status       VARCHAR(30),
    sentAt       DATETIME NULL,
    customerId   VARCHAR(20) NOT NULL,
    csoId        VARCHAR(20) NULL,
    deliveryId   VARCHAR(20) NULL,
    FOREIGN KEY (customerId) REFERENCES Customer(userId),
    FOREIGN KEY (csoId)      REFERENCES CustomerServiceOfficer(userId),
    FOREIGN KEY (deliveryId) REFERENCES Delivery(deliveryId)
);

CREATE TABLE FinanceOfficerViewsInvoice (
    financeOfficerId  VARCHAR(20) NOT NULL,
    invoiceId         VARCHAR(20) NOT NULL,
    PRIMARY KEY (financeOfficerId, invoiceId),
    FOREIGN KEY (financeOfficerId) REFERENCES FinanceOfficer(userId),
    FOREIGN KEY (invoiceId)        REFERENCES Invoice(invoiceId)
);

CREATE TABLE Payment (
    paymentId         VARCHAR(20) PRIMARY KEY,
    amount            DECIMAL(12,2) NOT NULL,
    paymentDate       DATE NOT NULL,
    paymentMethod     VARCHAR(50),
    transactionRef    VARCHAR(100),
    invoiceId         VARCHAR(20) NOT NULL,
    financeOfficerId  VARCHAR(20) NULL,
    FOREIGN KEY (invoiceId)        REFERENCES Invoice(invoiceId),
    FOREIGN KEY (financeOfficerId) REFERENCES FinanceOfficer(userId)
);

CREATE TABLE Receipt (
    receiptId   VARCHAR(20) PRIMARY KEY,
    issuedDate  DATE NOT NULL,
    amount      DECIMAL(12,2) NOT NULL,
    paymentId   VARCHAR(20) NOT NULL,
    customerId  VARCHAR(20) NOT NULL,
    FOREIGN KEY (paymentId)  REFERENCES Payment(paymentId),
    FOREIGN KEY (customerId) REFERENCES Customer(userId)
);

CREATE TABLE Refund (
    refundId             VARCHAR(20) PRIMARY KEY,
    refundReason         VARCHAR(255),
    originalAmount       DECIMAL(12,2),
    attemptCount         INT,
    returnCost           DECIMAL(12,2),
    returnOption         VARCHAR(50),
    returnDeliveryFee    DECIMAL(12,2),
    refundMethod         VARCHAR(50),
    collectionBranch     VARCHAR(100),
    requestedAt          DATETIME NOT NULL,
    expiresAt            DATETIME NULL,
    expiryNotifiedAt     DATETIME NULL,
    expiryAcknowledged   BOOLEAN NOT NULL DEFAULT FALSE,
    processedAt          DATETIME NULL,
    status               VARCHAR(30),
    accountHolderName    VARCHAR(150),
    bankName             VARCHAR(100),
    branchName           VARCHAR(100),
    accountNumber        VARCHAR(50),
    customerId           VARCHAR(20) NOT NULL,
    paymentId            VARCHAR(20) NULL,
    parcelId             VARCHAR(20) NULL,
    financeMgrId         VARCHAR(20) NULL,
    issueId              VARCHAR(20) NULL,
    FOREIGN KEY (customerId)   REFERENCES Customer(userId),
    FOREIGN KEY (paymentId)    REFERENCES Payment(paymentId),
    FOREIGN KEY (parcelId)     REFERENCES Parcel(parcelId),
    FOREIGN KEY (financeMgrId) REFERENCES FinanceManager(userId),
    FOREIGN KEY (issueId)      REFERENCES PostDeliveryIssue(issueId)
);

-- 9. NOTIFICATIONS

CREATE TABLE Notification (
    notificationId     VARCHAR(20) PRIMARY KEY,
    notificationType   VARCHAR(50),
    channel            VARCHAR(30),
    recipientContact   VARCHAR(150),
    content            TEXT,
    sentAt             DATETIME NULL,
    status             VARCHAR(30),
    customerId         VARCHAR(20) NULL,
    receiverLinkId     VARCHAR(20) NULL,
    FOREIGN KEY (customerId)     REFERENCES Customer(userId),
    FOREIGN KEY (receiverLinkId) REFERENCES ReceiverLink(linkId)
);

-- 10. REPORTS

CREATE TABLE PaymentReport (
    reportId            VARCHAR(20) PRIMARY KEY,
    generatedAt         DATETIME NOT NULL,
    periodStart         DATE NOT NULL,
    periodEnd           DATE NOT NULL,
    totalCollected      DECIMAL(14,2),
    totalRefunded       DECIMAL(14,2),
    expiredRefundTotal  DECIMAL(14,2),
    financeMgrId        VARCHAR(20) NULL,
    FOREIGN KEY (financeMgrId) REFERENCES FinanceManager(userId)
);

CREATE TABLE DeliveryReport (
    reportId          VARCHAR(20) PRIMARY KEY,
    generatedAt       DATETIME NOT NULL,
    periodStart       DATE NOT NULL,
    periodEnd         DATE NOT NULL,
    totalDeliveries   INT,
    failedDeliveries  INT,
    returnedParcels   INT,
    transportMgrId    VARCHAR(20) NULL,
    FOREIGN KEY (transportMgrId) REFERENCES TransportManager(userId)
);

CREATE TABLE StorageReport (
    reportId             VARCHAR(20) PRIMARY KEY,
    generatedAt          DATETIME NOT NULL,
    periodStart          DATE NOT NULL,
    periodEnd            DATE NOT NULL,
    parcelsStored        INT,
    unclaimedParcels     INT,
    capacityUtilisation  DECIMAL(5,2),
    warehouseMgrId       VARCHAR(20) NULL,
    FOREIGN KEY (warehouseMgrId) REFERENCES WarehouseManager(userId)
);

CREATE TABLE CustomerReport (
    reportId           VARCHAR(20) PRIMARY KEY,
    generatedAt        DATETIME NOT NULL,
    periodStart        DATE NOT NULL,
    periodEnd          DATE NOT NULL,
    newCustomers       INT,
    activeCustomers    INT,
    corporateCustomers INT,
    csMgrId            VARCHAR(20) NULL,
    FOREIGN KEY (csMgrId) REFERENCES CustomerServiceManager(userId)
);

CREATE TABLE MDViewsPaymentReport (
    directorId  VARCHAR(20) NOT NULL,
    reportId    VARCHAR(20) NOT NULL,
    PRIMARY KEY (directorId, reportId),
    FOREIGN KEY (directorId) REFERENCES ManagingDirector(userId),
    FOREIGN KEY (reportId)   REFERENCES PaymentReport(reportId)
);

CREATE TABLE MDViewsDeliveryReport (
    directorId  VARCHAR(20) NOT NULL,
    reportId    VARCHAR(20) NOT NULL,
    PRIMARY KEY (directorId, reportId),
    FOREIGN KEY (directorId) REFERENCES ManagingDirector(userId),
    FOREIGN KEY (reportId)   REFERENCES DeliveryReport(reportId)
);

CREATE TABLE MDViewsStorageReport (
    directorId  VARCHAR(20) NOT NULL,
    reportId    VARCHAR(20) NOT NULL,
    PRIMARY KEY (directorId, reportId),
    FOREIGN KEY (directorId) REFERENCES ManagingDirector(userId),
    FOREIGN KEY (reportId)   REFERENCES StorageReport(reportId)
);

CREATE TABLE MDViewsCustomerReport (
    directorId  VARCHAR(20) NOT NULL,
    reportId    VARCHAR(20) NOT NULL,
    PRIMARY KEY (directorId, reportId),
    FOREIGN KEY (directorId) REFERENCES ManagingDirector(userId),
    FOREIGN KEY (reportId)   REFERENCES CustomerReport(reportId)
);

-- 11. WAREHOUSE OFFICER <-> PARCEL

CREATE TABLE WarehouseOfficerHandlesParcel (
    warehouseOfficerId  VARCHAR(20) NOT NULL,
    parcelId            VARCHAR(20) NOT NULL,
    PRIMARY KEY (warehouseOfficerId, parcelId),
    FOREIGN KEY (warehouseOfficerId) REFERENCES WarehouseOfficer(userId),
    FOREIGN KEY (parcelId)           REFERENCES Parcel(parcelId)
);

-- =====================================================================
-- DUMMY DATA
-- ID formats: USR-000001, WH-001, VEH-001, PCK-2026-000051,
--             HEN-2026-000123 (parcels), RCV-000001, DLV-2026-000201 ...
-- Password hashes are placeholders (not real bcrypt hashes).
-- =====================================================================

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
('PCK-2026-000051','2026-10-27 09:15:00','2026-10-28','09:00-12:00','23 Galle Road, Dehiwala','COMPLETED',NULL,NULL,NULL,'USR-000013','USR-000004','USR-000008','DRM-2026-000001'),
('PCK-2026-000052','2026-10-28 14:30:00','2026-10-29','13:00-16:00','78 Temple Road, Mount Lavinia','COMPLETED',NULL,NULL,NULL,'USR-000014','USR-000004','USR-000008','DRM-2026-000001'),
('PCK-2026-000053','2026-10-29 10:00:00','2026-10-31','09:00-12:00','100 Duke Street, Colombo 01','COMPLETED',NULL,NULL,NULL,'USR-000015','USR-000005','USR-000008','DRM-2026-000002'),
('PCK-2026-000054','2026-10-30 16:45:00',NULL,'09:00-12:00','23 Galle Road, Dehiwala','REJECTED','Address outside service zone',NULL,NULL,'USR-000013','USR-000005',NULL,NULL),
('PCK-2026-000055','2026-10-31 11:20:00',NULL,'13:00-16:00','78 Temple Road, Mount Lavinia','CANCELLED',NULL,NULL,'2026-10-31 15:00:00','USR-000014','USR-000004',NULL,NULL);

-- Receivers
INSERT INTO Receiver (receiverId, receiverName, receiverPhone, receiverAddress, zoneCode) VALUES
('RCV-000001','Kamal Dissanayake','+94712223301','15 Lake Road, Kandy','KDY-01'),
('RCV-000002','Priya Mendis','+94712223302','9 Hospital Road, Galle','GLE-01'),
('RCV-000003','Ishara Fonseka','+94712223303','67 Beach Road, Negombo','NGB-01');

-- Parcels
INSERT INTO Parcel (parcelId, description, parcelCategory, isFragile, serviceType, intakeMethod, declaredWeight, confirmedWeight, dimensions, detailsConfirmed, parcelReceivedDate, storageLocationCode, status, holdExpiresAt, failureReason, customerId, receiverId, csoId, pickupId, warehouseId) VALUES
('HEN-2026-000123','Ceramic tableware set','HOUSEHOLD',TRUE,'EXPRESS','PICKUP',4.50,4.70,'40x30x25',TRUE,'2026-10-28','A-03-12','DELIVERED',NULL,NULL,'USR-000013','RCV-000001','USR-000004','PCK-2026-000051','WH-001'),
('HEN-2026-000124','Legal documents','DOCUMENT',FALSE,'STANDARD','PICKUP',0.50,0.45,'35x25x5',TRUE,'2026-10-29','B-01-04','IN_TRANSIT',NULL,NULL,'USR-000014','RCV-000002','USR-000004','PCK-2026-000052','WH-001'),
('HEN-2026-000125','Tea packs - export samples','COMMERCIAL',FALSE,'STANDARD','PICKUP',12.00,12.30,'50x40x30',TRUE,'2026-10-31','C-02-07','FAILED_DELIVERY',NULL,'Receiver not available','USR-000015','RCV-000003','USR-000005','PCK-2026-000053','WH-001'),
('HEN-2026-000126','Handmade leather bag','PERSONAL',FALSE,'STANDARD','WALK_IN',1.80,1.80,'38x28x12',TRUE,'2026-11-01','A-01-02','IN_WAREHOUSE','2026-11-04 17:00:00',NULL,'USR-000013','RCV-000001','USR-000005',NULL,'WH-001');

INSERT INTO ReceiverLink (linkId, tokenHash, createdAt, expiresAt, isActive, parcelId, csoId) VALUES
('LNK-000001','$2b$10$dummytoken0000000000000000000000000000000000000000001','2026-10-28 12:00:00','2026-11-28 12:00:00',TRUE,'HEN-2026-000123','USR-000004'),
('LNK-000002','$2b$10$dummytoken0000000000000000000000000000000000000000002','2026-10-29 16:30:00','2026-11-29 16:30:00',TRUE,'HEN-2026-000124','USR-000004'),
('LNK-000003','$2b$10$dummytoken0000000000000000000000000000000000000000003','2026-10-31 13:00:00','2026-11-30 13:00:00',TRUE,'HEN-2026-000125','USR-000005');

-- Post-delivery issue
INSERT INTO PostDeliveryIssue (issueId, issueType, description, submittedAt, status, faultParty, decision, decisionNotes, decidedAt, receiverLinkId, csMgrId) VALUES
('ISS-2026-000001','DAMAGED','Two plates arrived cracked.','2026-10-31 10:15:00','RESOLVED','CARRIER','REFUND_APPROVED','Packaging photos confirm transit damage.','2026-10-31 15:40:00','LNK-000001','USR-000003');

-- Deliveries
INSERT INTO Delivery (deliveryId, direction, deliveryAddress, deliveryDate, estimatedFrom, estimatedTo, status, failedAttemptCount, returnCostBearer, parcelId, deliveryManifestId, driverManifestId, issueId) VALUES
('DLV-2026-000201','OUTBOUND','15 Lake Road, Kandy','2026-10-30','2026-10-30 10:00:00','2026-10-30 14:00:00','DELIVERED',0,NULL,'HEN-2026-000123','DM-2026-000001','DRM-2026-000001','ISS-2026-000001'),
('DLV-2026-000202','OUTBOUND','9 Hospital Road, Galle','2026-11-02','2026-11-02 09:00:00','2026-11-02 13:00:00','IN_TRANSIT',0,NULL,'HEN-2026-000124','DM-2026-000002','DRM-2026-000002',NULL),
('DLV-2026-000203','OUTBOUND','67 Beach Road, Negombo','2026-11-01','2026-11-01 09:00:00','2026-11-01 13:00:00','FAILED',1,'CUSTOMER','HEN-2026-000125','DM-2026-000002','DRM-2026-000002',NULL);

INSERT INTO DeliveryAttempt (attemptId, attemptNumber, attemptedAt, arrivedAt, outcome, failureReason, nextAttemptAt, deliveryId) VALUES
('ATT-000001',1,'2026-10-30 11:20:00','2026-10-30 11:15:00','SUCCESS',NULL,NULL,'DLV-2026-000201'),
('ATT-000002',1,'2026-11-01 10:05:00','2026-11-01 10:00:00','FAILED','Receiver not available','2026-11-02 10:00:00','DLV-2026-000203');

INSERT INTO DeliveryVerification (verificationId, pinHash, generatedAt, expiresAt, verifiedAt, attemptCount, status, attemptId, receiverLinkId) VALUES
('VER-000001','$2b$10$dummypin000000000000000000000000000000000000000000001','2026-10-30 11:00:00','2026-10-30 12:00:00','2026-10-30 11:18:00',1,'VERIFIED','ATT-000001','LNK-000001'),
('VER-000002','$2b$10$dummypin000000000000000000000000000000000000000000002','2026-11-01 09:50:00','2026-11-01 10:50:00',NULL,0,'EXPIRED','ATT-000002','LNK-000003');

-- Tracking events (actorId is polymorphic - no FK)
INSERT INTO TrackingEvent (eventId, eventType, occurredAt, locationDescription, remarks, isVisibleToReceiver, actorType, actorId, parcelId, receiverLinkId, deliveryId, warehouseId) VALUES
('EVT-000001','PICKED_UP','2026-10-28 10:30:00','Dehiwala','Collected from sender',TRUE,'DRIVER','USR-000009','HEN-2026-000123','LNK-000001',NULL,NULL),
('EVT-000002','RECEIVED_AT_WAREHOUSE','2026-10-28 13:00:00','Colombo Central Hub','Weight confirmed 4.70kg',TRUE,'WAREHOUSE_OFFICER','USR-000012','HEN-2026-000123','LNK-000001',NULL,'WH-001'),
('EVT-000003','OUT_FOR_DELIVERY','2026-10-30 08:30:00','Colombo Central Hub','Loaded on VEH-001',TRUE,'DRIVER','USR-000009','HEN-2026-000123','LNK-000001','DLV-2026-000201','WH-001'),
('EVT-000004','DELIVERED','2026-10-30 11:20:00','Kandy','PIN verified',TRUE,'DRIVER','USR-000009','HEN-2026-000123','LNK-000001','DLV-2026-000201',NULL),
('EVT-000005','RECEIVED_AT_WAREHOUSE','2026-10-29 17:00:00','Colombo Central Hub','Intake complete',TRUE,'WAREHOUSE_OFFICER','USR-000012','HEN-2026-000124','LNK-000002',NULL,'WH-001'),
('EVT-000006','OUT_FOR_DELIVERY','2026-11-01 07:30:00','Colombo Central Hub','Loaded on VEH-002',TRUE,'DRIVER','USR-000010','HEN-2026-000124','LNK-000002','DLV-2026-000202','WH-001'),
('EVT-000007','DELIVERY_FAILED','2026-11-01 10:05:00','Negombo','Receiver not available',TRUE,'DRIVER','USR-000010','HEN-2026-000125','LNK-000003','DLV-2026-000203',NULL);

-- Pricing
INSERT INTO PricingRule (ruleId, serviceType, zoneCode, minWeight, maxWeight, baseFee, additionalPerKgRate, fragileSurcharge, perAttemptCost, effectiveFrom, effectiveTo, isActive) VALUES
('PR-001','STANDARD','KDY-01',0.00,5.00,450.00,80.00,150.00,200.00,'2026-01-01',NULL,TRUE),
('PR-002','EXPRESS','KDY-01',0.00,5.00,800.00,120.00,250.00,350.00,'2026-01-01',NULL,TRUE),
('PR-003','STANDARD','GLE-01',0.00,5.00,500.00,90.00,150.00,200.00,'2026-01-01',NULL,TRUE),
('PR-004','STANDARD','NGB-01',5.01,20.00,600.00,100.00,150.00,250.00,'2026-01-01',NULL,TRUE);

INSERT INTO ParcelPricingRule (parcelId, ruleId) VALUES
('HEN-2026-000123','PR-002'),
('HEN-2026-000124','PR-003'),
('HEN-2026-000125','PR-004');

INSERT INTO CostEstimate (estimateId, baseCharge, surcharges, totalEstimate, calculatedAt, pickupId) VALUES
('EST-000001',800.00,250.00,1050.00,'2026-10-27 09:20:00','PCK-2026-000051'),
('EST-000002',500.00,0.00,500.00,'2026-10-28 14:35:00','PCK-2026-000052'),
('EST-000003',600.00,0.00,600.00,'2026-10-29 10:05:00','PCK-2026-000053');

INSERT INTO CostEstimatePricingRule (estimateId, ruleId) VALUES
('EST-000001','PR-002'),('EST-000002','PR-003'),('EST-000003','PR-004');

-- Corporate agreement
INSERT INTO CorporateAgreement (agreementId, startDate, endDate, terms, discountRate, paymentTerms, status, corporateCustomerId, financeMgrId) VALUES
('AGR-2026-000001','2026-01-01','2026-12-31','Volume-based discount; monthly invoicing.',10.00,'NET_30','ACTIVE','USR-000015','USR-000006');

INSERT INTO CSManagerViewsAgreement (csManagerId, agreementId) VALUES
('USR-000003','AGR-2026-000001');

-- Billing
INSERT INTO Invoice (invoiceId, invoiceDate, dueDate, sourceType, subtotal, taxAmount, totalAmount, status, sentAt, customerId, csoId, deliveryId) VALUES
('INV-2026-000301','2026-10-30','2026-11-13','DELIVERY',1050.00,189.00,1239.00,'PAID','2026-10-30 12:00:00','USR-000013','USR-000004','DLV-2026-000201'),
('INV-2026-000302','2026-11-01','2026-11-15','DELIVERY',500.00,90.00,590.00,'SENT','2026-11-01 08:00:00','USR-000014','USR-000004','DLV-2026-000202'),
('INV-2026-000303','2026-11-01','2026-12-01','DELIVERY',540.00,97.20,637.20,'SENT','2026-11-01 11:00:00','USR-000015','USR-000005','DLV-2026-000203');

INSERT INTO FinanceOfficerViewsInvoice (financeOfficerId, invoiceId) VALUES
('USR-000007','INV-2026-000301'),('USR-000007','INV-2026-000302'),('USR-000007','INV-2026-000303');

INSERT INTO Payment (paymentId, amount, paymentDate, paymentMethod, transactionRef, invoiceId, financeOfficerId) VALUES
('PAY-2026-000401',1239.00,'2026-10-30','CARD','TXN-88120934','INV-2026-000301','USR-000007');

INSERT INTO Receipt (receiptId, issuedDate, amount, paymentId, customerId) VALUES
('RCT-2026-000501','2026-10-30',1239.00,'PAY-2026-000401','USR-000013');

INSERT INTO Refund (refundId, refundReason, originalAmount, attemptCount, returnCost, returnOption, returnDeliveryFee, refundMethod, collectionBranch, requestedAt, expiresAt, expiryNotifiedAt, expiryAcknowledged, processedAt, status, accountHolderName, bankName, branchName, accountNumber, customerId, paymentId, parcelId, financeMgrId, issueId) VALUES
('RFD-2026-000601','Damaged item in transit',1239.00,1,0.00,'NO_RETURN',0.00,'BANK_TRANSFER',NULL,'2026-10-31 15:45:00','2026-12-31 23:59:59',NULL,FALSE,NULL,'PENDING','Amal Rathnayake','Bank of Ceylon','Dehiwala','0012345678','USR-000013','PAY-2026-000401','HEN-2026-000123','USR-000006','ISS-2026-000001');

-- Notifications
INSERT INTO Notification (notificationId, notificationType, channel, recipientContact, content, sentAt, status, customerId, receiverLinkId) VALUES
('NTF-000001','PICKUP_CONFIRMED','EMAIL','amal.r@example.com','Your pickup PCK-2026-000051 is confirmed for 28 Oct.','2026-10-27 09:30:00','SENT','USR-000013',NULL),
('NTF-000002','DELIVERY_PIN','SMS','+94712223301','Your delivery PIN for HEN-2026-000123 is ready. Open your tracking link.','2026-10-30 11:00:00','SENT',NULL,'LNK-000001'),
('NTF-000003','DELIVERY_FAILED','EMAIL','orders@lankatea.example.com','Delivery of HEN-2026-000125 failed. Next attempt: 2 Nov.','2026-11-01 10:10:00','SENT','USR-000015','LNK-000003');

-- Reports
INSERT INTO PaymentReport (reportId, generatedAt, periodStart, periodEnd, totalCollected, totalRefunded, expiredRefundTotal, financeMgrId) VALUES
('RPT-PAY-2026-10','2026-11-01 06:00:00','2026-10-01','2026-10-31',1239.00,0.00,0.00,'USR-000006');
INSERT INTO DeliveryReport (reportId, generatedAt, periodStart, periodEnd, totalDeliveries, failedDeliveries, returnedParcels, transportMgrId) VALUES
('RPT-DLV-2026-10','2026-11-01 06:05:00','2026-10-01','2026-10-31',3,1,0,'USR-000008');
INSERT INTO StorageReport (reportId, generatedAt, periodStart, periodEnd, parcelsStored, unclaimedParcels, capacityUtilisation, warehouseMgrId) VALUES
('RPT-STO-2026-10','2026-11-01 06:10:00','2026-10-01','2026-10-31',4,0,24.80,'USR-000011');
INSERT INTO CustomerReport (reportId, generatedAt, periodStart, periodEnd, newCustomers, activeCustomers, corporateCustomers, csMgrId) VALUES
('RPT-CUS-2026-10','2026-11-01 06:15:00','2026-10-01','2026-10-31',3,3,1,'USR-000003');

INSERT INTO MDViewsPaymentReport VALUES ('USR-000002','RPT-PAY-2026-10');
INSERT INTO MDViewsDeliveryReport VALUES ('USR-000002','RPT-DLV-2026-10');
INSERT INTO MDViewsStorageReport VALUES ('USR-000002','RPT-STO-2026-10');
INSERT INTO MDViewsCustomerReport VALUES ('USR-000002','RPT-CUS-2026-10');

INSERT INTO WarehouseOfficerHandlesParcel (warehouseOfficerId, parcelId) VALUES
('USR-000012','HEN-2026-000123'),('USR-000012','HEN-2026-000124'),
('USR-000012','HEN-2026-000125'),('USR-000012','HEN-2026-000126');
