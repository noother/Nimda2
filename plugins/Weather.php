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
		
		if ($this->info['text'] == NULL){
			$output = sprintf($this->CONFIG['usage'],$this->info['triggerUsed']);
			$this->sendOutput($output);
			return;
		}
		
		$url    = "http://www.google.com/ig/api?weather=".urlencode($this->info['text'])."&hl=".$this->CONFIG['language'];
			
		$file = file_get_contents($url);
		$file = utf8_encode($file);
		$xml = simplexml_load_string($file);
		
		$temp = $xml->weather->forecast_information->city;
		if($temp)
		{
			if($this->info['triggerUsed'] == "!weather")
				$this->currentCondition($xml);
			else
				$this->forecast($xml);
		}
		else
		{
			$this->sendOutput($this->CONFIG['unknown']);
		}
	}
	
	function currentCondition($xml){
		$location = $xml->weather->forecast_information->city->attributes()->data;
		$condition = $xml->weather->current_conditions->condition->attributes()->data;
		$temp_c = $xml->weather->current_conditions->temp_c->attributes()->data;
		$humidity = $xml->weather->current_conditions->humidity->attributes()->data;
	
		$this->sendOutput("Wetter in \x0F\x02".$location.":\x02 ".$condition.", ".$temp_c."°C, ".$humidity);
	}
	
	function forecast($xml){
		
		$location = $xml->weather->forecast_information->city->attributes()->data;
		
		foreach ($xml->weather->forecast_conditions as $forecast_condition){
			$condition = $forecast_condition->condition->attributes()->data;
			$temp_c_min = $forecast_condition->low->attributes()->data;
			$temp_c_max = $forecast_condition->high->attributes()->data;
			$day = $forecast_condition->day_of_week->attributes()->data;
			
			$this->sendOutput("Wetter in \x0F\x02".$location." am ".$day.":\x02 ".$condition.", min.: ".$temp_c_min."°C, max.: ".$temp_c_max."°C");
		}
		
	}
}
?>
