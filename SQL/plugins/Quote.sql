
CREATE TABLE `quotes` (
  `id` int(11) NOT NULL auto_increment,
  `quote` text collate utf8_unicode_ci NOT NULL,
  `author` varchar(255) collate utf8_unicode_ci NOT NULL,
  `views` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
