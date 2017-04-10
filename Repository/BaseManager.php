<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/10/17
 * Time: 1:35 PM
 */

namespace Querdos\QFileEncryptionBundle\Repository;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class BaseManager
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Create a new entity in database
     *
     * @param $entity
     */
    public function create($entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }

    /**
     * Update an entity in database
     *
     * @param $entity
     */
    public function update($entity)
    {
        $uow = $this->entityManager->getUnitOfWork();
        if (!$uow->isEntityScheduled($entity)) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush($entity);
    }

    /**
     * Remove an entity in database
     *
     * @param $entity
     */
    public function delete($entity)
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush($entity);
    }

    /**
     * Return all existing entities in database
     *
     * @return array
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param EntityRepository $repository
     *
     * @return BaseManager
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return BaseManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }
}