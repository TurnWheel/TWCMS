<?php
// Simple test
//trigger_error('Just a test error!', E_USER_NOTICE);

// SQL test
sql_query('SELECT name FROM testdoesntexist
			WHERE x = "%d" AND y = "%d"',
				array(1,2), __FILE__, __LINE__);

// EOF
