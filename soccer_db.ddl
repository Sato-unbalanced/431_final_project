-- you can reformat this to mysql
-- you can 'pipe' this DB into your database (mariadb) with this command:
-- sudo /opt/lampp/bin/mysql < /path/to/this/file/soccer_db.ddl 
-- IF you don't have the database set up on your machine/server
-- similarly, you can create this DB by accessing sudo /opt/lampp/bin/mysql and entering the sql commands manually 

DROP DATABASE IF EXISTS SportsTeam;
CREATE DATABASE SportsTeam;
USE SportsTeam;

CREATE TABLE Roles (
  ID_Role TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  roleName VARCHAR(30) NOT NULL UNIQUE COMMENT 'Must match DB users (if needed)'
);

INSERT INTO Roles (ID_Role, roleName) VALUES 
  (1, 'Guest'), 
  (2, 'Player'), 
  (3, 'Coach'), 
  (4, 'Manager');


CREATE TABLE UserLogin (
  ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(128) NOT NULL,
  Email VARCHAR(255) NOT NULL UNIQUE,
  UserName VARCHAR(100) NOT NULL UNIQUE,
  Password VARCHAR(255) NOT NULL,
  Role TINYINT UNSIGNED NOT NULL DEFAULT 1,
  ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  IsTempPassword BOOLEAN DEFAULT 0,
  
  FOREIGN KEY (Role) REFERENCES Roles(ID_Role) ON DELETE CASCADE
);

-- Create DB users and assign passwords? 
--CREATE USER 'Guest'@'localhost' IDENTIFIED BY 'Password0';
--CREATE USER 'Player'@'localhost' IDENTIFIED BY 'Password1';
--CREATE USER 'Coach'@'localhost' IDENTIFIED BY 'Password2';
--CREATE USER 'Manager'@'localhost' IDENTIFIED BY 'Password3';

-- Grant permissions per role?
--GRANT SELECT ON SportsTeam.* TO 'Guest'@'localhost';
--GRANT SELECT, INSERT, UPDATE ON SportsTeam.* TO 'Player'@'localhost';
--GRANT SELECT, INSERT, UPDATE, DELETE ON SportsTeam.* TO 'Coach'@'localhost';
--GRANT ALL PRIVILEGES ON SportsTeam.* TO 'Manager'@'localhost';

CREATE TABLE IF NOT EXISTS PasswordReset (
  ID INT AUTO_INCREMENT PRIMARY KEY,
  Email VARCHAR(255) NOT NULL,
  Token VARCHAR(255) NOT NULL,
  Expiration DATETIME NOT NULL,
  INDEX (Email)
);


