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

    public function getTariffsForTalent($talentId) {
        return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('tt')
                ->from('AppBundle:TalentTariff', 'tt')
                ->where('tt.talent = :talentId')
                ->setParameter('talentId', $talentId)
                ->addOrderBy('tt.position')
                ->getQuery()
                ->getResult();
                
    }
    public function convertTalentToTariffs() {
        $out = '';
        $em = $this->getEntityManager();
        $talents = $em->getRepository('AppBundle:Talent')->findAll();
        foreach ($talents as $tal) {
            $n = str_pad(substr($tal->getName(), 0, 20) . "...", 24);
            $out .= "Checking talent id={$tal->getId()}, name={$n}\t";
            $tt = $em->getRepository('AppBundle:TalentTariff')->findOneBy(array(
                'type' => TariffType::$EINZELSTUNDEN->getId(),
                'talent' => $tal->getId()
            ));
            if ($tt === null) {
                $nt = new TalentTariff();
                $nt->setTalent($tal);
                $nt->setType(TariffType::$EINZELSTUNDEN->getId());
                $nt->setPrice($tal->getPrice());
                $nt->setRequestPrice($tal->getRequestPrice());
                $nt->setPosition(1);
                $em->persist($nt);
                $out .= "CREATED NEW Einzelstunden tariff";
            }
            else 
                $out .= "ALREADY EXISTS Einzelstunden tariff";
            $out .= "\n";
        }
        
        $em->flush();
        $out .= "Saved to database\n";
        
                                                   
        $inquiries = $em->getRepository('AppBundle:TalentInquiry')->findAll();
        foreach ($inquiries as $inq) {
            $out .= "Checking talent_inquiry id={$inq->getId()}\t";
            $type = $inq->getType();
            if ($type) {
                $out .= "ALREADY UPDATED";
            } else {
                $inq->setType(TariffType::$EINZELSTUNDEN->getId());
                $inq->setRequestPrice(0);
                $inq->setNum(null);
                $em->persist($inq);
                $out .= "Updating talent_inquiry type={$inq->getType()}, RequestedPrice={$inq->getRequestPrice()}, Num={$inq->getNum()}\t";
            }
            $out .= "\n";
        }
        $em->flush();
        
        return $out;
    }
}

