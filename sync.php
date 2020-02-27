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
		if(@$_POST['quarkco']) {
			if(__LOG_CLASS__ != 0) {
				Functions::writeLog("接收到代码同步请求，已发现代码参数");
			}
			return json_decode(@$_POST['quarkco']);
		} else {
			echo "error:1001,未发现post参数";
			exit();
		}
	}

}
echo '<pre>';
$sync = new Sync();
$codes = $sync->getPost();
$syncedClasses = CodeSync::getInstance();
$syncedClasses->sync($codes);

include "deploy.php";
?>
