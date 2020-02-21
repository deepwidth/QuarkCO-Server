<?php

require_once "config.php";
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
	private $className;	//类名字
	private $classFilePath;	//文件路径
	private $classContent;	//类代码内容
	private $classWords = array();	//类代码内容词汇数组
	private $classPackage;	//类所在的包

	private $isInterface = false;	//是否是接口类
	private $interfaceClass = null;	//其接口类
	private $implementClassHandler = array();	//其实现类

	private $importedClasses = array();	//所导入的类
	// private $abstractClass;	//是否抽象类
	
	private $splitSymbol = array(' ', "\n", "\t", ',', '(', ')', '[', ']', '{', '}', ';', "\'", '"');

	public function getClassName() {
		return $this->className;
	}

	public function getClassFullName() {
		return $this->classFullName;
	}
	
	public function setClassFullName($classFullName) {
		$this->className =
		substr($classFullName, strrpos($classFullName, '.') + 1);
		$this->classPackage = substr($classFullName, 0, strrpos($classFullName, '.'));
		$this->classFullName = $classFullName;
	}
	
	public function setClassFilePath($classFilePath) {
		$this->classFilePath = $classFilePath;
	}
	
	public function getClasFilePath() {
		return $this->classFilePath;
	}
	
	public function setClassContent($classContent) {
		$classContent = substr($classContent, 0, strlen($classContent) - 1);
		$this->classContent = $classContent;
	}
	
	public function getClassContent() {
		return $this->classContent;
	}
	
	private function setISInterface($isInterface) {
		$this->isInterface = $isInterface;
	}
	
	public function isInterface() {
		return $this->isInterface;
	}
	
	
	public function getImplementClassHandler() {
		return $this->implementClassHandler;
	}
	
	public function getInterfaceClass() {
		return $this->interfaceClass;
	}
	//分析java源代码，并将其以词的形式存储在数组中
	public function analyzeClassContent() {
		$classContent = $this->getClassContent();
		$index = 0;
		for($i = 0; $i < strlen($classContent); ++$i) {
            if($classContent[$i] == '/' && $classContent[1+$i] == '/') {
                while($classContent[$i] != '\n') {
                    ++$i;
                }
                ++$i;
            } else if($classContent[$i] == '/' && $classContent[1+$i] == '*') {
                while(!($classContent[$i-1] == '*' && $classContent[$i] == '/')) {
                    ++$i;
                }   
                ++$i;
            }
            while($i < strlen($classContent) && !in_array($classContent[$i], $this->splitSymbol)) {
                $word[$index] = $classContent[$i];
                ++$index;
                ++$i;
            }
            if($index == 0) continue;
            $index = 0;
			$getted = implode($word);
			array_push($this->classWords, $getted);
            $word = array();
		}
	}
	//设置此java类导入的包
	public function setImportedClasses() {
		$syncResult = CodeSync::getInstance()->getSyncResult();
		for($index = 0; $index <= count($this->classWords); ++$index) {
			if($this->classWords[$index] == "import") {
				++$index;
				$classFullName = $this->classWords[$index];
				if(array_key_exists($classFullName, $syncResult)) {
					$this->importedClasses[$syncResult[$classFullName]->getClassName()] = $syncResult[$classFullName];
				} 
			} else if($this->classWords[$index] == "implements") {
				++$index;
				$this->importedClasses[$this->classWords[$index]] = $syncResult[$this->classPackage . "." . $this->classWords[$index]];
			}
		}
	}
	//根据词数组分析java类关系
	public function analyzeClassWords() {
		$syncResult = CodeSync::getInstance()->getSyncResult();
		for($index = 0; $index <= count($this->classWords); ++$index) {
			if($this->classWords[$index] == "implements") {
				++$index;
				if(array_key_exists($this->classWords[$index], $this->importedClasses)) {
					array_push($this->importedClasses[$this->classWords[$index]]->implementClassHandler, $this);
				}
				$this->interfaceClass = $this->importedClasses[$this->classWords[$index]];
			}

			if($this->classWords[$index] == "interface") {
				++$index;
				$this->isInterface = true;
			}
			//其他关键词为后续完善工作
			// if($this->classWords[$index] == "extents") {
			// 	++$index;
			// 	if(array_key_exists($this->importedClasses, $this->classWords[$index])) {
			// 		array_push($this->importedClasses[$this->classWords[$index]]->implementClassHandler, $this);
			// 	}
			// }
		}
	}
}
 
?>