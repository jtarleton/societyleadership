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


INSERT INTO `user` (`username`, `first`, `last`, `password`, `email`, `role`, `created`)
VALUES ('bobbyjones', 'Robert', 'Jones', 'bobbyjones', 'robertjones@gmail.com', 'user', '0000-00-00 00:00:00');

INSERT INTO `user` (`username`, `first`, `last`, `password`, `email`, `role`, `created`)
VALUES ('jonhenderson', 'Jonathan', 'Henderson', 'jonhenderson', 'jonhenderson@gmail.com', 'admin', '0000-00-00 00:00:00');