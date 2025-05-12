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
  ID INT UNSIGNED PRIMARY KEY,
  Name VARCHAR(128) NOT NULL,
  Email VARCHAR(255) NOT NULL UNIQUE,
  UserName VARCHAR(100) NOT NULL UNIQUE,
  Password VARCHAR(255) NOT NULL,
  Role TINYINT UNSIGNED NOT NULL DEFAULT 1,
  ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  IsTempPassword BOOLEAN DEFAULT 0,
  
  FOREIGN KEY (Role) REFERENCES Roles(ID_Role) ON DELETE CASCADE
);
 
CREATE TABLE Team(
  ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(200) NOT NULL
);

INSERT INTO Team(ID,Name) VALUES
(111, "Manchester United"),
(121,"Real Madrid"),
(311, "FC Barcelona"),
(431,"Bayern Munich"),
(332,"Paris Saint-Germain"),
(122, "Juventus"),
(235,"Los Angeles FC"),
(554,"Flamengo"),
(555,"Al Hilal"),
(333,"Ajax");

CREATE TABLE Game (
  ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  Location VARCHAR(100) NOT NULL,
  Month TINYINT UNSIGNED,
  Day TINYINT UNSIGNED,
  Year YEAR,
  HomeTeam INT UNSIGNED NOT NULL,
  AwayTeam INT UNSIGNED NOT NULL,
  HomeScore TINYINT UNSIGNED ,
  AwayScore TINYINT UNSIGNED ,

  FOREIGN KEY (HomeTeam) REFERENCES Team(ID),
  FOREIGN KEY (AwayTeam) REFERENCES Team(ID)
);

-- HomeScore =0  && AwayScore = 0 == TBA, game not played 
-- However, in soccer 0 and 0 can be a tie game... might need to add some flag or conditional check 
INSERT INTO Game(HomeTeam, AwayTeam, Location, Month, Day, Year, HomeScore, AwayScore) VALUES
(111, 121, "Old Trafford, Manchester, England"                , 6, 15, 2025, 2, 1),
(235, 121, "Banc of California Stadium, Los Angeles, CA, USA" , 7, 5, 2025, 3, 2),
(121, 111, "Santiago Bernabéu Stadium, Madrid, Spain"         , 8, 10, 2025, 4, 3),
(235, 111, "Banc of California Stadium, Los Angeles, CA, USA" , 9, 1, 2025, 2, 2),
(111, 235, "Old Trafford, Manchester, England"                , 10, 7, 2025, 1, 0),
(121, 235, "Santiago Bernabéu Stadium, Madrid, Spain"         , 11, 3, 2025, 3, 1),
(111, 121, "Wembley Stadium, London, England"                 , 11, 28, 2025, 0, 0),
(235, 121, "Rose Bowl Stadium, Pasadena, CA, USA"             , 12, 12, 2025, 0, 0),
(121, 111, "Eestadio Metropolitano, Madrid, Spain"             , 1, 10, 2026, 0, 0),
(235, 111, "Levi's Stadium, Santa Clara, CA, USA"             , 2, 18, 2026, 0, 0);

CREATE TABLE Coach (
  ID INT UNSIGNED PRIMARY KEY, 
  TeamID INT UNSIGNED,
  FirstName VARCHAR(100),
  LastName VARCHAR(100),
  FOREIGN KEY (TeamID) REFERENCES Team(ID)
);


INSERT INTO Coach (ID, TeamID, FirstName, LastName) VALUES
(500, 235, "Frederick", "Moore"),
(501, 235, "Benjamin", "Parker"),
(502, 111, "Elijah", "Carter"),
(503, 121, "Dominic", "Reed"),
(504, 121, "Christopher", "Hill"),
(505, 121, "Alexander", "Mason");

