<?php

/**
 * deploy.php
 * Date: 2020.2.14
 * Author: Zhang Kangkang
 * Website: https://github.com/twoFiveOneTen/QuarkCO-Server
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
	private $serviceLabel;

	private function __construct() {
		$this->serviceLabel = makeRandStr();
		$this->addDeployedClass("serviceLabel", $this->serviceLabel);
	}

	private function __clone() {}

	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * 部署绑定服务的Java代码
	 * 
	 * @access private
	 * @param mixed $classHandler 绑定服务java代码中的创建类的变量类型
	 * @param mixed $implementClassHandler 绑定服务java代码中的创建类的方法
	 */
	private function writeDeployCode($classHandler, $implementClassHandler) {

		$classNamePair = $classHandler->getClassFullName() . "_" . $implementClassHandler->getClassFullName();
		$classNamePair = str_replace('.', '_', $classNamePair);
		$fileNameWithoutExt = __FILE_TEMP__ . $classNamePair;
		$fileName = $fileNameWithoutExt . ".java";

		// 部署服务前，先杀掉之前可能已经部署过的此服务，防止重复部署
		$killResult = sendMessageToServer("kill#$classNamePair");
		if($killResult == __FAILED__) {
			$port = sendMessageToServer("port#");
		} else {
			$port = $killResult;
		}

		$readDeployCodeFile = fopen(__DEPLOY_CODE_FILE__, "r");
		$deployCode = fread($readDeployCodeFile, __DEPLOYCODE_CONTEXT_LENGTH__);
		fclose($readDeployCodeFile);
		$deployCode = str_replace('?IMPORTCLASSES?', $this->importCode, $deployCode);
		$deployCode = str_replace('?DEPLOYFILENAME?', $classNamePair, $deployCode);
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

		if(($compileResult = sendMessageToServer("java#javac $fileName#$port")) == __SUCCESS__) {
			if(__FAILED__ !== sendMessageToServer("java#java $classNamePair#$port")){
				if(__FAILED__ !== sendMessageToServer("save#"
				. $implementClassHandler->getClassFullName() . "#" . "$port#$classNamePair#" . $this->serviceLabel)) {
					$this->addDeployedClass($classNamePair, $port);
				}
			}
		} else {
			exitWithErrorCode('1003', $fileName);
		}
	}

	// 依次部署每个Java服务
	public function deployCode() {
		$syncResult = CodeSync::getInstance()->getSyncResult();
		foreach($syncResult as $classFullName => $classHandler) {
			if($classHandler->isInterface) {
				foreach($classHandler->getImplementClassHandler() as $key => $implementClassHandler) {
					$this->setImportCode($classHandler->getClassFullName(), $implementClassHandler->getClassFullName());
					$this->writeDeployCode($classHandler, $implementClassHandler);
				}
			} else if($classHandler->isAbstractClass) {
				continue;
			}
		}
	}

	public function setDeployClasses($syncResult) {
		$this->deployClasses = $syncResult;
	}

	public function setImportCode($interfaceClassName, $implementClassName) {
		$this->importCode = "import $interfaceClassName;\nimport $implementClassName;";
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
$deployModule->deployCode();
exit(json_encode($deployModule->getDeployResult()));
?>