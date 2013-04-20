<?php

/*
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Library General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 * addpids.php
 * pids editor
 * Copyright (C) 2012 Laurent Pierru <renzo@imaginux.com>
 */

require("config.php");
include("menu.php");

$id = $_GET['id'];

//SQL INSERT manufact
if ( isset($_POST['addmanu']) )
{
	if ($rdmid != '' AND $manuname != ''){
		$sqla="INSERT INTO pids_manufact VALUES('','$rdmid','$manuname')";
		$sqla=mysql_query($sqla) or die(mysql_error());
		echo'<font color="green" size="4">Added</font>';
	}else{
		echo'<font color="red" size="4">Error</font>';
	}
}

//MANUFACTURERS
$sql="SELECT * FROM pids_manufact";

if ( isset ($_GET['id']) ){
	$sql.=" WHERE id_manufact=$id";
}

$sql=mysql_query($sql);
while ($data=mysql_fetch_array($sql)){
	echo'<br># <b>'.$data[name_manufact].' ('.$data[rdmid_manufact].')</b> :
				<a href="addpids.php?id='.$data[id_manufact].'">EditPids</a>
				- <a href="gendata.php?id='.$data[id_manufact].'" target="blank">GenData</a>';
	echo'<br>';
}

if ( !isset ($_GET['id']) ){

	echo'<br>Choose a manufacturer<br>';

	echo'<br>Or add one :<br>';

		//MANUFACT FORM
		echo'<form action="addpids.php" method="post">';

		echo' <b>Plasa iD</b> <input type="text" name="rdmid" value="" size="5">';
		echo' <b>Name</b> <input type="text" name="manuname" value="" size="25">';

		echo'<input type="submit" name="addmanu" value="ADD">';

		echo'</form>';

	die('<br>');
}

// SQL START

//SQL INSERT + UPDATE
if ( isset($_POST['addpid']) OR isset($_POST['modpid']) )
{
	//if 1, we will set values again in the form
	$newpidtry=1;

	$pidcheck=0;
	if ($pidname == '' OR $pidvalue == ''){
		$pidcheck=1;
		echo'<font color="red" size="4">Pid Name and Value NEEDED ! </font>';
	}

	if ($pidget != '1' AND $pidset != '1'){
		$pidcheck=1;
		echo'<font color="red" size="4">Pid Get and/or Set NEEDED ! </font>';
	}

	if ( $pidsame==1 AND ($pidget != '1' OR $pidset != '1') ){
		$pidcheck=1;
		echo'<font color="red" size="4">Same : Get and Set NEEDED ! </font>';
	}

	if ($pidcheck==0){

		$pidname=strtoupper($pidname);
		$pidname = str_replace(' ', '_', $pidname);

		$doublecheck=0;
		$sqlb="SELECT * FROM pids_list WHERE id_manufact=$id";
		//
		if ( isset($_POST['modpid']) ){
			$sqlb.=" AND id_pid != $pidid"; //exclude the one we modify
		}
		//
		$sqlb.=" ORDER BY id_pid";
		$sqlb=mysql_query($sqlb);
		while ($datab=mysql_fetch_array($sqlb)){

			if($datab[value_pid]==$pidvalue){
				$doublecheck=1;
				echo'<font color="red" size="4">Pid value already exists ! </font>';
			}

			if($datab[name_pid]==$pidname){
				$doublecheck=1;
				echo'<font color="red" size="4">Pid name already exists ! </font>';
			}

		}

		if($doublecheck==0){

			//SQL ADD
			if ( isset($_POST['addpid']) )
			{
				$sqla="INSERT INTO pids_list VALUES('','$id','$pidvalue','$pidname','$pidnotes','$pidlink','$pidsubget','$pidsubset','$pidget','$pidset','$pidsame')";
				$sqla=mysql_query($sqla) or die(mysql_error());
				echo'<font color="green" size="4">Added: '.$pidname.' </font>';
				//
				$newpidtry=0; //clean the form
			}

			//SQL MOD
			if ( isset($_POST['modpid']) )
			{
				$sqla="UPDATE pids_list SET value_pid='$pidvalue', name_pid='$pidname', notes_pid='$pidnotes', link_pid='$pidlink', subget_pid='$pidsubget', subset_pid='$pidsubset', get_pid='$pidget', set_pid='$pidset', samegs_pid='$pidsame' WHERE id_pid='$pidid'";
				$sqla=mysql_query($sqla) or die(mysql_error());
				echo'<font color="green" size="4">Modified: '.$pidname.' </font>';
			}

		} //doublecheck

	} //pidcheck

} //id add-mod

