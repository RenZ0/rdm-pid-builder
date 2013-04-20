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
 * gendata.php
 * pids data generator
 * Copyright (C) 2012 Laurent Pierru <renzo@imaginux.com>
 */

require("config.php");
//include("menu.php");

$color1='red';
$color2='green';
$color3='blue';
$color4='#b46c00'; //orange
$color5='purple';
$color6='#418579';
$color7='#626262';

//MANUFACT
$sql="SELECT * FROM pids_manufact WHERE id_manufact=$id";
$sql=mysql_query($sql);
while ($data=mysql_fetch_array($sql)){

	echo"{'id': <font color=\"$color1\">".$data[rdmid_manufact]."</font>,";
	echo"<br>&nbsp;";
	echo"'name': '<font color=\"$color1\">".$data[name_manufact]."</font>',";
	echo"<br>&nbsp;";
	echo"'pids': [";
	echo"<br>&nbsp;&nbsp;&nbsp;";

}

//LIST PIDS
$sqlb="SELECT * FROM pids_list WHERE id_manufact=$id ORDER BY id_pid";
$sqlb=mysql_query($sqlb);
$testb=mysql_num_rows($sqlb);
$i=0;
while ($datab=mysql_fetch_array($sqlb)){
//print_r($datab);

	//new pid
	echo"{";

	//we double check even if it should be ok
	if ($datab[get_pid]==1 AND $datab[set_pid]==1){
		$catarr= array('get_request', 'get_response', 'set_request', 'set_response');
	}

	if ($datab[get_pid]==0 AND $datab[set_pid]==1){
		$catarr= array('set_request', 'set_response');
	}

	if ($datab[get_pid]==1 AND $datab[set_pid]==0){
		$catarr= array('get_request', 'get_response');
	}

	foreach($catarr as $catvalue){

		//get_*, set_*
		echo"'<font color=\"$color2\">$catvalue</font>': {'items': [";

        //same or not
        if ($datab[samegs_pid]==1 AND $catvalue=='set_request'){
            $catvaluesql='get_response';
        }else{
            $catvaluesql=$catvalue;
        }
		//list items
		$sqlc="SELECT * FROM pids_items WHERE id_pid=$datab[id_pid] AND cat_item='$catvaluesql'";
		//echo"$sqlc";
		$sqlc=mysql_query($sqlc);
		$testc=mysql_num_rows($sqlc);
		$j=0;
		while ($datac=mysql_fetch_array($sqlc)){

			echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";
			echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo"{";
			echo"'name': '<font color=\"$color4\">".$datac[name_item]."</font>', ";
			echo"'type': '";

				$sqli="SELECT * FROM pids_type WHERE id_type=$datac[id_type]";
				$sqli=mysql_query($sqli);
				while ($datai=mysql_fetch_array($sqli)){
					//
					echo"<font color=\"$color5\">".$datai[name_type]."</font>";
					//
				}

			//type end
			echo"'";

			if ($datac[multiplier_item]!=0){
				echo", 'multiplier': '<font color=\"$color5\">".$datac[multiplier_item]."</font>'";
			}

			if ($datac[min_item]!=0){
				echo", 'min_size': '<font color=\"$color5\">".$datac[min_item]."</font>'";
			}

			if ($datac[max_item]!=0){
				echo", 'max_size': '<font color=\"$color5\">".$datac[max_item]."</font>'";
			}



			$rgearr= array('labels', 'range');
			foreach($rgearr as $rgevalue){

				//list ranges
				$sqld="SELECT * FROM pids_itemsrange WHERE id_item=$datac[id_item] AND cat_range='$rgevalue' ORDER BY value_range";
				$sqld=mysql_query($sqld);
				$testd=mysql_num_rows($sqld);
				$k=0;
				if($testd!=0){

					//labels, range...
					echo",";
					echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";
					echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					echo"&nbsp;'$rgevalue': [";

					while ($datad=mysql_fetch_array($sqld)){

						echo"<font color=\"$color6\">";

						if($datad[cat_range]=='labels'){
							echo"(".$datad[value_range].", '".$datad[value2_range]."')";
						}
						if($datad[cat_range]=='range'){
							echo"(".$datad[value_range].", ".$datad[value2_range].")";
						}

						echo"</font>";

						//close range
						$k++;
						if($k==$testd){
							echo"]";
						}else{
							echo",";
							echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";
							echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						}

					} //while

				} //if test

			} //foreach

			//close item
			$j++;
			if($j==$testc){
				echo"}";
			}else{
				echo"}, ";
			}

		}
		////////////

		//close items
		echo"]},";
		echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";

	} //foreach

	if ($datab[get_pid]==1){
		echo"'<font color=\"$color7\">get_sub_device_range</font>': ".$datab[subget_pid].",";
		echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	if ($datab[set_pid]==1){
		echo"'<font color=\"$color7\">set_sub_device_range</font>': ".$datab[subset_pid].",";
		echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//NAME
	echo"'name': '<font color=\"$color3\">".$datab[name_pid]."</font>',";
	echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";

	//link
	echo"'link': '".$datab[link_pid]."',";
	echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";

	//notes
	echo"'notes': '".$datab[notes_pid]."',";
	echo"<br>&nbsp;&nbsp;&nbsp;&nbsp;";

	//value
	echo"'value': <font color=\"$color3\">".$datab[value_pid]."</font>";

	//close pid
	$i++;
	if($i==$testb){
		echo"}]<br>";
	}else{
		echo"},<br>&nbsp;&nbsp;&nbsp;";
	}

}

//close manufact
echo"}";

?>

</body>

