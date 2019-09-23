<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_employer_Account_User{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->method = Controller::factory("method","user");
        $this->account = Controller::factory("account","user");
        $this->support = Controller::factory("support","user");
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Model::factory("sql","system");
    }
    
    
    function ads(){
        $this->error->cookie(FALSE);
        
        
        Registry::i()->data["file_name"] = "ads";

        if($ads = Request::get("ad")){
            return $this->create_ads($ads);
        }
        $xml_pars = "template|default::user_account_ads";
        $xsl_pars = "template|default::user_account_emploer_ads";
        
        $user = Registry::i()->user;
        
        // Незаполненный аккаунт переадресуется на заполнение с предупреждением.
        if($user["complete"] == 0){
            $this->error->set("error","warning",array("message" => 'В начале нужно заполнить профиль.'));
            
            $this->error->save_cookie();
            
            Request::redirect(Url::site("account/edit", TRUE));
        }
        
        if($user["days"] == 0){
            $this->error->set("error","danger",array("message" => 'Период обслуживания аккаунта окончен, все объявления не активны, <a href="/account/pay">продлите аккаунт</a>.'));
        }
        
        $data = array();
        
        $data["user"] = $user;
        
        $data["currency_name"] = Query::i()->sql("currency.name",array(),"id");
        unset($data["currency_name"][1]);
        
        $mark = strtolower(Request::get("mark",NULL, "all"));
        
        $where = '';
        $order = ' ORDER BY a.time DESC';
        
        switch($mark){
            case 'all': 
                $where = ' AND a.status <> 3';
                $order = ' ORDER BY a.approved = 2 DESC, a.approved = 0 DESC, a.time DESC';
                break; 
            case 'active': 
                $where = ' AND a.status = 1 AND a.approved = 1';
                break;  
            case 'disable': 
                $where = ' AND a.status = 0 AND a.approved = 1';
                break;  
            case 'moder': 
                $where = ' AND a.approved <> 1 AND a.status <> 3';
                $order = ' ORDER BY a.approved DESC, a.time_create';
                break;  
            case 'draft': 
                $where = ' AND a.status = 3';
                break; 
            default:
                $where = ' AND a.status <> 3';
                $order = ' ORDER BY a.approved = 2 DESC, a.approved = 0 DESC, a.time DESC';
        }
        $where .= " AND a.status <> 2";
        $where .= $order;
        $data["ads"] = Query::i()->sql("ads.get_user",array(
                                                        ":id" => $user["id"],
                                                        ":where" => $where 
                                                    ));
                                                    
        $data["price"] = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
        
        $data["count"] = Query::i()->sql("ads.count",array(":id_user" => $user["id"]));

        // Ошибки
        $error = $this->error->output();
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => $mark,
            array("error" => $error)
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    protected function create_ads($id){
        Registry::i()->data["file_name"] = "ad";
        
        $xml_pars = "template|default::user_account_ad";
        $xsl_pars = "template|default::user_account_emploer_ad";
        
        $user = Registry::i()->user;
        
        $data = array();
        
        $data["user"] = $user;
        $data["currency_name"] = Query::i()->sql("currency.name",array(),"id");
        unset($data["currency_name"][1]);
        
        $data["price"] = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
        
        try{
            if($id != 'new'){
                $data["ads"] = Query::i()->sql("ads.get",array(
                                                                ":id" => $id 
                                                            ),NULL,TRUE);
                
                $data["ads_language"] = Query::i()->sql("ads.language",array(
                                                                ":id" => $id 
                                                            ),"id_language");
                                                            
                $data["ads_specialization"] = Query::i()->sql("ads.specialization",array(
                                                                ":id" => $id 
                                                            ),"id_specialization");
                // Берем заметки
                $data["note"] = Query::i()->sql("note.ads",array(
                                                                ":id_ads" => $id 
                                                            ));
                if($data["ads"]["status"] != 3){
                    if($user["days"] == 0){
                        $this->error->set("error","danger",array("message" => 'Период обслуживания аккаунта окончен, вы не можете публиковать объявления, <a href="/account/pay">продлите аккаунт</a>.'));
                    }
                }
            }else{
                if($user["days"] == 0){
                    $this->error->set("error","danger",array("message" => 'Период обслуживания аккаунта окончен, вы можете сохранить только в черновик, <a href="/account/pay">продлите аккаунт</a>.'));
                }
            }
        }catch(Exception $e){
            // Нужна ли обработка?
        }
        
        // Обрабатываем запрос
        if(Request::method("post")){
            $this->error->clear();
            $this->account->ads_post($id, $data);
        }
        
        // Ошибки
        $error = $this->error->output();
        
        $lang_id = Registry::i()->user_language["id"];
        
        // Берем страны
        $country = Query::i()->sql("country.get",array(":lang_code"=>$lang_id));
        $data["country"] = $country;
        
        // Берем все языки
        $language = Query::i()->sql("lang.get",array(":lang_code"=>$lang_id));
        $data["language"] = $language;
        
        // Берем все специализации
        $spec = Query::i()->sql("spec.get",array(":lang_code"=>$lang_id));
        $data["specialization"] = $spec;
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => Request::get("mark",NULL, "all"),
            array("error" => $error)
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    function support($method = "fetch"){
        $pars = Model::factory("route","system")->parse_url(Registry::i()->founds["url"]);

        if($pars["param"]){
            array_shift($pars["param"]);
        }
        return Model::load("support","user",$method,$pars["param"]);
    }
    // Пишем настройки.
    function settings($method = "fetch"){
         
        Registry::i()->data["file_name"] = "settings";
         if($arg = func_get_args()){
            unset($arg[0]);
        }

        return Model::load("settings","user",$method,$arg);
    }
}