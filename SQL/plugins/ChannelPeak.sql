CREATE TABLE `channel_peaks` (
  `id` int(11) NOT NULL auto_increment,
  `channel` varchar(255) collate utf8_unicode_ci NOT NULL,
  `users` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;