//SQL DEL
if ( isset($_POST['delpid']) )
{
	$sqla="DELETE FROM pids_itemsrange WHERE id_item IN (SELECT id_item FROM pids_items WHERE id_pid='$pidid')";
	$sqla=mysql_query($sqla) or die(mysql_error());

	$sqla="DELETE FROM pids_items WHERE id_pid='$pidid'";
	$sqla=mysql_query($sqla) or die(mysql_error());

	$sqla="DELETE FROM pids_list WHERE id_pid='$pidid'";
	$sqla=mysql_query($sqla) or die(mysql_error());

	echo'<font color="orange" size="4">Pid Deleted</font>';
}

//SQL INSERT item
if ( isset($_POST['additem']) )
{
	if ($itemname != ''){
		$itemname=strtolower($itemname);
		$itemname = str_replace(' ', '_', $itemname);

		if ( ($itemmax!='' OR $itemmin!='') AND ($pidtype!=5 AND $pidtype!=6) ){
			$itemmax=0;
			$itemmin=0;
			echo'<font color="red" size="3">Min/Max removed (for string and group only) !</font><br>';
		}

		if ( $itemmultiplier!='' AND ($pidtype!=2 AND $pidtype!=3 AND $pidtype!=4 AND $pidtype!=7 AND $pidtype!=8 AND $pidtype!=9) ){
			$itemmultiplier=0;
			echo'<font color="red" size="3">Multiplier removed (for int only) !</font><br>';
		}

		$sqla="INSERT INTO pids_items VALUES('','$pidid','$itemcat','$itemname','$pidtype','$itemmax','$itemmin','$itemmultiplier')";
		$sqla=mysql_query($sqla) or die(mysql_error());
		echo'<font color="green" size="4">Added Item: '.$itemname.' </font>';
	}else{
		echo'<font color="red" size="4">Item Name NEEDED !</font>';
	}
}

//SQL DEL item
if ( isset($_POST['delitem']) )
{
	$sqla="DELETE FROM pids_itemsrange WHERE id_item='$itemid'";
	$sqla=mysql_query($sqla) or die(mysql_error());

	$sqla="DELETE FROM pids_items WHERE id_item='$itemid'";
	$sqla=mysql_query($sqla) or die(mysql_error());

	echo'<font color="orange" size="4">Item Deleted</font>';
}

//SQL INSERT range
if ( isset($_POST['addrange']) )
{
	if ($rangevalue != '' AND $rangevalue2 != ''){
		//$itemname=strtolower($itemname);
		$sqla="INSERT INTO pids_itemsrange VALUES('','$itemid','$rangecat','$rangevalue','$rangevalue2')";
		$sqla=mysql_query($sqla) or die(mysql_error());
		echo'<font color="green" size="4">Added Item Range: '.$rangecat.' </font>';
	}else{
		echo'<font color="red" size="4">Range Values NEEDED !</font>';
	}
}

//SQL DEL item range
if ( isset($_GET['delrge']) )
{
	$sqla="DELETE FROM pids_itemsrange WHERE id_range='$delrge'";
	$sqla=mysql_query($sqla) or die(mysql_error());

	echo'<font color="orange" size="4">Range Deleted</font>';
}

// SQL END

