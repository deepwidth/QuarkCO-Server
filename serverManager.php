<?php

// Socket举例：简单的TCP/IP服务器
// 改变地址和端口以满足你的设置和执行。
// telnet 192.168.1.53 10000连接到服务器，（这里是你设置的地址和端口）。 //输入任何东西都会在服务器端输出来，然后回显给你。
// 断开连接，请输入'quit'。

require_once "config.php";

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

	//从javac命令中获取编译后的.class文件路径
	private function getClassFilePathFromJavacCommand($javacCommand) {
		$javacPos = strpos($javacCommand, 'javac') + 5;
		while($javacCommand[$javacPos] == ' ') {
			++$javacPos;
		}
		$endPos = strpos($javacCommand, '.java') + 5;
		$javacCommand = str_replace('.java', '.class', $javacCommand);
		return substr($javacCommand, $javacPos, $endPos - $javacPos + 1);
	}

	private function addDeployedClasses($deployedClass) {
		$this->deployedClasses[$deployedClass->getClassFullName()] = $deployedClass;
	}

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
	//java类命令处理方法
	private function javaHandler($javaCommand, $javaCheckPort = 2201) {
		$javaCommandClass = $this->getJavaCommandClass($javaCommand);
		switch($javaCommandClass) {
			case 'java':
				$pid = pcntl_fork();
				$noWDate = time();
				if(0 == $pid) {
					shell_exec($javaCommand);
					exit();
				}
				$pid = shell_exec("lsof -i:$javaCheckPort | grep '(LISTEN)' | awk -F' ' {'print $2'}");
				while(time() - $noWDate < 2 && null == $pid) {
					usleep(1000);
					$pid = shell_exec("lsof -i:$javaCheckPort | grep '(LISTEN)' | awk -F' ' {'print $2'}");
				}
				if($pid != null) {
					return 0;
				}
				return -1;
			case 'javac':
				shell_exec($javaCommand);
				return 0;
			default:
				echo "Unknown Java Command:$javaCommand";
				return -1;
		}
	}

	//保存运行的服务的端口和进程号
	private function saveService($commandArray) {
		$deployedClass = new DeployedClass();
		if(strlen($commandArray[1]) <= 0 || $commandArray[2] <= 0) {
			return -1;
		}

		$deployedClass->setClassFullName($commandArray[1]);
		$deployedClass->setPort($commandArray[2]);
		$pid = shell_exec("lsof -i:$commandArray[2] | grep '(LISTEN)' | awk -F' ' {'print $2'}");
		$pid = substr($pid, 0, strlen($pid) - 1);
		$deployedClass->setPid($pid);
		$this->addDeployedClasses($deployedClass);
		var_dump($this->deployedClasses);
		return 0;
	}

	//消息接收
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