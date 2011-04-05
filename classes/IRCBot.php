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

require_once("classes/Plugin.php");
require_once("classes/Timer.php");
require_once("classes/OneShotTimer.php");
require_once("classes/MySQL.php");
require_once("libs/libArray.php");
require_once("libs/libString.php");
require_once("libs/libConvert.php");
require_once("libs/libHTTP.php");
require_once("libs/libCrypt.php");
require_once("libs/libInternet.php");
require_once("libs/libSystem.php");
require_once("libs/libTime.php");

class IRCBot{

	public  $CONFIG					= array();
	public  $nick					= "";
	private $username				= "";
	private $hostname				= "";
	private $realname				= "";
	private $password				= "";
	public  $server					= "";
	public  $servername				= "";
	public  $port					= 0;
	public  $socket					= false;
	public  $incomingMessage		= "";
	public  $lastMessageReceived	= 0;
	private $outgoingMessage		= "";
	public  $splittedMessage		= array();
	public  $ctcpParams				= array();
	public  $plugins				= array();
	private $timers					= array();
	public  $oneShotTimers			= array();
	private $info					= array();
	public  $MySQL;
	private $isAlive				= true;
	private $sleep					= false;
	private $logFile;
	public  $startTime;
	private $isBusy					= false;
	
	
	

	function IRCBot($nick="Lamer", $username="Lamer", $hostname="0", $realname="A lame bot") {
		$this->startTime = time();
		$this->loadConfig();
		$this->isAlive       = true;
		$this->nick          = $nick;
		$this->username      = $username;
		$this->hostname      = $hostname;
		$this->realname      = $realname;
		$this->MySQL         = new MySQL(
									$this->CONFIG['mysql_host'],
									$this->CONFIG['mysql_user'],
									$this->CONFIG['mysql_password'],
									$this->CONFIG['mysql_database']
									);
		
		$this->initializePlugins();
		$this->initializeTimers();
		$this->createLogFile();
	}
	
	private function createLogFile() {
		$filename = "logs/".date("Y-m-d_H-i-s").".log";
		$this->logFile = fopen($filename, 'w');
	}
	
	public function writeLog($text) {
		fputs($this->logFile,$text."\n");
	}
	
	private function closeLogFile() {
		fclose($this->logFile);
	}
	
	function parseConfigFile($file) {
		if(!file_exists($file)) return false;
		
		$array = array();
		$fp = fopen($file,"r");
		while($row = fgets($fp)) {
			$row = trim($row);
			if(preg_match('/^([A-Za-z0-9_]+?)\s+=\s+(.+?)$/',$row,$arr)) {
				$array[$arr[1]] = $arr[2];
			}
		}
		fclose($fp);
	return $array;
	}
	
	private function loadConfig() {
		$this->CONFIG = $this->parseConfigFile("config/ircbot.conf");
		if(!$this->CONFIG) {
			die("Config file config/ircbot.conf doesn't exist.\n");
		}
		echo "Config file conf/ircbot.conf has been loaded successfully.\n";
		
	}
	
	private function initializePlugins() {
		$dir = opendir("config/plugins");
		while($file = readdir($dir)) {
			if(substr($file,-5) == ".conf") {
				$this->loadPlugin($file);
			}
		}
		closedir($dir);
	}
	
	private function initializeTimers() {
		$dir = opendir("config/timers");
		while($file = readdir($dir)) {
			if(substr($file,-5) == ".conf") {
				$this->loadTimer($file);
			}
		}
		closedir($dir);
	}
	
	private function loadTimer($configFile) {
		if(!file_exists("config/timers/".$configFile)) {
			echo "Timer with config file '".$configFile."' has not been loaded cause it doesn't exist.";
			return false;
		}
		$fp = fopen("config/timers/".$configFile,"r");

		$config = array();
		while($row = fgets($fp)) {
			$row = trim($row);
			if(preg_match('/^([A-Za-z0-9_]+?)\s+=\s+(.+?)$/',$row,$arr)) {
				$config[$arr[1]] = $arr[2];
			}
		}
		fclose($fp);
		
		if(!isset($config['name'])) {
			echo "Timer with config file '".$configFile."' has not been loaded cause it has no name.\n";
			return false;
		}
		
		if(!isset($config['interval'])) {
			echo "Timer with config file '".$configFile."' has not been loaded cause it has no interval.\n";
			return false;
		}
		
		require_once("timers/".$config['name'].".php");
		$this->timers[$config['name']] = new $config['name']($this,$config);
		echo "Timer with name '".$config['name']."' has been loaded successfully\n";
	return true;
	}
	
