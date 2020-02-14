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

//代码同步模块
require_once "codeSync.php";

//端口管理模块
require_once "portManager.php";

//其他模块向管理模块通信功能
require_once "communicateToServer.php";

//===============个性化服务设置==================

//模块间通信端口,更改此项设置后重启管理模块(serverManager.php)生效
//默认为2200
$communicatePort = 2200;

//请在这里设置 QuarkCO 服务集所能使用端口范围的最小值
//默认为2201
$startPort = 2201;

//请在这里设置 QuarkCO 服务集所能使用端口范围的最大值
//默认为65535
$endPort = 65535;

/**
 * 请在这里设置 QuarkCO 服务集不可以使用的端口，通常您会将一些其他应用可能会使用的
 * 端口添加在下面,以防止 QuarkCO 服务集占用端口
 */
$exceptedPorts = array(
	22, 23, 80,	443, 1080, 3306,
	8080, 8081, 25536
);

$portManager = PortManager::getInstance();
$portManager->setCommunicatePort($communicatePort);
$portManager->setMiniPort($startPort);
$portManager->setMaxPort($endPort);
$portManager->setExceptedPorts($exceptedPorts);

unset($communicatePort);
unset($startPort);
unset($endPort);
unset($exceptedPorts);
?>
