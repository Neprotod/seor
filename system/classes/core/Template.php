<?php defined('SYSPATH') OR exit();
/**
 * Для подключения тем.
 *
 * @package    Tree
 * @category   Core
 */
class Core_Template{
    /**
     * @var string имя темы 
     */
    public $template;
    /**
     * @var string файл темы
     */
    public $file;
    /**
     * @var string расширение темы
     */
    public $ext;
    
    /**
     * @var string образец Template
     */
    public $view;
    /**
     * Что бы можно было создать темплате
     *
     * @param  string $template ----
     * @param  string $file     ----
     * @param  string $date     ----
     * @param  string $ext      ----
     * @return object Template
     */
    static function factory($template, $file, $date = NULL,$ext = ''){
        $view = new Template($template, $file, $date, $ext);
        return $view->view;
    }
    
    /**
     * Создает template
     *
     * @param  string $template ----
     * @param  string $file     ----
     * @param  string $date     ----
     * @param  string $ext      ----
     * @return object Template
     */
    protected function __construct($template, $file, $date, $ext){
        //Расширение
        $this->ext = $ext;
        
        $this->template = $template;

        $this->file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        $this->view = View::factory($this, NULL, $date);
    }
    /**
     * Проверяет и возвращает файл темы
     *
     * @return string Template
     */
    function template(){
        if(!$file = Core::find_file("template_{$this->template}", $this->file,$this->ext)){
            throw new Core_Exception('Не существует файла <b>:file</b> у темы <b>:template</b>',array(':file'=>$this->file,':template'=>$this->template));
        }
        return $file;
    }
}
