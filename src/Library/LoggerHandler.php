<?php

namespace App\Library;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerHandler
{
    public static function createLogger()
    {
        // Create a log channel
        $log = new Logger('qap-demo');

        // Create a stream handler (write logs to a file)
        $log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG));

        return $log;
    }
}
