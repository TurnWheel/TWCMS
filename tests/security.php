#!/usr/bin/php
<?php
define('SECURITY', TRUE);
$cfg = array(
	'hash_algo' => 'sha512',
	'enc_algo' => 'blowfish', // Algorithm for generating two-way enc keys (Default: MCRYPT_3DES); see mcrypt.ciphers for options
	'enc_key' => '!@1111---special', // Seed key for enc_algo (NOTE: CHANGE ONLY DURING INITIAL INSTALL)
);

require '../lib/security.inc.php';

// Generate hash
$pass = 'MySecretPass';

$hash1 = tw_genhash($pass);
$hash2 = tw_genhash($pass, TRUE, &$salt2);
$chk1 = tw_chkhash($pass, $hash1) ? 'TRUE' : 'FALSE';
$chk2 = tw_chkhash($pass, $hash2, $salt2) ? 'TRUE' : 'FALSE';

// Output to console
print 'Input: '.$pass."\n\n";

print 'Hash--'."\n";

print 'Hash: '.$hash1."\n";
print 'Hash2: '.$hash2."\n";
print 'Salt2: '.$salt2."\n";

print "\n".'Checks--'."\n";

print 'Check: '.$chk1."\n";
print 'Check2: '.$chk2."\n";

// Start encryption
print "\n".'Encryption--'."\n";

$enc = tw_enc($pass);
$dec = tw_dec($enc);

print 'Encrypted: '.$enc."\n";
print 'Decrypted: '.$dec."\n";

$algorithms = mcrypt_list_algorithms('/usr/local/lib/libmcrypt');

foreach ($algorithms as $cipher) {
	echo $cipher."\n";
}
// END
print "\n";
?>
