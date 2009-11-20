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

class Weather extends Plugin {

	function isTriggered() {
		
		# First get the raw XML data using Google API.		
		# Example: http://www.google.com/ig/api?weather=paris,france&hl=fr
		
        $url    = "http://www.google.com/ig/api?weather=".urlencode($this->info['text'])."&hl=de";
		
		
		$file = file_get_contents($url);
		$file = utf8_encode($file);
		
		
		#$xml = new SimpleXMLElement($url, NULL, TRUE);
		$xml = simplexml_load_string($file);
		
		
		$temp = $xml->weather->forecast_information->city;
		if($temp)
		{
			$location = $temp->attributes()->data;
			$condition = $xml->weather->current_conditions->condition->attributes()->data;
			$temp_c = $xml->weather->current_conditions->temp_c->attributes()->data;
			$humidity = $xml->weather->current_conditions->humidity->attributes()->data;
		
			$this->sendOutput("Wetter in ".$location.": ".$condition.", ".$temp_c."°C, ".$humidity);}
		else{
			$this->sendOutput($this->CONFIG['error']);
		}
	}

}
?>
