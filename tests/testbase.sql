SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
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
  `date_of_birth` date DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `contacts_openinghours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` smallint(6) NOT NULL,
  `hourstext` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `contacts_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_id` (`contacts_id`)
) ENGINE=InnoDB

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
  `main_file` tinyint(1) NOT NULL DEFAULT '0',
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
  `appearance` tinyint(1) NOT NULL DEFAULT '0',
  `helpdesk` tinyint(1) NOT NULL DEFAULT '0',
  `settings` tinyint(1) NOT NULL DEFAULT '0',
  `members` tinyint(1) NOT NULL DEFAULT '0',
  `pages` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_6` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_4` FOREIGN KEY (`countries_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `contacts_ibfk_5` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `contacts_openinghours`
  ADD CONSTRAINT `contacts_openinghours_ibfk_1` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


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

ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `snippets`
  ADD CONSTRAINT `snippets_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
  
  -- Data

INSERT INTO `categories` (`id`, `parent_id`, `description`, `title`, `sorted`) VALUES
(1, NULL, NULL, 'Nastavení', 145),
(2, NULL, '', 'Kontakty', 43),
(4, NULL, '', 'Links', 57),
(5, NULL, '', 'Členové', 60),
(6, 2, NULL, 'Značky zboží', 169),
(7, 2, NULL, 'Úloženka', 177),
(8, 2, NULL, 'Zákazníci', 44),
(9, 2, NULL, 'Kontakty na stránce', 45),
(10, 1, NULL, 'Základní nastavení', 146),
(11, 1, NULL, 'Kategorie', 149),
(12, 1, NULL, 'Kontakty členů', 148),
(13, 1, NULL, 'Obchod', 147),
(14, 13, NULL, 'Bonus', 167),
(15, 1, NULL, 'Blog', 154),
(16, 1, NULL, 'Kontakty', 166),
(17, 1, NULL, 'Služby', 176),
(18, 2, NULL, 'Místa k vyzvednutí', 46),
(19, 2, NULL, 'Newsletter', 153),
(20, 1, NULL, 'Vzhled', 154),
(21, 20, NULL, 'Carousel', 155),
(22, 20, NULL, 'Události', 157);

INSERT INTO `countries` (`id`, `title_cs`, `title_en`, `show`) VALUES
(1, 'Česká Republika', 'Czech Republic', 1),
(2, 'Slovensko', 'Slovakia', 0);


INSERT INTO `currencies` (`id`, `title`, `code`, `symbol`, `used`) VALUES
(1, 'Česká koruna', 'CZK', 'Kč', 1),
(2, 'Euro', 'EUR', '€', NULL);

INSERT INTO `helpdesk` (`id`, `title`, `description`, `fill_phone`) VALUES
(1, 'Kontaktní formulář', 'Tento formulář slouží Vašim zákazníkům, aby vás mohli kontaktovat ohledně jejich otázek nebo potávek.', 1),
(2, 'Vytvoření nové transakce', 'Když je objednávka vytvořena, je odeslán uživateli mail a zároveň mail administrátorovi.', 0),
(3, 'Registrační formulář', 'Formulář pro přihlášení jako člen', 0),
(4, 'Odeslání hesla pro existujícího uživatele', 'Tímto formulářem bude existujícímu uživateli odesláno nové heslo. Pozor. Pokaždé, když formulář odešlete, bude heslo změněno a odesláno.', 0),
(5, 'Vytvoření účtu z administrace', 'Vytvoření nového uživatelského účtu z administrace', 0),
(6, 'Změna stavu: Objednávka byla přijata', 'Znovupotvrzení objednávky', 0),
(7, 'Změna stavu: Objednávka byla potvrzena a vyřízena', 'Objednávka byla potvrzena a čeká na vyřízení', 0),
(8, 'Změna stavu: Objednávka zaplacena', 'Objdnávka byla zaplacena a čeká na vyřízení.', 0),
(9, 'Změna stavu: Objednávka byla expedována', 'Objednávka byla expedována a míří k zákazníkovi', 0),
(10, 'Změna stavu: Objednávka byla zrušena', 'Objednávka byla zrušena', 0),
(11, 'Zapomenuté heslo', 'Odeslání zapomenutého hesla', 0);


INSERT INTO `menu` (`id`, `parent_id`, `description`, `title`, `pages_id`, `url`, `sorted`) VALUES
(1, NULL, 'Hlavní menu/Main menu', 'Top', NULL, '', 5),
(2, 1, NULL, 'Homepage', NULL, '/', 3);

INSERT INTO `helpdesk_emails` (`id`, `template`, `subject`, `body`, `helpdesk_id`) VALUES
(1, 'request-admin-email', 'Poptávka', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">        <title>{$settings[''site:title'']}</title>        <style>        </style>                <table style="width: 800px; margin: 0 auto 0 auto;">            <tbody><tr>                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;                     font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">{$settings[''site:title'']}</td>            </tr>            <tr>                <td style="text-align: left;">                    <br>                    {$name}<br>                    {$phone}<br>                    {$email}<br>                    <br><br>                    {$message}<br>                    {$time}<br>                    {$address}                </td>            </tr>        </tbody></table>', 1),
(2, 'request-customer-email', 'Poptávka', '            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">        <title>{$settings[''site:title'']}</title>        <style>        </style>                <table style="width: 800px; margin: 0 auto 0 auto;">            <tbody><tr>                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;                     font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">{$settings[''site:title'']}</td>            </tr>            <tr>                <td style="text-align: left;">                    Děkujeme za Vaši zprávu. Budeme Vás brzy kontaktovat.                    <br>                    {$name}<br>                    {$phone}<br>                    {$email}<br>                    <br><br>                    {$message}<br>                </td>            </tr>        </tbody></table>    ', 1),
(3, 'state-1-auto', 'Objednávka od zákazníka', '{var $orderDb = $order->related(''orders_ids'', ''orders_id'')}\n{if $orderDb->count() > 0}\n    {var $orderId = $orderDb->fetch()}\n    {var $transId = ''Objednávka '' . $orderId->identifier}\n{else}\n    {var $transId = ''Transkace č. '' . str_pad($order->id, 6, ''0'', STR_PAD_LEFT)}\n{/if}\n\n<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\n        "http://www.w3.org/TR/html4/loose.dtd">\n<html lang="cs">\n<head>\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\n    <title>{$settings[''site:title'']}: {$transId}</title>\n    <style n:syntax="off">\n        * {\n            font-family: Arial;\n        }\n\n        .text-right {\n            text-align: right;\n        }\n    </style>\n</head>\n<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0"\n      style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\n\n<table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\n    <tr>\n        <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\n            {$transId}\n        </td>\n    </tr>\n    <tr>\n        <td>\n            Vaše objednávka byla přijata. O jejím průběhu budete informování e-mailem.\n        </td>\n    </tr>\n    <tr>\n        <td style="text-align: left;">\n            <br/>\n            <p>Doručovací metoda: {$order->store_settings_shipping->title}</p>\n\n            {if $pickup}\n                <p>Vyzvednutí na adrese:</p>\n\n            {$pickup->company}</br>\n            {$pickup->street}</br>\n            {$pickup->zip} {$pickup->city}</br>\n            {/if}\n\n            <p>Platební metoda: {$order->store_settings_payments->title}</p>\n            <p>E-mail: {$order->email}</p>\n            <p>Telefon: {$order->phone}</p>\n\n            <h3>Fakturační adresa</h3>\n\n            {$order->contacts->name}<br/>\n            {$order->contacts->company}<br/>\n            {$order->contacts->street}<br/>\n            {$order->contacts->zip} {$order->contacts->city}<br/>\n\n            {if $order->contacts->vatin}<p>IČ: {$order->contacts->vatin}</p>{/if}\n            {if $order->contacts->vatin}<p>DIČ: {$order->contacts->vatid}</p>{/if}\n            <br/>\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name && $order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <h3>Doručovací adresa</h3>\n            {/if}\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name}{$order->ref(''contacts'', ''delivery_contacts_id'')->name}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->company}{$order->ref(''contacts'', ''delivery_contacts_id'')->company}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->street}{$order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                {$order->ref(''contacts'', ''delivery_contacts_id'')->zip} {$order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                <br/><br/>\n            {/if}\n            {if $order->note}\n                <div class="well">\n                    <p>Zpráva:<br/>{$order->note}</p>\n                </div>\n            {/if}\n\n            {if $order->store_bonus_id}\n                <h2>Bonus</h2>\n\n                Váš dárek: {$order->store_bonus->stock->store->title}.\n            {/if}\n\n\n            <h2>Shrnutí objednávky</h2>\n\n            <table style="width: 100%;">\n                <tr>\n                    <th style="text-align: left;">Název</th>\n                    <th class="text-right">Cena/kus</th>\n                    <th class="text-right">DPH</th>\n                    <th class="text-right">Sazba DPH</th>\n                    <th class="text-right">Množství</th>\n                    <th class="text-right">Cena celkem</th>\n                </tr>\n                {foreach $order->related(''orders_items'', ''orders_id'') as $item}\n                    {if $settings[''store:order:isVatIncluded'']}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">{var $vat = round($item->price * $vatCoef, 0)}\n                                {$item->price - $vat} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">{number_format($item->price * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                    {var $amount += $item->amount}\n                    {var $total += $item->price * $item->amount}\n                    {var $weight += $item->stock->weight * $item->amount}\n                    {else}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">\n                                {var $vat = $item->price * ($item->store_settings_vats->vat/100)}\n                                {number_format($item->price - $vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">\n                                {number_format(($item->price * (1+($item->store_settings_vats->vat/100))) * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                        {var $amount += $item->amount}\n                        {var $total += $item->price * $item->amount}\n                        {var $weight += $item->stock->weight * $item->amount}\n                    {/if}\n                {/foreach}\n                <tr>\n                    <td colspan="5">Poštovné</td>\n                    <td class="text-right">{$order->shipping_price} {$settings[''store:currency:symbol'']}\n                    </td>\n                </tr>\n                {if $order->payment_price != 0}\n                    <tr>\n                        <td colspan="5">{$order->store_settings_payments->payment}</td>\n                        <td class="text-right">{$order->payment_price} {$settings[''store:currency:symbol'']}</td>\n                    </tr>\n                {/if}\n                <tr>\n                    <td>\n                        CELKEM\n                    </td>\n                    <td></td>\n                    <td></td>\n                    <td></td>\n                    <td class="text-right">{$amount}</td>\n                    <td class="text-right">{number_format($total + $order->shipping_price + $order->payment_price,  0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n            </table>\n\n        </td>\n    </tr>\n</table>\n\n</body>\n</html>', 2),
(4, 'state-1-admin', 'Nová objednávka', '{var $orderDb = $order->related(''orders_ids'', ''orders_id'')}\n\n{if $orderDb->count() > 0}\n    {var $orderId = $orderDb->fetch()}\n    {var $transId = ''Objednávka '' . $orderId->identifier}\n{else}\n    {var $transId = ''Transkace č. '' . str_pad($order->id, 6, ''0'', STR_PAD_LEFT)}\n{/if}\n\n<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\n        "http://www.w3.org/TR/html4/loose.dtd">\n<html lang="cs">\n<head>\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\n    <title>{$settings[''site:title'']}: Transakce č. {$transId}</title>\n    <style n:syntax="off">\n        * {\n            font-family: Arial;\n        }\n\n        .text-right {\n            text-align: right;\n        }\n    </style>\n</head>\n<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0"\n      style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\n\n<table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\n    <tr>\n        <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\n            {$transId}\n        </td>\n    </tr>\n    <tr>\n        <td style="text-align: left;">\n            <br/>\n            <p>Doručovací metoda: {$order->store_settings_shipping->title}</p>\n\n            {if $pickup}\n                <p>Vyzvednutí na adrese:</p>\n\n            {$pickup->company}</br>\n            {$pickup->street}</br>\n            {$pickup->zip} {$pickup->city}</br>\n            {/if}\n\n            <p>Platební metoda: {$order->store_settings_payments->title}</p>\n            <p>E-mail: {$order->email}</p>\n            <p>Telefon: {$order->phone}</p>\n\n            <h3>Fakturační adresa</h3>\n\n            {$order->contacts->name}<br/>\n            {$order->contacts->company}<br/>\n            {$order->contacts->street}<br/>\n            {$order->contacts->zip} {$order->contacts->city}<br/>\n\n            {if $order->contacts->vatin}<p>IČ: {$order->contacts->vatin}</p>{/if}\n            {if $order->contacts->vatin}<p>DIČ: {$order->contacts->vatid}</p>{/if}\n            <br/>\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name && $order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <h3>Doručovací adresa</h3>\n            {/if}\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name}{$order->ref(''contacts'', ''delivery_contacts_id'')->name}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->company}{$order->ref(''contacts'', ''delivery_contacts_id'')->company}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->street}{$order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                {$order->ref(''contacts'', ''delivery_contacts_id'')->zip} {$order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                <br/><br/>\n            {/if}\n            {if $order->note}\n                <div class="well">\n                    <p>Zpráva:<br/>{$order->note}</p>\n                </div>\n            {/if}\n\n            {if $order->store_bonus_id}\n                <h2>Bonus</h2>\n\n                Váš dárek: {$order->store_bonus->stock->store->title}.\n            {/if}\n\n\n            <h2>Shrnutí objednávky</h2>\n\n            <table style="width: 100%;">\n                <tr>\n                    <th style="text-align: left;">Název</th>\n                    <th class="text-right">Cena/kus</th>\n                    <th class="text-right">DPH</th>\n                    <th class="text-right">Sazba DPH</th>\n                    <th class="text-right">Množství</th>\n                    <th class="text-right">Cena celkem</th>\n                </tr>\n                {foreach $order->related(''orders_items'', ''orders_id'') as $item}\n                    {if $settings[''store:order:isVatIncluded'']}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">{var $vat = round($item->price * $vatCoef, 0)}\n                                {$item->price - $vat} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">{number_format($item->price * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                    {var $amount += $item->amount}\n                    {var $total += $item->price * $item->amount}\n                    {var $weight += $item->stock->weight * $item->amount}\n                    {else}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">\n                                {var $vat = $item->price * ($item->store_settings_vats->vat/100)}\n                                {number_format($item->price - $vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">\n                                {number_format(($item->price * (1+($item->store_settings_vats->vat/100))) * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                        {var $amount += $item->amount}\n                        {var $total += $item->price * $item->amount}\n                        {var $weight += $item->stock->weight * $item->amount}\n                    {/if}\n                {/foreach}\n                <tr>\n                    <td colspan="5">Poštovné</td>\n                    <td class="text-right">{$order->shipping_price} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n                {if $order->payment_price != 0}\n                    <tr>\n                        <td colspan="5">{$order->store_settings_payments->payment}</td>\n                        <td class="text-right">{$order->payment_price} {$settings[''store:currency:symbol'']}</td>\n                    </tr>\n                {/if}\n                <tr>\n                    <td>\n                        CELKEM\n                    </td>\n                    <td></td>\n                    <td></td>\n                    <td></td>\n                    <td class="text-right">{$amount}</td>\n                    <td class="text-right">{number_format($total + $order->shipping_price + $order->payment_price,  0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n            </table>\n\n            <br/><br/>\n\n            <br/><br/>\n\n        </td>\n    </tr>\n</table>\n\n</body>\n</html>', 2),
(5, 'signup-member', 'Nový účet', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">        <title>{$settings[''site:title'']}: Nový účet</title>                <p>Dobrý den,</p>        Ověření účtu <a href="{$settings[''site:url:base'']}/sign/verify?user={$username}&amp;code={$activationCode}&amp;do=verifyForm-submit">            {$settings[''site:url:base'']}/sign/verify?user={$username}&amp;code={$activationCode}&amp;do=verify-check</a>        <br>        <br>        V případě, že máte jakékoliv otázky, neváhejte se nás zeptat na tomto e-mailu.', 3),
(6, 'signup-member-confirmbyadmin', 'Nový účet - bude potvrzeno', '<html>\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Nový účet</title>\r\n    </head>\r\n    <body>\r\n        <p>Dobrý den,</p>\r\n\r\n        Po ověření administrací {$settings[''site:title'']} Vám bude zaslána zpráva a Vy se můžete přihlásit.\r\n        <br />\r\n        <br />\r\n        V případě, že máte jakékoliv otázky, neváhejte se nás zeptat na tomto e-mailu.\r\n    </body>\r\n</html>', 3),
(7, 'signup-admin-confirm', 'Nový účet pro potvrzení', '<html>\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Nový účet {$username}, </title>\r\n    </head>\r\n    <body>\r\n        <p>Nový účet</p>\r\n\r\n        <div style="display: block; color: white; background-color: navy; min-width: 150px; height: 60px; padding: 5px;">\r\n            <a href="{$settings[''site:url:base'']}/sign/verify?user={$username}&code={$activationCode}&do=verifyForm-submit">\r\n                Ověřit účet\r\n            </a>\r\n        </div>\r\n        <br />\r\n        <br />\r\n        <table>\r\n            <tr>\r\n                <td>Uživatel</td>\r\n                <td>{$form->values->username}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>E-mail</td>\r\n                <td>{$form->values->email}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Jméno</td>\r\n                <td>{$form->values->name}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Ulice</td>\r\n                <td>{$form->values->street}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Město</td>\r\n                <td>{$form->values->city}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>PSČ</td>\r\n                <td>{$form->values->zip}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Společnost</td>\r\n                <td>{$form->values->company}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>IČ</td>\r\n                <td>{$form->values->vatin}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>DIČ</td>\r\n                <td>{$form->values->vatid}</td>\r\n            </tr>\r\n        </table>\r\n\r\n        {if $g == 45646}\r\n            {if count($aresArr) > 0}\r\n                Výpis z ARESu:<br />\r\n                <br />\r\n                IČ: {$aresArr[''in'']}<br />\r\n                DIČ: {$aresArr[''tin'']}<br />\r\n                Název: {$aresArr[''company'']}<br />\r\n                Ulice: {$aresArr[''street'']}<br />\r\n                Město: {$aresArr[''city'']}<br />\r\n                PSČ: {$aresArr[''zip'']}<br />\r\n                Plátce DPH: {if $aresArr[''vat_pay'']}ano{else}ne{/if}<br />\r\n\r\n                Spis: {$aresArr[''file_number'']}<br />\r\n                Městský soud: {$aresArr[''court'']}\r\n            {/if}\r\n        {/if}\r\n    </body>\r\n</html>', 3),
(8, 'member-resend-login', 'Vytvoření nového hesla', '<html>\r\n<head>\r\n<title>{$settings[''site:title'']}: Vytvoření nového hesla</title>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">\r\n                    {$settings[''site:title'']}\r\n                </td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Bylo Vám vytvořeno nové heslo. Zde jsou údaje nutné k přihlášení\r\n                    <br /><br />\r\n                    uživatelské jméno: {$username}<br />\r\n                    heslo: {$password}<br />\r\n                    <br /><br />\r\n                    Přihlašte se: <a href="{$settings[''site:url:base'']}/sign/in">{$settings[''site:url:base'']}/sign/in</a>\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 4),
(9, 'member-new-email', 'Nový e-mail', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Nový e-mail</title>\r\n        <style>\r\n\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">{$settings[''site:title'']}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Your account was successfully created. You can now log in.\r\n                    <br /><br />\r\n                    user name: {$username}<br />\r\n                    password: {$password}<br />\r\n                    <br /><br />\r\n                    Log in: <a href="{$settings[''site:url:base'']}/admin">{$settings[''site:url:base'']}/admin</a>\r\n                </td>\r\n            </tr>\r\n        </table>\r\n\r\n    </body>\r\n</html>', 5),
(10, 'state-1', ' Objednávka  byla přijata', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Objednávka č. {$oid} byla přijata</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\r\n                    Objednávka č. {$oid} byla přijata\r\n                </td>\r\n            </tr>\r\n        </table>\r\n\r\n    </body>\r\n</html>', 6),
(11, 'state-2', 'Objednávka čeká na vyřízení', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Objednávka č. {$oid} byla potvrzena a čeká na vyřízení</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">{$settings["site:title"]}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Objednávka č. {$oid} čeká na vyřízení\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 7),
(12, 'state-3', 'Objednávka byla zaplacena a čeká na vyřízení', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Objednávka č. {$oid} byla zaplacena a čeká na vyřízení</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">{$settings["site:title"]}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Objednávka čeká na vyřízení\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 8),
(13, 'state-4', 'Objednávka byla expedována', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings["site:title"]}: Objednávka č. {$oid} byla expedována a míří k Vám.</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">{$settings["site:title"]}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Objednávka č. {$oid} byla expedována a míří k Vám.\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 9),
(14, 'state-5', 'Objednávka byla zrušena', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings["site:title"]}: Objednávka č. {$oid} byla zrušena</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">{$settings["site:title"]}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Objednávka č. {$oid} byla zrušena\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 10),
(15, 'lostpass-member', 'Informace o novém hesle', 'Na základě Vaší žádosti Vám posíláme odkaz na obnovení hesla.\r\n<br /><br />\r\nK vytvoření nového hesla klikněte na odkaz níže:\r\n<br />\r\n<a href="{$settings[''site:url:base'']}/sign/resetpass/?code={$code}&email={$email}">\r\n    {$settings[''site:url:base'']}/sign/resetpass/?code={$code}&email={$email}\r\n</a>\r\n<br /><br />\r\n<strong><a href="{$settings[''site:url:base'']}">{$settings[''site:title'']}</a></strong>\r\n', 11),
(16, 'lostpass-admin', 'Informace o novém hesle', 'Na základě Vaší žádosti Vám posíláme odkaz na obnovení hesla.\r\n<br /><br />\r\nK vytvoření nového hesla klikněte na odkaz níže:\r\n<br />\r\n<a href="{$settings[''site:url:base'']}/admin/sign/resetpass/?code={$code}&email={$email}">\r\n    {$settings[''site:url:base'']}/admin/sign/resetpass/?code={$code}&email={$email}\r\n</a>\r\n<br /><br />\r\n<strong><a href="{$settings[''site:url:base'']}">{$settings[''site:title'']}</a></strong>\r\n', 11);;

INSERT INTO `pages_types` (`id`, `content_type`, `presenter`, `action`, `prefix`) VALUES
(1, 'Page', 'Front:Pages', 'default', ''),
(2, 'Blog', 'Front:Blog', 'detail', 'blog'),
(3, 'Event', 'Front:Events', 'detail', ''),
(4, 'Product', 'Front:Product', 'default', ''),
(5, 'Contacts', 'Front:Contacts', 'detail', ''),
(6, 'Galerie', 'Front:Gallery', 'album', ''),
(7, 'Product Category', 'Front:Catalogue', 'default', ''),
(8, 'Dokumenty', 'Front:Documents', 'default', ''),
(9, 'Template', '', 'default', '');

INSERT INTO `pages_templates` (`id`, `pages_types_id`, `template`) VALUES
(1, 6, 'albumWithDescription');

UPDATE `pages_types` SET `id` = 0 WHERE `id` = 9;

INSERT INTO `pages` (`slug`, `title`, `document`, `preview`, `pages_id`, `users_id`, `public`, `metadesc`, `metakeys`, `date_created`, `date_published`, `pages_types_id`, `pages_templates_id`, `sorted`, `editable`, `presenter`) VALUES
('', 'Homepage', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 0, NULL, 41, 0, 'Front:Homepage'),
('kontakt', 'Kontakty', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 0, NULL, 43, 0, 'Front:Contact'),
('blog', 'Blog', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 0, NULL, 45, 0, 'Front:Blog'),
('galerie', 'Galerie', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 47, 0, 'Front:Gallery'),
('dokumenty', NULL, 'Dokumenty', NULL, NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 48, 0, 'Front:Documents');

INSERT INTO `settings` (`categories_id`, `setkey`, `setvalue`, `description_cs`, `description_en`, `type`, `admin_editable`) VALUES 
(15, 'blog:short:showPreview', '1', 'Zobrazovat zkrácenou verzi článku v krátké verzi blogu.', 'Show preview of an article.', 'boolean', 1),
(15, 'blog:short:showAuthor', '0', 'Zobrazovat autora', 'Show author (member) of the article.', 'boolean', 1),
(15, 'blog:short:showImage', '0', 'Zobrazovat náhled obrázku', 'Show image thumbnail', 'boolean', 1),
(15, 'blog:short:showDate', '0', 'Zobrazovat datum vydání', 'Show date published.', 'boolean', 1),
(15, 'blog:preview:length', '350', 'Délka náhledového textu (0 znamená nezkrácený text)', 'Length of preview text (0 means complete text)', 'numeric', 1),
(14, 'bonus:enabled', '0', 'Je aktivována odměna za výši nákupu', 'Is the reward for the amount of the purchase activated?', 'boolean', 1),
(14, 'bonus:registeredUsersOnly', '1', 'Bonus pouze pro registrované uživatele', 'Bonus for registered users only', 'boolean', 1),
(11, 'categories:adminEnabled', '0', 'Pokud není nastaveno, jen admn uvidí Categories', 'Only admin can see Categories if not set', 'boolean', 1),
(11, 'categories:id:pricelist', '3', 'Identikátor kategorie Ceník', 'Pricelist category identifier', '', 1),
(11, 'categories:id:members', '5', 'Identikátor kategorie Členové', 'Members category identifier', '', 1),
(11, 'categories:id:contact', '2', 'Identikátor kategorie Kontakty', 'Contact category identifier', '', 1),
(11, 'categories:id:link', '4', 'Identikátor kategorie Odkazy', 'Link category identifier', '', 1),
(11, 'categories:id:media', '7', 'Identikátor kategorie Media', 'Media category identifier', '', 1),
(11, 'categories:id:settings', '1', 'Identikátor kategorie Settings', 'Settings category identifier', '', 0),
(11, 'categories:id:contactsBrands', '6', 'Identifikátor kategorie obchodních značek v kontaktech', 'Category identifier for brands in contacts', '', 1),
(11, 'categories:id:contactsPickups', '16', 'Identifikátor kategorie míst k vyzvednutí', 'Category identifier for pickup places', '', 1),
(11, 'categories:id:mediaGallery', '6', 'Identikátor kategorie Media: Galerie', 'Media:Gallery category identifier', '', 1),
(11, 'categories:id:mediaDocs', '8', 'Identikátor kategorie Media: Dokumenty', 'Media:Documents category identifier', '', 1),
(11, 'categories:id:contactsNewsletter', '19', 'Identikátor kategorie Newsletter  v kontaktech', 'Contacts newsletter category identifier', '', 0),
(16, 'contacts:email:hq', '', 'Vaše e-mailová adresa', 'Your e-mail address', NULL, 1),
(16, 'contacts:email:techSupport', '', 'Technická podpora', 'Technical support', NULL, 1),
(16, 'contacts:email:order', '', 'E-mail pro odesílání objednávky', 'E-mail for sending orders', NULL, 1),
(16, 'contacts:smartForm:enabled', '0', 'Povolit doplňování SmartForm', 'SmartForm address autosuggest enabled', 'boolean', 1),
(16, 'contacts:smartForm:clientId', '', 'Klientský kód SmartForm', 'SmartForm client code', '', 1),
(16, 'contacts:residency:contacts_id', '14', 'Kontaktní informace o sídlu', 'Headquarters contact information', 'table:contacts;column:name', 1),
(16, 'members:groups:enabled', '0', 'Vytvářet uživatelské skupiny', 'Create user groups', 'boolean', 1),
(12, 'members:group:categoryId', '60', 'Identifikátor uživatelské kategorie', 'User category identifier', NULL, 1),
(12, 'members:signup:contactEnabled', '1', 'Kontaktní informace v registračním formuláři', 'Contasct information in sign up form', 'boolean', 1),
(12, 'members:signup:companyEnabled', '1', 'Firemní informace v registračním formuláři', 'Business information in sign up form', 'boolean', 1),
(12, 'members:signup:confirmByAdmin', '1', 'Registrace uživatele musí být potvrzena administrátorem.', 'User Registration must be confirmed by the admin.', 'boolean', 1),
(10, 'site:admin:adminBarEnabled', '1', 'Navigace administrace v prezentaci', 'Admin navigation in presentation', 'boolean', 1),
(10, 'site:currency', '1', 'Měna stránky', 'Currency of the site', 'table:currencies;column:title', 1),
(10, 'site:editor:type', 'summernote', 'Který editor bude vybrán. V současnosti Summernote nebo CK Editor', 'Which editor will be selected. Currently Summernote or CKEditor', '', 0),
(10, 'site:vat:payee', '1', 'Plátce DPH', 'VAT Payee', 'boolean', 1),
(10, 'site:title', '', 'Název stránky', 'Name of your page', NULL, 1),
(10, 'site:url:base', '', 'URL adresa', 'URL address', NULL, 1),
(10, 'maintenance_enabled', 0, 'Stránka v módu údržby', 'Site in maintenance mode', 'boolean', 1),
(10, 'maintenance_message', 'Stránka je v módu údržby', 'Informace o údržbě pro návštěvníka', 'Maintenace information for visitor', NULL, 1),
(10, 'site_ip_whitelist', '', 'Adresy povolené pro zobrazení obsahu. Oddělujte středníkem', 'IP addresses enabled to view the content. Separate by comma.', NULL, 1),
(10, 'site_cookie_whitelist', '', 'Heslo v cookie nutné pro zobrazení obsahu. Vkládá se pomocí querystringu (secretx=pwd)', 'Password in a cookie required for viewing the content. Insert using qeurystring (secretx pwd =)', NULL, 1),
(17, 'social:ga_code', '', 'Kód Google Analytics', 'Google Analytics code', NULL, 1),
(17, 'social:fb:enabled', '1', 'Povolené zobrazování Facebooku', 'Facebook enabled', 'boolean', 1),
(17, 'social:fb:type', 'page', 'Typ: účet nebo stránka', 'Type: account or page', NULL, 1),
(17, 'social:fb:url', '', 'Adresa stránky nebo účtu včetně http', 'Address of the page or account including http', NULL, 1),
(17, 'social:twitter:account', '', 'Účet na Twitteru', 'Twitter account', NULL, 1),
(13, 'store:enabled', '0', 'Provozujete obchod?', 'Is it store?', 'boolean', 1),
(13, 'store:stock:hideEmpty', '1', 'Schovávat zboží, které není na skladu', 'Hide products not in stock', 'boolean', 1),
(13, 'store:new:days', '14', 'Počet dní, kdy je produkt označován jako nový', 'Number of days when product is displayed as new', NULL, 1),
(13, 'store_admin_dashboard', '1', 'Zobrazit dashboard obchodu na homepage administrace', 'View store dashboard in administration''s homepage', 'boolean', '1'),
(13, 'store:order:generateIdsOnOrderConfirm', '1', 'Vytvářet automaticky čísla objednávek už při dokončení objednávky', 'Generate purchase order identifiers immediately aftter checkout', 'boolean', 1),
(13, 'store:currency:code', 'CZK', 'Kód měny', 'Currency code', NULL, 1),
(13, 'store:currency:symbol', 'Kč', 'Symbol měny', 'Currency symbol', NULL, 1),
(13, 'store:stock:amountDefault', '10', 'Přednastavené množství položek na skladu', 'Preset amount of items in stock', 'boolean', 0),
(13, 'store:parametres:searchEnabled', '0', 'Parametrické hledání povoleno', 'Parametric search enabled', 'boolean', 1),
(13, 'store:stock:shippingByWeight', '1', 'Cena poštovného podle hmotnosti', 'Shipping price calculated by weight', 'boolean', 1),
(13, 'store:order:guestOrderEnabled', '1', 'Povolit objednání neregistrovaným uživatelům', 'Unregistered user order enabled', 'boolean', 1),
(13, 'store:order:isVatIncluded', '1', 'Je DPH připočítáváno nebo odpočítáváno (1 je připočítáváno)', 'Is VAT included (1 means included)', 'boolean', 0),
(13, 'store:stock:deductStock', '0', 'Odečítat zboží po jeho koupi', 'Subtract the stock after purchase', 'boolean', 1),
(20, 'appearance:paths:logo', 'logo.png', 'Obrázek loga ve vysokém rozlišení a barvě', 'Image of logo in high resolution and color', 'local_path', 0),
(20, 'appearance:paths:favicon:ico', 'favicon.ico', 'Favicon s příponou ico', 'Favicon with ico suffix', 'local_path', 0),
(21, 'appearance:carousel:directions', '0', 'Zobrazit indikátory (levá a pravá šipka)', 'Show indicators (left and right arrow)', 'boolean', 1),
(21, 'appearance:carousel:indicators', '0', 'Zobrazit menu', 'Show menu', 'boolean', 1);

INSERT INTO `languages` (`id`, `code`, `title`, `used`, `default`) VALUES
(1, 'cs', 'čeština', 1, 1);

INSERT INTO `users_roles` (`id`, `title`, `admin_access`, `appearance_images`, `helpdesk_edit`, `settings_display`, `settings_edit`, `members_display`, `members_edit`, `members_create`, `members_delete`, `pages_edit`, `pages_document`) VALUES
(1, 'Admin', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(2, 'Super User', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(3, 'Editor', 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1),
(4, 'Site User', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

INSERT INTO `users` (`id`, `username`, `categories_id`, `uid`, `email`, `sex`, `name`, `password`, `date_created`, `date_visited`, `state`, `activation`, `newsletter`, `type`, `users_roles_id`, `login_error`, `login_success`) VALUES
(1, 'admin', NULL, '000001', '', 2, '', '$2y$10$DLhMCsYpbB.xHJ501e.xMOvhneiT1U6YypGAcOna/V2kzIGZOwxla', NULL, NULL, 1, NULL, 1, 1, 1, 0, 0);

INSERT INTO `blacklist` (`title`) VALUES
('viagra'),
('cialis'),
('essay'),
('casino'),
('zovirax'),
('levitra'),
('modafinil'),
('lorazepam'),
('piča'),
('píča'),
('píčo'),
('čubka'),
('kurva'),
('čubko'),
('kunda'),
('kundo'),
('hovado'),
('As seen on'),
('Buying judgments'),
('discount'),
('earn'),
('hidden'),
('income'),
('cards'),
('accept'),
('cash'),
('vicodin'),
('valium'),
('weight');
