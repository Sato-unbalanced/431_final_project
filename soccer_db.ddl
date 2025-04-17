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
  Role TINYINT UNSIGNED NOT NULL DEFAULT 1
  ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
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



