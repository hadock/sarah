<?php
include BASEPATH.'mongo_driver/mongodb_lib.php';
class db extends mongodb_lib{
    protected $config_file;
    public function __construct($config_file) {
        $this->config_file = $config_file;
    }
    
    public function load(){
        include $this->config_file;
        parent::load($config["default"]);
    }
}

