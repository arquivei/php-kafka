<?php

namespace PHP\Kafka\Config;

class ProducerConfiguration
{
    private string $topic;
    private string $acks;

    /**
     * ProducerConfiguration constructor.
     * @param string $topic
     * @param string $acks
     */
    public function __construct(
        string $topic,
        string $acks = 'all'
    )
    {
        $this->acks = $acks;
        $this->topic = $topic;
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @return string
     */
    public function getAcks(): string
    {
        return $this->acks;
    }
}
