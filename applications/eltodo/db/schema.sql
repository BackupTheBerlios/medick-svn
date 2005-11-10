DROP TABLE IF EXISTS `todos`;
CREATE TABLE IF NOT EXISTS `todos` (
  `id` int(11) NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `done` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);
