<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
//error_reporting(E_ALL);
if(isset($_POST['tree_prefix'])) {
	$_SESSION['tree_prefix'] = $_POST['tree_prefix'];
}

include_once(CMS_ROOTPATH."menu.php");
include_once (CMS_ROOTPATH.'include/person_cls.php');
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");

//echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'googlemaps/gslider.js"></script>';
echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'googlemaps/namesearch.js"></script>';

//cover map with loading animation + half opaque background till page is fully loaded
//using the slider/button before complete page load goes wrong
echo '<div id="wait" style="background:url(images/loader.gif) no-repeat center center; opacity:0.6; filter:alpha(opacity=60); position:fixed; top:70px; margin-left:auto; margin-right:auto; height:610px; width:1000px; background-color:#000000; z-index:100"></div>';

echo '<div style="position:relative"> ';  // div with table for all menu bars (2 + optional third)
echo '<table>';

// TOP MENU BAR
echo '<tr><td style="border:1px solid #bdbdbd; width:995px; background-color:#d8d8d8">';
if($language['dir']!="rtl") { echo '<div style="float:left">'; }  // div tree choice
else { echo '<div style="float:right">'; }

// SELECT FAMILY TREE
$tree_prefix_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
$tree_prefix_result = $dbh->query($tree_prefix_sql);
$count=0;
echo '<form method="POST" action="maps.php" style="display : inline;">';
echo '<select size="1" name="tree_prefix" onChange="this.form.submit();">';
	echo '<option value="">'.__('Select a family tree:').'</option>';
	//while ($tree_prefixDb=mysql_fetch_object($tree_prefix_result)){
	while ($tree_prefixDb=$tree_prefix_result->fetch(PDO::FETCH_OBJ)){
		// *** Check if family tree is shown or hidden for user group ***
		$hide_tree_array=explode(";",$user['group_hide_trees']);
		$hide_tree=false;
		for ($x=0; $x<=count($hide_tree_array)-1; $x++){
			if ($hide_tree_array[$x]==$tree_prefixDb->tree_id){ $hide_tree=true; }
		}
		if ($hide_tree==false){
			$selected='';
			if (isset($_SESSION['tree_prefix'])){
				if ($tree_prefixDb->tree_prefix==$_SESSION['tree_prefix']){ $selected=' SELECTED'; }
			}
			else {
				if($count==0) { $_SESSION['tree_prefix'] = $tree_prefixDb->tree_prefix; $selected=' SELECTED'; }
			}
			$treetext=show_tree_text($tree_prefixDb->tree_prefix, $selected_language);
			echo '<option value="'.$tree_prefixDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
			$count++;
		}
	}
echo '</select>';
echo '</form>';

// SET BIRTH OR DEATH MAP
if(!isset($_SESSION['type_birth']) AND !isset($_SESSION['type_death'])) { $_SESSION['type_birth']=1; $_SESSION['type_death']=0; }
if(isset($_POST['map_type']) AND $_POST['map_type']=="type_birth" ) { $_SESSION['type_birth']=1; $_SESSION['type_death']=0; }
if(isset($_POST['map_type']) AND $_POST['map_type']=="type_death" ) { $_SESSION['type_death']=1; $_SESSION['type_birth']=0; }

// BIRTH LOCATION BUTTON
echo  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
if($_SESSION['type_birth']==1) {
	echo  ' <input style="font-size:14px" type="button" value="'.__('Mark all birth locations').'" onclick="makeSelection(3)">  ';
}
elseif($_SESSION['type_death']==1) {
	echo  ' <input style="font-size:14px" type="button" value="'.__('Mark all death locations').'" onclick="makeSelection(3)">  ';
}
echo '</div>';

// slider defaults
$realmin=1560;  // first year shown on slider
$step="50";     // interval
$minval="1510"; // OFF position (first year minus step, year is not shown)
$yr = date("Y");

// check for stored min value, created with google maps admin menu
$query = "SELECT setting_value FROM humo_settings WHERE setting_variable='gslider_".safe_text($_SESSION['tree_prefix'])."' ";
//$result = mysql_query($query);
$result = $dbh->query($query);

