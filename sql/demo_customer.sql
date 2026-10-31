-- Sample customer so the pickup pages work before login/register are connected.
-- Run ONCE in phpMyAdmin (HELMS database). It creates userId 1 if the tables are still empty.
USE HELMS;
INSERT INTO User (userId, username, passwordHash, role) VALUES (1, 'demo.customer', 'not-a-real-hash', 'IndividualCustomer');
INSERT INTO Customer (userId, contactNumber, email, address) VALUES (1, '0771234567', 'demo@example.com', 'Colombo 03');
INSERT INTO IndividualCustomer (userId, name, nic) VALUES (1, 'Demo Customer', '200012345678');
