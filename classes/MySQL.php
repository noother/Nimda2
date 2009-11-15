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

class MySql {

	private $host;
	private $user;
	private $password;
	private $db;

	function MySQL($host, $user, $password, $db) {
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->db = $db;
		$this->connect();
	}

	function connect() {
		echo "Connecting to MySQL server..\n";
		mysql_connect($this->host, $this->user, $this->password) or die(mysql_error());
		mysql_select_db($this->db) or die(mysql_error());
		$this->sendQuery("set character set utf8");
	}
	
	function query($sql) {
		echo "SQL sent: ".$sql."\n";
		$result = mysql_query($sql);
		
		if(!$result) {
			$error = mysql_error();
			if($error == "MySQL server has gone away") {
				echo "Lost connection to MySQL server, reconnecting..\n";
				mysql_close();
				$this->connect();
				return $this->sendQuery($sql);
			} else {
				die("MySQL Error: ".$error);
			}
		}
		
		if(strtoupper(substr(trim($sql),0,6)) == "SELECT") {
			$return_array           = array();
			$return_array['result'] = array();
			$return_array['count']  = mysql_num_rows($result);
			while($row = mysql_fetch_assoc($result)) {
				array_push($return_array['result'],$row);
			}
			return $return_array;
		} elseif(strtoupper(substr(trim($sql),0,6)) == "INSERT") {
			return mysql_insert_id();
		} else {
			return true;
		}
	}
	
	function sendQuery($sql) {
		// DEPRECATED
		return $this->query($sql);
	}
	
	function getUserLevel($user) {
		$tmp = $this->sendQuery("SELECT level FROM users WHERE user='".addslashes($user)."'");
		if($tmp['count'] == 0) return 0;
	return $tmp['result'][0]['level'];
	}
	
	function userAdd($user) {
		if($this->userExists($user)) return false;
		$this->sendQuery("INSERT INTO users (user, level) VALUES ('".addslashes($user)."','0')");
	return true;
	}
	
	function userSetLevel($user, $level) {
		if(!$this->userExists($user)) return false;
		$this->sendQuery("UPDATE users SET level='".addslashes($level)."' WHERE user='".addslashes($user)."'");
	return true;
	}
	
	function userRemove($user) {
		if(!$this->userExists($user)) return false;
		$this->sendQuery("DELETE FROM users WHERE user='".addslashes($user)."'");
	return true;
	}
	
	function userExists($user) {
		$tmp = $this->sendQuery("SELECT * FROM users WHERE user='".addslashes($user)."'");
		if($tmp['count'] == 0) return false;
		else return true;
	}
}

?>