	private function loadPlugin($configFile) {
		$config = $this->parseConfigFile("config/plugins/".$configFile);
		$config['configFile'] = $configFile;
		
		if(!$config) {
			// This should never happen because only existing files are given by initializePlugins()
			echo "Plugin with config file '".$configFile."' has not been loaded cause it doesn't exist.";
			return false;
		}
		
		if(!isset($config['name'])) {
			echo "Plugin with config file '".$configFile."' has not been loaded cause it has no name.\n";
			return false;
		}
		
		echo "Loading Plugin '".$config['name']."'....  ";
		require_once("plugins/".$config['name'].".php");
		$this->plugins[$config['name']] = new $config['name']($config,$this);
		echo "OK\n";
	return true;
	}
	
	function connect($server="127.0.0.1", $port=6667) {
		$this->server = $server;
		$this->port   = $port;
		
		$connected = false;
		$limit = $this->CONFIG['connection_retrys'] != 0 ? $this->CONFIG['connection_retrys'] : 99999999;
		for($x=0;$x<$limit;$x++) {
			echo "Connecting to server ".$server."... ";
			$this->socket = @fsockopen($server,$port);
			if(!$this->socket) {
				echo "Error\n";
				echo "Could not connect to server ".$server." on port ".$port."\n";
			} else {
				$connected = true;
				break;
			}
			$sleep = ($x+1)*10;
			echo "Retrying in ".$sleep." seconds.\n";
			sleep($sleep);
		}
		
		stream_set_timeout($this->socket,$this->CONFIG['message_timeout']);
		echo "Done\n";
	}
	
	function disconnect() {
		fclose($this->socket);
	}
	
	function login() {
		$this->sendRawMessage("NICK ".$this->nick);
		$this->sendRawMessage("USER ".$this->username." ".$this->hostname." ".$this->server." :".$this->realname);
		//$this->sendRawMessage("MODE ".$this->nick." +B");
	}
	
	function identify() {
		$this->sendPrivmsg("NickServ","identify ".$this->CONFIG['nickserv_password']);
	}
	
	function readMessage() {
		$this->info = array();
		if(!$this->isAlive) return false;
		$this->init();
		$this->incomingMessage = fgets($this->socket);
		if(!empty($this->incomingMessage)) {
			$this->lastMessageReceived = time();
			$this->displayIncomingMessage(trim($this->incomingMessage));
			$this->writeLog('['.date("Y-m-d H:i:s")."] << ".trim($this->incomingMessage));
			$this->splitMessage($this->incomingMessage);
			$this->triggerEvents();
		}
		$this->triggerTimers();
		$this->triggerOneShotTimers();
	return true;
	}
	
	private function init() {
		$this->sleep = true;
		$this->splittedMessage = array();
		$this->ctcpParams      = array();
	}
	
	private function sendPong($ping) {
		$this->sendRawMessage("PONG :".$ping);
	}
	
