<?php

class libInternet {

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

	static function googleResults($string) {
		$host   = "www.google.com";
		$lang	= "de";
		$get    = "/search?q=".urlencode($string)."&hl=".$lang."&safe=off";
		$result = libHTTP::GET($host,$get);
		
		$result = implode($result['content'],"\n");
		
		preg_match('#Ungef.hr (.*?) Ergebnisse#',$result,$arr);
		
		if(empty($arr)) return 0;
	return str_replace('.','',$arr[1]);
	}
	
	/**
	 * translate with the google translate API
	 * @param string $sText text to translate
	 * @param string $sFrom original language (optional)
	 * @param string $sTo language to translate to (optional)
	 * @return string translated text
	 */
	static function googleTranslate($sText, $sFrom='', $sTo = 'de') {
		$sHost = 'ajax.googleapis.com';
		$sGet  = '/ajax/services/language/translate?v=1.0&q='.rawurlencode($sText).'&langpair='.rawurlencode($sFrom.'|'.$sTo);
		
		$result = libHTTP::GET($sHost, $sGet);
		preg_match('/{"translatedText":"(.*?)"}/i', $result['content'][0], $aMatches);

		if (empty($aMatches)) return 'error';
		return $aMatches[1];
	}
	
	static function getTvProgram() {
		$host = 'www.tvtoday.de';
		$result = array();
		for($page=0;$page<3;$page++) {
			$url = '/programm/?format=genre&offset='.$page.'&date='.date('d.m.Y').'&slotIndex=now&channel=all&tips=&order=time';
	
			$res = libHTTP::GET($host,$url);
	
			preg_match_all('#class="tv-sendung-uhrzeit">(.*?)<br/>-<br/>(.*?)<.*?class="tv-sendung-titel">(.*?)</a>.*?<a.*?<b>(.*?)</b>#s',$res['raw'],$arr);
	
		
	
			for($x=0;$x<sizeof($arr[0]);$x++) {
				if(isset($result[$arr[4][$x]])) continue;
				$result[$arr[4][$x]] = array('title' => $arr[3][$x], 'start_time' => $arr[1][$x], 'end_time' => $arr[2][$x]);
			}
		}
		
	return $result;
	}
	
}

?>
