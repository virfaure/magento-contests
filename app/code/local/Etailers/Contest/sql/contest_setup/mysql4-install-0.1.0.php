<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `contest`;
CREATE TABLE `contest` (
  `contest_id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_title` varchar(255) DEFAULT NULL,
  `contest_description` text,
  `contest_date_start` date NOT NULL,
  `contest_date_end` date NOT NULL,
  `contest_text_legal` text,
  `contest_share_meta` text,
  `contest_image` varchar(255) DEFAULT NULL,
  `contest_url` varchar(255) DEFAULT NULL,
  `contest_url_cms` varchar(255) DEFAULT NULL,
  `contest_status` tinyint(1) NOT NULL,
  PRIMARY KEY (`contest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `contest_participant`;
CREATE TABLE `contest_participant` (
  `contest_participant_id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `contest_participant_name` varchar(255) NOT NULL,
  `contest_participant_email` varchar(255) NOT NULL,
  `contest_participant_status` enum('looser','winner','--') NOT NULL DEFAULT '--',
  `contest_participant_informed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contest_participant_id`),
  KEY `contest_id` (`contest_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `contest_participant_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_contest_participant_1` FOREIGN KEY (`contest_id`) REFERENCES `contest` (`contest_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `newsletter_contest_subcriber`;
CREATE TABLE `newsletter_contest_subcriber` (
  `newsletter_contest_subcriber_id` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(10) unsigned NOT NULL,
  `last_contest_id` int(11) NOT NULL,
  PRIMARY KEY (`newsletter_contest_subcriber_id`),
  KEY `subcriber_id` (`subscriber_id`),
  KEY `last_contest_id` (`last_contest_id`),
  CONSTRAINT `fk_newsletter_contest_subcriber_1` FOREIGN KEY (`last_contest_id`) REFERENCES `contest` (`contest_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_newsletter_contest_subcriber_2` FOREIGN KEY (`subscriber_id`) REFERENCES `newsletter_subscriber` (`subscriber_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `contest_store`;
CREATE TABLE `contest_store` (
  `contest_id` int(11) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`contest_id`,`store_id`),
  KEY `contest_id` (`contest_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `fk_contest_store_1` FOREIGN KEY (`contest_id`) REFERENCES `contest` (`contest_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_contest_store_2` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup(); 
