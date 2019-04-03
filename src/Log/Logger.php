<?php

namespace Kafka\Consumer\Log;

class Logger
{
    public function error(?int $messageId, int $attempts, \Throwable $exception): void
    {
        $error = json_encode([
            'throwable' => $exception,
            'attempt' => $attempts,
            'time' => date('Y-m-d H:i:s'),
        ]);
        print "\033[0;31m[PHP-KAFKA-CONSUMER-ERROR][$messageId]: $error\033[0m" . PHP_EOL;
    }
}
