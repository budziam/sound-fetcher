<?php

/**
 * Sound Fetcher
 *
 * @package  Sound Fetcher
 * @author   Michal Budzia <michal.mariusz.b@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__.'/bootstrap/autoload.php';

/*
 * Run a fetcher
 */
require __DIR__.'/bootstrap/run.php';