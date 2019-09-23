<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package    Kohana/Codebench
 * @category   Tests
 * @author     Geert De Deckere <geert@idoe.be>
 */
class Bench_Test extends A_Codebench_Codebench_Admin {
    
    public $loops = 10000;

    public $subjects = array("Для объявления переменных в XSLT служит элемент xsl:variable, который может как присутствовать в теле шаблона, так и быть элементом верхнего уровня. Элемент xsl:variable связывает имя, указанное ::test:: в обязательном атрибуте name, со значением выражения, указанного в атрибуте select или с деревом, которое является результатом выполнения шаблона, содержащегося в этом элементе. В том случае, если объявление переменной было произведено элементом верхнего уровня, переменная называется глобальной переменной. Переменные, ::test:: определенные элементами xsl:variable в шаблонах (то есть не на верхнем уровне) называются локальными переменными.");
    
    public function bench_test_A($subjects){
        return $subjects;
    }
    public function bench_test_B($subjects){
       return Str::__($subjects,array("::test::"=>"ЗАМЕНА"));
    }

}