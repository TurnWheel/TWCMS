-- Form Data
CREATE TABLE forms (
	entryid int(10) NOT NULL auto_increment, -- Unique Entry ID
	data LONGTEXT NOT NULL, -- Form data
	name varchar(50) NOT NULL, -- Name of form
	date int(10) NOT NULL, -- Unix Timestamp
	PRIMARY KEY (entryid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
