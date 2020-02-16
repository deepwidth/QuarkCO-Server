<?php

/**
 * codeSync.php
 * Date: 2020.2.13
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */

 /**
  * 同步代码的模块，主要任务是根据接收的 post 内容
  * 将 Java 代码正确地存放在服务器端
  */
 
class CodeSync {

	private static $instance = null;

	//classMap is used to save all classes to be synced;
	private $classMap = array();
	
	private $syncResult = array();
	
	private function __construct() {}

	private function __clone() {}

	public static function getInstance() {
		if(self::$instance == null) {
			$instance = new self();
		}
		return $instance;
	}
	
	public function getSyncResult() {
		return $this->syncResult;
	}

	private function isCorrectSymbol($char) {
		if($char >= 'a' && $char <= 'z') {
			return true;
		}
		
		if($char >= 'A' && $char <= 'Z') {
			return true;
		}
		
		if($char >= '0' && $char <= '9') {
			return true;
		}
		switch($char) {
			case '-': return true;
			default: return false;
		};
	}

	//判断包的路径是否是正确格式
	private function isCorrectPackagePath($path) {
		for($i = 0; $i < strlen($path); $i = $j + 1) {
			if($path["$i"] == '_') {	//防止第一个字符为'_'或连续出现两个及以上'_'
				break;
			}
			for($j = $i; $path["$j"] != '_'; ++$j) {
				if(strlen($path) == $j) {
					return true;
				}
				if($this->isCorrectSymbol($path["$j"])) {
					continue;
				}
			}
		}
		return false;
	}

	//将post请求中类的文件名转换为路径格式
	private function classNameFormat($oldName) {
		 if(!$this->isCorrectPackagePath($oldName)) {
			return false;
		 }
		 return str_replace('_', '/', $oldName);
	}
	//写文件
	private function writeFile($filePath) {
		$fileToWrite = fopen($filePath, "w");
		if(false == fwrite($fileToWrite, $this->classMap[$filePath])) {
			return false;
		}
		fclose($fileToWrite);
		return true;
	}
	//创建路径和文件
	private function createFile($filePath) {
		$dirPath = substr($filePath, 0, strrpos($filePath, '/'));
		if(!is_dir($dirPath)){
			mkdir($dirPath, 0777, true);
		}
		touch($filePath);	//创建文件
		return $this->writeFile($filePath);	//写入文件，并返回写入结果
	}
	//建立 Java 代码文件
	public function sync($object) {
		foreach($object as  $oldFileName => $fileContent) {
			$this->classMap[__CLASSES_ROOT_DIR__.$this->classNameFormat($oldFileName).".java"] = $fileContent;	//更改classMap索引格式
		}
		foreach($this->classMap as $filePath => $fileContent) {
			if($this->createFile($filePath)) {
				array_push($this->syncResult, $filePath);	//记录成功同步的文件
			}
		}
	}
}
?>
