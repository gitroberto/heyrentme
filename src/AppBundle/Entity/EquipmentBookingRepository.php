<?php

namespace AppBundle\Entity;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityRepository;

class EquipmentBookingRepository extends EntityRepository {

    public function getAllForUser($userId) {
        $dql = <<<EOT
            select b, i, e
            from AppBundle:EquipmentBooking b
                join b.inquiry i
                join i.equipment e
            where
                i.user = :userId
            order by i.fromAt asc
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter('userId', $userId);
        $bks = $q->getResult();
        
        // "hydrate" equipment images
        $repo = $this->getEntityManager()->getRepository('AppBundle:Equipment');
        foreach($bks as $bk) {
            $eq = $bk->getInquiry()->getEquipment();
            $eq->setEquipmentImages($repo->getEquipmentImages($eq->getId()));
        }        
        
        return $bks;        
    }
    public function getAllForProvider($userId) {
        $dql = <<<EOT
            select b, i, e
            from AppBundle:EquipmentBooking b
                join b.inquiry i
                join i.equipment e
            where
                e.user = :userId
            order by i.fromAt asc
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter('userId', $userId);
        $bks = $q->getResult();
        
        // "hydrate" equipment images
        $repo = $this->getEntityManager()->getRepository('AppBundle:Equipment');
        foreach($bks as $bk) {
            $eq = $bk->getInquiry()->getEquipment();
            $eq->setEquipmentImages($repo->getEquipmentImages($eq->getId()));
        }        
        
        return $bks;        
    }
    
    public function getAllForRentUserReminder(DateTime $datetime) {
        // 6 hrs before booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT6H0M0S'); // add 6 hrs
        $t = clone $datetime;
        $t->add($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:EquipmentBooking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeRentUserAt is null
                    and i.fromAt < :tStr
                    and b.status != :userCanceled 
                    and b.status != :providerCanceled 
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":tStr", $tStr);
        $q->setParameter(":userCanceled", EquipmentBooking::STATUS_USER_CANCELLED);
        $q->setParameter(":providerCanceled", EquipmentBooking::STATUS_PROVIDER_CANCELLED);
        return $q->getResult();
    }
    public function getAllForRentProviderReminder(DateTime $datetime) {
        // 6 hrs before booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT6H0M0S'); // add 6 hrs
        $t = clone $datetime;
        $t->add($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:EquipmentBooking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeRentProviderAt is null
                    and i.fromAt < :tStr
                    and b.status != :userCanceled 
                    and b.status != :providerCanceled 
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":tStr", $tStr);
        $q->setParameter(":userCanceled", EquipmentBooking::STATUS_USER_CANCELLED);
        $q->setParameter(":providerCanceled", EquipmentBooking::STATUS_PROVIDER_CANCELLED);
        return $q->getResult();
    }
    public function getAllForReturnUserReminder(DateTime $datetime) {
        // 3 hrs before end of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT3H0M0S'); // add 3
        $t = clone $datetime;
        $t->add($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:EquipmentBooking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeReturnUserAt is null
                    and i.toAt < :tStr
                    and b.status != :userCanceled 
                    and b.status != :providerCanceled 
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":tStr", $tStr);
        $q->setParameter(":userCanceled", EquipmentBooking::STATUS_USER_CANCELLED);
        $q->setParameter(":providerCanceled", EquipmentBooking::STATUS_PROVIDER_CANCELLED);
        return $q->getResult();
    }
    public function getAllForReturnProviderReminder(DateTime $datetime) {
        // 3 hrs before end of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT3H0M0S'); // add 3 hrs
        $t = clone $datetime;
        $t->add($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:EquipmentBooking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeReturnProviderAt is null
                    and i.toAt < :tStr
                    and b.status != :userCanceled 
                    and b.status != :providerCanceled 
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":tStr", $tStr);
        $q->setParameter(":userCanceled", EquipmentBooking::STATUS_USER_CANCELLED);
        $q->setParameter(":providerCanceled", EquipmentBooking::STATUS_PROVIDER_CANCELLED);
        return $q->getResult();
    }
    public function getAllForAllOkUserReminder(DateTime $datetime) {
        // exact start of booking        
        // we assume scheduler will run every 5 minutes
                
        //$delta = new DateInterval('PT4M59S'); // 0 hrs 4 min 59 sec
        $t = clone $datetime;
        //$t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:EquipmentBooking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeAllOkUserAt is null
                    and i.fromAt < :tStr
                    and b.status != :userCanceled 
                    and b.status != :providerCanceled 
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":tStr", $tStr);
        $q->setParameter(":userCanceled", EquipmentBooking::STATUS_USER_CANCELLED);
        $q->setParameter(":providerCanceled", EquipmentBooking::STATUS_PROVIDER_CANCELLED);        
        return $q->getResult();
    }
    public function getAllForAllOkProviderReminder(DateTime $datetime) {
        // exact start of booking        
        // we assume scheduler will run every 5 minutes
                
        //$delta = new DateInterval('PT4M59S'); // 0 hrs 4 min 59 sec
        $t = clone $datetime;
        //$t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:EquipmentBooking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeAllOkProviderAt is null
                    and i.fromAt < :tStr
                    and b.status != :userCanceled 
                    and b.status != :providerCanceled 
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        
        $q->setParameter(":tStr", $tStr);
        $q->setParameter(":userCanceled", EquipmentBooking::STATUS_USER_CANCELLED);
        $q->setParameter(":providerCanceled", EquipmentBooking::STATUS_PROVIDER_CANCELLED);
        
        return $q->getResult();
    }
    public function getAllForRateUserReminder(DateTime $datetime) {
        // 3 hrs after end of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT3H0M0S'); // 3 hrs
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');        
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:EquipmentBooking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeRateUserAt is null
                    and i.toAt < :tStr
                    and b.status != :userCanceled 
                    and b.status != :providerCanceled 
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        
        $q->setParameter(":tStr", $tStr);
        $q->setParameter(":userCanceled", EquipmentBooking::STATUS_USER_CANCELLED);
        $q->setParameter(":providerCanceled", EquipmentBooking::STATUS_PROVIDER_CANCELLED);
        
        return $q->getResult();
    }
    public function getAllForRateProviderReminder(DateTime $datetime) {
        // 3 hrs after end of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT3H0M0S'); // 3 hrs
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:EquipmentBooking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeRateProviderAt is null
                    and i.toAt < :tStr
                    and b.status != :userCanceled 
                    and b.status != :providerCanceled 
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        
        $q->setParameter(":tStr", $tStr);
        $q->setParameter(":userCanceled", EquipmentBooking::STATUS_USER_CANCELLED);
        $q->setParameter(":providerCanceled", EquipmentBooking::STATUS_PROVIDER_CANCELLED);
        
        return $q->getResult();
    }
}
