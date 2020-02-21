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
	//获取java命令类型
	private function getJavaCommandClass($javaCommand) {
		for($indexA = 0; ' ' == $javaCommand[$indexA]; ++$indexA);
		for($indexB = $indexA; ' ' != $javaCommand[$indexB]; ++$indexB);
		return substr($javaCommand, $indexA, $indexB - $indexA);
	}
	//java类命令处理方法
	private function javaHandler($javaCommand) {
		$javaCommandClass = $this->getJavaCommandClass($javaCommand);
		switch($javaCommandClass) {
			case 'java':
				$pid = pcntl_fork();
				if(0 == $pid) {
					shell_exec($javaCommand);
					exit();
				}
				return true;
			case 'javac':
				// $noWDate = time();
				shell_exec($javaCommand);
				// usleep(200000);
				// $filePath = $this->getClassFilePathFromJavacCommand($javaCommand);
				// $fileTime = filemtime($filePath);
				// while($fileTime == false || $fileTime < $noWDate) {
				// 	usleep(10000);
				// 	if(time() - $noWDate > 3) {
				// 		return false;
				// 	}
				// 	$fileTime = filemtime($filePath);
				// }
				return true;
			default:
				echo "Unknown Java Command:$javaCommand";
				return false;
		}
	}
	//消息接收
	public function receiveMessage() {
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
						$handlerResult = $this->javaHandler($msgArray[1]);
						if($handlerResult) {
							$msg = "succeed";
						} else {
							$msg = "failed";
						}
					break;
					case 'port':
						$msg = PortManager::getInstance()->findAvailablePort();
					break;
					default:
						$msg = "unknown";
					break;
				}
				if(!socket_write($msgsock, $msg, strlen($msg))) {
					echo "socket_write() failed: reason: " . socket_strerror($msgsock) . "\n";
				}
			}
			socket_close ($msgsock);
		} while (true);
	}
}

$server = ServerManager::getInstance();
$server->initSetting();
$server->receiveMessage();