<?php defined('SYSPATH') OR exit();
/**
 * Класс работы со строками. 
 * 
 * @package    Tree
 * @category   Helpers
 */
class Core_Text{
    /**
     * Returns human readable sizes. Based on original functions written by
     * [Aidan Lister](http://aidanlister.com/repos/v/function.size_readable.php)
     * and [Quentin Zervaas](http://www.phpriot.com/d/code/strings/filesize-format/).
     *
     *     echo Text::bytes(filesize($file));
     *
     * @param   integer  size in bytes
     * @param   string   a definitive unit
     * @param   string   the return string format
     * @param   boolean  whether to use SI prefixes or IEC
     * @return  string
     */
    public static function bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE){
        // Format string
        $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

        // IEC prefixes (binary)
        if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE){
            $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            $mod   = 1024;
        }else{
            $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
            $mod   = 1000;
        }

        // Determine unit to use
        if (($power = array_search( (string) $force_unit, $units)) === FALSE){
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }

        return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
    }
    /**
     * Prevents [widow words](http://www.shauninman.com/archive/2006/08/22/widont_wordpress_plugin)
     * by inserting a non-breaking space between the last two words.
     *
     *     echo Text::widont($text);
     *
     * @param   string  text to remove widows from
     * @return  string
     */
    public static function widont($str){
        $str = rtrim($str);
        $space = strrpos($str, ' ');

        if ($space !== FALSE){
            $str = substr($str, 0, $space).'&nbsp;'.substr($str, $space + 1);
        }

        return $str;
    }
}