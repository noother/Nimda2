CREATE TABLE `roulette_stats` (
  `id` int(11) NOT NULL auto_increment,
  `nick` varchar(255) collate utf8_unicode_ci NOT NULL,
  `channel` varchar(255) collate utf8_unicode_ci NOT NULL,
  `played` int(11) NOT NULL default '0',
  `won` int(11) NOT NULL default '0',
  `lost` int(11) NOT NULL default '0',
  `trigger_pulled` int(11) NOT NULL default '0',
  `clicks` int(11) NOT NULL default '0',
  `lastUpdate` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `roulette_text` (
  `id` int(11) NOT NULL auto_increment,
  `text` text collate utf8_unicode_ci NOT NULL,
  `type` enum('kill','miss','lucky','2shots','broke','wait') collate utf8_unicode_ci NOT NULL,
  `author` varchar(255) collate utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;

INSERT INTO `roulette_text` (`id`, `text`, `type`, `author`, `created`) VALUES (3, '%ss Gehirn landet an der Wand!', 'kill', 'NexNo', '2008-01-27 03:31:46'),
(4, 'Das Klicken lässt %s die Luft anhalten...', 'wait', 'NexNo', '2008-01-27 03:31:46'),
(5, 'und er hat Glück!', 'miss', 'NexNo', '2008-01-27 03:32:28'),
(6, 'nichts passiert.', 'miss', 'NexNo', '2008-01-27 03:32:28'),
(7, 'Waffe klemmt.. Glück gehabt', 'lucky', 'memecan', '2008-01-27 03:33:58'),
(8, 'Da hat wohl jemand ne zittrige Hand', '2shots', 'memecan', '2008-01-27 03:33:58'),
(9, '%s hat es überlebt... er muss einen Schutzengel haben.', 'lucky', 'memecan', '2008-01-27 03:35:14'),
(10, '%s hinterlässt eine Sauerei... holt mal jemand schnell die Putzfrau', 'kill', 'memecan', '2008-01-27 03:35:14'),
(11, 'und die Kugel tötet ihn.', 'kill', 'NexNo', '2008-01-27 03:58:20'),
(12, '%s drückt ab...', 'wait', 'NexNo', '2008-01-27 03:58:58');
