#!/usr/bin/php
<?php
$_start = microtime(TRUE);
$phrase = isset($argv[1]) ? $argv[1] : '';

if ($phrase === '') {
	print 'Enter a phrase to encrypt.'."\n";
	exit;
}

print 'Base64: '.base64_encode($phrase)."\n";
$salt = crypt($phrase, '$5$'.str_shuffle(base64_encode(str_rot13($phrase))).'$');
print 'Salt: '.$salt."\n";

// Encrypt phrase with different methods
$algos = hash_algos();

print 'Encrypting...'."\n";

foreach ($algos AS $type) {
	$hash = hash($type, $phrase);
	printf("%-12s %3d %s\n",$type, strlen($hash), $hash);
	
	// WITH salt
	$hash = hash($type, $phrase.$salt);
	printf("%-12s %3d %s *\n",$type, strlen($hash), $hash);
}

print "\n";

$_end = microtime(TRUE);
print 'Processed In: '.($_end-$_start).' sec'."\n";
?>
