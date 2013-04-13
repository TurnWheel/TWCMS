-- Stores errors for error module if savedb is enabled
CREATE TABLE error (
	eid int(10) auto_increment NOT NULL,
	error MEDIUMTEXT NOT NULL, -- Serialized array of error details
	dump MEDIUMTEXT NOT NULL, -- Serialized array of all variables (context)
	trace MEDIUMTEXT NOT NULL, -- Serialized array of debug_backtrace
	date int(10) NOT NULL, -- Date of error (Unix timestamp)
	flags int(10) NOT NULL, -- Misc. Flags
	PRIMARY KEY (eid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
