<?php
// *** Support for gedcom files for MAC computers ***
@ini_set('auto_detect_line_endings', TRUE);

/**
* This is the gedcom processing file for HuMo-gen.
*
* If you are reading this in your web browser, your server is probably
* not configured correctly to run PHP applications!
*
* See the manual for basic setup instructions
*
* http://www.huubmons.nl/software/
*
* ----------
*
* Copyright (C) 2008-2009 Huub Mons,
* Klaas de Winkel, Jan Maat, Jeroen Beemster, Louis Ywema, Theo Huitema,
* René Janssen, Yossi Beck
* and others.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Import Gedcom file').'</h1>';

include_once(CMS_ROOTPATH_ADMIN."include/gedcom_asciihtml.php");
include_once(CMS_ROOTPATH_ADMIN."include/gedcom_anselhtml.php");
include_once(CMS_ROOTPATH_ADMIN."include/gedcom_ansihtml.php");

@set_time_limit(3000);

//*** TEST TIME LIMIT ***
//set_time_limit(10);
//set_time_limit(120);
//set_time_limit(30000000);

if(CMS_SPECIFIC=="Joomla") {
	$phpself = "index.php?option=com_humo-gen&amp;task=admin";
	global $gen_program, $not_processed;
}
else {
	$phpself = $_SERVER['PHP_SELF'];
}

// *** Step 1 ***
if (isset($_POST['step1'])){ $step1=$_POST['step1']; }
if (isset($_GET['step1'])){ $step1=$_GET['step1']; }
if (isset($step1)){

	// *** THIS CODE DOESN'T WORK WITH PROVIDER BHOSTED! GIVES A SERVER ERROR. ***
	// *** Only needed for Huub's test server ***
	// *** TO PREVENT GENERATING A HTACCES FILE IN THE WORKVERSION ***
	/*
	if (@!file_exists("../../gedcom-bestanden")){
		// *** Make sure gzip is turned off in .htaccess (for progress bar) ***
		if(file_exists(".htaccess")===true) { // file exists, now check for line
			$content = file_get_contents(".htaccess");
			if(strpos($content,"SetEnv no-gzip dont-vary")===false) { // the line isn't there yet, append it
				file_put_contents(".htaccess","\nSetEnv no-gzip dont-vary", FILE_APPEND);
			}
		}
		else {  // create .htaccess with the relevant line
			file_put_contents(".htaccess","\nSetEnv no-gzip dont-vary");
		}
	}
	*/

	echo __('<b>STEP 1) Select Gedcom file:</b>
<p>First a Gedcom file has to be placed in this folder: /humo-gen/admin/gedcom_files/<br>
This can be done in two ways:<br>
1) Put the file in this folder yourself (i.e. with FTP).<br>
2) With the following upload form.');

	// *** Upload gedcom file ***
	print '<table class="humo" border="1" cellspacing="0" width="100%" bgcolor="#DDFD9B"><tr><td>';

	echo __('Here you can upload a Gedcom file (or zipped Gedcom file).<br>
ATTENTION: the privileges of the file map may have to be adjusted!');

	if (isset($_POST['upload'])){
		// *** Only needed for Huub's test server ***
		if (file_exists("../gedcom-bestanden")){ $gedcom_directory="../gedcom-bestanden"; }
		elseif (file_exists("gedcom_files")){ $gedcom_directory="gedcom_files"; }
		else{
			if(CMS_SPECIFIC=="Joomla") {
				$gedcom_directory=substr(CMS_ROOTPATH_ADMIN, 0, -1); // take away the / at the end
			}
			else {
				$gedcom_directory=".";
			}
		}
		$new_upload = $gedcom_directory.'/' . basename($_FILES['upload_file']['name']);
		// *** Move and check for succesful upload ***
		echo '<p><b>'.$new_upload.'<br>';
		if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $new_upload))
			echo __('File successfully uploaded.').'</b>';
		else
			echo __('Upload has failed.').'</b>';

		// *** If file is zipped, unzip it ***
		if (substr($new_upload,-4)=='.zip'){
			$zip = new ZipArchive;
			$res = $zip->open($new_upload);
			if ($res === TRUE) {
				//$zip->extractTo('/myzips/extract_path/');
				$zip->extractTo($gedcom_directory);
				$zip->close();
				echo '<br>Succesfully unzipped file!';
			} else {
				echo '<br>Error in unzipping file!';
			}
		}

	}
	else {
		// *** Upload form ***
		echo "<form name='uploadform' enctype='multipart/form-data' action='".$phpself."' method='post'>";
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="family_tree_id" value="'.$family_tree_id.'">';
			echo "<input type='file' name='upload_file' >";
			echo "<input type='hidden' name='upload' value='Upload'>";
			echo "<input type='submit' name='step1' value='Upload'>";
		echo "</form>";
	}
	print '</td></tr></table>';

	if (isset($_POST['tree_prefix'])){
		$tree_prefix=$_POST['tree_prefix'];
		$_SESSION['tree_prefix']=$tree_prefix;
	}
	if (isset($_GET['tree_prefix'])){
		$tree_prefix=$_GET['tree_prefix'];
		$_SESSION['tree_prefix']=$tree_prefix;
	}

	print '<br>'.__('Select Gedcom file:').'<br>';
	print '<form method="post" action="'.$phpself.'" style="display : inline">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
	echo '<input type="hidden" name="family_tree_id" value="'.$family_tree_id.'">';

	if(CMS_SPECIFIC=="Joomla") {
		$gedcom_directory=substr(CMS_ROOTPATH_ADMIN, 0, -1); // take away the / at the end
	}
	else {
		$gedcom_directory="gedcom_files";
	}

	// *** Only needed for Huub's test server ***
	if (@file_exists("../../gedcom-bestanden")){
		$gedcom_directory="../../gedcom-bestanden";
	}
	// *** Only needed for Huub's test server (for component in Joomla) ***
	//if (@file_exists("../../../../../../gedcom-bestanden")){
	//	$gedcom_directory="../../../../../../gedcom-bestanden";
	//}
	$dh  = opendir($gedcom_directory);
	while (false !== ($filename = readdir($dh))) {
		if (strtolower(substr($filename, -3)) == "ged"){
			$filenames[]=$gedcom_directory."/".$filename;
		}
	}
	// *** Order gedcom files by alfabet ***
	if (isset($filenames)){ usort($filenames,'strnatcasecmp'); }
	echo '<select size="1" name="gedcom_file">';

	//$result = mysql_query("SELECT `tree_gedcom` FROM `humo_trees` WHERE `tree_prefix`='".$_SESSION['tree_prefix']."'");
	//$treegedDb = mysql_fetch_array($result);
	$result = $dbh->query("SELECT `tree_gedcom` FROM `humo_trees` WHERE `tree_prefix`='".$_SESSION['tree_prefix']."'");
	$treegedDb = $result->fetch();
	
	$gedfile = $treegedDb['tree_gedcom'];
	for ($i=0; $i<count($filenames); $i++){
		//print '<option value="'.$filenames[$i].'">'.$filenames[$i].'</option>';
		$selected = '';
		if($gedfile == $filenames[$i]) { $selected = " selected "; } // if this was last gedcom that was used for this tree - select it
		print '<option value="'.$filenames[$i].'" '.$selected.'>'.$filenames[$i].'</option>';
	}
	echo '</select>';

	$check=''; if ($humo_option["gedcom_read_reassign_gedcomnumbers"]=='y'){ $check=' checked'; }
	echo '<p><input type="checkbox" name="reassign_gedcomnumbers"'.$check.'> '.__('Reassign new ID numbers for persons, fams etc. (don\'t use IDs from gedcom)')."<br>\n";

	$check=''; if ($humo_option["gedcom_read_order_by_date"]=='y'){ $check=' checked'; }
	echo '<input type="checkbox" name="order_by_date"'.$check.'> '.__('Order children by date (only needed if children are in wrong order)')."<br>\n";

	// if a humo_location table exists, refresh the location_status column
	//if (mysql_num_rows( mysql_query("SHOW TABLES LIKE 'humo_location'", $db))) {
	$res = $dbh->query("SHOW TABLES LIKE 'humo_location'");
	if ($res->rowCount()) {
		$check=''; if ($humo_option["gedcom_read_process_geo_location"]=='y'){ $check=' checked'; }
		echo "<input type='checkbox' name='process_geo_location'".$check."> ".__('Add new locations to geo-location database (for Google Maps locations). This will slow down reading of gedcom file!')."\n";	
	}
	
	//$result = mysql_query("SELECT tree_persons FROM humo_trees WHERE tree_prefix ='".$_SESSION['tree_prefix']."'",$db);
	//$resultDb=mysql_fetch_object($result);
	$result = $dbh->query("SELECT tree_persons FROM humo_trees WHERE tree_prefix ='".$_SESSION['tree_prefix']."'");
	$resultDb=$result->fetch(PDO::FETCH_OBJ);	
	if ($resultDb->tree_persons != 0) {  // don't show if there is nothing in the database yet: this can't be a second gedcom!
		echo "<p><INPUT type='checkbox' name='add_tree'> ".__('Add this tree to the existing tree')."<br>\n";	
	}	

	echo '<p><input type="checkbox" name="check_processed"> '.__('Show non-processed items when processing gedcom (can be a long list!')."<br>\n";
	echo '<input type="checkbox" name="show_gedcomnumbers"> '.__('Show all numbers when processing gedcom (useful when a time-out occurs!)')."<br>\n";

	echo '<input type="checkbox" name="commit_checkbox" checked disabled> Batch processing: <select class="fonts" size="1" name="commit_records" style="width: 200px">';
		echo '<option value="1">'.__('1 record (slow processing, but needs less server-memory)').'</option>';
		$selected=''; if ($humo_option["gedcom_read_commit_records"]=='10'){ $selected=' selected'; }
		echo '<option value="10"'.$selected.'>10 '.__('records per batch').'</option>';
		$selected=''; if ($humo_option["gedcom_read_commit_records"]=='100'){ $selected=' selected'; }
		echo '<option value="100"'.$selected.'>100 '.__('records per batch').'</option>';
		$selected=''; if ($humo_option["gedcom_read_commit_records"]=='500'){ $selected=' selected'; }
		echo '<option value="500"'.$selected.'>500 '.__('records per batch').'</option>';
		$selected=''; if ($humo_option["gedcom_read_commit_records"]=='1000'){ $selected=' selected'; }
		echo '<option value="1000"'.$selected.'>1000 '.__('records per batch').'</option>';
		$selected=''; if ($humo_option["gedcom_read_commit_records"]=='5000'){ $selected=' selected'; }
		echo '<option value="5000"'.$selected.'>5000 '.__('records per batch').'</option>';
		$selected=''; if ($humo_option["gedcom_read_commit_records"]=='10000'){ $selected=' selected'; }
		echo '<option value="10000"'.$selected.'>10000 '.__('records per batch').'</option>';
		$selected=''; if ($humo_option["gedcom_read_commit_records"]=='9000000'){ $selected=' selected'; }
		echo '<option value="9000000"'.$selected.'>'.__('ALL records (fast processing, but needs server-memory)').'</option>';
	echo '</select>';

	print '<p><input type="Submit" name="step2" value="'.__('Step').' 2">';
	print '</form>';

	if(CMS_SPECIFIC=="Joomla") {
		echo '<br><br><br><br><br><br><br><br><br>'; //make sure left menu won't run off bottom of screen if content is not long enough
	}
}

