<?php
/**
 * HELMS - JasperReports settings.
 *
 * HELMS runs reports through JasperStarter (a download that contains JasperReports).
 * PHP starts Java, Java reads the .jrxml file from /reports, fills it from the
 * database and writes a PDF.
 *
 * >>> Only the constants in this file need to be changed for your computer. <<<
 * Use forward slashes (/) in Windows paths.
 */

// The "java" program. 'java' works if "java -version" works in a normal terminal
// AND Apache was started after Java was installed. If not, give the full path,
// e.g. 'C:/Program Files/Java/jdk-21/bin/java.exe'
const JASPER_JAVA = 'java';

// The "lib" folder inside the unzipped JasperStarter (it contains jasperstarter.jar).
const JASPER_LIB_DIR = 'C:/jasperstarter/lib';

// The MySQL Connector/J jar file itself (exact file name of the version you downloaded).
const JASPER_MYSQL_JAR = 'C:/jasperstarter/jdbc/mysql-connector-j-26.10.0.jar';

// JDBC driver class. For Connector/J 8.x use the first line.
// For an old 5.x jar use 'com.mysql.jdbc.Driver'.
const JASPER_DB_DRIVER = 'com.mysql.cj.jdbc.Driver';

const JASPER_DB_PORT = 3306;

// true  = show the technical error text when a report fails (good while developing).
// false = show only a friendly message (use for the final demo).
const JASPER_DEBUG = true;

// Where the .jrxml templates live.
const JASPER_REPORT_DIR = __DIR__ . '/../reports';
