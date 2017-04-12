<?php
namespace Querdos\QFileEncryptionBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class BaseManager
 * @package Querdos\QFileEncryptionBundle\Manager
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
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
     * Return an entity by its id
     *
     * @param $id
     *
     * @return null|object
     */
    public function readById($id)
    {
        return $this->repository->find($id);
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