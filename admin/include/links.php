<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Links').'</h1>';

echo __('"Own code" is the code that has to be entered in your genealogy program under "own code or REFN"
<p>Do the following:<br>
1) In your genealogy program, put a code. For example, with the patriarch enter a code "patriarch".<br>
2) Enter the same code in the table below (multiple codes are possible)<br>
3) After processing the gedcom file, an extra link will appear in the main menu, i.e. to the patriarch!<br>');

// *** Change Link ***
if (isset($_POST['change_link'])){
	//$datasql = mysql_query("SELECT * FROM humo_settings WHERE setting_variable='link'",$db);
	$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
	//while ($dataDb=mysql_fetch_object($datasql)){
	while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
		$setting_value=addslashes($_POST[$dataDb->setting_id.'own_code'])."|".addslashes($_POST[$dataDb->setting_id.'link_text']);
		$sql="UPDATE humo_settings SET setting_value='".$setting_value."' WHERE setting_id=".$_POST[$dataDb->setting_id.'id'];
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
}

// *** Remove link  ***
//$datasql = mysql_query("SELECT * FROM humo_settings WHERE setting_variable='link'",$db);
$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
//while ($dataDb=mysql_fetch_object($datasql)){
while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
	if (isset($_POST[$dataDb->setting_id.'remove_link'])){
		$sql="DELETE FROM humo_settings WHERE setting_id='".$dataDb->setting_id."'";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
}

// *** Add link ***
if (isset($_POST['add_link'])){
	$setting_value=addslashes($_POST['own_code'])."|".addslashes($_POST['link_text']);
	$sql="INSERT INTO humo_settings SET setting_variable='link', setting_value='".$setting_value."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

// *** Show all links ***
if(CMS_SPECIFIC == "Joomla") {
	print "<form method='post' action='index.php?option=com_humo-gen&amp;task=admin&amp;page=links'>";
}
else {
	print "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
}
echo '<input type="hidden" name="page" value="'.$page.'">';

echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large">';
	print '<th class="table_header">'.__('EXTRA LINKS IN MAIN MENU').'</th></tr>';
echo '</table><br>';

echo '<table class="humo standard" border="1">';
	print '<tr class="table_header"><th>Nr.</th><th>'.__('Own code').'</th><th>'.__('Description').'</th><th>'.__('Change / Add').'</th><th>'.__('Remove').'</th></tr>';
	//$datasql = mysql_query("SELECT * FROM humo_settings WHERE setting_variable='link'",$db);
	$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
	if ($datasql){
		$teller=1;
		//while ($dataDb=mysql_fetch_object($datasql)){
		while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
			$lijst=explode("|",$dataDb->setting_value);
			echo '<tr>';
			echo '<td><input type="hidden" name="'.$dataDb->setting_id.'id" value="'.$dataDb->setting_id.'">';
			echo 'Link '.$teller."</td>";
			echo '<td><input type="text" name="'.$dataDb->setting_id.'own_code" value="'.$lijst[0].'" size="20"></td>';
			echo '<td><input type="text" name="'.$dataDb->setting_id.'link_text" value="'.$lijst[1].'" size="30"></td>';
			echo '<td><input type="Submit" name="change_link" value="'.__('Change').'"></td>';
			echo '<td bgcolor="red"><input type="Submit" name="'.$dataDb->setting_id.'remove_link" value="'.__('Remove').'"></td>';
			echo "</tr>";
			$teller++;
		}

		// *** Add new link ***
		echo "<tr>";
			echo "<td><br></td>";
			echo '<td><input type="text" name="own_code" value="Code" size="20"></td>';
			echo '<td><input type="text" name="link_text" value="'.__('Owner of tree').'" size="30"></td>';
			echo '<td><input type="Submit" name="add_link" value="'.__('Add').'"></td>';
			echo '<td bgcolor="red"><br></td>';
		echo "</tr>";
	}
	else{
		echo '<tr><td colspan="4">'.__('Database is not yet available.').'</td></tr>';
	}
	print "</table>";
print "</form>";
?>