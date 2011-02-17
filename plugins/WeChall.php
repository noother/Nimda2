<?php

class WeChall extends Plugin {

	function isTriggered() {
		if(isset($this->info['text'])) $target = $this->info['text'];
		else $target = $this->info['nick'];
		
		$tmp = explode(' ',$this->info['text']);
		if(isset($tmp[0]) && $tmp[0][0] == '!' && !isset($tmp[1])) $target.= ' '.$this->info['nick'];
		
		
		if($this->info['triggerUsed'] == '!wcc') {
			$this->info['text'] = (!empty($this->info['text'])?'!wechall '.$this->info['text']:'!wechall');
			$this->info['triggerUsed'] = '!wc';
			return $this->isTriggered();
		}
		
		
		
		
		
		$result = libHTTP::GET("www.wechall.net","/wechall.php?username=".urlencode($target));
		$tmp = $result['content'][0];
		
		if($tmp == 'The user \''.$target.'\' doesnt exist.') {
			$this->sendOutput($this->CONFIG['notfound_text']);
			return;
		}
		
		$this->sendOutput($tmp);
	}

}

?>
