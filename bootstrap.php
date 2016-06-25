<?php

spl_autoload_register(function ($className) {
    $path = 'src/' . ltrim(str_replace('\\', '/', $className), '/') . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