CREATE TABLE Player (
  ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  TeamID INT UNSIGNED DEFAULT NULL,
  FirstName VARCHAR (25),
  LastName VARCHAR (25),
  Street VARCHAR (250),
  City VARCHAR (100),
  State CHAR(10),
  Country VARCHAR(100) NOT NULL,
  Zipcode VARCHAR(10),

-- allows null TeamIDs AND if a players team is deleted, their TeamIDs will be set to null
  FOREIGN KEY (TeamID) REFERENCES Team(ID)
    ON DELETE SET NULL
);


INSERT INTO Player(ID, TeamID,FirstName, LastName, Street, City, State, Country, Zipcode) VALUES
(143, 235,"James", "Anderson", "123 Oak Lane", "Santa Ana", "CA", "USA", 92701),  
(257, 235,"Robert", "Carter", "456 Maple Street", "Irvine", "CA", "USA", 92602),
(318, 235,"William", "Harris", "789 Pine Avenue", "Costa Mesa", "CA", "USA", 92627),
(426, 235,"David", "Thompson", "321 Birch Boulevard", "Tustin", "CA", "USA", 92780),
(593, 235,"John", "Lewis", "654 Walnut Drive", "Huntington Beach", "CA", "USA", 92646),
(734, 235,"Charles", "Walker", "987 Elm Road", "Fountain Valley", "CA", "USA", 92708),
(828, 235,"Michael", "Scott", "222 Cedar Lane", "Garden Grove", "CA", "USA", 92840),
(679, 235,"Thomas", "Phillips", "111 Oakwood Street", "Laguna Beach", "CA", "USA", 92651),
(410, 235,"Daniel", "Parker", "333 Sycamore Avenue", "Lake Forest", "CA", "USA", 92630),
(241, 235,"Richard", "Adams", "444 Cherry Street", "Mission Viejo", "CA", "USA", 92691),
(122, 235,"Joseph", "Reed", "555 Redwood Court", "Newport Beach", "CA", "USA", 92660), 

(985, 111,"James", "Anderson", "123 Oak Lane", "London", "England", "UK", 00000),
(703, 111,"Robert", "Carter", "456 Maple Street", "Manchester", "England", "UK", 00000), 
(659, 111,"William", "Harris", "789 Pine Avenue", "Birmingham", "England", "UK", 00000), 
(298, 111,"David", "Thompson", "321 Birch Boulevard", "Liverpool", "England", "UK", 00000), 
(361, 111,"John", "Lewis", "654 Walnut Drive", "Leeds", "England", "UK", 00000),
(475, 111,"Charles", "Walker", "987 Elm Road", "Sheffield", "England", "UK", 00000),
(587, 111,"Michael", "Scott", "222 Cedar Lane", "Bristol", "England", "UK", 00000), 
(810, 111,"Thomas", "Phillips", "111 Oakwood Street", "Newcastle", "England", "UK", 00000), 
(927, 111,"Daniel", "Parker", "333 Sycamore Avenue", "York", "England", "UK", 00000),
(134, 111,"Richard", "Adams", "444 Cherry Street", "Nottingham", "England", "UK", 00000), 
(555, 111,"Joseph", "Reed", "555 Redwood Court", "Brighton", "England", "UK", 00000),

(673, 121,"Victor", "Murray", "123 Calle de Atocha", "Madrid", "Spain", "ES", 00000), 
(729, 121,"Alan", "Porter", "456 Calle Mayor", "Barcelona", "Spain", "ES", 00000),
(384, 121,"Thomas", "Hughes", "789 Avenida del Puerto", "Valencia", "Spain", "ES", 00000), 
(612, 121,"Nathan", "Ward", "321 Gran Vía", "Bilbao", "Spain", "ES", 00000),
(249, 121,"Christopher", "Ross", "654 Calle de Sevilla", "Seville", "Spain", "ES", 00000), 
(888, 121,"Peter", "Wood", "987 Calle de Málaga", "Málaga", "Spain", "ES", 00000),
(440, 121,"Daniel", "Gray", "222 Paseo de Granada", "Granada", "Spain", "ES", 00000), 
(602, 121,"Ethan", "Reed", "111 Avenida de Toledo", "Toledo", "Spain", "ES", 00000), 
(319, 121,"Ryan", "Patton", "333 Plaza Mayor", "Zaragoza", "Spain", "ES", 00000),
(711, 121,"Julian", "Gibson", "444 Calle de Cádiz", "Cádiz", "Spain", "ES", 00000), 
(870, 121,"Charlie", "Fletcher", "555 Calle de Burgos", "Burgos", "Spain", "ES", 00000); 


