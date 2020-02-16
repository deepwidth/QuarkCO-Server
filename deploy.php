<?php

/**
 * deploy.php
 * Date: 2020.2.14
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */

 /**
  * 为每一个 Java 类选择一个可用端口，
  * 并生成绑定服务的 Java 代码
  */
 
class Deploy {
	
	private static $instance = null;
	private static $deployClasses = array();
	private $deployResult = array();

	private function __construct() {}

	private function __clone() {}

	public static function getInstance($syncResult) {
		if(self::$instance == null) {
			self::$instance = new self();
			self::$deployClasses = $syncResult;
		}
		return self::$instance;
	}

	public function deployCode() {
		foreach(self::$deployClasses as $key => $filePath) {
			shell_exec("javac $filePath");
		}
	}

	public function getDeployClasses() {
		return $this->deployClasses;
	}

	private function addDeployedClass($classPath, $port) {
		$this->deployResult[$classPath] = $port;
	}

	public function getDeployResult() {
		return $this->deployResult;
	}
}
