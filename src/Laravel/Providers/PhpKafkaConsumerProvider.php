<?php

namespace Kafka\Consumer\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use Kafka\Consumer\Laravel\Console\Commands\PhpKafkaConsumerCommand;

class PhpKafkaConsumerProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->commands([
            PhpKafkaConsumerCommand::class
        ]);
    }
}