	private function splitMessage($message) {
		preg_match("/^(:(.+?) +?)?([A-Za-z]+?|[0-9]{3})( +?.+?)\r\n$/",$message,$tmp);
		$prefix  = $tmp[2];
		$command = $tmp[3];
		$params  = $tmp[4];
		
		if(!empty($prefix)) {
			$this->splittedMessage['prefix'] = $prefix;
			preg_match("/^(.*?)(!(.*?))?(@(.*?))?$/",$prefix,$tmp);
			if(strstr($tmp[1],'.')) {
				$this->splittedMessage['servername'] = $tmp[1];
			} else {
				$this->splittedMessage['nick'] = $tmp[1];
				if(!empty($tmp[3])) $this->splittedMessage['user'] = $tmp[3];
				if(!empty($tmp[5])) $this->splittedMessage['host'] = $tmp[5];
			}
		}
		
		$this->splittedMessage['command'] = $command;
		
		$params_array = array();
		do {
			preg_match("/^ ((:(.*?$))|((.*?)( .*)?))?$/",$params,$tmp);
			if(!empty($tmp[3])) {
				$trailing = $tmp[3];
				$params = "";
				array_push($params_array,$trailing);
			} else {
				$middle = $tmp[5];
				$params = $tmp[6];
				array_push($params_array,$middle);
			}
		} while(!empty($params));
		
		$this->splittedMessage['params'] = $params_array;
	}
	
	function getUserStatus($nick) {
		$info = $this->info['PRIVMSG'];
		$this->sleep = false;
		$this->sendPrivmsg('NickServ','ACC '.$nick);
		$this->sleep = true;
		
		$check = false;
		stream_set_timeout($this->socket,1);
		for($x=0;$x<5;$x++) {
			$this->readMessage();
			if(strtolower($this->splittedMessage['nick']) != "nickserv") {
				continue;
			} else {
				$check = true;
				break;
			}
		}
		stream_set_timeout($this->socket,$this->CONFIG['message_timeout']);
		if(!$check) {
			$this->sendPrivmsg($info['target'],$this->CONFIG['text_no_nickserv']);
			return false;
		}
		preg_match('/ ACC (\d)/',$this->splittedMessage['params'][1],$arr);
	return $arr[1];
	}
	
	private function triggerEvents() {
		if($this->splittedMessage['command'] == "001") $this->onLoginSuccess();
		if($this->splittedMessage['command'] == "376") $this->onEndMotd();
		if($this->splittedMessage['command'] == "433") $this->onNicknameAlreadyInUse();
		if($this->splittedMessage['command'] == "PRIVMSG") {
			$this->onMessage();
			
			$tmp = explode(" ",$this->splittedMessage['params'][1]);
			switch($tmp[0]) {
				case $this->CONFIG['plugin_reload_trigger']:
					$this->onPluginReloadRequest();
					break;
				case $this->CONFIG['user_add_trigger']:
					$this->onUserAddRequest();
					break;
				case $this->CONFIG['user_remove_trigger']:
					$this->onUserRemoveRequest();
					break;
				case $this->CONFIG['user_setlevel_trigger']:
					$this->onUserSetLevelRequest();
					break;
				case $this->CONFIG['quit_trigger']:
					$this->onQuitRequest();
					break;
				case $this->CONFIG['rehash_trigger'];
					$this->onRehashRequest();
					break;
			}
			
			if(strtolower($this->splittedMessage['params'][0]) == strtolower($this->nick)) {
				$this->onQuery();
			} else {
				$this->onChannelMessage();
			}
			
			if(preg_match("/^\x01(.*)\x01$/",$this->splittedMessage['params'][1],$arr)) {
				$this->onCtcp($arr[1]);
			}
		}
		if($this->splittedMessage['command'] == "PING")	$this->onPing();
		if($this->splittedMessage['command'] == "KICK")	$this->onKick();
		if($this->splittedMessage['command'] == "TOPIC") $this->onTopic();
		if($this->splittedMessage['command'] == "MODE") $this->onMode();
		if($this->splittedMessage['command'] == "JOIN") $this->onJoin();
	}
	
	private function triggerTimers() {
		$currentTime = time();
		foreach($this->timers as $timer) {
			if($currentTime >= $timer->lastTimeTriggered+$timer->interval) {
				$timer->trigger();
			}
		}
	}
	
	private function triggerOneShotTimers() {
		foreach($this->oneShotTimers as $key => $timer) {
			if($timer->trigger()) {
				unset($this->oneShotTimers[$key]);
			}
		}
	}
	
	function joinChannels($channels) {
		$this->sendRawMessage("JOIN ".$channels);
	}
	
