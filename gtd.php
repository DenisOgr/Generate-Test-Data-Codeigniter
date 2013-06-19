<?php
/*
 * Автор  Денчик (denis.porplenko@gmail.com)
 * Со авторы  Санечек(trigalex)  и  Жека Книжник(Borsch)
 * Компания AttractGroup (http://attractgroup.com/)
 *
 *
 * //генерация
 * $this->gtd->generate('table','50',array('field1'=>'int(1,10)'));
 *
 * //тестовый вывод
 * $this->gtd->test('table','50',array('field1'=>'int(1,10)'));
 *
 * Паттерны:
 * int(min,max) - целое число от min к max
 * double(min,max) - вещественное  число от min к max
 * do_array(array(1,'test',100)) - значение из массива
 * str(min,max) - строка размер которой от min до max
 * name() - имя
 * surname() - фамилия
 * fullname() - имя фамилия
 * address() - адрес
 * phone() - номер телефона
 * text(min,max) - текст от min до max
 * date(date_start,date_finish,pattern) - дата от date_start до date_finish.
 * url() - доменное имя
 * md5(123) - md5 от 123
 *img($path) - название картинки из папки $path(относительный путь)
 *table(table,field,isOne) - выбор данных из таблицы.
 *
 *  * **/
class gtd
{
    private $currentField = NULL;
    private $flagDoGenerate = TRUE;
    private $flagDoInsert = TRUE;
    private $tableName = '';
    private $countData = 1;
    private $countFactData = 0;
    private $aInsert = array();
    private $aFieldPattern = array();
    private $aName = array('Denis', 'John', 'Julia', 'Maria');
    private $aSurname = array('Bush', 'Guetta', 'Linkoln', 'Smith');
    private $aEmailDomain = array('gmail.com', 'yandex.ru', 'yahoo.com', 'rambler.ru', 'mail.ru');
    private $aZoneDomain = array('.com', '.ru', '.com.ua', '.il', '.biz');
    private $oCI;

    private $aTable = array();
    private $aMethodParams = array();
    private $aDate = array();
    private $aImg = array();
    private $aDoArray = array();
    private $sText = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
    Maecenas sit amet purus metus.
    Proin nisl massa, rutrum venenatis ultricies id, tempor sed metus.
    Nam fringilla ornare diam, nec venenatis sapien suscipit a.';


    function __construct()
    {

        $this->oCI = & get_instance();
        $this->oCI->load->helper('string');
    }

    public function test($table = null, $count = 1, $param = array())
    {
        $this->flagDoInsert = FALSE;
        $this->generate($table, $count, $param);
    }

    public function generate($table = null, $count = 1, $param = array())
    {
        if (!$table || $count <= 0 || empty($param) || !is_array($param)) $this->handlerErrors(1);
        if ($this->oCI->db->query('SHOW TABLES LIKE "' . $table . '"')->num_rows() == 0) $this->handlerErrors(2);
        //проверя вверно ли указанны поля таблицы. все ли поля есть в таблице
        $aFieldTable = $this->oCI->db->get($table)->list_fields();
        foreach ($param as $key => $value)
        {
            if (!in_array($key, $aFieldTable)) $this->handlerErrors(3);

        }
        $this->aFieldPattern = $param;
        $this->tableName = $table;
        $this->countData = $count;

        for ($i = 0; $i < $this->countData; $i++)
        {
            if (!$this->flagDoGenerate) break;
            $this->randOneRow();
        }
        if (!empty($this->aInsert))
        {
            $this->countFactData = count($this->aInsert);
            if ($this->flagDoInsert)
            {
                $this->oCI->db->insert_batch($this->tableName, $this->aInsert);
            } else
                prn($this->aInsert);
            $this->handlerErrors(0);
        }
    }

