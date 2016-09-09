<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;


class ConvertTalentToTariff {

    protected $em;
   
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    public function run() {
        return $this->em->getRepository('AppBundle:TalentTariff')->convertTalentToTariffs();
    }    
}
