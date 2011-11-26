create table IF NOT EXISTS `#__subscribers` (
  `id` INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `created_user_id`  int(11) unsigned NOT NULL DEFAULT '0',
  `registerDate` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  /*`modified_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',*/
  `activation` varchar(100) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

create table IF NOT EXISTS `#__subscriber_category_map` (
  `subscriber_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

create table IF NOT EXISTS `#__subscriber_language_map` (
  `subscriber_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;