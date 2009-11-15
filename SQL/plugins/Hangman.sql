CREATE TABLE `hangman_stats` (
  `id` int(11) NOT NULL auto_increment,
  `channel` varchar(255) collate utf8_unicode_ci NOT NULL,
  `nick` varchar(255) collate utf8_unicode_ci NOT NULL,
  `points` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `last_played` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `hangman` (
  `id` int(11) NOT NULL auto_increment,
  `word` varchar(255) collate utf8_unicode_ci NOT NULL,
  `author` varchar(255) collate utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

INSERT INTO `hangman` (`id`, `word`, `author`, `created`) VALUES (1, 'Computer', 'noother', '2008-03-24 22:35:25');
