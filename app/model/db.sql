CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(40) NOT NULL,
  `email` char(80) NOT NULL,
  `password` char(60) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_visited` datetime DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT '0',
  `activation` char(40) DEFAULT NULL,
  `newsletter` int(11) DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '1',
  `role` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

INSERT INTO `users` (`id`, `username`, `email`, `password`, `date_created`, `date_visited`, `state`, `activation`, `newsletter`, `type`, `role`) VALUES
(1, 'admin', 'your@email', '$2y$10$ofufHIW6LPMNHhl8v5E2oeQf.3aKC4l8lBKXN1RBKmQItFOVMk.jy', NULL, NULL, 1, NULL, 1, 1, 1)