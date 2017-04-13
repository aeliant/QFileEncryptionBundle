<?php
namespace Querdos\QFileEncryptionBundle\Manager;

use Querdos\QFileEncryptionBundle\Entity\QFile;

/**
 * Class QFileManager
 * @package Querdos\QFileEncryptionBundle\Manager
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class QFileManager extends BaseManager
{
    /**
     * Return a QFile with the given filename (which is unique)
     *
     * @param $filename
     *
     * @return QFile
     */
    public function readByUniqueFileName($filename)
    {
        return $this->repository->findOneByFilename($filename);
    }

    /**
     * Return all QFile associated for the given username
     *
     * @param string $username
     * @return QFile[]
     */
    public function readAllForUsername($username)
    {
        return $this->repository->allForUsername($username);
    }
}