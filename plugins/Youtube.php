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

class Youtube extends Plugin {
	
	function onChannelMessage(){
		
		// Regex by noother
		preg_match("#(www\.)?youtube\.com/.*?(\?|&)v=([a-zA-Z0-9_-]{11})#",$this->info['text'],$videoIdArray);
		$videoId = $videoIdArray[3];
		
		if (!$this->validYoutubeId($videoId)) return;
		
		$res = libHTTP::GET('gdata.youtube.com','/feeds/api/videos/'.$videoId);
		$file = $res['raw'];
		
		$file = utf8_encode($file);
		$xml = simplexml_load_string($file);
		
		$xml_rates = $xml->children('http://schemas.google.com/g/2005');
		$xml_views = $xml->children('http://gdata.youtube.com/schemas/2007');
		
		$avgRating = number_format($xml->children('http://schemas.google.com/g/2005')->rating->attributes()->average,2);
		$views = number_format($xml->children('http://gdata.youtube.com/schemas/2007')->statistics->attributes()->viewCount);
		$title = utf8_decode($xml->title);
		
		$this->sendOutput("\x02[YouTube]\x02 |\x02 Title: \x02".$title. "\x02 \x02|\x02 Rate: \x02". + $avgRating."/5.00\x02 \x02|\x02 Views: \x02".$views);
		
	}

	
	/* Check if Youtube ID is valid */
	function validYoutubeId($id) {
		if ($id == "") return false;
		
		$res = libHTTP::GET('gdata.youtube.com','/feeds/api/videos/'.$id);
		$data = $res['raw'];
		if (!$data) return false;
		if ($data == "Invalid id") return false;
		
		return true;
	}

}
?>

