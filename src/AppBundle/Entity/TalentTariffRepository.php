<?php

namespace AppBundle\Entity;

class TalentTariffRepository extends \Doctrine\ORM\EntityRepository {

    public function getTariffCount($talentId) {
        $c = $this->getEntityManager()->createQueryBuilder()
                ->select('count(t)')
                ->from('AppBundle:TalentTariff', 't')
                ->andWhere('t.talent = :talentId')
                ->setParameter('talentId', $talentId)
                ->getQuery()
                ->getSingleScalarResult();
        return intval($c);
    }    
    public function getTariff($talentId, $type) {
        $q = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from('AppBundle:TalentTariff', 't')
                ->andWhere('t.type = :type')
                ->andWhere('t.talent = :talentId')
                ->setParameter('talentId', $talentId)
                ->setParameter('type', $type)
                ->getQuery();
        $rows = $q->getResult();
        if (count($rows) === 1)
            return $rows[0];
        else 
            return null;
    }
    public function getTariffs($talentId) {
        return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from('AppBundle:TalentTariff', 't')
                ->andWhere('t.talent = :talentId')
                ->setParameter('talentId', $talentId)
                ->addOrderBy('t.position', 'asc')
                ->getQuery()
                ->getResult();
    }
    public function saveOrder($talentId, $tariffIds) {
        $sql = '';
        $i = 1;
        foreach ($tariffIds as $id) {
            $sql .= "\nupdate talent_tariff set position = {$i} where id = {$id};";
            $i++;
        }
        $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }
    public function insert($tariff, $talentId) {
        $pos = $this->getEntityManager()->createQueryBuilder()
                ->select('max(tt.position)')
                ->from('AppBundle:TalentTariff', 'tt')
                ->andWhere('tt.talent = :talentId')
                ->setParameter('talentId', $talentId)
                ->getQuery()
                ->getSingleScalarResult();
        if ($pos === null)
            $pos = 1;
        else
            $pos = $pos + 1;
        $tariff->setPosition($pos);
        $em = $this->getEntityManager();
        $em->persist($tariff);
        $em->flush();
    }
}

