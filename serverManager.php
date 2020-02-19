<?php

// Socket举例：简单的TCP/IP服务器
// 改变地址和端口以满足你的设置和执行。
// telnet 192.168.1.53 10000连接到服务器，（这里是你设置的地址和端口）。 //输入任何东西都会在服务器端输出来，然后回显给你。
// 断开连接，请输入'quit'。

/* 允许脚本挂起等待连接。 */
set_time_limit ( 0 );

/* 打开绝对隐式输出刷新 */
ob_implicit_flush ();

$address = '127.0.0.1';
$port = PortManager::getInstance()->getCommunicatePort();

/* 产生一个socket，相当于产生一个socket的数据结构 */
if (($sock = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP )) === false) {
    echo "socket_create() 失败的原因是: " . socket_strerror ( socket_last_error () ) . "\n";
}

/* 把socket绑定在一个IP地址和端口上 */
if (socket_bind ( $sock, $address, $port ) === false) {
    echo "socket_bind() 失败的原因是: " . socket_strerror ( socket_last_error ( $sock ) ) . "\n";
}

/* 监听指定socket的所有连接 */
if (socket_listen ( $sock, 5 ) === false) {
    echo "socket_listen() 失败的原因是: " . socket_strerror ( socket_last_error ( $sock ) ) . "\n";
}

do {
    /* 接受一个Socket连接 */
    if (($msgsock = socket_accept ( $sock )) === false) {
        echo "socket_accept() 失败的原因是: " . socket_strerror ( socket_last_error ( $sock ) ) . "\n";
        break;
    }
	
	while($out = socket_read($msgsock, 100)) {
		//echo "接收服务器回传信息成功！\n";
		$msgArray = explode('/', $out);
		if($msgArray[0] == "java") {
			$javaMission = pcntl_fork();
			if(!$javaMission) shell_exec($msgArray[1]);	
		}
	}
//	do {
	//fwrite(STDOUT, "Server:");
	//$mesg = trim(fgets(STDIN));
	/*if(!socket_write($msgsock, $mesg, strlen($mesg))) {
    		echo "socket_write() failed: reason: " . socket_strerror($msgsock) . "\n";
	}
	}while($mesg != 'quit');
    关闭一个socket资源 */
	socket_close ( $msgsock );
} while ( true );

socket_close ( $sock );