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

class libString {
	
	static function isUTF8($string) {
	
	return (utf8_encode(utf8_decode($string)) == $string);
	}
	
	static function end_s($word,$number) {
		if($number == 1 || $number == -1) return $word;
		else return $word."s";
	}
	
	static function capitalize($string) {
        return strtoupper($string[0]).strtolower(substr($string,1));
	}
	
	static function convertUmlaute($string) {
		$replace = array("ä" => "ae", "ö" => "oe", "ü" => "ue", "Ä" => "Ae", "Ö" => "Oe", "Ü" => "Ue");
	return strtr($string,$replace);
	}
	
	static function countSmilies($string) {
		$count = 0;
		$smilies = array(	":)",":-)",":-]",":]",":(",":-(",":<",":-<",":>",":->",":[",":-[",
							":/",":-/",":|",":-|",";)",";-)",":p",":P",":-p",":-P",";p",";-P",":D",
							":-D",";D",";-D",":ß",":-ß",";ß",";-ß","B)","B-)","8)","8]",":o",":O",
							":-o",":-O",";o",";O",";-o",";-O",":S",":s",":-S",":-8",":-B",":8",":B",
							":x",":-x",";(",";-(",":'(",":'-(",":_(",":o)",";o)",">:-)",">:)","0:-)",
							"0:)","xD","XD","D:",":3",":V",":-t",":t",":*)",":-)*",":)*",":^o",
							":&",":-&",":{",":}",":-{",":-}",">:o~","^^","^_^","^-^",">_<","<_<",">_>",
							"=)", "=-)", "=]", "=-]", "=-}", "=}");
		foreach($smilies as $smiley) {
			$count+=substr_count(" ".$string." "," ".$smiley." ");
		}
	return $count;
	}
	
	static function getLink($string) {
		preg_match('#(^|\s)((http://)?([a-z0-9]+\.)([a-z0-9\-]+\.)?[a-z]{2,4}(/[\w/\.\-\?=_\&]+[^[:punct:]]/?)?)([\s[:punct:]]|$)#i',$string,$arr);
		if(isset($arr[2])) return $arr[2];
		
	return false;
	}
	
	static function softhyphe($string) {
		$pos = rand(1,strlen($string)-1);
		$newstring = substr($string,0,$pos)."\xC2\xAD".substr($string,$pos);
	return $newstring;
	}
	
}

?>
