<?php

/**
 * QuarkCO (Quark Computing Offloading)
 * Date: 2020.2.13
 * Author: Zhang Kangkang
 * Website: https://github.com/twoFiveOneTen/QuarkCO-Server
*/

/**
 * 这是配置文件
 * 更改配置文件后需要重启管理模块(serverManager.php)以生效
*/

//QuarkCO工作目录
define('__QUARKCO_ROOT_DIR__', dirname(__FILE__));

//部署代码
define('__DEPLOY_CODE_FILE__', 'var/deployCode.txt');

//程序间返回操作成功码
define('__SUCCESS__', "success");
//程序间返回操作失败码
define('__FAILED__', "fail");

@set_include_path(get_include_path() . PATH_SEPARATOR .
__QUARKCO_ROOT_DIR__ . '/var');

//代码同步模块
require_once "codeSync.php";

//端口管理模块
require_once "portManager.php";

//其他模块向管理模块通信功能
require_once "communicateToServer.php";

//Java类处理类
require_once "classHandler.php";

//Java类处理类
require_once "deployedClass.php";

//通用功能类
require_once "functions.php";

//===============个性化服务设置==================

// POST请求参数中包含迁移代码的参数名
// 默认为 quarkco
define("__POST_PARAM_NAME__", "quarkco");

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
	21, 22, 80,	443, 1080, 3306,
	8080, 8081, 25536
);

//存放类的文件夹，最后以'/'结束，默认为QuarkCO/
define('__CLASSES_ROOT_DIR__', 'QuarkCO/');

//临时文件存放文件夹，默认为当前目录下的tmp/
define('__FILE_TEMP__', 'tmp/');

// 服务端运行的日志类型
// 0:不开启日志
// 1：简要日志，仅包含服务的产生与消亡信息
// 2：详细日志，包含服务端运行情况细节
// 默认为开启详细日志
define('__LOG_CLASS__', 2);

//服务端运行的日志文件
define('__LOG_FILE__', 'quarkco.log');

//=============个性化服务设置结束==================

$portManager = PortManager::getInstance();
$portManager->setCommunicatePort($communicatePort);
$portManager->setMiniPort($startPort);
$portManager->setMaxPort($endPort);
$portManager->setExceptedPorts($exceptedPorts);

if(__CLASSES_ROOT_DIR__[strlen(__CLASSES_ROOT_DIR__) - 1] != '/') {
	echo "配置文件错误：'__CLASSES_ROOT_DIR__' 未以 '/' 符号结束，请检查配置文件;\n";
	$error = 1;
}

if(__FILE_TEMP__[strlen(__FILE_TEMP__) - 1] != '/') {
	echo "配置文件错误：'__FILE_TEMP__' 未以 '/' 符号结束，请检查配置文件;\n";
	$error = 1;
}

if(isset($error)) {
	exit();
}

unset($communicatePort);
unset($startPort);
unset($endPort);
unset($exceptedPorts);
?>
