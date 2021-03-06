<?php

namespace AppBundle\Entity;

/**
 * EquipmentAgeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EquipmentAgeRepository extends \Doctrine\ORM\EntityRepository {
    
    public function getAllForDropdown() {        
        $q = $this->createQueryBuilder('ea')
            ->select('ea')
            ->orderBy('ea.position')
            ->getQuery();
        $rows = $q->getResult();
        
        //$arr = array('' => 'ALTER (CA.)');
        $arr = array();
        foreach($rows as $sc) {
            $arr[$sc->getId()] = $sc->getName();
        }
        
        return $arr;
    }

}
