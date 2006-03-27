-- $Id$
-- Database Schema for testor project

DROP DATABASE IF EXISTS `testor`;
CREATE DATABASE `testor`;
USE `testor`;

CREATE TABLE tones (
  `id`      INT (11) PRIMARY KEY auto_increment,
  `name`    VARCHAR (255),
  `status`  INT(1)
) ENGINE=InnoDB;

CREATE TABLE strones (
  `id`      INT (11) PRIMARY KEY auto_increment,
  `inc`     INT (11)
) ENGINE=InnoDB;

INSERT INTO `strones` (`id`,`inc`) VALUES (1,0);

CREATE TABLE c_session (
	`session_id` VARCHAR (255) PRIMARY KEY,
	`session_data` TEXT,
	`session_lastmodified` DATETIME
) ENGINE=InnoDB;
