<?php

// directory
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define('ROOT_INCLUDES', __DIR__ . DIRECTORY_SEPARATOR);

// vendor autoload
require(ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

// mailgun
if (file_exists(ROOT_INCLUDES . 'configuration.i.php')) {
    require(ROOT_INCLUDES . 'configuration.i.php');
}
require(ROOT_INCLUDES . 'mailgun.i.php');
require(ROOT_INCLUDES . 'log.i.php');
require(ROOT_INCLUDES . 'cdn.i.php');
require(ROOT_INCLUDES . 'rss.i.php');

// default settings
define('AWS_BUCKET', 'bucket-name');
define('AWS_PREFIX', '');
define('EMAIL_MAX_SIZE', 512000); // 512KB

// use a default mapping function
if (!function_exists('mapFromToRss')) {
    function mapFromToRss($from) {
        return 'other.rss';
    }
}

// get configuration variable
function configurationGet($variable) {
    if (!isset($_ENV[$variable])) {
        Log::addError(sprintf("Expected %s environment variable.", $variable));
    }

    return $_ENV[$variable];
}
