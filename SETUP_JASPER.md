# HELMS – JasperReports Setup Guide

This guide explains how to configure JasperReports for the Managing Director portal.

## 1. Prerequisites

Ensure the following are installed:

- PHP 7.4 or newer
- Apache and MySQL/MariaDB
- Java 8 or newer
- JasperStarter
- MySQL Connector/J
- Jaspersoft Studio (optional, for editing reports)

## 2. Installation

### Step 1 – Install Java

Install Java and verify the installation using Command Prompt:

```bash
java -version
```

### Step 2 – Download JasperStarter

Download JasperStarter and extract it to:

```text
C:\jasperstarter
```

Verify that this file exists:

```text
C:\jasperstarter\lib\jasperstarter.jar
```

### Step 3 – Install MySQL Connector/J

Download the [MySQL Connector/J driver](https://dev.mysql.com/downloads/connector/j/).

Select the **Platform Independent** package and copy the extracted `.jar` file into:

```text
C:\jasperstarter\jdbc\
```

### Step 4 – Configure JasperReports

Open `config/jasper.php` and update the paths to match your installation:

```php
const JASPER_JAVA = 'java';
const JASPER_LIB_DIR = 'C:/jasperstarter/lib';
const JASPER_MYSQL_JAR = 'C:/jasperstarter/jdbc/mysql-connector-j-8.x.x.jar';
const JASPER_DB_DRIVER = 'com.mysql.cj.jdbc.Driver';
const JASPER_DB_PORT = 3306;
const JASPER_DEBUG = true;
```

Replace the example JAR filename with the one you downloaded.

**Note:** Database credentials are read automatically from `config/database.php`. If Apache cannot locate Java, specify the full path to `java.exe` in `JASPER_JAVA`.

## 3. Test JasperStarter

Create an empty directory:

```text
C:\jasperstarter\empty
```

Open Command Prompt in the HELMS project directory and run the following command. Adjust the Connector/J filename and database credentials if necessary.

```bash
java -cp "C:\jasperstarter\lib\*;C:\jasperstarter\jdbc\mysql-connector-j-8.x.x.jar" de.cenote.jasperstarter.App process reports\payment_report.jrxml -f pdf -o test -t generic --jdbc-dir C:\jasperstarter\empty --db-driver com.mysql.cj.jdbc.Driver --db-url "jdbc:mysql://localhost:3306/HELMS?useSSL=false&serverTimezone=UTC&allowPublicKeyRetrieval=true" -u root -P REPORT_ID=RPT-PAY-2026-10
```

If your database user has a password, add `-p YOUR_DATABASE_PASSWORD` before `-P`.

Replace the sample report ID with an ID that exists in your database.

A successful run should generate `test.pdf` in the project directory.

## 4. Managing Director Login

If the sample Managing Director account uses a placeholder password hash, generate a new one:

```bash
php -r "echo password_hash('md12345', PASSWORD_DEFAULT);"
```

Update the account in phpMyAdmin using the generated hash:

```sql
UPDATE User
SET passwordHash = '<generated hash>'
WHERE username = 'md.fernando';
```

Log in through `login.php?portal=staff` using the `md.fernando` account.

## 5. Editing Reports

The project includes four report templates in `reports/`:

- `payment_report.jrxml`
- `delivery_report.jrxml`
- `storage_report.jrxml`
- `customer_report.jrxml`

Open a template in Jaspersoft Studio to edit its design or query.

**Important:** Preserve the `REPORT_ID` String parameter used by the application when modifying existing templates.

Changes are picked up when the report is opened again.

## 6. Troubleshooting

| Error | Solution |
|---|---|
| JasperStarter library not found | Check `JASPER_LIB_DIR`. |
| MySQL driver not found | Check `JASPER_MYSQL_JAR` and its filename. |
| `proc_open()` is disabled | Enable `proc_open` in `php.ini` and restart Apache. |
| Java not found | Check `JASPER_JAVA` or configure the system PATH. |
| No suitable driver | Verify the Connector/J JAR and `JASPER_DB_DRIVER`. |
| Access denied | Check database credentials in `config/database.php`. |
| Report not found or blank | Verify the `REPORT_ID` and report query. |
| Unknown attribute in report template | Check compatibility between the Jaspersoft Studio and JasperReports versions. |

For development, keep:

```php
const JASPER_DEBUG = true;
```

Before the final demonstration, change it to:

```php
const JASPER_DEBUG = false;
```

Technical errors will still be recorded in the PHP error log.
