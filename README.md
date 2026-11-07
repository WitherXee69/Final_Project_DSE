# HELMS – Henowic Enterprise Logistics Management System

## Project Overview

HELMS is a web-based logistics management system developed to support the operations of a courier and logistics company. It provides separate interfaces for customers and staff members to manage pickup requests, parcels, deliveries, warehouse operations, customer records, and reports.

## Features

### Public Portal

- View the public homepage.
- Register as an individual customer.
- Log in using registered account credentials.
- Track parcels using a tracking number.

### Customer Portal

- Submit new pickup requests.
- View pickup request history.
- Search and filter pickup requests.
- View pickup request details.
- Edit or cancel eligible pickup requests.

### Customer Service Manager Portal

- View the customer directory.
- Search and filter customer records.
- Register corporate customers.
- Update customer information.
- Deactivate customer records.

### Driver Portal

- View assigned delivery manifests.
- Update driver availability.
- Mark pickup requests as successful or failed.
- Update delivery arrival status.
- Submit receiver PINs to confirm deliveries.

### Transport Manager Portal

- View driver information and statistics.
- Register new drivers.
- Update driver details, including license and contact information.
- View driver availability and status.
- Delete eligible driver records.

### Warehouse Portal

- View and search parcel records.
- Receive parcels into the warehouse.
- View and edit parcel details.
- Confirm parcel details and weight.
- Assign storage locations.
- Process parcels through packing and release operations.
- Generate and save storage reports.

### Managing Director Portal

- View dashboard summaries.
- View available reports.
- Access saved Payment, Delivery, Storage, and Customer reports.
- Open reports in PDF format.

### System Administrator Portal

- View system statistics.
- View user counts by role.
- Search and filter user records.
- Manage eligible user records.

## Technology Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL / MariaDB
- **Database Connectivity:** PHP Data Objects (PDO)
- **Reporting:** JasperReports and JasperStarter
- **Server Environment:** Apache

## Project Structure

```text
HELMS/
│
├── assets/
│   ├── css/
│   └── js/
│
├── config/
│   ├── auth.php
│   ├── database.php
│   ├── id_generator.php
│   └── jasper.php
│
├── customer/
├── customer_service_manager/
├── driver/
├── transportMgr/
├── warehouse/
├── managing_director/
├── system_admin/
│
├── reports/
│   ├── customer_report.jrxml
│   ├── delivery_report.jrxml
│   ├── payment_report.jrxml
│   └── storage_report.jrxml
│
├── sql/
│   ├── HELMS.sql
│   └── HELMS_new_schema.sql
│
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── logout.php
├── track_lookup.php
│
└── SETUP_JASPER.md
```

## Database

The project uses a MySQL/MariaDB database named `HELMS`.

The database structure and related SQL statements are provided in the `sql/` directory.

Two schema versions are included:

- **HELMS.sql:** Original database schema used by the current application.
- **HELMS_new_schema.sql:** Redesigned alternative database schema.

**Note:** The current application is compatible with `HELMS.sql`. The redesigned schema has not yet been integrated with the existing PHP application.

Import only one schema into a given `HELMS` database.

## Reporting

The system uses JasperReports to generate and display reports in PDF format.

The project includes the following report templates:

- Customer Report
- Delivery Report
- Payment Report
- Storage Report

JasperReports must be configured in the local environment for the Managing Director's report-viewing functionality. Refer to `SETUP_JASPER.md` for the required configuration details.
