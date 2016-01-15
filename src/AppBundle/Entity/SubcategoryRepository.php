<?php

namespace AppBundle\Entity;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * SubcategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SubcategoryRepository extends \Doctrine\ORM\EntityRepository
{
    private $subcategories = null; // categories dictionary for fast check
    
    public function getSubcategoryBySlug($slug) {
        // initialise $subcategories
        if ($this->subcategories == null) {
            $result = $this
                ->getEntityManager()
                ->createQuery('select c from AppBundle:Subcategory c')
                ->getResult();            
            
            $this->subcategories = array();
            foreach ($result as $s) {
                $this->subcategories[$s->getSlug()] = $s;
            }
        }
        // actual get/check
        if (array_key_exists($slug, $this->subcategories)) {
            return $this->subcategories[$slug];
        } else {
            return null;
        }
    }
    
    public function getAllOrderedByName() {
        $q = "select s from AppBundle:Subcategory s order by s.name asc";
        return $this->getEntityManager()->createQuery($q)->getResult();
    }
    public function getAllOrderedByPosition() {
        $q = "select s from AppBundle:Subcategory s join s.category c order by c.position asc, s.position asc";
        return $this->getEntityManager()->createQuery($q)->getResult();
    }
    
    public function countAllByCategoryId($categoryID) {
        return $this->createQueryBuilder('sc')
            ->where('sc.category = :categoryID')
            ->setParameter('categoryID', $categoryID)                
            ->select('count(sc.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function getAllAsArray($categoryId) {
        
        $q = $this->createQueryBuilder('sc')
            ->select('sc')
            ->where('sc.category = :categoryID')
            ->orderBy('sc.position')
            ->setParameter('categoryID', $categoryId)
            ->getQuery();
        $rows = $q->getResult();
        
        $arr = array();
        foreach($rows as $sc) {
            $arr[$sc->getId()] = $sc->getName();
        }
        
        return $arr;
    }
    public function getGridOverview($categoryID, $sortColumn, $sortDirection, $pageSize, $page) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        // build query
        $qb->select('sc')
            ->from('AppBundle:Subcategory', 'sc')
            ->where('sc.category = :categoryID')
            ->setParameter('categoryID', $categoryID);
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

    public function getFeatureSectionsSorted($subcategoryId) {
        $dql = <<<EOT
            select fs
            from AppBundle:FeatureSection fs
            where fs.subcategory = :subcategoryId
            order by fs.position
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(':subcategoryId', $subcategoryId);
        return $q->getResult();
    }
    
}
