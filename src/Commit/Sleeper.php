<?php

declare(strict_types=1);

namespace PHP\Kafka\Commit;

interface Sleeper
{
    public function sleep(int $timeInMicroseconds): void;
}
