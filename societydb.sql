CREATE DATABASE `societyleadership` COLLATE 'utf8_general_ci';

CREATE USER 'societyleadershi'@'127.0.0.1' IDENTIFIED BY PASSWORD '*7F972020B4620BAE0A434544F22928B6381AA66E';
GRANT ALTER, CREATE, CREATE VIEW, INDEX, INSERT, REFERENCES, SELECT, SHOW VIEW, TRIGGER, UPDATE ON societyleadership.* TO 'societyleadershi'@'127.0.0.1';


CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(250) COLLATE 'utf8_general_ci' NOT NULL,
  `first` varchar(250) COLLATE 'utf8_general_ci' NOT NULL,
  `last` varchar(250) COLLATE 'utf8_general_ci' NOT NULL,
  `password` varchar(250) COLLATE 'utf8_general_ci' NOT NULL,
  `email` varchar(250) COLLATE 'utf8_general_ci' NOT NULL,
  `role` varchar(250) COLLATE 'utf8_general_ci' NOT NULL DEFAULT 'user',
  `created` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE='InnoDB' COLLATE 'utf8_general_ci';

ALTER TABLE `user`
ADD UNIQUE `username` (`username`);

INSERT INTO `user` (`id`, `username`, `first`, `last`, `password`, `email`, `role`, `created`) VALUES
(1,	'bobbyjones',	'Robert',	'Jones',	'bobbyjones',	'robertjones@gmail.com',	'user',	'2018-11-24 22:56:31'),
(2,	'jonhenderson',	'Jonathan',	'Henderson',	'jonhenderson',	'jonhenderson@gmail.com',	'user',	'2018-11-24 15:41:00'),
(3,	'georgejetson',	'George',	'Jetson',	'georgejetson',	'gjetson@domain.com',	'user',	'2018-11-24 22:56:18'),
(8,	'jsanders',	'John',	'Sanders',	'jsanders',	'jsanders@gmail.com',	'user',	'2018-11-23 04:46:22'),
(9,	'fredf',	'Fred',	'Flintsone',	'fred',	'fred@acme.com',	'user',	'2018-11-23 05:46:19'),
(22,	'jtarleton',	'james',	'tarleton',	'jtarleton',	'jamestarleton@gmail.com',	'admin',	'2018-11-24 15:40:49'),
(29,	'jasmine',	'jasmine',	'asdf',	'jasmine',	'jasmine@gmail.com',	'user',	'2018-11-24 15:23:25'),
(36,	'george',	'george',	'jones',	'george',	'jeorgejones@george.com',	'user',	'2018-11-24 22:00:32');


