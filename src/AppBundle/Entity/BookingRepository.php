<?php

namespace AppBundle\Entity;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityRepository;

class BookingRepository extends EntityRepository {

    public function getAllForRentUserReminder(DateTime $datetime) {
        // 6 hrs before booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT5H55M01S'); // sub 6 hrs add 4 min 59 sec
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:Booking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeRentUserAt is null
                    and i.fromAt < '{$tStr}'
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        return $q->getResult();
    }
    public function getAllForRentProviderReminder(DateTime $datetime) {
        // 6 hrs before booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT5H55M01S'); // sub 6 hrs add 4 min 59 sec
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:Booking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeRentProviderAt is null
                    and i.fromAt < '{$tStr}'
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        return $q->getResult();
    }
    public function getAllForReturnUserReminder(DateTime $datetime) {
        // 3 hrs before end of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT2H55M01S'); // sub 3 hrs add 4 min 59 sec
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:Booking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeReturnUserAt is null
                    and i.toAt < '{$tStr}'
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        return $q->getResult();
    }
    public function getAllForReturnProviderReminder(DateTime $datetime) {
        // 3 hrs before end of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT2H55M01S'); // sub 3 hrs add 4 min 59 sec
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:Booking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeReturnProviderAt is null
                    and i.toAt < '{$tStr}'
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        return $q->getResult();
    }
    public function getAllForAllOkUserReminder(DateTime $datetime) {
        // exact start of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT4M59S'); // 0 hrs 4 min 59 sec
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:Booking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeAllOkUserAt is null
                    and i.fromAt < '{$tStr}'
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        return $q->getResult();
    }
    public function getAllForAllOkProviderReminder(DateTime $datetime) {
        // exact start of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT4M59S'); // 0 hrs 4 min 59 sec
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:Booking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeAllOkProviderAt is null
                    and i.fromAt < '{$tStr}'
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        return $q->getResult();
    }
    public function getAllForRateUserReminder(DateTime $datetime) {
        // 3 hrs after end of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT3H4M59S'); // 3 hrs 4 min 59 sec
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:Booking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeRateUserAt is null
                    and i.toAt < '{$tStr}'
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        return $q->getResult();
    }
    public function getAllForRateProviderReminder(DateTime $datetime) {
        // 3 hrs after end of booking        
        // we assume scheduler will run every 5 minutes
                
        $delta = new DateInterval('PT3H4M59S'); // 3 hrs 4 min 59 sec
        $t = clone $datetime;
        $t->sub($delta);
        $tStr = $t->format('Y-m-d H:i:s');
        
        $dql = <<<EOT
                select b, i, e
                from AppBundle:Booking b
                    join b.inquiry i
                    join i.equipment e
                where
                    b.noticeRateProviderAt is null
                    and i.toAt < '{$tStr}'
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        return $q->getResult();
    }
}
