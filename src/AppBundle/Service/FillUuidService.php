<?php

namespace AppBundle\Service;

use AppBundle\Utils\Utils;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Exception\Exception;


class FillUuidService {

    protected $em;
   
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    public function run($table) {
        if ($table == "eq" || $table == "equipment"){
            $this->equipment();
        } else if ($table == "tal" || $table == "talent"){
            $this->talent();
        } else if ($table == "blog"){
            $this->blog();
        } else if ($table == ""){
            $this->equipment();
            $this->talent();
            $this->blog();
        } else {
            return "There is no method for table ". $table . " yet";
        }       
    }
    
    protected function blog() {
        $blogs = $this->em->getRepository("AppBundle:Blog")->getAllWithoutUuid();
        foreach($blogs as $b) {
            $b->setUuid(Utils::getUuid());
        }
        $this->em->flush();
    }
    
    protected function equipment() {
        $equipments = $this->em->getRepository("AppBundle:Equipment")->getAllWithoutUuid();
        foreach($equipments as $eq) {
            $eq->setUuid(Utils::getUuid());
        }
        $this->em->flush();
    }
    
    protected function talent() {
        $talents = $this->em->getRepository("AppBundle:Talent")->getAllWithoutUuid();
        foreach($talents as $tal) {
            $tal->setUuid(Utils::getUuid());
        }
        $this->em->flush();
    }
    
    
}
