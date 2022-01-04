<?php

defined('VG_ACCESS') or die('Access denied');//проверяем константу безопасности

const TEMPLATE = 'templates/default/';//путь к шаблонам пользовательской части
const ADMIN_TEMPLATE = 'core/admin/views/';//путь к шаблонам административной части

//константы безопасности
const COOKIE_VERSION = '1.0.0';//версия куки
const CRYPT_KEY = '';//ключ шифрования для наших куки файлов
const COOKIE_TIME = 60;//время бездействия
const BLOCK_TIME = 3;//время блокировки того, кто попытался подобрать пароль(защита от бруд форса)

//константы постраничной навигации
const QTY = 8;//количество отображаемых товаров на странице
const QTY_LINKS = 3;//количество ссылок постраничной навигации

//пути к css  и js административной части
const ADMIN_CSS_JS = [
    'styles' => [],
    'scripts' => []
];

//пути к css  и js пользовательской части
const USER_CSS_JS = [
    'styles' => [],
    'scripts' => []
];

use core\base\exceptions\RouteException;//импортируем пространство имен класса RouteExcepton

//автозагрузка классов
function autoloadMainClasses($class_name){
    $class_name = str_replace('\\', '/', $class_name);

    if(!@include_once $class_name . '.php'){//если не подключаемся к файлу $class_name.php
        throw new RouteException('Не верное имя файла для подключения - ' . $class_name);//выкидываем исключенияе с сообщение м об ошибке
    }
}

spl_autoload_register('autoloadMainClasses');