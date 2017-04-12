<?php
namespace Querdos\QFileEncryptionBundle\Manager;

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
     * @return QKeyManager
     */
    public function findByUsername($username)
    {
        return $this->repository->findOneByUsername($username);
    }
}