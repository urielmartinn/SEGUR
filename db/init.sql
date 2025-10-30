-- 
CREATE DATABASE IF NOT EXISTS appdb;
USE appdb;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(255) NOT NULL,
  dni VARCHAR(12) NOT NULL UNIQUE,
  phone VARCHAR(9),
  birthdate DATE,
  email VARCHAR(255),
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  year INT,
  artist VARCHAR(255),
  genre VARCHAR(100),
  description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adb
INSERT INTO users (fullname, dni, phone, birthdate, email, username, password)
VALUES ('Juan Perez', '11111111-T', '600123456', '1990-05-10', 'juan@example.com', 'juan', SHA2('password',256));

INSERT INTO items (title, year, artist, genre, description) VALUES
('Disco A', 1977, 'Artista X', 'Rock', 'Disco clásico.'),
('Disco B', 1982, 'Artista Y', 'Pop', 'Edición limitada.');
