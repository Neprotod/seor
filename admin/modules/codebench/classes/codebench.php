<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Codebench — A benchmarking module.
 *
 * @package    Kohana/Codebench
 * @category   Controllers
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Codebench_Admin{

    // The codebench view
    public $template = 'codebench';
    
    function __construct(){
        
    }
    public function fetch(){
        Request::redirect(Url::root(false)."/action");
    }
    public function action($class = null){
        // Подключаем абстрактный класс.
        Admin_Module::abstracts("codebench","codebench");
        if (isset($_POST['class'])){
            Request::redirect(URL::site("codebench/action/".trim($_POST['class']),TRUE));
        }
        
        $_GET["return"] = 1;
        $data = array();
        
        $data["class"] = $class;
        
        if($class){
            $path = Admin_Module::mod_path("codebench") . "classes".DIRECTORY_SEPARATOR;
            $path .= "bench".DIRECTORY_SEPARATOR;
            $path .= str_replace("_", DIRECTORY_SEPARATOR, $class) . EXT;
            if(is_file($path)){
                Core::load($path);
                $class = "Bench_".$class;
                $class = new $class;
                $data["codebench"] = $class->run();
            }
        }
        
       echo Admin_View::factory("codebench","codebench",$data);
        /*
        echo Admin_View::factory("login","auth",array("root"=>$root,"error"=>$this->error));
        // Convert submitted class name to URI segment
        if (isset($_POST['class']))
        {
            $this->request->redirect('codebench/'.trim($_POST['class']));
        }

        // Pass the class name on to the view
        $this->template->class = (string) $class;

        // Try to load the class, then run it
        if (Kohana::auto_load($class) === TRUE)
        {
            $codebench = new $class;
            $this->template->codebench = $codebench->run();
        }*/
    }
}
