<?php

namespace PHP\Kafka\Log;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Formatter\JsonFormatter;

class PhpKafkaLogger
{
    private Logger $logger;

    public function __construct(string $name = 'PHP-KAFKA-LOG')
    {
        $handler = new StreamHandler("php://stdout");
        $handler->setFormatter(new JsonFormatter());
        $handler->pushProcessor(new UidProcessor(32));
        $this->logger = new Logger($name);
        $this->logger->pushHandler($handler);
        $this->logger->pushProcessor(
            function ($record) {
                $record['datetime'] = $record['datetime']->format('c');
                return $record;
            }
        );
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
