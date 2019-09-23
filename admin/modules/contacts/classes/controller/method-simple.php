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
        /*
        $dom = new DOMDocument();
        $xpath = new DOMXPath();
        $dom->load($xml_path);
        */
        
        $date = array();
        $date['type'] = $_POST['type'];
        $date['title'] = $_POST['title'];
        $date['message'] = $_POST['message'];
        $date['for'] = 'client';
        $date['date'] = time();
        
        
        $simple = simplexml_load_file($xml_path);
        
        $appeal = $simple->xpath("appeal");
        
        $id = NULL;
        if(!empty($appeal)){
            foreach($appeal AS $app){
                if($app['title'] == $date['title'] AND $app['type'] == $date['type']){
                    $id = $app['id'];
                    $app['new'] = $date['for'];
                    
                    $message = $app->addChild('message',$date['message']);
                    $message->addAttribute('for',$date['for']);
                    $message->addAttribute('date',$date['date']);
                    $message->addAttribute('num',$app->count());
                    break;
                }
            }
        }
        if(is_null($id)){
            $app = $simple->addChild('appeal');
            $app->addAttribute('title',$date['title']);
            $app->addAttribute('type',$date['type']);
            $app->addAttribute('date',$date['date']);
            $app->addAttribute('id',$simple->count());
            
            $message =  $app->addChild('message',$date['message']);
            $message->addAttribute('for',$date['for']);
            $message->addAttribute('date',$date['date']);
            
            $id = $app->count();
            $message->addAttribute('num',$id);
        }
        $appeal = $simple->xpath("appeal");
        echo '<pre>';
        echo htmlspecialchars();
        echo '</pre>';
    
    }    
}