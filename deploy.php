<?php

require_once "config.php";

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
	private static $importCode = "";	//部署代码的导包代码

	private function __construct() {}

	private function __clone() {}

	public static function getInstance($syncResult) {
		if(self::$instance == null) {
			self::$instance = new self();
			self::$deployClasses = $syncResult;
			foreach(self::$deployClasses as $classFullName => $classHandler) {
				self::$importCode = self::$importCode . "\nimport $classFullName;";
			}
		}
		return self::$instance;
	}

	private function sendMessageToServer($string) {
		$communicate = new CommunicateToServer();
		$communicate->sendMessage($string);
	}

	private function writeDeployCode($classHandler , $implementClassHandler) {
		$className = $classHandler->getClassName() . "-" . $implementClassHandler->getClassName();
		$fileName = "tmp/" . $className . ".java";
		$port = PortManager::getInstance()->findAvailablePort();
		$readDeployCodeFile = fopen("var/deployCode.txt", "r");
		$deployCode = fread($readDeployCodeFile, 8000);
		fclose($readDeployCodeFile);
		$deployCode = str_replace('?IMPORTCLASSES?', self::$importCode, $deployCode);
		$deployCode = str_replace('?DEPLOYFILENAME?', $className, $deployCode);
		$deployCode = str_replace('?INTERFACECLASS?', $classHandler->getClassName(), $deployCode);
		$deployCode = str_replace('?IMPLEMENTCLASS?', $implementClassHandler->getClassName(), $deployCode);
		$deployCode = str_replace('?PORT?', $port, $deployCode);
		$writeDeployCodeFile = fopen($fileName, "w");
		fwrite($writeDeployCodeFile, $deployCode);
		fclose($writeDeployCodeFile);
		shell_exec("javac tmp/$className.java");
		$this->sendMessageToServer("java $className");
	}

	public function deployCode() {
		$syncResult = CodeSync::getInstance()->getSyncResult();
		foreach($syncResult as $classFullName => $classHandler) {
			if($classHandler->isInterface()) {
				foreach($classHandler->getImplementClassHandler() as $key => $implementClassHandler) {
					$this->writeDeployCode($classHandler, $implementClassHandler);
				}
			} else if($classHandler->getInterfaceClass() == null){
				$this->writeDeployCode($classHandler, $classHandler);
			}
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

$deployModule = Deploy::getInstance(CodeSync::getInstance()->getSyncResult());
$deployModule->deployCode();

?>