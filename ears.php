<?php
/**
 * Description of ears
 *
 * @author hadock
 */
class ears {
    
    public function listen($command_text){
        //limpiar commando
        $result = "";
        //quitando espacios demas entre palabras
        $result = preg_replace('/\s\s+/', ' ', $command_text);
        //quitando espacios laterales
        $result = trim($result);
        //si viene de google speech, remuevo las comas pegadas a las letras
        $result = implode(" @ ",explode(",", $result));
        
        $result = preg_replace('/\s\s+/', ' ', $result);
        
        return $result;
    }
}
