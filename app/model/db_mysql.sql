SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `blacklist` (
  `id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL
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

CREATE TABLE `board` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `author` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `subject` text COLLATE utf8_czech_ci NOT NULL,
  `body` text COLLATE utf8_czech_ci,
  `date_created` datetime DEFAULT NULL,
  `show` smallint(6) NOT NULL DEFAULT '0',
  `ipaddress` varchar(32) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `carousel` (
  `id` int(11) NOT NULL,
  `title` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `uri` varchar(250) COLLATE utf8_czech_ci NOT NULL,
  `image` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `sorted` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `contacts_categories_id` int(11) DEFAULT NULL,
  `pages_id` int(11) DEFAULT NULL,
  `users_id` int(11) DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `post` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `notes` text COLLATE utf8_czech_ci,
  `name` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `company` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `street` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `city` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
  `countries_id` int(11) DEFAULT '1',
  `email` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `phone` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `vatin` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
  `vatid` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
  `banking_account` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `contacts_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `contacts_categories` (`id`, `parent_id`, `description`, `title`, `sorted`) VALUES
                                                                                               (1, NULL, '', 'Členové', 60),
                                                                                               (2, NULL, NULL, 'Kontakty na stránce', 45),
                                                                                               (3, NULL, NULL, 'Newsletter', 153),
                                                                                               (4, NULL, NULL, 'Místa pro události', 4),
                                                                                               (5, NULL, NULL, 'Proč další', 5);

CREATE TABLE `contacts_communication` (
  `id` int(11) NOT NULL,
  `contacts_id` int(11) NOT NULL,
  `communication_type` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `communication_value` varchar(250) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `contacts_openinghours` (
  `id` int(11) NOT NULL,
  `day` smallint(6) NOT NULL,
  `hourstext` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `contacts_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `title_cs` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `title_en` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `show` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `countries` (`id`, `title_cs`, `title_en`, `show`) VALUES
                                                                      (1, 'Česká Republika', 'Czech Republic', 1),
                                                                      (2, 'Slovensko', 'Slovakia', 0);

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `title` varchar(60) COLLATE utf8_czech_ci DEFAULT NULL,
  `code` varchar(8) COLLATE utf8_czech_ci DEFAULT NULL,
  `symbol` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
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
  `name` varchar(150) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_czech_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8_czech_ci DEFAULT NULL,
  `note` text COLLATE utf8_czech_ci,
  `events_id` int(11) NOT NULL,
  `ipaddress` varchar(15) COLLATE utf8_czech_ci NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `helpdesk` (
  `id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  `blacklist` int(11) NOT NULL DEFAULT '0',
  `email` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL,
  `subject` varchar(250) COLLATE utf8_czech_ci NOT NULL,
  `body` text COLLATE utf8_czech_ci,
  `helpdesk_templates_id` int(11) DEFAULT NULL,
  `log` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `helpdesk` (`id`, `title`, `description`, `blacklist`, `email`, `subject`, `body`, `helpdesk_templates_id`, `log`) VALUES
                                                                                                                                      (1, 'Kontaktní formulář', 'Tento formulář slouží Vašim zákazníkům, aby vás mohli kontaktovat ohledně jejich otázek nebo potávek.', 1, 'caloris@caloris.cz', 'Poptávka', '<br>                    {$name}<br>                    {$phone}<br>                    {$email}<br>                    <br><br>                    {$message}<br>                    {$time}<br>                    {$ipaddress}                </td>            </tr>        </tbody></table>', 1, 0),
                                                                                                                                      (2, 'Kontaktní formulář - admin', 'Poptávkový formulář - verze pro administraci', 1, NULL, 'Poptávka od návštěvníka', '                   Děkujeme za Vaši zprávu. Budeme Vás brzy kontaktovat.                    <br>                    {$name}<br>                    {$phone}<br>                    {$email}<br>                    <br><br>                    {$message}<br>                </td>            </tr>        </tbody></table>    ', 1, 0),
                                                                                                                                      (3, 'Registrační formulář', 'Formulář pro přihlášení jako člen', 0, NULL, 'Nový účet', '<p>Dobrý den,</p>        Ověření účtu <a href=\"{$settings[\'site:url:base\']}/sign/verify?user={$username}&amp;code={$activationCode}&amp;do=verifyForm-submit\">            {$settings[\'site:url:base\']}/sign/verify?user={$username}&amp;code={$activationCode}&amp;do=verify-check</a>        <br>        <br>        V případě, že máte jakékoliv otázky, neváhejte se nás zeptat na tomto e-mailu.', 1, 0),
                                                                                                                                      (4, 'Odeslání hesla pro existujícího uživatele', 'Tímto formulářem bude existujícímu uživateli odesláno nové heslo. Pozor. Pokaždé, když formulář odešlete, bude heslo změněno a odesláno.', 0, NULL, 'Vytvoření nového hesla', '                    <br /><br />\r\n                    Bylo Vám vytvořeno nové heslo. Zde jsou údaje nutné k přihlášení\r\n                    <br /><br />\r\n                    uživatelské jméno: {$username}<br />\r\n                    heslo: {$password}<br />\r\n                    <br /><br />\r\n                    Přihlašte se: <a href=\"{$settings[\'site:url:base\']}/sign/in\">{$settings[\'site:url:base\']}/sign/in</a>', 1, 0),
                                                                                                                                      (5, 'Vytvoření účtu z administrace', 'Vytvoření nového uživatelského účtu z administrace', 0, NULL, 'Nový e-mail', '                    Your account was successfully created. You can now log in.\r\n                    <br /><br />\r\n                    user name: {$username}<br />\r\n                    password: {$password}<br />\r\n                    <br /><br />\r\n                    Log in: <a href=\"{$settings[\'site:url:base\']}/admin\">{$settings[\'site:url:base\']}/admin</a>', 1, 0),
                                                                                                                                      (11, 'Zapomenuté heslo', 'Odeslání zapomenutého hesla', 0, NULL, 'Informace o novém hesle', 'Na základě Vaší žádosti Vám posíláme odkaz na obnovení hesla.\r\n<br /><br />\r\nK vytvoření nového hesla klikněte na odkaz níže:\r\n<br />\r\n<a href=\"{$settings[\'site:url:base\']}/sign/resetpass/?code={$code}&email={$email}\">\r\n    {$settings[\'site:url:base\']}/sign/resetpass/?code={$code}&email={$email}\r\n</a>\r\n<br /><br />\r\n<strong><a href=\"{$settings[\'site:url:base\']}\">{$settings[\'site:title\']}</a></strong>\r\n', 1, 0),
                                                                                                                                      (12, 'Registrační formulář ověřený administrátorem', 'Formulář pro registraci, která bude ověřena správou stránek', 0, NULL, 'Nový účet - bude potvrzeno', '<p>Dobrý den,</p>\r\n\r\n        Po ověření administrací {$settings[\'site:title\']} Vám bude zaslána zpráva a Vy se můžete přihlásit.\r\n        <br />\r\n        <br />\r\n        V případě, že máte jakékoliv otázky, neváhejte se nás zeptat na tomto e-mailu.', 1, 0),
                                                                                                                                      (13, 'Registrační formulář ověřený administrátorem - potvrzovací část', 'Formulář pro registraci, která bude ověřena správou stránek', 0, NULL, 'Nový účet pro potvrzení', '<p>Dobrý den,</p>\r\n\r\n        Po ověření administrací {$settings[\'site:title\']} Vám bude zaslána zpráva a Vy se můžete přihlásit.\r\n        <br />\r\n        <br />\r\n        V případě, že máte jakékoliv otázky, neváhejte se nás zeptat na tomto e-mailu.', 1, 0),
                                                                                                                                      (14, 'Zapomenuté heslo: administrace', 'Odeslání zapomenutého hesla pro členy administrace', 0, NULL, 'Informace o novém hesle', 'Na základě Vaší žádosti Vám posíláme odkaz na obnovení hesla.\r\n<br /><br />\r\nK vytvoření nového hesla klikněte na odkaz níže:\r\n<br />\r\n<a href=\"{$settings[\'site:url:base\']}/admin/sign/resetpass/?code={$code}&email={$email}\">\r\n    {$settings[\'site:url:base\']}/admin/sign/resetpass/?code={$code}&email={$email}\r\n</a>\r\n<br /><br />\r\n<strong><a href=\"{$settings[\'site:url:base\']}\">{$settings[\'site:title\']}</a></strong>\r\n', 1, 0);

CREATE TABLE `helpdesk_messages` (
  `id` int(11) NOT NULL,
  `message` text COLLATE utf8_czech_ci,
  `helpdesk_id` int(11) DEFAULT NULL,
  `email` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `ipaddress` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `helpdesk_templates` (
  `id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `document` text COLLATE utf8_czech_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `helpdesk_templates` (`id`, `title`, `document`) VALUES
                                                                    (1, 'Basic', '<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\\r\\n        \"http://www.w3.org/TR/html4/loose.dtd\">\\r\\n<html lang=\"cs\">\\r\\n<head>\\r\\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\\r\\n    <title>%TITLE%</title>\\r\\n    <style n:syntax=\"off\">\\r\\n        * {\\r\\n            font-family: Arial;\\r\\n        }\\r\\n\\r\\n        .text-right {\\r\\n            text-align: right;\\r\\n        }\\r\\n    </style>\\r\\n</head>\\r\\n<body bgcolor=\"#ffffff\" topmargin=\"0\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\"\\r\\n      style=\"width:100% !important; text-align: center; font-family: Arial sans-serif;\">\\r\\n\\r\\n<table style=\"width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;\">\\r\\n    <tr>\\r\\n        <td colspan=\"2\" style=\"color: white; vertical-align: middle; background-color: #8e8e8e;\\r\\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;\">\\r\\n{$settings[\'site:title\']} - TEST\\r\\n        </td>\\r\\n    </tr>\\r\\n    <tr>\\r\\n        <td style=\"text-align: left;\">\\r\\n%CONTENT%\\r\\n</td>\\r\\n    </tr>\\r\\n</table>\\r\\n\\r\\n</body>\\r\\n</html>'),
                                                                    (2, 'Ink', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\r\n\r\n<head>\r\n  <!-- The character set should be utf-8 -->\r\n  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n  <meta name=\"viewport\" content=\"width=device-width\">\r\n  <!-- Link to the email\'s CSS, which will be inlined into the email -->\r\n<title>%TITLE%</title>\r\n\r\n</head>\r\n\r\n<body style=\"-moz-box-sizing: border-box; -ms-text-size-adjust: 100%; -webkit-box-sizing: border-box; -webkit-text-size-adjust: 100%; Margin: 0; box-sizing: border-box; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; min-width: 100%; padding: 0; text-align: left; width: 100% !important;\">\r\n  <style>\r\n    @media only screen {\r\n      html {\r\n        min-height: 100%;\r\n        background: #f3f3f3;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      .small-float-center {\r\n        margin: 0 auto !important;\r\n        float: none !important;\r\n        text-align: center !important;\r\n      }\r\n      .small-text-center {\r\n        text-align: center !important;\r\n      }\r\n      .small-text-left {\r\n        text-align: left !important;\r\n      }\r\n      .small-text-right {\r\n        text-align: right !important;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      .hide-for-large {\r\n        display: block !important;\r\n        width: auto !important;\r\n        overflow: visible !important;\r\n        max-height: none !important;\r\n        font-size: inherit !important;\r\n        line-height: inherit !important;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      table.body table.container .hide-for-large,\r\n      table.body table.container .row.hide-for-large {\r\n        display: table !important;\r\n        width: 100% !important;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      table.body table.container .callout-inner.hide-for-large {\r\n        display: table-cell !important;\r\n        width: 100% !important;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      table.body table.container .show-for-large {\r\n        display: none !important;\r\n        width: 0;\r\n        mso-hide: all;\r\n        overflow: hidden;\r\n      }\r\n    }\r\n    \r\n    @media only screen and (max-width: 596px) {\r\n      table.body img {\r\n        width: auto;\r\n        height: auto;\r\n      }\r\n      table.body center {\r\n        min-width: 0 !important;\r\n      }\r\n      table.body .container {\r\n        width: 95% !important;\r\n      }\r\n      table.body .columns,\r\n      table.body .column {\r\n        height: auto !important;\r\n        -moz-box-sizing: border-box;\r\n        -webkit-box-sizing: border-box;\r\n        box-sizing: border-box;\r\n        padding-left: 16px !important;\r\n        padding-right: 16px !important;\r\n      }\r\n      table.body .columns .column,\r\n      table.body .columns .columns,\r\n      table.body .column .column,\r\n      table.body .column .columns {\r\n        padding-left: 0 !important;\r\n        padding-right: 0 !important;\r\n      }\r\n      table.body .collapse .columns,\r\n      table.body .collapse .column {\r\n        padding-left: 0 !important;\r\n        padding-right: 0 !important;\r\n      }\r\n      td.small-1,\r\n      th.small-1 {\r\n        display: inline-block !important;\r\n        width: 8.33333% !important;\r\n      }\r\n      td.small-2,\r\n      th.small-2 {\r\n        display: inline-block !important;\r\n        width: 16.66667% !important;\r\n      }\r\n      td.small-3,\r\n      th.small-3 {\r\n        display: inline-block !important;\r\n        width: 25% !important;\r\n      }\r\n      td.small-4,\r\n      th.small-4 {\r\n        display: inline-block !important;\r\n        width: 33.33333% !important;\r\n      }\r\n      td.small-5,\r\n      th.small-5 {\r\n        display: inline-block !important;\r\n        width: 41.66667% !important;\r\n      }\r\n      td.small-6,\r\n      th.small-6 {\r\n        display: inline-block !important;\r\n        width: 50% !important;\r\n      }\r\n      td.small-7,\r\n      th.small-7 {\r\n        display: inline-block !important;\r\n        width: 58.33333% !important;\r\n      }\r\n      td.small-8,\r\n      th.small-8 {\r\n        display: inline-block !important;\r\n        width: 66.66667% !important;\r\n      }\r\n      td.small-9,\r\n      th.small-9 {\r\n        display: inline-block !important;\r\n        width: 75% !important;\r\n      }\r\n      td.small-10,\r\n      th.small-10 {\r\n        display: inline-block !important;\r\n        width: 83.33333% !important;\r\n      }\r\n      td.small-11,\r\n      th.small-11 {\r\n        display: inline-block !important;\r\n        width: 91.66667% !important;\r\n      }\r\n      td.small-12,\r\n      th.small-12 {\r\n        display: inline-block !important;\r\n        width: 100% !important;\r\n      }\r\n      .columns td.small-12,\r\n      .column td.small-12,\r\n      .columns th.small-12,\r\n      .column th.small-12 {\r\n        display: block !important;\r\n        width: 100% !important;\r\n      }\r\n      table.body td.small-offset-1,\r\n      table.body th.small-offset-1 {\r\n        margin-left: 8.33333% !important;\r\n        Margin-left: 8.33333% !important;\r\n      }\r\n      table.body td.small-offset-2,\r\n      table.body th.small-offset-2 {\r\n        margin-left: 16.66667% !important;\r\n        Margin-left: 16.66667% !important;\r\n      }\r\n      table.body td.small-offset-3,\r\n      table.body th.small-offset-3 {\r\n        margin-left: 25% !important;\r\n        Margin-left: 25% !important;\r\n      }\r\n      table.body td.small-offset-4,\r\n      table.body th.small-offset-4 {\r\n        margin-left: 33.33333% !important;\r\n        Margin-left: 33.33333% !important;\r\n      }\r\n      table.body td.small-offset-5,\r\n      table.body th.small-offset-5 {\r\n        margin-left: 41.66667% !important;\r\n        Margin-left: 41.66667% !important;\r\n      }\r\n      table.body td.small-offset-6,\r\n      table.body th.small-offset-6 {\r\n        margin-left: 50% !important;\r\n        Margin-left: 50% !important;\r\n      }\r\n      table.body td.small-offset-7,\r\n      table.body th.small-offset-7 {\r\n        margin-left: 58.33333% !important;\r\n        Margin-left: 58.33333% !important;\r\n      }\r\n      table.body td.small-offset-8,\r\n      table.body th.small-offset-8 {\r\n        margin-left: 66.66667% !important;\r\n        Margin-left: 66.66667% !important;\r\n      }\r\n      table.body td.small-offset-9,\r\n      table.body th.small-offset-9 {\r\n        margin-left: 75% !important;\r\n        Margin-left: 75% !important;\r\n      }\r\n      table.body td.small-offset-10,\r\n      table.body th.small-offset-10 {\r\n        margin-left: 83.33333% !important;\r\n        Margin-left: 83.33333% !important;\r\n      }\r\n      table.body td.small-offset-11,\r\n      table.body th.small-offset-11 {\r\n        margin-left: 91.66667% !important;\r\n        Margin-left: 91.66667% !important;\r\n      }\r\n      table.body table.columns td.expander,\r\n      table.body table.columns th.expander {\r\n        display: none !important;\r\n      }\r\n      table.body .right-text-pad,\r\n      table.body .text-pad-right {\r\n        padding-left: 10px !important;\r\n      }\r\n      table.body .left-text-pad,\r\n      table.body .text-pad-left {\r\n        padding-right: 10px !important;\r\n      }\r\n      table.menu {\r\n        width: 100% !important;\r\n      }\r\n      table.menu td,\r\n      table.menu th {\r\n        width: auto !important;\r\n        display: inline-block !important;\r\n      }\r\n      table.menu.vertical td,\r\n      table.menu.vertical th,\r\n      table.menu.small-vertical td,\r\n      table.menu.small-vertical th {\r\n        display: block !important;\r\n      }\r\n      table.menu[align=\"center\"] {\r\n        width: auto !important;\r\n      }\r\n      table.button.small-expand,\r\n      table.button.small-expanded {\r\n        width: 100% !important;\r\n      }\r\n      table.button.small-expand table,\r\n      table.button.small-expanded table {\r\n        width: 100%;\r\n      }\r\n      table.button.small-expand table a,\r\n      table.button.small-expanded table a {\r\n        text-align: center !important;\r\n        width: 100% !important;\r\n        padding-left: 0 !important;\r\n        padding-right: 0 !important;\r\n      }\r\n      table.button.small-expand center,\r\n      table.button.small-expanded center {\r\n        min-width: 0;\r\n      }\r\n    }\r\n  </style>\r\n  <!-- Wrapper for the body of the email -->\r\n  <table class=\"body\" data-made-with-foundation=\"\" style=\"Margin: 0; background: #f3f3f3; border-collapse: collapse; border-spacing: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; height: 100%; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;\">\r\n    <tbody>\r\n      <tr style=\"padding: 0; text-align: left; vertical-align: top;\">\r\n        <!-- The class, align, and <center> tag center the container -->\r\n        <td class=\"float-center\" style=\"-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; float: none; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; word-wrap: break-word;\"\r\n          valign=\"top\" align=\"center\">\r\n          <center style=\"min-width: 580px; width: 100%;\">\r\n %CONTENT%\r\n          </center>\r\n        </td>\r\n      </tr>\r\n    </tbody>\r\n  </table>\r\n\r\n</body>\r\n\r\n</html>');

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `code` varchar(8) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '1',
  `default` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `languages` (`id`, `code`, `title`, `used`, `default`) VALUES
                                                                          (1, 'cs', 'čeština', 1, 1),
                                                                          (2, 'en', 'English', 1, NULL);

CREATE TABLE `lang_keys` (
  `id` int(11) NOT NULL,
  `lang_list_id` int(11) DEFAULT NULL,
  `directory` varchar(50) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `path` varchar(100) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `value_cs` text CHARACTER SET utf8 COLLATE utf8_czech_ci,
  `value_en` text CHARACTER SET utf8 COLLATE utf8_czech_ci
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `lang_keys` (`id`, `lang_list_id`, `directory`, `path`, `value_cs`, `value_en`) VALUES
                                                                                                   (1, 1, 'navigation', 'categories', 'Kategorie', 'Categories'),
                                                                                                   (2, 1, 'navigation', 'catalogue', 'Katalog', 'Catalogue'),
                                                                                                   (3, 1, 'navigation', 'files', 'Soubory', 'Files'),
                                                                                                   (4, 1, 'navigation', 'homepage', 'Homepage', 'Homepage'),
                                                                                                   (5, 1, 'navigation', 'logout', 'Odhlásit', 'Log out'),
                                                                                                   (6, 1, 'navigation', 'members', 'Členové', 'Members'),
                                                                                                   (7, 1, 'navigation', 'pages', 'Stránky', 'Pages'),
                                                                                                   (8, 1, 'navigation', 'myprofile', 'Můj profil', 'My profile'),
                                                                                                   (9, 1, 'homepage', 'welcome', 'Vítejte', 'Welcome'),
                                                                                                   (10, 1, 'catalogue', 'deletereallymessage', 'Opravdu smazat tento parametr?', 'Do you really want to delete this parameter?'),
                                                                                                   (11, 1, 'catalogue', 'ListOfBrands', 'Seznam značek', 'List of brands'),
                                                                                                   (12, 1, 'catalogue', 'ProductCatalogue', 'Katalog výrobků', 'Product catalogue'),
                                                                                                   (13, 1, 'catalogue', 'ProductGallery', 'Galerie výrobku', 'Product gallery'),
                                                                                                   (14, 1, 'categories', 'NothingRelated', 'Nemá propojenou kategorii', 'Nothing related'),
                                                                                                   (15, 1, 'categories', 'PageSelectedManually', 'Stránka zvolena manuálně', 'Page selected manually'),
                                                                                                   (16, 1, 'categories', 'RelatedCategory', 'Navazující kategorie', 'Related category'),
                                                                                                   (17, 1, 'categories', 'SelectPage', 'Vybrat stránku', 'Select page'),
                                                                                                   (18, 1, 'pages', 'name', 'Název', 'Name'),
                                                                                                   (19, 1, 'files', 'choosefile', 'Vyberte soubor', 'Choose file'),
                                                                                                   (20, 1, 'files', 'filemanager', 'Souborový manažer', 'File Manager'),
                                                                                                   (21, 1, 'files', 'filename', 'Název souboru', 'File name'),
                                                                                                   (22, 1, 'files', 'listoffiles', 'Seznam souborů', 'List of files'),
                                                                                                   (23, 1, 'files', 'sizeinkb', 'Velikost (in kB)', 'Size (in kB)'),
                                                                                                   (24, 1, 'files', 'thumbnail', 'Náhled', 'Thumbnail'),
                                                                                                   (25, 1, 'files', 'upload', 'Nahrát', 'Upload'),
                                                                                                   (26, 1, 'settings', 'whenUsedInTemplate', 'Při použití v šabloně', 'When used in template'),
                                                                                                   (27, 2, 'days', 'Monday', 'Pondělí', 'Monday'),
                                                                                                   (28, 2, 'days', 'Tuesday', 'Úterý', 'Tuesday'),
                                                                                                   (29, 2, 'days', 'Wednesday', 'Středa', 'Wednesday'),
                                                                                                   (30, 2, 'days', 'Thursday', 'Čtvrtek', 'Thursday'),
                                                                                                   (31, 2, 'days', 'Friday', 'Pátek', 'Friday'),
                                                                                                   (32, 2, 'days', 'Saturday', 'Sobota', 'Saturday'),
                                                                                                   (33, 2, 'days', 'Sunday', 'Neděle', 'Sunday'),
                                                                                                   (35, 2, 'months', 'January', 'Leden', 'January'),
                                                                                                   (36, 2, 'months', 'February', 'Únor', 'February'),
                                                                                                   (38, 2, 'months', 'April', 'Duben', 'April'),
                                                                                                   (39, 2, 'months', 'May', 'Květen', 'May'),
                                                                                                   (40, 2, 'months', 'June', 'Červen', 'June'),
                                                                                                   (41, 2, 'months', 'July', 'Červenec', 'July'),
                                                                                                   (42, 2, 'months', 'August', 'Srpen', 'August'),
                                                                                                   (43, 2, 'months', 'September', 'Září', 'September'),
                                                                                                   (44, 2, 'months', 'October', 'Říjen', 'October'),
                                                                                                   (45, 2, 'months', 'November', 'Listopad', 'November'),
                                                                                                   (46, 2, 'months', 'December', 'Prosinec', 'December'),
                                                                                                   (47, 2, 'sorting', 'new', 'od nejnovějšího', 'by newest'),
                                                                                                   (48, 2, 'sorting', 'old', 'od nejstaršího', 'by oldest'),
                                                                                                   (49, 2, 'sorting', 'cheap', 'od nejlevnějšího', 'by price (lowest)'),
                                                                                                   (50, 2, 'sorting', 'expensive', 'od nejdražšího', 'by price (highest)'),
                                                                                                   (51, 2, 'sorting', 'az', 'A - Z', 'A - Z'),
                                                                                                   (52, 2, 'sorting', 'za', 'Z - A', 'Z - A'),
                                                                                                   (53, 2, 'main', 'address', 'adresa', 'address'),
                                                                                                   (54, 2, 'main', 'Address', 'Adresa', 'Address'),
                                                                                                   (55, 2, 'main', 'addresses', 'adresy', 'addresses'),
                                                                                                   (56, 2, 'main', 'AddToCart', 'Do košíku', 'Add to Cart'),
                                                                                                   (57, 2, 'main', 'AllDayEvent', 'Celodenní událost', 'All day event'),
                                                                                                   (58, 2, 'main', 'amount', 'množství', 'amount'),
                                                                                                   (59, 2, 'main', 'Amount', 'Množství', 'Amount'),
                                                                                                   (60, 2, 'main', 'Appearance', 'Vzhled', 'Appearance'),
                                                                                                   (61, 2, 'main', 'BillingAddress', 'Fakturační adresa', 'Billing address'),
                                                                                                   (62, 2, 'main', 'blog', 'blog', 'blog'),
                                                                                                   (64, 2, 'main', 'Blog', 'Blog', 'Blog'),
                                                                                                   (65, 2, 'main', 'Bonus', 'Bonus', 'Bonus'),
                                                                                                   (66, 2, 'main', 'Brand', 'Značka', 'Brand'),
                                                                                                   (67, 2, 'main', 'brand', 'značka', 'brand'),
                                                                                                   (68, 2, 'main', 'brands', 'značky', 'brands'),
                                                                                                   (69, 2, 'main', 'Calendar', 'Kalendář', 'Calendar'),
                                                                                                   (70, 2, 'main', 'cart', 'košík', 'cart'),
                                                                                                   (71, 2, 'main', 'Cart', 'Košík', 'Cart'),
                                                                                                   (72, 2, 'main', 'catalogue', 'katalog', 'catalogue'),
                                                                                                   (73, 2, 'main', 'Catalogue', 'Katalog', 'Catalogue'),
                                                                                                   (74, 2, 'main', 'categories', 'kategorie', 'categories'),
                                                                                                   (75, 2, 'main', 'category', 'kategorie', 'category'),
                                                                                                   (76, 2, 'main', 'Category', 'Kategorie', 'Category'),
                                                                                                   (77, 2, 'main', 'Categories', 'Kategorie', 'Categories'),
                                                                                                   (78, 2, 'main', 'Change', 'Změnit', 'Change'),
                                                                                                   (79, 2, 'main', 'ChangeStatistics', 'Změnit statistiky', 'Change Statistics'),
                                                                                                   (80, 2, 'main', 'choose', 'vybrat', 'choose'),
                                                                                                   (81, 2, 'main', 'Choose', 'Vybrat', 'Choose'),
                                                                                                   (82, 2, 'main', 'ChooseAGift', 'Vyberte si dárek', 'Choose a gift'),
                                                                                                   (83, 2, 'main', 'city', 'město', 'city'),
                                                                                                   (84, 2, 'main', 'City', 'Město', 'City'),
                                                                                                   (85, 2, 'main', 'close', 'město', 'close'),
                                                                                                   (86, 2, 'main', 'Close', 'Město', 'Close'),
                                                                                                   (87, 2, 'main', 'Company', 'Společnost', 'Company'),
                                                                                                   (88, 2, 'main', 'CompanyInformation', 'Informace o společnosti', 'Company Information'),
                                                                                                   (89, 2, 'main', 'Confirm', 'Potvrdit', 'Confirm'),
                                                                                                   (90, 2, 'main', 'concept', 'koncept', 'concept'),
                                                                                                   (91, 2, 'main', 'contact', 'kontakt', 'contact'),
                                                                                                   (92, 2, 'main', 'Contact', 'Kontakt', 'Contact'),
                                                                                                   (93, 2, 'main', 'ContactInformation', 'Kontaktní informace', 'ContactInformation'),
                                                                                                   (94, 2, 'main', 'contacts', 'kontakty', 'contacts'),
                                                                                                   (95, 2, 'main', 'Contacts', 'Kontakty', 'Contacts'),
                                                                                                   (96, 2, 'main', 'continue', 'pokračovat', 'continue'),
                                                                                                   (97, 2, 'main', 'Continue', 'Pokračovat', 'Continue'),
                                                                                                   (98, 2, 'main', 'ContinueShopping', 'Pokračujte v nákupu', 'Continue shopping'),
                                                                                                   (99, 2, 'main', 'Create', 'Vytvořit', 'Create'),
                                                                                                   (100, 2, 'main', 'created', 'vytvořeno', 'created'),
                                                                                                   (101, 2, 'main', 'CreateNewAccount', 'Založit nový účet', 'Create new account'),
                                                                                                   (102, 2, 'main', 'CreateNewContact', 'Vytvoř nový kontakt', 'Create new contact'),
                                                                                                   (103, 2, 'main', 'daily', 'denní', 'daily'),
                                                                                                   (104, 2, 'main', 'date', 'datum', 'date'),
                                                                                                   (105, 2, 'main', 'Date', 'Datum', 'Date'),
                                                                                                   (107, 2, 'main', 'DateEventEnded', 'Ukončení', 'Event end'),
                                                                                                   (108, 2, 'main', 'DayOfTheWeek', 'Den v týdnu', 'Day of the Week'),
                                                                                                   (109, 2, 'main', 'delete', 'smazat', 'delete'),
                                                                                                   (110, 2, 'main', 'Delete', 'Smazat', 'Delete'),
                                                                                                   (111, 2, 'main', 'DeliveryAddress', 'Doručovací adresa', 'Delivery Address'),
                                                                                                   (112, 2, 'main', 'description', 'popisek', 'description'),
                                                                                                   (113, 2, 'main', 'Description', 'Popisek', 'Description'),
                                                                                                   (114, 2, 'main', 'detail', 'detail', 'detail'),
                                                                                                   (115, 2, 'main', 'Detail', 'Detail', 'Detail'),
                                                                                                   (116, 2, 'main', 'disabled', 'povolen', 'disabled'),
                                                                                                   (117, 2, 'main', 'DoAction', 'Proveď', 'Do'),
                                                                                                   (118, 2, 'main', 'document', 'dokument', 'document'),
                                                                                                   (119, 2, 'main', 'Document', 'Document', 'Document'),
                                                                                                   (120, 2, 'main', 'documents', 'dokumenty', 'documents'),
                                                                                                   (121, 2, 'main', 'Documents', 'Dokumenty', 'Documents'),
                                                                                                   (122, 2, 'main', 'edit', 'upravit', 'edit'),
                                                                                                   (123, 2, 'main', 'Edit', 'Upravit', 'Edit'),
                                                                                                   (124, 2, 'main', 'email', 'e-mail', 'e-mail'),
                                                                                                   (125, 2, 'main', 'Email', 'E-mail', 'E-mail'),
                                                                                                   (126, 2, 'main', 'E-mails', 'E-maily', 'E-mails'),
                                                                                                   (127, 2, 'main', 'enabled', 'povolen', 'enabled'),
                                                                                                   (128, 2, 'main', 'Event', 'Událost', 'Event'),
                                                                                                   (129, 2, 'main', 'Events', 'Události', 'Events'),
                                                                                                   (130, 2, 'main', 'ForgottenPassword', 'Zapomenuté heslo', 'Forgotten password'),
                                                                                                   (131, 2, 'main', 'file', 'soubor', 'file'),
                                                                                                   (132, 2, 'main', 'File', 'Soubor', 'File'),
                                                                                                   (133, 2, 'main', 'FileType', 'Typ souboru', 'File type'),
                                                                                                   (134, 2, 'main', 'Files', 'Soubory', 'Files'),
                                                                                                   (135, 2, 'main', 'from', 'z', 'from'),
                                                                                                   (136, 2, 'main', 'FullDescription', 'Celý popisek', 'Full description'),
                                                                                                   (137, 2, 'main', 'gallery', 'galerie', 'gallery'),
                                                                                                   (138, 2, 'main', 'Gallery', 'Galerie', 'Gallery'),
                                                                                                   (139, 2, 'main', 'Galleries', 'Galerie', 'Galleries'),
                                                                                                   (140, 2, 'main', 'group', 'skupina', 'group'),
                                                                                                   (141, 2, 'main', 'Group', 'Skupina', 'Group'),
                                                                                                   (142, 2, 'main', 'GroupActions', 'Vícenásobné akce', 'Group actions'),
                                                                                                   (143, 2, 'main', 'groups', 'skupiny', 'groups'),
                                                                                                   (144, 2, 'main', 'Helpdesk', 'Helpdesk', 'Helpdesk'),
                                                                                                   (145, 2, 'main', 'Icon', 'Ikona', 'Icon'),
                                                                                                   (146, 2, 'main', 'Icons', 'Ikony', 'Icons'),
                                                                                                   (147, 2, 'main', 'Image', 'Obrázek', 'Image'),
                                                                                                   (148, 2, 'main', 'images', 'obrázky', 'images'),
                                                                                                   (149, 2, 'main', 'insert', 'vložit', 'insert'),
                                                                                                   (150, 2, 'main', 'Insert', 'Vložit', 'Insert'),
                                                                                                   (151, 2, 'main', 'InsertCategory', 'Vložit kategorii', 'Insert category'),
                                                                                                   (152, 2, 'main', 'InsertCity', 'Zadejte město', 'Insert city'),
                                                                                                   (153, 2, 'main', 'InsertEmail', 'Zadejte e-mail', 'Insert e-mail'),
                                                                                                   (154, 2, 'main', 'InsertFile', 'Vložit soubor', 'Insert file'),
                                                                                                   (155, 2, 'main', 'InsertImage', 'Vložit obrázek', 'Insert image'),
                                                                                                   (156, 2, 'main', 'Insert name', 'Vloži jméno', 'Insert name'),
                                                                                                   (157, 2, 'main', 'InsertPhone', 'Zadejte telefon', 'Insert phone'),
                                                                                                   (158, 2, 'main', 'InsertProduct', 'Vložit produkt', 'Insert product'),
                                                                                                   (159, 2, 'main', 'InsertVatIn', 'Zadejte IČO', 'Insert VAT identifier'),
                                                                                                   (160, 2, 'main', 'InsertVatId', 'Zadejte DIČ', 'Insert VAT ID'),
                                                                                                   (161, 2, 'main', 'InsertZip', 'Vložte PSČ', 'Insert ZIP'),
                                                                                                   (162, 2, 'main', 'IPAddress', 'IP adresa', 'IP address'),
                                                                                                   (163, 2, 'main', 'Items', 'Položky', 'Items'),
                                                                                                   (164, 2, 'main', 'links', 'odkazů', 'links'),
                                                                                                   (165, 2, 'main', 'ListOfBrands', 'Seznam značek', 'List of Brands'),
                                                                                                   (166, 2, 'main', 'login', 'přihlásit', 'login'),
                                                                                                   (167, 2, 'main', 'LoggingIn', 'Přihlášení', 'Logging In'),
                                                                                                   (168, 2, 'main', 'Login', 'Přihlásit', 'Login'),
                                                                                                   (169, 2, 'main', 'logout', 'odhlásit', 'logout'),
                                                                                                   (170, 2, 'main', 'media', 'media', 'media'),
                                                                                                   (171, 2, 'main', 'Media', 'Media', 'Media'),
                                                                                                   (172, 2, 'main', 'MetaTags', 'Meta značky', 'Meta tags'),
                                                                                                   (173, 2, 'main', 'MetaKeys', 'Meta značky', 'Meta keys'),
                                                                                                   (174, 2, 'main', 'MetaDesc', 'Meta popisky', 'Meta descriptions'),
                                                                                                   (175, 2, 'main', 'member', 'člen', 'member'),
                                                                                                   (176, 2, 'main', 'Member', 'Člen', 'member'),
                                                                                                   (177, 2, 'main', 'members', 'členové', 'members'),
                                                                                                   (178, 2, 'main', 'Members', 'Členové', 'Members'),
                                                                                                   (179, 2, 'main', 'message', 'zpráva', 'message'),
                                                                                                   (180, 2, 'main', 'Message', 'Zpráva', 'message'),
                                                                                                   (181, 2, 'main', 'monthly', 'měsíčně', 'monthly'),
                                                                                                   (182, 2, 'main', 'MoreImages', 'Více obrázků', 'More images'),
                                                                                                   (183, 2, 'main', 'name', 'jméno', 'name'),
                                                                                                   (184, 2, 'main', 'Name', 'Jméno', 'Name'),
                                                                                                   (185, 2, 'main', 'NewContact', 'Nový kontakt', 'New contact'),
                                                                                                   (186, 2, 'main', 'NewPage', 'Nová stránka', 'New page'),
                                                                                                   (187, 2, 'main', 'next', 'další', 'next'),
                                                                                                   (188, 2, 'main', 'Next', 'Další', 'next'),
                                                                                                   (189, 2, 'main', 'Notes', 'Poznámky', 'Notes'),
                                                                                                   (190, 2, 'main', 'NoName', 'Nemá název', 'No name'),
                                                                                                   (191, 2, 'main', 'open', 'otevřít', 'open'),
                                                                                                   (192, 2, 'main', 'OpeningHours', 'Otevírací hodiny', 'Opening Hours'),
                                                                                                   (193, 2, 'main', 'Page', 'Stránka', 'Page'),
                                                                                                   (194, 2, 'main', 'PageGraphics', 'Grafika na stránce', 'Page Graphics'),
                                                                                                   (195, 2, 'main', 'PagePreview', 'Náhled stránky', 'Page Preview'),
                                                                                                   (196, 2, 'main', 'parameter', 'parametr', 'parameter'),
                                                                                                   (197, 2, 'main', 'parametres', 'parametry', 'parametres'),
                                                                                                   (198, 2, 'main', 'Parameter', 'Parametr', 'Parameter'),
                                                                                                   (199, 2, 'main', 'Parametres', 'Parametry', 'Parametres'),
                                                                                                   (200, 2, 'main', 'Password', 'Heslo', 'Password'),
                                                                                                   (201, 2, 'main', 'phone', 'telefon', 'phone'),
                                                                                                   (202, 2, 'main', 'Phone', 'Telefon', 'Phone'),
                                                                                                   (203, 2, 'main', 'Place', 'Místo', 'Place'),
                                                                                                   (204, 2, 'main', 'Post', 'Pozice', 'Post'),
                                                                                                   (205, 2, 'main', 'Preview', 'Náhled', 'Preview'),
                                                                                                   (206, 2, 'main', 'previous', 'předchozí', 'previous'),
                                                                                                   (207, 2, 'main', 'Previous', 'Předchozí', 'Previous'),
                                                                                                   (208, 2, 'main', 'price', 'cena', 'price'),
                                                                                                   (209, 2, 'main', 'Price', 'Cena', 'Price'),
                                                                                                   (210, 2, 'main', 'Pricelist', 'Ceník', 'Pricelist'),
                                                                                                   (211, 2, 'main', 'ProceedToCheckout', 'Pokračovat k pokladně', 'Proceed to checkout'),
                                                                                                   (212, 2, 'main', 'ProductTitle', 'Název produktu', 'Product title'),
                                                                                                   (213, 2, 'main', 'profile', 'profil', 'profile'),
                                                                                                   (214, 2, 'main', 'published', 'publikováno', 'published'),
                                                                                                   (215, 2, 'main', 'PublishedForm', ' publikováno', ' published'),
                                                                                                   (216, 2, 'main', 'publishingDate', 'Datum publikování', 'Publishing date'),
                                                                                                   (217, 2, 'main', 'RelatedProducts', 'Související produkty', 'Related Products'),
                                                                                                   (218, 2, 'main', 'ResetFilter', 'Vynulovat filtr', 'Reset filter'),
                                                                                                   (219, 2, 'main', 'Remove', 'Odstranit', 'Remove'),
                                                                                                   (220, 2, 'main', 'Role', 'Role', 'Role'),
                                                                                                   (221, 2, 'main', 'Sales', 'Prodeje', 'Sales'),
                                                                                                   (222, 2, 'main', 'save', 'uložit', 'save'),
                                                                                                   (223, 2, 'main', 'Save', 'Uložit', 'Save'),
                                                                                                   (224, 2, 'main', 'search', 'hledat', 'search'),
                                                                                                   (225, 2, 'main', 'Search', 'Hledat', 'Search'),
                                                                                                   (226, 2, 'main', 'Send', 'Odeslat', 'Send'),
                                                                                                   (227, 2, 'main', 'settings', 'nastavení', 'settings'),
                                                                                                   (228, 2, 'main', 'Settings', 'Nastavení', 'Settings'),
                                                                                                   (229, 2, 'main', 'Shipping', 'Poštovné', 'Shipping'),
                                                                                                   (230, 2, 'main', 'ShippingAndPayment', 'Poštovné a platba', 'ShippingAndPayment'),
                                                                                                   (231, 2, 'main', 'show', 'ukázat', 'show'),
                                                                                                   (232, 2, 'main', 'Show', 'Ukázat', 'Show'),
                                                                                                   (233, 2, 'main', 'shortDescription', 'Krátký popisek', 'Short description'),
                                                                                                   (234, 2, 'main', 'SignUp', 'Registrace', 'Sign Up'),
                                                                                                   (235, 2, 'main', 'signup', 'registrace', 'signup'),
                                                                                                   (236, 2, 'main', 'size', 'velikost', 'size'),
                                                                                                   (237, 2, 'main', 'Size', 'Velikost', 'Size'),
                                                                                                   (238, 2, 'main', 'Slug', 'Trvalý odkaz', 'Slug'),
                                                                                                   (239, 2, 'main', 'Snippets', 'Ústřižky', 'Snippets'),
                                                                                                   (240, 2, 'main', 'Sort', 'Seřadit', 'Sort'),
                                                                                                   (241, 2, 'main', 'state', 'stav', 'state'),
                                                                                                   (242, 2, 'main', 'State', 'Stav', 'State'),
                                                                                                   (243, 2, 'main', 'stock', 'zásoby', 'stock'),
                                                                                                   (244, 2, 'main', 'Store', 'Obchod', 'Store'),
                                                                                                   (245, 2, 'main', 'street', 'ulice', 'street'),
                                                                                                   (246, 2, 'main', 'Street', 'Ulice', 'Street'),
                                                                                                   (247, 2, 'main', 'Subject', 'Předmět', 'Subject'),
                                                                                                   (248, 2, 'main', 'SummaryAndOrderCompletion', 'Shrnutí a dokončení objednávky', 'Summary and Order Completion'),
                                                                                                   (249, 2, 'main', 'title', 'název', 'title'),
                                                                                                   (250, 2, 'main', 'Title', 'Název', 'Title'),
                                                                                                   (251, 2, 'main', 'total', 'celkem', 'total'),
                                                                                                   (252, 2, 'main', 'Total', 'Celkem', 'Total'),
                                                                                                   (253, 2, 'main', 'User', 'Uživatel', 'User'),
                                                                                                   (254, 2, 'main', 'UserName', 'Uživatelské jméno', 'User Name'),
                                                                                                   (255, 2, 'main', 'URL', 'URL', 'URL'),
                                                                                                   (256, 2, 'main', 'value', 'hodnota', 'value'),
                                                                                                   (257, 2, 'main', 'Value', 'Hodnota', 'Value'),
                                                                                                   (258, 2, 'main', 'view', 'pohled', 'view'),
                                                                                                   (259, 2, 'main', 'VAT', 'DPH', 'VAT'),
                                                                                                   (260, 2, 'main', 'VatId', 'DIČ', 'VAT ID'),
                                                                                                   (261, 2, 'main', 'VatIn', 'IČ', 'Identification Number'),
                                                                                                   (262, 2, 'main', 'View', 'Prohlédnout', 'View'),
                                                                                                   (263, 2, 'main', 'VisitTheWeb', 'Navštivte web', 'Visit the web'),
                                                                                                   (264, 2, 'main', 'Weight', 'Hmotnost', 'Weight'),
                                                                                                   (265, 2, 'main', 'ZIP', 'PSČ', 'ZIP'),
                                                                                                   (266, 2, '', '', '', ''),
                                                                                                   (267, 3, 'events', 'DateWasNotSet', 'Datum dosud nebylo stanoveno', 'Date was not set yet'),
                                                                                                   (268, 3, 'navigation', 'about', 'O nás', 'About us'),
                                                                                                   (269, 3, 'helpdesk', 'name', 'Jméno', 'Name'),
                                                                                                   (270, 3, 'helpdesk', 'email', 'E-mail', 'E-mail'),
                                                                                                   (271, 3, 'helpdesk', 'phone', 'Telefon', 'Phone'),
                                                                                                   (272, 3, 'helpdesk', 'message', 'Zpráva', 'Message'),
                                                                                                   (273, 3, 'helpdesk', 'request', 'Poptávka', 'Request'),
                                                                                                   (274, 3, 'helpdesk', 'send', 'Odeslat', 'Send'),
                                                                                                   (275, 3, 'members', 'sendLoginEmail', 'odeslat e-mail s přihlašovacími informacemi', 'send e-mail with login information'),
                                                                                                   (276, 3, 'members', 'memberAlreadyExists', 'Člen již existuje', 'Member already exists'),
                                                                                                   (277, 3, 'members', 'emailAlreadyExists', 'Člen s tímto e-mailem již existuje', 'Member with this e-mail already exists'),
                                                                                                   (278, 3, 'members', 'invalidEmailFormat', 'Napište správný formát e-mailu', 'Enter correct e-mail'),
                                                                                                   (279, 3, 'members', 'PermissionDenied', 'Nemáte oprávnění', 'Permission denied'),
                                                                                                   (280, 3, 'pages', 'NameThePage', 'Zadejte název stránky', 'Name the page'),
                                                                                                   (281, 3, 'sign', 'agreeWithConditions', 'Souhlasím s podmínkami', 'I agree with terms and conditions'),
                                                                                                   (282, 3, 'sign', 'descriptionSymbolsEnabled', 'Povoleny jsou pouze znaky a-z, 0-9 (pouze malá písmena)', 'Only a-z, 0-9 characters allowed'),
                                                                                                   (283, 3, 'sign', 'emailAlreadyExists', 'E-mail již existuje', 'E-mail already exists'),
                                                                                                   (284, 3, 'sign', 'emailNotSent', 'E-mail nebyl odeslán', 'E-mail not sent'),
                                                                                                   (285, 3, 'sign', 'enterEmailForCheck', 'Zadejte prosím heslo ještě jednou pro kontrolu', 'Please enter your password again'),
                                                                                                   (286, 3, 'sign', 'enterUserNameMinimum', 'Zvolte uživatelské jméno s alespoň %d znaky', 'Choose a user name with at least %d characters'),
                                                                                                   (287, 3, 'sign', 'enterUsernameMaximum', 'Zvolte uživatelské jméno s nejvýše %d znaky', 'Choose a user name with a maximum of %d characters'),
                                                                                                   (288, 3, 'sign', 'enterPasswordMinimum', 'Zvolte heslo s alespoň %d znaky', 'Choose a password with at least %d characters'),
                                                                                                   (289, 3, 'sign', 'enterPasswordMaximum', 'Zvolte heslo s nejvýše %d znaky', 'Choose a password with at least %d characters'),
                                                                                                   (290, 3, 'sign', 'enterValidEmail', 'Zadejte platný e-mail', 'Enter valid e-mail'),
                                                                                                   (291, 3, 'sign', 'enterValidName', 'Zadejte jméno', 'Enter valid name'),
                                                                                                   (292, 3, 'sign', 'enterValidStreet', 'Zadejte ulici', 'Enter street'),
                                                                                                   (293, 3, 'sign', 'enterValidCity', 'Zadejte město', 'Enter valid city'),
                                                                                                   (294, 3, 'sign', 'enterValidPassword', 'Zadejte platné heslo', 'Enter valid password'),
                                                                                                   (295, 3, 'sign', 'enterValidUserName', 'Zadejte platné uživatelské jméno', 'Enter valid user name'),
                                                                                                   (296, 3, 'sign', 'enterValidZip', 'Zadejte PSČ', 'Enter PSČ'),
                                                                                                   (297, 3, 'sign', 'fillInEmail', 'Vyplňte e-mail', 'You need to log in'),
                                                                                                   (298, 3, 'sign', 'invalidLogin', 'Musíte se přihlásit', 'Invalid login'),
                                                                                                   (299, 3, 'sign', 'logged-out', 'Byli jste odhlášeni', 'You were logged out'),
                                                                                                   (300, 3, 'sign', 'mustAgreeConditions', 'Pro pokračování zaškrtněte Souhlasím s podmínkami', 'To continue check the I agree to terms'),
                                                                                                   (301, 3, 'sign', 'newsletterCheck', 'Chci odebírat zprávy?', 'I want to subscribe to news'),
                                                                                                   (302, 3, 'sign', 'no-access', 'Nemáte oprávnění vstupu do administrace', 'You are not allowed to enter administration'),
                                                                                                   (303, 3, 'sign', 'NotFound', 'Nenalezeno', 'Not Found'),
                                                                                                   (304, 3, 'sign', 'passwords-not-same', 'Hesla se neshodují', 'Passwords are not same'),
                                                                                                   (305, 3, 'sign', 'signupSuccessfulCanLogin', 'Vaše registrace proběhla úspěšně. Po ověření se můžete přihlásit.', 'Your registration was successful. After e-mail verification, you can sign.'),
                                                                                                   (306, 3, 'sign', 'signupSuccessfulLoginWhenVerified', 'Registrace byla dokončena. Po ověření Vám bude zaslán e-mail, po kterém se můžete přihlásit', 'Sign up successfuly completed. you will receive e-mail when verified and you can then sign'),
                                                                                                   (307, 3, 'sign', 'thanksForMessage', 'Děkujeme a Vaši zprávu', 'Děkujeme za Vaši zprávu'),
                                                                                                   (308, 3, 'sign', 'userNameAlreadyExists', 'Uživatelské jméno již existuje', 'User name already exists'),
                                                                                                   (309, 3, 'error', 'invalidTypeOfImage', 'Neplatný typ obrázku', 'Invalid type of image'),
                                                                                                   (310, 3, 'error', 'CannotBeDeleted', 'Nelze smazat', 'Cannot be deleted'),
                                                                                                   (311, 3, 'error', 'CantDeleteMainGroup', 'Nemůžete vymazat hlavní skupinu', 'You can\'t delete main group'),
                                                                                                   (312, 3, 'error', 'categoryAlreadyExists', 'Kategorie tohoto jména již existuje', 'Category with this name already exists'),
                                                                                                   (313, 3, 'error', 'categoryMustHaveSomeName', 'Kategorie musí mít nějaký název', 'Category must have some name'),
                                                                                                   (314, 3, 'error', 'fillInName', 'Vyplňte Vaše skutečné jméno', 'Fill in your name'),
                                                                                                   (315, 3, 'error', 'fillInEmail', 'Vyplňte e-mail', 'Fill in your e-mail'),
                                                                                                   (316, 3, 'error', 'fillInMessage', 'Vyplňte zprávu', 'Fill in your message'),
                                                                                                   (317, 3, 'error', 'MustAgreeWithConditions', 'Musíte souhlasit s obchodními podmínkami, abyste mohli dokončit objednávku', 'You must agree with conditions to finish the order'),
                                                                                                   (318, 3, 'error', 'NoItemsBoughtYet', 'Zatím jste nekoupili žádné zboží', 'You haven\'t bought any items'),
                                                                                                   (319, 3, 'error', 'noPermissionForAdmin', 'Nemáte oprávnění vstoupit do administrace', 'You don\'t have permission to enter admnistration'),
                                                                                                   (320, 3, 'error', 'NotFound', 'Nebylo nalezeno', 'Not found'),
                                                                                                   (321, 3, 'error', 'youWereLoggedIn', 'Byli jste odhlášen', 'You were logged in'),
                                                                                                   (322, 2, 'main', 'Links', 'Odkazy', 'Links'),
                                                                                                   (323, 2, 'main', 'related', 'související', 'related'),
                                                                                                   (324, 2, 'main', 'files', 'Soubory', 'files');

CREATE TABLE `lang_list` (
  `id` int(11) NOT NULL,
  `title` varchar(20) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `lang_list` (`id`, `title`) VALUES
                                               (1, 'admin'),
                                               (2, 'dictionary'),
                                               (3, 'messages'),
                                               (4, 'ublaboo_datagrid');

CREATE TABLE `links` (
  `id` int(11) NOT NULL,
  `links_categories_id` int(11) DEFAULT NULL,
  `url` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `title` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `description` text CHARACTER SET latin1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `links_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `logger` (
  `id` int(11) NOT NULL,
  `event` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  `users_id` int(11) DEFAULT NULL,
  `pages_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `event_types_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `name` varchar(140) COLLATE utf8_czech_ci NOT NULL,
  `file_type` tinyint(1) NOT NULL DEFAULT '0',
  `filesize` int(11) NOT NULL DEFAULT '0',
  `pages_id` int(11) DEFAULT NULL,
  `title` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `date_created` datetime NOT NULL,
  `detail_view` tinyint(1) NOT NULL DEFAULT '1',
  `sorted` int(11) NOT NULL,
  `main_file` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `menu_menus_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `pages_id` int(11) DEFAULT NULL,
  `url` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL,
  `url_en` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0',
  `title_en` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `description_en` text COLLATE utf8_czech_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `menu` (`id`, `parent_id`, `menu_menus_id`, `description`, `title`, `pages_id`, `url`, `url_en`, `sorted`, `title_en`, `description_en`) VALUES
                                                                                                                                                            (1, NULL, 1, 'Hlavní menu/Main menu', 'Top', NULL, '', NULL, 1028, NULL, NULL),
                                                                                                                                                            (2, NULL, 2, NULL, 'Bottom', NULL, 'http://caloris.cz', 'http://caloris.cz/en', 1004, 'Caloris', NULL);

CREATE TABLE `menu_menus` (
  `id` int(11) NOT NULL,
  `title` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  `type` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `class` varchar(60) CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `menu_menus` (`id`, `title`, `description`, `type`, `class`) VALUES
                                                                                (1, 'Top', 'Menu pro horní navigaci', 'TopMenu', 'navbar-nav mr-auto'),
                                                                                (2, 'Bottom', 'Jednoduché menu pro spodní navigaci', 'SimpleMenu', 'nav flex-column');

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `title` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `document` text COLLATE utf8_czech_ci,
  `preview` text COLLATE utf8_czech_ci,
  `pages_id` int(11) DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL,
  `public` int(11) DEFAULT '0',
  `metadesc` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL,
  `metakeys` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_published` datetime DEFAULT NULL,
  `pages_types_id` int(11) DEFAULT '1',
  `pages_templates_id` int(11) DEFAULT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0',
  `editable` int(11) NOT NULL DEFAULT '1',
  `recommended` tinyint(4) DEFAULT '0',
  `sitemap` tinyint(4) NOT NULL DEFAULT '1',
  `title_en` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `slug_en` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `document_en` text COLLATE utf8_czech_ci,
  `preview_en` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `metakeys_en` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `metadesc_en` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages` (`id`, `slug`, `title`, `document`, `preview`, `pages_id`, `users_id`, `public`, `metadesc`, `metakeys`, `date_created`, `date_published`, `pages_types_id`, `pages_templates_id`, `sorted`, `editable`, `recommended`, `sitemap`, `title_en`, `slug_en`, `document_en`, `preview_en`, `metakeys_en`, `metadesc_en`) VALUES
                                                                                                                                                                                                                                                                                                                                                (1, '', 'Homepage', NULL, NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 4, 77, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL),
                                                                                                                                                                                                                                                                                                                                                (2, 'kontakt', 'Kontakt', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 9, 5, 79, 0, 0, 1, NULL, 'contact', NULL, NULL, NULL, NULL),
                                                                                                                                                                                                                                                                                                                                                (3, 'blog', 'Blog', '', NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 2, 81, 0, 0, 1, NULL, 'blog', NULL, NULL, NULL, NULL),
                                                                                                                                                                                                                                                                                                                                                (4, 'galerie', 'Galerie', '', NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 7, 71, 0, 0, 1, NULL, 'gallery', NULL, NULL, NULL, NULL),
                                                                                                                                                                                                                                                                                                                                                (5, 'udalosti', 'Události', '', NULL, 1, 1, 1, '', '', NULL, NULL, 9, 10, 73, 0, 0, 1, 'Events', 'events', NULL, NULL, NULL, NULL),
                                                                                                                                                                                                                                                                                                                                                (6, 'dokumenty', 'Dokumenty', '', NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 8, 75, 0, 0, 1, NULL, 'documents', NULL, NULL, NULL, NULL),
                                                                                                                                                                                                                                                                                                                                                (7, 'cenik', 'Ceník', NULL, NULL, 1, 1, 1, '', '', NULL, NULL, 9, 11, 800, 0, 0, 1, NULL, 'pricelist', NULL, NULL, NULL, NULL);

CREATE TABLE `pages_related` (
  `id` int(11) NOT NULL,
  `pages_id` int(11) NOT NULL,
  `related_pages_id` int(11) DEFAULT NULL,
  `description` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `pages_templates` (
  `id` int(11) NOT NULL,
  `pages_types_id` int(11) DEFAULT NULL,
  `template` varchar(250) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `title_en` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages_templates` (`id`, `pages_types_id`, `template`, `title`, `title_en`) VALUES
                                                                                               (1, NULL, 'Front:Media:albumWithDescription', 'Galerie s náhledy obrázků s podporou Lightboxu', 'Album with description'),
                                                                                               (2, NULL, 'Front:Pages:blogList', 'Seznam příspěvků', 'List of articles'),
                                                                                               (3, NULL, 'Front:Pages:default', 'Základní typ stránky', 'Basic type of page'),
                                                                                               (4, NULL, 'Front:Homepage:default', 'Homepage', 'Homepage'),
                                                                                               (5, NULL, 'Front:Contact:default', 'Kontaktní stránka s kontaktním formulářem', 'Contact page with contact/request form'),
                                                                                               (6, NULL, 'Front:Pages:blogDetail', 'Detail příspěvu', 'Article page'),
                                                                                               (7, NULL, 'Front:Media:album', 'Základní galerie', 'Basic gallery view'),
                                                                                               (8, NULL, 'Front:Media:folder', 'Seznam složek dokumentů', 'List of document folders'),
                                                                                               (9, NULL, 'Front:Media:folderList', 'Seznam dokumentů dané složky', 'List of documents of given folder'),
                                                                                               (10, NULL, 'Front:Events:detail', 'Seznam událostí', 'List of events'),
                                                                                               (11, NULL, 'Front:Pricelist:default', 'Ceníky', 'Seznam a zobrazení všech ceníků');

CREATE TABLE `pages_types` (
  `id` int(11) NOT NULL,
  `content_type` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `presenter` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `action` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `prefix` varchar(60) COLLATE utf8_czech_ci DEFAULT NULL,
  `admin_enabled` smallint(6) NOT NULL DEFAULT '1',
  `admin_link` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL,
  `icon` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
  `enable_snippets` tinyint(1) NOT NULL DEFAULT '1',
  `enable_images` tinyint(1) NOT NULL DEFAULT '1',
  `enable_files` tinyint(1) NOT NULL DEFAULT '1',
  `enable_related` tinyint(1) NOT NULL DEFAULT '1',
  `pages_id` int(11) DEFAULT NULL COMMENT 'Initial page id for this page type',
  `pages_templates_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages_types` (`id`, `content_type`, `presenter`, `action`, `prefix`, `admin_enabled`, `admin_link`, `icon`, `enable_snippets`, `enable_images`, `enable_files`, `enable_related`, `pages_id`, `pages_templates_id`) VALUES
                                                                                                                                                                                                                                        (1, 'Stránky', 'Front:Pages', 'default', '', 1, 'pages/?type=1', 'fa-files-o', 0, 1, 1, 1, 1, 3),
                                                                                                                                                                                                                                        (2, 'Aktuality', 'Front:Blog', 'detail', 'blog', 1, 'pages/?type=2', 'fa-newspaper-o', 1, 1, 1, 1, 3, 6),
                                                                                                                                                                                                                                        (3, 'Události', 'Front:Events', 'default', '', 1, 'pages/?type=3', 'fa-calendar-o', 0, 1, 1, 1, 4, 1),
                                                                                                                                                                                                                                        (6, 'Galerie', 'Front:Media', 'album', '', 1, 'pages/?type=6', 'fa-file-image-o', 0, 1, 1, 1, 4, 1),
                                                                                                                                                                                                                                        (8, 'Dokumenty', 'Front:Media', 'folder', '', 1, 'pages?type=8', 'fa-files-o', 1, 1, 1, 1, 6, 9),
                                                                                                                                                                                                                                        (9, 'Šablony', '', 'default', '', 1, 'pages?type=9', 'fa-th', 1, 1, 1, 1, 5, 10);

CREATE TABLE `pages_widgets` (
  `id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `pages_id` int(11) DEFAULT NULL,
  `sorted` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages_widgets` (`id`, `title`, `description`, `pages_id`, `sorted`) VALUES
                                                                                        (1, 'Offer', 'Menu s obrázky', 1, 1),
                                                                                        (2, 'About Us', 'Menu s nabídkou a obrázky nebo ikonami', 1, 2),
                                                                                        (3, 'Kontakt', 'Kontaktní informace', 1, 3),
                                                                                        (4, 'Mapa', 'Google mapa', 1, 4),
                                                                                        (5, 'Blog', 'Prvních několik přípěvků z blogu', 1, 5),
                                                                                        (6, 'Opening hours', 'Otevírací hodiny', 1, 0),
                                                                                        (7, 'Carousel', 'Rotující obrázky', 1, 0),
                                                                                        (8, 'Seznam kontaktů', 'Seznam všech kontaktů s stelefonem a e-mailem', 2, 0);

CREATE TABLE `param` (
  `id` int(11) NOT NULL,
  `param` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `param_en` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  `prefix` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Will be automatically filled before value',
  `suffix` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Will be automatically filled after value',
  `preset` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Value in preset will be autofilled',
  `ignore_front` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide in parametres in presentations',
  `ignore_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide in admin params view',
  `type_front` varchar(40) COLLATE utf8_czech_ci NOT NULL DEFAULT 'radio' COMMENT 'Display type of parametre form: select, radio etc.',
  `sorted` int(11) DEFAULT '0' COMMENT 'You can sort params',
  `block_class` varchar(60) COLLATE utf8_czech_ci DEFAULT '0' COMMENT 'Add classes for the parameter block',
  `replace_param` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `params` (
  `id` int(11) NOT NULL,
  `pages_id` int(11) NOT NULL,
  `param_id` int(11) NOT NULL,
  `paramvalue` varchar(120) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `pictures` (
  `id` int(11) NOT NULL,
  `name` varchar(140) COLLATE utf8_czech_ci NOT NULL,
  `file_type` tinyint(1) NOT NULL DEFAULT '0',
  `filesize` int(11) NOT NULL DEFAULT '0',
  `pages_id` int(11) DEFAULT NULL,
  `title` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `date_created` datetime NOT NULL,
  `detail_view` tinyint(1) NOT NULL DEFAULT '1',
  `sorted` int(11) NOT NULL,
  `main_file` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `pricelist` (
  `id` int(11) NOT NULL,
  `pricelist_categories_id` int(11) DEFAULT NULL,
  `title` varchar(400) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  `price` double NOT NULL,
  `price_info` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `pricelist_categories` (
  `id` int(11) NOT NULL,
  `pricelist_lists_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `pricelist_daily` (
  `id` int(11) NOT NULL,
  `pricelist_categories_id` int(11) DEFAULT NULL,
  `title` text CHARACTER SET latin1 NOT NULL,
  `pricelist_dates_id` int(11) NOT NULL,
  `price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `pricelist_dates` (
  `id` int(11) NOT NULL,
  `day` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `pricelist_lists` (
  `id` int(11) NOT NULL,
  `title` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `currencies_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `class` varchar(60) CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `settings_categories_id` int(11) DEFAULT NULL,
  `setkey` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `setvalue` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `description_cs` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `description_en` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `type` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
  `admin_editable` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `settings` (`id`, `settings_categories_id`, `setkey`, `setvalue`, `description_cs`, `description_en`, `type`, `admin_editable`) VALUES
                                                                                                                                                   (1, 15, 'blog:short:showPreview', '1', 'Zobrazovat zkrácenou verzi článku v krátké verzi blogu.', 'Show preview of an article.', 'boolean', 1),
                                                                                                                                                   (2, 15, 'blog:short:showAuthor', '0', 'Zobrazovat autora', 'Show author (member) of the article.', 'boolean', 1),
                                                                                                                                                   (3, 15, 'blog:short:showImage', '0', 'Zobrazovat náhled obrázku', 'Show image thumbnail', 'boolean', 1),
                                                                                                                                                   (4, 15, 'blog:short:showDate', '0', 'Zobrazovat datum vydání', 'Show date published.', 'boolean', 1),
                                                                                                                                                   (5, 15, 'blog:preview:length', '350', 'Délka náhledového textu (0 znamená nezkrácený text)', 'Length of preview text (0 means complete text)', 'numeric', 1),
                                                                                                                                                   (6, 15, 'blog_fblike', '1', 'Přidá sdílecí tlačítko na Facebook, pokud je zadán FB účet', 'Adds sharing button for Facebook, if FB account specified', 'boolean', 1),
                                                                                                                                                   (21, 16, 'contacts:email:hq', '', 'Vaše e-mailová adresa', 'Your e-mail address', NULL, 1),
                                                                                                                                                   (22, 16, 'contacts:email:techSupport', '', 'Technická podpora', 'Technical support', NULL, 1),
                                                                                                                                                   (23, 16, 'contacts:email:order', '', 'E-mail pro odesílání objednávky', 'E-mail for sending orders', NULL, 1),
                                                                                                                                                   (24, 16, 'contacts:smartForm:enabled', '0', 'Povolit doplňování SmartForm', 'SmartForm address autosuggest enabled', 'boolean', 1),
                                                                                                                                                   (25, 16, 'contacts:smartForm:clientId', '', 'Klientský kód SmartForm', 'SmartForm client code', '', 1),
                                                                                                                                                   (26, 16, 'contacts:residency:contacts_id', '', 'Kontaktní informace o sídlu', 'Headquarters contact information', 'table:contacts;column:name', 1),
                                                                                                                                                   (27, 12, 'members:groups:enabled', '0', 'Vytvářet uživatelské skupiny', 'Create user groups', 'boolean', 1),
                                                                                                                                                   (28, 12, 'members:group:categoryId', '', 'Identifikátor uživatelské kategorie', 'User category identifier', NULL, 1),
                                                                                                                                                   (29, 12, 'members:signup:contactEnabled', '', 'Kontaktní informace v registračním formuláři', 'Contasct information in sign up form', 'boolean', 1),
                                                                                                                                                   (30, 12, 'members:signup:companyEnabled', '', 'Firemní informace v registračním formuláři', 'Business information in sign up form', 'boolean', 1),
                                                                                                                                                   (31, 12, 'members:signup:confirmByAdmin', '', 'Registrace uživatele musí být potvrzena administrátorem.', 'User Registration must be confirmed by the admin.', 'boolean', 1),
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
                                                                                                                                                   (43, 17, 'social:fb:enabled', '0', 'Povoleno zobrazování Facebooku', 'Facebook enabled', 'boolean', 1),
                                                                                                                                                   (44, 17, 'social:fb:type', 'page', 'Typ: účet nebo stránka', 'Type: account or page', NULL, 1),
                                                                                                                                                   (45, 17, 'social:fb:url', '', 'Adresa stránky nebo účtu včetně http', 'Address of the page or account including http', NULL, 1),
                                                                                                                                                   (46, 17, 'social:twitter:account', '', 'Účet na Twitteru', 'Twitter account', NULL, 1),
                                                                                                                                                   (60, 20, 'appearance:paths:logo', '', 'Obrázek loga ve vysokém rozlišení a barvě', 'Image of logo in high resolution and color', 'local_path', 0),
                                                                                                                                                   (61, 20, 'appearance:paths:favicon:ico', 'favicon.ico', 'Favicon s příponou ico', 'Favicon with ico suffix', 'local_path', 0),
                                                                                                                                                   (62, 21, 'appearance:carousel:directions', '0', 'Zobrazit indikátory (levá a pravá šipka)', 'Show indicators (left and right arrow)', 'boolean', 1),
                                                                                                                                                   (63, 21, 'appearance:carousel:indicators', '0', 'Zobrazit menu', 'Show menu', 'boolean', 1),
                                                                                                                                                   (64, 12, 'members_username_as_email', '0', 'Použít e-mail jako uživatelské jméno', 'Use e-mail as username', 'boolean', 1),
                                                                                                                                                   (65, 12, 'members_signup_message', '0', 'Možnost napsat zprávu při registraci', 'Option to write message in signup', 'boolean', 1),
                                                                                                                                                   (66, 12, 'members_signup_conditions_agree', '0', 'Souhlasit s podmínkami zaškrtávací tlačítko', 'Agree with conditions box', 'boolean', 1),
                                                                                                                                                   (67, 12, 'members_signup_conditions_link', '', 'Odkaz na soubor nebo stránku s podmínkami', 'Link to a file or page with terms and conditions', NULL, 1),
                                                                                                                                                   (68, 23, 'media_thumb_dir', 'tn', 'Adresář pro náhledy', 'Directory for thumbnails', NULL, 1),
                                                                                                                                                   (69, 23, 'media_thumb_width', '300', 'Šířka náhledu', 'Width of thumbnail', NULL, 1),
                                                                                                                                                   (70, 23, 'media_thumb_height', '200', 'Výška náhledu', 'Height of thumbnail', NULL, 1);

CREATE TABLE `settings_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


INSERT INTO `settings_categories` (`id`, `parent_id`, `description`, `title`, `sorted`) VALUES
                                                                                               (3, NULL, NULL, 'Šablony', 146),
                                                                                               (10, NULL, NULL, 'Základní nastavení', 146),
                                                                                               (12, NULL, NULL, 'Členové', 148),
                                                                                               (15, NULL, NULL, 'Blog', 154),
                                                                                               (16, 3, NULL, 'Kontakty', 166),
                                                                                               (17, NULL, NULL, 'Služby', 176),
                                                                                               (20, NULL, NULL, 'Vzhled', 154),
                                                                                               (21, 20, NULL, 'Carousel', 155),
                                                                                               (23, NULL, '', 'Media', 0),
                                                                                               (24, 3, NULL, 'Homepage', 166);


CREATE TABLE `snippets` (
  `id` int(11) NOT NULL,
  `keyword` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `content` text COLLATE utf8_czech_ci,
  `content_en` text COLLATE utf8_czech_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `test` (
  `id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

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
  `login_success` int(11) NOT NULL DEFAULT '0',
  `adminbar_enabled` smallint(6) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users` (`id`, `username`, `users_categories_id`, `email`, `sex`, `name`, `password`, `date_created`, `date_visited`, `state`, `activation`, `newsletter`, `type`, `users_roles_id`, `login_error`, `login_success`, `adminbar_enabled`) VALUES
                                                                                                                                                                                                                                                            (1, 'admin', NULL, '', 2, '', '$2y$10$DLhMCsYpbB.xHJ501e.xMOvhneiT1U6YypGAcOna/V2kzIGZOwxla', NULL, '', 1, 'smx5anwed2dr', 1, 1, 1, 5, 35, 0);

CREATE TABLE `users_categories` (
  `id` int(11) NOT NULL,
  `title` varchar(250) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users_categories` (`id`, `title`) VALUES
                                                      (1, 'Hlavní skupina');

CREATE TABLE `users_roles` (
  `id` int(11) NOT NULL,
  `title` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `sign` tinyint(1) NOT NULL DEFAULT '0',
  `appearance` tinyint(1) NOT NULL DEFAULT '0',
  `helpdesk` tinyint(1) NOT NULL DEFAULT '0',
  `settings` tinyint(1) NOT NULL DEFAULT '0',
  `settings_permissions` int(11) NOT NULL DEFAULT '0',
  `members` tinyint(1) NOT NULL DEFAULT '0',
  `pages` tinyint(1) NOT NULL DEFAULT '0',
  `pictures` int(11) NOT NULL,
  `media` int(11) DEFAULT '0',
  `menu` int(11) NOT NULL DEFAULT '0',
  `contacts` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users_roles` (`id`, `title`, `sign`, `appearance`, `helpdesk`, `settings`, `settings_permissions`, `members`, `pages`, `pictures`, `media`, `menu`, `contacts`) VALUES
                                                                                                                                                                                    (1, 'Admin', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
                                                                                                                                                                                    (2, 'Super User', 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1),
                                                                                                                                                                                    (3, 'Editor', 1, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1),
                                                                                                                                                                                    (4, 'Site User', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);


ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `board`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `helpdesk_templates_id` (`helpdesk_templates_id`),
  ADD KEY `helpdesk_templates_id_2` (`helpdesk_templates_id`);

ALTER TABLE `helpdesk_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `helpdesk_id` (`helpdesk_id`);

ALTER TABLE `helpdesk_templates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lang_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lang_list_id` (`lang_list_id`);

ALTER TABLE `lang_list`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `links_category_id` (`links_categories_id`);

ALTER TABLE `links_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `logger`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `albums_id` (`pages_id`);

ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pages_id` (`pages_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `menu_menus_id` (`menu_menus_id`);

ALTER TABLE `menu_menus`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `pages_id` (`pages_id`),
  ADD KEY `pages_templates_id` (`pages_templates_id`);

ALTER TABLE `pages_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pages_id` (`pages_id`);

ALTER TABLE `param`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `params`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`pages_id`),
  ADD KEY `store_param_id` (`param_id`);

ALTER TABLE `pictures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `albums_id` (`pages_id`);

ALTER TABLE `pricelist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pricelist_categories_id` (`pricelist_categories_id`);

ALTER TABLE `pricelist_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `pricelist_lists_id` (`pricelist_lists_id`);

ALTER TABLE `pricelist_daily`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pricelist_categories_id` (`pricelist_categories_id`),
  ADD KEY `pricelist_dates_id` (`pricelist_dates_id`),
  ADD KEY `pricelist_dates_id_2` (`pricelist_dates_id`);

ALTER TABLE `pricelist_dates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pricelist_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `currencies_id` (`currencies_id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_id` (`settings_categories_id`);

ALTER TABLE `settings_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `snippets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `test`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_id` (`users_categories_id`),
  ADD KEY `users_roles_id` (`users_roles_id`);

ALTER TABLE `users_categories`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users_roles`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

ALTER TABLE `board`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `carousel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `contacts_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `contacts_communication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `contacts_openinghours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `events_signed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `helpdesk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `helpdesk_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `lang_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=325;

ALTER TABLE `lang_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `links_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `logger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

ALTER TABLE `menu_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

ALTER TABLE `pages_related`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pages_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `pages_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `pages_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `param`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `params`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

ALTER TABLE `pricelist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `pricelist_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `pricelist_daily`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pricelist_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `pricelist_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

ALTER TABLE `settings_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

ALTER TABLE `snippets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `users_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_4` FOREIGN KEY (`countries_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `contacts_ibfk_5` FOREIGN KEY (`contacts_categories_id`) REFERENCES `contacts_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_6` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `contacts_openinghours`
  ADD CONSTRAINT `contacts_openinghours_ibfk_1` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `events_signed`
  ADD CONSTRAINT `events_signed_ibfk_1` FOREIGN KEY (`events_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `helpdesk`
  ADD CONSTRAINT `helpdesk_ibfk_1` FOREIGN KEY (`helpdesk_templates_id`) REFERENCES `helpdesk_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `helpdesk_messages`
  ADD CONSTRAINT `helpdesk_messages_ibfk_3` FOREIGN KEY (`helpdesk_id`) REFERENCES `helpdesk` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `lang_keys`
  ADD CONSTRAINT `lang_keys_ibfk_1` FOREIGN KEY (`lang_list_id`) REFERENCES `lang_list` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `links`
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`links_categories_id`) REFERENCES `links_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `links_categories`
  ADD CONSTRAINT `links_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `links_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_4` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menu_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menu_ibfk_3` FOREIGN KEY (`menu_menus_id`) REFERENCES `menu_menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE `pages_types`
  ADD CONSTRAINT `pages_types_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_types_ibfk_2` FOREIGN KEY (`pages_templates_id`) REFERENCES `pages_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pages_widgets`
  ADD CONSTRAINT `pages_widgets_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `params`
  ADD CONSTRAINT `params_ibfk_3` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `params_ibfk_4` FOREIGN KEY (`param_id`) REFERENCES `param` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pricelist`
  ADD CONSTRAINT `pricelist_ibfk_1` FOREIGN KEY (`pricelist_categories_id`) REFERENCES `pricelist_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pricelist_categories`
  ADD CONSTRAINT `pricelist_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `pricelist_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pricelist_categories_ibfk_2` FOREIGN KEY (`pricelist_lists_id`) REFERENCES `pricelist_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pricelist_daily`
  ADD CONSTRAINT `pricelist_daily_ibfk_1` FOREIGN KEY (`pricelist_dates_id`) REFERENCES `pricelist_dates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pricelist_daily_ibfk_2` FOREIGN KEY (`pricelist_categories_id`) REFERENCES `pricelist_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pricelist_lists`
  ADD CONSTRAINT `pricelist_lists_ibfk_1` FOREIGN KEY (`currencies_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`settings_categories_id`) REFERENCES `settings_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`users_categories_id`) REFERENCES `users_categories` (`id`);
COMMIT;
