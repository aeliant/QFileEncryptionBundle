<?php
namespace Querdos\QFileEncryptionBundle\Entity;

/**
 * Class QKey
 * @package Querdos\QFileEncryptionBundle\Entity
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class QKey
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $recipient;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * QKey constructor.
     *
     * @param string $recipient
     * @param string $passphrase
     * @param null   $username
     */
    public function __construct($recipient = null, $passphrase = null, $username = null)
    {
        $this->recipient  = $recipient;
        $this->passphrase = $passphrase;
        $this->username   = $username;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return QKey
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * @param string $passphrase
     *
     * @return QKey
     */
    public function setPassphrase($passphrase)
    {
        $this->passphrase = $passphrase;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param string $recipient
     *
     * @return QKey
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return QKey
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
}