	function setNickName($nick, $password) {
		$this->sendRawMessage("NICK ".$nick);
		$this->sendPrivmsg("NickServ","identify ".$password);
	}
	
	private function displayIncomingMessage($message) {
		echo date($this->CONFIG['time_format'])." ".$this->CONFIG['message_incoming_indicator']." ".(libString::isUTF8($message)?$message:utf8_encode($message))."\n";
	}
	
	private function displayOutgoingMessage($message) {
		echo date($this->CONFIG['time_format'])." ".$this->CONFIG['message_outgoing_indicator']." ".$message."\n";
	}
	
	function sendRawMessage($message) {
		fputs($this->socket,$message."\r\n");
		$this->outgoingMessage = $message;
		$this->displayOutgoingMessage($message);
		$this->writeLog('['.date("Y-m-d H:i:s")."] >> ".$message);
		if($this->sleep) usleep(200000);
	}
	
	function sendPrivmsg($target, $text, $checkcontrolchars=true) {
		if($checkcontrolchars) {
			for($x=0;$x<strlen($text);$x++) {
				if(in_array(ord($text{$x}), array(1, 10, 13))) {
					echo "INFO: Dropped bad message\n";
					return false;
				}
			}
		}
		
		$this->sendRawMessage("PRIVMSG ".$target." :".$text);
		
	return true;
	}
	
	function sendNotice($target, $text) {
		$this->sendRawMessage("NOTICE ".$target." :".$text);
	}
	
	function setUserModes($channel,$modes,$nicks) {
		$this->sendRawMessage("MODE ".$channel." ".$modes." ".$nicks);
	}
	
	function sendAction($target,$text) {
		$this->sendCTCP($target,"ACTION ".$text);
	}
	
	function sendCtcp($target, $text) {
		$this->sendPrivmsg($target, "\x01".$text."\x01", false);
	}
	
	function answerCtcp($nick, $text) {
		$this->sendNotice($nick, "\x01".$text."\x01");
	}
	
	private function sendCtcpPing() {
		
	}
	
	private function getInfo() {
		$info = array();
		
		if(isset($this->splittedMessage['params'][1])) {
			$info['text'] = $this->splittedMessage['params'][1];
			if(!libString::isUTF8($info['text'])) {
				$info['text'] = utf8_encode($info['text']);
			}
			$info['text'] = trim($info['text']);
		}
		
		if(strtolower($this->splittedMessage['params'][0]) == strtolower($this->nick)) {
			$info['isQuery'] = true;
			$info['target']  = $this->splittedMessage['nick'];
		} else {
			$info['isQuery'] = false;
			$info['target']  = $this->splittedMessage['params'][0];
			$info['channel'] = $info['target'];
		}
		$info['prefix'] = $this->splittedMessage['prefix'];
		$info['host']   = $this->splittedMessage['host'];
		$info['nick']   = $this->splittedMessage['nick'];
		$info['user']   = $this->splittedMessage['user'];
		$info['nick']   = $this->splittedMessage['nick'];
		
	return $info;
	}
	
	private function getCtcpInfo() {
		$info = $this->info['PRIVMSG'];
		unset($info['text']);
		
		$info['CTCP'] = $this->ctcpParams[0];
		
		$tmp = "";
		for($x=1;$x<sizeof($this->ctcpParams);$x++) {
			$tmp.= $this->ctcpParams[$x]." ";
		}
		$tmp = substr($tmp,0,-1);
		
		if(!empty($tmp)) $info['text'] = $tmp;
	return $info;
	}
	
	function getKickInfo() {
		$info = $this->getInfo();
		unset($info['isQuery']);
		$info['kicked'] = $info['text'];
		unset($info['text']);
		$info['reason'] = $this->splittedMessage['params'][2];
		
	return $info;
	}
	
	function getTopicInfo() {
		$info = $this->getInfo();
		unset($info['isQuery']);
		$info['topic'] = $info['text'];
		unset($info['text']);
		
	return $info;
	}
	
