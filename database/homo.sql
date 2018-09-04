CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `screen_name` varchar(255) NOT NULL,
    `service` varchar(20) NOT NULL,
    `url` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `screen_name` (`screen_name`)
);
