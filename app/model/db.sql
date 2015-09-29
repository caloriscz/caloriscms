SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `article` text CHARACTER SET utf8 COLLATE utf8_czech_ci,
  `date_created` datetime NOT NULL,
  `public` smallint(6) NOT NULL DEFAULT '0',
  `blog_categories_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `blog_category_id` (`blog_categories_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `blog_categories` (`id`, `title`) VALUES
(1, 'Main');

CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL,
  `uid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_czech_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8_czech_ci DEFAULT NULL,
  `store_settings_shipping_id` int(11) DEFAULT '0',
  `store_settings_payments_id` int(11) NOT NULL DEFAULT '0',
  `contacts_id` int(11) NOT NULL,
  `note` text COLLATE utf8_czech_ci,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `store_settings_shipping_id` (`store_settings_shipping_id`),
  KEY `store_settings_payments_id` (`store_settings_payments_id`),
  KEY `contacts_id` (`contacts_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cart_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '1',
  `contacts_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `contacts_id` (`contacts_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `store_stock_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `price` double NOT NULL,
  `vat` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `store_id` (`store_id`),
  KEY `store_stock_id` (`store_stock_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_parent` int(11) NOT NULL,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contacts_groups_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL DEFAULT '0',
  `post` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `notes` text COLLATE utf8_czech_ci,
  `name` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `street` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `city` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
  `country` varchar(85) COLLATE utf8_czech_ci DEFAULT NULL,
  `email` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `vatin` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
  `vatid` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
  `url_web` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `cv` text COLLATE utf8_czech_ci,
  `cv_en` text COLLATE utf8_czech_ci,
  `show_web` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contacts_groups_id` (`contacts_groups_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `contacts_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` char(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `contacts_groups` (`id`, `group`) VALUES
(1, 'Main');


CREATE TABLE IF NOT EXISTS `contacts_openinghours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` smallint(6) NOT NULL,
  `hourstext` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `contacts_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_id` (`contacts_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `gallery_categories_id` int(11) NOT NULL DEFAULT '1',
  `gallery_albums_id` int(11) NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `description2` text COLLATE utf8_czech_ci,
  `date_created` datetime NOT NULL,
  `sorted` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_categories_id` (`gallery_categories_id`),
  KEY `galleries_albums_id` (`gallery_albums_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `gallery_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(120) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  `gallery_categories_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `galleries_categories_id` (`gallery_categories_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `gallery_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(120) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `helpdesk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `message` text COLLATE utf8_czech_ci,
  `doctor` int(11) NOT NULL DEFAULT '0',
  `email` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `ipaddress` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  `users_id` int(11) NOT NULL,
  `email` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8_czech_ci DEFAULT NULL,
  `store_settings_shipping_id` int(11) NOT NULL DEFAULT '0',
  `store_settings_payments_id` int(11) NOT NULL DEFAULT '0',
  `state` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `note` text COLLATE utf8_czech_ci,
  `note_admin` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `store_settings_shipping_id` (`store_settings_shipping_id`),
  KEY `store_settings_payments_id` (`store_settings_payments_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `orders_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orders_id` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '1',
  `contacts_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_id` (`orders_id`,`contacts_id`),
  KEY `contacts_id` (`contacts_id`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `orders_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orders_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `store_stock_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_id` (`orders_id`),
  KEY `store_id` (`store_id`),
  KEY `store_stock_id` (`store_stock_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `orders_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `show` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `orders_states` (`id`, `title`, `show`) VALUES
(1, 'Pending', 1),
(3, 'Confirmed', 1),
(4, 'Shipped', 1),
(5, 'Cancelled', 1);

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `body` text COLLATE utf8_czech_ci,
  `body_en` text COLLATE utf8_czech_ci,
  `body_de` text COLLATE utf8_czech_ci,
  `body_ru` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pages_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pages_id` int(11) NOT NULL,
  `title` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `filename` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pages_id` (`pages_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `setkey` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `setvalue` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `settings` (`id`, `prefix`, `setkey`, `setvalue`, `description`) VALUES
(1, 'store', 'currency_code', '', 'Kód měny'),
(2, 'store', 'currency_symbol', '', 'Symbol měny'),
(3, NULL, 'vatin', '', 'IČ'),
(4, NULL, 'vatid', '', 'DIČ'),
(5, 'store', 'vat_payee', '1', 'Plátce DPH'),
(7, NULL, 'contact_email', '', 'Your e-mail address'),
(8, NULL, 'site_title', '', 'Name of your page'),
(9, 'store', 'store', '1', 'Is it store?'),
(10, 'stats', 'google_analytics_code', '', 'Google Analytics code');

CREATE TABLE IF NOT EXISTS `store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(200) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  `store_brands_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `store_brands_id` (`store_brands_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `store_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `store_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`title`),
  KEY `name_2` (`title`),
  KEY `parent` (`parent_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `store_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_categories_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_categories_id` (`store_categories_id`,`store_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `store_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `filename` varchar(150) COLLATE utf8_czech_ci NOT NULL,
  `filesize` int(11) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_czech_ci,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `store_param` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `param_cs` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `param_en` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `prefix` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
  `suffix` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `store_params` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `group` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `store_param_id` int(11) NOT NULL,
  `paramvalue` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `store_param_id` (`store_param_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `store_param_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_param_id` int(11) NOT NULL,
  `store_categories_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_param_id` (`store_param_id`,`store_categories_id`),
  KEY `store_categories_id` (`store_categories_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `store_settings_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `payment` int(11) NOT NULL,
  `vat` int(11) NOT NULL,
  `show` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `store_settings_payments` (`id`, `title`, `payment`, `vat`, `show`) VALUES
(6, 'Cash on delivery', 39, 21, 1),
(7, 'Bank transfer', 0, 0, 1);

CREATE TABLE IF NOT EXISTS `store_settings_shipping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `shipping` int(11) NOT NULL,
  `vat` int(11) NOT NULL,
  `show` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `store_settings_shipping` (`id`, `title`, `shipping`, `vat`, `show`) VALUES
(6, 'Pick up', 0, 0, 1);

CREATE TABLE IF NOT EXISTS `store_settings_vats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `vat` int(11) NOT NULL,
  `show` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `store_settings_vats` (`id`, `title`, `vat`, `show`) VALUES
(0, 'Zero', 0, 1),
(1, 'Test', 15, 1);

CREATE TABLE IF NOT EXISTS `store_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `title` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `store_settings_vats_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `store_brands_id` (`store_settings_vats_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(40) NOT NULL,
  `uid` varchar(10) NOT NULL,
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
  `role` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `users` (`id`, `username`, `uid`, `email`, `sex`, `name`, `password`, `date_created`, `date_visited`, `state`, `activation`, `newsletter`, `type`, `role`) VALUES
(0, 'guest', '', '', 0, NULL, '', NULL, NULL, 0, NULL, 0, 4, 4),
(1, 'admin', '000001', '', 0, '', '$2y$10$ofufHIW6LPMNHhl8v5E2oeQf.3aKC4l8lBKXN1RBKmQItFOVMk.jy', '', '', 1, NULL, 1, 1, 1);

ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`blog_categories_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_4` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`store_settings_shipping_id`) REFERENCES `store_settings_shipping` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`store_settings_payments_id`) REFERENCES `store_settings_payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cart_addresses`
  ADD CONSTRAINT `cart_addresses_ibfk_2` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_addresses_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_4` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`store_stock_id`) REFERENCES `store_stock` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`contacts_groups_id`) REFERENCES `contacts_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`store_settings_shipping_id`) REFERENCES `store_settings_shipping` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`store_settings_payments_id`) REFERENCES `store_settings_payments` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `orders_addresses`
  ADD CONSTRAINT `orders_addresses_ibfk_2` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orders_addresses_ibfk_1` FOREIGN KEY (`orders_id`) REFERENCES `orders` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `orders_items`
  ADD CONSTRAINT `orders_items_ibfk_1` FOREIGN KEY (`orders_id`) REFERENCES `orders` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orders_items_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orders_items_ibfk_3` FOREIGN KEY (`store_stock_id`) REFERENCES `store_stock` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `pages_files`
  ADD CONSTRAINT `pages_files_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `store`
  ADD CONSTRAINT `store_ibfk_3` FOREIGN KEY (`store_brands_id`) REFERENCES `store_brands` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `store_categories`
  ADD CONSTRAINT `store_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `store_categories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `store_category`
  ADD CONSTRAINT `store_category_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `store_category_ibfk_1` FOREIGN KEY (`store_categories_id`) REFERENCES `store_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `store_params`
  ADD CONSTRAINT `store_params_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `store_params_ibfk_2` FOREIGN KEY (`store_param_id`) REFERENCES `store_param` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `store_stock`
  ADD CONSTRAINT `store_stock_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `store_stock_ibfk_3` FOREIGN KEY (`store_settings_vats_id`) REFERENCES `store_settings_vats` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;