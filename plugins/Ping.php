<?php

class Ping extends Plugin {

	function isTriggered() {
		
		switch($this->info['triggerUsed']) {
			case '!pong':
				$this->sendOutput('Ping?');
				break;
			case '!pang':
				$this->sendOutput('Peng!');
				break;
			case '!peng':
				$this->sendOutput('Pang!');
				break;
			case '!pung':
				$this->sendOutput('Pyng?');
				break;
			default:
				$this->sendOutput($this->CONFIG['pong_text']);
		}
		
	}

}

?>
