<?php
spl_autoload_register(function ($class) {
    if (false !== stripos($class, 'Gaoming13/WechatPhpSdk')) {
        $file =  dirname(dirname(__DIR__)).'/'.str_replace('\\', '/', $class).'.class.php';
        require_once $file;
    }
});

