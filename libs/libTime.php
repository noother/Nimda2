<?php

/*
	Copyright (C) 2009  noother [noothy@gmail.com]

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

class libTime {

	function secondsToString($seconds) {
		$minutes = (int)($seconds/60);
		$seconds = $seconds-$minutes*60;
		$hours   = (int)($minutes/60);
		$minutes = $minutes-$hours*60;
		$days    = (int)($hours/24);
		$hours   = $hours-$days*24;
		
		if($seconds==1)  $output = "and ".$seconds." second";
		else             $output = "and ".$seconds." seconds";
		if($minutes==1)  $output = $minutes." minute ".$output;
		elseif($minutes) $output = $minutes." minutes ".$output;
		if($hours==1)    $output = $hours.  " hour, ".$output;
		elseif($hours)   $output = $hours.  " hours, ".$output;
		if($days==1)     $output = $days.   " day, ".$output;
		elseif($days)    $output = $days.   " days, ".$output;
		
		if(substr($output,0,4)=="and ") $output = substr($output,4);
	return $output;
	}
}


?>
