<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BlogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DiscountCodeRepository extends EntityRepository
{    
   
    public function countAll() {
        return $this->createQueryBuilder('dc')
            ->select('count(dc.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function getGridOverview($sortColumn, $sortDirection, $pageSize, $page) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        // build query
        $qb->select(array('dc', 'u', 'e', 'eb', 'ei', 'ep', 't', 'tb', 'ti', 'tp', 'sub'))
            ->from('AppBundle:DiscountCode', 'dc')
                ->leftJoin('dc.user', 'u')
                ->leftJoin('dc.equipmentBooking', 'eb')
                ->leftJoin('eb.inquiry', 'ei')
                ->leftJoin('ei.equipment', 'e')
                ->leftJoin('e.user', 'ep')
                ->leftJoin('dc.talentBooking', 'tb')
                ->leftJoin('tb.inquiry', 'ti')
                ->leftJoin('ti.talent', 't')
                ->leftJoin('t.user', 'tp')
                ->leftJoin('dc.subscriber', 'sub');
        // sort by
        if (!empty($sortColumn)) {
            if (!empty($sortDirection)) {
                $qb->orderBy($sortColumn, $sortDirection);
            }
            else {
                $qb->orderBy($sortColumn);
            }
        }

        $q = $qb->getQuery();
        // page and page size
        if (!empty($pageSize)) {
            $q->setMaxResults($pageSize);
        }
        if (!empty($page) && $page != 1) {
            $q->setFirstResult(($page - 1) * $pageSize);
        }
        return $q->getResult();        
    }
    
    public function updateStatusIfExpired($now){        
        
        $tStr = $now->format('Y-m-d H:i:s');
        
        $dql = 'update AppBundle:DiscountCode dc set dc.status = :expired where (dc.status = :new or dc.status = :assigned) and dc.expiresAt is not null and dc.expiresAt < :now';        
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(':new', DiscountCode::STATUS_NEW);
        $q->setParameter(':assigned', DiscountCode::STATUS_ASSIGNED);        
        $q->setParameter(':expired', DiscountCode::STATUS_EXPIRED);        
        $q->setParameter(':now', $tStr);        
        return $q->execute();
    }
 
    public function assignToUser($user) {
        // find a free discount code
        $dql = sprintf("select dc from AppBundle:DiscountCode dc where dc.status = %d",
                DiscountCode::STATUS_NEW);
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setMaxResults(1);
        $rows = $q->getResult();
        
        if (count($rows) == 0) {
            return null;
        } 
        $code = $rows[0];
        
        // assign to the user
        $code->setUser($user);
        $code->setStatus(DiscountCode::STATUS_ASSIGNED);
        $this->getEntityManager()->flush();
        
        return $code;
    }
    public function assignToSubscriber($subscriber, $value) {
        // find a free discount code
        $dql = "select dc from AppBundle:DiscountCode dc where dc.status = :status and dc.value = :value";
        $q = $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('status', DiscountCode::STATUS_NEW)
                ->setParameter('value', $value);
        $q->setMaxResults(1);
        $rows = $q->getResult();
        
        if (count($rows) == 0)
            return null;
        
        $code = $rows[0];
        
        // assign to the user
        $code->setSubscriber($subscriber);
        $code->setStatus(DiscountCode::STATUS_ASSIGNED);
        $this->getEntityManager()->flush();
        
        return $code;
    }
    public function updateFromSubscriber($user) {
        $em = $this->getEntityManager();
        $sub = $em->getRepository('AppBundle:Subscriber')->findOneByEmail($user->getEmail());
        if ($sub !== null) {
            $dcodes = $sub->getDiscountCodes();
            foreach ($dcodes as $dc)
                $dc->setUser($user);
            $em->flush();
        }
    }
    public function updateFromUser($sub) {
        $em = $this->getEntityManager();
        $user = $em->getRepository('AppBundle:User')->findOneByEmail($sub->getEmail());
        if ($user !== null) {
            $dcodes = $sub->getDiscountCodes();
            foreach ($dcodes as $dc)
                $dc->setUser($user);
            $em->flush();
        }
    }

    
    public function isCodeUnique($code) {        
        $dql = "select dc from AppBundle:DiscountCode dc where dc.code = :code";
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":code", $code);
        $code = $q->getOneOrNullResult();
        return $code == null;
    }
}
