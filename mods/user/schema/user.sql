-- User Management
CREATE TABLE user (
	userid int(10) auto_increment NOT NULL,
	firstname varchar(100) NOT NULL, -- First Name (Real Name)
	lastname varchar(100) NOT NULL, -- Last Name (Real Name)
	email varchar(200) NOT NULL, -- Email Address
	phone varchar(50) NOT NULL, -- Phone #
	zip varchar(20) NOT NULL, -- Zip Code
	password varchar(255) NOT NULL, -- Password (hashed, up to 255-bit)
	salt varchar(128) NOT NULL, -- Password Salt (up to 128-bit)
	date int(10) NOT NULL, -- Date Created (Unix Timestamp)
	flags int(10) NOT NULL, -- Flags used for permissions, etc.
	PRIMARY KEY (userid),
	UNIQUE (email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Password Recovery Temp Table
CREATE TABLE user_pass (
	recoverid int(10) NOT NULL auto_increment, -- Unique ID String
	hash varchar(200) NOT NULL, -- Unqiue HASH string used in URL
	userid int(10) NOT NULL, -- References user.userid
	date int(10) NOT NULL, -- Date of recovery
	PRIMARY KEY (recoverid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
