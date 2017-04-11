<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/9/17
 * Time: 8:21 PM
 */

namespace Querdos\QFileEncryptionBundle\Manager;

class QFileManager extends BaseManager
{
    /**
     * Return a QFile with the given filename (which is unique)
     *
     * @param $filename
     *
     * @return mixed
     */
    public function readByUniqueFileName($filename)
    {
        return $this->repository->findOneByFilename($filename);
    }
}