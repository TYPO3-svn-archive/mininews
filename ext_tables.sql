# TYPO3 CVS ID: $Id$
#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_mininews_frontpage_list int(11) unsigned DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_mininews_news'
#
CREATE TABLE tx_mininews_news (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	datetime int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	teaser text NOT NULL,
	full_text text NOT NULL,
	front_page tinyint(3) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);