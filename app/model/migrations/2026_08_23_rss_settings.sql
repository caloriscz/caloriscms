INSERT INTO `settings` (`setkey`, `setvalue`, `description_cs`, `type`, `admin_editable`)
SELECT 'rss:enabled', '0', 'Povolit RSS feed aktualit', 'boolean', 1
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setkey` = 'rss:enabled');

INSERT INTO `settings` (`setkey`, `setvalue`, `description_cs`, `type`, `admin_editable`)
SELECT 'rss:title', 'Aktuality', 'Název RSS feedu', NULL, 1
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setkey` = 'rss:title');

INSERT INTO `settings` (`setkey`, `setvalue`, `description_cs`, `type`, `admin_editable`)
SELECT 'rss:description', '', 'Popis RSS feedu', NULL, 1
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setkey` = 'rss:description');

INSERT INTO `settings` (`setkey`, `setvalue`, `description_cs`, `type`, `admin_editable`)
SELECT 'rss:limit', '20', 'Počet položek v RSS feedu', 'numeric', 1
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setkey` = 'rss:limit');

INSERT INTO `settings` (`setkey`, `setvalue`, `description_cs`, `type`, `admin_editable`)
SELECT 'rss:tags', '', 'Vyhrazeno pro budoucí filtrování podle tagů', NULL, 1
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setkey` = 'rss:tags');
