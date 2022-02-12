<?php

declare(strict_types=1);

namespace CoralMedia\Component\HttpFoundation\Session\Storage\Proxy;

use SessionHandlerInterface;
use SodiumException;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

class EncryptedSessionProxy extends SessionHandlerProxy
{
    protected string $sessionKey;
    protected string $keyPair;
    protected string $publicKey;
    protected string $privateKey;

    public const SODIUM_BASE64_VARIANT = SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;

    /**
     * EncryptedSessionProxy constructor.
     * @param SessionHandlerInterface $handler
     * @param string $sessionKey
     * @throws SodiumException
     */
    public function __construct(SessionHandlerInterface $handler, string $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->generateKeys();
        parent::__construct($handler);
    }

    /**
     * @param string $sessionId
     * @return false|string
     * @throws SodiumException
     */
    public function read($sessionId)
    {
        $data = parent::read($sessionId);

        if ($data === false || empty($data)) {
            return $data;
        }

        return sodium_crypto_box_seal_open(
            sodium_base642bin($data, self::SODIUM_BASE64_VARIANT),
            $this->keyPair
        );
    }

    /**
     * @param string $sessionId
     * @param string $data
     * @return bool
     * @throws SodiumException
     */
    public function write($sessionId, $data): bool
    {
        $data = sodium_bin2base64(
            sodium_crypto_box_seal($data, $this->publicKey),
            self::SODIUM_BASE64_VARIANT
        );

        return parent::write($sessionId, $data);
    }

    /**
     * @throws SodiumException
     */
    protected function generateKeys()
    {
        $this->keyPair = sodium_crypto_box_seed_keypair($this->sessionKey);
        $this->publicKey = sodium_crypto_box_publickey($this->keyPair);
        $this->privateKey = sodium_crypto_box_secretkey($this->keyPair);
    }

    /**
     * @return string
     */
    public function getKeyPair(): string
    {
        return $this->keyPair;
    }
}
