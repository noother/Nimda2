CREATE TABLE `uptime` (
  `id` int(11) NOT NULL auto_increment,
  `seconds` int(11) NOT NULL,
  `quitted` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

INSERT INTO `users` (`user`, `level`) VALUES ('root', 100);
