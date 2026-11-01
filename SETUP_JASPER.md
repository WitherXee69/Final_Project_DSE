# Managing Director dashboard + JasperReports - setup

## How it works (one minute)

```
MD picks a report in the dropdown
        |
managing_director/report_view.php   (checks the user is ManagingDirector)
        |   starts Java with JasperStarter (a download that contains JasperReports)
reports/payment_report.jrxml  <-- you edit these in Jaspersoft Studio
        |   JasperStarter reads the HELMS database through JDBC
PDF is sent back and shown in the page
```

* **No Composer, no PHP libraries.** PHP just starts one program.
* The four templates are in `reports/` - edit them freely, no PHP change needed.
* PHP **7.4 or newer** is required (XAMPP 8.x is fine).

## One-time setup (about 10 minutes)

### 1. Copy the files
Unzip over your project folder (keep the folder structure). New: `managing_director/`, `reports/`,
`config/jasper.php`, `assets/css/md.css`. Changed: `dashboard.php` (4 added lines that send the
Managing Director to the new page).

### 2. Install Java
Open a terminal and run `java -version`. Any Java 8 or newer works (11, 17, 21 are all fine).

### 3. Download JasperStarter
Download the **JasperStarter** zip (it already includes JasperReports - search "JasperStarter" and
use the SourceForge / GitHub release page) and unzip it to `C:\jasperstarter`
so that `C:\jasperstarter\lib\jasperstarter.jar` exists.

### 4. Download the MySQL driver
Download **MySQL Connector/J** (choose "Platform Independent", unzip) and copy the
`mysql-connector-j-8.x.x.jar` file into `C:\jasperstarter\jdbc\` (any folder is fine).

### 5. Edit `config/jasper.php`
Set `JASPER_LIB_DIR` and `JASPER_MYSQL_JAR` (use the exact jar file name). If `java -version` works in a
terminal but reports fail, put the full path to `java.exe` in `JASPER_JAVA`.
The database login is read automatically from `config/database.php`.

### 6. Give the Managing Director a password
The sample data uses placeholder hashes, so nobody can log in as `md.fernando` yet. Make a real hash:

```
php -r "echo password_hash('md12345', PASSWORD_DEFAULT);"
```

then in phpMyAdmin run (paste your hash):

```sql
UPDATE User SET passwordHash = '<paste hash here>' WHERE username = 'md.fernando';
```

Log in at `login.php?portal=staff` as `md.fernando` - you land on the new dashboard.

## Test without PHP (do this first if something fails)

Run from the project folder (adjust the jar name):

```
java -cp "C:\jasperstarter\lib\*;C:\jasperstarter\jdbc\mysql-connector-j-8.4.0.jar" de.cenote.jasperstarter.App process reports\payment_report.jrxml -f pdf -o test -t generic --jdbc-dir C:\jasperstarter\empty --db-driver com.mysql.cj.jdbc.Driver --db-url "jdbc:mysql://localhost:3306/HELMS?useSSL=false&serverTimezone=UTC&allowPublicKeyRetrieval=true" -u root -P REPORT_ID=RPT-PAY-2026-10
```

(Create an empty folder `C:\jasperstarter\empty` first.) If `test.pdf` appears, Jasper works and the PHP side will too.

## Editing a report design

1. Install **Jaspersoft Studio** (free) and open `reports/payment_report.jrxml`.
2. The query is under the report's *Dataset and Query*; it has one parameter, `REPORT_ID`.
3. Save the file, then click **View report** again - the page picks up the change straight away.

Amounts show as `LKR`. To change it, edit the `pattern` of the text fields (search for `'LKR'`).

## Adding a fifth report later
1. Save a new `.jrxml` in `reports/` with a `REPORT_ID` String parameter.
2. Add one entry to `md_report_types()` in `managing_director/report_methods.php`.

## Troubleshooting

| Message on the page | Fix |
|---|---|
| `JasperStarter lib folder not found` / `MySQL driver jar not found` | Wrong `JASPER_LIB_DIR` or `JASPER_MYSQL_JAR` in `config/jasper.php` (check the exact jar file name). |
| `proc_open() is disabled` | Remove `proc_open` from `disable_functions` in `php.ini`, restart Apache. |
| `Could not start Java` / `'java' is not recognized` | Put the full path to `java.exe` in `JASPER_JAVA`, or restart Apache after installing Java. |
| `No suitable driver` / `ClassNotFoundException ... mysql` | `JASPER_MYSQL_JAR` points to the wrong file, or `JASPER_DB_DRIVER` does not match the jar version (8.x = `com.mysql.cj.jdbc.Driver`). |
| `Access denied for user` | Check `config/database.php` credentials. |
| Blank report / only titles | The `REPORT_ID` does not exist in that table. |
| Error mentions an unknown attribute after editing in Studio | Studio saved a newer format than JasperStarter understands: in Studio preferences set the *Compatibility* version to an older JasperReports release. |

Set `JASPER_DEBUG` to `false` before the final demo so users never see technical text.
