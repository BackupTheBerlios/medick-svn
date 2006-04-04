-- $Id$
-- Database Schema for testor project

SET AUTOCOMMIT=0;
START TRANSACTION;

DROP DATABASE IF EXISTS `testor`;
CREATE DATABASE `testor`;
USE `testor`;

-- --------------------------------------------------------
-- Table structure for table `tones`

CREATE TABLE tones (
  `id`      INT (11) PRIMARY KEY auto_increment,
  `name`    VARCHAR (255),
  `status`  INT(1)
) ENGINE=InnoDB;


-- --------------------------------------------------------
-- Table structure for table `strones`

CREATE TABLE strones (
  `id`      INT (11) PRIMARY KEY auto_increment,
  `inc`     INT (11)
) ENGINE=InnoDB;

INSERT INTO `strones` (`id`,`inc`) VALUES (1,0);

-- --------------------------------------------------------
-- Table structure for table `c_sessions`

CREATE TABLE c_sessions (
	`session_id` VARCHAR (255) PRIMARY KEY,
	`session_data` TEXT,
	`session_lastmodified` DATETIME
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- ActiveRecord::Association related tables
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Table structure for table `categories`

CREATE TABLE categories (
  id        int(11)         NOT NULL auto_increment,
  `name`    varchar(150)    NOT NULL,
  PRIMARY KEY  (id)
) Engine=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `categories_projects`

CREATE TABLE categories_projects (
  category_id   int(11) NOT NULL,
  project_id    int(11) NOT NULL
) Engine=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `milestones`

CREATE TABLE milestones (
  id            int(11)      NOT NULL auto_increment,
  project_id    int(11)      NOT NULL,
  title         varchar(150) NOT NULL,
  description   text,
  PRIMARY KEY  (id)
) Engine=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `portfolios`

CREATE TABLE portfolios (
  id        int(11)     NOT NULL auto_increment,
  `name`    varchar(40) default NULL,
  PRIMARY KEY  (id)
) Engine=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `managers`

CREATE TABLE managers (
  id            int(11)     NOT NULL auto_increment,
  first_name    varchar(40) default NULL,
  last_name     varchar(40) default NULL,
  PRIMARY KEY  (id)
) Engine=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `projects`

CREATE TABLE projects (
  id            int(11)     NOT NULL auto_increment,
  portfolio_id  int(11)     NOT NULL,
  manager_id    int(11)     NOT NULL,
  `name`        varchar(40) default NULL,
  created_at    datetime    default NULL,
  PRIMARY KEY  (id)
) Engine=InnoDB;

-- --------------------------------------------------------
-- Foreign Keys

ALTER TABLE projects ADD
    constraint fk_project_portofolio foreign key (portfolio_id) references portfolios(id);

ALTER TABLE projects ADD
    constraint fk_project_manager foreign key (manager_id) references managers(id);

ALTER TABLE categories_projects ADD
    constraint fk_categories foreign key (category_id) references categories(id);

ALTER TABLE categories_projects ADD
    constraint fk_projects foreign key (project_id) references projects(id);

ALTER TABLE milestones ADD
    constraint fk_milestones_project foreign key (project_id) references projects(id);

-- --------------------------------------------------------
-- Inserts

INSERT INTO `categories` ( `id` , `name` )
    VALUES (NULL , 'medick');
INSERT INTO `categories` ( `id` , `name` )
    VALUES (NULL , 'PHP');
INSERT INTO `portfolios` ( `id` , `name` )
    VALUES (NULL , 'locknet.ro');
INSERT INTO `managers` ( `id` , `first_name` , `last_name` )
    VALUES ( NULL , 'oancea', 'aurelian');
INSERT INTO `projects` ( `id` , `portfolio_id` , `manager_id`,`name` , `created_at` )
    VALUES ( NULL , '1', '1', 'elproject', NOW());
INSERT INTO `milestones` ( `id` , `project_id` , `title` , `description` )
    VALUES (NULL , '1', 'First Release', 'A description of this milestone.');
INSERT INTO `categories_projects` ( `category_id` , `project_id` )
    VALUES ('1', '1'), ('2', '1');

COMMIT;
