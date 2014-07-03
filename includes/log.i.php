<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{
    /**
     * @var Logger
     */
    private static $_logger;

    public static function getLogger() {
        if (!isset(self::$_logger)) {
            self::$_logger = new Logger('emailtorss');
            self::$_logger->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));
        }

        return self::$_logger;
    }

    /**
     * @param $warning,...
     */
    public static function addWarning($warning) {
        call_user_func_array([self::getLogger(), 'addWarning'], func_get_args());
    }

    /**
     * @param $error,...
     */
    public static function addError($error) {
        call_user_func_array([self::getLogger(), 'addError'], func_get_args());
    }
}
