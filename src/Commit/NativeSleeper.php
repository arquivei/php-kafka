<?php

declare(strict_types=1);

namespace PHP\Kafka\Commit;

class NativeSleeper implements Sleeper
{
    public function sleep(int $timeInMicroseconds): void
    {
        usleep($timeInMicroseconds);
    }
}
