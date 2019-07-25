<?php

namespace Softspring\AdminBundle\Manager;

use Doctrine\ORM\EntityRepository;

interface AdminEntityManagerInterface
{
    /**
     * @return string
     */
    public function getClass(): string;

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository;

    /**
     * @return object
     */
    public function createEntity();

    /**
     * @param object $entity
     */
    public function saveEntity($entity): void;
}