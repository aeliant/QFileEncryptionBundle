<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/9/17
 * Time: 8:21 PM
 */

namespace Querdos\QFileEncryptionBundle\Manager;


use Doctrine\ORM\EntityManager;
use Querdos\QFileEncryptionBundle\Entity\QFile;
use Querdos\QFileEncryptionBundle\Repository\QFileRepository;

class QFileManager
{
    /**
     * @var QFileRepository
     */
    private $repository;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Encrypt the file and store information in database
     *
     * @param QFile $qFile
     */
    public function create(QFile $qFile)
    {
        //
    }

    /**
     * @param QFileRepository $repository
     *
     * @return QFileManager
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @param EntityManager $em
     *
     * @return QFileManager
     */
    public function setEm($em)
    {
        $this->em = $em;
        return $this;
    }
}