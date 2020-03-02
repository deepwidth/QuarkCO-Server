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
function sendMessageToServer($string) {
    $communicate = new CommunicateToServer();
    $result = $communicate->sendMessage($string);
    if(false === $result) {
        return __FAILED__;
    } else {
        return $result;
    };
}