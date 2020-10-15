<?php

declare(strict_types=1);

namespace PHP\Kafka\Commit;

interface Committer
{
    public function commitMessage(): void;
    public function commitFailure(): void;
}
