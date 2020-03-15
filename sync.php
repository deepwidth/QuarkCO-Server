<?php

/**
 * sync.php
 * Date: 2020.2.13
 * Author: Zhang Kangkang
 * Website: https://github.com/twoFiveOneTen/QuarkCO-Server
 */
 
require_once "config.php";

class Sync {

	public function getPost() {
		if(@$_POST[__POST_PARAM_NAME__]) {
			if(__LOG_CLASS__ != 0) {
				writeLog("接收到代码同步请求，已发现代码参数");
			}
			return json_decode(@$_POST[__POST_PARAM_NAME__]);
		} else {
			echo "error:1001,未发现post参数";
			exit();
		}
	}

}
$sync = new Sync();
$codes = $sync->getPost();
if(false === isManagerWorking()) {
	exit("error:1002,服务端未启动");
}
$syncedClasses = CodeSync::getInstance();
$syncedClasses->sync($codes);

include "deploy.php";
?>