//if (mysql_num_rows($result)) {
if($result->rowCount() > 0) {
	//$sliderDb=mysql_fetch_object($result);
	$sliderDb=$result->fetch(PDO::FETCH_OBJ);
	$realmin = $sliderDb->setting_value;
	$step = floor(($yr - $realmin) / 9);
	$minval = $realmin - $step;
}

$qry="SELECT setting_value FROM humo_settings WHERE setting_variable='gslider_default_pos'";
//$result=mysql_query($qry);
$result = $dbh->query($qry);
//if(mysql_num_rows($result)) {
if($result->rowCount() > 0) {
	//$def = mysql_fetch_array($result);
	$def = $result->fetch(); // defaults to array
	$slider_def = $def['setting_value'];
	if($slider_def=="off") { $defaultyr = $minval; $default_display = "-------->"; $makesel=""; } // slider at leftmost position
	else { $defaultyr = $yr; $default_display = $defaultyr; $makesel = " makeSelection(3); "; } // slider ar rightmost position
}
else {
	$defaultyr = $minval; $default_display = "-------->"; $makesel=""; // slider at leftmost position (default)
}

echo ' 
	<script> 
	var minval = '.$minval.'; 
	$(function() { 
		'.$makesel.'
		$( "#slider" ).slider({
			value: '.$defaultyr.',
			min: '.$minval.',
			max: '.$yr.',
			step: '.$step.',
			slide: function( event, ui ) {
				if(ui.value == minval) { $( "#amount" ).val("------>"); }
				else if(ui.value > 2000) { $( "#amount" ).val('.$yr.'); }
				else {	$( "#amount" ).val(ui.value ); }
			}
		});
		$( "#amount" ).val("'.$default_display.'");
	});

	</script>
';

// SLIDER
if($language['dir']!="rtl") {echo '<div style="float:left">'; } // div slider text + year box
else { echo '<div style="float:right">'; }
if($_SESSION['type_birth']==1) {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Mark births until: ').'&nbsp;';
}
elseif($_SESSION['type_death']==1) {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Mark deaths until: ').'&nbsp;';
}

echo '<input type="text" id="amount" disabled="disabled" size="4" style="border:0; color:#0000CC; font-weight:normal;font-size:115%;" />';
echo '&nbsp;&nbsp;&nbsp;&nbsp;</div>';

if($language['dir']!="rtl"){ echo '<div id="slider" style="float:left;width:170px;margin-top:7px;margin-right:15px;">'; }
else { echo '<div id="slider" style="float:right;direction:ltr;width:150px;margin-top:7px;margin-right:15px;">'; }

echo '</div>';
echo '</td></tr>';

// SECOND MENU BAR
echo '<tr><td style="border:1px solid #d8d8d8;width:995px;background-color:#f2f2f2">';

// BUTTON: SEARCH BY SPECIFIC NAME
echo ' <input type="Submit" style="font-size:110%;" name="anything" onclick="document.getElementById(\'namemapping\').style.display=\'block\' ;" value="'.__('Map by specific family name(s)').'">';

// BUTTON: SEARCH BY DESCENDANTS
echo '<form method="POST" style="display:inline" name="descform" action='.$_SERVER['PHP_SELF'].'>';
echo '<input type="hidden" name="descmap" value="1">';
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="Submit" style="font-size:110%;" name="anything" value="'.__('Map by descendants of a person').'">';
echo '</form>';

