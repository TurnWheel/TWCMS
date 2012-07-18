-- Tracks all emails sent through tw_sendmail
CREATE TABLE email (
	emailid int(10) auto_increment NOT NULL,
	`to` TINYTEXT NOT NULL, -- List of email addresses sent to (comma separated)
	subject varchar(255) NOT NULL, -- Subject of remail
	body TEXT NOT NULL, -- Full body of email
	headers TINYTEXT NOT NULL, -- Additional headers sent in email
	date int(10) NOT NULL, -- Date sent (unix timestamp)
	flags int(10) NOT NULL, -- Misc Bit Flags
	PRIMARY KEY (emailid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
