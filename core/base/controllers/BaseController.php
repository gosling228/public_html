<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseController
{

    use \core\base\controllers\BaseMethods;//подключаем трейт

    protected $page;//хранится вся страница сайта

    protected $errors;

    //свойства родительского класса
    protected $controller;
    protected $inputMethod;//хранится имя метода отвечающего за сбор данных из бд
    protected $outputMethod;//хранится имя метода отвечающего за подключение видов
    protected $parameters;

    protected $styles;
    protected $scripts;

    public function route(){

        $controller = str_replace('/', '\\', $this->controller);

        try {

            //создаем объект класса \ReflectionMethod и в конструкторе его класса проверяем наличие метода "request" в классе "controller"
            $object = new \ReflectionMethod($controller, 'request');

            //создаем массив аргументов
            $args = [
                'parameters' => $this->parameters,//параметры адресной строки
                'inputMethod' => $this->inputMethod,//входной метод, который формирует запрос к модели и обрабатывает его
                'outputMethod' => $this->outputMethod//выходной метод, отвечает за подключение видов
            ];

            $object->invoke(new $controller, $args);//вызываем метод request

        }
        catch (\ReflectionException $e){

            throw new RouteException($e->getMessage());

        }

    }

    public function request($args){

        $this->parameters = $args['parameters'];
        $inputData = $args['inputMethod'];
        $outputData = $args['outputMethod'];

        $data = $this->$inputData();

        if (method_exists($this, $outputData)){

            $page = $this->$outputData($data);//сохраняем результат работы outputData в переменную
            if ($page) $this->page = $page;

        }elseif ($data){
            $this->page = $data;
        }

        if ($this->errors){
            $this->writeLog($this->errors);
        }

        $this->getPage();

    }

    //метод шаблонизатор, который собирает всю страницу
    protected function render($path = '', $parameters = []){

        extract($parameters);//разбираем массив на ключ -> значение

        //если путь не был передан методу
        if (!$path){

            $class = new \ReflectionClass($this);

            $space = str_replace('\\','/',$class->getNamespaceName() . '\\');
            $routes = Settings::get('routes');

            if ($space === $routes['user']['path']) $template = TEMPLATE;
            else $template = ADMIN_TEMPLATE;


            $path = $template . explode('controller', strtolower($class->getShortName()))[0];

        }

        ob_start();//открываем буфер обмена

        //внутри буфера обмена могут сохранятся все значения, и при этом не выводится в браузер

        //подключаем шаблон
        if (!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - ' . $path);

        return ob_get_clean();//закрываем буфер обмена и возвращаем значения буфера

    }

    //завершаем работу скрипта и показываем страницу
    protected function getPage(){

        if (is_array($this->page)){
            foreach ($this->page as $block) echo $block;
        }else{
            echo $this->page;
        }
        exit();

    }

    //инициализируем стили и скрипты которые указали в константах
    protected function init($admin = false){

        if (!$admin){
            if (USER_CSS_JS['styles']){
                foreach (USER_CSS_JS['styles'] as $item) $this->styles[] = PATH . TEMPLATE . trim($item, '/');
            }

            if (USER_CSS_JS['scripts']){
                foreach (USER_CSS_JS['scripts'] as $item) $this->scripts[] = PATH . TEMPLATE . trim($item, '/');
            }
        }else{

            if (ADMIN_CSS_JS['styles']){
                foreach (USER_CSS_JS['styles'] as $item) $this->styles[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
            }

            if (ADMIN_CSS_JS['scripts']){
                foreach (USER_CSS_JS['scripts'] as $item) $this->scripts[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
            }

        }

    }

}