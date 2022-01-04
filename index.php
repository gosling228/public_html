<?php

define('VG_ACCESS', true);//константа безопасности, чтобы не было доступа напрямую к другим файлам

header('Content-Type:text/html;charset=utf-8');
session_start();//стартуем сессию

require_once 'config.php';//в этом файле базовые настройки для быстрого развертывания сайта на другом хостинге
require_once 'core/base/settings/internal_settings.php';//фундаментальные настройки(пути к шаблонам, настройки безопасности)

use core\base\exceptions\RouteException;//импортируем пространство имен класса RouteExcepton
use core\base\controllers\RouteController;//импортируем пространство имен класса RouteControler

try{
    RouteController::instance()->route();
}
catch (RouteException $e){
    exit($e->getMessage());
}