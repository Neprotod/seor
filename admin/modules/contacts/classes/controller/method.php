<?php defined('MODPATH') OR exit();

/*
 * ћодель определ¤ет к какому типу относитс¤ URL  
 */
class Controller_Method_Contacts_Admin{
    
    /**
     * @var object для работы с XML
     */
    public $xml;
    /**
     * @var object для работы с XML
     */
    public $error;
    /**
     * @var array типы и их противоположности
     */
    public $for = array(
                        'client'=>'developer',
                        'developer'=>'client'
                        );
    
    function __construct(){
        $this->xml = Module::factory('xml',TRUE);
        $this->error = Module::factory('error',TRUE);
    }
    
    /**
     * 
     * 
     * 
     */
    function add_appeal($xml_path,$for = 'client',$array = array()){

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
        $date['title'] = strip_tags($_POST['title']);
        $date['message'] = nl2br(strip_tags($_POST['message'],'<a><b><i>'));
        //Нужен для идентификации кто писал.
        $date['for'] = $for;
        $date['date'] = date("d.m.Y G:i");
        $date = Arr::merge($date,$array);
        
        if(empty($date['title']) OR empty($date['message'])){
            if(empty($date['message']))
                $this->error->set('error','error',array('message'=>'Нельзя отправлять пустое обращение','role'=>'message'));
            if(empty($date['title']))
                $this->error->set('error','error',array('message'=>'Нельзя отправлять обращение без темы','role'=>'title'));
            return;
        }
        
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
                    
                    $last = $xpath->query("(appeal[@id = {$id}]/message)[last()]")->item(0);
                    if(!empty($last) AND $last->hasAttribute('num')){
                        $num = $last->getAttribute('num')+1;
                    }else{
                        //Берем номер последнего сообщения
                        $mess = $xpath->query("count(appeal[@id = {$id}]/message)");
                        $num = $mess->length+1;
                        $date['num'] = $num;
                    }
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
            $last = $xpath->query("appeal[last()]")->item(0);
            if(!empty($last) AND $last->hasAttribute('id')){
                $id = $last->getAttribute('id')+1;
            }else{
                $id = $appeal->length+1;
            }
            $date['num'] = 1;
            $date['id'] = $id;
            
            $xml = $this->xml->xml_module($date,'contacts','appeal',TRUE);
            $f = $dom->createDocumentFragment();
            $f->appendXML($xml);
            
            $root->appendChild($f);
        }
        $return = array();
        $return['save'] = (bool)$dom->save($xml_path);
        $return['id'] = $id;
        
        return $return;
    }    
    /**
     * 
     * 
     * 
     */
    function get($xml_path,$id,$for = 'client'){
        $dom = new DOMDocument();
        $dom->load($xml_path);
        $xpath = new DOMXPath($dom);
        
        $appeal = $xpath->query("appeal[@id = $id]");
        
        $app = $appeal->item(0);

        if(($app->hasAttribute('new') AND $new = $app->getAttribute('new')) AND $new != $for){
            //Ставим метку, что последним писал клиент в это обращение
            $app->setAttribute('new','');
        }
        
        Registry::i()->title = "Тема: " .$app->getAttribute('title');
        
        $xml = $dom->saveXML($app);
        $dom->save($xml_path);
        return $this->xml->xsl_module($xml,'contacts','message',TRUE);
    }    
    /**
     * 
     * 
     * 
     */
    function delete_appeal($xml_path,$id){
        $dom = new DOMDocument();
        $dom->load($xml_path);
        $xpath = new DOMXPath($dom);
        
        $root = $dom->documentElement;
        
        
        
        $appeal = $xpath->query("appeal[@id = {$id}]")->item(0);
        
        if(!empty($appeal)){
            $root->removeChild($appeal);
            return (bool)$dom->save($xml_path);
        }else{
            $this->error->set('error','error',array('message'=>'Обращение уже удалено.'));
        }
        return FALSE;
    }    
    /**
     * 
     * 
     * 
     */
    function get_appeal($xml_path,$for = 'client',$host_set = NULL){
        $dom = new DOMDocument();
        $dom->load($xml_path);
        $xpath = new DOMXPath($dom);
        
        $root = $dom->documentElement;
        
        
        
        $appeal = $xpath->query("appeal");

        if($new = $root->getAttribute('new') AND $new != $for){
            //Ставим метку, что бы убрать индикатор
            $root->setAttribute('new','');
        }
        $dom->save($xml_path);
        
        //Дополняем хост
        $host = $dom->createElement('host');

        $host_set = is_null($host_set)? Url::i()->root():$host_set;
        $host->appendChild($dom->createTextNode($host_set));
        
        $type = $dom->createElement('for');
        $type->appendChild($dom->createTextNode($this->for[$for]));
        
        $root->appendChild($host);
        $root->appendChild($type);
        
        $xml = $dom->saveXML();
        
        return $this->xml->xsl_module($xml,'contacts','appeal',TRUE);
    }    
    /**
     * 
     * 
     * 
     */
    function add_message($xml_path,$id,$for = 'client'){
        $dom = new DOMDocument();
        //Загружаем XML
        $dom->load($xml_path);
        $dom->encoding = 'UTF-8';
        
        $root = $dom->documentElement;
        
        $xpath = new DOMXPath($dom);
        
        
        $appeal = $xpath->query("appeal[@id = $id]");
        
        
        if($app = $appeal->item(0)){
            //Основные данные для вставки
            $date = array();
            
            //Тип как пожелание или проблема
            $date['message'] = nl2br(strip_tags($_POST['message'],'<a><b><i>'));
            
            //Нужен для идентификации кто писал.
            $date['for'] = $for;
            $date['date'] = time();
            
            if(empty($date['message'])){
                $this->error->set('error','error',array('message'=>'Нельзя отправлять пустое обращение'));
                return;
            }
            
            //Ставим метку, что последним писал клиент
            $root->setAttribute('new',$date['for']);
            
            //Ставим метку, что последним писал клиент в это обращение
            $app->setAttribute('new',$date['for']);
            
            $mess = $xpath->query("(appeal[@id = {$id}]/message)[last()]")->item(0);
            $num = $mess->getAttribute('num')+1;
            $date['num'] = $num;
            
            $xml = $this->xml->xml_module($date,'contacts','message',TRUE);
            $f = $dom->createDocumentFragment();
            $f->appendXML($xml);
            $app->appendChild($f);
            
            $return = array();
            $return['save'] = (bool)$dom->save($xml_path);
            $return['id'] = $id;
            
            return $return;
        }
    }    

}