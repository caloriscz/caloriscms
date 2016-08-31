SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `board` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `author` varchar(80) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `subject` text NOT NULL,
  `body` text,
  `date_created` datetime DEFAULT NULL,
  `show` smallint(6) NOT NULL DEFAULT '0',
  `ipaddress` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `carousel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) DEFAULT NULL,
  `description` text,
  `uri` varchar(250) NOT NULL,
  `image` varchar(120) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `sorted` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `description` text,
  `title` varchar(80) NOT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_id` int(11) DEFAULT NULL,
  `pages_id` int(11) NOT NULL,
  `users_id` int(11) DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `post` varchar(80) DEFAULT NULL,
  `notes` text,
  `name` varchar(120) NOT NULL,
  `company` varchar(120) DEFAULT NULL,
  `street` varchar(120) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `countries_id` int(11) DEFAULT '1',
  `email` varchar(80) DEFAULT NULL,
  `phone` varchar(150) DEFAULT NULL,
  `vatin` varchar(10) DEFAULT NULL,
  `vatid` varchar(10) DEFAULT NULL,
  `banking_account` varchar(80) DEFAULT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `categories_id` (`categories_id`),
  KEY `countries_id` (`countries_id`),
  KEY `pages_id` (`pages_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `contacts_communication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contacts_id` int(11) NOT NULL,
  `communication_type` varchar(80) NOT NULL,
  `communication_value` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_id` (`contacts_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `contacts_docs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_id` int(11) DEFAULT NULL,
  `contacts_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_id` (`contacts_id`),
  KEY `categories_id` (`categories_id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `contacts_openinghours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` smallint(6) NOT NULL,
  `hourstext` varchar(80),
  `contacts_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_id` (`contacts_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_cs` varchar(120),
  `title_en` varchar(120),
  `show` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(60) DEFAULT NULL,
  `code` varchar(8) DEFAULT NULL,
  `symbol` varchar(20) DEFAULT NULL,
  `used` tinyint(1) DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_event` datetime DEFAULT NULL,
  `date_event_end` datetime DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '1',
  `show` tinyint(1) NOT NULL DEFAULT '0',
  `pages_id` int(11) DEFAULT NULL,
  `contacts_id` int(11) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT '0',
  `capacity_start` int(11) NOT NULL DEFAULT '0',
  `capacity_filled` int(11) NOT NULL DEFAULT '0',
  `price` int(11) DEFAULT NULL,
  `time_range` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pages_id` (`pages_id`),
  KEY `contacts_id` (`contacts_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `events_signed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `note` text,
  `events_id` int(11) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `events_id` (`events_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `helpdesk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `fill_phone` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `helpdesk_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template` varchar(40) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `body` text NOT NULL,
  `helpdesk_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `helpdesk_id` (`helpdesk_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `helpdesk_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(80) DEFAULT NULL,
  `message` text,
  `helpdesk_id` int(11),
  `contacts_id` int(11),
  `email` varchar(120) DEFAULT NULL,
  `phone` varchar(40) NOT NULL,
  `session_id` varchar(40) DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL,
  `ipaddress` varchar(80) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_id` (`contacts_id`),
  KEY `helpdesk_id` (`helpdesk_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(8) NOT NULL,
  `title` varchar(40) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '1',
  `default` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_id` int(11) DEFAULT NULL,
  `url` varchar(250) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `links_category_id` (`categories_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `file_type` tinyint(1) NOT NULL DEFAULT '0',
  `filesize` int(11) NOT NULL DEFAULT '0',
  `pages_id` int(11) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `description` text  NOT NULL,
  `date_created` datetime NOT NULL,
  `detail_view` tinyint(1) NOT NULL DEFAULT '1',
  `sorted` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `albums_id` (`pages_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `description` text,
  `title` varchar(80) NOT NULL,
  `pages_id` int(11) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pages_id` (`pages_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(250) DEFAULT NULL,
  `slug_en` varchar(250) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `document` text,
  `preview` text,
  `pages_id` int(11) DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL,
  `public` int(11) DEFAULT '0',
  `metadesc` varchar(200) DEFAULT NULL,
  `metakeys` varchar(150) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_published` datetime DEFAULT NULL,
  `pages_types_id` int(11) DEFAULT '1',
  `pages_templates_id` int(11) DEFAULT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0',
  `editable` int(11) NOT NULL DEFAULT '1',
  `presenter` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `pages_id` (`pages_id`),
  KEY `content_type` (`pages_types_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pages_related` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pages_id` int(11) NOT NULL,
  `related_pages_id` int(11) DEFAULT NULL,
  `description` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`pages_id`,`related_pages_id`),
  KEY `related_store_id` (`related_pages_id`),
  KEY `blog_id` (`pages_id`),
  KEY `related_blog_id` (`related_pages_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pages_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pages_types_id` int(11) DEFAULT NULL,
  `template` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pages_types_id` (`pages_types_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pages_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` varchar(40) NOT NULL,
  `presenter` varchar(40) NOT NULL,
  `action` varchar(40) NOT NULL,
  `prefix` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `param` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `param` varchar(80) NOT NULL,
  `param_en` varchar(80) NOT NULL,
  `prefix` varchar(40) DEFAULT NULL COMMENT 'Will be automatically filled before value',
  `suffix` varchar(40) DEFAULT NULL COMMENT 'Will be automatically filled after value',
  `preset` varchar(80) DEFAULT NULL COMMENT 'Value in preset will be autofilled',
  `ignore_front` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide in parametres in presentations',
  `ignore_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide in admin params view',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `params` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pages_id` int(11) NOT NULL,
  `param_id` int(11) NOT NULL,
  `paramvalue` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`pages_id`),
  KEY `store_param_id` (`param_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pricelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_id` int(11) DEFAULT NULL,
  `title` varchar(400) NOT NULL,
  `description` text,
  `price` double NOT NULL,
  `price_info` varchar(80) DEFAULT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pricelist_categories_id` (`categories_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pricelist_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_id` int(11) DEFAULT NULL,
  `title` text NOT NULL,
  `pricelist_dates_id` int(11) NOT NULL,
  `price` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pricelist_categories_id` (`categories_id`),
  KEY `pricelist_dates_id` (`pricelist_dates_id`),
  KEY `pricelist_dates_id_2` (`pricelist_dates_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pricelist_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_id` int(11) DEFAULT NULL,
  `setkey` varchar(40) NOT NULL,
  `setvalue` varchar(120) NOT NULL,
  `description_cs` varchar(150) DEFAULT NULL,
  `description_en` varchar(150) DEFAULT NULL,
  `type` varchar(40) DEFAULT NULL,
  `admin_editable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `categories_id` (`categories_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `snippets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(80) NOT NULL,
  `content` text,
  `pages_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pages_id` (`pages_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(40) NOT NULL,
  `categories_id` int(11) DEFAULT NULL,
  `uid` varchar(10) DEFAULT NULL,
  `email` char(80) NOT NULL,
  `sex` int(11) NOT NULL DEFAULT '0',
  `name` varchar(80) DEFAULT NULL,
  `password` char(60) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_visited` datetime DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT '0',
  `activation` char(40) DEFAULT NULL,
  `newsletter` int(11) DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '1',
  `users_roles_id` int(11) DEFAULT '0',
  `login_error` int(11) NOT NULL DEFAULT '0',
  `login_success` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `categories_id` (`categories_id`),
  KEY `users_roles_id` (`users_roles_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `users_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `admin_access` tinyint(1) NOT NULL DEFAULT '0',
  `appearance_images` tinyint(1) NOT NULL DEFAULT '0',
  `helpdesk_edit` tinyint(1) NOT NULL DEFAULT '0',
  `settings_display` tinyint(1) NOT NULL DEFAULT '0',
  `settings_edit` tinyint(1) NOT NULL DEFAULT '0',
  `members_display` tinyint(1) NOT NULL DEFAULT '0',
  `members_edit` tinyint(1) NOT NULL DEFAULT '0',
  `members_create` tinyint(1) NOT NULL DEFAULT '0',
  `members_delete` tinyint(1) NOT NULL DEFAULT '0',
  `pages_edit` tinyint(1) NOT NULL DEFAULT '0',
  `pages_document` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_6` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_4` FOREIGN KEY (`countries_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `contacts_ibfk_5` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `contacts_docs`
  ADD CONSTRAINT `contacts_docs_ibfk_1` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_docs_ibfk_3` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
  
ALTER TABLE `events_signed`
  ADD CONSTRAINT `events_signed_ibfk_1` FOREIGN KEY (`events_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `helpdesk_emails`
  ADD CONSTRAINT `helpdesk_emails_ibfk_1` FOREIGN KEY (`helpdesk_id`) REFERENCES `helpdesk` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `helpdesk_messages`
  ADD CONSTRAINT `helpdesk_messages_ibfk_4` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `helpdesk_messages_ibfk_1` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `helpdesk_messages_ibfk_3` FOREIGN KEY (`helpdesk_id`) REFERENCES `helpdesk` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `links`
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_4` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_4` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_ibfk_5` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_ibfk_6` FOREIGN KEY (`pages_types_id`) REFERENCES `pages_types` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_ibfk_7` FOREIGN KEY (`pages_templates_id`) REFERENCES `pages_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pages_related`
  ADD CONSTRAINT `pages_related_ibfk_4` FOREIGN KEY (`related_pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_related_ibfk_3` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `pages_templates`
  ADD CONSTRAINT `pages_templates_ibfk_1` FOREIGN KEY (`pages_types_id`) REFERENCES `pages_types` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `params`
  ADD CONSTRAINT `params_ibfk_3` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `params_ibfk_4` FOREIGN KEY (`param_id`) REFERENCES `param` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pricelist`
  ADD CONSTRAINT `pricelist_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pricelist_daily`
  ADD CONSTRAINT `pricelist_daily_ibfk_5` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pricelist_daily_ibfk_2` FOREIGN KEY (`pricelist_dates_id`) REFERENCES `pricelist_dates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `snippets`
  ADD CONSTRAINT `snippets_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;