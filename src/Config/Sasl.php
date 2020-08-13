<?php

namespace PHP\Kafka\Config;

/**
 * Class Sasl
 *
 * @package PHP\Kafka\Config
 *
 * Provide configuration for Simple Authentication and Security Layer.
 */
class Sasl
{
    private string $username;
    private string $password;
    private string $mechanisms;
    private string $securityProtocol;

    public function __construct(string $username, string $password, string $mechanisms, string $securityProtocol)
    {
        $this->username = $username;
        $this->password = $password;
        $this->mechanisms = $mechanisms;
        $this->securityProtocol = $securityProtocol;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getMechanisms(): string
    {
        return $this->mechanisms;
    }

    public function getSecurityProtocol(): string
    {
        return $this->securityProtocol;
    }

    public function isPlainText(): bool
    {
        return $this->securityProtocol == 'SASL_PLAINTEXT';
    }
}
