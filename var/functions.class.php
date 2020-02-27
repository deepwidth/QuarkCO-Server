<?php

/**
 * functions.class.php
 * Date: 2020.2.26
 * Author: Zhang Kangkang
 * Website: https://github.com/twoFiveOneTen/QuarkCO-Server
 */

 /**
  * 包含各种通用功能的类
  */

class Functions {

    /**
     * 写日志
     * 
     * @access public
     * @param string $string 要写入的内容
     * @return int 写入内容的总长度
     */
    public static function writeLog($string) {
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
}