    private function  handlerErrors($typeError = null)
    {
        switch ($typeError)
        {
            case 0:
                {
                echo "Генерация прошла успешно! Добавленн " . $this->countFactData . " записей!";
                break;

                }
            case 1:
                {
                echo "Ошибка входящих параметров";
                break;

                }
            case 2:
                {
                echo "Нет указанной таблицы в базе данных";
                break;

                }
            case 3:
                {
                echo "Ошибка при указании полей для генерации. Вы указали поле, которого нет в таблице";
                break;
                }
            case 4:
                {
                echo "Вы не указали паттерн для одного из полей! Вообще не указали! Ну как, так-то..... :(";
                break;
                }
            case 5:
                {
                echo "Вы указали  на несуществующую функцию. Я ее не нашел ни в классе, ни вообще. Вверху этого класса описанны  все возможные функции! Или напиши свою... :)";
                break;
                }
            case 6:
                {
                echo "При генерации картинок Вы  не указали папку или указали  НЕСУЩЕСТВУЮЩУЮ папку или папка пустая.... Нужно указать относительный путь :)";
                break;
                }
            case 7:
                {
                echo "При генерации данных из массив (функция do_array()) Вы  не указали  элементы массива.... Нужно указать  :)";
                break;
                }
            case 8:
                {
                echo "При генерации данных из таблицы БД  (функция table(table,field,type)) Вы  не указали  таблицу или поле .... Внимательнее  :)";
                break;
                }
            case 9:
                {
                echo "При генерации данных из таблицы БД  (функция table(table,field,type)) Вы   указали  ТАБЛИЦУ, которой НЕТ в БД .... Внимательнее  :)";
                break;
                }
            case 10:
                {
                echo "При генерации данных из таблицы БД  (функция table(table,field,type)) Вы   указали  ПОЛЕ, которого НЕТ в БД .... Внимательнее  :)";
                break;
                }
        }
        exit();
    }

    private function randOneRow()
    {
        $temp = array();
        foreach ($this->aFieldPattern as $key => $value)
        {
            $this->currentField = $key;
            $temp[$key] = $this->patternToFunction(trim($value));
        }
        array_push($this->aInsert, $temp);
    }

    private function  patternToFunction($str = '')
    {
        if (!isset($this->aMethodParams[$str]))
        {
            if (empty($str)) $this->handlerErrors(4);

            $method = substr($str, 0, strpos($str, '(', 1));
            $params = substr($str, strpos($str, '(', 1) + 1, -1);
            $aParam = explode(',', $params);
            $this->aMethodParams[$str]['PARAMS'] = '';
            foreach ($aParam as $key => $value)
            {
                $this->aMethodParams[$str]['PARAMS'] .= "'" . trim(str_replace(array("'"),"",$value)) . "',";
            }
                $this->aMethodParams[$str]['PARAMS'] = substr($this->aMethodParams[$str]['PARAMS'],0,-1);


            $flagIsMethodClass = method_exists(__CLASS__, $method);
            $flagIsFunction = function_exists($method);
            if ($flagIsMethodClass === FALSE && $flagIsFunction === FALSE) $this->handlerErrors(5);

            $this->aMethodParams[$str]['METHOD'] = ($flagIsMethodClass) ? 'return($this->' . $method . '(' . $this->aMethodParams[$str]['PARAMS']  . '));' : 'return(' . $method . '(' . $this->aMethodParams[$str]['PARAMS']  . '));';
        }
       //prn($this->aMethodParams[$str]['METHOD']);
        return eval($this->aMethodParams[$str]['METHOD']);

    }


    private function int($min = 0, $max = 100000)
    {
        return mt_rand((int)$min, (int)$max);
    }

    private function name()
    {
        return $this->aName[array_rand($this->aName)];
    }

    private function surname()
    {
        return $this->aSurname[array_rand($this->aSurname)];
    }

    private function fullname()
    {
        return $this->aName[array_rand($this->aName)] . ' ' . $this->aSurname[array_rand($this->aSurname)];
    }

