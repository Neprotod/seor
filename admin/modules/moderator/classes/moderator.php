<?php

class Moderator_Admin{
    function user($type = "group"){
        
        Registry::i()->title = "Модераторы";
        $menu = Admin_Model::factory("menu","system");
        
        $permission = Admin_Permission::i()->perm_module("moderator","user");
        
        $menu->attach("moderator_user","group","Группы","");
        $menu->attach("moderator_user","moder","Модераторы","update");
        $menu->attach("moderator_user","delete","Удаление","delete");
        $dop_menu = array(
            "action" => "moderator_user",
            "link" => "delete?group='all'",
            "name" => "Группы",
            "rule" => "delete",
            );
            
        $menu->attach_to("delete",$dop_menu);
        $dop_menu["link"] = "delete?moder='all'";
        $dop_menu["name"] = "Модераторы";
        $menu->attach_to("delete",$dop_menu);

        $array_menu = $menu->get_array();
        
        $data["menu"] = $menu->get($permission,"group");
        
        // Подключаем модель поведения.
        $model_name = "user_" . $type;
        $model = Admin_Model::factory($model_name,"moderator");
        
        $data["content"] = $model->get();
        
        return Admin_Template::factory(Registry::i()->template,"content_moderator_user",$data);
    }
}