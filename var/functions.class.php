<?php

/**
 * functions.class.php
 * Date: 2020.2.26
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */

 /**
  * 包含各种通用功能的类
  */

class Functions {

    //写日志
    public static function writeLog($string) {
        echo "写入日志: $string \n";
        if(!file_exists(__LOG_FILE__)) {
            touch(__LOG_FILE__);
        }

        if('\n' != $string[strlen($string) - 1]) {
            $string = $string . "\n";
        }
        $logFile = fopen(__LOG_FILE__, "a");
        $log = "[" . date("Y-m-d h:i:s") . "] " . $string;
        $result = fwrite($logFile, $log);
        fclose($logFile);
        return $result;
    } 
}