<?php

error_reporting(E_ALL);
set_time_limit(0);
echo "<h2>TCP/IP Connection</h2>\n";

$port = 10001;
$ip = "127.0.0.1";


$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket < 0) {
    echo "socket_create() 失败的原因是: " . socket_strerror($socket) . "\n";
}else {
    echo "OK.\n";
}

echo "试图连接 '$ip' 端口 '$port'...\n";
$result = socket_connect($socket, $ip, $port);
if ($result < 0) {
    echo "socket_connect() 失败的原因是: ($result) " . socket_strerror($result) . "\n";
}else {
    echo "连接 OK\n";
}

$pid = pcntl_fork();
if($pid == 0) {
	//fwrite(STDOUT, "Client:");
	//$mesg = trim(fgets(STDIN));
	$mesg = "start";
	if(!socket_write($socket, $mesg, strlen($mesg))) {
    		echo "socket_write() failed: reason: " . socket_strerror($socket) . "\n";
	}
}else {
//	while($out = socket_read($socket, 8192)) {
    //echo "接收服务器回传信息成功！\n";
    //echo "\nServer:",$out, "\n";
//	if($out == "quit") exit();
}
//    echo "发送到服务器信息成功！\n";
//    echo "发送的内容为:<font color='red'>$start</font> <br>";

/* while($out = socket_read($socket, 8192)) {
    echo "接收服务器回传信息成功！\n";
    echo "接受的内容为:",$out;
} */


echo "关闭SOCKET...\n";
socket_close($socket);
echo "关闭OK\n";
?>