// PULL-DOWN: FIND LOCATION
echo  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
//$result = mysql_query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
//$exists = mysql_num_rows($result);
$result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
//if($exists) {
if($result->rowCount()>0) {
	if($_SESSION['type_birth']==1) {
		$loc_search = "SELECT * FROM humo_location WHERE location_status LIKE '%".$_SESSION['tree_prefix']."birth%' OR location_status LIKE '%".$_SESSION['tree_prefix']."bapt%' OR location_status = '' ORDER BY location_location";
	}
	if($_SESSION['type_death']==1) {
		$loc_search = "SELECT * FROM humo_location WHERE location_status LIKE '%".$_SESSION['tree_prefix']."death%' OR location_status LIKE '%".$_SESSION['tree_prefix']."buried%' OR location_status = '' ORDER BY location_location";
	}
}
else { // this is for backward compatibility - if someone doesn't yet have a location_status column: show all locations as until now
	$loc_search = "SELECT * FROM humo_location ORDER BY location_location";
}
//$loc_search_result = mysql_query($loc_search,$db) or die(mysql_error());
$loc_search_result = $dbh->query($loc_search);
echo '<form method="POST" action="" style="display : inline;">';
echo '<select style="max-width:250px" onChange="findPlace()" size="1" id="loc_search" name="loc_search">';
echo '<option value="toptext">'.__('Find location on the map').'</option>';
//while ($loc_searchDb=mysql_fetch_object($loc_search_result)){
while($loc_searchDb=$loc_search_result->fetch(PDO::FETCH_OBJ)) {	
	echo '<option value="'.$loc_searchDb->location_id.','.$loc_searchDb->location_lat.','.$loc_searchDb->location_lng.'">'.$loc_searchDb->location_location.'</option>';
	$count++;
}
echo '</select>';
echo '</form>';

// PULL-DOWN: births/bapt OR death/burial
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<form name="type_form" method="POST" action="" style="display : inline;">';
echo '<select style="max-width:200px" size="1" onChange="document.type_form.submit()" id="map_type" name="map_type">';
$selected=''; if(isset($_SESSION['type_birth']) AND $_SESSION['type_birth']==1 ) { $selected = ' selected '; }
echo '<option value="type_birth" '.$selected.'>'.__('Map by births').'</option>';
$selected=''; if(isset($_SESSION['type_death']) AND $_SESSION['type_death']==1 )  { $selected = ' selected '; }
echo '<option value="type_death" '.$selected.'>'.__('Map by deaths').'</option>';
echo '</select>';
//echo '<input type=submit value=Submit>';
echo '</form>';

// HELP POPUP
if(CMS_SPECIFIC=="Joomla") {
	echo '<div class="fonts table_header '.$rtlmarker.'sddm" style="z-index:400;position:absolute;top:20px;left:10px;">';
	$popwidth="width:700px;";
}
else {
	echo '<div class="fonts table_header '.$rtlmarker.'sddm" style="display:inline;float:right;">';
	$popwidth="";
}
echo '<a href="#"';
echo ' style="display:inline" ';
if(CMS_SPECIFIC=="Joomla") {
	echo 'onmouseover="mopen(event,\'help_menu\',0,0)"';
}
else {
	echo 'onmouseover="mopen(event,\'help_menu\',10,150)"';
}
echo 'onmouseout="mclosetime()">';
echo '<strong>'.__('Help').'</strong>';
echo '</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<div class="sddm_fixed" style="'.$popwidth.' z-index:400; text-align:'.$alignmarker.'; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

