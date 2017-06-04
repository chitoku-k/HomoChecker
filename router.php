<?php
$uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
$path = parse_url($uri, PHP_URL_PATH);
$filename = $path !== '/' ? $path : '/index.html';

if (file_exists(__DIR__ . $filename)) {
    return false;
}

if (file_exists($target = __DIR__ . "/client/dist{$filename}")) {
    // Output appropriate header
    switch (substr($target, strrpos($target, '.') + 1)) {
        case 'css':
            header('Content-Type: text/css');
            break;

        case 'js':
            header('Content-Type: text/javascript');
            break;
    }

    return require $target;
}

// Do a little trick to get Slim to work
$_SERVER['SCRIPT_NAME'] = 'index.php';

// Then load our controller
require __DIR__ . '/app/src/index.php';
