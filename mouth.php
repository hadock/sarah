<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mouth
 *
 * @author hadock
 */
class mouth {
    protected $audio_path;
    protected $actor;
    protected $try_count = 0;
    
    public function __construct() {
        $this->audio_path = "/media/LINXUFS/audio_memory/";
        $this->actor = array("engine" => "1", "voice" => "Lorena");
    }
    
    public function talk($phrase){
        if($this->pharse_exist($phrase)){
            //si la frase ya existe, la obtengo de la base de conocimientos
            $audio = file_get_contents($this->audio_path.md5($phrase)."mp3");
            $content = file_put_contents(BASEPATH."audio.mp3", $audio);
            return true;
        }else{
            //si no existe la genero peticiono una nueva frase
            $request = $this->request_audio($phrase);
            //obtengo el audio de esa peticion
            $url = $this->get_audio($request);
            if($url){
                //si resultó exitoso, entonces almaceno el audio en un historial
                $this->store_audio_history($url, $phrase);
                //llamo a este mismo metodo para que tome el audio del historial
                return $this->talk($phrase);
            }else{
                //no hubo exito obteniendo el audio, lo intento en 1 segundo mas
                $this->try_count++;
                sleep(1);
                //si la cantidad de intento fue inferior a 5, entonce lo intento nuevamente
                // sino, ya era... fue bonito mientras duro...
                if($this->try_count < 5){
                    return $this->talk($phrase);
                }else{
                    return false;
                }
            }
        }
    }
    
    protected function request_audio($phrase){
        $this->actor["msg"] = $phrase;
        $post = $this->actor;
        $url = "http://www.lumenvox.com/products/tts/processTTS.ashx";
        $con = curl_init();
        curl_setopt($con, CURLOPT_URL,$url);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($con, CURLOPT_POST, TRUE);
        curl_setopt($con, CURLOPT_POSTFIELDS, $post);

        /// DESCOMENTAR LA SIGUIENTE LINEA PARA EJECUTAR
        $response = curl_exec($con);
        curl_close($con);
        //$response = "<xdoc><status><success>true</success><voice>Violeta</voice><path>747777408.mp3</path></status></xdoc>";
        return $this->parse_xml($response);
    }
    
    protected function get_audio($result){
        if(!isset($result["error"]) AND isset($result["success"])){
	
            $mp3_ready = false;
            $count = 0;
            $url = "http://www.lumenvox.com/products/tts/fileReady.ashx";

            while(!$mp3_ready){

                    $con = curl_init();
                    $post = array("filename" => $result["path"]);
                    curl_setopt($con, CURLOPT_URL,$url);
                    curl_setopt($con, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($con, CURLOPT_POST, TRUE);
                    curl_setopt($con, CURLOPT_POSTFIELDS, $post);
                    ///////////////
                    //descomentar la siguiente linea para ejecutar
                    $response = curl_exec($con);
                    curl_close($con);

                    //$response = "<xdoc><status><success>true</success><exists>1</exists></status></xdoc>";
                    $check = $this->parse_xml($response);



                    if(!isset($check["error"]) AND isset($check["success"])){
                            if($check["exists"]){
                                    $mp3_ready = true;
                                    break;
                            }
                    }

                    if($count > 15 AND !$mp3_ready) {return false;}
                    $count++;
                    usleep(50000);
            }

            if($mp3_ready AND isset($result["path"])){
                    $url = "http://www.lumenvox.com/products/tts/audio/{$result["path"]}";                
                    return $url;
            }
        }
        return false;
    }
    
    protected function store_audio_history($url, $phrase){
        $history_file = $this->audio_path.md5($phrase)."mp3";
        $audio_data = file_get_contents($url);
        $content = file_put_contents(BASEPATH."audio.mp3", $audio_data);	
        $content = file_put_contents($history_file, $audio_data);
        usleep(25000);
    }
    
    protected function pharse_exist($phrase){
        $history_file = $this->audio_path.md5($phrase)."mp3";
	if(file_exists($history_file)){
		//$audio = file_get_contents($history_file);
		//$content = file_put_contents("audio.mp3", $audio);
                return true;
	}
        return false;
    }
    
    protected function parse_xml($xml_body){
        $xml = xml_parser_create();
	$result = array();
	if(xml_parse_into_struct($xml, $xml_body, $values, $index)){
		xml_parser_free($xml);
		if(is_array($values)){
			foreach($values as $level):
				if(is_array($level)){
					switch($level["tag"]){
						case "SUCCESS":
							$result["success"] = $level["value"];
						break;
						case "PATH":
							$result["path"] = $level["value"];
						break;
						case "EXISTS":
							$result["exists"] = $level["value"];
						break;
					}
				}
			endforeach;
		}
	}else{
		return array("error" => "xml not parsed");
	}

	return $result;
    }
}