// *** Step 2 generate tables ***
if (isset($_POST['step2'])){

	if(!isset($_POST['add_tree'])) {  	
		$_SESSION['add_tree']=false; 
		print '<b>'.__('STEP 2) Creating or deleting tables:').'</b><br>';
		if(CMS_SPECIFIC=="Joomla") {
			$rootpathinclude = CMS_ROOTPATH_ADMIN."include/";
		}
		else {
			$rootpathinclude = '';
		}
		include_once($rootpathinclude."gedcom_tables.php");
	}
	else {
		$_SESSION['add_tree']=true;
		print __('The data in this gedcom will be appended to the existing data in this tree!').'<br>';
	}

	if(!isset($_POST['reassign_gedcomnumbers'])) {
		//$result = mysql_query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_reassign_gedcomnumbers'") or die(mysql_error());
		$result = $dbh->query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_reassign_gedcomnumbers'");
	}
	else {
		//$result = mysql_query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_reassign_gedcomnumbers'") or die(mysql_error());
		$result = $dbh->query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_reassign_gedcomnumbers'");
	}

	if(!isset($_POST['order_by_date'])) {
		//$result = mysql_query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_order_by_date'") or die(mysql_error());
		$result = $dbh->query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_order_by_date'");
	}
	else {
		//$result = mysql_query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_order_by_date'") or die(mysql_error());
		$result = $dbh->query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_order_by_date'");
	}

	if (isset($_POST['process_geo_location'])){
		//$result = mysql_query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_process_geo_location'") or die(mysql_error());
		$result = $dbh->query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_process_geo_location'");
	}
	else{
		//$result = mysql_query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_process_geo_location'") or die(mysql_error());
		$result = $dbh->query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_process_geo_location'");
	}

	if (isset($_POST['commit_records'])){
		//$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST['commit_records'])."' WHERE setting_variable='gedcom_read_commit_records'") or die(mysql_error());
		$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text($_POST['commit_records'])."' WHERE setting_variable='gedcom_read_commit_records'");
	}

	// *** PREPARE PROGRESS BAR ***
	// *** person-family-source-address-text ***
	//$progress_counter=0;
	$handle = fopen($_POST["gedcom_file"], "r");
	$accent='';
	while (!feof($handle)) {
		$buffer = fgets($handle, 4096);
		//$buffer=rtrim($buffer,"\n\r");  // *** Strip newline ***
		//$buffer=ltrim($buffer," ");  // *** Strip starting spaces, for Pro-gen ***
		$buffer=trim($buffer); // *** Strip starting spaces for Pro-gen and ending spaces for Ancestry.
		/*
		if (substr($buffer, -6, 6)=='@ INDI'){ $progress_counter++; }
		if (substr($buffer, -5, 5)=='@ FAM'){ $progress_counter++; }
		if (substr($buffer, 0, 3)=='0 @' AND substr($buffer, -6, 6)=='@ SOUR'){ $progress_counter++; }

		if (substr($buffer, 0, 3)=='0 @' AND substr($buffer, -6, 6)=='@ REPO'){ $progress_counter++; }

		if (substr($buffer, 0, 3)=='0 @' AND substr($buffer, -6, 6)=='@ RESI'){ $progress_counter++; }

		//if (substr($buffer, 0, 3)=='0 @' AND substr($buffer, -6, 6)=='@ NOTE'){ $progress_counter++; }
		if (substr($buffer, 0, 3)=='0 @' AND (strpos($buffer,'@ NOTE')>1)){ $progress_counter++; }

		if (substr($buffer, 0, 3)=='0 @' AND substr($buffer, -6, 6)=='@ OBJE'){ $progress_counter++; }
		*/
		// Save accent kind (ASCII, ANSI, ANSEL or UTF-8)
		if (substr($buffer, 0, 6)=='1 CHAR'){ $accent=substr($buffer,7); }
	}

	//$_SESSION['save_progress_counter']=$progress_counter;
	//$_SESSION['save_progress']='0';
	$_SESSION['save_progress2']='0';
	$_SESSION['save_perc']='0';
	$_SESSION['save_total']='0';
	$_SESSION['save_starttime']='0';
	// *** END PREPARE PROGRESS BAR ***

	// Reset variables (needed to proceed after time out)
	$_SESSION['save_pointer']='0';
	
	// *** Reset gen_program ***
	$gen_program='';
	$_SESSION['save_gen_program']=$gen_program;

	print '<br><table><tr><td>';
	print '<form method="post" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
	echo '<input type="hidden" name="family_tree_id" value="'.$family_tree_id.'">';

	print '<input type="hidden" name="gedcom_accent" value="'.$accent.'">';

	if (isset($_POST['check_processed'])){
		print '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
	}
	if (isset($_POST['show_gedcomnumbers'])){
		print '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
	}

	if(!isset($_POST['add_tree'])) {
		// *** Reset nr of persons and families ***
		//$sql = mysql_query("UPDATE humo_trees
		//	SET tree_persons='', tree_families=''
		//	WHERE tree_prefix='".$_SESSION['tree_prefix']."'", $db);
		$sql = $dbh->query("UPDATE humo_trees
			SET tree_persons='', tree_families=''
			WHERE tree_prefix='".$_SESSION['tree_prefix']."'");			
	}
	if(isset($_POST['add_tree'])) {
		print '<input type="hidden" name="add_tree" value="1">';
	}
	else {
		print '<input type="hidden" name="add_tree" value="">';
	}
	
	//if(isset($_POST['reassign_gedcomnumbers'])) {
	//	print '<input type="hidden" name="reassign_gedcomnumbers" value="1">';
	//}
	//else {
	//	print '<input type="hidden" name="reassign_gedcomnumbers" value="">';
	//}	

	//if(isset($_POST['order_by_date'])) { print '<input type="hidden" name="order_by_date" value="1">'; }
	
	print '<input type="hidden" name="gedcom_file" value="'.$_POST['gedcom_file'].'">';

	print '<input type="Submit" name="step3" value="'.__('Step').' 3">';
	print '</form>';
	print '</td>';
	if(isset($_POST['add_tree'])) { 
		print '<td>';
		print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		print '<form method="post" style="display:inline" action="'.$phpself.'">';
		echo '<input type="hidden" name="page" value="tree">';
		echo '<input type="hidden" name="family_tree_id" value="'.$family_tree_id.'">';
		print '<input type="Submit" name="back" value="'.__('Cancel').'">';
		print '</form>';
		print '</td>';
	}
	print '</tr></table>';

	if(CMS_SPECIFIC=="Joomla") {
		echo '<br><br><br><br><br><br><br><br><br>'; //make sure left menu won't run off bottom of screen if content is not long enough
	}
}

