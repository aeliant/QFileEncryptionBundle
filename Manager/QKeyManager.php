<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/10/17
 * Time: 1:33 PM
 */

namespace Querdos\QFileEncryptionBundle\Manager;


use Querdos\QFileEncryptionBundle\Repository\BaseManager;

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