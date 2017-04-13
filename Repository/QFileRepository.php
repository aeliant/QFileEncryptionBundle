<?php
namespace Querdos\QFileEncryptionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Querdos\QFileEncryptionBundle\Entity\QFile;

/**
 * Class QFileRepository
 * @package Querdos\QFileEncryptionBundle\Repository
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class QFileRepository extends EntityRepository
{
    /**
     * Return all QFile associated for the given username
     *
     * @param string $username
     * @return QFile[]
     */
    public function allForUsername($username)
    {
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()

            ->select('qfile')
            ->from('QFileEncryptionBundle:QFile', 'qfile')

            ->join('qfile.qkey', 'qkey')
            ->where('qkey.username = :username')

            ->setParameter('username', $username)
        ;

        return $query
            ->getQuery()
            ->getResult()
        ;
    }
}