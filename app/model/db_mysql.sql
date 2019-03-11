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
                                                                                                                                      (4, 'Odeslání hesla pro existujícího uživatele', 'Tímto formulářem bude existujícímu uživateli odesláno nové heslo. Pozor. Pokaždé, když formulář odešlete, bude heslo změněno a odesláno.', 0, NULL, 'Vytvoření nového hesla', '                    <br /><br />\r\n                    Bylo Vám vytvořeno nové heslo. Zde jsou údaje nutné k přihlášení\r\n                    <br /><br />\r\n                    uživatelské jméno: {$username}<br />\r\n                    heslo: {$password}<br />\r\n                    <br /><br />\r\n                    Přihlašte se: <a href=\"{$settings[\'site:url:base\']}/sign/in\">{$settings[\'site:url:base\']}/sign/in</a>', 1, 0),
                                                                                                                                      (5, 'Vytvoření účtu z administrace', 'Vytvoření nového uživatelského účtu z administrace', 0, NULL, 'Nový e-mail', '                    Your account was successfully created. You can now log in.\r\n                    <br /><br />\r\n                    user name: {$username}<br />\r\n                    password: {$password}<br />\r\n                    <br /><br />\r\n                    Log in: <a href=\"{$settings[\'site:url:base\']}/admin\">{$settings[\'site:url:base\']}/admin</a>', 1, 0),
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
                                                                    (1, 'Basic', '<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\\r\\n        \"http://www.w3.org/TR/html4/loose.dtd\">\\r\\n<html lang=\"cs\">\\r\\n<head>\\r\\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\\r\\n    <title>%TITLE%</title>\\r\\n    <style n:syntax=\"off\">\\r\\n        * {\\r\\n            font-family: Arial;\\r\\n        }\\r\\n\\r\\n        .text-right {\\r\\n            text-align: right;\\r\\n        }\\r\\n    </style>\\r\\n</head>\\r\\n<body bgcolor=\"#ffffff\" topmargin=\"0\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\"\\r\\n      style=\"width:100% !important; text-align: center; font-family: Arial sans-serif;\">\\r\\n\\r\\n<table style=\"width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;\">\\r\\n    <tr>\\r\\n        <td colspan=\"2\" style=\"color: white; vertical-align: middle; background-color: #8e8e8e;\\r\\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;\">\\r\\n{$settings[\'site:title\']}\\r\\n        </td>\\r\\n    </tr>\\r\\n    <tr>\\r\\n        <td style=\"text-align: left;\">\\r\\n%CONTENT%\\r\\n</td>\\r\\n    </tr>\\r\\n</table>\\r\\n\\r\\n</body>\\r\\n</html>'),
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
                                                                          (2, 'en', 'English', 0, NULL);

