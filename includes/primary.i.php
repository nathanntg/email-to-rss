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
define('AWS_REGION', 'us-east-1');
define('AWS_BUCKET', 'bucket-name');
define('AWS_PREFIX', '');
define('EMAIL_MAX_SIZE', 512000); // 512KB
define('STORE_HTML_PAGE', true);

// use a default mapping function
if (!function_exists('mapFromToRss')) {
    function mapFromToRss($from, $to) {
        // accept wildcard email addresses (for example, sample.rss@emailtorss.com)
        if (preg_match('/([-_a-z0-9]+\.rss)/i', $to, $match)) {
            return $match[0];
        }

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
