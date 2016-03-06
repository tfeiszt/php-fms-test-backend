CREATE TABLE logs (
	id bigint unsigned not null auto_increment primary key,
	method varchar(10) not null,
	type varchar(10) not null,
	objname varchar(255) not null,
	created_at datetime not null
);
