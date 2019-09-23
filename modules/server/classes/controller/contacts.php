<?php defined('MODPATH') OR exit();

class Controller_Contacts_Server{
    
    public $error;
    
    function __construct(){
        if(!Server_Module::admin_connect()){
            return FALSE;
        }
        $this->error = Module::factory('error',TRUE);
    }
    function appeal($path){
        $contacts = Admin_Module::factory('contacts',TRUE);
        $file = $contacts->xml_contacts;

        $data = array();
        $data['xml'] = $contacts->method->get_appeal($file,'developer',$path);
        $data['error'] = $this->error->output();
        
        return  $data;
    }
    function get($id){
            $contacts = Admin_Module::factory('contacts',TRUE);
            $file = $contacts->xml_contacts;
            $data = array();
            $data['xml'] = $contacts->method->get($file,$id,'developer');
            $data['error'] = $this->error->output();
            
            return  $data;
    }
    function add_message($id,$post){
            if(!isset($post['message'])){
                return FALSE;
            }
            
            $_POST['message'] = $post['message'];
            $contacts = Admin_Module::factory('contacts',TRUE);
            $file = $contacts->xml_contacts;
            $data = array();
            $data['set'] = $contacts->method->add_message($file,$id,'developer');
            $data['error'] = $this->error->output();
            
            return  $data;
    }
    function delete_appeal($id){
            $contacts = Admin_Module::factory('contacts',TRUE);
            $file = $contacts->xml_contacts;
            $data = array();
            $data['delete'] = $contacts->method->delete_appeal($file,$id);
            $data['error'] = $this->error->output();
            
            return  $data;
    }
}