<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of hands
 *
 * @author hadock
 */
class hands {
    private $base_command;
    private $commands_ready;
    public function __construct() {
        $this->base_command = "python ".BASEPATH."lhpins.pyc";
        $this->commands_ready = array();
    }
    
    public function load_instruccions($instructions){
        if(is_array($instructions)){
            foreach ($instructions as $key => $value):
                $this->commands_ready[] = "$this->base_command $value->sys_command";
            endforeach;
        }
        return $this;
    }
    
    public function execute(){
        file_put_contents(BASEPATH."temp_files/commands.do", json_encode($this->commands_ready));
    }
}
