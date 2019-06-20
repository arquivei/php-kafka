<?php

namespace Kafka\Consumer\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

class Logger
{
    private $logger;

    public function __construct()
    {
        $handler = new StreamHandler("php://stdout");
        $handler->setFormatter(new JsonFormatter());
        $this->logger = new \Monolog\Logger('PHP-KAFKA-CONSUMER-ERROR');
        $this->logger->pushHandler($handler);
        $this->logger->pushProcessor(function ($record) {
            $record['datetime'] = $record['datetime']->format('c');
            return $record;
        });
    }

    public function error(?int $messageId, \Throwable $exception, string $prefix = 'ERROR'): void
    {
        $this->logger->error("[$prefix] Error to consume message", [
            'message_id' => $messageId,
            'throwable' => $exception
        ]);
    }
}
