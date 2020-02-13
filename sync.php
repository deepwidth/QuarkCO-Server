<?php

/**
 * sync.php
 * Date:2020.2.13
 * Author: Zhang Kangkang
 */
 
require_once "config.php";
$post = @$_POST['param'];
$param = json_decode($post);
$syncedClasses = CodeSync::getInstance();
$syncedClasses->sync($param);
?>
