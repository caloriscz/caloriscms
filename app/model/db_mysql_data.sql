-- Data

INSERT INTO `categories` (`id`, `parent_id`, `description`, `title`, `sorted`) VALUES
(1, NULL, NULL, 'Nastavení', 145),
(2, NULL, '', 'Kontakty', 43),
(3, NULL, '', 'Ceník', 54),
(4, NULL, '', 'Links', 57),
(5, NULL, '', 'Členové', 60),
(6, 2, NULL, 'Značky zboží', 169),
(7, 2, NULL, 'Úloženka', 177),
(8, 2, NULL, 'Zákazníci', 44),
(9, 2, NULL, 'Kontakty na stránce', 45),
(10, 1, NULL, 'Základní nastavení', 146),
(11, 1, NULL, 'Kategorie', 149),
(12, 1, NULL, 'Konakty členů', 148),
(13, 1, NULL, 'Obchod', 147),
(14, 13, NULL, 'Bonus', 167),
(15, 1, NULL, 'Blog', 154),
(16, 1, NULL, 'Kontakty', 166),
(17, 1, NULL, 'Sociální sítě', 176),
(18, 2, NULL, 'Místa k vyzvednutí', 46),
(19, 2, NULL, 'Newsletter', 153),
(20, 1, NULL, 'Vzhled', 154),
(21, 20, NULL, 'Carousel', 155);

INSERT INTO `countries` (`id`, `title_cs`, `title_en`, `show`) VALUES
(1, 'Česká Republika', 'Czech Republic', 1),
(2, 'Slovensko', 'Slovakia', 1);

INSERT INTO `helpdesk` (`id`, `title`, `description`, `fill_phone`) VALUES
(1, 'Kontaktní formulář', 'Tento formulář slouží Vašim zákazníkům, aby vás mohli kontaktovat ohledně jejich otázek nebo potávek.', 1),
(2, 'Vytvoření nové transakce', 'Když je objednávka vytvořena, je odeslán uživateli mail a zároveň mail administrátorovi.', 0);

