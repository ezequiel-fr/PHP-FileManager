<?php

use FileManager\Except;

define('E_USER_EXCEPTION', 65536);

spl_autoload_register(function (string $class) {

    $file = preg_replace("/".explode("\\", $class)[0]."/", "", $class, 1);

    # Direct method
    $path = __DIR__.'/src/'.$file.'.php';
    
    if (is_readable($path)) {
        require_once $path;

        return;
    }

    # In *test* folder
    $path = dirname(__FILE__).'/../tests/'.$file.'.php';

    if (is_readable($path)) {
        require_once $path;

        return;
    }

    # In *vendor* folder
    $path = dirname(__FILE__).'/../vendor/'.$file.'.php';

    if (is_readable($path)) {
        require_once $path;

        return;
    }

});

// error_reporting(0);

set_error_handler(function ($errorNo, $errorText) {
    if (!error_reporting() & $errorNo)
        return false;

    $except = new Except();

    switch ($errorNo) {
        case intval(2**1) || intval(2**9):
            return $except->warning($errorText);
            
        case intval(2**8):
            die ($except->error($errorText));
        
        case intval(2**10):
            return $except->notice($errorText);
        
        case intval(2**14):
            die ($except->deprecated($errorText));
        
        case intval(2**16):
            die ($except->exception($errorText));
        
        default:
            die ($except->error("Uncaught error: " . $errorText));
    }
});
