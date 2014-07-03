<?php

// settings
define('AWS_BUCKET', 'rss.emailtorss.com');
define('AWS_PREFIX', '');
define('EMAIL_MAX_SIZE', 512000); // 512KB

// directory
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define('ROOT_INCLUDES', __DIR__ . DIRECTORY_SEPARATOR);

// vendor autoload
require(ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

// mailgun
require(ROOT_INCLUDES . 'mailgun.i.php');
require(ROOT_INCLUDES . 'log.i.php');
require(ROOT_INCLUDES . 'cdn.i.php');
require(ROOT_INCLUDES . 'rss.i.php');

// get configuration variable
function configurationGet($variable) {
    if (!isset($_ENV[$variable])) {
        Log::addError(sprintf("Expected %s environment variable.", $variable));
    }

    return $_ENV[$variable];
}

function mapFromToRss($from) {
    $mapping = [
        '@qz.com'           =>  'quartz.rss',
        '@nowiknow.com'     =>  'nowiknow.com',
        '@quora.com'        =>  'quora.rss',
        '@davenetics.com'   =>  'nextdraft.rss',
        '@nextdraft.com'    =>  'nextdraft.rss',
        '@lists.stripe.com' =>  'stripe.rss'
    ];

    foreach ($mapping as $key => $val) {
        if (false !== stripos($from, $key)) {
            return $val;
        }
    }

    return 'other.rss';
}
