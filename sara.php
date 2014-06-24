<?php
include 'config/config.php';
include 'mongo_driver/db.php';
/**
 * Description of sara
 *
 * @author hadock
 */
include BASEPATH.'brain.php';
include BASEPATH.'ears.php';
include BASEPATH.'hands.php';
include BASEPATH.'mouth.php';

class sara {
    protected $brain;
    protected $ears;
    protected $hands;
    protected $mouth;
    
    public function __construct($lang) {
        $this->brain = new brain($lang);
        $this->ears = new ears();
        $this->hands = new hands();
        $this->mouth = new mouth();
    }
    
    public function start($command_text){
        $result = $this->ears->listen($command_text);
        $do = $this->brain->process_command($result);
        switch ($do->result) {
            case "execute":
                //la boca debe hablar en futuro
                echo utf8_decode($do->speak->future), "<br>";
                if($this->mouth->talk($do->speak->future)){
                    //la accion debe ser realizada
                    echo json_encode($do->actions);
                    $this->hands->load_instruccions($do->actions)->execute();
                    //la boca debe hablar en pasado
                    echo "<br>",utf8_decode($do->speak->past), "<br>";
                    $this->mouth->talk($do->speak->past);
                }else{
                    echo "no se pudo generar el habla";
                }
            break;
        
            case "learn":
                $this->mouth->talk($do->speak);
                echo utf8_encode($do->speak), "<br>";
                echo json_encode($do->actions);
            break;
        
            case "knowledge_adquired":
                $this->mouth->talk($do->speak);
                echo utf8_encode($do->speak), "<br>";
            break;

            default:
                echo "no cacho";
            break;
        }
    }
}
$sara = new sara("es");
$sara->start(!isset($_REQUEST["command"])?$_REQUEST["text"]:$_REQUEST["command"]);