-- Caloris CMS SQL Dump
-- version 2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES 'utf8';

CREATE TABLE `addons` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `key` varchar(40) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `blacklist` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `blacklist` (`id`, `title`) VALUES
(1, 'viagra'),
(2, 'cialis'),
(3, 'essay'),
(4, 'casino'),
(5, 'zovirax'),
(6, 'levitra'),
(7, 'modafinil'),
(8, 'lorazepam'),
(9, 'piča'),
(10, 'píča'),
(11, 'píčo'),
(12, 'čubka'),
(13, 'kurva'),
(14, 'čubko'),
(15, 'kunda'),
(16, 'kundo'),
(17, 'hovado'),
(18, 'As seen on'),
(19, 'Buying judgments'),
(20, 'discount'),
(21, 'earn'),
(22, 'hidden'),
(23, 'income'),
(24, 'cards'),
(25, 'accept'),
(26, 'cash'),
(27, 'vicodin'),
(28, 'valium'),
(29, 'weight');

CREATE TABLE `carousel` (
  `id` int(11) NOT NULL,
  `title` varchar(80) DEFAULT NULL,
  `description` text,
  `uri` varchar(250) NOT NULL,
  `image` varchar(120) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `sorted` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `contacts_categories_id` int(11) DEFAULT NULL,
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
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `contacts_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text,
  `title` varchar(80) NOT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `contacts_categories` (`id`, `parent_id`, `description`, `title`, `sorted`) VALUES
(5, NULL, '', 'Členové', 60),
(6, NULL, NULL, 'Značky zboží', 169),
(7, NULL, NULL, 'Úloženka', 177),
(8, NULL, NULL, 'Zákazníci', 44),
(9, NULL, NULL, 'Kontakty na stránce', 45),
(18, NULL, NULL, 'Místa k vyzvednutí', 46),
(19, NULL, NULL, 'Newsletter', 153);

CREATE TABLE `contacts_communication` (
  `id` int(11) NOT NULL,
  `contacts_id` int(11) NOT NULL,
  `communication_type` varchar(80) NOT NULL,
  `communication_value` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `contacts_openinghours` (
  `id` int(11) NOT NULL,
  `day` smallint(6) NOT NULL,
  `hourstext` varchar(80) DEFAULT NULL,
  `contacts_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `title_cs` varchar(120) DEFAULT NULL,
  `title_en` varchar(120) DEFAULT NULL,
  `show` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `countries` (`id`, `title_cs`, `title_en`, `show`) VALUES
(1, 'Česká Republika', 'Czech Republic', 1),
(2, 'Slovensko', 'Slovakia', 0);

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `title` varchar(60) DEFAULT NULL,
  `code` varchar(8) DEFAULT NULL,
  `symbol` varchar(20) DEFAULT NULL,
  `used` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `currencies` (`id`, `title`, `code`, `symbol`, `used`) VALUES
(1, 'Česká koruna', 'CZK', 'Kč', 1),
(2, 'Euro', 'EUR', '€', NULL),
(3, 'Americký dolar', 'USD', '$', 0);

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
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
  `time_range` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `events_signed` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `note` text,
  `events_id` int(11) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `helpdesk` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_czech_ci,
  `fill_phone` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

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
(11, 'Zapomenuté heslo', 'Odeslání zapomenutého hesla', 0),
(12, 'Registrační formulář ověřený administrátorem', 'Formulář pro registraci, která bude ověřena správou stránek', 0);

CREATE TABLE `helpdesk_emails` (
  `id` int(11) NOT NULL,
  `template` varchar(40) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `body` text NOT NULL,
  `helpdesk_templates_id` int(11) DEFAULT NULL,
  `helpdesk_id` int(11) DEFAULT NULL,
  `log` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `helpdesk_emails` (`id`, `template`, `subject`, `body`, `helpdesk_id`) VALUES
(1, 'request-admin-email', 'Poptávka', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">        <title>{$settings[''site:title'']}</title>        <style>        </style>                <table style="width: 800px; margin: 0 auto 0 auto;">            <tbody><tr>                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;                     font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">{$settings[''site:title'']}</td>            </tr>            <tr>                <td style="text-align: left;">                    <br>                    {$name}<br>                    {$phone}<br>                    {$email}<br>                    <br><br>                    {$message}<br>                    {$time}<br>                    {$address}                </td>            </tr>        </tbody></table>', 1),
(2, 'request-customer-email', 'Poptávka', '            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">        <title>{$settings[''site:title'']}</title>        <style>        </style>                <table style="width: 800px; margin: 0 auto 0 auto;">            <tbody><tr>                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;                     font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">{$settings[''site:title'']}</td>            </tr>            <tr>                <td style="text-align: left;">                    Děkujeme za Vaši zprávu. Budeme Vás brzy kontaktovat.                    <br>                    {$name}<br>                    {$phone}<br>                    {$email}<br>                    <br><br>                    {$message}<br>                </td>            </tr>        </tbody></table>    ', 1),
(3, 'state-1-auto', 'Objednávka od zákazníka', '{var $orderDb = $order->related(''orders_ids'', ''orders_id'')}\n{if $orderDb->count() > 0}\n    {var $orderId = $orderDb->fetch()}\n    {var $transId = ''Objednávka '' . $orderId->identifier}\n{else}\n    {var $transId = ''Transkace č. '' . str_pad($order->id, 6, ''0'', STR_PAD_LEFT)}\n{/if}\n\n<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\n        "http://www.w3.org/TR/html4/loose.dtd">\n<html lang="cs">\n<head>\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\n    <title>{$settings[''site:title'']}: {$transId}</title>\n    <style n:syntax="off">\n        * {\n            font-family: Arial;\n        }\n\n        .text-right {\n            text-align: right;\n        }\n    </style>\n</head>\n<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0"\n      style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\n\n<table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\n    <tr>\n        <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\n            {$transId}\n        </td>\n    </tr>\n    <tr>\n        <td>\n            Vaše objednávka byla přijata. O jejím průběhu budete informování e-mailem.\n        </td>\n    </tr>\n    <tr>\n        <td style="text-align: left;">\n            <br/>\n            <p>Doručovací metoda: {$order->store_settings_shipping->title}</p>\n\n            {if $pickup}\n                <p>Vyzvednutí na adrese:</p>\n\n            {$pickup->company}</br>\n            {$pickup->street}</br>\n            {$pickup->zip} {$pickup->city}</br>\n            {/if}\n\n            <p>Platební metoda: {$order->store_settings_payments->title}</p>\n            <p>E-mail: {$order->email}</p>\n            <p>Telefon: {$order->phone}</p>\n\n            <h3>Fakturační adresa</h3>\n\n            {$order->contacts->name}<br/>\n            {$order->contacts->company}<br/>\n            {$order->contacts->street}<br/>\n            {$order->contacts->zip} {$order->contacts->city}<br/>\n\n            {if $order->contacts->vatin}<p>IČ: {$order->contacts->vatin}</p>{/if}\n            {if $order->contacts->vatin}<p>DIČ: {$order->contacts->vatid}</p>{/if}\n            <br/>\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name && $order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <h3>Doručovací adresa</h3>\n            {/if}\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name}{$order->ref(''contacts'', ''delivery_contacts_id'')->name}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->company}{$order->ref(''contacts'', ''delivery_contacts_id'')->company}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->street}{$order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                {$order->ref(''contacts'', ''delivery_contacts_id'')->zip} {$order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                <br/><br/>\n            {/if}\n            {if $order->note}\n                <div class="well">\n                    <p>Zpráva:<br/>{$order->note}</p>\n                </div>\n            {/if}\n\n            {if $order->store_bonus_id}\n                <h2>Bonus</h2>\n\n                Váš dárek: {$order->store_bonus->stock->store->title}.\n            {/if}\n\n\n            <h2>Shrnutí objednávky</h2>\n\n            <table style="width: 100%;">\n                <tr>\n                    <th style="text-align: left;">Název</th>\n                    <th class="text-right">Cena/kus</th>\n                    <th class="text-right">DPH</th>\n                    <th class="text-right">Sazba DPH</th>\n                    <th class="text-right">Množství</th>\n                    <th class="text-right">Cena celkem</th>\n                </tr>\n                {foreach $order->related(''orders_items'', ''orders_id'') as $item}\n                    {if $settings[''store:order:isVatIncluded'']}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">{var $vat = round($item->price * $vatCoef, 0)}\n                                {$item->price - $vat} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">{number_format($item->price * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                    {var $amount += $item->amount}\n                    {var $total += $item->price * $item->amount}\n                    {var $weight += $item->stock->weight * $item->amount}\n                    {else}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">\n                                {var $vat = $item->price * ($item->store_settings_vats->vat/100)}\n                                {number_format($item->price - $vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">\n                                {number_format(($item->price * (1+($item->store_settings_vats->vat/100))) * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                        {var $amount += $item->amount}\n                        {var $total += $item->price * $item->amount}\n                        {var $weight += $item->stock->weight * $item->amount}\n                    {/if}\n                {/foreach}\n                <tr>\n                    <td colspan="5">Poštovné</td>\n                    <td class="text-right">{$order->shipping_price} {$settings[''store:currency:symbol'']}\n                    </td>\n                </tr>\n                {if $order->payment_price != 0}\n                    <tr>\n                        <td colspan="5">{$order->store_settings_payments->payment}</td>\n                        <td class="text-right">{$order->payment_price} {$settings[''store:currency:symbol'']}</td>\n                    </tr>\n                {/if}\n                <tr>\n                    <td>\n                        CELKEM\n                    </td>\n                    <td></td>\n                    <td></td>\n                    <td></td>\n                    <td class="text-right">{$amount}</td>\n                    <td class="text-right">{number_format($total + $order->shipping_price + $order->payment_price,  0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n            </table>\n\n        </td>\n    </tr>\n</table>\n\n</body>\n</html>', 2),
(4, 'state-1-admin', 'Nová objednávka', '{var $orderDb = $order->related(''orders_ids'', ''orders_id'')}\n\n{if $orderDb->count() > 0}\n    {var $orderId = $orderDb->fetch()}\n    {var $transId = ''Objednávka '' . $orderId->identifier}\n{else}\n    {var $transId = ''Transkace č. '' . str_pad($order->id, 6, ''0'', STR_PAD_LEFT)}\n{/if}\n\n<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\n        "http://www.w3.org/TR/html4/loose.dtd">\n<html lang="cs">\n<head>\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\n    <title>{$settings[''site:title'']}: Transakce č. {$transId}</title>\n    <style n:syntax="off">\n        * {\n            font-family: Arial;\n        }\n\n        .text-right {\n            text-align: right;\n        }\n    </style>\n</head>\n<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0"\n      style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\n\n<table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\n    <tr>\n        <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\n            {$transId}\n        </td>\n    </tr>\n    <tr>\n        <td style="text-align: left;">\n            <br/>\n            <p>Doručovací metoda: {$order->store_settings_shipping->title}</p>\n\n            {if $pickup}\n                <p>Vyzvednutí na adrese:</p>\n\n            {$pickup->company}</br>\n            {$pickup->street}</br>\n            {$pickup->zip} {$pickup->city}</br>\n            {/if}\n\n            <p>Platební metoda: {$order->store_settings_payments->title}</p>\n            <p>E-mail: {$order->email}</p>\n            <p>Telefon: {$order->phone}</p>\n\n            <h3>Fakturační adresa</h3>\n\n            {$order->contacts->name}<br/>\n            {$order->contacts->company}<br/>\n            {$order->contacts->street}<br/>\n            {$order->contacts->zip} {$order->contacts->city}<br/>\n\n            {if $order->contacts->vatin}<p>IČ: {$order->contacts->vatin}</p>{/if}\n            {if $order->contacts->vatin}<p>DIČ: {$order->contacts->vatid}</p>{/if}\n            <br/>\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name && $order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <h3>Doručovací adresa</h3>\n            {/if}\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name}{$order->ref(''contacts'', ''delivery_contacts_id'')->name}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->company}{$order->ref(''contacts'', ''delivery_contacts_id'')->company}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->street}{$order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                {$order->ref(''contacts'', ''delivery_contacts_id'')->zip} {$order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                <br/><br/>\n            {/if}\n            {if $order->note}\n                <div class="well">\n                    <p>Zpráva:<br/>{$order->note}</p>\n                </div>\n            {/if}\n\n            {if $order->store_bonus_id}\n                <h2>Bonus</h2>\n\n                Váš dárek: {$order->store_bonus->stock->store->title}.\n            {/if}\n\n\n            <h2>Shrnutí objednávky</h2>\n\n            <table style="width: 100%;">\n                <tr>\n                    <th style="text-align: left;">Název</th>\n                    <th class="text-right">Cena/kus</th>\n                    <th class="text-right">DPH</th>\n                    <th class="text-right">Sazba DPH</th>\n                    <th class="text-right">Množství</th>\n                    <th class="text-right">Cena celkem</th>\n                </tr>\n                {foreach $order->related(''orders_items'', ''orders_id'') as $item}\n                    {if $settings[''store:order:isVatIncluded'']}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">{var $vat = round($item->price * $vatCoef, 0)}\n                                {$item->price - $vat} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">{number_format($item->price * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                    {var $amount += $item->amount}\n                    {var $total += $item->price * $item->amount}\n                    {var $weight += $item->stock->weight * $item->amount}\n                    {else}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">\n                                {var $vat = $item->price * ($item->store_settings_vats->vat/100)}\n                                {number_format($item->price - $vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">\n                                {number_format(($item->price * (1+($item->store_settings_vats->vat/100))) * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                        {var $amount += $item->amount}\n                        {var $total += $item->price * $item->amount}\n                        {var $weight += $item->stock->weight * $item->amount}\n                    {/if}\n                {/foreach}\n                <tr>\n                    <td colspan="5">Poštovné</td>\n                    <td class="text-right">{$order->shipping_price} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n                {if $order->payment_price != 0}\n                    <tr>\n                        <td colspan="5">{$order->store_settings_payments->payment}</td>\n                        <td class="text-right">{$order->payment_price} {$settings[''store:currency:symbol'']}</td>\n                    </tr>\n                {/if}\n                <tr>\n                    <td>\n                        CELKEM\n                    </td>\n                    <td></td>\n                    <td></td>\n                    <td></td>\n                    <td class="text-right">{$amount}</td>\n                    <td class="text-right">{number_format($total + $order->shipping_price + $order->payment_price,  0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n            </table>\n\n            <br/><br/>\n\n            <br/><br/>\n\n        </td>\n    </tr>\n</table>\n\n</body>\n</html>', 2),
(5, 'signup-member', 'Nový účet', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">        <title>{$settings[''site:title'']}: Nový účet</title>                <p>Dobrý den,</p>        Ověření účtu <a href="{$settings[''site:url:base'']}/sign/verify?user={$username}&amp;code={$activationCode}&amp;do=verifyForm-submit">            {$settings[''site:url:base'']}/sign/verify?user={$username}&amp;code={$activationCode}&amp;do=verify-check</a>        <br>        <br>        V případě, že máte jakékoliv otázky, neváhejte se nás zeptat na tomto e-mailu.', 3),
(6, 'signup-member-confirmbyadmin', 'Nový účet - bude potvrzeno', '<html>\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Nový účet</title>\r\n    </head>\r\n    <body>\r\n        <p>Dobrý den,</p>\r\n\r\n        Po ověření administrací {$settings[''site:title'']} Vám bude zaslána zpráva a Vy se můžete přihlásit.\r\n        <br />\r\n        <br />\r\n        V případě, že máte jakékoliv otázky, neváhejte se nás zeptat na tomto e-mailu.\r\n    </body>\r\n</html>', 12),
(7, 'signup-admin-confirm', 'Nový účet pro potvrzení', '<html>\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Nový účet {$username}, </title>\r\n    </head>\r\n    <body>\r\n        <p>Nový účet</p>\r\n\r\n        <div style="display: block; color: white; background-color: navy; min-width: 150px; height: 60px; padding: 5px;">\r\n            <a href="{$settings[''site:url:base'']}/sign/verify?user={$username}&code={$activationCode}&do=verifyForm-submit">\r\n                Ověřit účet\r\n            </a>\r\n        </div>\r\n        <br />\r\n        <br />\r\n        <table>\r\n            <tr>\r\n                <td>Uživatel</td>\r\n                <td>{$form->values->username}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>E-mail</td>\r\n                <td>{$form->values->email}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Jméno</td>\r\n                <td>{$form->values->name}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Ulice</td>\r\n                <td>{$form->values->street}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Město</td>\r\n                <td>{$form->values->city}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>PSČ</td>\r\n                <td>{$form->values->zip}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Společnost</td>\r\n                <td>{$form->values->company}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>Ič</td>\r\n                <td>{$form->values->vatin}</td>\r\n            </tr>\r\n            <tr>\r\n                <td>DIČ</td>\r\n                <td>{$form->values->vatid}</td>\r\n            </tr>\r\n        </table>\r\n\r\n        {if $g == 45646}\r\n            {if count($aresArr) > 0}\r\n                Výpis z ARESu:<br />\r\n                <br />\r\n                IČ: {$aresArr[''in'']}<br />\r\n                DIČ: {$aresArr[''tin'']}<br />\r\n                Název: {$aresArr[''company'']}<br />\r\n                Ulice: {$aresArr[''street'']}<br />\r\n                Město: {$aresArr[''city'']}<br />\r\n                PSČ: {$aresArr[''zip'']}<br />\r\n                Plátce DPH: {if $aresArr[''vat_pay'']}ano{else}ne{/if}<br />\r\n\r\n                Spis: {$aresArr[''file_number'']}<br />\r\n                Městský soud: {$aresArr[''court'']}\r\n            {/if}\r\n        {/if}\r\n    </body>\r\n</html>', 12),
(8, 'member-resend-login', 'Vytvoření nového hesla', '<html>\r\n<head>\r\n<title>{$settings[''site:title'']}: Vytvo?ení nového hesla</title>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">\r\n                    {$settings[''site:title'']}\r\n                </td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Bylo Vám vytvořeno nové heslo. Zde jsou údaje nutné k přihlášení\r\n                    <br /><br />\r\n                    uživatelské jméno: {$username}<br />\r\n                    heslo: {$password}<br />\r\n                    <br /><br />\r\n                    Přihlašte se: <a href="{$settings[''site:url:base'']}/sign/in">{$settings[''site:url:base'']}/sign/in</a>\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 4),
(9, 'member-new-email', 'Nový e-mail', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Nový e-mail</title>\r\n        <style>\r\n\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">{$settings[''site:title'']}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Your account was successfully created. You can now log in.\r\n                    <br /><br />\r\n                    user name: {$username}<br />\r\n                    password: {$password}<br />\r\n                    <br /><br />\r\n                    Log in: <a href="{$settings[''site:url:base'']}/admin">{$settings[''site:url:base'']}/admin</a>\r\n                </td>\r\n            </tr>\r\n        </table>\r\n\r\n    </body>\r\n</html>', 5),
(10, 'state-1', ' Objednávka  byla přijata', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Objednávka č. {$oid} byla přijata</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\r\n                    Objednávka č. {$oid} byla přijata\r\n                </td>\r\n            </tr>\r\n        </table>\r\n\r\n    </body>\r\n</html>', 6),
(11, 'state-2', 'Objednávka čeká na vyřízení', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Objednávka č. {$oid} byla potvrzena a čeká na vyřízení</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">{$settings["site:title"]}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Objednávka č. {$oid} čeká na vyřízení\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 7),
(12, 'state-3', 'Objednávka byla zaplacena a čeká na vyřízení', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings[''site:title'']}: Objednávka č. {$oid} byla zaplacena a čeká na vyřízení</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">{$settings["site:title"]}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Objednávka čeká na vyřízení\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 8),
(13, 'state-4', 'Objednávka byla expedována', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings["site:title"]}: Objednávka č. {$oid} byla expedována a míří k Vám.</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">{$settings["site:title"]}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Objednávka č. {$oid} byla expedována a míří k Vám.\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 9),
(14, 'state-5', 'Objednávka byla zrušena', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n    "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n    <head>\r\n        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n        <title>{$settings["site:title"]}: Objednávka č. {$oid} byla zrušena</title>\r\n        <style n:syntax="off">\r\n            * {font-family: Arial;}\r\n        </style>\r\n    </head>\r\n    <body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" \r\n          style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n        <table style="width: 800px; margin: 0 auto 0 auto;">\r\n            <tr>\r\n                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e; \r\n                    font-size: 1.82em; height: 80px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">{$settings["site:title"]}</td>\r\n            </tr>\r\n            <tr>\r\n                <td style="text-align: left;">\r\n                    <br /><br />\r\n                    Objednávka č. {$oid} byla zrušena\r\n                </td>\r\n            </tr>\r\n        </table>\r\n    </body>\r\n</html>', 10),
(15, 'lostpass-member', 'Informace o novém hesle', 'Na základě Vaší žádosti Vám posíláme odkaz na obnovení hesla.\r\n<br /><br />\r\nK vytvoření nového hesla klikněte na odkaz níže:\r\n<br />\r\n<a href="{$settings[''site:url:base'']}/sign/resetpass/?code={$code}&email={$email}">\r\n    {$settings[''site:url:base'']}/sign/resetpass/?code={$code}&email={$email}\r\n</a>\r\n<br /><br />\r\n<strong><a href="{$settings[''site:url:base'']}">{$settings[''site:title'']}</a></strong>\r\n', 11),
(16, 'lostpass-admin', 'Informace o novém hesle', 'Na základě Vaší žádosti Vám posíláme odkaz na obnovení hesla.\r\n<br /><br />\r\nK vytvoření nového hesla klikněte na odkaz níže:\r\n<br />\r\n<a href="{$settings[''site:url:base'']}/admin/sign/resetpass/?code={$code}&email={$email}">\r\n    {$settings[''site:url:base'']}/admin/sign/resetpass/?code={$code}&email={$email}\r\n</a>\r\n<br /><br />\r\n<strong><a href="{$settings[''site:url:base'']}">{$settings[''site:title'']}</a></strong>\r\n', 11);

CREATE TABLE `helpdesk_messages` (
  `id` int(11) NOT NULL,
  `message` text,
  `helpdesk_id` int(11) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `ipaddress` varchar(80) NOT NULL,
  `date_created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `helpdesk_templates` (
  `id` int(11) NOT NULL,
  `title` varchar(200) COLLATE NOT NULL,
  `document` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `helpdesk_templates` (`id`, `title`, `document`) VALUES
(1, 'Basic', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\r\n        "http://www.w3.org/TR/html4/loose.dtd">\r\n<html lang="cs">\r\n<head>\r\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n    <title>%TITLE%</title>\r\n    <style n:syntax="off">\r\n        * {\r\n            font-family: Arial;\r\n        }\r\n\r\n        .text-right {\r\n            text-align: right;\r\n        }\r\n    </style>\r\n</head>\r\n<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0"\r\n      style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\r\n\r\n<table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\r\n    <tr>\r\n        <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;\r\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\r\n{$settings[''site:title'']} - TEST\r\n        </td>\r\n    </tr>\r\n    <tr>\r\n        <td style="text-align: left;">\r\n%CONTENT%\r\n</td>\r\n    </tr>\r\n</table>\r\n\r\n</body>\r\n</html>'),
(2, 'Ink', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\r\n<html xmlns="http://www.w3.org/1999/xhtml">\r\n\r\n<head>\r\n  <!-- The character set should be utf-8 -->\r\n  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\r\n  <meta name="viewport" content="width=device-width">\r\n  <!-- Link to the email''s CSS, which will be inlined into the email -->\r\n<title>%TITLE%</title>\r\n\r\n</head>\r\n\r\n<body style="-moz-box-sizing: border-box; -ms-text-size-adjust: 100%; -webkit-box-sizing: border-box; -webkit-text-size-adjust: 100%; Margin: 0; box-sizing: border-box; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; min-width: 100%; padding: 0; text-align: left; width: 100% !important;">\r\n  <style>\r\n    @media only screen {\r\n      html {\r\n        min-height: 100%;\r\n        background: #f3f3f3;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      .small-float-center {\r\n        margin: 0 auto !important;\r\n        float: none !important;\r\n        text-align: center !important;\r\n      }\r\n      .small-text-center {\r\n        text-align: center !important;\r\n      }\r\n      .small-text-left {\r\n        text-align: left !important;\r\n      }\r\n      .small-text-right {\r\n        text-align: right !important;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      .hide-for-large {\r\n        display: block !important;\r\n        width: auto !important;\r\n        overflow: visible !important;\r\n        max-height: none !important;\r\n        font-size: inherit !important;\r\n        line-height: inherit !important;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      table.body table.container .hide-for-large,\r\n      table.body table.container .row.hide-for-large {\r\n        display: table !important;\r\n        width: 100% !important;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      table.body table.container .callout-inner.hide-for-large {\r\n        display: table-cell !important;\r\n        width: 100% !important;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      table.body table.container .show-for-large {\r\n        display: none !important;\r\n        width: 0;\r\n        mso-hide: all;\r\n        overflow: hidden;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      table.body img {\r\n        width: auto;\r\n        height: auto;\r\n      }\r\n      table.body center {\r\n        min-width: 0 !important;\r\n      }\r\n      table.body .container {\r\n        width: 95% !important;\r\n      }\r\n      table.body .columns,\r\n      table.body .column {\r\n        height: auto !important;\r\n        -moz-box-sizing: border-box;\r\n        -webkit-box-sizing: border-box;\r\n        box-sizing: border-box;\r\n        padding-left: 16px !important;\r\n        padding-right: 16px !important;\r\n      }\r\n      table.body .columns .column,\r\n      table.body .columns .columns,\r\n      table.body .column .column,\r\n      table.body .column .columns {\r\n        padding-left: 0 !important;\r\n        padding-right: 0 !important;\r\n      }\r\n      table.body .collapse .columns,\r\n      table.body .collapse .column {\r\n        padding-left: 0 !important;\r\n        padding-right: 0 !important;\r\n      }\r\n      td.small-1,\r\n      th.small-1 {\r\n        display: inline-block !important;\r\n        width: 8.33333% !important;\r\n      }\r\n      td.small-2,\r\n      th.small-2 {\r\n        display: inline-block !important;\r\n        width: 16.66667% !important;\r\n      }\r\n      td.small-3,\r\n      th.small-3 {\r\n        display: inline-block !important;\r\n        width: 25% !important;\r\n      }\r\n      td.small-4,\r\n      th.small-4 {\r\n        display: inline-block !important;\r\n        width: 33.33333% !important;\r\n      }\r\n      td.small-5,\r\n      th.small-5 {\r\n        display: inline-block !important;\r\n        width: 41.66667% !important;\r\n      }\r\n      td.small-6,\r\n      th.small-6 {\r\n        display: inline-block !important;\r\n        width: 50% !important;\r\n      }\r\n      td.small-7,\r\n      th.small-7 {\r\n        display: inline-block !important;\r\n        width: 58.33333% !important;\r\n      }\r\n      td.small-8,\r\n      th.small-8 {\r\n        display: inline-block !important;\r\n        width: 66.66667% !important;\r\n      }\r\n      td.small-9,\r\n      th.small-9 {\r\n        display: inline-block !important;\r\n        width: 75% !important;\r\n      }\r\n      td.small-10,\r\n      th.small-10 {\r\n        display: inline-block !important;\r\n        width: 83.33333% !important;\r\n      }\r\n      td.small-11,\r\n      th.small-11 {\r\n        display: inline-block !important;\r\n        width: 91.66667% !important;\r\n      }\r\n      td.small-12,\r\n      th.small-12 {\r\n        display: inline-block !important;\r\n        width: 100% !important;\r\n      }\r\n      .columns td.small-12,\r\n      .column td.small-12,\r\n      .columns th.small-12,\r\n      .column th.small-12 {\r\n        display: block !important;\r\n        width: 100% !important;\r\n      }\r\n      table.body td.small-offset-1,\r\n      table.body th.small-offset-1 {\r\n        margin-left: 8.33333% !important;\r\n        Margin-left: 8.33333% !important;\r\n      }\r\n      table.body td.small-offset-2,\r\n      table.body th.small-offset-2 {\r\n        margin-left: 16.66667% !important;\r\n        Margin-left: 16.66667% !important;\r\n      }\r\n      table.body td.small-offset-3,\r\n      table.body th.small-offset-3 {\r\n        margin-left: 25% !important;\r\n        Margin-left: 25% !important;\r\n      }\r\n      table.body td.small-offset-4,\r\n      table.body th.small-offset-4 {\r\n        margin-left: 33.33333% !important;\r\n        Margin-left: 33.33333% !important;\r\n      }\r\n      table.body td.small-offset-5,\r\n      table.body th.small-offset-5 {\r\n        margin-left: 41.66667% !important;\r\n        Margin-left: 41.66667% !important;\r\n      }\r\n      table.body td.small-offset-6,\r\n      table.body th.small-offset-6 {\r\n        margin-left: 50% !important;\r\n        Margin-left: 50% !important;\r\n      }\r\n      table.body td.small-offset-7,\r\n      table.body th.small-offset-7 {\r\n        margin-left: 58.33333% !important;\r\n        Margin-left: 58.33333% !important;\r\n      }\r\n      table.body td.small-offset-8,\r\n      table.body th.small-offset-8 {\r\n        margin-left: 66.66667% !important;\r\n        Margin-left: 66.66667% !important;\r\n      }\r\n      table.body td.small-offset-9,\r\n      table.body th.small-offset-9 {\r\n        margin-left: 75% !important;\r\n        Margin-left: 75% !important;\r\n      }\r\n      table.body td.small-offset-10,\r\n      table.body th.small-offset-10 {\r\n        margin-left: 83.33333% !important;\r\n        Margin-left: 83.33333% !important;\r\n      }\r\n      table.body td.small-offset-11,\r\n      table.body th.small-offset-11 {\r\n        margin-left: 91.66667% !important;\r\n        Margin-left: 91.66667% !important;\r\n      }\r\n      table.body table.columns td.expander,\r\n      table.body table.columns th.expander {\r\n        display: none !important;\r\n      }\r\n      table.body .right-text-pad,\r\n      table.body .text-pad-right {\r\n        padding-left: 10px !important;\r\n      }\r\n      table.body .left-text-pad,\r\n      table.body .text-pad-left {\r\n        padding-right: 10px !important;\r\n      }\r\n      table.menu {\r\n        width: 100% !important;\r\n      }\r\n      table.menu td,\r\n      table.menu th {\r\n        width: auto !important;\r\n        display: inline-block !important;\r\n      }\r\n      table.menu.vertical td,\r\n      table.menu.vertical th,\r\n      table.menu.small-vertical td,\r\n      table.menu.small-vertical th {\r\n        display: block !important;\r\n      }\r\n      table.menu[align="center"] {\r\n        width: auto !important;\r\n      }\r\n      table.button.small-expand,\r\n      table.button.small-expanded {\r\n        width: 100% !important;\r\n      }\r\n      table.button.small-expand table,\r\n      table.button.small-expanded table {\r\n        width: 100%;\r\n      }\r\n      table.button.small-expand table a,\r\n      table.button.small-expanded table a {\r\n        text-align: center !important;\r\n        width: 100% !important;\r\n        padding-left: 0 !important;\r\n        padding-right: 0 !important;\r\n      }\r\n      table.button.small-expand center,\r\n      table.button.small-expanded center {\r\n        min-width: 0;\r\n      }\r\n    }\r\n  </style>\r\n  <!-- Wrapper for the body of the email -->\r\n  <table class="body" data-made-with-foundation="" style="Margin: 0; background: #f3f3f3; border-collapse: collapse; border-spacing: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; height: 100%; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">\r\n    <tbody>\r\n      <tr style="padding: 0; text-align: left; vertical-align: top;">\r\n        <!-- The class, align, and <center> tag center the container -->\r\n        <td class="float-center" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; float: none; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; word-wrap: break-word;"\r\n          valign="top" align="center">\r\n          <center style="min-width: 580px; width: 100%;">\r\n %CONTENT%\r\n          </center>\r\n        </td>\r\n      </tr>\r\n    </tbody>\r\n  </table>\r\n\r\n</body>\r\n\r\n</html>');

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `code` varchar(8) NOT NULL,
  `title` varchar(40) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '1',
  `default` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `languages` (`id`, `code`, `title`, `used`, `default`) VALUES
(1, 'cs', 'čeština', 1, 1);

CREATE TABLE `logger` (
  `id` int(11) NOT NULL,
  `event` varchar(200) NOT NULL,
  `description` text,
  `users_id` int(11) DEFAULT NULL,
  `pages_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `event_types_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `name` varchar(140) NOT NULL,
  `file_type` tinyint(1) NOT NULL DEFAULT '0',
  `filesize` int(11) NOT NULL DEFAULT '0',
  `pages_id` int(11) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `description` text NOT NULL,
  `date_created` datetime NOT NULL,
  `detail_view` tinyint(1) NOT NULL DEFAULT '1',
  `sorted` int(11) NOT NULL,
  `main_file` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_czech_ci,
  `title` varchar(80) NOT NULL,
  `pages_id` int(11) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `menu` (`id`, `parent_id`, `description`, `title`, `pages_id`, `url`, `sorted`) VALUES
(1, NULL, 'Hlavní menu/Main menu', 'Top', NULL, '', 5),
(2, 1, NULL, 'Homepage', NULL, '/', 3);

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `title` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `document` text COLLATE utf8_czech_ci,
  `preview` text CHARACTER SET latin1,
  `pages_id` int(11) DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL,
  `public` int(11) DEFAULT '0',
  `metadesc` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `metakeys` varchar(150) CHARACTER SET latin1 DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_published` datetime DEFAULT NULL,
  `pages_types_id` int(11) DEFAULT '1',
  `pages_templates_id` int(11) DEFAULT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0',
  `editable` int(11) NOT NULL DEFAULT '1',
  `recommended` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `document`, `preview`, `pages_id`, `users_id`, `public`, `metadesc`, `metakeys`, `date_created`, `date_published`, `pages_types_id`, `pages_templates_id`, `sorted`, `editable`, `recommended`) VALUES
(1, '', 'Úvodní stránka', '', NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 4, 17, 1, 0),
(2, 'kontakt', 'Kontakt', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 9, 5, 19, 0, 0),
(3, 'blog', 'Blog', '', NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 2, 21, 0, 0),
(5, 'udalosti', 'Události', NULL, NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 9, 23, 0, 0),
(8, 'profil', 'Profil', NULL, NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 19, 25, 0, 0),
(9, 'profil-obrazek', 'Profil: Obrázek', NULL, '8', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 20, 27, 0, 0),
(10, 'profil-heslo', 'Profil: Změna hesla', NULL, '8', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 21, 29, 0, 0),
(11, 'profil-adresy', 'Profil: Adresy', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 18, 31, 0, 0),
(12, 'profil-adresa', 'Profil: Adresa', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 17, 33, 0, 0),
(13, 'uspesna-registrace', 'Úspěšná registrace', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 22, 35, 0, 0),
(14, 'prihlaseni', 'Přihlášení', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 23, 37, 0, 0),
(15, 'zapomenute-heslo', 'Zapomenuté heslo', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 24, 39, 0, 0),
(16, 'reset', 'Znovunastavení hesla', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 25, 41, 0, 0),
(17, 'registrace', 'Registrace uživatele', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 26, 43, 0, 0),
(18, 'verifikace', 'Ověření účtu', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 27, 45, 0, 0),
(19, 'kosik', 'Košík', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 7, 47, 0, 0),
(20, 'katalog', 'Katalog', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 8, 49, 0, 0),
(21, 'bonus', 'Bonus', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 11, 51, 0, 0),
(22, 'delivery', 'Poštovní a platební metoda', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 12, 53, 0, 0),
(23, 'summary', 'Shrnutí objednávky', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 14, 55, 0, 0),
(24, 'adresa-objednavky', 'Adresa objednávky', '', '1', 1, 1, 1, NULL, NULL, NULL, NULL, 9, 10, 57, 0, 0),
(25, 'order-success', 'Objednávka úspěšně zadána', '', '1', 1, 1, 1, NULL, NULL, NULL, NULL, 9, 13, 59, 0, 0),
(26, 'objednavky', 'Objednávky', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 15, 61, 0, 0),
(27, 'objednavky-detail', 'Detail objednávky', NULL, '1', 1, NULL, 1, NULL, NULL, NULL, NULL, 9, 16, 63, 0, 0);

CREATE TABLE `pages_related` (
  `id` int(11) NOT NULL,
  `pages_id` int(11) NOT NULL,
  `related_pages_id` int(11) DEFAULT NULL,
  `description` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `pages_templates` (
  `id` int(11) NOT NULL,
  `pages_types_id` int(11) DEFAULT NULL,
  `template` varchar(250) NOT NULL,
  `title` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages_templates` (`id`, `pages_types_id`, `template`, `title`) VALUES
(1, NULL, 'Front:Gallery:albumWithDescription', 'Album with description'),
(2, NULL, 'Front:Pages:blogList', 'Blog list'),
(3, NULL, 'Front:Pages:default', 'Simple page'),
(4, NULL, 'Front:Homepage:default', 'Homepage'),
(5, NULL, 'Front:Contact:default', 'Contact page'),
(6, NULL, 'Front:Pages:blogDetail', 'Blog detail'),
(7, 9, 'Front:Cart:default', 'Košík'),
(8, NULL, 'Front:Catalogue:default', 'Katalog'),
(9, NULL, 'Front:Events:default', 'Události'),
(10, NULL, 'Front:Order:address', 'Adresa vybrané objednávky'),
(11, NULL, 'Front:Order:bonus', 'Výběr bonusu při objednávce'),
(12, NULL, 'Front:Order:delivery', 'Objednávka - vyplění poštovného a platební metody'),
(13, NULL, 'Front:Order:success', 'Úspěšně dokončená objednávka'),
(14, NULL, 'Front:Order:summary', 'Souhrn objednávky'),
(15, NULL, 'Front:Orders:default', 'Přehled objednávek'),
(16, NULL, 'Front:Orders:detail', 'Detail objednávek'),
(17, NULL, 'Front:Profile:address', 'Adresa vybraného profilu'),
(18, NULL, 'Front:Profile:addresses', 'Přehled adres uživatele'),
(19, NULL, 'Front:Profile:default', 'Stránka proflu uživatele'),
(20, NULL, 'Front:Profile:image', 'Stránka s výběrem profilového obrázku'),
(21, NULL, 'Front:Profile:password', 'Změna hesla uživatele'),
(22, NULL, 'Front:Sign:ed', 'Úspěšná registrace'),
(23, NULL, 'Front:Sign:in', 'Přihlašovací stránka'),
(24, NULL, 'Front:Sign:lostpass', 'Zapomenuté heslo'),
(25, NULL, 'Front:Sign:resetpass', 'Vytvoření nového hesla'),
(26, NULL, 'Front:Sign:up', 'Registrace'),
(27, NULL, 'Front:Sign:verify', 'Ověření účtu');

CREATE TABLE `pages_types` (
  `id` int(11) NOT NULL,
  `content_type` varchar(40) NOT NULL,
  `presenter` varchar(40) NOT NULL,
  `action` varchar(40) NOT NULL,
  `prefix` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages_types` (`id`, `content_type`, `presenter`, `action`, `prefix`) VALUES
(1, 'Page', 'Front:Pages', 'default', ''),
(2, 'Blog', 'Front:Blog', 'detail', 'blog'),
(3, 'Event', 'Front:Events', 'detail', ''),
(4, 'Product', 'Front:Product', 'default', ''),
(5, 'Contacts', 'Front:Contacts', 'detail', ''),
(6, 'Galerie', 'Front:Media', 'album', ''),
(7, 'Product Category', 'Front:Catalogue', 'default', ''),
(8, 'Dokumenty', 'Front:Media', 'folder', ''),
(9, 'Template', '', 'default', ''),
(10, 'Blog categories', '', '', 'blog-categories');

CREATE TABLE `param` (
  `id` int(11) NOT NULL,
  `param` varchar(80) NOT NULL,
  `param_en` varchar(80) NOT NULL,
  `description` text,
  `prefix` varchar(40) DEFAULT NULL COMMENT 'Will be automatically filled before value',
  `suffix` varchar(40) DEFAULT NULL COMMENT 'Will be automatically filled after value',
  `preset` varchar(80) DEFAULT NULL COMMENT 'Value in preset will be autofilled',
  `ignore_front` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide in parametres in presentations',
  `ignore_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide in admin params view',
  `type_front` varchar(40) NOT NULL DEFAULT 'radio' COMMENT 'Display type of parametre form: select, radio etc.',
  `sorted` int(11) DEFAULT '0' COMMENT 'You can sort params',
  `block_class` varchar(60) DEFAULT '0' COMMENT 'Add classes for the parameter block',
  `replace_param` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `params` (
  `id` int(11) NOT NULL,
  `pages_id` int(11) NOT NULL,
  `param_id` int(11) NOT NULL,
  `paramvalue` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `settings_categories_id` int(11) DEFAULT NULL,
  `setkey` varchar(40) NOT NULL,
  `setvalue` varchar(120) NOT NULL,
  `description_cs` varchar(150) DEFAULT NULL,
  `description_en` varchar(150) DEFAULT NULL,
  `type` varchar(40) CHARACTER SET latin1 DEFAULT NULL,
  `admin_editable` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `settings`
--

INSERT INTO `settings` (`id`, `settings_categories_id`, `setkey`, `setvalue`, `description_cs`, `description_en`, `type`, `admin_editable`) VALUES
(1, 15, 'blog:short:showPreview', '1', 'Zobrazovat zkrácenou verzi článku v krátké verzi blogu.', 'Show preview of an article.', 'boolean', 1),
(2, 15, 'blog:short:showAuthor', '0', 'Zobrazovat autora', 'Show author (member) of the article.', 'boolean', 1),
(3, 15, 'blog:short:showImage', '0', 'Zobrazovat náhled obrázku', 'Show image thumbnail', 'boolean', 1),
(4, 15, 'blog:short:showDate', '0', 'Zobrazovat datum vydání', 'Show date published.', 'boolean', 1),
(5, 15, 'blog:preview:length', '350', 'Délka náhledového textu (0 znamená nezkrácený text)', 'Length of preview text (0 means complete text)', 'numeric', 1),
(6, 15, 'blog_fblike', '1', 'Přidá sdílecí tlačítko na Facebook, pokud je zadán FB účet', 'Adds sharing button for Facebook, if FB account specified', 'boolean', 1),
(7, 14, 'bonus:enabled', '0', 'Je aktivována odměna za výši nákupu', 'Is the reward for the amount of the purchase activated?', 'boolean', 1),
(8, 14, 'bonus:registeredUsersOnly', '1', 'Bonus pouze pro registrované uživatele', 'Bonus for registered users only', 'boolean', 1),
(9, 11, 'categories:adminEnabled', '0', 'Pokud není nastaveno, jen admin uvidí Categories', 'Only admin can see Categories if not set', 'boolean', 1),
(11, 11, 'categories:id:members', '5', 'Identikátor kategorie členové', 'Members category identifier', '', 1),
(12, 11, 'categories:id:contact', '2', 'Identikátor kategorie Kontakty', 'Contact category identifier', '', 1),
(14, 11, 'categories:id:media', '7', 'Identikátor kategorie Media', 'Media category identifier', '', 1),
(15, 11, 'categories:id:settings', '1', 'Identikátor kategorie Settings', 'Settings category identifier', '', 0),
(16, 11, 'categories:id:contactsBrands', '6', 'Identifikátor kategorie obchodních značek v kontaktech', 'Category identifier for brands in contacts', '', 1),
(17, 11, 'categories:id:contactsPickups', '16', 'Identifikátor kategorie míst k vyzvednutí', 'Category identifier for pickup places', '', 1),
(20, 11, 'categories:id:contactsNewsletter', '19', 'Identikátor kategorie Newsletter  v kontaktech', 'Contacts newsletter category identifier', '', 0),
(21, 16, 'contacts:email:hq', '', 'Vaše e-mailová adresa', 'Your e-mail address', NULL, 1),
(22, 16, 'contacts:email:techSupport', '', 'Technická podpora', 'Technical support', NULL, 1),
(23, 16, 'contacts:email:order', '', 'E-mail pro odesílání objednávky', 'E-mail for sending orders', NULL, 1),
(24, 16, 'contacts:smartForm:enabled', '0', 'Povolit doplňování SmartForm', 'SmartForm address autosuggest enabled', 'boolean', 1),
(25, 16, 'contacts:smartForm:clientId', '', 'Klientský kód SmartForm', 'SmartForm client code', '', 1),
(26, 16, 'contacts:residency:contacts_id', '14', 'Kontaktní informace o sídlu', 'Headquarters contact information', 'table:contacts;column:name', 1),
(27, 12, 'members:groups:enabled', '0', 'Vytvářet uživatelské skupiny', 'Create user groups', 'boolean', 1),
(28, 12, 'members:group:categoryId', '60', 'Identifikátor uživatelské kategorie', 'User category identifier', NULL, 1),
(29, 12, 'members:signup:contactEnabled', '1', 'Kontaktní informace v registračním formuláři', 'Contasct information in sign up form', 'boolean', 1),
(30, 12, 'members:signup:companyEnabled', '1', 'Firemní informace v registračním formuláři', 'Business information in sign up form', 'boolean', 1),
(31, 12, 'members:signup:confirmByAdmin', '1', 'Registrace uživatele musí být potvrzena administrátorem.', 'User Registration must be confirmed by the admin.', 'boolean', 1),
(32, 10, 'site:admin:adminBarEnabled', '1', 'Navigace administrace v prezentaci', 'Admin navigation in presentation', 'boolean', 1),
(33, 10, 'site:currency', '1', 'Měna stránky', 'Currency of the site', 'table:currencies;column:title', 1),
(34, 10, 'site:editor:type', 'summernote', 'Který editor bude vybrán. V současnosti Summernote nebo Ace', 'Which editor will be selected. Currently Summernote or CKEditor', '', 0),
(35, 10, 'site:vat:payee', '1', 'Plátce DPH', 'VAT Payee', 'boolean', 1),
(36, 10, 'site:title', '', 'Název stránky', 'Name of your page', NULL, 1),
(37, 10, 'site:url:base', '', 'URL adresa', 'URL address', NULL, 1),
(38, 10, 'maintenance_enabled', '0', 'Stránka v módu údržby', 'Site in maintenance mode', 'boolean', 1),
(39, 10, 'maintenance_message', 'Stránka je v módu údržby', 'Informace o údržbě pro návštěvníka', 'Maintenace information for visitor', NULL, 1),
(40, 10, 'site_ip_whitelist', '', 'Adresy povolené pro zobrazení obsahu. Oddělujte středníkem', 'IP addresses enabled to view the content. Separate by comma.', NULL, 1),
(41, 10, 'site_cookie_whitelist', '', 'Heslo v cookie nutné pro zobrazení obsahu. Vkládá se pomocí querystringu (secretx=pwd)', 'Password in a cookie required for viewing the content. Insert using qeurystring (secretx pwd =)', NULL, 1),
(42, 17, 'social:ga_code', '', 'Kód Google Analytics', 'Google Analytics code', NULL, 1),
(43, 17, 'social:fb:enabled', '1', 'Povoleno zobrazování Facebooku', 'Facebook enabled', 'boolean', 1),
(44, 17, 'social:fb:type', 'page', 'Typ: účet nebo stránka', 'Type: account or page', NULL, 1),
(45, 17, 'social:fb:url', '', 'Adresa stránky nebo účtu včetně http', 'Address of the page or account including http', NULL, 1),
(46, 17, 'social:twitter:account', '', 'Účet na Twitteru', 'Twitter account', NULL, 1),
(47, 13, 'store:enabled', '0', 'Provozujete obchod?', 'Is it store?', 'boolean', 1),
(48, 13, 'store:stock:hideEmpty', '1', 'Schovávat zboží, které není na skladu', 'Hide products not in stock', 'boolean', 1),
(49, 13, 'store:new:days', '14', 'Počet dní, kdy je produkt označován jako nový', 'Number of days when product is displayed as new', NULL, 1),
(50, 13, 'store_admin_dashboard', '1', 'Zobrazit dashboard obchodu na homepage administrace', 'View store dashboard in administration''s homepage', 'boolean', 1),
(51, 13, 'store:order:generateIdsOnOrderConfirm', '1', 'Vytvářet automaticky čísla objednávek už při dokončení objednávky', 'Generate purchase order identifiers immediately aftter checkout', 'boolean', 1),
(52, 13, 'store:currency:code', 'CZK', 'Kód měny', 'Currency code', NULL, 1),
(53, 13, 'store:currency:symbol', 'Kč', 'Symbol měny', 'Currency symbol', NULL, 1),
(54, 13, 'store:stock:amountDefault', '10', 'Přednastavení množství položek na skladu', 'Preset amount of items in stock', 'boolean', 0),
(55, 13, 'store:parametres:searchEnabled', '0', 'Parametrické hledání povoleno', 'Parametric search enabled', 'boolean', 1),
(56, 13, 'store:stock:shippingByWeight', '1', 'Cena poštovného podle hmotnosti', 'Shipping price calculated by weight', 'boolean', 1),
(57, 13, 'store:order:guestOrderEnabled', '1', 'Povolit objednání neregistrovanému uživateli', 'Unregistered user order enabled', 'boolean', 1),
(58, 13, 'store:order:isVatIncluded', '1', 'Je DPH připočítáváno nebo odpočítáváno (1 je připočítáváno)', 'Is VAT included (1 means included)', 'boolean', 0),
(59, 13, 'store:stock:deductStock', '0', 'Odečítat zboží po jeho koupi', 'Subtract the stock after purchase', 'boolean', 1),
(60, 20, 'appearance:paths:logo', 'logo.png', 'Obrázek loga ve vysokém rozlišení a barvě', 'Image of logo in high resolution and color', 'local_path', 0),
(61, 20, 'appearance:paths:favicon:ico', 'favicon.ico', 'Favicon s příponou ico', 'Favicon with ico suffix', 'local_path', 0),
(62, 21, 'appearance:carousel:directions', '0', 'Zobrazit indikátory (levá a pravá šipka)', 'Show indicators (left and right arrow)', 'boolean', 1),
(63, 21, 'appearance:carousel:indicators', '0', 'Zobrazit menu', 'Show menu', 'boolean', 1),
(64, 12, 'members_username_as_email', '0', 'Použít e-mail jako uživatelské jméno', 'Use e-mail as username', 'boolean', 1),
(65, 12, 'members_signup_message', '0', 'Možnost napsat zprávu při registraci', 'Option to write message in signup', 'boolean', 1),
(66, 12, 'members_signup_conditions_agree', '0', 'Souhlasit s podmínkami zaškrtávací tlačítko', 'Agree with conditions box', 'boolean', 1),
(67, 12, 'members_signup_conditions_link', '', 'Odkaz na soubor nebo stránku s podmínkami', 'Link to a file or page with terms and conditions', NULL, 1);

CREATE TABLE `settings_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text,
  `title` varchar(80) NOT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `settings_categories` (`id`, `parent_id`, `description`, `title`, `sorted`) VALUES
(10, 1, NULL, 'Základní nastavení', 146),
(11, 1, NULL, 'Kategorie', 149),
(12, 1, NULL, 'Členové', 148),
(13, 1, NULL, 'Obchod', 147),
(14, 13, NULL, 'Bonus', 167),
(15, 1, NULL, 'Blog', 154),
(16, 1, NULL, 'Kontakty', 166),
(17, 1, NULL, 'Služby', 176),
(20, 1, NULL, 'Vzhled', 154),
(21, 20, NULL, 'Carousel', 155),
(22, 20, NULL, 'Události', 157);

CREATE TABLE `snippets` (
  `id` int(11) NOT NULL,
  `keyword` varchar(80) NOT NULL,
  `content` text,
  `pages_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `snippets` (`id`, `keyword`, `content`, `pages_id`) VALUES
(1, 'Zkouška inline editace', 'Toto je zkouška inlineghjghjghjeditace <strong>panoramatu a dalších informací<br></strong>', 1);

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` char(40) CHARACTER SET latin1 NOT NULL,
  `users_categories_id` int(11) DEFAULT NULL,
  `email` char(80) CHARACTER SET latin1 NOT NULL,
  `sex` int(11) NOT NULL DEFAULT '0',
  `name` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `password` char(60) CHARACTER SET latin1 NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_visited` datetime DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT '0',
  `activation` char(40) CHARACTER SET latin1 DEFAULT NULL,
  `newsletter` int(11) DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '1',
  `users_roles_id` int(11) DEFAULT '0',
  `login_error` int(11) NOT NULL DEFAULT '0',
  `login_success` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users` (`id`, `username`, `users_categories_id`, `uid`, `email`, `sex`, `name`, `password`, `date_created`, `date_visited`, `state`, `activation`, `newsletter`, `type`, `users_roles_id`, `login_error`, `login_success`) VALUES
(1, 'admin', NULL, '000001', '', 2, '', '$2y$10$DLhMCsYpbB.xHJ501e.xMOvhneiT1U6YypGAcOna/V2kzIGZOwxla', NULL, '2017-02-16 15:44:13', 1, NULL, 1, 1, 1, 0, 3);

CREATE TABLE `users_categories` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users_categories` (`id`, `title`) VALUES
(1, 'Hlavní skupina');

CREATE TABLE `users_roles` (
  `id` int(11) NOT NULL,
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
  `pages_document` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users_roles` (`id`, `title`, `admin_access`, `appearance_images`, `helpdesk_edit`, `settings_display`, `settings_edit`, `members_display`, `members_edit`, `members_create`, `members_delete`, `pages_edit`, `pages_document`) VALUES
(1, 'Admin', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(2, 'Super User', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(3, 'Editor', 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1),
(4, 'Site User', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

ALTER TABLE `addons`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `carousel`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `categories_id` (`contacts_categories_id`),
  ADD KEY `countries_id` (`countries_id`),
  ADD KEY `pages_id` (`pages_id`);

ALTER TABLE `contacts_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `contacts_communication`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_id` (`contacts_id`);

ALTER TABLE `contacts_openinghours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_id` (`contacts_id`);

ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pages_id` (`pages_id`),
  ADD KEY `contacts_id` (`contacts_id`);

ALTER TABLE `events_signed`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_id` (`events_id`);

ALTER TABLE `helpdesk`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `helpdesk_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `helpdesk_id` (`helpdesk_id`),
  ADD KEY `helpdesk_templates_id` (`helpdesk_templates_id`);

ALTER TABLE `helpdesk_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_id` (`contacts_id`),
  ADD KEY `helpdesk_id` (`helpdesk_id`),
  ADD KEY `users_id` (`users_id`);
  
ALTER TABLE `helpdesk_templates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `albums_id` (`pages_id`);

ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pages_id` (`pages_id`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `pages_id` (`pages_id`),
  ADD KEY `content_type` (`pages_types_id`),
  ADD KEY `pages_ibfk_7` (`pages_templates_id`);

ALTER TABLE `pages_related`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`pages_id`,`related_pages_id`),
  ADD KEY `related_store_id` (`related_pages_id`),
  ADD KEY `blog_id` (`pages_id`),
  ADD KEY `related_blog_id` (`related_pages_id`);

ALTER TABLE `pages_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pages_types_id` (`pages_types_id`);

ALTER TABLE `pages_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `param`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `params`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`pages_id`),
  ADD KEY `store_param_id` (`param_id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_id` (`settings_categories_id`);

ALTER TABLE `settings_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `snippets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pages_id` (`pages_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_id` (`users_categories_id`),
  ADD KEY `users_roles_id` (`users_roles_id`);

ALTER TABLE `users_categories`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users_roles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `addons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

ALTER TABLE `carousel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contacts_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

ALTER TABLE `contacts_communication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contacts_openinghours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `events_signed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `helpdesk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `helpdesk_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `helpdesk_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

ALTER TABLE `pages_related`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pages_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

ALTER TABLE `pages_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `param`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `params`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

ALTER TABLE `settings_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `snippets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_4` FOREIGN KEY (`countries_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `contacts_ibfk_5` FOREIGN KEY (`contacts_categories_id`) REFERENCES `contacts_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_6` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `contacts_openinghours`
  ADD CONSTRAINT `contacts_openinghours_ibfk_1` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `events_signed`
  ADD CONSTRAINT `events_signed_ibfk_1` FOREIGN KEY (`events_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `helpdesk_emails`
  ADD CONSTRAINT `helpdesk_emails_ibfk_1` FOREIGN KEY (`helpdesk_id`) REFERENCES `helpdesk` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `helpdesk_emails_ibfk_2` FOREIGN KEY (`helpdesk_templates_id`) REFERENCES `helpdesk_templates` (`id`);

ALTER TABLE `helpdesk_messages`
  ADD CONSTRAINT `helpdesk_messages_ibfk_1` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `helpdesk_messages_ibfk_3` FOREIGN KEY (`helpdesk_id`) REFERENCES `helpdesk` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `helpdesk_messages_ibfk_4` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_4` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menu_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_4` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_ibfk_5` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_ibfk_6` FOREIGN KEY (`pages_types_id`) REFERENCES `pages_types` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_ibfk_7` FOREIGN KEY (`pages_templates_id`) REFERENCES `pages_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pages_related`
  ADD CONSTRAINT `pages_related_ibfk_3` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_related_ibfk_4` FOREIGN KEY (`related_pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pages_templates`
  ADD CONSTRAINT `pages_templates_ibfk_1` FOREIGN KEY (`pages_types_id`) REFERENCES `pages_types` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `params`
  ADD CONSTRAINT `params_ibfk_3` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `params_ibfk_4` FOREIGN KEY (`param_id`) REFERENCES `param` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`settings_categories_id`) REFERENCES `settings_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `snippets`
  ADD CONSTRAINT `snippets_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`users_categories_id`) REFERENCES `users_categories` (`id`);