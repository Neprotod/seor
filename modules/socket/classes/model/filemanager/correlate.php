<?php defined('MODPATH') OR exit();

class Model_Filemanager_Correlate_Socket{

    public $file_correlate = "./system/error/correlate.php";
    
    public $scan_directory = array();
    
    function __construct(){}
    
    function fetch(){
        $this->init();
        if(Request::post('update')){
            $this->file_correlate_set();
        }
        if(($file = $this->file_correlate_get()) != ($scan = $this->scan_dir())){
            $fonds = $this->array_diff($scan,$file);
        }
        $fond = array(
            'fonds' => $fonds
        );
        echo View::factory('filemanager_correlate_correlate','socket',$fond);
    }
    private function init(){
        if(is_file($this->file_correlate)){
            if(!file_get_contents($this->file_correlate)){
                $this->file_correlate_set();
            }
        }else{
            $this->file_correlate_set();
        }
    }
    function scan_dir($dir = '.'){
        $scandir = scandir('.');
        foreach($scandir as $key => &$directory){
            if($directory == '.' OR $directory == '..'){
                unset($scandir[$key]);
                continue;
            }
            if(is_file($directory))
                unset($scandir[$key]);
            else
                $directory .= DIRECTORY_SEPARATOR;
        }
        return Core::list_files('.',$scandir);
    }
    
    function file_correlate_set(){
        $serialize = serialize((array)$this->scan_dir());
        if(empty($serialize))
            return FALSE;
        file_put_contents($this->file_correlate,$serialize);
        return TRUE;
    }
    
    function file_correlate_get(){
        if(!is_file($this->file_correlate))
            return FALSE;
        $get = file_get_contents($this->file_correlate);
        if(!empty($get))
            return unserialize($get);
    }
    /*
     * @param исходный массив 
     * @param массив для сравнения
     */
    function array_diff($array1,$array2){
        if(!is_array($array1) OR !is_array($array2))
            return FALSE;
        if($array1 == $array2)
            return array();
        $fond = array();
        for ($i = 0, $total = func_num_args(); $i < $total; $i++){
            if($i == 0){
                $arr1 = func_get_arg($i);
                $arr2 = func_get_arg($i+1);
            }else{
                $arr1 = func_get_arg($i);
                $arr2 = func_get_arg($i-1);
            }
            $fond[] = $this->array_diff_foreach($arr1,$arr2,$i);
        }
        return Arr::merge($fond[0],$fond[1]);
    }
    // Техническая функция для array_diff
    private function array_diff_foreach($array1,$array2,$i = 0){
        // Для результата
        $fond = array();
        $first = $array1;
        $last = $array2;
        if($i == 0){
            $action = 'Добавлено';
        }else{
            $action = 'Удалено';
        }
        foreach($first as $key => $value){
            if(isset($last[$key])){
                if($first[$key] != $last[$key]){
                    if(is_array($first[$key])){
                        $fond[$key] = $this->array_diff_foreach($first[$key],$last[$key],$i);
                    }else{
                        $fond[$key] = $first[$key];
                    }
                }
            }else{
                $fond[$action][$key] = $first[$key];
            }
        }
        return $fond;
    }
}