if ( !isset($_GET[edit]) ){
	//ADD PID FORM

	if ( isset($_POST['addpid']) AND $newpidtry==1 ){
		$pidname_try = $pidname;
		$pidvalue_try= $pidvalue;
		$pidnotes_try= $pidnotes;
		$pidlink_try = $pidlink;
		$pidsubget_try=$pidsubget;
		$pidsubset_try=$pidsubset;
		$pidget_try =  $pidget;
		$pidset_try =  $pidset;
		$pidsame_try=  $pidsame;
	}

	echo'<br>
	<div class="sideborder"><table><tr>
	<td>

	<form action="addpids.php?id='.$id.'" method="post">

		<b>Name </b>(ex: DEVICE_MODE) <input type="text" name="pidname" value="'.$pidname_try.'" size="20"><br>
		<b>Value </b>(ex: 32768 or 0x8001) <input type="text" name="pidvalue" value="'.$pidvalue_try.'" size="15"><br>

		<b>Notes </b>(ex: Controls the operating mode of the device)<br>
			<input type="text" name="pidnotes" value="'.$pidnotes_try.'" size="40"><br>

		<b>Link </b>(ex: http://www...)<br>
			<input type="text" name="pidlink" value="'.$pidlink_try.'" size="40"><br>

	</td>
	<td>

		Sub-device range :';

		echo'<br><b>Get</b> <select name="pidsubget">';
			echo'<option value="0"'; if($pidsubget_try==0){echo' selected';} echo'>0-Root device only (0x0)';
			echo'<option value="1"'; if($pidsubget_try==1){echo' selected';} echo'>1-Root or all sub-devices (0x0 - 0x200, 0xffff)';
			echo'<option value="2"'; if($pidsubget_try==2){echo' selected';} echo'>2-Root or sub devices (0x0 - 0x200)';
			echo'<option value="3"'; if($pidsubget_try==3){echo' selected';} echo'>3-Only sub-devices (0x1 - 0x200)';
		echo'</select>';

		echo'<br><b>Set</b> <select name="pidsubset">';
			echo'<option value="0"'; if($pidsubset_try==0){echo' selected';} echo'>0-Root device only (0x0)';
			echo'<option value="1"'; if($pidsubset_try==1){echo' selected';} echo'>1-Root or all sub-devices (0x0 - 0x200, 0xffff)';
			echo'<option value="2"'; if($pidsubset_try==2){echo' selected';} echo'>2-Root or sub devices (0x0 - 0x200)';
			echo'<option value="3"'; if($pidsubset_try==3){echo' selected';} echo'>3-Only sub-devices (0x1 - 0x200)';
		echo'</select>';

		echo'<br><br>
		<b>Get</b> <input type="checkbox" name="pidget" value="1" size="2"'; if($pidget_try==1){echo' checked';} echo'> -
		<b>Set</b> <input type="checkbox" name="pidset" value="1" size="2"'; if($pidset_try==1){echo' checked';} echo'><br>
		<b>Same</b> (get_response = set_request) <input type="checkbox" value="1" name="pidsame" size="2"'; if($pidsame_try==1){echo' checked';} echo'><br>

		<br>
		<input type="submit" name="addpid" value="ADD">
	</form>

	</td>';

	echo'</tr></table></div>';

	echo'<a href="addpids.php?id='.$id.'&see=all">See all details (or use Edit for each)</a><br><br>';
}else{
	echo'<br>';
}

echo'<div id="sequence"><table>';

//LIST PIDS, form to add ITEMS
$sqlb="SELECT * FROM pids_list WHERE id_manufact=$id";
if ( isset($_GET[edit]) ){
	$sqlb.=" AND id_pid=$_GET[edit]";
}
$sqlb.=" ORDER BY id_pid";
$sqlb=mysql_query($sqlb);
while ($datab=mysql_fetch_array($sqlb)){

		//name for # on full view
		echo'<tr>';
			echo'<td><a name="'.$datab[id_pid].'"></a></td>';
		echo'</tr>';

	if ( !isset($_GET[edit]) AND $_POST[pidid]==$datab[id_pid] ){
		echo'<tr bgcolor="green">'; //
	}else{
		echo'<tr bgcolor="#0066cc">'; //blue
	}

		echo'<td></td>';
		echo'<td><b>Name</b></td>';
		echo'<td><b>Value</b></td>';
		//echo'<td><b>Notes</b></td>';
		//echo'<td><b>Link</b></td>';
		echo'<td><b>Get-SubD-Range</b></td>';
		echo'<td><b>Set-SubD-Range</b></td>';
		echo'<td><b>Get</b></td>';
		echo'<td><b>Set</b></td>';
		echo'<td><b>Same</b></td>';
	echo'</tr>';

	//UPDATE FORM
	echo'<form action="addpids.php?id='.$id.'';

	if ( isset($_GET[edit]) ){
		echo'&edit=';
	}else{
		echo'#';
	}

	echo''.$datab[id_pid].'" method="post">';

	echo'<tr>';
		echo'<td width="80"><a href="addpids.php?id='.$id.'&edit='.$datab[id_pid].'"><b>-> Edit</b></a></td>';

		echo'<td><input name="pidname" value="'.$datab[name_pid].'" size="35"></td>';
		echo'<td><input name="pidvalue" value="'.$datab[value_pid].'" size="8"></td>';
		//echo'<td><input name="pidnotes" value="'.$datab[notes_pid].'" size="20"></td>';
		//echo'<td><input name="pidlink" value="'.$datab[link_pid].'" size="20"></td>';
		//echo'<td><input name="pidsubget" value="'.$datab[subget_pid].'" size="2"></td>';
		//echo'<td><input name="pidsubset" value="'.$datab[subset_pid].'" size="2"></td>';

		echo'<td><select name="pidsubget">';
			echo'<option value="0"'; if($datab[subget_pid]==0){echo' selected';} echo'>0-Root Only';
			echo'<option value="1"'; if($datab[subget_pid]==1){echo' selected';} echo'>1-Root-AllSub';
			echo'<option value="2"'; if($datab[subget_pid]==2){echo' selected';} echo'>2-Root-Sub';
			echo'<option value="3"'; if($datab[subget_pid]==3){echo' selected';} echo'>3-Sub Only';
		echo'</select></td>';

		echo'<td><select name="pidsubset">';
			echo'<option value="0"'; if($datab[subset_pid]==0){echo' selected';} echo'>0-Root Only';
			echo'<option value="1"'; if($datab[subset_pid]==1){echo' selected';} echo'>1-Root-AllSub';
			echo'<option value="2"'; if($datab[subset_pid]==2){echo' selected';} echo'>2-Root-Sub';
			echo'<option value="3"'; if($datab[subset_pid]==3){echo' selected';} echo'>3-Sub Only';
		echo'</select></td>';

		echo'<td><input type="checkbox" name="pidget" value="1"';
			if ($datab[get_pid]==1){echo' checked';}
		echo'></td>';

		echo'<td><input type="checkbox" name="pidset" value="1"';
			if ($datab[set_pid]==1){echo' checked';}
		echo'></td>';

		echo'<td>#<input type="checkbox" name="pidsame" value="1"';
			if ($datab[samegs_pid]==1){echo' checked';}
		echo'></td>';

		echo'<input name="pidid" value="'.$datab[id_pid].'" type="hidden">';

		echo'<td><input type="submit" name="modpid" value="Save"></td>';
		echo'<td><input type="submit" name="delpid" value="Del.Pid"></td>';

	echo'</tr>';
	echo'<tr bgcolor="#D3E1FF">';

		echo'<td></td>';

		//notes
		echo'<td colspan="2"><i><font color="#2A2A2A">';
		if ($datab[notes_pid]!=''){
			echo'<a href="#'.$datab[id_pid].'" onmousemove="over(\''.$datab[notes_pid].'\', event)" onmouseout="overstop()">Notes</a>';
		}else{
			echo'Notes';
		}
		echo'</font></i>';
		echo' <input name="pidnotes" value="'.$datab[notes_pid].'" size="40" style="width: 100%"></td>';

		//link
		echo'<td colspan="5"><i><font color="#2A2A2A">';
		if ($datab[link_pid]!=''){
			echo'<a href="'.$datab[link_pid].'" onmousemove="over(\''.$datab[link_pid].'\', event)" onmouseout="overstop()" target="blank">Link</a>';
		}else{
			echo'Link';
		}
		echo'</font></i>';
		echo' <input name="pidlink" value="'.$datab[link_pid].'" size="40" style="width: 100%"></td>';

	echo'</tr>';

	echo'</form>';

	//EDIT ONLY ONE
	if ($datab[id_pid]==$_GET[edit] OR $_GET[see]=='all'){

		//auto-populate
		if ($datab[get_pid]==1 AND $datab[set_pid]==1){
			$catarr= array('get_request', 'get_response', 'set_request', 'set_response');
		}

		if ($datab[get_pid]==0 AND $datab[set_pid]==1){
			$catarr= array('set_request', 'set_response');
		}

		if ($datab[get_pid]==1 AND $datab[set_pid]==0){
			$catarr= array('get_request', 'get_response');
		}

	echo'<tr bgcolor="#d0b8ff">'; //purple
		echo'<td colspan="10">';

			echo'<table><tr><td>Add Item to</td>';

			echo'<td><form action="addpids.php?id='.$id.'&edit='.$datab[id_pid].'" method="post">';

			echo'<select name="itemcat">';

				foreach($catarr as $catvalue){
					echo'<option value="'.$catvalue.'">'.$catvalue.'';
				}

			echo'</select></td>';

			if ($datab[samegs_pid]==1){
				echo'<td><i>(Same, get_response = set_request)</i></td>';
			}

			echo'</tr></table>';

			echo'<b>Name </b>(ex: number, type, unit...) <input type="text" name="itemname" value="" size="20">';

			echo' <b>Type</b> <select name="pidtype">';
			$sqli="SELECT * FROM pids_type";
			$sqli=mysql_query($sqli);
			while ($datai=mysql_fetch_array($sqli)){
				//
				echo'<option value="'.$datai[id_type].'">'.$datai[name_type].'';
				//
			}
			echo'</select>';

			echo'<br><br>For int : ';
			echo' <b>Multiplier</b> <input type="text" name="itemmultiplier" value="" size="2">';
			echo' For string and group : ';
			echo' <b>MaxSize</b> <input type="text" name="itemmax" value="" size="2">';
			echo' <b>MinSize</b> <input type="text" name="itemmin" value="" size="2">';

			echo'<input type="hidden" name="pidid" value="'.$datab[id_pid].'">';
			echo"&nbsp;&nbsp;&nbsp;&nbsp;";
			echo' <input type="submit" name="additem" value="ADD">';

			echo'</form>';

	echo'</tr>';

		//collapse
		foreach($catarr as $catvalue){

	echo'<tr bgcolor="#cceecc">'; //green
		echo'<td colspan="9">';

					echo'<b>'.$catvalue.'';

					if ($datab[samegs_pid]==1 AND $catvalue=='get_response'){
						echo' / set_request';
					}

					if ($datab[samegs_pid]==1 AND $catvalue=='set_request'){
						echo' (see get_response)';
					}

		echo'</b></td>';
	echo'</tr>';

				//ITEMS LIST, form to add range
				$sqlc="SELECT * FROM pids_items WHERE id_pid=$datab[id_pid] AND cat_item='$catvalue'";
				$sqlc=mysql_query($sqlc);
				//$testc=mysql_num_rows($sqlc);
				while ($datac=mysql_fetch_array($sqlc)){


	echo'<tr bgcolor="#eeeffa">'; //grey
		echo'<td>';

			//Del button
			echo'<form action="addpids.php?id='.$id.'&edit='.$datab[id_pid].'" method="post">';
			echo'<input type="hidden" name="itemid" value="'.$datac[id_item].'">';
			echo'<input type="submit" name="delitem" value="Del.Item">';
			echo'</form>';

		echo'</td><td colspan="8">';

					echo' '.$datac[name_item].' - ';

					$sqli="SELECT * FROM pids_type WHERE id_type=$datac[id_type]";
					$sqli=mysql_query($sqli);
					while ($datai=mysql_fetch_array($sqli)){
						//
						echo''.$datai[name_type].'';
						//
					}

					if ($datac[max_item]!=0){
						echo' - maxsize ('.$datac[max_item].')';
					}

					if ($datac[min_item]!=0){
						echo' - minsize ('.$datac[min_item].')';
					}

					if ($datac[multiplier_item]!=0){
						echo' - multiplier ('.$datac[multiplier_item].')';
					}

					//RANGE FORM
					echo'<br><br><form action="addpids.php?id='.$id.'&edit='.$datab[id_pid].'" method="post">';

					echo'<b></b> <select name="rangecat">';
						echo'<option value="labels">labels';
						echo'<option value="range">range';
					echo'</select>';

					echo' <b>Values </b>(ex: 1, CMY slow) <input type="text" name="rangevalue" value="" size="5">';
					echo' <input type="text" name="rangevalue2" value="" size="20">';

					echo'<input type="hidden" name="itemid" value="'.$datac[id_item].'">';
					echo'<input type="submit" name="addrange" value="ADD">';

					echo'</form>';

						//list ranges
						$sqld="SELECT * FROM pids_itemsrange WHERE id_item=$datac[id_item] ORDER BY cat_range, value_range";
						$sqld=mysql_query($sqld);
						while ($datad=mysql_fetch_array($sqld)){

						echo'<a href="addpids.php?id='.$id.'&edit='.$datab[id_pid].'&delrge='.$datad[id_range].'"';
						echo" onclick=\"javascript:if(!confirm('Delete this range?')) return false;\">";
						echo'<font size="1" color="#ADADAD"> Delete </font></a>';

							echo' # '.$datad[cat_range].'';

							echo' : '.$datad[value_range].', ';

							if ($datad[cat_range]=='labels'){
								echo"'";
								echo''.$datad[value2_range].'';
								echo"'";
							}else{
								echo''.$datad[value2_range].'';
							}

							echo'<br>';

						}

					echo'<hr>';

				} //fetch

			} //foreach

		echo'</td>';
	echo'</tr>';

	} //EDIT ONLY ONE
}

echo'</table></div>';

echo'<div style="{height:600px;}"';

//print_r($_POST);

?>

</body>

