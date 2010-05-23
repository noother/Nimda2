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

class libArray {
	static function sortByLengthASC($array) {
		$tempFunction = create_function('$a,$b','return strlen($a)-strlen($b);');
		usort($array,$tempFunction);
	return $array;
	}
	
	static function sortByLengthDESC($array) {
		$tempFunction = create_function('$a,$b','return strlen($b)-strlen($a);');
		usort($array,$tempFunction);
	return $array;
	}
	
	static function stripslashes($array) {
    	$value = is_array($array) ? array_map('stripslashes', $array) : stripslashes($array);
    return $value;
	}
}

?>
