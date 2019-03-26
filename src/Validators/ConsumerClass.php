<?php

namespace Kafka\Consumer\Validators;

use Kafka\Consumer\Entities\Config\Consumer;

class ConsumerClass
{
    public function validate(Consumer $consumer, \RdKafka\Message $message): ?string
    {
        if ($consumer->isOnlyDefault()) {
            return $consumer->getDefault();
        }

        foreach ($consumer->getCustoms() as $custom) {
            if ($this->checkValidations($custom['validations'], $message)) {
                return $custom['consumer'];
                break;
            }
        }

        if ($consumer->hasConsumerDefault()) {
            return $consumer->getDefault();
        }

        return null;
    }

    private function checkValidations(array $validations, \RdKafka\Message $message): bool
    {
        foreach ($validations as $validation) {
            $value = $this->getValueValidation($validation['key'], $message);
            if ($value !== $validation['value']) {
                return false;
            }
        }

        return true;
    }

    private function getValueValidation(array $keys, \RdKafka\Message $message)
    {
        $validation = $this->parserMessagePayload($message);
        foreach ($keys as $key) {
            $validation = $validation->$key ?? $validation;
        }
        return $validation;
    }

    private function parserMessagePayload(\RdKafka\Message $message)
    {
        try {
            return json_decode($message->payload);
        } catch (\Throwable $exception) {
            return $message->payload;
        }
    }
}
