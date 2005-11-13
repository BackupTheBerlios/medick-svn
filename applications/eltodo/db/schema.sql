-- ------------------------
-- eltodo database schema.
-- $Id$
-- ------------------------

DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
	id 	int(11) 	not null auto_increment,
	name 	varchar(255) 	not null,
	primary key (id)
);

DROP TABLE IF EXISTS todos;
CREATE TABLE IF NOT EXISTS todos (
	id 		int(11) 	not null auto_increment,
	project_id	int(11)		not null,
	description 	varchar(100) 	not null default '',
	done 		tinyint(4) 	not null default '0',
	constraint fk_todos_project foreign key (project_id) references projects(id),
	primary key (id)
);

