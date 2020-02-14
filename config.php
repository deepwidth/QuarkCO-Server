<?php

/**
 * QuarkCO (Quark Computing Offloading)
 * Date: 2020.2.13
 * Author: Zhang Kangkang
 * Website: https://zkk.me
*/

/**
 * This is the config file of QuarkCO work.
*/

//QuarkCO工作目录
define('__QUARKCO_ROOT_DIR__', dirname(__FILE__));

//存放类的文件夹，在QuarkCO工作目录之下的目录，最后以'/'结束
define('__CLASSES_ROOT_DIR__', 'QuarkCO/');

@set_include_path(get_include_path() . PATH_SEPARATOR .
__QUARKCO_ROOT_DIR__ . '/var');

require_once "codeSync.php";

require_once "portManager.php";

//===============个性化服务设置==================

//请在这里设置 QuarkCO 服务集所能使用端口范围的最小值
$startPort = 2201;

//请在这里设置 QuarkCO 服务集所能使用端口范围的最大值
$endPort = 65535;

/**
 * 请在这里设置 QuarkCO 服务集不可以使用的端口，通常您会将一些其他应用可能会使用的
 * 端口添加在下面
 */
$exceptedPorts = array(
	22, 23, 80,	443, 1080, 3306, 25536
);

$portManager = PortManager::getInstance();
$portManager->setMiniPort($startPort);
$portManager->setMaxPort($endPort);
$portManager->setExceptedPorts($exceptedPorts);

?>
