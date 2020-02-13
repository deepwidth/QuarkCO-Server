<?php
$post = @$_POST['param'];
$param = json_decode($post);
var_dump($param);
echo $param -> MyKey;
?>
