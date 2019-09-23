<?php defined('SYSPATH') OR exit();
/**
 * @package   Tree
 * @category  Crypto
 */
class Core_Crypto_Native{

    /**
     * Создает
     *
     * @param   string строка для кодировки
     * @return  string
     */
    static function set($string){
        if(is_string($string)){
            $bin = unpack("H*",$string);
            $bin = self::coder($bin[1]);
        }
        return $bin;
    }

    /**
     * Возвращает
     *
     * @param   string строка для кодировки
     * @return  string
     */
    static function get($string){
        if(is_string($string)){
            $bin = self::coder($string);
            $bin = pack('H*',$bin);
        }
        return $bin;
    }
    
    /**
     * Кодирует
     *
     * @param   array   строка для кодировки
     * @return  string
     */
    protected static function coder($arr = array()){
        if(!is_array($arr)){
            $leng = Utf8::strlen($arr);
            $fond = new SplFixedArray($leng);
            for($i = 0; $i < $leng; $i++){
                $fond[$i] = $arr{$i};
            }
            $arr = $fond;
        }else{
            $leng = count($arr);
            $arr = SplFixedArray::fromArray($arr);
        }
        
        $newArr = new SplFixedArray($leng);
        $arr->rewind();
        for(;;){
            $first = '';
            $two = '';
            $key_first = $arr->key();
            try{
                $first = $arr->current();
            }catch(Exception $e){
                break;
            }
            $arr->next();
            
            $key_two   = $arr->key();
            try{
                $two   = $arr->current();
            }catch(Exception $e){}
            $arr->next();
            
            if($two == ''){
                $newArr[$key_first] = $first;
                break;
            }else{
                 $newArr[$key_first] = $two;
            }
            $newArr[$key_two] = $first;
            
        }
        return implode($newArr->toArray());
    }
}