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

class Wiki extends Plugin {

	function isTriggered() {
		if(!isset($this->info['text'])) {
			$this->sendOutput("Usage: ".$this->info['triggerUsed']." term");
			return;
		}
		
		$term = $this->info['text'];
		
		switch($this->info['triggerUsed']) {
			case "!wiki":
			case "!wikipedia":
				$output = $this->getWikiText($term,$this->CONFIG['server'],"/wiki/","Diese Seite existiert nicht");
				if(!$output) {
					$output = $this->getWikiText($term,$this->CONFIG['server_alt'],"/wiki/","Wikipedia does not have an article with this exact name");
				}
				break;
			case "!wiki-en":
				$output = $this->getWikiText($term,$this->CONFIG['server_alt'],"/wiki/","Wikipedia does not have an article with this exact name");
				break;
			case "!stupi":
				$output = $this->getWikiText($term,"www.stupidedia.org","/stupi/","Der Artikel kann nicht angezeigt werden");
				break;
		}
		
		if(!$output) {
			$this->sendOutput($this->CONFIG['notfound_text']);
			return;
		}
		
		
		$link = $output['link'];
		$text = substr($output['text'],0,$this->CONFIG['max_length']-(strlen($link)+6));
		$text.= "... (".$link.")";
		
		$this->sendOutput($text);
		
	return;
	}
	
	function getWikiText($term,$server,$path,$notfound) {
		$term = str_replace(" ","_",$term);
		$term[0] = strtoupper($term[0]);
		$result = libHTTP::GET($server,$path.str_replace("%23","#",urlencode($term)));
		$header = $result['header'];
		if(isset($header['Location'])) {
			preg_match("#".$path."(.*)#",$header['Location'],$arr);
			return $this->getWikiText(urldecode($arr[1]),$server,$path,$notfound);
		}
		
		$content = implode(" ",$result['content']);
		
		if(stristr($content,$notfound)) {
			return false;
		}
		
		$pos = strpos($content,'<div id="contentSub">');
		$content = substr($content,$pos);
		$content = preg_replace("#<tr.*?</tr>#",'',$content);
		$content = str_replace("</li>",",</li>",$content);
		
		preg_match_all("#<(p|li)>(.*?)</(p|li)>#",$content,$arr);
		
		$content = "";
		foreach($arr[2] as $row) {
			$row = trim(strip_tags($row));
			if(empty($row)) continue;
			var_dump($content);
			$content.= $row." ";
		}
		
		$content = html_entity_decode($content);
		$content = str_replace(chr(160)," ",$content);
		
		$output['text'] = $content;
		$output['link'] = "http://".$server.$path.urlencode($term);
	return $output;
	}

}

?>
