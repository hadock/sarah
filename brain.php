<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of brain
 *
 * @author hadock
 */
class brain {
	protected $lang;
        protected $db;
        protected $mode;
        
	public function __construct($lang = "es") {
            $this->db = new db(BASEPATH."mongo_driver/mongodb.php");
            $this->db->load();
            $this->lang = $lang;
            $this->set_mode();
	}
        
        protected function remove_connectors($command){
            $connectors = $this->db->select(array("word"))->get_where("connectors",array("lang" => $this->lang))->result();
            foreach($connectors as $conector):
                if(preg_match("/\s{$conector->word}\s/", $command)){
                    if(!isset($list)){$list = "\s{$conector->word}\s";}
                    else{$list.= "|\s{$conector->word}\s";}
                }
            endforeach;
            if(isset($list)){
                $command = preg_replace("/$list/", " ", $command);
            }
            return $command;
        }
        
        protected function remove_strange_characters($command){
            $clean = trim(analize_command(strtolower(utf8_decode($line))));
            $no_chars = str_replace(array(".",","), "", $clean);
            return $no_chars;
        }

        protected function learn($command, $linked_to = false){
            //guardo un registro para un nuevo comando en estado de aprendizaje
            $no_connectors = $this->remove_connectors($command);
            $command_array = explode(" ", $no_connectors);
            $id = $this->db->insert("commands",array("text" => $command_array, "time" => time(), "learning" => true));
            return $id;
        }
        
        protected function search_on_memory($command, $return = "execute"){
            if(!is_array($command)){
                $no_connectors = $this->remove_connectors($command);
                $command_array = explode(" ", $no_connectors);
            }else{
                $command_array = $command;
            }

            $result = $this->db->select(array("_id","execute","text", "alias_to"))->where_in_all("text",$command_array)->get("commands")->result();

            if(!count($result)){
                return false;
            }
            if(isset($result[0]->alias_to)){
               if(!is_array($result[0]->alias_to)){
                   $id = !is_object($result[0]->alias_to)? new MongoId($result[0]->alias_to):new MongoId($result[0]->alias_to->{'$id'}); 
                   $link = $this->db->select(array("text"))
                           ->get_where("commands", array("_id" => $id))
                           ->row();
                   $linked = $this->search_on_memory($link->text, $return);
               }
            }
            
            if(isset($linked)){
                return $linked;
            }
            if(isset($result[0]->execute)){
                return $return == "all"?$result[0]:$result[0]->execute;
            }
        }
        
        public function fetch_actions($execute_list){
            
        }
        
        public function set_mode(){
            $modes = $this->db->select(array("mode"))->get_where("modes",array("status" => true, "lang" => $this->lang))->row();
            $this->mode = isset($modes->mode)? $modes->mode: "assistant";
        }
        
        public function check_equal_magik_word($command){
            $equals = $this->db->select(array("name"))->get_where("linkers", array("lang" => $this->lang))->result();
            $words = array();
            foreach($equals as $magik):
                $words[] = $magik->name;
            endforeach;
            //si $word es cero no hay palabras magicas para aprender a igualar
            if(!count($words)){return false;}
            $count = 0;
            //preparar el string para generar el array
            $command_prepared = str_replace($words, "|", $command, $count);
            //si el commando no tiene palabras magicas para igualar retorno false
            if(!$count){ return false;}
            $posibilities = $this->check_known_probabilities($command_prepared);
            
            //array de palabras que conformaran el alias
            $alias_array = array();
            //accion a linkear
            $link_to = FALSE;
            
            if($posibilities){           
                foreach($posibilities as $phrase):
                    $array_to_link = explode("|", str_replace(" | ", "|", $phrase));
                    $alias_words = explode(" ", $array_to_link[0]);
                    foreach($alias_words as $alias):
                        $alias_array[$alias] = $alias;
                    endforeach;
                    
                    $result = $this->search_on_memory($array_to_link[1], "all");
                    if($result){
                        $link_to = $result;
                    }
                endforeach;
            }else{
                $array_to_link = explode("|", str_replace(" | ", "|", $command_prepared));
                $alias_array = explode(" ", $array_to_link[0]);
                $result = $this->search_on_memory($array_to_link[1], "all");
                if($result){
                    $link_to = $result;
                }
            }
            
            if(count($alias_array) AND $link_to){
                echo "estas palabras voy a aprender: <br>";
                $no_connectors = $this->remove_connectors(implode(" ", $alias_array));
                $learn_array = explode(" ", $no_connectors);
                echo implode(" ", $alias_array);
                echo "<br> a este comando lo voy a asociar: ".print_r($link_to->text, true)."<br>";
                return json_decode(
                        json_encode(
                                array(
                                    "text" => $learn_array, 
                                    "natural_alias" => implode(" ", $alias_array), 
                                    "link_to" => $link_to->_id,
                                    "natural_link"  => implode(", ", $link_to->text)
                        )));
            }else{
                return false;
            }
            
        }
        
