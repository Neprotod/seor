<?php
//if($_SESSION["verify"] != "FileManager4TinyMCE" OR $_SESSION["RF"]["verify"] != 'RESPONSIVEfilemanager') die('forbidden');
$_SESSION['fVerify'] = TRUE;
class Config{
    public $root;
    public $MaxSizeUpload=100; //Mb
    
    //**********************
    //Allowed extensions
    //**********************
    public $ext_img = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff'); //Images
    public $ext_file = array('doc', 'docx', 'pdf', 'xls', 'xlsx', 'txt', 'csv','html','psd','sql','log','fla','xml','ade','adp','ppt','pptx','php'); //Files
    public $ext_video = array('mov', 'mpeg', 'mp4', 'avi', 'mpg','wma'); //Videos
    public $ext_music = array('mp3', 'm4a', 'ac3', 'aiff', 'mid'); //Music
    public $ext_archive = array('zip', 'rar','gzip'); //Archives


    public $ext; //allowed extensions
    
    function __construct(){
        $this->root = rtrim($_SERVER['DOCUMENT_ROOT'],'/'); // don't touch this configuration
        $this->root = strtr($root,array('\\'=>'/'));
        
        $this->ext = array_merge($this->ext_img, $this->ext_file, $this->ext_archive, $this->ext_video,$this->ext_music); //allowed extensions
    }
}


$root = rtrim($_SERVER['DOCUMENT_ROOT'],'/'); // don't touch this configuration
$root = strtr($root,array('\\'=>'/'));
$MaxSizeUpload=100; //Mb

//**********************
//Allowed extensions
//**********************
$ext_img = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff'); //Images
$ext_file = array('doc', 'docx', 'pdf', 'xls', 'xlsx', 'txt', 'csv','html','psd','sql','log','fla','xml','ade','adp','ppt','pptx','php'); //Files
$ext_video = array('mov', 'mpeg', 'mp4', 'avi', 'mpg','wma'); //Videos
$ext_music = array('mp3', 'm4a', 'ac3', 'aiff', 'mid'); //Music
$ext_archive = array('zip', 'rar','gzip'); //Archives


$ext = array_merge($ext_img, $ext_file, $ext_archive, $ext_video,$ext_music); //allowed extensions
?>
