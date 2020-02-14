<?php

/**
 * communicateToManager.php
 * Date: 2020.2.14
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */

require_once "config.php";

class CommunicateToManager {
	private $ip = "127.0.0.1";
	private $port;
	private $socket;

	function __construct() {
		$portManager = PortManager::getInstance();
		$this->port = $portManager->getCommunicatePort();
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->socket < 0) {
		    return null;
		}
	}

	function __destruct() {
		socket_close($this->socket);
	}

	public function sendMessage($message) {
		$connectResult = socket_connect($this->socket, $this->ip, $this->port);
		if ($connectResult < 0) {
		    return -1;
		}
		if(!socket_write($this->socket, $message, strlen($message))) {
			return -1;
		} else {
			$out = socket_read($this->socket, 1024);
			return $out;
		}
	}
}
