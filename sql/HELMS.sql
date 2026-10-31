-- HELMS database schema (generated from FINAL_FIXED_RELATIONAL_SCHEMA)

CREATE DATABASE IF NOT EXISTS HELMS;
USE HELMS;

-- 1. USERS AND ROLE TABLES

-- User.adminId -> SystemAdmin.userId is added later via ALTER TABLE
-- (User and SystemAdmin reference each other).
CREATE TABLE User (
    userId        INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    passwordHash  VARCHAR(255) NOT NULL,
    role          VARCHAR(50)  NOT NULL,
    adminId       INT NULL
);

CREATE TABLE SystemAdmin (
    userId         INT PRIMARY KEY,
    accessLevel    VARCHAR(50) NOT NULL,
    lastLoginDate  DATETIME NULL,
    FOREIGN KEY (userId) REFERENCES User(userId)
);

ALTER TABLE User
    ADD CONSTRAINT fk_user_admin FOREIGN KEY (adminId) REFERENCES SystemAdmin(userId);

CREATE TABLE Warehouse (
    warehouseId    INT AUTO_INCREMENT PRIMARY KEY,
    warehouseName  VARCHAR(150) NOT NULL,
    address        VARCHAR(255) NOT NULL,
    zoneCode       VARCHAR(20)  NOT NULL,
    status         VARCHAR(30)  NOT NULL,
    capacity       INT NOT NULL,
    currentLoad    INT NOT NULL DEFAULT 0
);

