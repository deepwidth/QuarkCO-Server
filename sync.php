<?php

/**
 * sync.php
 * Date: 2020.2.13
 * Author: Zhang Kangkang
 * Website: https://zkk.me
 */
 
require_once "config.php";

$param = json_decode(@$_POST['param']);

$syncedClasses = CodeSync::getInstance();
$syncedClasses->sync($param);
?>
