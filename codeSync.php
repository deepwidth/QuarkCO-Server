<?php


class CodeSync {

	private static $instance = null;

	//classMap is used to save all classes to be synced;
	private $classMap = array();

	private function __construct() {}

	private function __clone() {}

	public static function getInstance() {
		if(self::$instance == null) {
			$instance = new self();
		}
		return $instance;
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
		fwrite($fileToWrite, $this->classMap[$filePath]);
		fclose($fileToWrite);
	}
	//创建路径和文件
	private function createFile($filePath) {
		$dirPath = substr($filePath, 0, strrpos($filePath, '/'));
		if(!is_dir($dirPath)){
			mkdir($dirPath, 0777, true);
		}
		touch($filePath);
		$this->writeFile($filePath);
	}

	public function sync($object) {
		foreach($object as  $key => $value) {
				$this->classMap[$this->classNameFormat(__CLASSES_ROOT_DIR__.$key.".java")] = $value;
		}
		foreach($this->classMap as $classPath => $classContent) {
			$this->createFile($classPath);
		}
		return true;
	}
}
?>
