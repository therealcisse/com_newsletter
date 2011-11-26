/* MySQL tables */

/*

CREATE TABLE IF NOT EXISTS `#__newsletter_config` (
  `namekey` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY (`namekey`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

*/

/*drop table if exists `#__subscribers`;*/
create table IF NOT EXISTS `#__subscribers` (
  `id` INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `created_user_id`  int(11) unsigned NOT NULL DEFAULT '0',
  `registerDate` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

/*drop table if exists `#__subscriber_category_map`;*/
create table IF NOT EXISTS `#__subscriber_category_map` (
  `subscriber_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL
/*, `created_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_user_id`  int(11) unsigned NOT NULL DEFAULT '0'*/
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

/*drop table if exists `#__subscriber_language_map`;*/
create table IF NOT EXISTS `#__subscriber_language_map` (
  `subscriber_id` INT(11) NOT NULL,
  `language_id` INT(11) NOT NULL
/*, `created_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_user_id`  int(11) unsigned NOT NULL DEFAULT '0'*/
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/* FOREIGN KEYS */

/*

ALTER  table `#__subscribers` ADD CONSTRAINT `unique_email` UNIQUE(`email`);
ALTER  table `#__subscribers` ADD CONSTRAINT `fk_created_user_id` FOREIGN KEY(`created_user_id`) REFERENCES `#__users`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER  table `#__subscriber_category_map` ADD CONSTRAINT `fk_subscriber_id` FOREIGN KEY(`subscriber_id`) REFERENCES `#__subscribers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER  table `#__subscriber_category_map` ADD CONSTRAINT `fk_category_id` FOREIGN KEY(`category_id`) REFERENCES `#__categories`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER  table `#__subscriber_category_map` ADD CONSTRAINT `fk_created_user_id` FOREIGN KEY(`created_user_id`) REFERENCES `#__users`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER  table `#__subscriber_category_map` ADD CONSTRAINT `pk_subscriber_category_map` PRIMARY KEY (`subscriber_id`, `category_id`);

ALTER  table `#__subscriber_language_map` ADD CONSTRAINT `fk_subscriber_id` FOREIGN KEY(`subscriber_id`) REFERENCES `#__subscribers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER  table `#__subscriber_language_map` ADD CONSTRAINT `fk_language_id` FOREIGN KEY(`language_id`) REFERENCES `#__languages`(`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER  table `#__subscriber_language_map` ADD CONSTRAINT `fk_created_user_id` FOREIGN KEY(`created_user_id`) REFERENCES `#__users`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER  table `#__subscriber_language_map` ADD CONSTRAINT `pk_subscriber_language_map` PRIMARY KEY (`subscriber_id`; `language_id`);

*/

/* MySQL tables */

/* FOREIGN KEYS */

/*

ALTER  table `#__subscribers` drop CONSTRAINT `unique_email`;
ALTER  table `#__subscribers` drop CONSTRAINT `fk_created_juser_id`;

ALTER  table `#__subscriber_category_map` drop CONSTRAINT `fk_subscriber_id`;
ALTER  table `#__subscriber_category_map` drop CONSTRAINT `fk_category_id`;
ALTER  table `#__subscriber_category_map` drop CONSTRAINT `fk_created_user_id`;
ALTER  table `#__subscriber_category_map` drop CONSTRAINT `pk_subscriber_category_map`;

ALTER  table `#__subscriber_language_map` drop CONSTRAINT `fk_subscriber_id`;
ALTER  table `#__subscriber_language_map` drop CONSTRAINT `fk_language_id`;
ALTER  table `#__subscriber_language_map` drop CONSTRAINT `fk_created_user_id`;
ALTER  table `#__subscriber_language_map` drop CONSTRAINT `pk_subscriber_language_map`;

*/

drop table if exists `#__subscriber_category_map`;
drop table if exists `#__subscriber_language_map`;
drop table if exists `#__subscribers`;

SELECT subscriber.id AS id,
        concat(`subscriber.first_name`, ' ', `subscriber.last_name`) AS name,`email`,
        category.title AS category_title,
        category.alias AS category_alias,
        category.id AS category_id,
        language.lang_id AS language_id,
        language.title_title AS language_title_native,
        language.title AS language_title
  FROM `j_subscribers` AS subscriber,
          `j_subscriber_category_map` AS category_map,
          `j_categories` AS category,
          `j_subscriber_language_map` AS language_map,
          `j_languages` AS language
  WHERE subscriber.activation = '' AND subscriber.published = '1' AND
          subscriber.id = category_map.subscriber_id AND
          category_map.category_id = category.id AND category.id = '2' AND
          subscriber.id = language_map.subscriber_id AND
          language.lang_id = language_map.language_id AND
          language.lang_code IN ( '*', 'en-GB');