<?php

/*
	This file is part of Nimda - An advanced event-driven IRC Bot written in PHP with a nice plugin system
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

class Translate extends Plugin {

	function isTriggered() {
		if(!isset($this->info['text'])) {
			$this->sendOutput("Usage: ".$this->info['triggerUsed']." text");
			return;
		}
		$trigger = substr($this->info['triggerUsed'],1);
		$tmp = explode("-",$trigger);
		$sl = $tmp[0];
		$tl = $tmp[1];
		$text = utf8_decode($this->info['text']);
		
		$translation = utf8_encode($this->unhtmlentities($this->getTranslation($text,$sl,$tl)));
		$this->sendOutput("\x02Translation: \x02".$translation);
	}
	
	function getTranslation($text,$from,$to) {
		$post_data = "text=".urlencode($text)."&sl=".$from."&tl=".$to;
		$res = libHTTP::POST("translate.google.com","/translate_t?",$post_data);
		preg_match('#<div id=result_box dir="ltr">(.*?)</div>#',$res['raw'],$arr);
	return $arr[1];
	}
	
	function unhtmlentities($string)
	{
	// replace numeric entities
	$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
	$string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
	// replace literal entities
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($string, $trans_tbl);
	}
}

?>
