-- Crear la base de datos y tablas básicas
CREATE DATABASE IF NOT EXISTS websystem;
USE websystem;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(200) NOT NULL,
  nan VARCHAR(10) NOT NULL,
  phone VARCHAR(9),
  birthdate DATE,
  email VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  field1 VARCHAR(255),
  field2 VARCHAR(255),
  field3 VARCHAR(255),
  field4 VARCHAR(255),
  field5 TEXT
);

/* INSERTs de ejemplo */
INSERT INTO users (username, password, full_name, nan, phone, birthdate, email)
VALUES ('demo', 'demo_hash', 'Demo Usuario', '11111111-Z', '600000000', '1990-01-01', 'demo@example.com');