CREATE TABLE CustomerServiceOfficer (
    userId      INT PRIMARY KEY,
    deskNumber  VARCHAR(20),
    shift       VARCHAR(30),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE CustomerServiceManager (
    userId      INT PRIMARY KEY,
    region      VARCHAR(100),
    department  VARCHAR(100),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE FinanceOfficer (
    userId         INT PRIMARY KEY,
    contactNumber  VARCHAR(30),
    shift          VARCHAR(30),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE FinanceManager (
    userId         INT PRIMARY KEY,
    approvalLimit  DECIMAL(12,2),
    department     VARCHAR(100),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE TransportManager (
    userId        INT PRIMARY KEY,
    region        VARCHAR(100),
    officeNumber  VARCHAR(20),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE Driver (
    userId          INT PRIMARY KEY,
    licenseNumber   VARCHAR(50) NOT NULL UNIQUE,
    contactNumber   VARCHAR(30),
    status          VARCHAR(30),
    transportMgrId  INT NULL,
    FOREIGN KEY (userId) REFERENCES User(userId),
    FOREIGN KEY (transportMgrId) REFERENCES TransportManager(userId)
);

CREATE TABLE WarehouseOfficer (
    userId        INT PRIMARY KEY,
    assignedZone  VARCHAR(20),
    shift         VARCHAR(30),
    warehouseId   INT NULL,
    FOREIGN KEY (userId) REFERENCES User(userId),
    FOREIGN KEY (warehouseId) REFERENCES Warehouse(warehouseId)
);

CREATE TABLE WarehouseManager (
    userId        INT PRIMARY KEY,
    officeNumber  VARCHAR(20),
    warehouseId   INT NULL,
    FOREIGN KEY (userId) REFERENCES User(userId),
    FOREIGN KEY (warehouseId) REFERENCES Warehouse(warehouseId)
);

CREATE TABLE ManagingDirector (
    userId           INT PRIMARY KEY,
    appointmentDate  DATE,
    officeNumber     VARCHAR(20),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

-- 2. CUSTOMERS

CREATE TABLE Customer (
    userId         INT PRIMARY KEY,
    contactNumber  VARCHAR(30),
    email          VARCHAR(150),
    address        VARCHAR(255),
    FOREIGN KEY (userId) REFERENCES User(userId)
);

CREATE TABLE IndividualCustomer (
    userId   INT PRIMARY KEY,
    name     VARCHAR(150) NOT NULL,
    nic      VARCHAR(20),
    csMgrId  INT NULL,
    FOREIGN KEY (userId) REFERENCES Customer(userId),
    FOREIGN KEY (csMgrId) REFERENCES CustomerServiceManager(userId)
);

CREATE TABLE CorporateCustomer (
    userId         INT PRIMARY KEY,
    companyName    VARCHAR(150) NOT NULL,
    contactPerson  VARCHAR(150),
    csMgrId        INT NULL,
    FOREIGN KEY (userId) REFERENCES Customer(userId),
    FOREIGN KEY (csMgrId) REFERENCES CustomerServiceManager(userId)
);

-- 3. TRANSPORT: VEHICLES AND MANIFESTS

CREATE TABLE Vehicle (
    vehicleId           INT AUTO_INCREMENT PRIMARY KEY,
    registrationNumber  VARCHAR(30) NOT NULL UNIQUE,
    vehicleType         VARCHAR(50),
    capacity            DECIMAL(10,2),
    isAvailable         BOOLEAN NOT NULL DEFAULT TRUE,
    transportMgrId      INT NULL,
    FOREIGN KEY (transportMgrId) REFERENCES TransportManager(userId)
);

CREATE TABLE DeliveryManifest (
    deliveryManifestId  INT AUTO_INCREMENT PRIMARY KEY,
    manifestDate        DATE NOT NULL,
    route               VARCHAR(255),
    manifestStatus      VARCHAR(30),
    transportMgrId      INT NULL,
    FOREIGN KEY (transportMgrId) REFERENCES TransportManager(userId)
);

CREATE TABLE DriverManifest (
    driverManifestId    INT AUTO_INCREMENT PRIMARY KEY,
    assignedDate        DATE NOT NULL,
    schedule            VARCHAR(255),
    status              VARCHAR(30),
    transportMgrId      INT NULL,
    driverId            INT NULL,
    deliveryManifestId  INT NULL,
    vehicleId           INT NULL,
    FOREIGN KEY (transportMgrId)     REFERENCES TransportManager(userId),
    FOREIGN KEY (driverId)           REFERENCES Driver(userId),
    FOREIGN KEY (deliveryManifestId) REFERENCES DeliveryManifest(deliveryManifestId),
    FOREIGN KEY (vehicleId)          REFERENCES Vehicle(vehicleId)
);

-- 4. PICKUPS, RECEIVERS, PARCELS

CREATE TABLE Pickup (
    pickupId             INT AUTO_INCREMENT PRIMARY KEY,
    requestedAt          DATETIME NOT NULL,
    pickupDate           DATE,
    preferredTimeWindow  VARCHAR(50),
    pickupAddress        VARCHAR(255) NOT NULL,
    status               VARCHAR(30),
    rejectionReason      VARCHAR(255),
    failureReason        VARCHAR(255),
    cancelledAt          DATETIME NULL,
    customerId           INT NOT NULL,
    csoId                INT NULL,
    transportMgrId       INT NULL,
    driverManifestId     INT NULL,
    FOREIGN KEY (customerId)       REFERENCES Customer(userId),
    FOREIGN KEY (csoId)            REFERENCES CustomerServiceOfficer(userId),
    FOREIGN KEY (transportMgrId)   REFERENCES TransportManager(userId),
    FOREIGN KEY (driverManifestId) REFERENCES DriverManifest(driverManifestId)
);

CREATE TABLE Receiver (
    receiverId       INT AUTO_INCREMENT PRIMARY KEY,
    receiverName     VARCHAR(150) NOT NULL,
    receiverPhone    VARCHAR(30),
    receiverAddress  VARCHAR(255),
    zoneCode         VARCHAR(20)
);

CREATE TABLE Parcel (
    parcelId             INT AUTO_INCREMENT PRIMARY KEY,
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
    customerId           INT NOT NULL,
    receiverId           INT NOT NULL,
    csoId                INT NULL,
    pickupId             INT NULL,
    warehouseId          INT NULL,
    FOREIGN KEY (customerId)  REFERENCES Customer(userId),
    FOREIGN KEY (receiverId)  REFERENCES Receiver(receiverId),
    FOREIGN KEY (csoId)       REFERENCES CustomerServiceOfficer(userId),
    FOREIGN KEY (pickupId)    REFERENCES Pickup(pickupId),
    FOREIGN KEY (warehouseId) REFERENCES Warehouse(warehouseId)
);

CREATE TABLE ReceiverLink (
    linkId     INT AUTO_INCREMENT PRIMARY KEY,
    tokenHash  VARCHAR(255) NOT NULL,
    createdAt  DATETIME NOT NULL,
    expiresAt  DATETIME NOT NULL,
    isActive   BOOLEAN NOT NULL DEFAULT TRUE,
    parcelId   INT NOT NULL,
    csoId      INT NULL,
    FOREIGN KEY (parcelId) REFERENCES Parcel(parcelId),
    FOREIGN KEY (csoId)    REFERENCES CustomerServiceOfficer(userId)
);

-- 5. POST-DELIVERY ISSUES, DELIVERIES

CREATE TABLE PostDeliveryIssue (
    issueId         INT AUTO_INCREMENT PRIMARY KEY,
    issueType       VARCHAR(50),
    description     TEXT,
    submittedAt     DATETIME NOT NULL,
    status          VARCHAR(30),
    faultParty      VARCHAR(50),
    decision        VARCHAR(50),
    decisionNotes   TEXT,
    decidedAt       DATETIME NULL,
    receiverLinkId  INT NULL,
    csMgrId         INT NULL,
    FOREIGN KEY (receiverLinkId) REFERENCES ReceiverLink(linkId),
    FOREIGN KEY (csMgrId)        REFERENCES CustomerServiceManager(userId)
);

CREATE TABLE Delivery (
    deliveryId          INT AUTO_INCREMENT PRIMARY KEY,
    direction           VARCHAR(30),
    deliveryAddress     VARCHAR(255),
    deliveryDate        DATE,
    estimatedFrom       DATETIME,
    estimatedTo         DATETIME,
    status              VARCHAR(30),
    failedAttemptCount  INT NOT NULL DEFAULT 0,
    returnCostBearer    VARCHAR(30),
    parcelId            INT NOT NULL,
    deliveryManifestId  INT NULL,
    driverManifestId    INT NULL,
    issueId             INT NULL,
    FOREIGN KEY (parcelId)           REFERENCES Parcel(parcelId),
    FOREIGN KEY (deliveryManifestId) REFERENCES DeliveryManifest(deliveryManifestId),
    FOREIGN KEY (driverManifestId)   REFERENCES DriverManifest(driverManifestId),
    FOREIGN KEY (issueId)            REFERENCES PostDeliveryIssue(issueId)
);

CREATE TABLE DeliveryAttempt (
    attemptId      INT AUTO_INCREMENT PRIMARY KEY,
    attemptNumber  INT NOT NULL,
    attemptedAt    DATETIME,
    arrivedAt      DATETIME,
    outcome        VARCHAR(30),
    failureReason  VARCHAR(255),
    nextAttemptAt  DATETIME NULL,
    deliveryId     INT NOT NULL,
    FOREIGN KEY (deliveryId) REFERENCES Delivery(deliveryId)
);

CREATE TABLE DeliveryVerification (
    verificationId  INT AUTO_INCREMENT PRIMARY KEY,
    pinHash         VARCHAR(255) NOT NULL,
    generatedAt     DATETIME NOT NULL,
    expiresAt       DATETIME NOT NULL,
    verifiedAt      DATETIME NULL,
    attemptCount    INT NOT NULL DEFAULT 0,
    status          VARCHAR(30),
    attemptId       INT NULL,
    receiverLinkId  INT NULL,
    FOREIGN KEY (attemptId)      REFERENCES DeliveryAttempt(attemptId),
    FOREIGN KEY (receiverLinkId) REFERENCES ReceiverLink(linkId)
);

-- actorId is polymorphic (depends on actorType), so it has no FK.
CREATE TABLE TrackingEvent (
    eventId              INT AUTO_INCREMENT PRIMARY KEY,
    eventType            VARCHAR(50) NOT NULL,
    occurredAt           DATETIME NOT NULL,
    locationDescription  VARCHAR(255),
    remarks              VARCHAR(255),
    isVisibleToReceiver  BOOLEAN NOT NULL DEFAULT TRUE,
    actorType            VARCHAR(50),
    actorId              INT NULL,
    parcelId             INT NULL,
    receiverLinkId       INT NULL,
    deliveryId           INT NULL,
    warehouseId          INT NULL,
    FOREIGN KEY (parcelId)       REFERENCES Parcel(parcelId),
    FOREIGN KEY (receiverLinkId) REFERENCES ReceiverLink(linkId),
    FOREIGN KEY (deliveryId)     REFERENCES Delivery(deliveryId),
    FOREIGN KEY (warehouseId)    REFERENCES Warehouse(warehouseId)
);

-- 6. PRICING

CREATE TABLE CostEstimate (
    estimateId     INT AUTO_INCREMENT PRIMARY KEY,
    baseCharge     DECIMAL(12,2) NOT NULL,
    surcharges     DECIMAL(12,2) NOT NULL DEFAULT 0,
    totalEstimate  DECIMAL(12,2) NOT NULL,
    calculatedAt   DATETIME NOT NULL,
    pickupId       INT NULL,
    FOREIGN KEY (pickupId) REFERENCES Pickup(pickupId)
);

CREATE TABLE PricingRule (
    ruleId              INT AUTO_INCREMENT PRIMARY KEY,
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
    parcelId  INT NOT NULL,
    ruleId    INT NOT NULL,
    PRIMARY KEY (parcelId, ruleId),
    FOREIGN KEY (parcelId) REFERENCES Parcel(parcelId),
    FOREIGN KEY (ruleId)   REFERENCES PricingRule(ruleId)
);

CREATE TABLE CostEstimatePricingRule (
    estimateId  INT NOT NULL,
    ruleId      INT NOT NULL,
    PRIMARY KEY (estimateId, ruleId),
    FOREIGN KEY (estimateId) REFERENCES CostEstimate(estimateId),
    FOREIGN KEY (ruleId)     REFERENCES PricingRule(ruleId)
);

-- 7. CORPORATE AGREEMENTS

CREATE TABLE CorporateAgreement (
    agreementId          INT AUTO_INCREMENT PRIMARY KEY,
    startDate            DATE NOT NULL,
    endDate              DATE,
    terms                TEXT,
    discountRate         DECIMAL(5,2),
    paymentTerms         VARCHAR(100),
    status               VARCHAR(30),
    corporateCustomerId  INT NOT NULL,
    financeMgrId         INT NULL,
    FOREIGN KEY (corporateCustomerId) REFERENCES CorporateCustomer(userId),
    FOREIGN KEY (financeMgrId)        REFERENCES FinanceManager(userId)
);

CREATE TABLE CSManagerViewsAgreement (
    csManagerId  INT NOT NULL,
    agreementId  INT NOT NULL,
    PRIMARY KEY (csManagerId, agreementId),
    FOREIGN KEY (csManagerId) REFERENCES CustomerServiceManager(userId),
    FOREIGN KEY (agreementId) REFERENCES CorporateAgreement(agreementId)
);

-- 8. BILLING: INVOICES, PAYMENTS, RECEIPTS, REFUNDS

CREATE TABLE Invoice (
    invoiceId    INT AUTO_INCREMENT PRIMARY KEY,
    invoiceDate  DATE NOT NULL,
    dueDate      DATE,
    sourceType   VARCHAR(50),
    subtotal     DECIMAL(12,2) NOT NULL,
    taxAmount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    totalAmount  DECIMAL(12,2) NOT NULL,
    status       VARCHAR(30),
    sentAt       DATETIME NULL,
    customerId   INT NOT NULL,
    csoId        INT NULL,
    deliveryId   INT NULL,
    FOREIGN KEY (customerId) REFERENCES Customer(userId),
    FOREIGN KEY (csoId)      REFERENCES CustomerServiceOfficer(userId),
    FOREIGN KEY (deliveryId) REFERENCES Delivery(deliveryId)
);

CREATE TABLE FinanceOfficerViewsInvoice (
    financeOfficerId  INT NOT NULL,
    invoiceId         INT NOT NULL,
    PRIMARY KEY (financeOfficerId, invoiceId),
    FOREIGN KEY (financeOfficerId) REFERENCES FinanceOfficer(userId),
    FOREIGN KEY (invoiceId)        REFERENCES Invoice(invoiceId)
);

CREATE TABLE Payment (
    paymentId         INT AUTO_INCREMENT PRIMARY KEY,
    amount            DECIMAL(12,2) NOT NULL,
    paymentDate       DATE NOT NULL,
    paymentMethod     VARCHAR(50),
    transactionRef    VARCHAR(100),
    invoiceId         INT NOT NULL,
    financeOfficerId  INT NULL,
    FOREIGN KEY (invoiceId)        REFERENCES Invoice(invoiceId),
    FOREIGN KEY (financeOfficerId) REFERENCES FinanceOfficer(userId)
);

CREATE TABLE Receipt (
    receiptId   INT AUTO_INCREMENT PRIMARY KEY,
    issuedDate  DATE NOT NULL,
    amount      DECIMAL(12,2) NOT NULL,
    paymentId   INT NOT NULL,
    customerId  INT NOT NULL,
    FOREIGN KEY (paymentId)  REFERENCES Payment(paymentId),
    FOREIGN KEY (customerId) REFERENCES Customer(userId)
);

CREATE TABLE Refund (
    refundId             INT AUTO_INCREMENT PRIMARY KEY,
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
    customerId           INT NOT NULL,
    paymentId            INT NULL,
    parcelId             INT NULL,
    financeMgrId         INT NULL,
    issueId              INT NULL,
    FOREIGN KEY (customerId)   REFERENCES Customer(userId),
    FOREIGN KEY (paymentId)    REFERENCES Payment(paymentId),
    FOREIGN KEY (parcelId)     REFERENCES Parcel(parcelId),
    FOREIGN KEY (financeMgrId) REFERENCES FinanceManager(userId),
    FOREIGN KEY (issueId)      REFERENCES PostDeliveryIssue(issueId)
);

-- 9. NOTIFICATIONS

CREATE TABLE Notification (
    notificationId     INT AUTO_INCREMENT PRIMARY KEY,
    notificationType   VARCHAR(50),
    channel            VARCHAR(30),
    recipientContact   VARCHAR(150),
    content            TEXT,
    sentAt             DATETIME NULL,
    status             VARCHAR(30),
    customerId         INT NULL,
    receiverLinkId     INT NULL,
    FOREIGN KEY (customerId)     REFERENCES Customer(userId),
    FOREIGN KEY (receiverLinkId) REFERENCES ReceiverLink(linkId)
);

-- 10. REPORTS

CREATE TABLE PaymentReport (
    reportId            INT AUTO_INCREMENT PRIMARY KEY,
    generatedAt         DATETIME NOT NULL,
    periodStart         DATE NOT NULL,
    periodEnd           DATE NOT NULL,
    totalCollected      DECIMAL(14,2),
    totalRefunded       DECIMAL(14,2),
    expiredRefundTotal  DECIMAL(14,2),
    financeMgrId        INT NULL,
    FOREIGN KEY (financeMgrId) REFERENCES FinanceManager(userId)
);

CREATE TABLE DeliveryReport (
    reportId          INT AUTO_INCREMENT PRIMARY KEY,
    generatedAt       DATETIME NOT NULL,
    periodStart       DATE NOT NULL,
    periodEnd         DATE NOT NULL,
    totalDeliveries   INT,
    failedDeliveries  INT,
    returnedParcels   INT,
    transportMgrId    INT NULL,
    FOREIGN KEY (transportMgrId) REFERENCES TransportManager(userId)
);

CREATE TABLE StorageReport (
    reportId             INT AUTO_INCREMENT PRIMARY KEY,
    generatedAt          DATETIME NOT NULL,
    periodStart          DATE NOT NULL,
    periodEnd            DATE NOT NULL,
    parcelsStored        INT,
    unclaimedParcels     INT,
    capacityUtilisation  DECIMAL(5,2),
    warehouseMgrId       INT NULL,
    FOREIGN KEY (warehouseMgrId) REFERENCES WarehouseManager(userId)
);

CREATE TABLE CustomerReport (
    reportId           INT AUTO_INCREMENT PRIMARY KEY,
    generatedAt        DATETIME NOT NULL,
    periodStart        DATE NOT NULL,
    periodEnd          DATE NOT NULL,
    newCustomers       INT,
    activeCustomers    INT,
    corporateCustomers INT,
    csMgrId            INT NULL,
    FOREIGN KEY (csMgrId) REFERENCES CustomerServiceManager(userId)
);

CREATE TABLE MDViewsPaymentReport (
    directorId  INT NOT NULL,
    reportId    INT NOT NULL,
    PRIMARY KEY (directorId, reportId),
    FOREIGN KEY (directorId) REFERENCES ManagingDirector(userId),
    FOREIGN KEY (reportId)   REFERENCES PaymentReport(reportId)
);

CREATE TABLE MDViewsDeliveryReport (
    directorId  INT NOT NULL,
    reportId    INT NOT NULL,
    PRIMARY KEY (directorId, reportId),
    FOREIGN KEY (directorId) REFERENCES ManagingDirector(userId),
    FOREIGN KEY (reportId)   REFERENCES DeliveryReport(reportId)
);

CREATE TABLE MDViewsStorageReport (
    directorId  INT NOT NULL,
    reportId    INT NOT NULL,
    PRIMARY KEY (directorId, reportId),
    FOREIGN KEY (directorId) REFERENCES ManagingDirector(userId),
    FOREIGN KEY (reportId)   REFERENCES StorageReport(reportId)
);

CREATE TABLE MDViewsCustomerReport (
    directorId  INT NOT NULL,
    reportId    INT NOT NULL,
    PRIMARY KEY (directorId, reportId),
    FOREIGN KEY (directorId) REFERENCES ManagingDirector(userId),
    FOREIGN KEY (reportId)   REFERENCES CustomerReport(reportId)
);

-- 11. WAREHOUSE OFFICER <-> PARCEL

CREATE TABLE WarehouseOfficerHandlesParcel (
    warehouseOfficerId  INT NOT NULL,
    parcelId            INT NOT NULL,
    PRIMARY KEY (warehouseOfficerId, parcelId),
    FOREIGN KEY (warehouseOfficerId) REFERENCES WarehouseOfficer(userId),
    FOREIGN KEY (parcelId)           REFERENCES Parcel(parcelId)
);
