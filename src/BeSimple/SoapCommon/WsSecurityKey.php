<?php

/*
 * This file is part of BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon;

use ass\XmlSecurity\Key as XmlSecurityKey;

/**
 * This class represents a security key for WS-Security (WSS).
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class WsSecurityKey
{
    /**
     * Private key.
     *
     * @var \ass\XmlSecurity\Key
     */
    protected $privateKey;

    /**
     * Public key.
     *
     * @var \ass\XmlSecurity\Key
     */
    protected $publicKey;

    /**
     * Add private key.
     *
     * @param string  $encryptionType Encryption type
     * @param string  $key            Private key
     * @param bool $keyIsFile      Given key parameter is path to key file
     * @param string  $passphrase     Passphrase for key
     */
    public function addPrivateKey($encryptionType, $key = null, $keyIsFile = true, $passphrase = null): void
    {
        $this->privateKey = XmlSecurityKey::factory($encryptionType, $key, $keyIsFile, XmlSecurityKey::TYPE_PRIVATE, $passphrase);
    }

    /**
     * Add public key.
     *
     * @param string  $encryptionType Encryption type
     * @param string  $key            Public key
     * @param bool $keyIsFile      Given key parameter is path to key file
     */
    public function addPublicKey($encryptionType, $key = null, $keyIsFile = true): void
    {
        $this->publicKey = XmlSecurityKey::factory($encryptionType, $key, $keyIsFile, XmlSecurityKey::TYPE_PUBLIC);
    }

    /**
     * Get private key.
     *
     * @return \ass\XmlSecurity\Key
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Get public key.
     *
     * @return \ass\XmlSecurity\Key
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Has private and public key?
     *
     * @return bool
     */
    public function hasKeys()
    {
        return null !== $this->privateKey && null !== $this->publicKey;
    }

    /**
     * Has private key?
     *
     * @return bool
     */
    public function hasPrivateKey()
    {
        return null !== $this->privateKey;
    }

    /**
     * Has public key?
     *
     * @return bool
     */
    public function hasPublicKey()
    {
        return null !== $this->publicKey;
    }
}
