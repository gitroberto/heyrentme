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
    public function run() {
        $equipments = $this->em->getRepository("AppBundle:Equipment")->getAllWithoutUuid();
        $talents = $this->em->getRepository("AppBundle:Talent")->getAllWithoutUuid();
        
        foreach($equipments as $eq) {
            $eq->setUuid(Utils::getUuid());
        }
        foreach($talents as $tal) {
            $tal->setUuid(Utils::getUuid());
        }
        $this->em->flush();
    }
}