INSERT INTO `helpdesk_emails` (`id`, `template`, `subject`, `body`, `helpdesk_id`) VALUES
(1, 'request-admin-email', 'Poptávka', '            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">        <title>{$settings[''site:title'']}</title>        <style>        </style>                <table style="width: 800px; margin: 0 auto 0 auto;">            <tbody><tr>                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;                     font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">{$settings[''site:title'']}</td>            </tr>            <tr>                <td style="text-align: left;">                    <br>                    {$name}<br>                    {$phone}<br>                    {$email}<br>                    <br><br>                    {$message}<br>                    {$time}<br>                    {$address}                </td>            </tr>        </tbody></table>    ', 1),
(2, 'request-customer-email', 'Poptávka', '            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">        <title>{$settings[''site:title'']}</title>        <style>        </style>                <table style="width: 800px; margin: 0 auto 0 auto;">            <tbody><tr>                <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;                     font-size: 1.82em; height: 80px; border-bottom: 4px solid #ac3535; font-weight: bold; padding-left: 20px;">{$settings[''site:title'']}</td>            </tr>            <tr>                <td style="text-align: left;">                    Děkujeme za Vaši zprávu. Budeme Vás brzy kontaktovat.                    <br>                    {$name}<br>                    {$phone}<br>                    {$email}<br>                    <br><br>                    {$message}<br>                </td>            </tr>        </tbody></table>    ', 1),
(3, 'state-1-auto', 'Objednávka od zákazníka', '{var $orderDb = $order->related(''orders_ids'', ''orders_id'')}\n{if $orderDb->count() > 0}\n    {var $orderId = $orderDb->fetch()}\n    {var $transId = ''Objednávka '' . $orderId->identifier}\n{else}\n    {var $transId = ''Transkace č. '' . str_pad($order->id, 6, ''0'', STR_PAD_LEFT)}\n{/if}\n\n<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\n        "http://www.w3.org/TR/html4/loose.dtd">\n<html lang="cs">\n<head>\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\n    <title>{$settings[''site:title'']}: {$transId}</title>\n    <style n:syntax="off">\n        * {\n            font-family: Arial;\n        }\n\n        .text-right {\n            text-align: right;\n        }\n    </style>\n</head>\n<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0"\n      style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\n\n<table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\n    <tr>\n        <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\n            {$transId}\n        </td>\n    </tr>\n    <tr>\n        <td>\n            Vaše objednávka byla přijata. O jejím průběhu budete informování e-mailem.\n        </td>\n    </tr>\n    <tr>\n        <td style="text-align: left;">\n            <br/>\n            <p>Doručovací metoda: {$order->store_settings_shipping->title}</p>\n\n            {if $pickup}\n                <p>Vyzvednutí na adrese:</p>\n\n            {$pickup->company}</br>\n            {$pickup->street}</br>\n            {$pickup->zip} {$pickup->city}</br>\n            {/if}\n\n            <p>Platební metoda: {$order->store_settings_payments->title}</p>\n            <p>E-mail: {$order->email}</p>\n            <p>Telefon: {$order->phone}</p>\n\n            <h3>Fakturační adresa</h3>\n\n            {$order->contacts->name}<br/>\n            {$order->contacts->company}<br/>\n            {$order->contacts->street}<br/>\n            {$order->contacts->zip} {$order->contacts->city}<br/>\n\n            {if $order->contacts->vatin}<p>IČ: {$order->contacts->vatin}</p>{/if}\n            {if $order->contacts->vatin}<p>DIČ: {$order->contacts->vatid}</p>{/if}\n            <br/>\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name && $order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <h3>Doručovací adresa</h3>\n            {/if}\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name}{$order->ref(''contacts'', ''delivery_contacts_id'')->name}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->company}{$order->ref(''contacts'', ''delivery_contacts_id'')->company}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->street}{$order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                {$order->ref(''contacts'', ''delivery_contacts_id'')->zip} {$order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                <br/><br/>\n            {/if}\n            {if $order->note}\n                <div class="well">\n                    <p>Zpráva:<br/>{$order->note}</p>\n                </div>\n            {/if}\n\n            {if $order->store_bonus_id}\n                <h2>Bonus</h2>\n\n                Váš dárek: {$order->store_bonus->stock->store->title}.\n            {/if}\n\n\n            <h2>Shrnutí objednávky</h2>\n\n            <table style="width: 100%;">\n                <tr>\n                    <th style="text-align: left;">Název</th>\n                    <th class="text-right">Cena/kus</th>\n                    <th class="text-right">DPH</th>\n                    <th class="text-right">Sazba DPH</th>\n                    <th class="text-right">Množství</th>\n                    <th class="text-right">Cena celkem</th>\n                </tr>\n                {foreach $order->related(''orders_items'', ''orders_id'') as $item}\n                    {if $settings[''store:order:isVatIncluded'']}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">{var $vat = round($item->price * $vatCoef, 0)}\n                                {$item->price - $vat} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">{number_format($item->price * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                    {var $amount += $item->amount}\n                    {var $total += $item->price * $item->amount}\n                    {var $weight += $item->stock->weight * $item->amount}\n                    {else}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">\n                                {var $vat = $item->price * ($item->store_settings_vats->vat/100)}\n                                {number_format($item->price - $vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">\n                                {number_format(($item->price * (1+($item->store_settings_vats->vat/100))) * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                        {var $amount += $item->amount}\n                        {var $total += $item->price * $item->amount}\n                        {var $weight += $item->stock->weight * $item->amount}\n                    {/if}\n                {/foreach}\n                <tr>\n                    <td colspan="5">Poštovné</td>\n                    <td class="text-right">{$order->shipping_price} {$settings[''store:currency:symbol'']}\n                    </td>\n                </tr>\n                {if $order->payment_price != 0}\n                    <tr>\n                        <td colspan="5">{$order->store_settings_payments->payment}</td>\n                        <td class="text-right">{$order->payment_price} {$settings[''store:currency:symbol'']}</td>\n                    </tr>\n                {/if}\n                <tr>\n                    <td>\n                        CELKEM\n                    </td>\n                    <td></td>\n                    <td></td>\n                    <td></td>\n                    <td class="text-right">{$amount}</td>\n                    <td class="text-right">{number_format($total + $order->shipping_price + $order->payment_price,  0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n            </table>\n\n        </td>\n    </tr>\n</table>\n\n</body>\n</html>', 2),
(4, 'state-1-admin', 'Nová objednávka', '{var $orderDb = $order->related(''orders_ids'', ''orders_id'')}\n\n{if $orderDb->count() > 0}\n    {var $orderId = $orderDb->fetch()}\n    {var $transId = ''Objednávka '' . $orderId->identifier}\n{else}\n    {var $transId = ''Transkace č. '' . str_pad($order->id, 6, ''0'', STR_PAD_LEFT)}\n{/if}\n\n<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\n        "http://www.w3.org/TR/html4/loose.dtd">\n<html lang="cs">\n<head>\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">\n    <title>{$settings[''site:title'']}: Transakce č. {$transId}</title>\n    <style n:syntax="off">\n        * {\n            font-family: Arial;\n        }\n\n        .text-right {\n            text-align: right;\n        }\n    </style>\n</head>\n<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0"\n      style="width:100% !important; text-align: center; font-family: Arial sans-serif;">\n\n<table style="width: 800px; margin: 0 auto 0 auto; font-family: Arial sans-serif;">\n    <tr>\n        <td colspan="2" style="color: white; vertical-align: middle; background-color: #8e8e8e;\n                    font-size: 1.82em; height: 60px; border-bottom: 4px solid #0064b4; font-weight: bold; padding-left: 20px;">\n            {$transId}\n        </td>\n    </tr>\n    <tr>\n        <td style="text-align: left;">\n            <br/>\n            <p>Doručovací metoda: {$order->store_settings_shipping->title}</p>\n\n            {if $pickup}\n                <p>Vyzvednutí na adrese:</p>\n\n            {$pickup->company}</br>\n            {$pickup->street}</br>\n            {$pickup->zip} {$pickup->city}</br>\n            {/if}\n\n            <p>Platební metoda: {$order->store_settings_payments->title}</p>\n            <p>E-mail: {$order->email}</p>\n            <p>Telefon: {$order->phone}</p>\n\n            <h3>Fakturační adresa</h3>\n\n            {$order->contacts->name}<br/>\n            {$order->contacts->company}<br/>\n            {$order->contacts->street}<br/>\n            {$order->contacts->zip} {$order->contacts->city}<br/>\n\n            {if $order->contacts->vatin}<p>IČ: {$order->contacts->vatin}</p>{/if}\n            {if $order->contacts->vatin}<p>DIČ: {$order->contacts->vatid}</p>{/if}\n            <br/>\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name && $order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <h3>Doručovací adresa</h3>\n            {/if}\n\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->name}{$order->ref(''contacts'', ''delivery_contacts_id'')->name}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->company}{$order->ref(''contacts'', ''delivery_contacts_id'')->company}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->street}{$order->ref(''contacts'', ''delivery_contacts_id'')->street}\n                <br/>{/if}\n            {if $order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                {$order->ref(''contacts'', ''delivery_contacts_id'')->zip} {$order->ref(''contacts'', ''delivery_contacts_id'')->city}\n                <br/><br/>\n            {/if}\n            {if $order->note}\n                <div class="well">\n                    <p>Zpráva:<br/>{$order->note}</p>\n                </div>\n            {/if}\n\n            {if $order->store_bonus_id}\n                <h2>Bonus</h2>\n\n                Váš dárek: {$order->store_bonus->stock->store->title}.\n            {/if}\n\n\n            <h2>Shrnutí objednávky</h2>\n\n            <table style="width: 100%;">\n                <tr>\n                    <th style="text-align: left;">Název</th>\n                    <th class="text-right">Cena/kus</th>\n                    <th class="text-right">DPH</th>\n                    <th class="text-right">Sazba DPH</th>\n                    <th class="text-right">Množství</th>\n                    <th class="text-right">Cena celkem</th>\n                </tr>\n                {foreach $order->related(''orders_items'', ''orders_id'') as $item}\n                    {if $settings[''store:order:isVatIncluded'']}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">{var $vat = round($item->price * $vatCoef, 0)}\n                                {$item->price - $vat} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">{number_format($item->price * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                    {var $amount += $item->amount}\n                    {var $total += $item->price * $item->amount}\n                    {var $weight += $item->stock->weight * $item->amount}\n                    {else}\n                        <tr>\n                            <td>{$item->pages->title}</td>\n                            <td class="text-right">\n                                {var $vat = $item->price * ($item->store_settings_vats->vat/100)}\n                                {number_format($item->price - $vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}\n                            </td>\n                            <td class="text-right">{number_format($vat, 0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                            <td class="text-right">{$item->store_settings_vats->vat}%</td>\n                            <td class="text-right">{$item->amount}</td>\n                            <td class="text-right">\n                                {number_format(($item->price * (1+($item->store_settings_vats->vat/100))) * $item->amount, 0, '','', '' '')}\n                                {$settings[''store:currency:symbol'']}\n                            </td>\n                        </tr>\n                        {var $amount += $item->amount}\n                        {var $total += $item->price * $item->amount}\n                        {var $weight += $item->stock->weight * $item->amount}\n                    {/if}\n                {/foreach}\n                <tr>\n                    <td colspan="5">Poštovné</td>\n                    <td class="text-right">{$order->shipping_price} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n                {if $order->payment_price != 0}\n                    <tr>\n                        <td colspan="5">{$order->store_settings_payments->payment}</td>\n                        <td class="text-right">{$order->payment_price} {$settings[''store:currency:symbol'']}</td>\n                    </tr>\n                {/if}\n                <tr>\n                    <td>\n                        CELKEM\n                    </td>\n                    <td></td>\n                    <td></td>\n                    <td></td>\n                    <td class="text-right">{$amount}</td>\n                    <td class="text-right">{number_format($total + $order->shipping_price + $order->payment_price,  0, '','', '' '')} {$settings[''store:currency:symbol'']}</td>\n                </tr>\n            </table>\n\n            <br/><br/>\n\n            <br/><br/>\n\n        </td>\n    </tr>\n</table>\n\n</body>\n</html>', 2);

