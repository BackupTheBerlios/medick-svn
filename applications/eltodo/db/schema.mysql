-- ------------------------
-- eltodo database schema.
-- $Id$
-- ------------------------

SET AUTOCOMMIT=0;
START TRANSACTION;
DROP DATABASE IF EXISTS eltodo;
CREATE DATABASE eltodo DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE eltodo;

-- ------------------------------------------------
-- projects table

CREATE TABLE projects (
    id          INT(11)       NOT NULL auto_increment,
    name        VARCHAR(255)  NOT NULL,
    description TEXT          NOT NULL,
    created_at  DATETIME      NOT NULL,
    PRIMARY KEY (id)
) Engine=InnoDB;


COMMIT;

