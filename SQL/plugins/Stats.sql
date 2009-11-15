CREATE TABLE `stats` (
  `id` int(11) NOT NULL auto_increment,
  `channel` varchar(255) collate utf8_unicode_ci NOT NULL,
  `nick` varchar(255) collate utf8_unicode_ci NOT NULL,
  `chars` int(11) NOT NULL,
  `words` int(11) NOT NULL,
  `lines` int(11) NOT NULL,
  `actions` int(11) NOT NULL,
  `smilies` int(11) NOT NULL,
  `kicks` int(11) NOT NULL,
  `kicked` int(11) NOT NULL,
  `modes` int(11) NOT NULL,
  `topics` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `channel` (`channel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
