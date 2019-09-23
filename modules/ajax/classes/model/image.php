<?php defined('MODPATH') OR exit();

/**
 * Модель определяет к какому типу относится URL  
 * 
 * @package    module/system
 * @category   route
 */
class Model_Image_Ajax{
    
    function fetch(){
       
    }
    
    function logo($coordinate = null){
        $this->method = Controller::factory("method","user");
        $this->dir = Model::factory("filemanager_dir","filesystem");
        
        
        $data = array();
        
        if(Request::post("default")){
            $data["src"] = $this->method->media_user_path()."tmp/".Registry::i()->user["logo"];
            $data["coordinate"] = Registry::i()->session->get("logo_coordinate");
            $data["content"] = View::factory("account_logo","ajax",$data);
            return $data;
        }
        
        if(!$coordinate = Request::post("coordinate")){
            // Создаем TMP файл картинки и возвращаем ее.
            
            //$this->dir->upload_form($this->method->media_user_path() . "tmp","logo.jpg");
            $file = current($_FILES);
            
            $tmp_name = $file['tmp_name'];

            try{
                $img = Image::factory($tmp_name);
                
                if($img->width < 100 OR $img->height < 100){
                    $data["content"] = "Картинка слишком маленькая, минимальный размер это 100x100";
                    return $data;
                }

                if($img->width > 360)
                    $img->resize(360);
                
                if($img->height > 360)
                    $img->resize(NULL,360);
               
                $tmp = $this->method->media_user_path()."tmp";
   
                $this->dir->create_dir($tmp);
                
                $path_info = pathinfo($file['name']);
                
                $tmp .= "/" . UTF8::substr($path_info["filename"],0,50).".".$path_info['extension'];
                
                $img->save($tmp);
                
                unlink($tmp_name);
                
                $data["src"] = $tmp;
                $data["content"] = View::factory("account_logo","ajax",$data);
            }catch(Exception $e){
                Core_Exception::client($e);

                $data["content"] = $e;
            }
        }else{
            if(isset($coordinate["x1"])){
                $coordinate["x"] = $coordinate["x1"];
                $coordinate["y"] = $coordinate["y1"];
            }
            Registry::i()->session->set("logo_coordinate",json_encode($coordinate));
            
            $src = Request::post("src");
            
            $path_parts = pathinfo($src);
            
            $size = isset(Registry::i()->settings["size_user_logo"])
                ? Registry::i()->settings["size_user_logo"]
                : 100;

            $image = $this->crop_resize($src, $coordinate);

            $folder = $this->method->media_user_path()."logo";
            $name = $folder . "/". $path_parts["basename"];
            
            $this->dir->create_dir($folder);

            $image->save($name);
            Module::factory("image", TRUE)->drop_image_db(trim($name,"/"), FALSE);
            /*
            $tmp = $this->method->media_user_path()."tmp";
            $this->dir->unlink(array($tmp=>""));
            */
            Query::i()->sql("update",array(
                                        ":table" => "user",
                                        ":set" => "logo = " . DB::escape($path_parts["basename"]),
                                        ":id" => Registry::i()->user["id"]
                                    ));
            
            $data["content"] = "Логотип сохранен успешно.";
            $data["src"] = "/".$name;
        }
            
        return $data;
    }
    
    protected function crop_resize($src, $coordinate, $widthResize = NULL, $heightResize = NULL){
        $img = Image::factory(trim($src,"/"));
        $test_coordinat = array("x1","y1","x2","y2");

        if($test = Arr::diff_key(array_flip($test_coordinat), $coordinate, TRUE)){
            throw new Core_Exception("Не хватает координат <b>:coordinate</b>",array(":coordinate"=>implode("|",array_keys($test))));
        }
        $coordinate = Arr::intersect_key($coordinate, $test_coordinat);
        
        extract($coordinate);
        
        $width = abs($x2 - $x1);
        $height = abs($y2 - $y1);

        $img->crop($width, $height,$x1,$y1);

        return $img;
    }
}