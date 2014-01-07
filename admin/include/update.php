<?php
/*
HuMo-gen database update script by Huub Mons.

15-08-2011 Completely rewritten this script, and update for version 4.7.
*/

@set_time_limit(3000);

echo '<h2>UPDATE PROCEDURE</h2>';

echo __('Multiple updates will be done. It is recommended to do a database backup!<br><b>NEVER INTERRUPT THE UPDATE PROCEDURE!</b><br><b>Please wait until the notice that the update has been completed!</b>');

if (!isset($_GET['proceed'])){
	echo '<p><a href="index.php?page=update&proceed=1">START UPDATE PROCEDURE</a>';
}
else{
	// *** UPDATE PROCEDURES ****************************************************************
	//$update='';
	$humo_update=0;
	if (isset($_SESSION['save_humo_update'])){ $humo_update=$_SESSION['save_humo_update']; }

	echo '<p><table class="humo">';
	echo '<tr class="table_header"><th colspan="2">'.__('HuMo-gen status').'</th></tr>';


	// ********************************
	// *** HuMo-gen update 1 (V3.1) ***
	// ********************************
	$update_check = @mysql_query("SELECT * FROM humo_tree_texts",$db);
	$update_check2 = @mysql_query("SELECT * FROM humo_stambomen_tekst",$db);
	if ($update_check OR $update_check2){
		echo '<tr><td>Check table humo_tree_texts</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>Check table humo_tree_texts</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';
		$db_update = mysql_query("CREATE TABLE humo_tree_texts (
			treetext_id smallint(5) unsigned NOT NULL auto_increment,
			treetext_tree_id smallint(5),
			treetext_language varchar(100) CHARACTER SET utf8,
			treetext_name varchar(100) CHARACTER SET utf8,
			treetext_mainmenu_text text CHARACTER SET utf8,
			treetext_mainmenu_source text CHARACTER SET utf8,
			treetext_family_top text CHARACTER SET utf8,
			treetext_family_footer text CHARACTER SET utf8,
			PRIMARY KEY  (`treetext_id`)
			) DEFAULT CHARSET=utf8") or die(mysql_error());

		// *** Re-check ***
		$update_check2 = mysql_query("SELECT * FROM humo_tree_texts",$db);
		if ($update_check2){
			echo __('UPDATE OK!');
		}
		else{
			echo __('UPDATE FAILED!');
		}

		$datasql = mysql_query("SELECT * FROM humo_stambomen ORDER BY volgorde",$db);
		if ($datasql){
			while ($dataDb=mysql_fetch_object($datasql)){
				$sql="INSERT INTO humo_tree_texts SET
				treetext_tree_id='".$dataDb->id."',
				treetext_language='nl',
				treetext_name='".$dataDb->naam."',
				treetext_mainmenu_text='".$dataDb->tekst."',
				treetext_mainmenu_source='".$dataDb->bron."',
				treetext_family_top='Familypage'
				";
				$db_update = mysql_query($sql) or die(mysql_error());
			}
		}

		echo '</td></tr>';
	}


	// ********************************
	// *** HuMo-gen update 4 (V4.6) ***
	// ********************************
	$update_check_sql = mysql_query("SELECT * FROM humo_tree_texts");
	if ($update_check_sql){
		echo '<tr><td>Check table humo_tree_texts 2</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>Check table humo_tree_texts 2</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';
		// *** Translate dutch table name into english ***
		$sql='ALTER TABLE humo_stambomen_tekst RENAME humo_tree_texts';
		$update_Db = mysql_query($sql);

		$sql='ALTER TABLE humo_tree_texts
			CHANGE tekst_id treetext_id smallint(5) unsigned NOT NULL auto_increment,
			CHANGE stamboom_id treetext_tree_id smallint(5),
			CHANGE taal treetext_language varchar(100) CHARACTER SET utf8,
			CHANGE stamboom_naam treetext_name varchar(100) CHARACTER SET utf8,
			CHANGE hoofdmenu_tekst treetext_mainmenu_text text CHARACTER SET utf8,
			CHANGE hoofdmenu_bron treetext_mainmenu_source text CHARACTER SET utf8,
			CHANGE gezin_kop treetext_family_top text CHARACTER SET utf8,
			CHANGE gezin_voet treetext_family_footer text CHARACTER SET utf8
			';
		$update_Db = mysql_query($sql) or die(mysql_error());

		// *** New check ***
		$update_check2 = mysql_query("SELECT * FROM humo_tree_texts",$db);
		if ($update_check2){
			echo __('UPDATE OK!');
		}
		else{
			echo __('UPDATE FAILED!');
		}
		echo '</td></tr>';
	}



	// *********************************
	// *** HuMo-gen update 3 (V 4.2) ***
	// *********************************
	// *** Change names of languages in table humo_tree_texts ***
	$sql='UPDATE humo_tree_texts SET treetext_language="nl"
		WHERE treetext_language="talen/taal-nederlands.php"';
	$result=mysql_query($sql) or die(mysql_error());

	$sql='UPDATE humo_tree_texts SET treetext_language="de"
		WHERE treetext_language="talen/taal-deutsch.php"';
	$result=mysql_query($sql) or die(mysql_error());

	$sql='UPDATE humo_tree_texts SET treetext_language="en"
		WHERE treetext_language="talen/taal-english.php"';
	$result=mysql_query($sql) or die(mysql_error());

	$sql='UPDATE humo_tree_texts SET treetext_language="fr"
		WHERE treetext_language="talen/taal-francais.php"';
	$result=mysql_query($sql) or die(mysql_error());



	// ********************************
	// *** HuMo-gen update 2 (V3.2) ***
	// ********************************
	$update_check = mysql_query("SELECT * FROM humo_stat_date",$db);
	$update_check2 = mysql_query("SELECT * FROM humo_stat_datum",$db);
	if ($update_check OR $update_check2) {
		echo '<tr><td>Check table humo_stat_date</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>Check table humo_stat_date</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';

		$db_update = mysql_query("CREATE TABLE humo_stat_date (
			stat_id int(10) NOT NULL auto_increment,
			stat_easy_id varchar(100) CHARACTER SET utf8,
			stat_ip_address varchar(20) CHARACTER SET utf8,
			stat_user_agent varchar(255) CHARACTER SET utf8,
			stat_tree_id varchar(5) CHARACTER SET utf8,
			stat_gedcom_fam varchar(20) CHARACTER SET utf8,
			stat_gedcom_man varchar(20) CHARACTER SET utf8,
			stat_gedcom_woman varchar(20) CHARACTER SET utf8,
			stat_date_stat datetime NOT NULL,
			stat_date_linux varchar(50) CHARACTER SET utf8,
			PRIMARY KEY (`stat_id`)
		) DEFAULT CHARSET=utf8") or die(mysql_error());

		// *** New check ***
		$update_check2 = mysql_query("SELECT * FROM humo_stat_date",$db);
		if ($update_check2){
			echo __('UPDATE OK!');
		}
		else{
			echo __('UPDATE FAILED!');
		}
		echo '</td></tr>';
	}


	$update_check_sql = mysql_query("SELECT * FROM humo_stat_date",$db);
	if ($update_check_sql){
		echo '<tr><td>Check table humo_stat_date 2</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>Check table humo_stat_date 2</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';

		// *** Translate dutch table name into english ***
		$sql='ALTER TABLE humo_stat_datum RENAME humo_stat_date';
		$update_Db = mysql_query($sql);

		$sql='ALTER TABLE humo_stat_date
			CHANGE id stat_id int(10) NOT NULL auto_increment,
			CHANGE samengesteld_id stat_easy_id varchar(100) CHARACTER SET utf8,
			CHANGE ip_adres stat_ip_address varchar(20) CHARACTER SET utf8,
			CHANGE stamboom_id stat_tree_id varchar(5) CHARACTER SET utf8,
			CHANGE gedcom_gezin stat_gedcom_fam varchar(20) CHARACTER SET utf8,
			CHANGE gedcom_man stat_gedcom_man varchar(20) CHARACTER SET utf8,
			CHANGE gedcom_vrouw stat_gedcom_woman varchar(20) CHARACTER SET utf8,
			CHANGE datum_stat stat_date_stat datetime NOT NULL,
			CHANGE datum_linux stat_date_linux varchar(50) CHARACTER SET utf8
			';
		$update_Db = mysql_query($sql) or die(mysql_error());

		// *** New check ***
		$update_check2 = mysql_query("SELECT * FROM humo_stat_date",$db);
		if ($update_check2){
			echo __('UPDATE OK!');
		}
		else{
			echo __('UPDATE FAILED!');
		}
		echo '</td></tr>';
	}

	// *** Automatic installation or update ***
	if (isset($field)){ unset ($field); }
	$column_qry = mysql_query('SHOW COLUMNS FROM humo_stat_date');
	while ($columnDb = mysql_fetch_assoc($column_qry)) {
		$field_value=$columnDb['Field'];
		$field[$field_value]=$field_value;
		// *** test line ***
		//print '<span>'.$field[$field_value].'</span><br />';
	}
	if (isset($field['stat_user_agent'])){
		$sql="ALTER TABLE humo_stat_date
			CHANGE stat_user_agent stat_user_agent varchar(255) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['stat_user_agent'])){
		$sql="ALTER TABLE humo_stat_date
			ADD stat_user_agent VARCHAR(255) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}


	// ********************************
	// *** HuMo-gen update 4 (V4.6) ***
	// ********************************
	$update_check_sql = mysql_query("SELECT * FROM humo_settings",$db);
	if ($update_check_sql){
		echo '<tr><td>Check table humo_settings</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>Check table humo_settings</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';

		// *** Translate dutch table name into english ***
		$sql='ALTER TABLE humo_instellingen RENAME humo_settings';
		$update_Db = mysql_query($sql);

		$sql='ALTER TABLE humo_settings
			CHANGE id setting_id smallint(5) unsigned NOT NULL auto_increment,
			CHANGE variabele setting_variable varchar(50) CHARACTER SET utf8,
			CHANGE waarde setting_value text CHARACTER SET utf8
			';
		$update_Db = mysql_query($sql) or die(mysql_error());

		// *** New check ***
		$update_check2 = mysql_query("SELECT * FROM humo_settings",$db);
		if ($update_check2){
			echo __('UPDATE OK!');
		}
		else{
			echo __('UPDATE FAILED!');
		}
		echo '</td></tr>';
	}

	// *** Change default languages in table humo_settings ***
	$sql='UPDATE humo_settings SET setting_value="nl" WHERE setting_value="languages/nederlands.php"';
	$result=mysql_query($sql) or die(mysql_error());

	$sql='UPDATE humo_settings SET setting_value="nl" WHERE setting_value="talen/taal-nederlands.php"';
	$result=mysql_query($sql) or die(mysql_error());

	$sql='UPDATE humo_settings SET setting_value="en" WHERE setting_value="languages/english.php"';
	$result=mysql_query($sql) or die(mysql_error());

	$sql='UPDATE humo_settings SET setting_value="en" WHERE setting_value="talen/taal-english.php"';
	$result=mysql_query($sql) or die(mysql_error());

	$sql='UPDATE humo_settings SET setting_value="de" WHERE setting_value="talen/taal-deutsch.php"';
	$result=mysql_query($sql) or die(mysql_error());

	$sql='UPDATE humo_settings SET setting_value="fr" WHERE setting_value="talen/taal-francais.php"';
	$result=mysql_query($sql) or die(mysql_error());



	$update_check_sql = mysql_query("SELECT * FROM humo_trees",$db);
	if ($update_check_sql){
		echo '<tr><td>Check table humo_trees</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>Check table humo_trees</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';
		// *** Translate dutch table name into english ***
		$sql='ALTER TABLE humo_stambomen RENAME humo_trees';
		$update_Db = mysql_query($sql);

		$sql='ALTER TABLE humo_trees
			CHANGE id tree_id smallint(5) unsigned NOT NULL auto_increment,
			CHANGE volgorde tree_order smallint(5),
			CHANGE voorvoegsel tree_prefix varchar(10) CHARACTER SET utf8,
			CHANGE datum tree_date varchar(20) CHARACTER SET utf8,
			CHANGE personen tree_persons varchar(10) CHARACTER SET utf8,
			CHANGE gezinnen tree_families varchar(10) CHARACTER SET utf8
			';
		$update_Db = mysql_query($sql) or die(mysql_error());

		// *** New check ***
		$update_check2 = mysql_query("SELECT * FROM humo_trees",$db);
		if ($update_check2){
			echo __('UPDATE OK!');
		}
		else{
			echo __('UPDATE FAILED!');
		}
		echo '</td></tr>';
	}


	// *** Automatic installation or update ***
	if (isset($field)){ unset ($field); }
	$column_qry = mysql_query('SHOW COLUMNS FROM humo_trees');
	while ($columnDb = mysql_fetch_assoc($column_qry)) {
		$field_value=$columnDb['Field'];
		$field[$field_value]=$field_value;
		// *** test line ***
		//print '<span>'.$field[$field_value].'</span><br />';
	}

	// *** Automatic installation or update ***
	if (isset($field['email'])){
		$sql="ALTER TABLE humo_trees
			CHANGE email tree_email varchar(100) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['tree_email'])){
		$sql="ALTER TABLE humo_trees
			ADD tree_email VARCHAR(100) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['eigenaar'])){
		$sql="ALTER TABLE humo_trees
			CHANGE eigenaar tree_owner varchar(100) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['tree_owner'])){
		$sql="ALTER TABLE humo_trees
			ADD tree_owner VARCHAR(100) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['afbpad'])){
		$sql="ALTER TABLE humo_trees
			CHANGE afbpad tree_pict_path varchar(100) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['tree_pict_path'])){
		$sql="ALTER TABLE humo_trees
			ADD tree_pict_path VARCHAR(100) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['privacy_stamboom'])){
		$sql="ALTER TABLE humo_trees
			CHANGE privacy_stamboom tree_privacy varchar(100) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['tree_privacy'])){
		$sql="ALTER TABLE humo_trees
			ADD tree_privacy VARCHAR(100) CHARACTER SET utf8";
		$result=mysql_query($sql) or die(mysql_error());
	}


	echo '<tr><td>Check table humo_user_log</td>';
	$update_check_sql = mysql_query("SELECT * FROM humo_user_log",$db);
	if ($update_check_sql){
		echo '<td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		// *** Check if there is an old humo_logboek table ***
		$update_check2_sql = mysql_query("SELECT * FROM humo_logboek",$db);
		if ($update_check2_sql){
			echo '<td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';
			// *** Translate dutch table name into english ***
			$sql='ALTER TABLE humo_logboek RENAME humo_user_log';
			$update_Db = mysql_query($sql);

			$sql='ALTER TABLE humo_user_log
				CHANGE username log_username varchar(25) CHARACTER SET utf8,
				CHANGE datum log_date varchar(20) CHARACTER SET utf8
				';
			$update_Db = mysql_query($sql) or die(mysql_error());

			// *** New check ***
			$update_check2 = mysql_query("SELECT * FROM humo_user_log",$db);
			if ($update_check2){
				echo __('UPDATE OK!');
			}
			else{
				echo __('UPDATE FAILED!');
			}
		}
		else{
			// *** There is no user log table ***
			echo '<td style="background-color:#00FF00">'.__('There is no humo_user_log table');
		}
		echo '</td></tr>';
	}



	// *** Update users ***
	$update_check_sql = mysql_query("SELECT * FROM humo_users",$db);
	$tabel_controleDb=mysql_fetch_object($update_check_sql);
	if (!isset($tabel_controleDb->id)){
		echo '<tr><td>Check table humo_users</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>Check table humo_users</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';

		$sql='ALTER TABLE humo_users
			CHANGE id user_id smallint(5) NOT NULL auto_increment,
			CHANGE username user_name varchar(25) CHARACTER SET utf8,
			CHANGE paswoord user_password varchar(50) CHARACTER SET utf8,
			CHANGE groeps_id user_group_id varchar(1) CHARACTER SET utf8
			';
		$update_Db = mysql_query($sql) or die(mysql_error());

		// *** New check ***
		$update_check2 = mysql_query("SELECT * FROM humo_users",$db);
		if ($update_check2){
			echo __('UPDATE OK!');
		}
		else{
			echo __('UPDATE FAILED!');
		}
		echo '</td></tr>';
	}



	// *** Update groups ***
	$groupsql="SELECT * FROM humo_groups";
	$groupresult=mysql_query($groupsql,$db);
	$groupDb=mysql_fetch_object($groupresult);

	if (!isset($groupDb->id)){
		echo '<tr><td>Check table humo_groups</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>Check table humo_groups</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';
		$sql='ALTER TABLE humo_groups
			CHANGE id group_id smallint(5) unsigned NOT NULL auto_increment,
			CHANGE groepsnaam group_name varchar(25) CHARACTER SET utf8,
			CHANGE privacy group_privacy varchar(1) CHARACTER SET utf8,
			CHANGE plaatsen group_menu_places varchar(1) CHARACTER SET utf8,
			CHANGE beheer group_admin varchar(1) CHARACTER SET utf8,
			CHANGE bronnen group_sources varchar(1) CHARACTER SET utf8,
			CHANGE afbeeldingen group_pictures varchar(1) CHARACTER SET utf8,
			CHANGE gedcomnummer group_gedcomnr varchar(1) CHARACTER SET utf8,
			CHANGE woonplaats group_living_place varchar(1) CHARACTER SET utf8,
			CHANGE plaats group_places varchar(1) CHARACTER SET utf8,
			CHANGE religie group_religion varchar(1) CHARACTER SET utf8,
			CHANGE plaatsdatum group_place_date varchar(1) CHARACTER SET utf8,
			CHANGE soortindex group_kindindex varchar(1) CHARACTER SET utf8,
			CHANGE gebeurtenis group_event varchar(1) CHARACTER SET utf8,
			CHANGE adressen group_addresses varchar(1) CHARACTER SET utf8,
			CHANGE eigencode group_own_code varchar(1) CHARACTER SET utf8,
			CHANGE werktekst group_work_text varchar(1) CHARACTER SET utf8,
			CHANGE teksten group_texts varchar(1) CHARACTER SET utf8,
			CHANGE tekstpersoon group_text_pers varchar(1) CHARACTER SET utf8,
			CHANGE tekstpersgeg group_texts_pers varchar(1) CHARACTER SET utf8,
			CHANGE tekstgezgeg group_texts_fam varchar(1) CHARACTER SET utf8,
			CHANGE levend group_alive varchar(1) CHARACTER SET utf8,
			CHANGE levenddatum group_alive_date_act varchar(1) CHARACTER SET utf8,
			CHANGE levenddatum2 group_alive_date varchar(4) CHARACTER SET utf8,
			CHANGE filterovl group_filter_death varchar(1) CHARACTER SET utf8,
			CHANGE filtertotaal group_filter_total varchar(1) CHARACTER SET utf8,
			CHANGE filternaam group_filter_name varchar(1) CHARACTER SET utf8,
			CHANGE gezinfilter group_filter_fam varchar(1) CHARACTER SET utf8,
			CHANGE persoonfilter group_filter_pers_show_act varchar(1) CHARACTER SET utf8,
			CHANGE filterkarakter group_filter_pers_show varchar(50) CHARACTER SET utf8,
			CHANGE persoonfilter2 group_filter_pers_hide_act varchar(1) CHARACTER SET utf8,
			CHANGE filterkarakter2 group_filter_pers_hide varchar(50) CHARACTER SET utf8
		';
		$update_Db = mysql_query($sql) or die(mysql_error());

		// *** New check ***
		$update_check2 = mysql_query("SELECT * FROM humo_groups",$db);
		if ($update_check2){
			echo __('UPDATE OK!');
		}
		else{
			echo __('UPDATE FAILED!');
		}
		echo '</td></tr>';
	}


	// *** Automatic installation or update ***
	if (isset($field)){ unset ($field); }
	$column_qry = mysql_query('SHOW COLUMNS FROM humo_groups');
	while ($columnDb = mysql_fetch_assoc($column_qry)) {
		$field_value=$columnDb['Field'];
		$field[$field_value]=$field_value;
		// *** test line ***
		//print '<span>'.$field[$field_value].'</span><br />';
	}

	// *** Automatic installation or update ***
	if (isset($field['group_editor'])){
		$sql="ALTER TABLE humo_groups
			CHANGE group_editor group_editor varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_editor'])){
		$sql_update="ALTER TABLE humo_groups
			ADD group_editor VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
		$result=mysql_query($sql_update) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['group_statistics'])){
		$sql="ALTER TABLE humo_groups
			CHANGE group_statistics group_statistics varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_statistics'])){
		$sql="ALTER TABLE humo_groups
			ADD group_statistics VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['verjaardagen_rss'])){
		$sql="ALTER TABLE humo_groups
			CHANGE verjaardagen_rss group_birthday_rss varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_birthday_rss'])){
		$sql="ALTER TABLE humo_groups
			ADD group_birthday_rss VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['verjaardagen_lijst'])){
		$sql="ALTER TABLE humo_groups
			CHANGE verjaardagen_lijst group_birthday_list varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_birthday_list'])){
		$sql="ALTER TABLE humo_groups
			ADD group_birthday_list VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['filterdatum'])){
		$sql="ALTER TABLE humo_groups
			CHANGE filterdatum group_filter_date varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_filter_date'])){
		$sql="ALTER TABLE humo_groups
			ADD group_filter_date VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['gen_protection'])){
		$sql="ALTER TABLE humo_groups
			CHANGE gen_protection group_gen_protection VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_gen_protection'])){
		$sql="ALTER TABLE humo_groups ADD group_gen_protection VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['persoonfilter3'])){
		$sql="ALTER TABLE humo_groups
			CHANGE persoonfilter3 group_pers_hide_totally_act VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_pers_hide_totally_act'])){
		$sql="ALTER TABLE humo_groups
			ADD group_pers_hide_totally_act VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['filterkarakter3'])){
		$sql="ALTER TABLE humo_groups
			CHANGE filterkarakter3 group_pers_hide_totally varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT 'X'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_pers_hide_totally'])){
		$sql="ALTER TABLE humo_groups ADD group_pers_hide_totally VARCHAR(50) CHARACTER SET utf8 NOT NULL DEFAULT 'X';";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['photobook'])){
		$sql="ALTER TABLE humo_groups
			CHANGE photobook group_photobook varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_photobook'])){
		$sql="ALTER TABLE humo_groups ADD group_photobook VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
		$result=mysql_query($sql) or die(mysql_error());
	}

	// *** Automatic installation or update ***
	if (isset($field['hide_trees'])){
		$sql="ALTER TABLE humo_groups
			CHANGE hide_trees group_hide_trees varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT ''";
		$result=mysql_query($sql) or die(mysql_error());
	}
	elseif (!isset($field['group_hide_trees'])){
		$sql="ALTER TABLE humo_groups ADD group_hide_trees VARCHAR( 200 ) NOT NULL DEFAULT '';";
		$result=mysql_query($sql) or die(mysql_error());
	}


	// *** Check for update version 4.6 ***
	echo '<tr><td>HuMo-gen update V4.6</td><td style="background-color:#00FF00">';
	// *** Read all family trees from database ***
	$update_sql = mysql_query("SELECT * FROM humo_trees
		WHERE tree_prefix!='LEEG' AND tree_prefix!='EMPTY' ORDER BY tree_order",$db);
	while ($updateDb=mysql_fetch_object($update_sql)){

		echo '<b>Check '.$updateDb->tree_prefix.'</b>';

		$translate_tables=false;

		// *** Rename old tables, rename fields, convert html to utf-8 ***
		$update_check_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."persoon",$db);		// *** Translate table names, update tables ***
		if ($update_check_sql){

			$translate_tables=true;

			// *** Convert tables into utf-8 ***
			$get_tables = mysql_query("SHOW TABLES");
			while($x = mysql_fetch_array($get_tables)) {
				if (substr($x[0],0,strlen($updateDb->tree_prefix))==$updateDb->tree_prefix){
					// *** Change table into UTF-8 ***
					$update_char='ALTER TABLE '.$x[0].' DEFAULT CHARACTER SET utf8';
					$update_charDb = mysql_query($update_char) or die(mysql_error());
				}
			}

			// *** Translate dutch table name into english ***
			$sql='ALTER TABLE '.$updateDb->tree_prefix.'persoon
				RENAME '.$updateDb->tree_prefix.'person';
			$update_Db = mysql_query($sql) or die(mysql_error());

			$sql='ALTER TABLE '.$updateDb->tree_prefix.'person
				CHANGE id pers_id	mediumint(6) unsigned NOT NULL auto_increment,
				CHANGE gedcomnummer pers_gedcomnumber	varchar(20) CHARACTER SET utf8,
				CHANGE famc pers_famc varchar(50) CHARACTER SET utf8,
				CHANGE fams pers_fams varchar(150) CHARACTER SET utf8,
				CHANGE indexnr pers_indexnr varchar(20) CHARACTER SET utf8,
				CHANGE voornaam pers_firstname varchar(50) CHARACTER SET utf8,
				CHANGE roepnaam pers_callname varchar(50) CHARACTER SET utf8,
				CHANGE voorzetsel pers_prefix varchar(20) CHARACTER SET utf8,
				CHANGE achternaam pers_lastname varchar(50) CHARACTER SET utf8,
				CHANGE patroniem pers_patronym varchar(50) CHARACTER SET utf8,
				CHANGE naamtekst pers_name_text text CHARACTER SET utf8,
				CHANGE naambron pers_name_source text CHARACTER SET utf8,
				CHANGE sexe pers_sexe varchar(1) CHARACTER SET utf8,
				CHANGE eigencode pers_own_code varchar(100) CHARACTER SET utf8,
				CHANGE geboorteplaats pers_birth_place varchar(50) CHARACTER SET utf8,
				CHANGE geboortedatum pers_birth_date varchar(35) CHARACTER SET utf8,
				CHANGE geboortetijd pers_birth_time varchar(25) CHARACTER SET utf8,
				CHANGE geboortetekst pers_birth_text text CHARACTER SET utf8,
				CHANGE geboortebron pers_birth_source text CHARACTER SET utf8,
				CHANGE doopplaats pers_bapt_place varchar(50) CHARACTER SET utf8,
				CHANGE doopdatum pers_bapt_date varchar(35) CHARACTER SET utf8,
				CHANGE dooptekst pers_bapt_text text CHARACTER SET utf8,
				CHANGE doopbron pers_bapt_source text CHARACTER SET utf8,
				CHANGE religie pers_religion varchar(50) CHARACTER SET utf8,
				CHANGE overlijdensplaats pers_death_place varchar(50) CHARACTER SET utf8,
				CHANGE overlijdensdatum pers_death_date varchar(35) CHARACTER SET utf8,
				CHANGE overlijdenstijd pers_death_time varchar(25) CHARACTER SET utf8,
				CHANGE overlijdenstekst pers_death_text text CHARACTER SET utf8,
				CHANGE overlijdensbron pers_death_source text CHARACTER SET utf8,
				CHANGE oorzaak pers_death_cause varchar(50) CHARACTER SET utf8,
				CHANGE begrafenisplaats pers_buried_place varchar(50) CHARACTER SET utf8,
				CHANGE begrafenisdatum pers_buried_date varchar(35) CHARACTER SET utf8,
				CHANGE begrafenistekst pers_buried_text text CHARACTER SET utf8,
				CHANGE begrafenisbron pers_buried_source text CHARACTER SET utf8,
				CHANGE crematie pers_cremation varchar(1) CHARACTER SET utf8,
				CHANGE plaatsindex pers_place_index text CHARACTER SET utf8,
				CHANGE tekst pers_text text CHARACTER SET utf8,
				CHANGE levend pers_alive varchar(20) CHARACTER SET utf8
				';
			$update_Db = mysql_query($sql) or die(mysql_error());
			//$update.=$sql.'<br>';


			// *** Translate dutch table name into english ***
			$sql='ALTER TABLE '.$updateDb->tree_prefix.'gezin
				RENAME '.$updateDb->tree_prefix.'family';
			$update_Db = mysql_query($sql) or die(mysql_error());

			$sql='ALTER TABLE '.$updateDb->tree_prefix. 'family
				CHANGE id fam_id	mediumint(6) unsigned NOT NULL auto_increment,
				CHANGE gedcomnummer fam_gedcomnumber varchar(20) CHARACTER SET utf8,
				CHANGE man fam_man varchar(20) CHARACTER SET utf8,
				CHANGE vrouw fam_woman varchar(20) CHARACTER SET utf8,
				CHANGE kinderen fam_children text CHARACTER SET utf8,
				CHANGE soort fam_kind varchar(50) CHARACTER SET utf8,
				CHANGE samendatum fam_relation_date varchar(35) CHARACTER SET utf8,
				CHANGE samenplaats fam_relation_place varchar(50) CHARACTER SET utf8,
				CHANGE samentekst fam_relation_text text CHARACTER SET utf8,
				CHANGE samenbron fam_relation_source text CHARACTER SET utf8,
				CHANGE einddatum fam_relation_end_date varchar(35) CHARACTER SET utf8,
				CHANGE ondertrdatum fam_marr_notice_date varchar(35) CHARACTER SET utf8,
				CHANGE ondertrplaats fam_marr_notice_place varchar(50) CHARACTER SET utf8,
				CHANGE ondertrtekst fam_marr_notice_text text CHARACTER SET utf8,
				CHANGE ondertrbron fam_marr_notice_source text CHARACTER SET utf8,
				CHANGE trdatum fam_marr_date varchar(35) CHARACTER SET utf8,
				CHANGE trplaats fam_marr_place varchar(50) CHARACTER SET utf8,
				CHANGE trtekst fam_marr_text text CHARACTER SET utf8,
				CHANGE trbron fam_marr_source text CHARACTER SET utf8,
				CHANGE kerkondertrdatum fam_marr_church_notice_date varchar(35) CHARACTER SET utf8,
				CHANGE kerkondertrplaats fam_marr_church_notice_place varchar(50) CHARACTER SET utf8,
				CHANGE kerkondertrtekst fam_marr_church_notice_text text CHARACTER SET utf8,
				CHANGE kerkondertrbron fam_marr_church_notice_source text CHARACTER SET utf8,
				CHANGE kerktrdatum fam_marr_church_date varchar(35) CHARACTER SET utf8,
				CHANGE kerktrplaats fam_marr_church_place varchar(50) CHARACTER SET utf8,
				CHANGE kerktrtekst fam_marr_church_text text CHARACTER SET utf8,
				CHANGE kerktrbron fam_marr_church_source text CHARACTER SET utf8,
				CHANGE religie fam_religion varchar(50) CHARACTER SET utf8,
				CHANGE scheidingsdatum fam_div_date varchar(35) CHARACTER SET utf8,
				CHANGE scheidingsplaats fam_div_place varchar(50) CHARACTER SET utf8,
				CHANGE scheidingstekst fam_div_text text CHARACTER SET utf8,
				CHANGE scheidingsbron fam_div_source text CHARACTER SET utf8,
				CHANGE huwtekst fam_text text CHARACTER SET utf8,
				CHANGE levend fam_alive int(1),
				CHANGE teller fam_counter mediumint(8)
				';
			$update_Db = mysql_query($sql) or die(mysql_error());
			//$update.=$sql.'<br>';


			$sql='ALTER TABLE '.$updateDb->tree_prefix. 'texts
				CHANGE text_gedcomnr text_gedcomnr varchar(20) CHARACTER SET utf8,
				CHANGE text_text text_text text CHARACTER SET utf8,
				CHANGE text_new_date text_new_date varchar(35) CHARACTER SET utf8,
				CHANGE text_new_time text_new_time varchar(25) CHARACTER SET utf8,
				CHANGE text_changed_date text_changed_date varchar(35) CHARACTER SET utf8,
				CHANGE text_changed_time text_changed_time varchar(25) CHARACTER SET utf8
				';
			$update_Db = mysql_query($sql) or die(mysql_error());
			//$update.=$sql.'<br>';
			$sql='ALTER TABLE '.$updateDb->tree_prefix. 'sources
				CHANGE source_gedcomnr source_gedcomnr varchar(20) CHARACTER SET utf8,
				CHANGE source_title source_title text CHARACTER SET utf8,
				CHANGE source_abbr source_abbr varchar(50) CHARACTER SET utf8,
				CHANGE source_date source_date varchar(35) CHARACTER SET utf8,
				CHANGE source_publ source_publ varchar(150) CHARACTER SET utf8,
				CHANGE source_place source_place varchar(50) CHARACTER SET utf8,
				CHANGE source_refn source_refn varchar(50) CHARACTER SET utf8,
				CHANGE source_auth source_auth varchar(50) CHARACTER SET utf8,
				CHANGE source_subj source_subj varchar(50) CHARACTER SET utf8,
				CHANGE source_item source_item varchar(30) CHARACTER SET utf8,
				CHANGE source_kind source_kind varchar(50) CHARACTER SET utf8,
				CHANGE source_text source_text text CHARACTER SET utf8,
				CHANGE source_photo source_photo text CHARACTER SET utf8,
				CHANGE source_repo_name source_repo_name varchar(50) CHARACTER SET utf8,
				CHANGE source_repo_caln source_repo_caln varchar(50) CHARACTER SET utf8,
				CHANGE source_repo_page source_repo_page varchar(50) CHARACTER SET utf8,
				CHANGE source_new_date source_new_date varchar(35) CHARACTER SET utf8,
				CHANGE source_new_time source_new_time varchar(25) CHARACTER SET utf8,
				CHANGE source_changed_date source_changed_date varchar(35) CHARACTER SET utf8,
				CHANGE source_changed_time source_changed_time varchar(25) CHARACTER SET utf8
				';
			$update_Db = mysql_query($sql) or die(mysql_error());
			//$update.=$sql.'<br>';


			$sql='ALTER TABLE '.$updateDb->tree_prefix. 'addresses
				CHANGE address_gedcomnr address_gedcomnr varchar(20) CHARACTER SET utf8,
				CHANGE address_person_id address_person_id varchar(20) CHARACTER SET utf8,
				CHANGE address_family_id address_family_id varchar(20) CHARACTER SET utf8,
				CHANGE address_address address_address text CHARACTER SET utf8,
				CHANGE address_zip address_zip varchar(20) CHARACTER SET utf8,
				CHANGE address_place address_place varchar(50) CHARACTER SET utf8,
				CHANGE address_phone address_phone varchar(20) CHARACTER SET utf8,
				CHANGE address_date address_date varchar(35) CHARACTER SET utf8,
				CHANGE address_source address_source text CHARACTER SET utf8,
				CHANGE address_text address_text text CHARACTER SET utf8,
				CHANGE address_photo address_photo text CHARACTER SET utf8,
				CHANGE address_new_date address_new_date varchar(35) CHARACTER SET utf8,
				CHANGE address_new_time address_new_time varchar(25) CHARACTER SET utf8,
				CHANGE address_changed_date address_changed_date varchar(35) CHARACTER SET utf8,
				CHANGE address_changed_time address_changed_time varchar(25) CHARACTER SET utf8
					';
			$update_Db = mysql_query($sql) or die(mysql_error());

			$sql='ALTER TABLE '.$updateDb->tree_prefix. 'events
				CHANGE event_person_id event_person_id varchar(20) CHARACTER SET utf8,
				CHANGE event_family_id event_family_id varchar(20) CHARACTER SET utf8,
				CHANGE event_kind event_kind varchar(20) CHARACTER SET utf8,
				CHANGE event_event event_event text CHARACTER SET utf8,
				CHANGE event_gedcom event_gedcom varchar(10) CHARACTER SET utf8,
				CHANGE event_date event_date varchar(35) CHARACTER SET utf8,
				CHANGE event_place event_place varchar(50) CHARACTER SET utf8,
				CHANGE event_source event_source text CHARACTER SET utf8,
				CHANGE event_text event_text text CHARACTER SET utf8,
				CHANGE event_new_date event_new_date varchar(35) CHARACTER SET utf8,
				CHANGE event_new_time event_new_time varchar(25) CHARACTER SET utf8,
				CHANGE event_changed_date event_changed_date varchar(35) CHARACTER SET utf8,
				CHANGE event_changed_time event_changed_time varchar(25) CHARACTER SET utf8
				';
			$update_Db = mysql_query($sql) or die(mysql_error());

			echo ' Tree updated!';
		} // *** End of tabel check ***



		// ******************************
		// *** AUTOMATIC UPDATES HERE ***
		// ******************************

		// *** Automatic installation or update ***
		if (isset($field)){ unset ($field); }
		$column_qry = mysql_query("SHOW COLUMNS FROM ".$updateDb->tree_prefix."person");
		while ($columnDb = mysql_fetch_assoc($column_qry)) {
			$field_value=$columnDb['Field'];
			$field[$field_value]=$field_value;
			// *** test line ***
			//print '<span>'.$field[$field_value].'</span><br />';
		}

		// *** Automatic installation or update ***
		if (isset($field['voorvoegsel'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				CHANGE voorvoegsel pers_tree_prefix	varchar(10) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['pers_tree_prefix'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_tree_prefix varchar(10) CHARACTER SET utf8 AFTER pers_gedcomnumber";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** Automatic installation or update ***
		if (isset($field['person_text_source'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				CHANGE person_text_source pers_text_source text CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['pers_text_source'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_text_source text CHARACTER SET utf8 AFTER pers_text";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** Automatic installation or update ***
		if (isset($field['person_favorite'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				CHANGE person_favorite pers_favorite varchar(1) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['pers_favorite'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_favorite varchar(1) CHARACTER SET utf8 AFTER pers_alive";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** Automatic installation or update ***
		if (isset($field['person_new_date'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				CHANGE person_new_date pers_new_date varchar(35) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['pers_new_date'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_new_date varchar(35) CHARACTER SET utf8 AFTER pers_favorite";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** Automatic installation or update ***
		if (isset($field['person_new_time'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				CHANGE person_new_time pers_new_time varchar(25) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['pers_new_time'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_new_time varchar(25) CHARACTER SET utf8 AFTER pers_new_date";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** Automatic installation or update ***
		if (isset($field['person_changed_date'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				CHANGE person_changed_date pers_changed_date varchar(35) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['pers_changed_date'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_changed_date varchar(35) CHARACTER SET utf8 AFTER pers_new_time";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** Automatic installation or update ***
		if (isset($field['person_changed_time'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				CHANGE person_changed_time pers_changed_time varchar(25) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['pers_changed_time'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_changed_time varchar(25) CHARACTER SET utf8 AFTER pers_changed_time";
			$result=mysql_query($sql) or die(mysql_error());
		}



		// *** HuMo-gen 4.7 updates ***
		// *** UPDATE 1: Add pers_stillborn in ALL person tables ***
		if (!isset($field['pers_stillborn'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_stillborn VARCHAR(1) CHARACTER SET utf8  DEFAULT 'n' AFTER pers_birth_source;";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** UPDATE 2: remove pers_index_bapt in ALL person tables ***
		if (isset($field['indexdoop'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person DROP indexdoop";
			$result=mysql_query($sql) or die(mysql_error());
		}
		if (isset($field['pers_index_bapt'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person DROP pers_index_bapt";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** UPDATE 3: remove pers_index_death in ALL person tables ***
		if (isset($field['indexovl'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person DROP indexovl";
			$result=mysql_query($sql) or die(mysql_error());
		}
		if (isset($field['pers_index_death'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person DROP pers_index_death";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** Show number of fields in table ***
		echo ' fields: '.count($field);

		// Add family items
		// *** Automatic installation or update ***
		if (isset($field)){ unset ($field); }
		$column_qry = mysql_query("SHOW COLUMNS FROM ".$updateDb->tree_prefix."family");
		while ($columnDb = mysql_fetch_assoc($column_qry)) {
			$field_value=$columnDb['Field'];
			$field[$field_value]=$field_value;
			// *** test line ***
			//print '<span>'.$field[$field_value].'</span><br />';
		}

		if (isset($field['family_text_source'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				CHANGE family_text_source fam_text_source text CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['fam_text_source'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				ADD fam_text_source text CHARACTER SET utf8 AFTER fam_text";
			$result=mysql_query($sql) or die(mysql_error());
		}

		if (isset($field['trinstantie'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				CHANGE trinstantie fam_marr_authority text CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['fam_marr_authority'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				ADD fam_marr_authority text CHARACTER SET utf8 AFTER fam_marr_source";
			$result=mysql_query($sql) or die(mysql_error());
		}

		if (isset($field['scheidingsinstantie'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				CHANGE scheidingsinstantie fam_div_authority text CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['fam_div_authority'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				ADD fam_div_authority text CHARACTER SET utf8 AFTER fam_div_source";
			$result=mysql_query($sql) or die(mysql_error());
		}

		if (isset($field['family_new_date'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				CHANGE family_new_date fam_new_date varchar(35) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['fam_new_date'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				ADD fam_new_date varchar(35) CHARACTER SET utf8 AFTER fam_counter";
			$result=mysql_query($sql) or die(mysql_error());
		}

		if (isset($field['family_new_time'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				CHANGE family_new_time fam_new_time varchar(25) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['fam_new_time'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				ADD fam_new_time varchar(35) CHARACTER SET utf8 AFTER fam_new_date";
			$result=mysql_query($sql) or die(mysql_error());
		}

		if (isset($field['family_changed_date'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				CHANGE family_changed_date fam_changed_date varchar(35) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['fam_changed_date'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				ADD fam_changed_date varchar(35) CHARACTER SET utf8 AFTER fam_new_time";
			$result=mysql_query($sql) or die(mysql_error());
		}

		if (isset($field['family_changed_time'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				CHANGE family_changed_time fam_changed_time varchar(25) CHARACTER SET utf8";
			$result=mysql_query($sql) or die(mysql_error());
		}
		elseif (!isset($field['fam_changed_time'])){
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				ADD fam_changed_time varchar(35) CHARACTER SET utf8 AFTER fam_changed_date";
			$result=mysql_query($sql) or die(mysql_error());
		}


		// *****************************
		// *** CHANGE OF TABLES HERE ***
		// *****************************
		if ($translate_tables==true){

			// *** Update person table (html to utf-8) ***
			$read_pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."person",$db);
			while ($read_persDb=mysql_fetch_object($read_pers_sql)){
				$sql='UPDATE '.$updateDb->tree_prefix.'person SET
					pers_firstname="'.safe_text($read_persDb->pers_firstname).'",
					pers_callname="'.safe_text($read_persDb->pers_callname).'",
					pers_prefix="'.safe_text($read_persDb->pers_prefix).'",
					pers_lastname="'.safe_text($read_persDb->pers_lastname).'",
					pers_patronym="'.safe_text($read_persDb->pers_patronym).'",
					pers_name_text="'.safe_text($read_persDb->pers_name_text).'",
					pers_name_source="'.safe_text($read_persDb->pers_name_source).'",
					pers_own_code="'.safe_text($read_persDb->pers_own_code).'",
					pers_birth_place="'.safe_text($read_persDb->pers_birth_place).'",
					pers_birth_text="'.safe_text($read_persDb->pers_birth_text).'",
					pers_birth_source="'.safe_text($read_persDb->pers_birth_source).'",
					pers_bapt_place="'.safe_text($read_persDb->pers_bapt_place).'",
					pers_bapt_text="'.safe_text($read_persDb->pers_bapt_text).'",
					pers_bapt_source="'.safe_text($read_persDb->pers_bapt_source).'",
					pers_religion="'.safe_text($read_persDb->pers_religion).'",
					pers_death_place="'.safe_text($read_persDb->pers_death_place).'",
					pers_death_text="'.safe_text($read_persDb->pers_death_text).'",
					pers_death_source="'.safe_text($read_persDb->pers_death_source).'",
					pers_death_cause="'.safe_text($read_persDb->pers_death_cause).'",
					pers_buried_place="'.safe_text($read_persDb->pers_buried_place).'",
					pers_buried_text="'.safe_text($read_persDb->pers_buried_text).'",
					pers_buried_source="'.safe_text($read_persDb->pers_buried_source).'",
					pers_place_index="'.safe_text($read_persDb->pers_place_index).'",
					pers_text="'.safe_text($read_persDb->pers_text).'",
					pers_text_source="'.safe_text($read_persDb->pers_text_source).'"
					WHERE pers_id="'.$read_persDb->pers_id.'"';
				//$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
				$sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
				//$sql = str_replace("<br>\n", "\n", $sql);
				$sql = str_replace("<br>", "", $sql);
				$update_Db = mysql_query($sql,$db) or die(mysql_error());
			}

			// *** Update family table (html to utf-8) ***
			$read_pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."family",$db);
			while ($read_persDb=mysql_fetch_object($read_pers_sql)){
				$sql='UPDATE '.$updateDb->tree_prefix.'family SET
					fam_id="'.$read_persDb->fam_id.'",
					fam_relation_place="'.safe_text($read_persDb->fam_relation_place).'",
					fam_relation_text="'.safe_text($read_persDb->fam_relation_text).'",
					fam_relation_source="'.safe_text($read_persDb->fam_relation_source).'",
					fam_marr_notice_place="'.safe_text($read_persDb->fam_marr_notice_place).'",
					fam_marr_notice_text="'.safe_text($read_persDb->fam_marr_notice_text).'",
					fam_marr_notice_source="'.safe_text($read_persDb->fam_marr_notice_source).'",
					fam_marr_place="'.safe_text($read_persDb->fam_marr_place).'",
					fam_marr_text="'.safe_text($read_persDb->fam_marr_text).'",
					fam_marr_source="'.safe_text($read_persDb->fam_marr_source).'",
					fam_marr_authority="'.safe_text($read_persDb->fam_marr_authority).'",
					fam_marr_church_notice_place="'.safe_text($read_persDb->fam_marr_church_notice_place).'",
					fam_marr_church_notice_text="'.safe_text($read_persDb->fam_marr_church_notice_text).'",
					fam_marr_church_notice_source="'.safe_text($read_persDb->fam_marr_church_notice_source).'",
					fam_marr_church_place="'.safe_text($read_persDb->fam_marr_church_place).'",
					fam_marr_church_text="'.safe_text($read_persDb->fam_marr_church_text).'",
					fam_marr_church_source="'.safe_text($read_persDb->fam_marr_church_source).'",
					fam_religion="'.safe_text($read_persDb->fam_religion).'",
					fam_div_place="'.safe_text($read_persDb->fam_div_place).'",
					fam_div_text="'.safe_text($read_persDb->fam_div_text).'",
					fam_div_source="'.safe_text($read_persDb->fam_div_source).'",
					fam_div_authority="'.safe_text($read_persDb->fam_div_authority).'",
					fam_text="'.safe_text($read_persDb->fam_text).'",
					fam_text_source="'.safe_text($read_persDb->fam_text_source).'"
					WHERE fam_id="'.$read_persDb->fam_id.'"';
				//$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
				$sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
				//$sql = str_replace("<br>\n", "\n", $sql);
				$sql = str_replace("<br>", "", $sql);
				//$update.=$sql.'<br>';
				$update_Db = mysql_query($sql,$db) or die(mysql_error());
			}

			// *** Update text table (html to utf-8) ***
			$read_pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."texts",$db);
			while ($read_persDb=mysql_fetch_object($read_pers_sql)){
				$sql='UPDATE '.$updateDb->tree_prefix.'texts SET
					text_text="'.safe_text($read_persDb->text_text).'"
					WHERE text_id="'.$read_persDb->text_id.'"';
				//$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
				$sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
				//$sql = str_replace("<br>\n", "\n", $sql);
				$sql = str_replace("<br>", "", $sql);
				//$update.=$sql.'<br>';
				$update_Db = mysql_query($sql,$db) or die(mysql_error());
			}

			// *** Update source table (html to utf-8) ***
			$read_pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."sources",$db);
			while ($read_persDb=mysql_fetch_object($read_pers_sql)){
				$sql='UPDATE '.$updateDb->tree_prefix.'sources SET
					source_title="'.safe_text($read_persDb->source_title).'",
					source_abbr="'.safe_text($read_persDb->source_abbr).'",
					source_publ="'.safe_text($read_persDb->source_publ).'",
					source_place="'.safe_text($read_persDb->source_place).'",
					source_refn="'.safe_text($read_persDb->source_refn).'",
					source_auth="'.safe_text($read_persDb->source_auth).'",
					source_subj="'.safe_text($read_persDb->source_subj).'",
					source_item="'.safe_text($read_persDb->source_item).'",
					source_kind="'.safe_text($read_persDb->source_kind).'",
					source_text="'.safe_text($read_persDb->source_text).'",
					source_repo_name="'.safe_text($read_persDb->source_repo_name).'",
					source_repo_caln="'.safe_text($read_persDb->source_repo_caln).'",
					source_repo_page="'.safe_text($read_persDb->source_repo_page).'"
					WHERE source_id="'.$read_persDb->source_id.'"';
				//$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
				$sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
				//$sql = str_replace("<br>\n", "\n", $sql);
				$sql = str_replace("<br>", "", $sql);
				//$update.=$sql.'<br>';
				$update_Db = mysql_query($sql,$db) or die(mysql_error());
			}

			// *** Update address table (html to utf-8) ***
			$read_pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."addresses",$db);
			while ($read_persDb=mysql_fetch_object($read_pers_sql)){
				$sql='UPDATE '.$updateDb->tree_prefix.'addresses SET
					address_address="'.safe_text($read_persDb->address_address).'",
					address_zip="'.safe_text($read_persDb->address_zip).'",
					address_place="'.safe_text($read_persDb->address_place).'",
					address_phone="'.safe_text($read_persDb->address_phone).'",
					address_date="'.safe_text($read_persDb->address_date).'",
					address_source="'.safe_text($read_persDb->address_source).'",
					address_text="'.safe_text($read_persDb->address_text).'",
					address_photo="'.safe_text($read_persDb->address_photo).'"
					WHERE address_id="'.$read_persDb->address_id.'"';
				//$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
				$sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
				//$sql = str_replace("<br>\n", "\n", $sql);
				$sql = str_replace("<br>", "", $sql);
				//$update.=$sql.'<br>';
				$update_Db = mysql_query($sql,$db) or die(mysql_error());
			}

			// *** Update event table (html to utf-8) ***
			$read_pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."events",$db);
			while ($read_persDb=mysql_fetch_object($read_pers_sql)){
				$sql='UPDATE '.$updateDb->tree_prefix.'events SET
					event_person_id="'.safe_text($read_persDb->event_person_id).'",
					event_family_id="'.safe_text($read_persDb->event_family_id).'",
					event_kind="'.safe_text($read_persDb->event_kind).'",
					event_event="'.safe_text($read_persDb->event_event).'",
					event_gedcom="'.safe_text($read_persDb->event_gedcom).'",
					event_date="'.safe_text($read_persDb->event_date).'",
					event_place="'.safe_text($read_persDb->event_place).'",
					event_source="'.safe_text($read_persDb->event_source).'",
					event_text="'.safe_text($read_persDb->event_text).'"
					WHERE event_id="'.$read_persDb->event_id.'"';
				//$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
				$sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
				//$sql = str_replace("<br>\n", "\n", $sql);
				$sql = str_replace("<br>", "", $sql);
				//$update.=$sql.'<br>';
				$update_Db = mysql_query($sql,$db) or die(mysql_error());
			}

		} // end translate of tables

		echo '<br>';
	} // End of reading family trees ***

	echo '</td></tr>';
	// *** End of update version 4.6 ***



	// ************************************
	// *** Update procedure version 4.7 ***
	// ************************************

	// *** Automatic installation or update ***
	// Check for this setting is necessary for update for old version ***
	if (!isset($humo_option["update_status"])){
		$humo_option["update_status"]='0';
		$sql="INSERT INTO humo_settings SET setting_variable='update_status', setting_value='0'";
		@$result=mysql_query($sql) or die(mysql_error());
	}

	if ($humo_option["update_status"]>'0'){
		echo '<tr><td>HuMo-gen update V4.7</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		//echo '<tr><td>HuMo-gen update V4.7</td><td style="background-color:#00FF00">'.__('AUTOMATIC UPDATE PROCESS STARTED!').'<br>';
		echo '<tr><td>HuMo-gen update V4.7</td><td style="background-color:#00FF00">';

		// *** Automatic installation or update ***
		$update_check_sql = mysql_query("SELECT * FROM humo_user_log",$db);
		if ($update_check_sql){
			if (isset($field)){ unset ($field); }
			$column_qry = mysql_query("SHOW COLUMNS FROM humo_user_log");
			while ($columnDb = mysql_fetch_assoc($column_qry)) {
				$field_value=$columnDb['Field'];
				$field[$field_value]=$field_value;
				// *** test line ***
				//print '<span>'.$field[$field_value].'</span><br />';
			}
			if (!isset($field['log_ip_address'])){
				$sql="ALTER TABLE humo_user_log
					ADD log_ip_address varchar(20) CHARACTER SET utf8 DEFAULT ''";
				$result=mysql_query($sql) or die(mysql_error());
			}
			if (!isset($field['log_user_admin'])){
				$sql="ALTER TABLE humo_user_log
					ADD log_user_admin varchar(5) CHARACTER SET utf8 DEFAULT ''";
				$result=mysql_query($sql) or die(mysql_error());
			}
		}


		// *** Update 'Empty' line if used in tree table ***
		$update_tree_sql = mysql_query("SELECT * FROM humo_trees WHERE tree_prefix='LEEG'",$db);
		while ($update_treeDb=mysql_fetch_object($update_tree_sql)){
			$update_tree_sql2="UPDATE humo_trees SET
				tree_prefix='EMPTY',
				tree_persons='EMPTY',
				tree_families='EMPTY',
				tree_email='EMPTY',
				tree_pict_path='EMPTY',
				tree_privacy='EMPTY'
				WHERE tree_id=".$update_treeDb->tree_id;
			mysql_query($update_tree_sql2,$db);
		}

		// *** Read all family trees from tree table ***
		$update_sql = mysql_query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY'",$db);
		while ($updateDb=mysql_fetch_object($update_sql)){
			echo '<b>Check '.$updateDb->tree_prefix.'</b>';

			// *** Update tree privacy ***
			$tree_privacy='';
			if ($updateDb->tree_privacy=='Standaard'){ $tree_privacy='standard'; }
			if ($updateDb->tree_privacy=='personen_filteren'){ $tree_privacy='filter_persons'; }
			if ($updateDb->tree_privacy=='personen_weergeven'){ $tree_privacy='show_persons'; }
			if ($tree_privacy){
				$sql="UPDATE humo_trees
					SET tree_privacy='".$tree_privacy."' WHERE tree_id='".$updateDb->tree_id."'";
				$result=mysql_query($sql) or die(mysql_error());
			}

			// *** Update person table ***
			$privacy_sql = mysql_query("SELECT pers_id, pers_alive FROM ".$updateDb->tree_prefix."person
				WHERE pers_alive!=''",$db);
			while ($privacyDb=mysql_fetch_object($privacy_sql)){
				$pers_alive=$privacyDb->pers_alive;
				if ($privacyDb->pers_alive=='HZ_levend') { $pers_alive='alive'; }
				if ($privacyDb->pers_alive=='HZ_ovl') { $pers_alive='deceased'; }
				if ($privacyDb->pers_alive=='Aldfaer_ovl') { $pers_alive='deceased'; }
				$sql='UPDATE '.$updateDb->tree_prefix.'person SET pers_alive="'.$pers_alive.'"
					WHERE pers_id="'.$privacyDb->pers_id.'"';
				$update_Db = mysql_query($sql,$db) or die(mysql_error());
			}

			// *** Update person table ***
			$pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."person
				WHERE pers_lastname='doodgeboren kind'",$db);
			while ($persDb=mysql_fetch_object($pers_sql)){
				$sql='UPDATE '.$updateDb->tree_prefix.'person
					SET pers_lastname="N.N",
					pers_stillborn="y"
					WHERE pers_id="'.$persDb->pers_id.'"';
				$update_Db = mysql_query($sql,$db) or die(mysql_error());
			}

			// *** Update event table: translate all event_kind items to english ***
			$read_pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."events",$db);
			while ($read_persDb=mysql_fetch_object($read_pers_sql)){
				if ($read_persDb->event_kind=='adres') { $event_kind='address'; }
				if ($read_persDb->event_kind=='afbeelding') { $event_kind='picture'; }
				if ($read_persDb->event_kind=='begrafenisgetuige') { $event_kind='burial_witness'; }
				if ($read_persDb->event_kind=='beroep') { $event_kind='profession'; }
				if ($read_persDb->event_kind=='bron') { $event_kind='source'; }
				if ($read_persDb->event_kind=='doopgetuige') { $event_kind='baptism_witness'; }
				if ($read_persDb->event_kind=='gebeurtenis') { $event_kind='event'; }
				if ($read_persDb->event_kind=='geboorteaangifte') { $event_kind='birth_declaration'; }
				if ($read_persDb->event_kind=='getuige') { $event_kind='witness'; }
				if ($read_persDb->event_kind=='heerlijkheid') { $event_kind='lordship'; }
				if ($read_persDb->event_kind=='kerktrgetuige') { $event_kind='marriage_witness_rel'; }
				if ($read_persDb->event_kind=='naam') { $event_kind='name'; }
				if ($read_persDb->event_kind=='predikaat') { $event_kind='nobility'; }
				if ($read_persDb->event_kind=='overlijdensaangifte') { $event_kind='death_declaration'; }
				if ($read_persDb->event_kind=='titel') { $event_kind='title'; }
				if ($read_persDb->event_kind=='trgetuige') { $event_kind='marriage_witness'; }
				if (isset($event_kind)){
					$sql='UPDATE '.$updateDb->tree_prefix.'events SET event_kind="'.$event_kind.'"
						WHERE event_id="'.$read_persDb->event_id.'"';
						$update_Db = mysql_query($sql,$db) or die(mysql_error());
				}

			}

			echo ' Tree updated!<br>';
		}

		// *** Update "update_status" to number 1 ***
		$result = mysql_query("UPDATE humo_settings SET setting_value='1'
			WHERE setting_variable='update_status'") or die(mysql_error());

	}

	// ************************************
	// *** Update procedure version 4.8 ***
	// ************************************
	if ($humo_option["update_status"]>'1'){
		echo '<tr><td>HuMo-gen update V4.8</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>HuMo-gen update V4.8</td><td style="background-color:#00FF00">';

		// *** Update sources by persons and families ***
		function update_source($read_dB,$source_value,$connect_kind, $connect_sub_kind, $connect_connect_id) {
			global $db, $updateDb;
			unset($source_array); $source_array=explode(";",$source_value);
			for ($i=0; $i<=(count($source_array)-1); $i++) {
				$gebeurtsql="INSERT INTO ".$updateDb->tree_prefix."connections SET
					connect_order='".($i+1)."',
					connect_kind='".$connect_kind."',
					connect_sub_kind='".$connect_sub_kind."',
					connect_connect_id='".$connect_connect_id."',";
				// *** Check if old source was a link or a text ***
				if (substr($source_array[$i],0,1)=='@'){
					$gebeurtsql.=" connect_source_id='".substr($source_array[$i],1,-1)."'";
				}
				else{
					$gebeurtsql.=" connect_text='".$source_array[$i]."'";
				}
//echo $gebeurtsql.'<br>';
				$result=mysql_query($gebeurtsql) or die(mysql_error());
			}
		}

		// *** Read all family trees from database ***
		$update_sql = mysql_query("SELECT * FROM humo_trees
			WHERE tree_prefix!='EMPTY' ORDER BY tree_order",$db);
		while ($updateDb=mysql_fetch_object($update_sql)){

			echo '<b>Check '.$updateDb->tree_prefix.'</b>';

			// *** Update source table ***
			$sql="ALTER TABLE ".$updateDb->tree_prefix."sources
				ADD source_status VARCHAR(10) CHARACTER SET utf8 DEFAULT '' AFTER source_id,
				ADD source_repo_gedcomnr VARCHAR(20) CHARACTER SET utf8 DEFAULT '' AFTER source_repo_page";
			$result=mysql_query($sql) or die(mysql_error());

			// *** Repositories table ***
			$tbldb = mysql_query("DROP TABLE ".$updateDb->tree_prefix."repositories"); // Remove table.
			// *** Generate new table ***
			print '<br>'.__('creating repositories...').'<br>';
			$tbldb = mysql_query("CREATE TABLE ".$updateDb->tree_prefix."repositories (
				repo_id mediumint(6) unsigned NOT NULL auto_increment,
				repo_gedcomnr varchar(20) CHARACTER SET utf8,
				repo_name text CHARACTER SET utf8,
				repo_address text CHARACTER SET utf8,
				repo_zip varchar(20) CHARACTER SET utf8,
				repo_place varchar(75) CHARACTER SET utf8,
				repo_phone varchar(20) CHARACTER SET utf8,
				repo_date varchar(35) CHARACTER SET utf8,
				repo_source text CHARACTER SET utf8,
				repo_text text CHARACTER SET utf8,
				repo_photo text CHARACTER SET utf8,
				repo_mail varchar(100) CHARACTER SET utf8,
				repo_url varchar(150) CHARACTER SET utf8,
				repo_new_date varchar(35) CHARACTER SET utf8,
				repo_new_time varchar(25) CHARACTER SET utf8,
				repo_changed_date varchar(35) CHARACTER SET utf8,
				repo_changed_time varchar(25) CHARACTER SET utf8,
				PRIMARY KEY (`repo_id`)) DEFAULT CHARSET=utf8") or die(mysql_error());

			// *** Sources connections table ***
			$tbldb = mysql_query("DROP TABLE ".$updateDb->tree_prefix."connections"); // Remove table.
			// *** Generate new table ***
			print ' '.__('creating connections...');
			$tbldb = mysql_query("CREATE TABLE ".$updateDb->tree_prefix."connections (
				connect_id mediumint(6) unsigned NOT NULL auto_increment,
				connect_order mediumint(6),
				connect_kind varchar(25) CHARACTER SET utf8,
				connect_sub_kind varchar(30) CHARACTER SET utf8,
				connect_connect_id varchar(20) CHARACTER SET utf8,
				connect_date varchar(35) CHARACTER SET utf8,
				connect_place varchar(75) CHARACTER SET utf8,
				connect_time varchar(25) CHARACTER SET utf8,
				connect_page text CHARACTER SET utf8,
				connect_role varchar(75) CHARACTER SET utf8,
				connect_text text CHARACTER SET utf8,
				connect_source_id varchar(20) CHARACTER SET utf8,
				connect_item_id varchar(20) CHARACTER SET utf8,
				connect_status varchar(10) CHARACTER SET utf8,
				connect_new_date varchar(35) CHARACTER SET utf8,
				connect_new_time varchar(25) CHARACTER SET utf8,
				connect_changed_date varchar(35) CHARACTER SET utf8,
				connect_changed_time varchar(25) CHARACTER SET utf8,
				PRIMARY KEY (`connect_id`),
				KEY (connect_connect_id)
				) DEFAULT CHARSET=utf8") or die(mysql_error());


			// *** Move extended addresses from event to connect table ***
			$event_qry=mysql_query("SELECT * FROM ".$updateDb->tree_prefix."events
				WHERE event_kind='address'",$db);
			$eventnr=0;
			while($eventDb=mysql_fetch_object($event_qry)){
				$gebeurtsql="INSERT INTO ".$updateDb->tree_prefix."connections SET
					connect_order='".$eventDb->event_order."',
					connect_kind='person',
					connect_sub_kind='person_address',
					connect_connect_id='".$eventDb->event_person_id."',
					connect_source_id='',
					connect_role='".$eventDb->event_event."',
					connect_date='".$eventDb->event_date."',
					connect_item_id='".substr($eventDb->event_source,1,-1)."',
					connect_text='',
					connect_new_date='".$eventDb->event_new_date."',
					connect_new_time='".$eventDb->event_new_time."',
					connect_changed_date='".$eventDb->event_changed_date."',
					connect_changed_time='".$eventDb->event_changed_time."'
					";
				$result=mysql_query($gebeurtsql) or die(mysql_error());
			}
			// *** Remove old addresses from connect table ***
			$sql="DELETE FROM ".$updateDb->tree_prefix."events WHERE event_kind='address'";
			$result=mysql_query($sql) or die(mysql_error());


			// *** Copy extended sources to new connect table ***
			$event_qry=mysql_query("SELECT * FROM ".$updateDb->tree_prefix."events
				WHERE event_source LIKE '_%'
				ORDER BY event_person_id,event_family_id,event_order",$db);
			while($eventDb=mysql_fetch_object($event_qry)){
				unset($source_array);
				$source_array=explode(";",$eventDb->event_source);

				// *** Event by person ***
				if ($eventDb->event_person_id){
					$connect_kind='person';
					$connect_sub_kind='person_source';
					$connect_connect_id=$eventDb->event_person_id;
				}
				// *** Event by family ***
				if ($eventDb->event_family_id){
					$connect_kind='family';
					$connect_sub_kind='family_source';
					$connect_connect_id=$eventDb->event_family_id;
				}
				// *** Source by event ***
				if ($eventDb->event_kind!='source'){
					$connect_sub_kind='event_source';
					$connect_connect_id=$eventDb->event_id;
				}

				for ($i=0; $i<=(count($source_array)-1); $i++) {
					$gebeurtsql="INSERT INTO ".$updateDb->tree_prefix."connections SET";
						if ($eventDb->event_kind=='source'){
							$gebeurtsql.= " connect_order='".$eventDb->event_order."',";
						}else{
							$gebeurtsql.= " connect_order='".($i+1)."',";
						}

						$gebeurtsql.=" connect_kind='".$connect_kind."',
						connect_sub_kind='".$connect_sub_kind."',
						connect_connect_id='".$connect_connect_id."',";

						// *** Check if old source was a link or a text ***
						if (substr($source_array[$i],0,1)=='@'){
							$gebeurtsql.=" connect_source_id='".substr($source_array[$i],1,-1)."'";
						}
						else{
							$gebeurtsql.=" connect_text='".$source_array[$i]."'";
						}

						if ($eventDb->event_kind=='source'){
							$gebeurtsql.=",
							connect_role='".$eventDb->event_event."',
							connect_date='".$eventDb->event_date."',
							connect_place='".$eventDb->event_place."'";
						}
					$result=mysql_query($gebeurtsql) or die(mysql_error());
				}

				// *** Update old source fields, or remove old source records ***
				if ($eventDb->event_kind!='source'){
					$sql="UPDATE ".$updateDb->tree_prefix."events SET event_source='SOURCE'
						WHERE event_id='".$eventDb->event_id."'";
					$result=mysql_query($sql) or die(mysql_error());
				}
				else{
					$sql="DELETE FROM ".$updateDb->tree_prefix."events WHERE event_id='".$eventDb->event_id."'";
					$result=mysql_query($sql) or die(mysql_error());
				}
			}


			// *** Update sources in person table ***
			$read_pers_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."person",$db);
			while ($read_persDb=mysql_fetch_object($read_pers_sql)){
				$update=false;
				$sql="UPDATE ".$updateDb->tree_prefix."person SET";

				if ($read_persDb->pers_name_source){
					update_source($read_persDb,$read_persDb->pers_name_source,'person','pers_name_source', $read_persDb->pers_gedcomnumber);
					$sql.=" pers_name_source='SOURCE'";
					$update=true;
				}
				if ($read_persDb->pers_birth_source){
					update_source($read_persDb,$read_persDb->pers_birth_source,'person','pers_birth_source', $read_persDb->pers_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" pers_birth_source='SOURCE'";
					$update=true;
				}
				if ($read_persDb->pers_bapt_source){
					update_source($read_persDb,$read_persDb->pers_bapt_source,'person','pers_bapt_source', $read_persDb->pers_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" pers_bapt_source='SOURCE'";
					$update=true;
				}
				if ($read_persDb->pers_death_source){
					update_source($read_persDb,$read_persDb->pers_death_source,'person','pers_death_source', $read_persDb->pers_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" pers_death_source='SOURCE'";
					$update=true;
				}
				if ($read_persDb->pers_buried_source){
					update_source($read_persDb,$read_persDb->pers_buried_source,'person','pers_buried_source', $read_persDb->pers_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" pers_buried_source='SOURCE'";
					$update=true;
				}
				if ($read_persDb->pers_text_source){
					update_source($read_persDb,$read_persDb->pers_text_source,'person','pers_text_source', $read_persDb->pers_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" pers_text_source='SOURCE'";
					$update=true;
				}
				$sql.=" WHERE pers_id='".$read_persDb->pers_id."'";

				if ($update==true){
					$result=mysql_query($sql) or die(mysql_error());
				}
			}


			// *** Update sources in family table ***
			$read_fam_sql = mysql_query("SELECT * FROM ".$updateDb->tree_prefix."family",$db);
			while ($read_famDb=mysql_fetch_object($read_fam_sql)){
				$update=false;
				$sql="UPDATE ".$updateDb->tree_prefix."family SET";

				if ($read_famDb->fam_relation_source){
					update_source($read_famDb,$read_famDb->fam_relation_source,'family','fam_relation_source', $read_famDb->fam_gedcomnumber);
					$sql.=" fam_relation_source='SOURCE'";
					$update=true;
				}
				if ($read_famDb->fam_marr_notice_source){
					update_source($read_famDb,$read_famDb->fam_marr_notice_source,'family','fam_marr_notice_source', $read_famDb->fam_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" fam_marr_notice_source='SOURCE'";
					$update=true;
				}
				if ($read_famDb->fam_marr_source){
					update_source($read_famDb,$read_famDb->fam_marr_source,'family','fam_marr_source', $read_famDb->fam_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" fam_marr_source='SOURCE'";
					$update=true;
				}
				if ($read_famDb->fam_marr_church_notice_source){
					update_source($read_famDb,$read_famDb->fam_marr_church_notice_source,'family','fam_marr_church_notice_source', $read_famDb->fam_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" fam_marr_church_notice_source='SOURCE'";
					$update=true;
				}
				if ($read_famDb->fam_marr_church_source){
					update_source($read_famDb,$read_famDb->fam_marr_church_source,'family','fam_marr_church_source', $read_famDb->fam_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" fam_marr_church_source='SOURCE'";
					$update=true;
				}
				if ($read_famDb->fam_div_source){
					update_source($read_famDb,$read_famDb->fam_div_source,'family','fam_div_source', $read_famDb->fam_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" fam_div_source='SOURCE'";
					$update=true;
				}
				if ($read_famDb->fam_text_source){
					update_source($read_famDb,$read_famDb->fam_text_source,'family','fam_text_source', $read_famDb->fam_gedcomnumber);
					if ($update==true){ $sql.=', ';  }
					$sql.=" fam_text_source='SOURCE'";
					$update=true;
				}

				$sql.=" WHERE fam_id='".$read_famDb->fam_id."'";

				if ($update==true){
					$result=mysql_query($sql) or die(mysql_error());
				}
			}
			echo '<br>';

		}

		// *** Update "update_status" to number 2 ***
		$result = mysql_query("UPDATE humo_settings SET setting_value='2'
			WHERE setting_variable='update_status'") or die(mysql_error());

		echo '</td></tr>';
	}


	// **************************************
	// *** Update procedure version 4.8.2 ***
	// **************************************
	if ($humo_option["update_status"]>'2'){
		echo '<tr><td>HuMo-gen update V4.8.2</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>HuMo-gen update V4.8.2</td><td style="background-color:#00FF00">';

		$db_update = mysql_query("DROP TABLE humo_cms_menu");
		print __('creating humo_cms_menu...').'<br>';
		$db_update = mysql_query("CREATE TABLE humo_cms_menu (
			menu_id int(10) NOT NULL AUTO_INCREMENT,
			menu_parent_id int(10) NOT NULL DEFAULT '0',
			menu_order int(5) NOT NULL DEFAULT '0',
			menu_name varchar(25) CHARACTER SET utf8 DEFAULT '',
			PRIMARY KEY (`menu_id`)
			) DEFAULT CHARSET=utf8") or die(mysql_error());

		$db_update = mysql_query("DROP TABLE humo_cms_pages");
		print __('creating humo_cms_pages...').'<br>';
		$db_update = mysql_query("CREATE TABLE humo_cms_pages (
			page_id int(10) NOT NULL AUTO_INCREMENT,
			page_status varchar(1) CHARACTER SET utf8 DEFAULT '',
			page_menu_id int(10) NOT NULL DEFAULT '0',
			page_order int(10) NOT NULL DEFAULT '0',
			page_counter int(10) NOT NULL DEFAULT '0',
			page_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			page_edit_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			page_title varchar(50) CHARACTER SET utf8 DEFAULT '',
			page_text longtext CHARACTER SET utf8 DEFAULT '',
			PRIMARY KEY (`page_id`)
			) DEFAULT CHARSET=utf8") or die(mysql_error());

		// *** Update "update_status" to number 3 ***
		$result = mysql_query("UPDATE humo_settings SET setting_value='3'
			WHERE setting_variable='update_status'") or die(mysql_error());

		echo '</td></tr>';
	}

	// **************************************
	// *** Update procedure version 4.8.8 ***
	// **************************************
	if ($humo_option["update_status"]>'3'){
		echo '<tr><td>HuMo-gen update V4.8.8</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>HuMo-gen update V4.8.8</td><td style="background-color:#00FF00">';

		// *** Read all family trees from database ***
		$update_sql = mysql_query("SELECT * FROM humo_trees
			WHERE tree_prefix!='EMPTY' ORDER BY tree_order",$db);
		while ($updateDb=mysql_fetch_object($update_sql)){
			echo '<b>Check '.$updateDb->tree_prefix.'</b><br>';

			// *** Update events table ***
			$sql="ALTER TABLE ".$updateDb->tree_prefix."events
				ADD event_gedcomnr varchar(20) CHARACTER SET utf8 AFTER event_id";
			$result=mysql_query($sql) or die(mysql_error());

			// *** Update person table ***
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_sexe_source text CHARACTER SET utf8 AFTER pers_sexe";
			$result=mysql_query($sql) or die(mysql_error());
		}

		// *** Update "update_status" to number 4 ***
		$result = mysql_query("UPDATE humo_settings SET setting_value='4'
			WHERE setting_variable='update_status'") or die(mysql_error());

		echo '</td></tr>';
	}


	// **************************************
	// *** Update procedure version 4.8.9 ***
	// **************************************
	if ($humo_option["update_status"]>'4'){
		echo '<tr><td>HuMo-gen update V4.8.9</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>HuMo-gen update V4.8.9</td><td style="background-color:#00FF00">';

		// *** Read all family trees from database ***
		$update_sql = mysql_query("SELECT * FROM humo_trees
			WHERE tree_prefix!='EMPTY' ORDER BY tree_order",$db);
		while ($updateDb=mysql_fetch_object($update_sql)){
			//echo '<b>Check '.$updateDb->tree_prefix.'</b><br>';

			// *** Automatic installation or update ***
			if (isset($field)){ unset ($field); }
			$column_qry = mysql_query("SHOW COLUMNS FROM ".$updateDb->tree_prefix."person");
			while ($columnDb = mysql_fetch_assoc($column_qry)) {
				$field_value=$columnDb['Field'];
				$field[$field_value]=$field_value;
				// *** test line ***
				//print '<span>'.$field[$field_value].'</span><br />';
			}

			if (!isset($field['pers_unprocessed_tags'])){
				// *** Update person table ***
				$sql="ALTER TABLE ".$updateDb->tree_prefix."person
					ADD pers_unprocessed_tags text CHARACTER SET utf8 AFTER pers_favorite";
				$result=mysql_query($sql) or die(mysql_error());

				$sql="ALTER TABLE ".$updateDb->tree_prefix."family
					ADD fam_unprocessed_tags text CHARACTER SET utf8 AFTER fam_counter";
				$result=mysql_query($sql) or die(mysql_error());

				$sql="ALTER TABLE ".$updateDb->tree_prefix."texts
					ADD text_unprocessed_tags text CHARACTER SET utf8 AFTER text_text";
				$result=mysql_query($sql) or die(mysql_error());

				$sql="ALTER TABLE ".$updateDb->tree_prefix."sources
					ADD source_unprocessed_tags text CHARACTER SET utf8 AFTER source_repo_gedcomnr";
				$result=mysql_query($sql) or die(mysql_error());

				$sql="ALTER TABLE ".$updateDb->tree_prefix."addresses
					ADD address_unprocessed_tags text CHARACTER SET utf8 AFTER address_photo";
				$result=mysql_query($sql) or die(mysql_error());

				$sql="ALTER TABLE ".$updateDb->tree_prefix."events
					ADD event_unprocessed_tags text CHARACTER SET utf8 AFTER event_text";
				$result=mysql_query($sql) or die(mysql_error());

				$sql="ALTER TABLE ".$updateDb->tree_prefix."connections
					ADD connect_unprocessed_tags text CHARACTER SET utf8 AFTER connect_status";
				$result=mysql_query($sql) or die(mysql_error());

				$sql="ALTER TABLE ".$updateDb->tree_prefix."repositories
					ADD repo_unprocessed_tags text CHARACTER SET utf8 AFTER repo_url";
				$result=mysql_query($sql) or die(mysql_error());
			}
			
			// *** Update tree tables ***
			$sql="ALTER TABLE ".$updateDb->tree_prefix."person
				ADD pers_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER pers_alive";
			$result=mysql_query($sql) or die(mysql_error());

			$sql="ALTER TABLE ".$updateDb->tree_prefix."family
				ADD fam_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER fam_alive";
				$result=mysql_query($sql) or die(mysql_error());

			$sql="ALTER TABLE ".$updateDb->tree_prefix."texts
				ADD text_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER text_text";
			$result=mysql_query($sql) or die(mysql_error());

			$sql="ALTER TABLE ".$updateDb->tree_prefix."sources
				ADD source_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER source_repo_gedcomnr";
			$result=mysql_query($sql) or die(mysql_error());

			$sql="ALTER TABLE ".$updateDb->tree_prefix."addresses
				ADD address_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER address_photo";
			$result=mysql_query($sql) or die(mysql_error());

			$sql="ALTER TABLE ".$updateDb->tree_prefix."events
				ADD event_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER event_text,
				ADD event_event_extra text CHARACTER SET utf8 AFTER event_event";
			$result=mysql_query($sql) or die(mysql_error());

			$sql="ALTER TABLE ".$updateDb->tree_prefix."connections
				ADD connect_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER connect_status";
			$result=mysql_query($sql) or die(mysql_error());

			$sql="ALTER TABLE ".$updateDb->tree_prefix."repositories
				ADD repo_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER repo_url";
			$result=mysql_query($sql) or die(mysql_error());
		}

		$sql="ALTER TABLE humo_users
			ADD user_mail varchar(100) CHARACTER SET utf8 AFTER user_name,
			ADD user_trees text CHARACTER SET utf8 AFTER user_mail,
			ADD user_remark text CHARACTER SET utf8 AFTER user_trees,
			ADD user_status varchar(1) CHARACTER SET utf8 AFTER user_remark,
			ADD user_register_date varchar(20) CHARACTER SET utf8 AFTER user_group_id,
			ADD user_last_visit varchar(25) CHARACTER SET utf8 AFTER user_register_date";
			$result=mysql_query($sql) or die(mysql_error());

		// *** Update "update_status" to number 5 ***
		$result = mysql_query("UPDATE humo_settings SET setting_value='5'
			WHERE setting_variable='update_status'") or die(mysql_error());

		echo ' Tree updated!';

		echo '</td></tr>';
	}


	// **************************************
	// *** Update procedure version 4.9.1 ***
	// **************************************
	if ($humo_option["update_status"]>'5'){
		echo '<tr><td>HuMo-gen update V4.9.1</td><td style="background-color:#00FF00">OK</td></tr>';
	}
	else{
		echo '<tr><td>HuMo-gen update V4.9.1</td><td style="background-color:#00FF00">';
		// *** Read all family trees from database ***
		$update_sql = mysql_query("SELECT * FROM humo_trees
			WHERE tree_prefix!='EMPTY' ORDER BY tree_order",$db);
		while ($updateDb=mysql_fetch_object($update_sql)){
			// *** Update family table ***
			$sql="ALTER TABLE ".$updateDb->tree_prefix."family ADD INDEX (fam_man), ADD INDEX (fam_woman)";
			$result=mysql_query($sql) or die(mysql_error());
		}
		// *** Update "update_status" to number 6 ***
		$result = mysql_query("UPDATE humo_settings SET setting_value='6'
			WHERE setting_variable='update_status'") or die(mysql_error());
		echo ' Tree updated!';
		echo '</td></tr>';
	}

	/*	END OF UPDATE SCRIPT

		*** VERY IMPORTANT REMARKS FOR PROGRAMMERS ***
		* Change update_status in install.php
		* Change version check in admin/index.php
		* Don't forget to add new database fields in the tables of install.php and gedcom_tables.php!
		* If a database field is added in a family tree table,
			DO IT IN ALL TABLES FOR ALL FAMILY TREES!
			Otherwise the search in multiple family trees doesn't work!
		* Change database fields: check if database changes can be made in
			global parts of this update script.
	*/
	
	// *** END OF UPDATES ***
	echo '<table><br>';
	echo __('All updates completed, click at "Mainmenu"');
	// *** END OF UPDATE PROCEDURES *******************************************************
}
?>