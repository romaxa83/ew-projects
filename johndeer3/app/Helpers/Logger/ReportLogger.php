<?php

namespace App\Helpers\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class ReportLogger
{
    const INFO = 'INFO';
    const ERROR = 'ERROR';

    private static $instance;

    private static $type;
    private $enable;
    private Logger $logger;

    private function __construct()
    {
        $this->init();
    }

    private function writte($message, array $context = [])
    {
        if (self::$type == self::INFO) {
            $this->logger->info($message, $context);
        }
        if (self::$type == self::ERROR) {
            $this->logger->error($message, $context);
        }
    }

    public function init()
    {
        $this->logger = new Logger("REPORT_");
        $this->logger->pushHandler(new StreamHandler(config('logging.custom.report.path')));
        $this->enable = config('logging.custom.report.enable');
    }

    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }


        return static::$instance;
    }

    public static function info($message, array $context = [])
    {
        self::$type = self::INFO;
        self::getInstance()->writte($message, $context);
    }

    public static function error($message, array $context = [])
    {
        self::$type = self::ERROR;
        self::getInstance()->writte($message, $context);
    }
}
