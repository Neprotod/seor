<?php defined('MODPATH') OR exit();
/**
 * Изменяет размер изображений. Записывает в базу данных и удаляет изображения из базы
 * 
 * @package    module
 * @category   image
 */
class Image_Module implements I_Module{

    const VERSION = '1.0.0';
    
    //@var array расширения изображения
    private    $allowed_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
    
    function index($setting = null){}
    
    function __construct(){}
    
    /**
     * Изменяет размер изображения и сохраняет в базу данных путь, для дальнейшего удаления.
     *
     * Массив с настройками должен быть в следующем формате
     *             $params[width]        - изменить ширину картинки
     *             $params[height]       - изменить высоту картинки
     *             $params[resizeWidth]  - обрезать по ширине
     *             $params[resizeHeight] - обрезать по высоте
     *             $params[offSetX]       - центрировать по X координате
     *             $params[offSetY]       - центрировать по Y координате
     *             $params[path]            - если путь отличается от регионального
     *             $params[resizeDir]       - если изменения нужно сохранить в другую папку
     *
     * Массив settings должен обязательно содержать две переменные
     *            $settings[original]   - путь к оригинальным файлам[!]
     *            $settings[resize]     - путь к измененным файлам  [!]
     *            $settings[no_image]   - путь к картинке если нет изображения
     *
     * @param string $img      имя картинки
     * @param array  $params   массив с настройками
     * @param array  $settings начальные настройки если их не указать, сохранятся начальные
     */
    function resize($img,$params = array(),$settings = NULL){
        //Начальные настройки для заменение
        $default = array();
        $default['width']           = NULL;
        $default['height']          = NULL;
        $default['resizeWidth']  = NULL;
        $default['resizeHeight'] = NULL;
        $default['offSetX']      = NULL;
        $default['offSetY']      = NULL;
        $default['path']          = NULL;
        $default['resizeDir']      = NULL;
        
        //Что бы не было ошибки
        if(!is_array($params))
            $params = array();
        
        //Дополняем массив
        $params = Arr::merge($default,$params);
        unset($default);
        
        //Превращаем массив в переменные.
        extract($params);
        
        //Начальные настройки.
        if(empty($settings) OR !is_array($settings)){
            $settings = Registry::i()->settings;
        }else{
            $default_settings = Registry::i()->settings;
            $settings = Arr::merge($default_settings, $settings);
        }

        // Проверки на правильность массива settings
        if(!isset($settings["original"])){
            throw new Core_Exception("В массиве settings нет ячейки original");
        }
        if(!isset($settings["resize"])){
            throw new Core_Exception("В массиве settings нет ячейки resize");
        }
        
        //Если есть пути отличные от оригинальных
        if(isset($path))
            $settings['original'] = $path;
        if(isset($resizeDir)){
            $oldResize = $settings['resize'];
            $settings['resize'] = $resizeDir;
        }
        
        //Проверка обязательных настроек
        if(!isset($settings['original']) OR !isset($settings['resize'])){
            throw new Core_Exception('Нет обязательных настроек original и resize');
        }else{
            $settings['original'] = trim($settings['original'],'/');
            $settings['resize']   = trim($settings['resize'],'/').'/';
            if(isset($oldResize)){
                $oldResize        = trim($oldResize,'/').'/';
            }
            //Если нет картинки по умолчанию
            $settings['no_image'] = (isset($settings['no_image']))
                                        ? $settings['no_image'] 
                                        : NULL;
        }
        
        //Соберем картинку
        try{
            //Соберем путь к файлу.
            $original = $settings['original'] . '/' . $img;
            
            //Проверяем существует ли файл.
            if(!is_file($original)){
                //Если есть связи с этим файлом удаляем их
                $this->drop_image_db($original);

                $original = $settings['no_image'];
                $img = $settings['no_image'];
                $settings['original'] = "no_image";
                if(isset($oldResize))
                    $settings['resize'] = $oldResize;
            }
            
            //Создаем папку для измененных по размеру картинок
            if(!is_dir($settings['resize'])){
                mkdir($settings['resize'],0777,TRUE);
            }
            
            //Разделяем расширение с путем, для дальнейшего дополнения.
            $expImg  = explode('.', $img);
            $imgName = $expImg[0];
            $imgExp  = $expImg[1];
            
            //Создаем правильное имя, для сохранения
            $imgName = '-'.str_replace('/','-', $imgName);
            
            $originalPath = str_replace('/','-', $settings['original']);
    
            //Собираем все координаты для сохранения в имени файла
            $widthRes = '-'.$width;
            $heightRes = '-'.$height;
            
            //Если мы урезаем сохраняем это в имя файла
            $resizeRes = '';
            if(!empty($resizeWidth) OR !empty($resizeHeight)){
                $resizeRes = '-resize';
                
                $resizeRes .= '-' . $resizeWidth . '-' . $resizeHeight;
            }
            
            //Если мы смещаем, сохраняем это в имя файла
            $offSetRes = '';
            if((!empty($offSetX) OR $offSetX == 0) OR (!empty($offSetY) OR $offSetY == 0)){
                $offSetRes = '-offset';

                $offSetRes .= '-' . $offSetX . '-' . $offSetY;
            }
            
            //Полное имя для измененного изображения
            $resizeName = $originalPath . $widthRes . $heightRes. $resizeRes . $offSetRes .$imgName. '.'.$imgExp;
            
            $resizeDir = $settings['resize'] . $resizeName;
            
            //Подключаем если такой файл есть
            if(is_file($resizeDir)){
                return '/'.$resizeDir;
            }
            
            //Образец класса, для изменения изображения
            $imgCore = Image::factory($original);

            //Изменяем размер
            if(!empty($width) OR !empty($height))
                $imgCore->resize($width, $height);

            //Если нет значения на resize заполняем размером картинки
            $resizeWidth  = empty($resizeWidth)? $imgCore->width : $resizeWidth;
            $resizeHeight = empty($resizeHeight)? $imgCore->height : $resizeHeight;
            //Урезаем
            $imgCore->crop($resizeWidth, $resizeHeight, $offSetX, $offSetY);
            
            //Сохраняем
            $imgCore->save($resizeDir);
                
            //Сохраняем в базу данных
            $this->save_image_db($original, $resizeDir);
                
            return '/' . $resizeDir;
        }catch(Exception $e){
            //echo $e->getMessage();
            return '';
        }
    }
    
