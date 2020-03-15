<?php

/**
 * serverManager.php
 * Date: 2020.2.20
 * Author: Zhang Kangkang
 * Website: https://github.com/twoFiveOneTen/QuarkCO-Server
 */

/**
 * 增加服务是否成功杀掉的判断；
 */

require_once "config.php";

/**
 * 管理模块
 */

class ServerManager {
	
	private static $instance = null;
	
	private $address = "127.0.0.1";
	private $port;
	private $socket;

	private $deployedClasses = array();	//正在运行的java远程调用服务
	private function __construct() {}

	private function __clone() {}

	function __destruct(){
		socket_close($this->socket);
	}

	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	//初始化设置
	public function initSetting() {
		$this->address = "127.0.0.1";
		$this->port = PortManager::getInstance()->getCommunicatePort();
		set_time_limit (0);	// 允许脚本挂起等待连接
		ob_implicit_flush();	// 打开绝对隐式输出刷新

		$pid = getmypid();
		echo "管理模块启动，进程号为 $pid \n";
		if(__LOG_CLASS__ == 2) {
			writeLog("管理模块启动，进程号为$pid");
		}

		/* 产生一个socket，相当于产生一个socket的数据结构 */
		if (($this->socket = socket_create (AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
			echo "socket_create() 失败的原因是: " . socket_strerror(socket_last_error()) . "\n";
		}
		/* 把socket绑定在一个IP地址和端口上 */
		if (socket_bind ($this->socket, $this->address, $this->port) === false) {
    		echo "socket_bind() 失败的原因是: " . socket_strerror(socket_last_error($this->socket)) . "\n";
		}
		/* 监听指定socket的所有连接 */
		if (socket_listen ($this->socket, 5) === false) {
   			echo "socket_listen() 失败的原因是: " . socket_strerror(socket_last_error($this->socket) ) . "\n";
		}
	}

	private function addDeployedClasses($deployedClass) {
		$this->deployedClasses[$deployedClass->getClassFullName()] = $deployedClass;
	}

	private function deleteDeployedClass($classFullName) {
		unset($this->deployedClasses[$classFullName]);
	}

	/**
	 * 从已部署服务中寻找某个服务
	 * 
	 * @access private
	 * @param string $classFullName 要寻找的java类的全名
	 * @param string $result 要返回的结果，'all':整个结构体;'port'端口;'pid':进程号;
	 */
	private function findDeployedClasses($classFullName, $result = 'all') {
		if(array_key_exists($classFullName, $this->deployedClasses)) {
			$found = $this->deployedClasses[$classFullName];
		} else {
			return null;
		}
		switch($result) {
			case 'all':
				return $found;
			case 'port':
				return $found->getPort();
			case 'pid':
				return $found->getPid();
			default:
				return $found;
		}
	}

	//获取java命令类型
	private function getJavaCommandClass($javaCommand) {
		for($indexA = 0; ' ' == $javaCommand[$indexA]; ++$indexA);
		for($indexB = $indexA; ' ' != $javaCommand[$indexB]; ++$indexB);
		return substr($javaCommand, $indexA, $indexB - $indexA);
	}

	/**
	 * java类命令处理方法
	 * 
	 * @access private
	 * @param string $javaCommand java命令
	 * @param string $javaCheckPort 服务绑定的端口
	 * @return __FAILED__:失败 __SUCCESS__:成功
	 */
	private function javaHandler($javaCommand, $javaCheckPort) {
		$javaCommandClass = $this->getJavaCommandClass($javaCommand);
		switch($javaCommandClass) {
			case 'java':
				$pid = pcntl_fork();
				$noWDate = time();
				if(0 == $pid) {
					shell_exec($javaCommand);
					if(__LOG_CLASS__ != 0) {
						writeLog("$javaCommand 已成功部署，端口为$javaCheckPort");
					}
					exit();
				}
				$pid = shell_exec("lsof -i:$javaCheckPort | grep '(LISTEN)' | awk -F' ' {'print $2'}");
				while(time() - $noWDate < 2 && null == $pid) {
					usleep(1000);
					$pid = shell_exec("lsof -i:$javaCheckPort | grep '(LISTEN)' | awk -F' ' {'print $2'}");
				}
				if($pid != null) {
					return __SUCCESS__;
				}
				return __FAILED__;
			case 'javac':
				shell_exec($javaCommand);
				return __SUCCESS__;
			default:
				if(__LOG_CLASS__ != 0) {
					writeLog("未知Java命令:$javaCommand");
				}
				return __FAILED__;
		}
	}

	/**
	 * 保存运行的服务的端口和进程号
	 * 
	 * @access private
	 * @param array(string) $commandArray 保存服务的save指令数组
	 * @return __FAILED__:失败 __SUCCESS__:成功
	 */
	private function saveService($commandArray) {
		$deployedClass = new DeployedClass();
		if(strlen($commandArray[1]) <= 0 || $commandArray[2] <= 0) {
			return __FAILED__;
		}

		$deployedClass->setClassFullName($commandArray[1]);
		$deployedClass->setPort($commandArray[2]);
		$pid = shell_exec("lsof -i:$commandArray[2] | grep '(LISTEN)' | awk -F' ' {'print $2'}");
		$pid = substr($pid, 0, strlen($pid) - 1);
		$deployedClass->setPid($pid);
		$this->addDeployedClasses($deployedClass);
		if(__LOG_CLASS__ != 0) {
			writeLog($commandArray[1] . "已部署，端口为$commandArray[2]");
		}
		return __SUCCESS__;
	}

	/**
	 * 杀掉某个服务
	 * 
	 * @access private
	 * @param string $classFUllName 杀掉服务的java类全名
	 * @return __FAILED__:失败 __SUCCESS__:成功
	 * 
	 */
	private function killService($classFullName) {
		$pid = $this->findDeployedClasses($classFullName, "pid");
		if($pid === null) {
			return __FAILED__;
		} else {
			shell_exec("kill $pid");
			$this->deleteDeployedClass($classFullName);
			if(__LOG_CLASS__ != 0) {
				writeLog("$classFullName 已被关闭\n");
			}
			return __SUCCESS__;
		}
	}

	/**
	 * 消息接受方法
	 * 有以下几种消息类型
	 * Java类消息(java#Java Command#port)
	 * * * java#javac tmp/ExampleService_ExampleServiceImpl.java#2201
	 * * * java#java ExampleService_ExampleServiceImpl#2201
	 * port类消息(port#)
	 * * * port#
	 * save类消息(save#classFullName#port)
	 * * * save#me.zkk.kkapp.ExampleServiceImpl#2201
	 */
	public function receiveMessage() {
		echo "Server is working now!\n";
		do {
			/* 接受一个Socket连接 */
			if (($msgsock = socket_accept ($this->socket)) === false) {
				echo "socket_accept() 失败的原因是: " . socket_strerror (socket_last_error($this->socket)) . "\n";
				break;
			}
			
			while($out = socket_read($msgsock, 100)) {
				echo "接受命令：" . $out . "\n";
				//接收服务器回传信息成功
				$msgArray = explode('#', $out);
				switch($msgArray[0]) {
					case 'java':
						$msg = $this->javaHandler($msgArray[1], $msgArray[2]);
					break;
					case 'port':
						$msg = PortManager::getInstance()->findAvailablePort();
					break;
					case 'save':
						$msg = $this->saveService($msgArray);
					break;
					case 'kill':
						$msg = $this->killService($msgArray[1]);
					break;
					default:
						$msg = "unknown";
					break;
				}
				if(!socket_write($msgsock, $msg, strlen($msg))) {
					echo "socket_write() failed: reason: " . socket_strerror($msgsock) . "\n";
				}
			}
			socket_close($msgsock);
		} while(true);
	}
}

$server = ServerManager::getInstance();
$server->initSetting();
$server->receiveMessage();