CREATE TABLE Statistics(
  ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  PlayerID INT UNSIGNED NOT NULL,
  Goals TINYINT UNSIGNED,
  Assists TINYINT UNSIGNED,
  Passes TINYINT UNSIGNED,

  FOREIGN KEY (PlayerID) REFERENCES Player(ID) ON DELETE CASCADE
);

INSERT INTO Statistics(PlayerID, Goals, Assists, Passes) VALUES
(143, 15, 7, 120),  
(257, 20, 12, 135),  
(318, 18, 10, 110),  
(426, 25, 14, 140),  
(593, 22, 9, 130),  
(734, 30, 11, 150),  
(828, 27, 13, 145),  
(679, 24, 8, 125),  
(410, 17, 6, 100),  
(241, 12, 5, 90),  
(122, 10, 4, 80),  

(985, 35, 18, 200),  
(703, 28, 12, 175),  
(659, 25, 11, 160),  
(298, 18, 8, 110),  
(361, 20, 10, 120),  
(475, 22, 14, 135),  
(587, 29, 16, 180),  
(810, 33, 15, 190),  
(927, 40, 17, 210),  
(134, 12, 5, 85),  
(555, 15, 8, 100),  

(673, 27, 13, 145),  
(729, 29, 14, 155),  
(384, 18, 9, 115),  
(612, 25, 12, 140),  
(249, 20, 10, 125),  
(888, 32, 15, 185),  
(440, 16, 6, 100),  
(602, 23, 11, 130),  
(319, 19, 8, 115),  
(711, 28, 14, 155),  
(870, 35, 18, 200); 

CREATE OR REPLACE USER Guest@localhost IDENTIFIED by '' PASSWORD EXPIRE NEVER;
GRANT SELECT ON Roles TO Guest@localhost;
GRANT SELECT ON UserLogin TO Guest@localhost;
GRANT SELECT ON Game TO Guest@localhost;
GRANT SELECT ON Statistics To Guest@localhost;
GRANT SELECT ON Team To Guest@localhost;


CREATE OR REPLACE USER Manager@localhost IDENTIFIED BY '!manger_0_0_1' PASSWORD EXPIRE NEVER;
GRANT SELECT, INSERT, DELETE, UPDATE, EXECUTE ON *  to Manager@localhost;

CREATE OR REPLACE USER Coach@localhost IDENTIFIED BY '!_coach_2_0_01' PASSWORD EXPIRE NEVER;
GRANT SELECT, INSERT, UPDATE ON Team to Coach@localhost;
GRANT SELECT, UPDATE ON  Player to Coach@localhost;
GRANT SELECT ON Game to Coach@localhost;
GRANT UPDATE (Day, Month, Year,Location) ON Game to Coach@localhost;
GRANT SELECT, UPDATE ON Coach to Coach@localhost;


CREATE OR REPLACE USER Player@localhost IDENTIFIED By '!_player_20_022_1' PASSWORD EXPIRE NEVER;
GRANT SELECT, UPDATE ON Player to Player@localhost;
GRANT SELECT ON Game to Player@localhost;
GRANT SELECT ON Team to Player@localhost;
GRANT SELECT ON Statistics to Player@localhost;

--GRANT SELECT, INSERT ON UserLogin to phpWebEngine@localhost IDENTIFIED by '!_phpWebEngine';
--GRANT SELECT, INSERT ON Roles to phpWebEngine@localhost IDENTIFIED by '!_phpWebEngine';


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
