<?php
include 'sara.php';
exit();
function match_sentences($string){
	$to_analize = explode(",", $string);
        $file = fopen("commands.txt", "w+");
        fwrite($file, $string);
        fclose($file);
        die();
	if(count($to_analize)>1){
		$die = true;
		foreach($to_analize as $line):
			$string = trim(analize_command(strtolower(utf8_decode($line))));
			if($string){
				$file = fopen("commands.txt", "w+");
				fwrite($file, $string);
				fclose($file);
				$die = false;
			}
		endforeach;
		if($die) die();
	}else{
		$string = trim(analize_command(strtolower(utf8_decode($to_analize[0]))));
		if(!$string) die();
		$file = fopen("commands.txt", "w+");
		fwrite($file, $string);
		fclose($file);
	}
        die();
	switch ($string) {
		case "presentate":
			return "Hola, soy sara 1.1 y he sido creada para automatizar hogares con commandos de voz";
		break;
	
		case "once apaga":
			$content = file_put_contents("commands.txt", "python lhpins.pyc --action set --pin 11 --status off");
			return "He encendido la luz que está en el suelo";
			
		case "once enciende":
			$content = file_put_contents("commands.txt", "python lhpins.pyc --action set --pin 11 --status on");
			return "He apagado la luz ubicada en el suelo";
		
		case "si":
			return "okey";
			
		case "doce enciende":
			$content = file_put_contents("commands.txt", "python lhpins.pyc --action set --pin 12 --status on");
			return "He encendido la lámpara verde";
			
		case "doce apaga":
			$content = file_put_contents("commands.txt", "python lhpins.pyc --action set --pin 12 --status off");
			return "He apagado la lámpara verde";
			
		case "once y doce apaga":
			$content = file_put_contents("commands.txt", 
										"python lhpins.pyc --action set --pin 11 --status off && "
										."python lhpins.pyc --action set --pin 12 --status off"
										);
			return "he apagado todas las luces conectadas a la extensión";
			
		case "once y doce enciende":
			$content = file_put_contents("commands.txt", 
										"python lhpins.pyc --action set --pin 11 --status on && "
										."python lhpins.pyc --action set --pin 12 --status on"
										);
			return "he encendido solamente las luces conectadas a la extensión";
			
		case "enciende todo":
			$content = file_put_contents("commands.txt", 
										"python lhpins.pyc --action set --pin 11 --status on && "
										."python lhpins.pyc --action set --pin 12 --status on && "
										."python lhpins.pyc --action set --pin 13 --status on"
										);
			return "he encendido todo lo que se encuentra conectado a la extensión";
			
		case "apaga todo":
			$content = file_put_contents("commands.txt", 
										"python lhpins.pyc --action set --pin 11 --status off && "
										."python lhpins.pyc --action set --pin 12 --status off && "
										."python lhpins.pyc --action set --pin 13 --status off"
										);
			return "he apagado todo equipo o dispositivo conectado a la extensión";
		
		case "enciende el televisor":
			$content = file_put_contents("executethis", "echo 1 > /sys/devices/virtual/misc/gpio/pin/gpio13");
			return "He encendido el televisor";
		
		case "apaga el televisor":
			$content = file_put_contents("executethis", "echo 0 > /sys/devices/virtual/misc/gpio/pin/gpio13");
			return "He apagado el televisor";
			
		default:
			return "No logré analizar lo que me dijiste, ¿puedes repetirlo?";
			break;
	}
}

function analize_command($voice_command){
	$search = array("zhara", "zarah", "sarah", "shara", "sahara", "zahara", "zara", "sara");
	$command = str_replace($search, "", $voice_command, $count);
	$command = str_replace("á", "a", $command);
	$command = str_replace("é", "e", $command);
	$command = str_replace("í", "i", $command);
	$command = str_replace("ó", "o", $command);
	$command = str_replace("ú", "u", $command);
	
	if($count){
		return $command;
	}else{
		return 0;
	}
}

function is_command($string){
	if(preg_match("//", "hola sara enciende la luz")){
		return true;
	}else{
		return false;
	}
}

function get_specific_command(){
	
}

function parse_xml($xml_body){
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

$url = "http://www.lumenvox.com/products/tts/processTTS.ashx";
$con = curl_init();
if(isset($_REQUEST["text"]) AND $_REQUEST["text"]){
	$msg = $_REQUEST["text"];
	$msg = match_sentences(trim(utf8_encode($msg)));
	$history_file = md5($msg)."mp3";
	if(file_exists($history_file)){
		$audio = file_get_contents($history_file);
		$content = file_put_contents("audio.mp3", $audio);
		header("Location: /");
		exit;
	}
}else{
	exit("No hay mensaje");
}
$post = array("engine" => "2", "msg" => $msg, "voice" => "Rita");
curl_setopt($con, CURLOPT_URL,$url);
curl_setopt($con, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($con, CURLOPT_POST, TRUE);
curl_setopt($con, CURLOPT_POSTFIELDS, $post);

/// DESCOMENTAR LA SIGUIENTE LINEA PARA EJECUTAR
$response = curl_exec($con);
curl_close($con);
//$response = "<xdoc><status><success>true</success><voice>Violeta</voice><path>747777408.mp3</path></status></xdoc>";
$result = parse_xml($response);

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
		$check = parse_xml($response);
	
		

		if(!isset($check["error"]) AND isset($check["success"])){
			if($check["exists"]){
				$mp3_ready = true;
				break;
			}
		}
		
		if($count > 10) break;
		$count++;
		sleep(1);
	}

	if($mp3_ready AND isset($result["path"])){
		$url = "http://www.lumenvox.com/products/tts/audio/{$result["path"]}";
		$audio_data = file_get_contents($url);
		$content = file_put_contents("audio.mp3", $audio_data);	
		$content = file_put_contents($history_file, $audio_data);
		header("Location: /");
	}
}


?>