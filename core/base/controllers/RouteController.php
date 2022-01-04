<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController extends BaseController
{

    use Singleton;

    protected $routes;//свойство маршрутов

    private function __construct()
    {

        $adress_str = $_SERVER['REQUEST_URI'];//сохраняем адреесную строку(URL)

        //проверяем последний символ в адресной строке
        if (strrpos($adress_str, '/') === strlen($adress_str) - 1 && strrpos($adress_str, '/') !== 0){
            $this->redirect(rtrim($adress_str, '/'), 301);
        }

        //сохраняем обрезанную строку, в которой содержится имя скрипта
        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));

        //проверяем совпадает ли путь к корню нашего сайта, если да, то выполняем условия
        if ($path === PATH){

            $this->routes = Settings::get('routes');//подключаем маршруты нашего сайта

            if (!$this->routes) throw new RouteException('Сайт находится на техническом обслуживании');//если свойство с маршрутами пустое выкидываем исключение

            $url = explode('/', substr($adress_str, strlen(PATH)));//разделяем строку на массив


            //проверяем куда осуществляется вход, в административную часть или пользовательскую
            if ($url[0] && $url[0] === $this->routes['admin']['alias']){

                //административная часть

                array_shift($url);

                //проверка входа в плагин админ
                if ($url[0] && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $url[0])){

                    $plugin = array_shift($url);
                    $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin . 'Settings');//путь к файлу настроек плагина

                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')){

                        $pluginSettings = str_replace('/', '\\', $pluginSettings);
                        $this->routes = $pluginSettings::get('routes');

                    }

                    $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';
                    $dir = str_replace('//', '/', $dir);

                    $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;

                    $hrUrl = $this->routes['plugins']['hrUrl'];

                    $route = 'plugins';

                }else{

                    $this->controller = $this->routes['admin']['path'];

                    $hrUrl = $this->routes['admin']['hrUrl'];

                    $route = 'admin';

                }

            }else{

                //пользовательская часть

                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';

            }

            $this->createRoute($route, $url);//создаем маршрут

            if ($url[1]){
                $count = count($url);
                $key = '';

                if (!$hrUrl){
                    $i = 1;
                }else{
                    $this->parameters['alias'] = $url[1];
                    $i = 2;
                }

                for ( ; $i < $count ; $i++){
                    if (!$key){
                        $key = $url[$i];
                        $this->parameters[$key] = '';
                    }else{
                        $this->parameters[$key] = $url[$i];
                        $key = '';
                    }
                }
            }

        }else{

            try {
                throw new \Exception('Некорректная директория сайта');
            }
            catch (\Exception $e){
                exit($e->getMessage());
            }

        }

    }

    //метод создания маршрута
    private function createRoute($var, $arr){
        $route = [];

        if (!empty($arr[0])){
            if ($this->routes[$var]['routes'][$arr[0]]){
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);

                $this->controller .= ucfirst($route[0].'Controller');
            }else{
                $this->controller .= ucfirst($arr[0].'Controller');
            }
        }else{
            $this->controller .= $this->routes['default']['controller'];
        }


        $this->inputMethod = $route[1] ? $route[1] : $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ? $route[2] : $this->routes['default']['outputMethod'];

        return;
    }

}