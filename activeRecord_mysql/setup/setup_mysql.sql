CREATE TABLE `page` (
 `id` int(11) DEFAULT NULL,
 `title` varchar(2000) DEFAULT NULL,
 `keywords` text,
 `description` text,
 `url` varchar(2000) DEFAULT NULL,
 `name` varchar(2000) DEFAULT NULL,
 `layout` varchar(2000) DEFAULT NULL,
 `blocks` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
 `id` int(11) DEFAULT NULL,
 `login` varchar(2000) DEFAULT NULL,
 `password` varchar(2000) DEFAULT NULL,
 `email` varchar(2000) DEFAULT NULL,
 `name` varchar(2000) DEFAULT NULL,
 `sex` varchar(2000) DEFAULT NULL,
 `avatar` varchar(2000) DEFAULT NULL,
 `photo` varchar(2000) DEFAULT NULL,
 `country` varchar(2000) DEFAULT NULL,
 `city` varchar(2000) DEFAULT NULL,
 `active` int(11) DEFAULT NULL,
 `last_visit` int(12) NOT NULL,
 `role` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `blog` (
 `id` int(11) DEFAULT NULL,
 `title` varchar(2000) DEFAULT NULL,
 `description` text,
 `user` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `record` (
 `id` int(11) DEFAULT NULL,
 `title` varchar(2000) DEFAULT NULL,
 `body` text,
 `user` int(11) DEFAULT NULL,
 `date` int(12) DEFAULT NULL,
 `blog` int(11) DEFAULT NULL,
 `active` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `block` (
 `id` int(11) DEFAULT NULL,
 `name` varchar(2000) DEFAULT NULL,
 `value` varchar(2000) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;