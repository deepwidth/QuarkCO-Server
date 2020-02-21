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
	private $deployClasses = array();
	private $deployResult = array();
	private $importCode = "";	//部署代码的导包代码

	private function __construct() {}

	private function __clone() {}

	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function sendMessageToServer($string) {
		$communicate = new CommunicateToServer();
		return $communicate->sendMessage($string);
	}

	private function writeDeployCode($classHandler , $implementClassHandler) {
		$className = $classHandler->getClassName() . "_" . $implementClassHandler->getClassName();
		$fileName = "tmp/" . $className . ".java";
		$port = $this->sendMessageToServer("port#");
		$readDeployCodeFile = fopen("var/deployCode.txt", "r");
		$deployCode = fread($readDeployCodeFile, 8000);
		fclose($readDeployCodeFile);
		$deployCode = str_replace('?IMPORTCLASSES?', $this->importCode, $deployCode);
		$deployCode = str_replace('?DEPLOYFILENAME?', $className, $deployCode);
		$deployCode = str_replace('?INTERFACECLASS?', $classHandler->getClassName(), $deployCode);
		$deployCode = str_replace('?IMPLEMENTCLASS?', $implementClassHandler->getClassName(), $deployCode);
		$deployCode = str_replace('?PORT?', $port, $deployCode);
		if(!is_dir("tmp")) {
			mkdir("tmp", 0777, true);
		}
		touch($fileName);
		$writeDeployCodeFile = fopen($fileName, "w");
		fwrite($writeDeployCodeFile, $deployCode);
		fclose($writeDeployCodeFile);
		if("succeed" == $this->sendMessageToServer("java#javac $fileName")) {
			if("succeed" == $this->sendMessageToServer("java#java $className")) {
				$this->addDeployedClass($implementClassHandler->getClassFullName(), $port);
			}
		}
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

	public function setDeployClasses($syncResult) {
		$this->deployClasses = $syncResult;
	}

	public function setImportCode() {
		foreach($this->deployClasses as $classFullName => $classHandler) {
			$this->importCode = $this->importCode . "\nimport $classFullName;";
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
$deployModule = Deploy::getInstance();
$deployModule->setDeployClasses(CodeSync::getInstance()->getSyncResult());
$deployModule->setImportCode();
$deployModule->deployCode();
exit(json_encode($deployModule->getDeployResult()));
?>