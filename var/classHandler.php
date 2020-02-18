<?php
	
/**
 * classHandler.php
 * Date: 2020.2.14
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */
 
 /**
  * 此类代表了同步过来的 Java 代码中的类，每一个实例都包含了
  * 一个 Java 类的各种信息，方便同步模块与部署模块进行协同工作
  */
  
class ClassHandler {
	
	private $classFullName;	//类完整名字，包括包名
	private $classFilePath;	//文件路径
	private $classContent;	//类代码内容
	
	private $interfaceClass;	//是否是声明类
	private $implementClassHandler;	//其声明类
	
	private $compiled;	//是否已经编译
	private $compileNeedClasses = array();	//编译此java类所需要的java类
	
	public function getClassFullName() {
		return $this->classFullName;
	}
	
	public function setClassFullName($classFullName) {
		$this->classFullName = $classFullName;
	}
	
	public function setClassFilePath($classFilePath) {
		$this->classFilePath = $classFilePath;
	}
	
	public function getClasFilePath() {
		return $this->classFilePath;
	}
	
	public function setClassContent($classContent) {
		$this->classContent = $classContent;
	}
	
	public function getClasContent() {
		return $this->classContent;
	}
	
	private function setInterface($interfaceClass) {
		$this->interfaceClass = $interfaceClass;
	}
	
	public function isInterfaceClass() {
		return $this->interfaceClass;
	}
	
	private function setImplementClassHandler($implementClassHandler) {
		$this->implementClassHandler = $implementClassHandler;
	}
	
	public function getImplementClassHandler() {
		return $this->implementClassHandler;
	}
	//设置是否已编译
	public function setCompiled($compiled) {
		$this->compiled = $compiled;
	}
	//是否可以进行编译
	public function canCompile() {
		foreach($this->compileNeedClasses as $key => $classHandler) {
			if(!$classHandler->isCompiled()) {
				return false;
			}
		}
		return true;
	}
	//是否已经编译
	public function isCompiled() {
		return $this->compiled;
	}
	
	private function getNextWord($string, $index) {
		while($string[$index] != ' ') {
			++$index;
		}
		while($string[$index] == ' ' || $string[$index] == '\n') {
			++$index;
		}
		$indexB = 0;
		while($string[$index] != ' ' || $string[$index] != '\n') {
			
		}
	}
	
	public function analyzeClassInformation() {
		$classContent = $this->getClasContent();
		for($i = 0; $i < strlen($classContent); ++i) {
			if($classContent[$i] == '/' && $classContent[1+$i] == '/') {
				while($classContent[$i] != '\n') {
					++$i;
				}
			} else if($classContent[$i] == '/' && $classContent[1+$i] == '*') {
				while(!($classContent[$i] == '*' && $classContent[1+$i] == '/')) {
					++$i;
				}
			}
		}
		$this->setInterface();
		$this->setImplementClassHandler();
	}
}
 
?>