	function getModeInfo() {
		$info = $this->getInfo();
		unset($info['isQuery']);
		unset($info['text']);
		$modes = $this->splittedMessage['params'][1];
		$sign = '';
		$count = 2;
		$array = array();
		for($x=0;$x<strlen($modes);$x++) {
			if($modes[$x] == "+" || $modes[$x] == "-") {
				$sign = $modes[$x];
				continue;
			}
			$nick = $this->splittedMessage['params'][$count++];
			array_push($array,array($sign,$modes[$x],$nick));
		}
		$info['modes'] = $array;
		
	return $info;
	}
	
	function getHost($nick) {
		$this->sendRawMessage("WHOIS ".$nick);
		
		while($this->splittedMessage['command'] != '311') {
			$this->readMessage();
			if($this->splittedMessage['command'] == '401') return false;
		}
	return $this->splittedMessage['params'][3];
	}
	
	function getUsers($channel) {
		if($this->isBusy) {
			echo "INFO: Called getUsers() for ".$channel." while busy\n";
			return false;
		}
		
		$this->isBusy = true;
		$this->sendRawMessage("WHO ".$channel);
		
		$nicks = array();
		while(true) {
			if($this->splittedMessage['command'] == "315" && $this->splittedMessage['params'][1] == $channel) break;
			
			$this->readMessage();
			if($this->splittedMessage['command'] == "352" && $this->splittedMessage['params'][1] == $channel) {
				$nick = array();
				$nick['nick'] = $this->splittedMessage['params'][5];
				$nick['host'] = $this->splittedMessage['params'][3];
				$nick['rights'] = $this->splittedMessage['params'][6];
				
				array_push($nicks,$nick);
			}
		}
		
		$this->isBusy = false;
	return $nicks;
	}
	
	function getUserLevel($nick) {
		$level = $this->MySQL->getUserLevel($nick);
		$this->lastLevel = $level;
		if($level == 0) return 0;
		
		$userStatus = $this->getUserStatus($nick);
		if($userStatus === false) return false;
		if($userStatus != 3) return 0;
	return $level;
	}
	
	function quit($quitMessage="Requested Quit") {
		$this->sendRawMessage("QUIT :".$quitMessage);
		fclose($this->socket);
		$this->closeLogFile();
		$this->isAlive = false;
	}
	
	function isAuthorized($nick, $level) {
		if($level == 0) return true;
		if($this->getUserLevel($nick) < $level) return false;
		else return true;
	}
	
	
	
	// Events
	function onLoginSuccess() {
		$this->servername = $this->splittedMessage['servername'];
	}
	
	function onEndMotd() {
		if($this->CONFIG['nickserv_password'] != 'empty') {
			$this->identify();
			sleep(5);
		}
		
		$this->joinChannels($this->CONFIG['channels']);
	}
	
	function onNicknameAlreadyInUse() {
		$org_nick = $this->nick;
		$this->nick = $org_nick.rand(10000,99999);
		$this->login();
		if($this->CONFIG['nickserv_password'] != 'empty') {
			$this->sendPrivmsg("NickServ","ghost ".$org_nick." ".$this->CONFIG['nickserv_password']);
			sleep(1);
			$this->setNickName($org_nick,$this->CONFIG['nickserv_password']);
			$this->nick = $org_nick;
		}
	}
	
	function onMessage() {
		$this->info['PRIVMSG'] = $this->getInfo();
		$org_info = $this->info['PRIVMSG'];
		
		foreach($this->plugins as $name => $config) {
			$info = $org_info;
			$this->plugins[$name]->info = $info;
			$this->plugins[$name]->onMessage();
			foreach($config->triggers as $trigger) {
				if(strtolower(substr($info['text'],0,strlen($trigger))) == strtolower($trigger)) {
					echo $trigger."\n";
					$info['triggerUsed'] = $trigger;
					$tmp = substr($info['text'],strlen($trigger)+1);
					$info['fullText'] = $info['text'];
					unset($info['text']);
					if(!empty($tmp)) $info['text'] = $tmp;
					$this->plugins[$name]->info = $info;
					$this->plugins[$name]->trigger();
					break;
				}
			}
		}
	}
	
