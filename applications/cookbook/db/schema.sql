-- $Id: medick.php 379 2006-03-18 17:36:03Z aurelian $
-- Database Schema for cookbook project

DROP DATABASE IF EXISTS `cookbook`;
CREATE DATABASE `cookbook`;
USE `cookbook`;

CREATE TABLE `recipes` (
    `id` INT(6) NOT NULL auto_increment,
    `title` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `date` DATE NOT NULL,
    `instructions` TEXT NOT NULL,
    `category_id` INT (6) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE `categories` (
    `id` INT(6) NOT NULL auto_increment,
    `name` VARCHAR(255),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;


INSERT INTO `categories` VALUES (1, 'Snacks');
INSERT INTO `categories` VALUES (2, 'Beverages');

INSERT INTO `recipes` (`title`, `description`,`date`, `instructions`, `category_id`)
       VALUES ('Hot Chips', 'Only for brave!', now(), 'Sprinkle hot sauce on corn chips.', 1);
       
INSERT INTO `recipes` (`title`, `description`,`date`, `instructions`, `category_id`)
       VALUES ('Ice Water', 'Everyone\'s favorite', now(), 'Put ice cubes in a glass of water.', 2);
       
