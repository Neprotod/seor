<?php defined('SYSPATH') OR exit();

/**
 * ������� ������� ������ � ��������� ��.
 *
 * @package   Tree
 * @category  Core
 */
 class Core_Direct{
    /**
     * @var string ���� � ����������
     * @static
     */
    public static $dir = 'direct';
    
    /*********methods***********/
    
    /**
     * ���������� ��� ���������� ���������
     *
     * @param   array $directory ���������� ���� ��� ����� ��������� ��������
     * @param   bool  $cache     ������������ �������?
     * @return  array  
     */
    static function directory_out(array $directory,$cache = FALSE){
        $found = array();
        // ������������ ��?
        if($cache !== TRUE){
            foreach($directory as $dir){
                // ���������� ���� �� � ����� ����
                $int_str = mb_strlen($dir);
                if(mb_substr($dir,$int_str - 1,$int_str) == DIRECTORY_SEPARATOR)
                    $dir = mb_substr($dir,0,-1);
                    
                // ��������� ����������
                if($d = @opendir($dir)){
                    while($file = readdir($d)){
                        // ������ �������� ��������
                        if($file == '.' OR $file == '..')
                            continue;
                        if(is_dir($dir.DIRECTORY_SEPARATOR.$file)){
                            $found[] = $dir.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR;
                        }
                    }
                }
            }
            /*������ �������� �����������*/
            Direct::cache_direct($found,$directory);
        }else{
            $found = Direct::get_cache($directory);
        }
        
        
        return $found;
    }
    
    /*
     * ���������� ������������� ������
     * 
     * @param array ������ ������� ����� ������������.
     * @param array ������ �������� ����� ������� ���.
     */
    protected static function cache_direct(array $array,$hesh = FALSE ){
        if($hesh != FALSE){
            $array_string = serialize($array);
            $hesh = md5(serialize($hesh));
        }else{
            $array_string = serialize($array);
            $hesh = md5($array_string);
        }
        file_put_contents(SYSPATH.'config/'.Direct::$dir.'/'.$hesh.'.cache',$array_string);
    }
    /*
     * ������ ������������� ������
     * 
     * @param array ������ ������� ����� �������.
     */
    protected static function get_cache($array){
        $array_string = serialize($array);
        $hesh = md5($array_string);
        if($string = @file_get_contents(SYSPATH.'config/'.Direct::$dir.'/'.$hesh.'.cache')){
            return unserialize($string);
        }else{
            return Direct::directory_out($array);
        }
    }
 }