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

class libCrypt {


	static function rot($string,$key) {
		$newstring = "";
		for($x=0;$x<strlen($string);$x++) {
			$chr = $string{$x};
			if($chr>="a" && $chr<="z") {
				$chr = ord($chr);
				$chr+=$key;
				if($chr>122) $chr-=26;
				$chr = chr($chr);
			} elseif($chr>="A" && $chr<="Z") {
				$chr = ord($chr);
				$chr+=$key;
				if($chr>90) $chr-=26;
				$chr = chr($chr);
			}
			$newstring.=$chr;
		}
	return $newstring;
	}
	
	static function vigenere_dec($string,$key) {
		$output = "";
		$key = strtolower($key);
		$key_pos = 0;
		for($x=0;$x<strlen($string);$x++) {
			$keychar = substr($key,$key_pos,1);
			$stringchar = substr($string,$x,1);
			if( (ord($stringchar) >= 65 && ord($stringchar) <= 90 && $check=1) ||
				(ord($stringchar) >= 97 && ord($stringchar) <= 122 && $check=2)) {
				if($check == 1) $alpha = ord($stringchar)-64;
				if($check == 2) $alpha = ord($stringchar)-96;
				$key_pos++;
				if($key_pos>=strlen($key)) $key_pos-=strlen($key);
				$alpha_key = ord($keychar)-97;
				$new_value = $alpha-=$alpha_key;
				if($new_value<1) $new_value+=26;
				if($check==1) $output.=chr($new_value+64);
				if($check==2) $output.=chr($new_value+96);
			} else $output.=$stringchar;
		}
	return $output;
	}

}

?>