INSERT INTO `pages_types` (`id`, `content_type`, `presenter`, `action`) VALUES
(1, 'Page', 'Front:Services', 'default'),
(2, 'Blog', 'Front:Blog', 'detail'),	
(3, 'Event', 'Front:Events', 'detail'),
(4, 'Product', 'FrontStore:Product', 'default'),
(5, 'Contacts', 'Front:Contacts', 'detail'),
(6, 'Galerie', 'Front:Gallery', 'album'),
(7, 'Product Category', 'FrontStore:Catalogue', 'default'),
(8, 'Dokumenty', 'Front:Documents', 'default'),
(9, 'Template', '', '');

INSERT INTO `pages` (`slug`, `title`, `document`, `preview`, `pages_id`, `users_id`, `public`, `metadesc`, `metakeys`, `date_created`, `date_published`, `pages_types_id`, `sorted`, `editable`, `presenter`) VALUES
('', 'Homepage', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 0, 1, 0, 'Front:Homepage'),
('kontakt', 'Kontakty', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 0, 2, 0, 'Front:Contact'),
('blog', 'Blog', NULL, NULL, NULL, NULL, 1, '', '', NULL, NULL, 0, 3, 0, 'Front:Blog');

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
(10, 'site:ga_code', '', 'Kód Google Analytics', 'Google Analytics code', NULL, 1),
(10, 'site:url:base', '', 'URL adresa', 'URL address', NULL, 1),
(17, 'social:fb:enabled', '1', 'Povolené zobrazování Facebooku', 'Facebook enabled', 'boolean', 1),
(17, 'social:fb:type', 'page', 'Typ: účet nebo stránka', 'Type: account or page', NULL, 1),
(17, 'social:fb:url', '', 'Adresa stránky nebo účtu včetně http', 'Address of the page or account including http', NULL, 1),
(13, 'store:enabled', '0', 'Provozujete obchod?', 'Is it store?', 'boolean', 1),
(13, 'store:stock:hideEmpty', '1', 'Schovávat zboží, které není na skladu', 'Hide products not in stock', 'boolean', 1),
(13, 'store:new:days', '14', 'Počet dní, kdy je produkt označován jako nový', 'Number of days when product is displayed as new', NULL, 1),
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

INSERT INTO `users` (`id`, `username`, `categories_id`, `uid`, `email`, `sex`, `name`, `password`, `date_created`, `date_visited`, `state`, `activation`, `newsletter`, `type`, `users_roles_id`, `login_error`, `login_success`) VALUES
(1, 'admin', NULL, '000001', '', 2, '', '$2y$10$DLhMCsYpbB.xHJ501e.xMOvhneiT1U6YypGAcOna/V2kzIGZOwxla', NULL, NULL, 1, NULL, 1, 1, 1, 3, 7);


INSERT INTO `users_roles` (`id`, `title`, `admin_access`, `appearance_images`, `helpdesk_edit`, `settings_edit`, `members_display`, `members_edit`, `members_create`, `members_delete`) VALUES
(1, 'Admin', 1, 0, 1, 1, 1, 1, 1, 1),
(2, 'Super User', 1, 0, 1, 1, 1, 1, 1, 1),
(3, 'Editor', 1, 0, 1, 0, 0, 0, 0, 0),
(4, 'Site User', 0, 0, 0, 0, 0, 0, 0, 0);

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