echo __('<b>Top menu line:</b><br>
<ul><li>Choose family tree. On sites with multiple family trees here you can choose which tree to map.</li>
<li>All birth locations". This button will place markers for all birth locations in the tree, irrespective of the dates of birth. Also people with no known birth date are included.</li>
<li>The "births until" slider. With this slider you can mark the birthplaces of persons who where born until a certain date. The slider has ten positions.</li></ul>
<b>Second menu line:</b>
<ul><li>Map by specific family name(s)". This will open a window with all family names. Mark checkboxes next to names and press "Choose" to start mapping the locations of those families only.<br>
After pressing "Choose" you will see a yellow banner near the top of the map, informing you which family names are being filtered. Now use the slider or "mark all location" button to place the markers.</li>
<li>Map by descendants". This will open a window with all persons that have descendants. Click the person whose descendants you want to map.<br>
A yellow banner will appear near the top of the map, informing which persons\' descendants are filtered. Now use the slider or "mark all location" button to place the markers.</li>
<li>Find location on the map". Here you can pick a location from all locations in the tree and zoom in to it automatically.</li></ul>
<b>The map:</b>
<ul><li>After clicking the "All locations" button or using the slider, coloured markers will be placed on the map. Inside the marker you will see the number of people born in that location.</li>
<li>There are 4 different size markers (from small to big): Red markers (over 100 people), blue markers (50-99 people), green markers (9-49 people) and yellow markers (1-9 people)</li>
<li>When you hover with the mouse over a marker a "tooltip" will show with the name of the location.</li>
<li>When you click on the marker you will see two links.</li>
<li>The first link will open a new browser tab with the Wikipedia entry about this location (if such an entry exists).</li>
<li>The second link will present (in the Info Window itself) a list of all persons born in this location.</li>
<li>The names in this list are clickable and will open a new browser tab with the family page of this person.</li>');

echo '</ul>';
echo '</div>';
echo '</div>';

echo '</td></tr>';

// OPTIONAL THIRD (YELLOW) NOTIFICATION MENU BAR
echo '<tr><td style="border:1px solid #bdbdbd; width:995px; background-color:#d8d8d8">';

// NOTIFICATION: SEARCHING BY SPECIFIC NAMES
$flag_namesearch='';
if(isset($_POST['items'])) {
	// for use in google_initiate.php
	echo '<div id="name_search" style="border: 0px solid #bdbdbd;background-color:#f3f781;">';
	$flag_namesearch = $_POST['items'];
	$names='';
	echo '&nbsp;'.__('Mapping with specific name(s): ');
	foreach($flag_namesearch as $value) {
		$pos = strpos($value,'_');
		$pref=''; $last='';
		$last = substr($value,0,$pos);
		$pref = substr($value,$pos+1); if($pref!='') { $pref = $pref.' '; }
		//$names .= $value.", ";
		$names .= $pref.$last.", ";
	}
	$names = substr($names,0,-2); // take off last ", "
	echo $names;
	echo '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'">'.__('Switch name filter off').'</a>';
	echo '</div>';
}

// FUNCTION TO FIND DESCENDANTS OF CHOSEN PERSON AND SHOW NOTIFICATION
$flag_desc_search=0; $chosenperson=''; $persfams = '';
if(isset($_GET['persged']) AND isset($_GET['persfams'])) {
	$flag_desc_search=1;
	$chosenperson=	$_GET['persged'];
	$persfams = $_GET['persfams'];
	$persfams_arr = explode(';',$persfams);
	//$myresult=mysql_query("SELECT pers_lastname, pers_firstname, pers_prefix FROM ".$_SESSION['tree_prefix'].'person
	//WHERE pers_gedcomnumber="'.$chosenperson.'"',$db);
	//$myresultDb=mysql_fetch_object($myresult);
	$myresult = $dbh->query("SELECT pers_lastname, pers_firstname, pers_prefix FROM ".$_SESSION['tree_prefix'].'person
		WHERE pers_gedcomnumber="'.$chosenperson.'"');
	$myresultDb=$myresult->fetch(PDO::FETCH_OBJ);
	$chosenname = $myresultDb->pers_firstname.' '.strtolower(str_replace('_','',$myresultDb->pers_prefix)).' '.$myresultDb->pers_lastname;

	$gn=0;   // generatienummer
	
	// prepared statements for use in outline loops
	$family_prep = $dbh->prepare("SELECT fam_man, fam_woman FROM ".safe_text($_SESSION['tree_prefix'])."family WHERE fam_gedcomnumber=?");
	$family_prep->bindParam(1,$fam_prep_var);
    $person_prep = $dbh->prepare("SELECT pers_fams FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber=?");	
	$person_prep->bindParam(1,$pers_prep_var);	
	$family_prep2 = $dbh->prepare("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."family WHERE fam_gedcomnumber=?");
	$family_prep2->bindParam(1,$fam_prep_var2);
	$person_man_prep= $dbh->prepare("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber=?");
	$person_man_prep->bindParam(1,$pers_man_prep_var);
	
	function outline($family_id,$main_person,$gn) {

		global $db, $desc_array, $dbh;
		global $language, $dirmark1, $dirmark1;
		global $family_prep, $fam_prep_var, $person_prep, $pers_prep_var;
		global $family_prep2, $fam_prep_var2, $person_man_prep, $pers_man_prep_var;
		$family_nr=1; //*** Process multiple families ***
		/*
		$family=mysql_query("SELECT fam_man, fam_woman FROM ".$_SESSION['tree_prefix'].'family
		WHERE fam_gedcomnumber="'.$family_id.'"',$db);
		@$familyDb=mysql_fetch_object($family) or die("Geen geldig gezinsnummer.");
		*/
		$fam_prep_var = $family_id;
		$family_prep->execute(); 
		try{
			@$familyDb=$family_prep->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			print "No valid family number / Geen geldig gezinsnummer<br/>";
		}	
		$parent1=''; $parent2='';	$change_main_person=false;

		// *** Standard main_person is the father ***
		if ($familyDb->fam_man){
			$parent1=$familyDb->fam_man;
		}
		// *** If mother is selected, mother will be main_person ***
		if ($familyDb->fam_woman==$main_person){
			$parent1=$familyDb->fam_woman;
			$change_main_person=true;
		}

		// *** Check family with parent1: N.N. ***
		if ($parent1){
			// *** Save man's families in array ***
			//$person_qry=mysql_query("SELECT pers_fams FROM ".$_SESSION['tree_prefix']."person
			//	WHERE pers_gedcomnumber='$parent1'",$db);
			//@$personDb=mysql_fetch_object($person_qry);
			$pers_prep_var = $parent1;
			$person_prep->execute();
			@$personDb=$person_prep->fetch(PDO::FETCH_OBJ);
			$marriage_array=explode(";",$personDb->pers_fams);
			$nr_families=substr_count($personDb->pers_fams, ";");
		}
		else{
			$marriage_array[0]=$family_id;
			$nr_families="0";
		}

		// *** Loop multiple marriages of main_person ***
		for ($parent1_marr=0; $parent1_marr<=$nr_families; $parent1_marr++){
			$id=$marriage_array[$parent1_marr];
			//$family=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."family WHERE fam_gedcomnumber='$id'",$db);
			//@$familyDb=mysql_fetch_object($family);
			$fam_prep_var2 = $id;
			$family_prep2->execute();
			@$familyDb = $family_prep2->fetch(PDO::FETCH_OBJ);

			// *** Privacy filter man and woman ***
			//$person_man=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person WHERE pers_gedcomnumber='$familyDb->fam_man'",$db);
			//@$person_manDb=mysql_fetch_object($person_man);
			$pers_man_prep_var=$familyDb->fam_man;
			$person_man_prep->execute();
			@$person_manDb=$person_man_prep->fetch(PDO::FETCH_OBJ);
			//$person_woman=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person WHERE pers_gedcomnumber='$familyDb->fam_woman'",$db);
			//@$person_womanDb=mysql_fetch_object($person_woman);
			$pers_man_prep_var=$familyDb->fam_woman;
			$person_man_prep->execute();			
			@$person_womanDb=$person_man_prep->fetch(PDO::FETCH_OBJ);
			
			// *************************************************************
			// *** Parent1 (normally the father)                         ***
			// *************************************************************
			if ($familyDb->fam_kind!='PRO-GEN'){  //onecht kind, vrouw zonder man
				if ($family_nr==1){
				// *** Show data of man ***

					if ($change_main_person==true){
						if($person_womanDb->pers_birth_place OR $person_womanDb->pers_bapt_place) {
							$desc_array[]=$person_womanDb->pers_gedcomnumber;
						}
					}
					else{
						if($person_manDb->pers_birth_place OR $person_manDb->pers_bapt_place) {
							$desc_array[]=$person_manDb->pers_gedcomnumber;
						}
					}
				}
				else{  }   // don't take person twice!
				$family_nr++;
			} // *** end check of PRO-GEN ***

			// *************************************************************
			// *** Children                                              ***
			// *************************************************************
			if ($familyDb->fam_children){
				$childnr=1;
				$child_array=explode(";",$familyDb->fam_children);

				for ($i=0; $i<=substr_count("$familyDb->fam_children", ";"); $i++){
					//$child=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person WHERE pers_gedcomnumber='$child_array[$i]'",$db);
					//@$childDb=mysql_fetch_object($child);
					$pers_man_prep_var=$child_array[$i];
					$person_man_prep->execute();			
					@$childDb=$person_man_prep->fetch(PDO::FETCH_OBJ);
			
					// *** Build descendant_report ***
					if ($childDb->pers_fams){
						// *** 1e family of child ***
						$child_family=explode(";",$childDb->pers_fams);
						$child1stfam=$child_family[0];
						outline($child1stfam,$childDb->pers_gedcomnumber,$gn);  // recursive
					}
					else{    // Child without own family
						if($childDb->pers_birth_place OR $childDb->pers_bapt_place) {
							$desc_array[]=$childDb->pers_gedcomnumber;
						}
					}
				}
					$childnr++;
			}

		} // Show  multiple marriages
	} // End of outline function

	// ******* Start function here - recursive if started ******
	$desc_array = '';
	outline($persfams_arr[0], $chosenperson, $gn);
	if($desc_array != '') {
		$desc_array = array_unique($desc_array); // removes duplicate persons (because of related ancestors)
	}

	echo '<div id="desc_search" style="border: 0px solid #bdbdbd;background-color:#f3f781;">';
	if($desc_array!='') {
		echo '&nbsp;'.__('Map by descendants of: ').$chosenname.'&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'">'.'&nbsp;|&nbsp;'.__('Switch descendant filter off').'</a>' ;
	}
	else {
		echo '&nbsp;'.__('No known birth places amongst descendants').'&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'">'.__('Close').'</a>';
	}
	echo '</div>';
} // end descendant notifications

echo '</td></tr>';
echo '</table>';
echo '</div>';
// END MENU

// FIXED WINDOW WITH LIST OF SPECIFIC FAMILY NAMES TO MAP BY
$fam_search = "SELECT * , CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) as totalname FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE (pers_birth_place != '' OR (pers_birth_place='' AND pers_bapt_place != '')) AND pers_lastname != '' GROUP BY totalname ";
//$fam_search_result = mysql_query($fam_search,$db);
$fam_search_result = $dbh->query($fam_search);
echo '<div id="namemapping" style="display:none; z-index:100; position:absolute; top:90px; margin-left:10px; height:460px; width:250px; border:1px solid #000; background:#d8d8d8; color:#000; margin-bottom:1.5em;">';
echo '<form method="POST" action="maps.php" name="yossi" style="display : inline;">';
echo '<table style="z-index:200;"><tr><td style="text-align:center">'.__('Mark checkbox next to name(s)');
echo '</td></tr><tr><td>';
echo '<div style="z-index:110;height: 400px; width:241px; overflow: auto; border: 1px solid #000; background: #eee; color: #000; "> ';
//while ($fam_searchDb=mysql_fetch_object($fam_search_result)){
while($fam_searchDb=$fam_search_result->fetch(PDO::FETCH_OBJ)) {
	$pos = strpos($fam_searchDb->totalname,'_');
	$pref=''; $last='';
	$last = substr($fam_searchDb->totalname,0,$pos);
	$pref = substr($fam_searchDb->totalname,$pos+1); if($pref!='') { $pref = ', '.$pref; }
	echo '<input type="checkbox" name="items[]" value="'.$fam_searchDb->totalname.'">'.$last.$pref.'<br>';
}
echo '</div>';
echo '</td></tr><tr><td style="text-align:center">';
echo '<input type="Submit" name="submit" value="'.__('Choose').'">';
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<input type="button" name="cancelfam" onclick="document.getElementById(\'namemapping\').style.display=\'none\';"  value="'.__('Cancel').'">';
echo '</td></tr></table>';
echo '</form>';
echo '</div>';

// FIXED WINDOW WITH LIST TO CHOOSE PERSON TO MAP WITH DESCENDANTS
if(isset($_POST['descmap'])) {

	//adjust pulldown for mobiles/tablets
	$select_size = 'size="20"'; $select_height = '400px';
	if (isset($_SERVER["HTTP_USER_AGENT"]) OR ($_SERVER["HTTP_USER_AGENT"] != "")) { //adjust pulldown for mobiles/tablets
        	$visitor_user_agent = $_SERVER["HTTP_USER_AGENT"];
		if(strstr($visitor_user_agent,"Android")!== false OR
		   strstr($visitor_user_agent,"iOS")!== false OR
		   strstr($visitor_user_agent,"iPad")!== false OR
		   strstr($visitor_user_agent,"iPhone")!== false ) {
		   	$select_size=""; $select_height= '100px';
		}
    	}

	echo '<div id="descmapping" style="display:block; z-index:100; position:absolute; top:90px; margin-left:140px; height:'.$select_height.'; width:400px; border:1px solid #000; background:#d8d8d8; color:#000; margin-bottom:1.5em;z-index:20">';
	if($user['group_kindindex']=="j") { $orderlast = "CONCAT(pers_prefix,pers_lastname)"; }
	else { $orderlast = "pers_lastname"; }
	$desc_search = "SELECT * FROM ".$_SESSION['tree_prefix']."person WHERE pers_fams !='' ORDER BY ".$orderlast.", pers_firstname";
	//$desc_search_result = mysql_query($desc_search,$db) or die(mysql_error());
	$desc_search_result = $dbh->query($desc_search);
	echo '&nbsp;&nbsp;'.__('Pick a name or enter ID:').'<br>';
	echo '<form method="POST" action="" style="display : inline;">';
	echo '<select style="max-width:396px;background:#eee" '.$select_size.' onChange="window.location=this.value;" id="desc_map" name="desc_map">';
	echo '<option value="toptext">'.__('Pick a name from the pulldown list').'</option>';
	//prepared statement out of loop
	$chld_prep = $dbh->prepare("SELECT fam_children FROM ".safe_text($_SESSION['tree_prefix'])."family WHERE fam_gedcomnumber =? AND fam_children != ''");
	$chld_prep->bindParam(1,$chld_var);
	//while ($desc_searchDb=mysql_fetch_object($desc_search_result)){
	while($desc_searchDb=$desc_search_result->fetch(PDO::FETCH_OBJ)) {
		$countmarr = 0;
		$man_cls = New person_cls;
		$fam_arr = explode(";", $desc_searchDb->pers_fams);
		foreach ($fam_arr as $value) {
			if($countmarr==1) { break; } //this person is already listed
			//$chld_search = "SELECT fam_children FROM ".$_SESSION['tree_prefix']."family WHERE fam_gedcomnumber ='".$value."' AND fam_children != ''";
			//$chld_search_result = mysql_query($chld_search, $db);
			$chld_var = $value;
			$chld_prep->execute();
			//while ($chld_search_resultDb = mysql_fetch_object($chld_search_result)) {
			while($chld_search_resultDb = $chld_prep->fetch(PDO::FETCH_OBJ)) {
				$countmarr = 1;
				$selected='';
				//if($desc_searchDb->pers_gedcomnumber == $chosenperson) { $selected = ' SELECTED '; }
				$man_cls->construct($desc_searchDb);
				$privacy_man=$man_cls->privacy;
				$date='';
				if(!$privacy_man) {
					// if a person has privacy set (even if only for data, not for name,
					// we won't put them on the list. Most likely it concerns recent people.
					// Also, using the $man_cls->person_name functions takes too much time...
					$b_date=$desc_searchDb->pers_birth_date;
					$b_sign = __('born').' ';
					if (!$desc_searchDb->pers_birth_date AND $desc_searchDb->pers_bapt_date) {
						$b_date = $desc_searchDb->pers_bapt_date;
						$b_sign = __('baptised').' ';
					}
					$d_date=$desc_searchDb->pers_death_date;
					$d_sign = __('died').' ';
					if (!$desc_searchDb->pers_death_date AND $desc_searchDb->pers_buried_date) {
						$d_date = $desc_searchDb->pers_buried_date;
						$d_sign = __('buried').' ';
					}
					$date='';
					if($b_date AND !$d_date) {
						$date = ' ('.$b_sign.date_place($b_date,'').')';
					}
					if($b_date AND $d_date) {
						$date .= ' ('.$b_sign.date_place($b_date,'').' - '.$d_sign.date_place($d_date,'').')';
					}
					if(!$b_date AND $d_date) {
						$date = '('.$d_sign.date_place($d_date,'').')';
					}
					$name = ''; $pref = ''; $last = '- , '; $first = '-';
					if($desc_searchDb->pers_lastname) { $last = $desc_searchDb->pers_lastname.', '; }
					if($desc_searchDb->pers_firstname) { $first = $desc_searchDb->pers_firstname; }
					if($desc_searchDb->pers_prefix) { $pref = strtolower(str_replace('_','',$desc_searchDb->pers_prefix)); }

					if($user['group_kindindex']=="j") {
						if($desc_searchDb->pers_prefix) { $pref = strtolower(str_replace('_','',$desc_searchDb->pers_prefix)).' '; }
						$name = $pref.$last.$first;
					}
					else {
						if($desc_searchDb->pers_prefix) { $pref = ' '.strtolower(str_replace('_','',$desc_searchDb->pers_prefix)); }
						$name = $last.$first.$pref;
					}
					echo '<option value="'.$_SERVER['PHP_SELF'].'?persged='.$desc_searchDb->pers_gedcomnumber.'&persfams='.$desc_searchDb->pers_fams.'" '.$selected.'>'.$name.$date.' [#'.$desc_searchDb->pers_gedcomnumber.']</option>';
				}
			}
		}
	}
	echo '</select>';
	echo '</form>';

	?>
	<script type="text/javascript">    
	function findGednr (pers_id) { 
		for(var i=1;i<desc_map.length-1;i++) {
			if(desc_map.options[i].text.indexOf("[#" + pers_id + "]") != -1 || desc_map.options[i].text.indexOf("[#I" + pers_id + "]") != -1 ) { 
				window.location = desc_map.options[i].value;
			}
		}
	}
	</script>
	<?php
	echo '<br><div style="margin-top:5px;text-align:left">&nbsp;&nbsp;Find by ID (I324):<input id="id_field" type="text" style="font-size:120%;width:60px;" value=""><input type="button" value="'.__('Go!').'" onclick="findGednr(getElementById(\'id_field\').value);">';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'">'.__('Cancel').'</a></div>';
	echo '</div>';
}

// GOOGLE MAP
echo '<div id="map_canvas" style="width:1000px; height:520px"></div>'; // placeholder div for map generated below

// function to read multiple values from location search bar and zoom to map location:
?>
<script type="text/javascript">    
function findPlace () {
	infoWindow.close();
	var e = document.getElementById("loc_search");
	var locSearch = e.options[e.selectedIndex].value;
	if(locSearch != "toptext") {   // if not default text "find location on map"
		var opt_array = new Array();
		opt_array = locSearch.split(",",3);
		map.setZoom(11);
		var ltln = new google.maps.LatLng(opt_array[1],opt_array[2]);
		map.setCenter(ltln);
	}
}
</script>

<script type="text/javascript"
		src="http://maps.googleapis.com/maps/api/js?sensor=false">
</script>

<script type="text/javascript">
	var map;
	function initialize() {
		var latlng = new google.maps.LatLng(22, -350);
		var myOptions = {
			zoom: 2,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	}
</script>

<script type="text/javascript">
	initialize();
</script>

<script type="text/javascript">
	function hide() {
		document.getElementById('wait').style.display = "none";
	}
</script>

<?php

include_once(CMS_ROOTPATH."googlemaps/google_initiate.php");
?>

<script type="text/javascript">
	window.onload = hide;
</script>

<?php
include_once(CMS_ROOTPATH."footer.php");
?>