    private function phone()
    {
        return '+' . mt_rand(0, 9) . '(' . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . ')' . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9);
    }

    private function email()
    {
        return strtolower(random_string('alpha', mt_rand(5, 15))) . '@' . $this->aEmailDomain[array_rand($this->aEmailDomain)];
    }

    private function str($min = 0, $max = 200)
    {
        return strtolower(random_string('alpha', mt_rand($min, $max)));
    }

    private function img($path = null)
    {

        if (!isset($this->aImg[$this->currentField]))
        {
            if ($path == null || !is_dir($path) || count(scandir($path)) <= 2) $this->handlerErrors(6);
            $this->aImg[$this->currentField] = scandir($path);
            unset($this->aImg[$this->currentField][0]);
            unset($this->aImg[$this->currentField][1]);
        }
        return $this->aImg[$this->currentField][array_rand($this->aImg[$this->currentField])];
    }

    private function do_array()
    {
        if (empty($this->aDoArray[$this->currentField]))
        {
            $this->aDoArray[$this->currentField] = func_get_args();
            if (count($this->aDoArray[$this->currentField]) < 1) $this->handlerErrors(7);
        }
        return $this->aDoArray[$this->currentField][array_rand($this->aDoArray[$this->currentField])];

    }

    private function text($min = 100, $max = 5000)
    {
        $countChar = mt_rand($min, $max);
        $countCharText = strlen($this->sText);
        $count = round($countChar / $countCharText);
        $returnText = '';
        if (!$count) return $this->sText;
        for ($i = 0; $i < $count; $i++)
        {
            $returnText .= $this->sText;
        }
        return $returnText;

    }

    private function md5($param = '1')
    {
        return md5($param);
    }

    private function double($min = 1, $max = 100)
    {
        return (float)mt_rand($min, $max) + (1 / mt_rand(1, 100));
    }

    private function address()
    {
        return ucfirst(strtolower(random_string('alpha', mt_rand(5, 20)))) . ', ' . mt_rand(1, 200);
    }

    private function url()
    {
        return 'http://www.' . strtolower(random_string('alpha', mt_rand(5, 15))) . $this->aZoneDomain[array_rand($this->aZoneDomain)];
    }

    private function date($dateStart = null, $dateFinish = null, $pattern = 'Y-m-d H:i:s')
    {

        if (!isset($this->aDate[$this->currentField]))
        {
            $this->aDate[$this->currentField]['dateStart'] = strtotime(($dateStart) ? $dateStart : '1985-10-01 08:00:00');
            $this->aDate[$this->currentField]['dateFinish'] = strtotime(($dateFinish) ? $dateFinish : '2020-10-01 08:00:00');
        }

        return date($pattern, mt_rand($this->aDate[$this->currentField]['dateStart'], $this->aDate[$this->currentField]['dateFinish']));
    }


    private function table($table = null, $field = null, $onlyOne = FALSE)
    {

        if (!isset($this->aTable[$this->currentField]))
        {
            $this->aTable[$this->currentField] = array();
            if ($table == null || $field == null) $this->handlerErrors(8);
            if ($this->oCI->db->query('SHOW TABLES LIKE "' . $table . '"')->num_rows() == 0) $this->handlerErrors(9);
            $aFieldTable = $this->oCI->db->get($table)->list_fields();
            // prn($aFieldTable);
            if (!in_array($field, $aFieldTable)) $this->handlerErrors(10);
            $aTemp = $this->oCI->db->select($field)->get($table)->result_array();
            if (!empty($aTemp))
            {
                foreach ($aTemp as $key => $value)
                {
                    array_push($this->aTable[$this->currentField], $value[$field]);
                }

            }
        }
        $getFromArray = array_rand($this->aTable[$this->currentField]);

        $result = $this->aTable[$this->currentField][$getFromArray];
        if ($onlyOne) unset($this->aTable[$this->currentField][$getFromArray]);
        if (count($this->aTable[$this->currentField]) < 1) $this->flagDoGenerate = FALSE;
        return $result;

    }

}

/*
* DEBUG
*/
if (!function_exists('prn'))
{
    function prn($content)
    {
        echo '<pre style="background: lightgray; border: 1px solid black;">';
        print_r($content);
        echo '</pre>';
    }
}