        protected function check_known_probabilities($command){
            $posibles_array = explode(" @ ", $command);
            if(count($posibles_array)> 1){
                return $posibles_array;
            }
            return false;
        }
        
        // this is the trigger of everithing
        public function process_command($command){
            //si estamos en modo asistente o normal
            $learn = $this->check_equal_magik_word($command);
            if(!$learn){
                echo 'no debo aprender nada<br>';
            }else{
                $this->db->insert(
                        "commands", 
                        array("text"    => $learn->text, 
                            "lang"      => $this->lang, 
                            "alias_to"  => $learn->link_to,
                            "time"      => time()
                        ));
                $this->mode = "knowledge_adquired";
            }
            if($this->mode == "assistant"){
                $memory = $this->search_on_memory($command);
                if(!$memory){
                    //aprender
                    //solicitar la asociacion a un commando basico predefinido
                    
                    //dejar en modo aprendizaje
                    $speak_response = $this->question_response("unknown_command",$command);
                    return json_decode(
                        json_encode(
                                array(
                                    "result" => "learn",
                                    "actions" => array("learn_command" => $command), 
                                    "speak" => $speak_response
                                    )
                                )
                        );
                }
                
                //transformar el commando en respuesta
                $speak_response = $this->response_replacement($command);
                return json_decode(
                        json_encode(
                                array(
                                    "result" => "execute",
                                    "actions" => $memory, 
                                    "speak" => $speak_response
                                    )
                                )
                        );
            }
            if($this->mode == "learning"){
                
            }
            
            if($this->mode == "knowledge_adquired"){
                if($learn){
                    $speak_response = $this->after_alias_learn_response(
                            "after_learn", 
                            $learn->natural_alias, 
                            $learn->natural_link
                        );
                    
                    return json_decode(
                        json_encode(
                                array(
                                    "result" => $this->mode,
                                    "actions" => array("learn_command" => $speak_response), 
                                    "speak" => $speak_response
                                    )
                                )
                        );
                    
                }
            }
        }
        
        public function question_response($use_case, $command){
            $count = $this->db->count("questions");
            $rand = rand(0, $count-1);
            $result = $this->db->select(array("question"))
                    ->limit(-1)
                    ->offset($rand)
                    ->get_where("questions", array("use_case" => $use_case, "lang" => $this->lang))
                    ->row();
            $with_command = str_replace("{command}", $command, $result->question);
            $with_name = str_replace("{name}", "", $with_command);
            
            return $with_name;
        }
        
        public function after_alias_learn_response($use_case, $alias, $command){
            $count = $this->db->count("afirmative_answers");
            $rand = rand(0, $count-1);
            $affirm = $this->db->select(array("phrase"))
                    ->limit(-1)
                    ->offset($rand)
                    ->get_where("afirmative_answers", array("use_case" => $use_case, "lang" => $this->lang))
                    ->row();
            
           $count = $this->db->count("linkers");
           $rand = rand(0, $count-1); 
           $linker = $this->db->select(array("name"))
                    ->limit(-1)
                    ->offset($rand)
                    ->get_where("linkers", array("lang" => $this->lang))
                    ->row();
            return $affirm->phrase .", $alias ". $linker->name. ", $command";
        }
        
        public function response_replacement($command, $id = NULL, $linked_from = NULL){
            if(!$id){$condition = array("lang" => $this->lang);}
            else{ $condition = array("lang" => $this->lang, "_id" => new MongoId($id));}
            $replacement = $this->db->select(array("_id","word", "translate","alias_to"))
                    ->where_ne("word","")
                    ->get_where("verbs", $condition)->result();
            $result = array("present" => "","past" => "","future" => "");
            foreach ($replacement as $replace):
                if(preg_match("/{$replace->word}/", $command) OR $id != NULL){
                    if($linked_from){
                        $from = $this->db
                                ->select(array("word"))
                                ->get_where("verbs", array("_id" => new MongoId($linked_from)))->row();
                    }
                    if(isset($replace->alias_to)){
                        return $this->response_replacement($command,$replace->alias_to,$replace->_id->{"\$id"});
                    }
                    $word = isset($from->word)?$from->word:$replace->word;
                    $result["present"] = preg_replace("/{$word}/", $replace->translate->present, $command);
                    $result["past"] = preg_replace("/{$word}/", $replace->translate->past, $command);
                    $result["future"] = preg_replace("/{$word}/", $replace->translate->future, $command);
                }
            endforeach;
            if($result["present"] == "" OR $result["past"] == "" OR $result["future"] == ""){
                $replacement = $this->db->select(array("word", "translate"))
                    ->get_where("verbs", array("lang" => $this->lang, "default" => true))
                    ->row();
                $result = array(
                    "present"   => $replacement->translate->present,
                    "past"      => $replacement->translate->past,
                    "future"    => $replacement->translate->future
                );
            }
            return $result;
        }
}
