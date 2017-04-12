<?php
namespace Querdos\QFileEncryptionBundle\Manager;

use Querdos\QFileEncryptionBundle\Entity\QKey;

/**
 * Class QKeyManager
 * @package Querdos\QFileEncryptionBundle\Manager
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class QKeyManager extends BaseManager
{
    /**
     * Find a key pair with the given username
     *
     * @param string $username
     *
     * @return QKey
     */
    public function findByUsername($username)
    {
        return $this->repository->findOneByUsername($username);
    }

    /**
     * Find a key pair with the given email
     *
     * @param string $recipient
     *
     * @return QKey
     */
    public function findByRecipient($recipient)
    {
        return $this->repository->findOneByRecipient($recipient);
    }
}