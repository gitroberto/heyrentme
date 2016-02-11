<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Filesystem\Filesystem;


class ReportOffertRepository extends EntityRepository
{    
    
    public function countAll() {
        return $this->createQueryBuilder('ro')
            ->select('count(ro.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function getGridOverview($sortColumn, $sortDirection, $pageSize, $page) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        // build query
        $qb->select('b')
            ->from('AppBundle:ReportOffert', 'ro');
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
       
}
