<?php
// *** To make HuMo-php work, fill these lines properly! ***
// *** Om humo-php werkend te krijgen onderstaande regels GOED invullen! ***
define("DATABASE_HOST",     "localhost");
define("DATABASE_USERNAME", "root");
define("DATABASE_PASSWORD", "usbw");
define("DATABASE_NAME",     "humo-gen");

// *** DON'T CHANGE ANYTHING BELOW THIS LINE! ***
// *** HIERONDER NIETS WIJZIGEN! ***

// *** Open database using deprecated mysql method ***
// *** $db and $database_check are used for installation and to check database connection ***
@$db=mysql_connect(DATABASE_HOST,DATABASE_USERNAME,DATABASE_PASSWORD);
@$database_check=mysql_select_db(DATABASE_NAME,$db);
if (!$database_check AND !isset($ADMIN) ){
	die('<br><font color=red><b>
	Database is not yet installed! Possible problems:<br>
	- Database not yet installed.<br>
	Go to the <a href="admin">administration area</a> to solve this problem.
	<p>De database is nog niet bereikbaar! Mogelijke oorzaken:<br>
	- De database is nog niet gemaakt.<br>
	Ga naar het <a href="admin">administratie scherm</a> om dit probleem op te lossen.
	</b></font>');
}

// *** Open database using PDO **
//$conn = 'mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME;
//try {
//	$dbh = new PDO($conn,DATABASE_USERNAME,DATABASE_PASSWORD);
//} catch (PDOException $e) {
//	//print "Error!: " . $e->getMessage() . "<br/>";
//	//die();
//}
?>