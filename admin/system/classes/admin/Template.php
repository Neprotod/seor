<?php defined('SYSPATH') OR exit();
/**
 * Для подключения тем.
 *
 * @package    Tree
 * @category   Admin
 */
class Admin_Template{
    /**
     * @var string имя темы 
     */
    public $template;
    /**
     * @var string файл темы
     */
    public $file;
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
     * @return object Template
     */
    static function factory($template, $file, $date = NULL){
        $view = new Admin_Template($template, $file, $date);
        return $view->view;
    }
    /**
     * Создает template
     *
     * @param  string $template ----
     * @param  string $file     ----
     * @param  string $date     ----
     * @return object Template
     */
    protected function __construct($template, $file, $date){
        $this->template = $template;

        $this->file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        
        $this->view = Admin_View::factory($this, NULL, $date);
    }
    
    function template(){
        if(!$file = Admin::find_file("template_{$this->template}", $this->file)){
            throw new Core_Exception('Не существует файла <b>:file</b> у темы <b>:template</b>',array(':file'=>$this->file,':template'=>$this->template));
        }
        
        return $file;
    }
}