	function onChannelMessage() {
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->info = $this->info['PRIVMSG'];
			$this->plugins[$name]->onChannelMessage();
		}
	}
	
	function onQuery() {
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->onQuery();
		}
	}
	
	function onPing() {
		$this->sendPong($this->splittedMessage['params'][0]);
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->onPing();
		}
	}
	
	function onKick() {
		$this->info['KICK'] = $this->getKickInfo();
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->info = $this->info['KICK'];
			$this->plugins[$name]->onKick();
		}
	}
	
	function onTopic() {
		$this->info['TOPIC'] = $this->getTopicInfo();
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->info = $this->info['TOPIC'];
			$this->plugins[$name]->onTopic();
		}
	}
	
	function onMode() {
		$this->info['MODE'] = $this->getModeInfo();
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->info = $this->info['MODE'];
			$this->plugins[$name]->onMode();
		}
	}
	
	function onAction() {
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->info = $this->info['CTCP'];
			$this->plugins[$name]->onAction();
		}
	}
	function onJoin() {
		$this->info['JOIN'] = $this->getInfo();
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->info = $this->info['JOIN'];
			$this->plugins[$name]->onJoin();
		}
	}
	
	
	function onCtcp($params) {
		$tmp = explode(" ",$params);
		$this->ctcpParams = $tmp;
		$this->info['CTCP'] = $this->getCtcpInfo();
		switch($this->ctcpParams[0]) {
			case "ACTION":
				$this->onAction();
				break;
			case "PING":
				$this->onCtcpPing();
				break;
			case "FINGER":
				$this->onCtcpFinger();
				break;
			case "VERSION":
				$this->onCtcpVersion();
				break;
			case "TIME":
				$this->onCtcpTime();
				break;
		}
		
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->onCtcp();
		}
	}
	
	function onCtcpPing() {
		$this->answerCtcp($this->splittedMessage['nick'], "PING ".$this->ctcpParams[1]);
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->onCtcpPing();
		}
	}
	
	function onCtcpFinger() {
		$this->answerCtcp($this->splittedMessage['nick'], "FINGER ".$this->CONFIG['ctcp_finger']);
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->onCtcpFinger();
		}
	}
	
	function onCtcpVersion() {
		$this->answerCtcp($this->splittedMessage['nick'], "VERSION ".$this->CONFIG['ctcp_version']);
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->onCtcpVersion();
		}
	}
	
	function onCtcpTime() {
		$this->answerCtcp($this->splittedMessage['nick'], "TIME ".date($this->CONFIG['ctcp_time']));
		foreach($this->plugins as $name => $config) {
			$this->plugins[$name]->onCtcpTime();
		}
	}
	
	private function onPluginReloadRequest() {
		$info  = $this->info['PRIVMSG'];
		$tmp = explode(" ",$info['text'],2);
		$info['text'] = $tmp[1];
		
		$userLevel = $this->getUserLevel($info['nick']);
		if($userLevel === false) return false;
		if($userLevel < $this->CONFIG['admin_level']) {
			$this->sendPrivmsg($info['target'],sprintf($this->CONFIG['text_not_authorized'],$this->CONFIG['admin_level'],$this->lastLevel));
			return;
		}
		
		if(!isset($info['text'])) {
			$this->sendPrivmsg($info['target'],$this->CONFIG['text_no_plugin_specified']);
			return;
		}
		
		if(!isset($this->plugins[$info['text']])) {
			$this->sendPrivmsg($info['target'],$this->CONFIG['text_plugin_doesnt_exist']);
			return;
		}
		
		if(!$this->loadPlugin($this->plugins[$info['text']]->configFile)) {
			$this->sendPrivmsg($info['target'],"Some error occurred while reloading the Plugin with name '".$this->plugins[$info['text']]->name."'.");
			return;
		}
		
		$this->sendPrivmsg($info['target'],sprintf($this->CONFIG['text_plugin_reloaded'],$this->plugins[$info['text']]->name));
	}
	
	private function onUserAddRequest() {
		$info = $this->info['PRIVMSG'];
		$tmp = explode(" ",$info['text'],2);
		$info['text'] = $tmp[1];
		
		$tmp  = explode(" ",$info['text']);
		
		$userLevel = $this->getUserLevel($info['nick']);
		if($userLevel === false) return false;
		if($userLevel < $this->CONFIG['admin_level']) {
			$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_not_authorized'],$this->CONFIG['admin_level'],$this->lastLevel));
			return;
		}
		
		if(!$this->MySQL->userAdd($tmp[0])) {
			$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_user_already_in_database'],$tmp[0]));
			return;
		}
		
		$level = 0;
		if(isset($tmp[1]) && is_numeric($tmp[1])) {
			$level = (int)$tmp[1];
			$this->MySQL->userSetLevel($tmp[0], $level);
		}
		$this->sendPrivmsg($info['target'],sprintf($this->CONFIG['text_user_added'],$tmp[0],$level));
	}
	
	private function onUserRemoveRequest() {
		$info = $this->info['PRIVMSG'];
		$tmp = explode(" ",$info['text'],2);
		$info['text'] = $tmp[1];
		
		$userLevel = $this->getUserLevel($info['nick']);
		if($userLevel === false) return false;
		if($userLevel < $this->CONFIG['admin_level']) {
			$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_not_authorized'],$this->CONFIG['admin_level'],$this->lastLevel));
			return;
		}
		
		if(!$this->MySQL->userRemove($tmp[1])) {
			$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_user_doesnt_exist'],$tmp[1]));
			return;
		}
		
		$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_user_removed'],$tmp[1]));
	}
	
	private function onUserSetLevelRequest() {
		$info  = $this->info['PRIVMSG'];
		$tmp = explode(" ",$info['text'],2);
		$info['text'] = $tmp[1];
		
		$tmp   = explode(" ",$info['text']);
		$level = (int)$tmp[1];
		
		$userLevel = $this->getUserLevel($info['nick']);
		if($userLevel === false) return false;
		if($userLevel < $this->CONFIG['admin_level']) {
			$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_not_authorized'],$this->CONFIG['admin_level'],$this->lastLevel));
			return;
		}
		
		if(!isset($tmp[1]) || !is_numeric($tmp[1])) {
			$this->sendPrivmsg($info['target'], $this->CONFIG['text_no_userlevel_specified']);
			return;
		}
		
		if(!$this->MySQL->userSetLevel($tmp[0],$level)) {
			$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_user_doesnt_exist'],$tmp[0]));
			return;
		}
		
		$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_userlevel_set'],$tmp[0],$level));
	}
	
	private function onQuitRequest() {
		$info  = $this->info['PRIVMSG'];
		$tmp = explode(" ",$info['text'],2);
		$info['text'] = $tmp[1];
		
		$userLevel = $this->getUserLevel($info['nick']);
		if($userLevel === false) return false;
		if($userLevel < $this->CONFIG['admin_level']) {
			$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_not_authorized'],$this->CONFIG['admin_level'],$this->lastLevel));
			return;
		}
		
		if(isset($info['text'])) $quitMessage = $info['text'];
		else $quitMessage = $this->CONFIG['quit_message'];
		
		$this->MySQL->sendQuery("INSERT INTO uptime (seconds, quitted) VALUES('".(time()-$this->startTime)."',NOW())");
		
		$this->quit($quitMessage);
	}
	
	private function onRehashRequest() {
		$info  = $this->info['PRIVMSG'];
		$tmp = explode(" ",$info['text'],2);
		$info['text'] = $tmp[1];
		
		$userLevel = $this->getUserLevel($info['nick']);
		if($userLevel === false) return false;
		if($userLevel < $this->CONFIG['admin_level']) {
			$this->sendPrivmsg($info['target'], sprintf($this->CONFIG['text_not_authorized'],$this->CONFIG['admin_level'],$this->lastLevel));
			return;
		}
		
		$this->loadConfig();
		$this->initializePlugins();
		$this->initializeTimers();
		
		$this->sendPrivmsg($info['target'], $this->CONFIG['text_rehash_complete']);
	}
	
}

?>
