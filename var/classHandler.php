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
	
	public function setInterface($interfaceClass) {
		$this->interfaceClass = $interfaceClass;
	}
	
	public function isInterfaceClass() {
		return $this->interfaceClass;
	}
	
	public function setImplementClassHandler($implementClassHandler) {
		$this->implementClassHandler = $implementClassHandler;
	}
	
	public function getImplementClassHandler() {
		return $this->implementClassHandler;
	}
}
 
?>