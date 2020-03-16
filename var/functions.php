<?php

/**
 * functions.php
 * Date: 2020.2.26
 * Author: Zhang Kangkang
 * Website: https://github.com/twoFiveOneTen/QuarkCO-Server
 */

/**
 * 写日志
 * 
 * @param string $string 要写入的内容
 * @return int 写入内容的总长度
 */
function writeLog($string) {
    $dir = getPath(__LOG_FILE__);
    if($dir !== false && !is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    if(!file_exists(__LOG_FILE__)) {
        touch(__LOG_FILE__);
    }

    $string = $string . "\n";
    $logFile = fopen(__LOG_FILE__, "a");
    $log = "[" . date("Y-m-d H:i:s") . "] " . $string;
    $result = fwrite($logFile, $log);
    fclose($logFile);
    return $result;
}


/**
 * 判断文件路径字符串是否包含文件夹("a/b.txt")
 * 
 * @param string $string 文件路径字符串
 * @return string/false 包含文件夹路径则返回文件所属目录路径，不是则返回false
 */
function getPath($string) {
    $isPath = strrpos($string, '/');
    if(false === $isPath) {
        return false;
    } else {
        return substr($string, 0, $isPath + 1);
    }
}

/**
 * 与管理模块通信
 * 
 * @param string $string 向管理模块发送的消息
 * @return __FAILED__/string 操作失败或者管理模块返回的消息 
 */
function sendMessageToServer($message) {

    $ip = "127.0.0.1";
    $port = PortManager::getInstance()->getCommunicatePort();
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if(false === $socket) {
        return __FAILED__;
    }
    $connectResult = socket_connect($socket, $ip, $port);
	if (false === $connectResult) {
	    return __FAILED__;
	}
	if(!socket_write($socket, $message, strlen($message))) {
        socket_close($socket);
		return __FAILED__;
	} else {
        $out = socket_read($socket, 5096);
        socket_close($socket);
        if(false === $out) {
            return __FAILED__;
        }
		return $out;
	}
}

/**
 * 检查管理模块是否正常工作
 * 
 * @return bool 是否在正常工作
 */
function isManagerWorking() {
    if(__FAILED__ === sendMessageToServer("check#")) {
        return false;
    }
    return true;
}

/**
 * 当服务端发生错误时，通过此方法终止执行，并返回错误码
 * 
 * @param $code 错误码
 * 错误码类别：
 * *1001: 未发现Post请求参数
 * *1002: 服务端未运行
 */
function exitWithErrorCode($code, $errorContent = "Unknown error") {
    
    switch($code) {
        case '1001':
            $error = array("errorCode" => $code, "errorContent" => "Post param Not Found!");
        break;
        case '1002':
            $error = array("errorCode" => $code, "errorContent" => "Server is not working!");
        break;
        case '1003':
            $error = array("errorCode" => $code, "errorContent" => $errorContent);
        break;
        default:
            $error = array("errorCode" => $code, "errorContent" => "Unknown error");
        break;
    }
    exit(json_encode($error));
}

/**
 * 处理shell_exec指令为方便检测执行成功与否的形式
 * 执行命令成功返回success
 * 
 * @param $shellCmd shell指令
 * @return $shellCmd 处理后的shell指令
 */
function changeShellCommand($shellCmd) {
    $shellCmd = $shellCmd . " >/dev/null 2>&1 && echo success";
    return $shellCmd;
}