    /**
     * Возвращает и удаляет с базы данных записи с измененными изображениями
     *
     * [!!!] Настройки должны быть сохранены в Registry::i()->settings;
     *
     * @param  string $file     имя файла который нужно удалить
     * @param  int    $id_table id к которому привязана картинка
     * @param  string $type     тип данных как post, category, page
     * @return bool             TRUE если полностью удалена
     */
    function drop_image($file,$id_table,$type){
        if(isset(Registry::i()->settings) AND isset(Registry::i()->settings['original']))
            $original = trim(Registry::i()->settings['original'],'/') .'/'.$file;
        
        $sql = "DELETE FROM __image
                    WHERE file = :file
                    AND id_type = (SELECT t.id FROM type t WHERE t.type = :type LIMIT 1)
                    AND id_table = :id_table";
                    
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::DELETE, $sql);
        
        //Параметры
        $query->param(':file',$file);
        $query->param(':type',$type);
        $query->param(':id_table',$id_table);
        
        //Проверяем общее число записей, если 0 удаляем файл. И зачищаем записи 
        if($query->execute()){
            $sql = "SELECT COUNT(id) FROM __image
                    WHERE file = :file";
                    
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::SELECT, $sql);
            
            $query->param(':file',$file);
            
            //Если больше записей нет, удаляем файл и зачищаем все его изменения размера
            if($count = $query->execute(NULL,TRUE) AND reset($count) == '0'){
                $this->drop_image_db($original);
            }else{
                return FALSE;
            }
        }
        return TRUE;
    }
    
    /**
     * Водяной знак на изображении
     *
     * [!!]  Настройки должны быть сохранены в Registry::i()->settings;
     * [!!!] Данный метод просто образец
     */
    function watermark(){
        //Образец класса, для изменения изображения
        $imgCore = Image::factory('media/original/image.jpg');
        
        $imgCore->watermark(Image::factory('media/original/mark.jpg'), NULL, NULL, 50);
        
        $imgCore->save('media/original/test.jpg',95);
    }
    
    /**
     * Возвращает и удаляет с базы данных записи с измененными изображениями
     *
     * @param  string путь $original к оригинальному файлу
     * @return array  пути           к измененным изображениям
     */
    function drop_image_db($original, $delete_original = TRUE){
        $sql = "SELECT original, resize FROM __image_resize 
                    WHERE original=:original";
            
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        //Параметры
        $query->param(':original',$original);
        
        // На выдачу
        $result = $query->execute();
        //Удаляем записи
        if(!empty($result)){
            $sql = "DELETE FROM __image_resize 
                        WHERE original=:original";
            $sql = DB::placehold($sql);
        
            $query = DB::query(Database::DELETE, $sql);
            
            //Параметры
            $query->param(':original',$original);
            
            $query->execute();
            if($delete_original)
                $this->delete_image($original);
            
            foreach($result AS $resize){
                $this->delete_image($resize['resize']);
            }
            return $result;
        }
        
        return FALSE;
    }
    
    /**
     * Удаляет файл изображения
     *
     * @param  string путь к файлу
     * @return bool   TRUE если удалился
     */
    function delete_image($image){
        if(is_file($image) AND @unlink($image)){
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Записывает в базу данных путь к измененным по размеру изображениям
     *
     * @param string путь к оригинальному файлу
     * @param string путь к измененному файлу
     * @return void
     */
    protected function save_image_db($original,$resize){
        $sql = "INSERT IGNORE INTO __image_resize SET original=:original, resize=:resize";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        
        //Параметры
        $query->param(':original',$original);
        $query->param(':resize',$resize);
        
        $query->execute();
    }
    
}