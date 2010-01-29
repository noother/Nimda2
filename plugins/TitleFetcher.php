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

class TitleFetcher extends Plugin {

	function onChannelMessage() {
		$link = libString::getLink($this->info['text']);
		if(!$link) return;
		
		if(substr(strtolower($link),0,7) == 'http://') {
			$link = substr($link,7);
		}
		
		list($host,$get) = explode('/',$link,2);
		if($host == 'youtube.com' || $host == 'www.youtube.com') return;
		$get = '/'.$get;
		
		$res = libHTTP::GET($host,$get);
		if(!$res) return;
		if(substr($res['header']['Content-Type'],0,9) != 'text/html') return;
		
		$check = preg_match('#<title>(.*?)</title>#i',$res['raw'],$arr);
		if(!$check || empty($arr[1])) return;
		
		$title = html_entity_decode($arr[1],null,'UTF-8');
		$title = strtr($title,array("\r" => ' ', "\n" => ' '));
		
		if($title == '301 Moved') return;
		
		$this->sendOutput($title." ( http://".$link." )");
	}

}

?>