CREATE TABLE `links` (
  `id` int(11) NOT NULL,
  `links_categories_id` int(11) DEFAULT NULL,
  `url` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `title` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `description` text CHARACTER SET latin1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


INSERT INTO `links_categories` (
    `id` ,
    `parent_id` ,
    `description` ,
    `title` ,
    `sorted`
    )
VALUES (1, NULL , NULL , 'Odkazy', '1');

CREATE TABLE `links_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `title` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `sorted` int(11) NOT NULL DEFAULT '0'
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
  `sorted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `menu` (`id`, `parent_id`, `menu_menus_id`, `description`, `title`, `pages_id`, `url`, `sorted`) VALUES
                                                                                                                                                            (1, NULL, 1, 'Hlavní menu/Main menu', 'Top', NULL, '', 1028),
                                                                                                                                                            (2, NULL, 2, NULL, 'Bottom', NULL, '', 1004);

CREATE TABLE `menu_menus` (
  `id` int(11) NOT NULL,
  `title` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  `type` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `class` varchar(60) CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `menu_menus` (`id`, `title`, `description`, `type`, `class`) VALUES
                                                                                (1, 'Top', 'Menu pro horní navigaci', 'Menu', 'navbar-nav mr-auto'),
                                                                                (2, 'Bottom', 'Jednoduché menu pro spodní navigaci', 'BadgesMenu', 'nav flex-column');

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
  `sitemap` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages` (`id`, `slug`, `title`, `document`, `preview`, `pages_id`, `users_id`, `public`, `metadesc`, `metakeys`, `date_created`, `date_published`, `pages_types_id`, `pages_templates_id`, `sorted`, `editable`, `sitemap`) VALUES
                                                                                                                                                                                                                                                                                                                                                (1, '', 'Homepage', NULL, NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 4, 77, 1, 1),
                                                                                                                                                                                                                                                                                                                                                (2, 'kontakt', 'Kontakt', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 9, 5, 79, 0, 1),
                                                                                                                                                                                                                                                                                                                                                (3, 'blog', 'Blog', '', NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 2, 81, 0, 1),
                                                                                                                                                                                                                                                                                                                                                (4, 'galerie', 'Galerie', '', NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 7, 71, 0, 1),
                                                                                                                                                                                                                                                                                                                                                (5, 'udalosti', 'Události', '', NULL, 1, 1, 1, '', '', NULL, NULL, 9, 10, 73, 0, 1),
                                                                                                                                                                                                                                                                                                                                                (6, 'dokumenty', 'Dokumenty', '', NULL, NULL, 1, 1, '', '', NULL, NULL, 9, 8, 75, 0, 1),
                                                                                                                                                                                                                                                                                                                                                (7, 'cenik', 'Ceník', NULL, NULL, 1, 1, 1, '', '', NULL, NULL, 9, 11, 795, 0, 1),
                                                                                                                                                                                                                                                                                                                                                (8, 'odkazy', 'Odkazy', NULL, NULL, 1, 1, 1, '', '', NULL, NULL, 9, 12, 800, 0, 1);

CREATE TABLE `pages_templates` (
  `id` int(11) NOT NULL,
  `pages_types_id` int(11) DEFAULT NULL,
  `template` varchar(250) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages_templates` (`id`, `pages_types_id`, `template`, `title`) VALUES
                                                                                               (1, NULL, 'Front:Media:albumWithDescription', 'Galerie s náhledy obrázků s podporou Lightboxu'),
                                                                                               (2, NULL, 'Front:Pages:blogList', 'Seznam příspěvků'),
                                                                                               (3, NULL, 'Front:Pages:default', 'Základní typ stránky'),
                                                                                                (4, NULL, 'Front:Homepage:default', 'Homepage'),
                                                                                                (5, NULL, 'Front:Contact:default', 'Kontaktní stránka s kontaktním formulářem'),
                                                                                                (6, NULL, 'Front:Pages:blogDetail', 'Detail příspěvu'),
                                                                                                (7, NULL, 'Front:Media:album', 'Základní galerie'),
                                                                                                (8, NULL, 'Front:Media:folder', 'Seznam složek dokumentů'),
                                                                                                (9, NULL, 'Front:Media:folderList', 'Seznam dokumentů dané složky'),
                                                                                                (10, NULL, 'Front:Events:detail', 'Seznam událostí'),
                                                                                               (12, NULL , 'Front:Links:default', 'Odkazy');

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
  `pages_id` int(11) DEFAULT NULL COMMENT 'Initial page id for this page type',
  `pages_templates_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `pages_types` (`id`, `content_type`, `presenter`, `action`, `prefix`, `admin_enabled`, `admin_link`, `icon`, `enable_snippets`, `enable_images`, `enable_files`, `pages_id`, `pages_templates_id`) VALUES
                                                                                                                                                                                                                                        (1, 'Stránky', 'Front:Pages', 'default', '', 1, 'pages/?type=1', 'fa-files-o', 0, 1, 1, 1, 3),
                                                                                                                                                                                                                                        (2, 'Aktuality', 'Front:Blog', 'detail', 'blog', 1, 'pages/?type=2', 'fa-newspaper-o', 1, 1, 1, 3, 6),
                                                                                                                                                                                                                                        (2, 'Aktuality', 'Front:Blog', 'detail', 'blog', 1, 'pages/?type=2', 'fa-newspaper-o', 1, 1, 1, 3, 6),
                                                                                                                                                                                                                                        (6, 'Galerie', 'Front:Media', 'album', '', 1, 'pages/?type=6', 'fa-file-image-o', 0, 1, 1, 4, 1),
                                                                                                                                                                                                                                        (8, 'Dokumenty', 'Front:Media', 'folder', '', 1, 'pages?type=8', 'fa-files-o', 1, 1, 1, 6, 9),
                                                                                                                                                                                                                                        (9, 'Šablony', '', 'default', '', 1, 'pages?type=9', 'fa-th', 1, 1, 1, 5, 10);

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

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setkey` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `setvalue` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `description_cs` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `type` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
  `admin_editable` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `settings` (`id`, `setkey`, `setvalue`, `description_cs`, `type`, `admin_editable`) VALUES
                                                                                                                                                   (1, 'blog:short:showPreview', '1', 'Zobrazovat zkrácenou verzi článku v krátké verzi blogu.', 'boolean', 1),
                                                                                                                                                   (2, 'blog:short:showAuthor', '0', 'Zobrazovat autora', 'boolean', 1),
                                                                                                                                                   (3, 'blog:short:showImage', '0', 'Zobrazovat náhled obrázku', 'boolean', 1),
                                                                                                                                                   (4, 'blog:short:showDate', '0', 'Zobrazovat datum vydání', 'boolean', 1),
                                                                                                                                                   (5, 'blog:preview:length', '350', 'Délka náhledového textu (0 znamená nezkrácený text)', 'numeric', 1),
                                                                                                                                                   (6, 'blog_fblike', '1', 'Přidá sdílecí tlačítko na Facebook, pokud je zadán FB účet', 'boolean', 1),
                                                                                                                                                   (21, 'contacts:email:hq', '', 'Vaše e-mailová adresa', NULL, 1),
                                                                                                                                                   (26, 'contacts:residency:contacts_id', '', 'Kontaktní informace o sídlu', 'table:contacts;column:name', 1),
                                                                                                                                                   (32, 'site:admin:adminBarEnabled', '1', 'Navigace administrace v prezentaci', 'boolean', 1),
                                                                                                                                                   (34, 'site:editor:type', 'summernote', 'Který editor bude vybrán. V současnosti Summernote nebo Ace', '', 0),
                                                                                                                                                   (36, 'site:title', '', 'Název stránky', NULL, 1),
                                                                                                                                                   (37, 'site:url:base', '', 'URL adresa', NULL, 1),
                                                                                                                                                   (38, 'maintenance_enabled', '0', 'Stránka v módu údržby', 'boolean', 1),
                                                                                                                                                   (39, 'maintenance_message', 'Stránka je v módu údržby', 'Informace o údržbě pro návštěvníka', NULL, 1),
                                                                                                                                                   (40, 'site_ip_whitelist', '', 'Adresy povolené pro zobrazení obsahu. Oddělujte středníkem', NULL, 1),
                                                                                                                                                   (41, 'site_cookie_whitelist', '', 'Heslo v cookie nutné pro zobrazení obsahu. Vkládá se pomocí querystringu (secretx=pwd)', NULL, 1),
                                                                                                                                                   (42, 'social:ga_code', '', 'Kód Google Analytics', NULL, 1),
                                                                                                                                                   (43, 'social:fb:enabled', '0', 'Povoleno zobrazování Facebooku', 'boolean', 1),
                                                                                                                                                   (44, 'social:fb:type', 'page', 'Typ: účet nebo stránka', NULL, 1),
                                                                                                                                                   (45, 'social:fb:url', '', 'Adresa stránky nebo účtu včetně http', NULL, 1),
                                                                                                                                                   (60, 'appearance:paths:logo', '', 'Obrázek loga ve vysokém rozlišení a barvě', 'local_path', 0),
                                                                                                                                                   (61, 'appearance:paths:favicon:ico', 'favicon.ico', 'Favicon s příponou ico', 'local_path', 0),
                                                                                                                                                   (62, 'appearance_carousel_directions', '0', 'Zobrazit indikátory (levá a pravá šipka)', 'boolean', 1),
                                                                                                                                                   (63, 'appearance_carousel_indicators', '0', 'Zobrazit menu', 'boolean', 1),
                                                                                                                                                   (68, 'media_thumb_dir', 'tn', 'Adresář pro náhledy', NULL, 1),
                                                                                                                                                   (69, 'media_thumb_width', '300', 'Šířka náhledu', NULL, 1),
                                                                                                                                                   (70, 'media_thumb_height', '200', 'Výška náhledu', NULL, 1),
                                                                                                                                                   (71, 'navigation_search_position_top', '1', 'Zobrazit vyhledávací políčko v horní navigaci', 'boolean', 1),
                                                                                                                                                   (72, 'homepage_template', 'Homepage', 'Soubor s vybranou šablonou', '', 1),
                                                                                                                                                   (73, 'appearance_carousel_caption', '1', 'Zobrazit titulek k položce', 'boolean', 1),
                                                                                                                                                   (74, 'navigation_footer_template', 'Footer', 'Soubor s vybranou šablonou pro patičku', '', 1),
                                                                                                                                                   (75, 'navigation_template', 'Navigation', 'Soubor s vybranou šablonou pro hlavičku', '', 1),
                                                                                                                                                   (76, 'pages_template', 'Page', 'Soubor s vybranou šablonou pro běžné stránky', '', 1),
                                                                                                                                                   (77, 'contacts_template', 'Contact', 'Soubor s vybranou šablonou pro stránku kontaktů', '', 1);

CREATE TABLE `snippets` (
  `id` int(11) NOT NULL,
  `keyword` varchar(80) COLLATE utf8_czech_ci NOT NULL,
  `content` text COLLATE utf8_czech_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` char(40) CHARACTER SET latin1 NOT NULL,
  `email` char(80) CHARACTER SET latin1 NOT NULL,
  `name` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `password` char(60) CHARACTER SET latin1 NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_visited` datetime DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT '0',
  `activation` char(40) CHARACTER SET latin1 DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '1',
  `users_roles_id` int(11) DEFAULT '0',
  `login_error` int(11) NOT NULL DEFAULT '0',
  `login_success` int(11) NOT NULL DEFAULT '0',
  `adminbar_enabled` smallint(6) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users` (`id`, `username`, `email`, `name`, `password`, `date_created`, `date_visited`, `state`, `activation`, `type`, `users_roles_id`, `login_error`, `login_success`, `adminbar_enabled`) VALUES
                                                                                                                                                                                                                                                            (1, 'admin', '', '', '$2y$10$DLhMCsYpbB.xHJ501e.xMOvhneiT1U6YypGAcOna/V2kzIGZOwxla', NULL, '', 1, 'smx5anwed2dr', 1, 1, 5, 35, 0);

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

ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `links_category_id` (`links_categories_id`);

ALTER TABLE `links_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

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

ALTER TABLE `pictures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `albums_id` (`pages_id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `snippets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_roles_id` (`users_roles_id`);

ALTER TABLE `users_roles`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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

ALTER TABLE `helpdesk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `helpdesk_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `links_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

ALTER TABLE `menu_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

ALTER TABLE `pages_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `pages_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `pages_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

ALTER TABLE `snippets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `users_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_4` FOREIGN KEY (`countries_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `contacts_ibfk_5` FOREIGN KEY (`contacts_categories_id`) REFERENCES `contacts_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_6` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `contacts_openinghours`
  ADD CONSTRAINT `contacts_openinghours_ibfk_1` FOREIGN KEY (`contacts_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `helpdesk`
  ADD CONSTRAINT `helpdesk_ibfk_1` FOREIGN KEY (`helpdesk_templates_id`) REFERENCES `helpdesk_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `helpdesk_messages`
  ADD CONSTRAINT `helpdesk_messages_ibfk_3` FOREIGN KEY (`helpdesk_id`) REFERENCES `helpdesk` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

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

ALTER TABLE `pages_templates`
  ADD CONSTRAINT `pages_templates_ibfk_1` FOREIGN KEY (`pages_types_id`) REFERENCES `pages_types` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pages_types`
  ADD CONSTRAINT `pages_types_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `pages_types_ibfk_2` FOREIGN KEY (`pages_templates_id`) REFERENCES `pages_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `pages_widgets`
  ADD CONSTRAINT `pages_widgets_ibfk_1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
COMMIT;
