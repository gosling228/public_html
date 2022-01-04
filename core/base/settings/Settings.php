<?php

namespace core\base\settings;

use core\base\controllers\Singleton;

class Settings
{

    use Singleton;

    //маршруты
    private $routes = [
        'admin' => [
            'alias' => 'admin', /* имя из поисковой строки, по которому будем определять вход в административную часть */
            'path' => 'core/admin/controllers/', /* путь к административной части*/
            'hrUrl' => false, /* человеко-понятный url (human readable url) */
            'routes' => [

             ]
        ],
        'settings' => [
            'path' => 'core/base/settings/'/* путь к файлу настроек */
        ],
        'plugins' => [
            'path' => 'core/plugins/', /* путь к плагинам */
            'hrUrl' => false, /* человеко-понятный url (human readable url) */
            'dir' => false
        ],
        'user' => [
            'path' => 'core/user/controllers/',
            'hrUrl' => true, /* человеко-понятный url (human readable url) */
            /* ячейка маршрутов  */
            'routes' => [

            ]
        ],

        /* раздел по умолчанию */
        'default' => [
            'controller' => 'IndexController', /* контроллер подключаемый по умолчанию */
            'inputMethod' =>  'inputData', /* метод, подключаемый по умолчанию, который вызовется у контроллера */
            'outputMethod' => 'outputData'/* метод подключаемый по умолчанию, отвечает за вывод данных в пользовательские шаблоны */
        ]
    ];

    private $templateArr = [
        'text' => ['name', 'phone', 'adress'],
        'textarea' => ['content', 'keywords']
    ];

    //получаем нужные свойства настроек
    static public function get($property){
        return self::instance()->$property;
    }

    //метод для склейки массивов
    public function clueProperties($class){
        $baseProperties = [];

        foreach ($this as $name => $item){
            $property = $class::get($name);

            if(is_array($property) && is_array($item)){
                $baseProperties[$name] = $this->arrayMergeRecursive($this->$name, $property);
                continue;
            }
            if (!$property) $baseProperties[$name] = $this->$name;
        }
        return $baseProperties;
    }

    //алгоритм склейки массивов
    public function arrayMergeRecursive(){
        $arrays = func_get_args();

        $base = array_shift($arrays);//возвращает первый элемент массива, удаляя из исходного массива

        foreach ($arrays as $array){
            foreach ($array as  $key => $value){
                if(is_array($value) && is_array($base[$key])){
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                }else{
                    if(is_int($key)){
                        if(!in_array($value, $base)) array_push($base, $value);
                        continue;
                    }
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }

}