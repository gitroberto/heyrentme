<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * EquipmentRatingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EquipmentRatingRepository extends EntityRepository
{
    public function getAllSorted($equipmentId) {
        $sql = "select er from AppBundle:EquipmentRating er where er.equipment = :equipmentId order by er.createdAt desc";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('equipmentId', $equipmentId);
        return $query->getResult();        
    }       
}
