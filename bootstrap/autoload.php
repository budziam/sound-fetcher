<?php

define('APP_START', microtime(true));
define('APP_PATH', realpath(__DIR__.'/..') . '/');

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require APP_PATH . 'vendor/autoload.php';

try {
    Dotenv::load(APP_PATH);
} catch (InvalidArgumentException $e) {
    //
}