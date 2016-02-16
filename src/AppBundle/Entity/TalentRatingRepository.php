<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TalentRatingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TalentRatingRepository extends EntityRepository
{
    public function getAllSorted($talentId) {
        $sql = "select er from AppBundle:TalentRating er where er.talent = :talentId order by er.createdAt desc";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('talentId', $talentId);
        return $query->getResult();        
    }       
}