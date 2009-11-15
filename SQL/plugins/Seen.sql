CREATE TABLE `seen` (
  `nick` varchar(255) collate utf8_unicode_ci NOT NULL,
  `channel` varchar(255) collate utf8_unicode_ci NOT NULL,
  `action` enum('PRIVMSG') collate utf8_unicode_ci NOT NULL,
  `text` text collate utf8_unicode_ci NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY  (`nick`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
