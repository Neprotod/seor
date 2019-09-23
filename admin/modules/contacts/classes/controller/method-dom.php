<?php defined('MODPATH') OR exit();

/*
 * ћодель определ¤ет к какому типу относитс¤ URL  
 */
class Controller_Method_Contacts_Admin{
    
    /**
     * @var object для работы с XML
     */
    public $xml;
    
    function __construct(){
        $this->xml = Module::factory('xml',TRUE);
    }
    
    /**
     * 
     * 
     * 
     */
    function add_appeal($xml_path){

        $dom = new DOMDocument();
        //Загружаем XML
        $dom->load($xml_path);
        $dom->encoding = 'UTF-8';
        
        $root = $dom->documentElement;
        
        $xpath = new DOMXPath($dom);
        
        //Осноыные данные для вставки
        $date = array();
        
        //Тип как пожелание или проблема
        $date['type'] = $_POST['type'];
        $date['title'] = $_POST['title'];
        $date['message'] = $_POST['message'];
        //Нужен для идентификации кто писал.
        $date['for'] = 'client';
        $date['date'] = time();
        
        //Находим все обращения
        $appeal = $xpath->query("appeal");
        
        //переменная хранит id обращения
        $id = NULL;
        
        //Если клиент не заметил, что он уже писал с таким же заголовком.
        if($appeal->length > 0){
            foreach($appeal AS $app){
                if($app->getAttribute('title') == $date['title'] AND $app->getAttribute('type') == $date['type']){
                    
                    //Ставим метку, что последним писал клиент
                    $root->setAttribute('new',$date['for']);
                    
                    $id = $app->getAttribute('id');
                    //Ставим метку, что последним писал клиент в это обращение
                    $app->setAttribute('new',$date['for']);
                    
                    //Берем номер последнего сообщения
                    $mess = $xpath->query("appeal[@id = {$id}]/message");
                    $num = $mess->length+1;
                    $date['num'] = $num;

                    $xml = $this->xml->xml_module($date,'contacts','message',TRUE);
                    $f = $dom->createDocumentFragment();
                    $f->appendXML($xml);
                    $app->appendChild($f);
                    break;
                }
            }
        }
        
        //Если нет похожих обращений.
        if(is_null($id)){
            //Ставим метку, что последним писал клиент
            $root->setAttribute('new',$date['for']);
            
            //id последнего обращения.
            $id = $appeal->length+1;
            $date['num'] = 1;
            $date['id'] = $id;
            
            $xml = $this->xml->xml_module($date,'contacts','appeal',TRUE);
            $f = $dom->createDocumentFragment();
            $f->appendXML($xml);
            
            $root->appendChild($f);
        }
        
        $dom->save($xml_path);
    }    
}