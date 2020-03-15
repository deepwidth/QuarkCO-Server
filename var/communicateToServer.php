<?php

/**
 * communicateToManager.php -- (弃用)
 * Date: 2020.2.14
 * Author: Zhang Kangkang
 * Website: https://github.com/twoFiveOneTen/QuarkCO-Server
 */

require_once "config.php";

class CommunicateToServer {
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

	/**
	 * 向管理模块发送一条消息并获取一条返回的消息
	 * 
	 * @access public
	 * @param string $message 要发送的消息
	 * @return string 管理模块返回的消息
	 */
	public function sendMessage($message) {
		$connectResult = socket_connect($this->socket, $this->ip, $this->port);
		if (false === $connectResult) {
		    return false;
		}
		if(!socket_write($this->socket, $message, strlen($message))) {
			return false;
		} else {
			$out = socket_read($this->socket, 1024);
			return $out;
		}
	}
}