// ************************************************************************************************
// *** STEP 3 READ Gedcom file ***
// ************************************************************************************************
if (isset($_POST['step3'])){

	if($_SESSION['save_starttime']==0) {$_SESSION['save_starttime']=time();}

	// begin step 3 merge additions
	$add_tree = false;
	if($_SESSION['add_tree']==true) {
		$add_tree = true;
		unset($_SESSION['add_tree']); // we don't want the session variable to persist - can cause problems!
	}
	
	$reassign = false;
	//if($_SESSION['reassign']==true) {
	//	$reassign = true;
	//	unset($_SESSION['reassign']); // we don't want the session variable to persist - can cause problems!
	//}
	if ($humo_option["gedcom_read_reassign_gedcomnumbers"]=='y'){ $reassign = true; }
	
	if($add_tree==true) {
		// if we add a tree we have to change the gedcomnumbers of pers, fam, source, addresses and notes
		// so that they will be different from the existing ones.
		// therefore we check what is the largest gednr in each of them
		// and in gedcom_cls.php we add this number to the ones in the new gedcom
		// this way they will never be the same as the existing ones

		// I40
		$test_qry = "SELECT `pers_gedcomnumber` FROM ".$_SESSION['tree_prefix']."person";
		//$geds = mysql_query($test_qry,$db);
		$geds = $dbh->query($test_qry);
		$largest_pers_ged = 0;
		//while ($gedsDb = mysql_fetch_object($geds)) {
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->pers_gedcomnumber));
			if ($gednum > $largest_pers_ged) { $largest_pers_ged = $gednum; }
		}
		// F40
		$test_qry = "SELECT `fam_gedcomnumber` FROM ".$_SESSION['tree_prefix']."family";
		//$geds = mysql_query($test_qry,$db);		
		$geds = $dbh->query($test_qry);
		$largest_fam_ged = 0;
		//while ($gedsDb = mysql_fetch_object($geds)) {
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->fam_gedcomnumber));
			if ($gednum > $largest_fam_ged) { $largest_fam_ged = $gednum; }
		}
		// S40
		$test_qry = "SELECT `source_gedcomnr` FROM ".$_SESSION['tree_prefix']."sources";
		//$geds = mysql_query($test_qry,$db);
		$geds = $dbh->query($test_qry);
		$largest_source_ged = 0;
		//while ($gedsDb = mysql_fetch_object($geds)) {
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->source_gedcomnr));
			if ($gednum > $largest_source_ged) { $largest_source_ged = $gednum; }
		}
		//  R40 (RESI)
		$test_qry = "SELECT `address_gedcomnr` FROM ".$_SESSION['tree_prefix']."addresses";
		//$geds = mysql_query($test_qry,$db);
		$geds = $dbh->query($test_qry);
		$largest_address_ged = 0;
		//while ($gedsDb = mysql_fetch_object($geds)) {
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->address_gedcomnr));
			if ($gednum > $largest_address_ged) { $largest_address_ged = $gednum; }
		}
		//  R40 (REPO)
		$test_qry = "SELECT `repo_gedcomnr` FROM ".$_SESSION['tree_prefix']."repositories";
		//$geds = mysql_query($test_qry,$db);
		$geds = $dbh->query($test_qry);
		$largest_repo_ged = 0;
		//while ($gedsDb = mysql_fetch_object($geds)) {
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->repo_gedcomnr));
			if ($gednum > $largest_repo_ged) { $largest_repo_ged = $gednum; }
		}
		// @N40@
		$test_qry = "SELECT `text_gedcomnr` FROM ".$_SESSION['tree_prefix']."texts";
		//$geds = mysql_query($test_qry,$db);		
		$geds = $dbh->query($test_qry);
		$largest_text_ged = 0;
		//while ($gedsDb = mysql_fetch_object($geds)) {
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->text_gedcomnr)); // takes away @@'s and any other letters
			if ($gednum > $largest_text_ged) { $largest_text_ged = $gednum; }
		}

		// @O40@ object table
		$test_qry = "SELECT `event_gedcomnr` FROM ".$_SESSION['tree_prefix']."events";
		//$geds = mysql_query($test_qry,$db);		
		$geds = $dbh->query($test_qry);
		$largest_object_ged = 0;
		//while ($gedsDb = mysql_fetch_object($geds)) {
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->event_gedcomnr)); // takes away @@'s and any other letters
			if ($gednum > $largest_object_ged) { $largest_object_ged = $gednum; }
		}

	}
	
	// for merging when we read in a new tree we have to make sure that the relevant rel_merge row in the Db is removed.
	$qry = "DELETE FROM humo_settings WHERE setting_variable ='rel_merge_".$_SESSION['tree_prefix']."'"; // doesn't create error if not exists
	//$result = mysql_query($qry,$db);
	$result = $dbh->query($qry);
	// we have to make sure that the dupl_arr session is unset if it exists.
	if(isset($_SESSION['dupl_arr_'.$_SESSION['tree_prefix']])) {
		unset($_SESSION['dupl_arr_'.$_SESSION['tree_prefix']]);
	// we have to make sure the present_compare session is unset, if exists
	}	
	if(isset($_SESSION['present_compare_'.$_SESSION['tree_prefix']]))	 {
		unset($_SESSION['present_compare_'.$_SESSION['tree_prefix']]);
	}
	// End step 3 merge additions 

	// *** Weblog Class ***

	// variables to reassign new gedcomnumbers (in gedcom_cls.php)
	if(isset($reassign_array)) { unset($reassign_array); }
	if($reassign==true) {  // reassign gedcomnumbers when importing tree
		$new_gednum["I"] = 1;
		$new_gednum["F"] = 1;
		$new_gednum["S"] = 1;
		$new_gednum["R"] = 1;
		$new_gednum["RP"] = 1;
		$new_gednum["N"] = 1;
	}
	if($add_tree==true) { // reassign gedcomnumbers when importing added tree in merging
		$new_gednum["I"] = $largest_pers_ged + 1;
		$new_gednum["F"] = $largest_fam_ged + 1;
		$new_gednum["S"] = $largest_source_ged + 1;
		$new_gednum["R"] = $largest_address_ged + 1;
		$new_gednum["RP"] = $largest_repo_ged + 1;
		$new_gednum["N"] = $largest_text_ged + 1;
	}

	include_once(CMS_ROOTPATH_ADMIN.'include/gedcom_cls.php');
	$gedcom_cls = New gedcom_cls;

	require (CMS_ROOTPATH_ADMIN."prefixes.php");
	$loop2=count($pers_prefix);
	for ($i=0; $i<$loop2; $i++) {
		//$prefix[$i]=addslashes($pers_prefix[$i]);
		$prefix[$i]=$pers_prefix[$i];
		$prefix[$i]=str_replace("_", " ", $prefix[$i]);
		$prefix_length[$i]=strlen($prefix[$i]);
	}

	echo __('<b>STEP 3) Processing Gedcom file:</b>
<p>The following lines have to be processed without error messages...<br>
<b>Processing may take a while!!</b>').'<br>';

	// *** some providers use a timeout of 30 seconden, continue button needed. ***
	echo '<form method="post" action="'.$phpself.'" style="display : inline">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="hidden" name="family_tree_id" value="'.$family_tree_id.'">';

		echo '<input type="hidden" name="gedcom_accent" value="'.$_POST['gedcom_accent'].'">';
		if (isset($_POST['check_processed'])){
			echo '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
		}
		if (isset($_POST['show_gedcomnumbers'])){
			echo '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
		}

		echo '<input type="hidden" name="gedcom_file" value="'.$_POST['gedcom_file'].'">';
		echo '<input type="hidden" name="timeout" value="1">';
		echo __('ONLY use in case of a time-out, to continue click:').' <input type="Submit" name="step3" value="'.__('Step').' 3">';
	echo '</form><br><br>';

	//$start_time=time();
	$process_gedcom="";
	$buffer2="";

	// *** PREPARE PROGRESS BAR ***
	//$progress=$_SESSION['save_progress'];
	$progress2=$_SESSION['save_progress2'];
	//$progress_counter=$_SESSION['save_progress_counter'];

	// *** Show progress bar after timeout ***
	/*
	if (isset($_POST['timeout'])){
		echo ' '.__('Processed gedcom lines:').' '.$_SESSION['save_pointer'].'<br>';

		// *** Hide progress bar if all items are shown ***
		if (!isset($_POST['show_gedcomnumbers'])){
			// *** Show progress bar ***
			//echo '<hr width="500" align="left">';
			//echo '<div id="style_progress_bar"></div>.'; // *** Dot is necessary, otherwise progress bar doesn't work ***

			// *** HTML4 progressbar ***
			echo '<div id="progressbar">';
				echo '<div id="style_progress_bar"></div>.'; // *** Dot is necessary, otherwise progress bar doesn't work ***
			echo '</div>';
		}

	}
	*/
	
	if (!isset($_POST['show_gedcomnumbers'])){
		echo '<div id="progress" style="width:500px;border:1px solid #ccc;"></div>';
		echo '<!-- Progress information -->';
		echo '<div id="information" style="width"></div>';

		$i=$_SESSION['save_progress2']; // save number of lines processed
		$perc=$_SESSION['save_perc'];   // save percentage processed

		// Javascript for initial display of the progress bar and information (or after timeout)
		$percent=$perc."%"; if($perc==0) { $percent="0.5%"; } // show at least some green 
		echo '<script language="javascript">';
		echo 'document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#00CC00;\">&nbsp;</div>";';
		//echo 'document.getElementById("information").innerHTML="'.$i.' / '.$total.' lines processed ('.$perc.'%).";';
		echo 'document.getElementById("information").innerHTML="'.$i.' / '.$total.' '.__('lines processed').' ('.$perc.'%).";';
		echo '</script>';
		// This is for the buffer achieve the minimum size in order to flush data
		echo str_repeat(' ',1024*64);
		// Send output to browser immediately
		ob_flush(); 
		flush(); // IE

		$total=0;
		if($_SESSION['save_total']=='0') { // only first time in session: count number of lines in gedcom
			$handle = fopen($_POST["gedcom_file"], "r");
			while(!feof($handle)){
				$line = fgets($handle);
				$total++;
			}
			$_SESSION['save_total']=$total;
			fclose($handle);
		}
		$total = $_SESSION['save_total'];

		$devider=50; // determines the steps in percentages - regular: 2%
		if($total >200000) { $devider=100; } // 1% for larger files with over 200,000 lines
		if($total > 1000000) { $devider = 200; } // 0.5% for very large files

		$step = round($total/$devider); 
	}
	// *** END preparation of progress bar ***

	// *** Read file ***
	$handle = fopen($_POST["gedcom_file"], "r");

	// *** CONTINUE AFTER TIME_OUT ***
	// Set pointer if continued
	if ($_SESSION['save_pointer']>0) {
		fseek($handle, $_SESSION['save_pointer']);
	}
	if (isset($_SESSION['save_gen_program'])) {
		$gen_program=$_SESSION['save_gen_program'];
	}
	$level0='';
	//************************************

	require_once(CMS_ROOTPATH_ADMIN."include/ansel2unicode/ansel2unicode.php");
	global $a2u;
	$a2u = new Ansel2Unicode();

	function encode($buffer, $gedcom_accent){
		global $a2u;
	
		if ($gedcom_accent=="ASCII") {
			// These methods don't work :-(
			//$buffer=iconv("ASCII","UTF-8//IGNORE//TRANSLIT",$buffer);
			//$buffer=utf8_encode($buffer);
	
			// It looks like this is the only method that alway works:
			// Step 1: convert ASCII to html entities.
			$buffer=asciihtml($buffer);
			// Step 2: convert entities to UTF-8.
			$buffer = html_entity_decode($buffer, ENT_QUOTES, 'UTF-8');
		}

		if ($gedcom_accent=="ANSEL") {
			$buffer=$a2u->convert($buffer);
	
			// *** Method below is a lot faster, but accent characters are a problem ***
			//$buffer=asciihtml($buffer);
			//$buffer=anselhtml($buffer);
			//$buffer = html_entity_decode($buffer, ENT_QUOTES, 'UTF-8');
		}

		if ($gedcom_accent=="ANSI") {
			//$buffer=htmlentities($buffer,ENT_QUOTES,'ISO-8859-1');
			//$buffer=ansihtml($buffer);
			$buffer=iconv("windows-1252","UTF-8",$buffer);
			//$buffer=iconv("windows-1252","UTF-8//IGNORE//TRANSLIT",$buffer);
		}

		if ($gedcom_accent=="UTF-8") {
			// *** No conversion needed ***
		}

		//$buffer=addslashes($buffer);
		return $buffer;
	}


	// TEST: lock tables. Unfortunately not much faster than usual... ONLY FOR MYISAM TABLES!
	/*
	mysql_query("LOCK TABLES
		".$_SESSION['tree_prefix']."person
		".$_SESSION['tree_prefix']."events
		".$_SESSION['tree_prefix']."addresses
		".$_SESSION['tree_prefix']."family
		".$_SESSION['tree_prefix']."connections
		".$_SESSION['tree_prefix']."humo_location
		".$_SESSION['tree_prefix']."texts
		".$_SESSION['tree_prefix']."sources
		".$_SESSION['tree_prefix']."repositories
		WRITE;");
	*/
	// *** Batch processing for InnoDB tables ***
	$commit_counter=0;
	//$commit_records=$_POST['commit_records'];
	$commit_records=$humo_option["gedcom_read_commit_records"];
	//if ($commit_records>1){ mysql_query("START TRANSACTION"); }
	if ($commit_records>1){ $dbh->beginTransaction(); }

	while (!feof($handle)) {
		$buffer = fgets($handle, 4096);
		$buffer=rtrim($buffer,"\n\r");  // *** strip newline ***
		$buffer=ltrim($buffer," ");  // *** Strip starting spaces, for Pro-gen ***

		// *** Commit genealogical data every 100 records. CAN ONLY BE USED WITH InnoDB TABLES!! ***
		if ($commit_records>1){
			$commit_counter++;
			//if ($commit_counter>$_POST['commit_records']){
			if ($commit_counter>$humo_option["gedcom_read_commit_records"]){
				$commit_counter=0;
				// *** Save data in database ***
				//mysql_query("COMMIT");
				$dbh->commit();
				// *** Start next process batch ***
				//mysql_query("START TRANSACTION");
				$dbh->beginTransaction();
				
				$pointer=ftell($handle);
				$_SESSION['save_pointer']=$pointer;
			}
		}
		
		// *** Strip all spaces for Ancestry gedcom ***
		if ( isset($gen_program) AND $gen_program=='Ancestry.com Family Trees'){
			$buffer=rtrim($buffer," ");
		}

		$start_gedcom='';
		// *** Remove BOM header from UTF-8 BOM file ***
		if ($start_gedcom==''){
			if(substr($buffer,0,3) == pack("CCC",0xef,0xbb,0xbf)){
				// *** Remove BOM UTF-8 characters from 1st line ***
				$buffer = substr($buffer,3);
			}
		}
		if ( substr($buffer, 0, 3)=='0 @' OR $buffer=="0 TRLR"){ $start_gedcom=1; }

		// *** Start reading gedcom parts ***
		if ($start_gedcom){
			if ($process_gedcom=="person"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_person($buffer2);
	
				//save pointer value, so reading can be continued after timeout
				if ($commit_records==1){
					$pointer=ftell($handle);
					$_SESSION['save_pointer']=$pointer;
				}
				$process_gedcom="";
				$buffer2="";
				//$progress++;  // *** progress bar ***
				//$_SESSION['save_progress']=$progress;
			}

			elseif ($process_gedcom=="family"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				//$gedcom_cls -> process_family($buffer2);
				$gedcom_cls -> process_family($buffer2,0,0);
	
				//save pointer value, so reading can be continued after timeout
				if ($commit_records==1){
					$pointer=ftell($handle);
					$_SESSION['save_pointer']=$pointer;
				}

				$process_gedcom="";
				$buffer2="";
				//$progress++;  // *** progress bar ***
				//$_SESSION['save_progress']=$progress;
			}
	
			elseif ($process_gedcom=="text"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_text($buffer2);
	
				//save pointer value, so reading can be continued after timeout
				if ($commit_records==1){
					$pointer=ftell($handle);
					$_SESSION['save_pointer']=$pointer;
				}

				$process_gedcom="";
				$buffer2="";
				//$progress++;  // *** progress bar ***
				//$_SESSION['save_progress']=$progress;
			}
	
			elseif ($process_gedcom=="source"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_source($buffer2);
	
				//save pointer value, so reading can be continued after timeout
				if ($commit_records==1){
					$pointer=ftell($handle);
					$_SESSION['save_pointer']=$pointer;
				}

				$process_gedcom="";
				$buffer2="";
				//$progress++;  // *** progress bar ***
				//$_SESSION['save_progress']=$progress;
			}

			// *** Repository ***
			elseif ($process_gedcom=="repository"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_repository($buffer2);
	
				//save pointer value, so reading can be continued after timeout
				if ($commit_records==1){
					$pointer=ftell($handle);
					$_SESSION['save_pointer']=$pointer;
				}

				$process_gedcom="";
				$buffer2="";
				//$progress++;  // *** progress bar ***
				//$_SESSION['save_progress']=$progress;
			}
	
			elseif ($process_gedcom=="address"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_address($buffer2);
	
				//save pointer value, so reading can be continued after timeout
				if ($commit_records==1){
					$pointer=ftell($handle);
					$_SESSION['save_pointer']=$pointer;
				}

				$process_gedcom="";
				$buffer2="";
				//$progress++;  // *** progress bar ***
				//$_SESSION['save_progress']=$progress;
			}

			elseif ($process_gedcom=="object"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_object($buffer2);
	
				//save pointer value, so reading can be continued after timeout
				if ($commit_records==1){
					$pointer=ftell($handle);
					$_SESSION['save_pointer']=$pointer;
				}

				$process_gedcom="";
				$buffer2="";
				//$progress++;  // *** progress bar ***
				//$_SESSION['save_progress']=$progress;		
			}

		}

		// *** CHECK ***
		if (substr($buffer, -6, 6)=='@ INDI'){
			$process_gedcom="person";
			$buffer2="";
		}
		elseif (substr($buffer, -5, 5)=='@ FAM'){
			$process_gedcom="family";
			$buffer2="";
		}
		elseif (substr($buffer, 0, 3)=='0 @'){
			// *** Aldfaer text: 0 @N954@ NOTE ***
			if (strpos($buffer,'@ NOTE')>1){
				$process_gedcom="text";
				$buffer2="";
			}

			if (substr($buffer, -6, 6)=='@ SOUR'){
				$process_gedcom="source";
				$buffer2="";
			}
			elseif (substr($buffer, -6, 6)=='@ REPO'){
				$process_gedcom="repository";
				$buffer2="";
			}
			elseif (substr($buffer, -6, 6)=='@ RESI'){
				$process_gedcom="address";
				$buffer2="";
			}
			elseif (substr($buffer, -6, 6)=='@ OBJE'){
				$process_gedcom="object";
				$buffer2="";
			}
		}

		$buffer2=$buffer2.$buffer."\n";

		// *** Save level0 ***
		if (substr($buffer,0,1)=='0'){ $level0=substr($buffer,2,6); }
		// *** 1 SOUR Haza-Data ***
		if ($level0=='HEAD' AND substr($buffer,2,4)=='SOUR'){
			$gen_program=substr($buffer,7);
			$_SESSION['save_gen_program']=$gen_program;
			print "<br><br>".__('Gedcom file').": <b>$gen_program</b>, ";

			printf(__('this is an <b>%s</b> file'), $_POST["gedcom_accent"]);
			echo '<br>';

			// NEW tree<->gedcom connection - write gedcom to "tree_gedcom" in relevant tree
			//mysql_query("UPDATE humo_trees SET tree_gedcom='".$_POST["gedcom_file"]."',
			//	tree_gedcom_program='".$gen_program."'
			//	WHERE tree_prefix='".$_SESSION['tree_prefix']."'") or die("tree_gedcom_error ".mysql_error());
			$dbh->query("UPDATE humo_trees SET tree_gedcom='".$_POST["gedcom_file"]."',
				tree_gedcom_program='".$gen_program."'
				WHERE tree_prefix='".$_SESSION['tree_prefix']."'");				
			// END	
			
			// *** progress bar ***
			/*
			if (!isset($_POST['show_gedcomnumbers'])){
				//echo '<hr width="500" align="left">';
				//echo '<div id="style_progress_bar"></div>.';   // dot is nessecary, otherwise progress bar doesn't work...

				// *** HTML4 progressbar ***
				echo '<div id="progressbar">';
					echo '<div id="style_progress_bar"></div>.'; // dot is nessecary, otherwise progress bar doesn't work...
				echo '</div>';
			}
			*/
		}

		// *** progress bar ***
		//if (!isset($_POST['show_gedcomnumbers']) AND $progress>($progress_counter/500)){
		if (!isset($_POST['show_gedcomnumbers'])) {
			$i++;
			$_SESSION['save_progress2']=$i;

			// Calculate the percentage
			if($i%$step==0) {
				if($devider==50)  {$perc+=2; }
				elseif($devider==100) { $perc+=1; }
				elseif($devider==200) { $perc+=0.5; }
				$_SESSION['save_perc']=$perc;
				$percent = $perc."%";
				 
				// Javascript for updating the progress bar and information
				echo '<script language="javascript">';
				echo 'document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#00CC00;\">&nbsp;</div>";';
				echo 'document.getElementById("information").innerHTML="'.$i.' / '.$total.' '.__('lines processed').' ('.$percent.')";';
				echo '</script>';    

				// This is for the buffer achieve the minimum size in order to flush data
				echo str_repeat(' ',1024*64);    

				// Send output to browser immediately
				ob_flush();
				flush(); // for IE

			}

			/*

			$progress2++;
			$_SESSION['save_progress2']=$progress2;

			echo '<script type="text/javascript">var width='.$progress2.';
			var style_progress_bar = document.getElementById("style_progress_bar");
			style_progress_bar.style.width = width + "px"</script>';

			$progress=0;
			$_SESSION['save_progress']=$progress;
			*/
		}

	}
	fclose($handle);

	// *** End of MyISAM batch processing ***
	// mysql_query("UNLOCK TABLES;");
	// *** End of InnoDB batch processing ***
	//if ($commit_records>1){ mysql_query("COMMIT"); }
	if ($commit_records>1){ $dbh->commit(); }

	// *** Fill progress bar ***
	/*
	if (!isset($_POST['show_gedcomnumbers'])){
		echo '<script type="text/javascript">var width=500;
		var style_progress_bar = document.getElementById("style_progress_bar");
		style_progress_bar.style.width = width + "px"</script>';
	}
	*/

	// *** Show endtime ***
	$end_time=time();

	printf('<br>'.__('Reading in the file took: %d seconds').'<br>', $end_time-$_SESSION['save_starttime']);

	//*** Show "non-processed gedcom items" ***
	if (isset($_POST['check_processed'])){
		echo '<div style="height:350px;width:900px; overflow-y: scroll; white-space:nowrap;">';
			echo '<table class="humo" border="1" cellspacing="0">';
			echo '<tr><th>nr.</th><th colspan=5>'.__('Non-processed items').'</th></tr>';
			echo '<tr><th><br></th><th>'.__('Level').' 0</th><th>'.__('Level').' 1</th><th>'.__('Level').' 2</th><th>'.__('Level').' 3</th><th>'.__('text').'</th></tr>';
			if (isset($not_processed)){
				for ($i=0; $i<count($not_processed);$i++){ echo '<tr><td>'.($i+1).'</td><td>'.$not_processed[$i].'</td></tr>'."\n"; }
			}
			else{
				echo '<tr><td>0</td><td colspan=4>'.__('All items have been processed!').'</td></tr>'."\n";
			}
			echo '</table><br>';
		echo '</div>';
	}
	if (!isset($_POST['show_gedcomnumbers'])) {
		echo '<script language="javascript">';
		echo 'document.getElementById("progress").innerHTML="<div style=\"width:100%;background-color:#00CC00;\">&nbsp;</div>";';
		echo 'document.getElementById("information").innerHTML="'.$total.' / '.$total.' '.__('lines processed').' (100%).";';
		echo '</script>';
		ob_flush();
		flush(); // for IE
	}
	echo '<br><form method="post" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
	echo '<input type="hidden" name="family_tree_id" value="'.$family_tree_id.'">';
	echo '<input type="hidden" name="gen_program" value="'.$gen_program.'">';
	echo '<input type="Submit" name="step4" value="'.__('Step').' 4">';
	echo '</form>';

	if(CMS_SPECIFIC=="Joomla") {
		echo '<br><br><br><br><br><br><br><br><br>'; //make sure left menu won't run off bottom of screen if content is not long enough
	}

}

// *** Step 4 ***
if (isset($_POST['step4'])){
	$start_time=time();
	$gen_program=$_POST['gen_program'];

	print '<b>'.__('STEP 4) Processing single persons:').'</b><br>';

	// *** To proceed if a (30 seconds) timeout has occured ***
	echo '<form method="post" action="'.$phpself.'">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="hidden" name="family_tree_id" value="'.$family_tree_id.'">';
		echo '<input type="hidden" name="gen_program" value="'.$_POST['gen_program'].'">';
		if (isset($_POST['check_processed'])){
			echo '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
		}
		if (isset($_POST['show_gedcomnumbers'])){
			echo '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
		}
		echo '<br>'.__('ONLY use in case of a time-out, to continue click:').' <input type="Submit" name="step4" value="'.__('Step').' 4">';
	echo '</form><br>';

	print '&gt;&gt;&gt; '.__('Processing single persons...');

	// *** Check for seperate saved texts in database (used in aldfaer program, and some other programs) ***
	//$zoektekst=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."texts",$db);
	//$aantal_teksten = mysql_num_rows($zoektekst);
	//echo 'AANTAL TEKSTEN'.$aantal_teksten;

	// *** Process text by name etc. ***
	//$person_qry=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person",$db);
	//$person_qry=mysql_query("SELECT pers_id, pers_name_text, pers_firstname, pers_lastname FROM ".$_SESSION['tree_prefix']."person",$db);
	$person_qry=$dbh->query("SELECT pers_id, pers_name_text, pers_firstname, pers_lastname FROM ".$_SESSION['tree_prefix']."person");
	//while ($personDb=mysql_fetch_object($person_qry)){
	while ($personDb=$person_qry->fetch(PDO::FETCH_OBJ)){
		//*** Haza-data option: text IN name where "*" is. ***
		if ($personDb->pers_name_text){
			$position=strpos($personDb->pers_firstname, '*');
			//if ( $position!== false AND $personDb->pers_name_text ){
			if ($position!== false){
				// pers_name_text change into: process_text(pers_name_text)
				$pers_firstname=substr($personDb->pers_firstname,0,$position).
					$personDb->pers_name_text.substr($personDb->pers_firstname,$position+1);
				$pers_name_text='';
				$sql="UPDATE ".$_SESSION['tree_prefix']."person
					SET pers_firstname='$pers_firstname', pers_name_text=''
					WHERE pers_id=$personDb->pers_id";
				//mysql_query($sql, $db) or die(mysql_error());
				$dbh->query($sql);
			}
			$position=strpos($personDb->pers_lastname, '*');
			//if ( $position!== false AND $personDb->pers_name_text ){
			if ($position!== false){
				//pers_name_text change into: process_text(pers_name_text)
				$pers_lastname=substr( $personDb->pers_lastname,0,$position).
					$personDb->pers_name_text.substr( $personDb->pers_lastname,$position+1);
				$pers_name_text='';
				$sql="UPDATE ".$_SESSION['tree_prefix']."person SET pers_lastname='$pers_lastname', pers_name_text=''
					WHERE pers_id=$personDb->pers_id";
				//mysql_query($sql, $db) or die(mysql_error());
				$dbh->query($sql);
			}
		}
	}

	print '<br>&gt;&gt;&gt; '.__('Counting persons and families and enter into database...').' ';
	// *** Calculate number of persons and families ***
	//$person_qry=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person",$db);
	//$person_qry=mysql_query("SELECT pers_id FROM ".$_SESSION['tree_prefix']."person",$db);
	//$persons=mysql_num_rows($person_qry);
	$person_qry=$dbh->query("SELECT pers_id FROM ".$_SESSION['tree_prefix']."person");
	$persons=$person_qry->rowCount();	

	//$family_qry=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."family",$db);
	//$family_qry=mysql_query("SELECT fam_id FROM ".$_SESSION['tree_prefix']."family",$db);
	//$families=mysql_num_rows($family_qry);
	$family_qry=$dbh->query("SELECT fam_id FROM ".$_SESSION['tree_prefix']."family");
	$families=$family_qry->rowCount();	

	$tree_date=date("Y-m-d H:i");
	$sql="UPDATE humo_trees SET
	tree_persons='".$persons."',
	tree_families='".$families."',
	tree_date='".$tree_date."'
	WHERE tree_prefix='".$_SESSION['tree_prefix']."'";
	//mysql_query($sql,$db) or die(mysql_error());
	$dbh->query($sql);

	// if a humo_location table exists, refresh the location_status column
	//if (isset($_POST['process_geo_location']) AND mysql_num_rows( mysql_query("SHOW TABLES LIKE 'humo_location'", $db))) {
	//if ($humo_option["gedcom_read_process_geo_location"]=='y' AND mysql_num_rows( mysql_query("SHOW TABLES LIKE 'humo_location'", $db))){
	$res = $dbh->query("SHOW TABLES LIKE 'humo_location'");
	if ($humo_option["gedcom_read_process_geo_location"]=='y' AND $res->rowCount()) {
		// after import, and ONLY for people with a humo_location table for googlemaps, refresh the location_status fields
		// first, make sure the location_status column exists. If not create it
		echo '<br><br>>>> '.__('Updating location database...').'<br>';
		//$result = mysql_query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
		//$exists = mysql_num_rows($result);
		$result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
		$exists = $result->rowCount();		
		if(!$exists) {
			//mysql_query("ALTER TABLE humo_location ADD location_status TEXT DEFAULT '' AFTER location_lng");
			$dbh->query("ALTER TABLE humo_location ADD location_status TEXT DEFAULT '' AFTER location_lng");
		}
		//$all_loc = mysql_query("SELECT location_location FROM humo_location");
		$all_loc = $dbh->query("SELECT location_location FROM humo_location");
		//while($all_locDb = mysql_fetch_object($all_loc)) {  
		while($all_locDb = $all_loc->fetch(PDO::FETCH_OBJ)) {  
			$loca_array[$all_locDb->location_location] = "";
		}
		$status_string = ""; 

		$tree_pref_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		//$tree_pref_result = mysql_query($tree_pref_sql,$db);
		//while ($tree_prefDb=mysql_fetch_object($tree_pref_result)){	
		$tree_pref_result = $dbh->query($tree_pref_sql);
		while ($tree_prefDb=$tree_pref_result->fetch(PDO::FETCH_OBJ)){			

			//$result=mysql_query("SELECT pers_birth_place, pers_bapt_place, pers_death_place, pers_buried_place FROM ".$tree_prefDb->tree_prefix."person",$db);
			$result=$dbh->query("SELECT pers_birth_place, pers_bapt_place, pers_death_place, pers_buried_place FROM ".$tree_prefDb->tree_prefix."person");

			//while($resultDb = mysql_fetch_object($result)) { 
			while($resultDb = $result->fetch(PDO::FETCH_OBJ)) { 
				if (isset($loca_array[$resultDb->pers_birth_place]) AND strpos($loca_array[$resultDb->pers_birth_place],$tree_prefDb->tree_prefix."birth ")===false) { 
					$loca_array[$resultDb->pers_birth_place] .= $tree_prefDb->tree_prefix."birth ";  
				}
				if (isset($loca_array[$resultDb->pers_bapt_place]) AND strpos($loca_array[$resultDb->pers_bapt_place],$tree_prefDb->tree_prefix."bapt ")===false) {   
					$loca_array[$resultDb->pers_bapt_place] .= $tree_prefDb->tree_prefix."bapt ";  
				}
				if (isset($loca_array[$resultDb->pers_death_place]) AND strpos($loca_array[$resultDb->pers_death_place],$tree_prefDb->tree_prefix."death ")===false) {   
					$loca_array[$resultDb->pers_death_place] .= $tree_prefDb->tree_prefix."death "; 
				}
				if (isset($loca_array[$resultDb->pers_buried_place]) AND strpos($loca_array[$resultDb->pers_buried_place],$tree_prefDb->tree_prefix."buried ")===false) { 
					$loca_array[$resultDb->pers_buried_place] .= $tree_prefDb->tree_prefix."buried ";  
				}
			}
		}
		foreach($loca_array as $key => $value) {
			//mysql_query("UPDATE humo_location SET location_status = '".$value."' WHERE location_location = '".addslashes($key)."'");
			$dbh->query("UPDATE humo_location SET location_status = '".$value."' WHERE location_location = '".addslashes($key)."'");
		}			 		
	} // end refresh location_status column



	// *** Jeroen Beemster Jan 2006. Code rewritten in June 2013 by Huub. Order children and marriages ***
	if ($humo_option["gedcom_read_order_by_date"]=='y') {
		function date_string($text) {
			$text=str_replace("JAN", "01", $text);
			$text=str_replace("FEB", "02", $text);
			$text=str_replace("MAR", "03", $text);
			$text=str_replace("APR", "04", $text);
			$text=str_replace("MAY", "05", $text);
			$text=str_replace("JUN", "06", $text);
			$text=str_replace("JUL", "07", $text);
			$text=str_replace("AUG", "08", $text);
			$text=str_replace("SEP", "09", $text);
			$text=str_replace("OCT", "10", $text);
			$text=str_replace("NOV", "11", $text);
			$text=str_replace("DEC", "12", $text);
			$returnstring = substr($text,-4).substr(substr($text,-7),0,2).substr($text,0,2);
			return $returnstring;
			// Solve maybe later: date_string 2 mei is smaller then 10 may (2 birth in 1 month is rare...).
		}

		print '<br>&gt;&gt;&gt; '.__('Order children...');

		//$fam_qry=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."family WHERE fam_children!=''",$db);
		//while ($famDb=mysql_fetch_object($fam_qry)){
		$fam_qry=$dbh->query("SELECT * FROM ".$_SESSION['tree_prefix']."family WHERE fam_children!=''");
		while ($famDb=$fam_qry->fetch()){		
			$child_array=explode(";",$famDb->fam_children);
			$nr_children = count($child_array);
			if ($nr_children > 1) {
				unset ($children_array);
				for ($i=0; $i<$nr_children; $i++){
					//$child=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
					//	WHERE pers_gedcomnumber='".$child_array[$i]."'",$db);
					//@$childDb=mysql_fetch_object($child);
					$child=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
						WHERE pers_gedcomnumber='".$child_array[$i]."'");
					@$childDb=$child->fetch(PDO::FETCH_OBJ);
					
					$child_array_nr=$child_array[$i];
					if ($childDb->pers_birth_date){
						$children_array[$child_array_nr]=date_string($childDb->pers_birth_date);
					}
					elseif ($childDb->pers_bapt_date){
						$children_array[$child_array_nr]=date_string($childDb->pers_bapt_date);
					}
					else{
						$children_array[$child_array_nr]='';
					}
				}

				asort ($children_array);

				$fam_children='';
				foreach ($children_array as $key => $val) {
					if ($fam_children!=''){ $fam_children.=';'; }
					$fam_children.=$key;
				}

				if ($famDb->fam_children!=$fam_children){
					$sql = "UPDATE ".$_SESSION['tree_prefix']."family SET fam_children='".$fam_children."' WHERE fam_id='".$famDb->fam_id."'";
					//mysql_query($sql, $db) or die(mysql_error());
					$dbh->query($sql);
				}
			}
		}
	}

	/*
	// *** OLD CODE!!!! This code must be rewritten if it's used!!!!! ***
	function Sorteerhuwelijken($family_array2,$persoonDb2,$db){
		$max = substr_count($persoonDb2->fams,";");
		if (substr_count($persoonDb2->fams, ";")>0) {
			for ($j=0; $j<substr_count($persoonDb2->fams, ";"); $j++){
				for ($i=0; $i<$max; $i++){
					$fams1=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."gezin WHERE gedcomnummer=$family_array2[$i]",$db);
					$famsDb1=mysql_fetch_object($fams1);
					$ii = $i+1;
					$fams2=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."gezin WHERE gedcomnummer=$family_array2[$ii]",$db);
					$famsDb2=mysql_fetch_object($fams2);
					if (date_string($famsDb1->trdatum) > date_string($famsDb2->trdatum)) {
						$tempfams = $family_array2[$i];
						$family_array2[$i] = $family_array2[$i+1];
						$family_array2[$i+1] = $tempfams;
					} 
				}
				$max--;
			}
		}
		return $family_array2;
	}

	// *** Order families ***
	echo "<P><B>Huwelijken sorteren:</B>";
	$persoon=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."persoon",$db);
	while ($persoonDb=mysql_fetch_object($persoon)) {
		$family_array=explode(";",$persoonDb->fams);
		if (substr_count($persoonDb->fams, ";") > 0) {
			$family_array = Sorteerhuwelijken($family_array,$persoonDb,$db); 
			$s = $family_array[0];
			for ($i=1; $i<=substr_count($persoonDb->fams, ";"); $i++){
				$s = $s.";".$family_array[$i];
			}
			if ($persoonDb->fams <> $s) {
				echo "<BR>Gezin: ".$persoonDb->gedcomnummer;
				echo " Van: ".$persoonDb->fams;
				echo "<BR>Gezin: ".$persoonDb->gedcomnummer;
				echo " Naar: ".$s;
				$Sql = "UPDATE ".$_SESSION['tree_prefix']."persoon SET fams='".$s."' WHERE id =".$persoonDb->id;
				mysql_query($Sql); 
				mysql_query("COMMIT"); 
			}
		} 
	}
	*/

	
	// Show process time:
	$end_time=time();
	printf('<p>'.__('Processing took: %d seconds').'<br>', $end_time-$start_time);
	print __('No error messages? In this case the database is ready for use!');
	
	if(CMS_SPECIFIC=="Joomla") {
		printf('<p><b>'.__('Ready! Now click %s to watch the family tree').'</b><br>', ' <a href="index.php?option=com_humo-gen&task=index">index.php</a> ');

		echo '<br><br><br><br><br><br><br><br><br>'; //make sure left menu won't run off bottom of screen if content is not long enough
	}
	else {
		printf('<p><b>'.__('Ready! Now click %s to watch the family tree').'</b><br>', ' <a href="'.CMS_ROOTPATH.'index.php">index.php</a> ');
	}

} // end of read gedcom (step 4)
?>