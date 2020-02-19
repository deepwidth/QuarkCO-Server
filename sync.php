<?php

/**
 * sync.php
 * Date: 2020.2.13
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */
 
require_once "config.php";

class Sync {

	public function getPost() {
		if(@$_POST['quarkco']) {
			return json_decode(@$_POST['quarkco']);
		} else {
			echo "1001";
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
