#! /usr/bin/env php

<?php

set_time_limit(0);

use Symfony\Component\Console\Application;

// Composer
if (!file_exists('vendor/autoload.php')) {
    die('Composer dependency manager is needed: https://getcomposer.org/');
}
require 'vendor/autoload.php';

$app = new Application('Converter', '1.0');

$app->add(new Arall\Converter\Commands\Convert());

$app->run();
