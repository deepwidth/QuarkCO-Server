<?php

/**
 * deployedClass.php
 * Date: 2020.2.22
 * Author: Zhang Kangkang
 * Website: https://github.com/twoFiveOneTen/QuarkCO-Server
 */

 /**
  * deployedClass类用来代表被部署并在运行的java服务类
  */

class DeployedClass {
    private $classFullName;
    private $port;
    private $pid;
    private $toolFileName;

    public function setClassFullName($classFullName) {
        $this->classFullName = $classFullName;
    }

    public function getClassFullName() {
        return $this->classFullName;
    }

    public function setPort($port) {
        $this->port = $port;
    }

    public function getPort() {
        return $this->port;
    }

    public function setPid($pid) {
        $this->pid = $pid;
    }

    public function getPid() {
        return $this->pid;
    }

    public function setToolFileName($toolFile) {
        $this->toolFileName = $toolFile;
    }

    public function getToolFileName() {
        return $this->toolFileName;
    }
}
?>