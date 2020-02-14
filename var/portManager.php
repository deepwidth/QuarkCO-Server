<?php

/**
 * portManager.php
 * Date: 2020.2.13
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */

class PortManager {
	
	private static $instance = null;

	private $communicatePort = 2200;

	//最小端口是部署模块绑定服务的起始搜索端口
	private $miniPort = 2201;

	//最大端口是部署模块绑定服务的端口上界
	private $maxPort = 65535;

	//排除端口数组为不能用于 QuarkCO 绑定服务的端口
	private $exceptedPorts = array();

	private function __construct() {}

	private function __clone() {}

	public static function  getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function setCommunicatePort($port) {
		$this->communicatePort = $port;
	}

	public function getCommunicatePort() {
		return $this->communicatePort;
	}

	public function setMiniPort($port) {
		$this->miniPort = $port;
	}

	public function getMiniPort() {
		return $this->miniPort;
	}

	public function setMaxPort($port) {
		$this->maxPort = $port;
	}

	public function getMaxPort() {
		return $this->maxPort;
	}
	
	public function setExceptedPorts($ports) {
		$this->exceptedPorts = $ports;
	}

	public function getExceptedPorts() {
		return $this->exceptedPorts;
	}

	private function isAvailablePort($port) {
		if(0 == strlen(shell_exec("lsof -i:$port")) && !in_array($port, $this->exceptedPorts)) {
			return true;
		} else {
			return false;
		}
	}

	public function findAvailablePort() {
		for($port = $this->miniPort; $port <= $this->maxPort; ++$port) {
			if($this->isAvailablePort($port)) {
				return $port;
			}
		}
		return -1;
	}
}
