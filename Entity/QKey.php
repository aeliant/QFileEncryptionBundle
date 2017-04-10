<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/10/17
 * Time: 8:58 AM
 */

namespace Querdos\QFileEncryptionBundle\Entity;


class QKey
{
    /**
     * @var int
     */
    private $id;

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
     */
    public function __construct($recipient = null, $passphrase = null)
    {
        $this->recipient  = $recipient;
        $this->passphrase = $passphrase;
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
}