<?php

/**
 * deploy.php
 * Date: 2020.2.14
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */

require_once "config.php";

define('__DEPLOYCODE_CONTEXT_LENGTH__', 2000);

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
		$fileName = __FILE_TEMP__ . $className . ".java";
		$port = $this->sendMessageToServer("port#");
		$readDeployCodeFile = fopen(__DEPLOY_CODE_FILE__, "r");
		$deployCode = fread($readDeployCodeFile, __DEPLOYCODE_CONTEXT_LENGTH__);
		fclose($readDeployCodeFile);
		$deployCode = str_replace('?IMPORTCLASSES?', $this->importCode, $deployCode);
		$deployCode = str_replace('?DEPLOYFILENAME?', $className, $deployCode);
		$deployCode = str_replace('?INTERFACECLASS?', $classHandler->getClassName(), $deployCode);
		$deployCode = str_replace('?IMPLEMENTCLASS?', $implementClassHandler->getClassName(), $deployCode);
		$deployCode = str_replace('?PORT?', $port, $deployCode);
		if(!is_dir(__FILE_TEMP__)) {
			mkdir(__FILE_TEMP__, 0777, true);
		}
		touch($fileName);
		$writeDeployCodeFile = fopen($fileName, "w");
		fwrite($writeDeployCodeFile, $deployCode);
		fclose($writeDeployCodeFile);
		if(0 == $this->sendMessageToServer("java#javac $fileName#$port")) {
			if(0 == $this->sendMessageToServer("java#java $className#$port")){
				if(0 == $this->sendMessageToServer("save#"
				. $implementClassHandler->getClassFullName() . "#" . $port)) {
					$this->addDeployedClass($implementClassHandler->getClassFullName(), $port);
				}
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