<?php

namespace AppBundle\Entity;

use AppBundle\Utils\SearchParams;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * EquipmentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EquipmentRepository extends EntityRepository
{
    public function getAllBySubcategory($subcategoryId) {
        $sql = "select e from AppBundle:Equipment e where e.subcategory = :subcategoryId and e.status = :approved";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('subcategoryId', $subcategoryId);
        $query->setParameter('approved', Equipment::STATUS_APPROVED);
        return $query->getResult();        
    }   
    
    public function getAllForOtherCategories($subcategoryId) {
        //TODO: add user status = ok 
        $sql = "select e from AppBundle:Equipment e where e.subcategory != :subcategoryId and e.status = :approved";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('subcategoryId', $subcategoryId);
        $query->setParameter('approved', Equipment::STATUS_APPROVED);
        return $query->setMaxResults(4)->getResult();        
    }   
    
    public function getSamplePreviewEquipmentsBySubcategory($subcategoryId, $eqId) {
        #TODO: Correct query, remove hardcoded number of items
        // TODO: refactor: write query with proper "order by" and take first four items
        //TODO: add user status = ok 
        $sql = "select e from AppBundle:Equipment e where e.subcategory = :subcategoryId and e.id != :id and e.status = :approved";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('subcategoryId', $subcategoryId);
        $query->setParameter('id', $eqId);
        $query->setParameter('approved', Equipment::STATUS_APPROVED);        
        $results = $query->setMaxResults(4)->getResult();        
        
        if (count($results) < 4) {
            $resultsFromOtherCats = $this->getAllForOtherCategories($subcategoryId);
            foreach($resultsFromOtherCats as $rfoc){
                $results[count($results)] = $rfoc;
                if (count($results) == 4) {
                    break;
                }
            }
        }
        
        return $results;
    }   
    
    public function getAllByUserId($userId) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('AppBundle:Equipment', 'e')
            ->join('e.user', 'u');
        $qb->andWhere("u.id = {$userId}");

        $q = $qb->getQuery();
        
        return $q->getResult();        
    }
    
    public function getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $sStatus) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        // build query
        $qb->select('e, u')
            ->from('AppBundle:Equipment', 'e')
            ->join('e.user','u');
        
        if (!empty($sStatus)) {
            $qb->andWhere($qb->expr()->eq('e.status', ':status'));
        }
        
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
        if (!empty($sStatus)) {
            $q->setParameter(':status', $sStatus);
        }
        
        // page and page size
        if (!empty($pageSize)) {
            $q->setMaxResults($pageSize);
        }
        if (!empty($page) && $page != 1) {
            $q->setFirstResult(($page - 1) * $pageSize);
        }
        return $q->getResult();        
    }
    
    public function countAll() {
        return $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function getImageCount($equipmentId) {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('count(ei.image)')
            ->from('AppBundle:EquipmentImage', 'ei')
            ->andWhere("ei.equipment = {$equipmentId}")
            ->getQuery()
            ->getSingleScalarResult();            
    }
 
    public function getEquipmentImages($equipmentId) {
        // main first
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ei', 'i')
            ->from('AppBundle:EquipmentImage', 'ei')
            ->join('ei.image', 'i')
            ->andWhere("ei.equipment = {$equipmentId}")
            ->addOrderBy('ei.main', 'desc')
            ->addOrderBy('i.id');

        $q = $qb->getQuery();
        
        return $q->getResult();        
    }
    public function setMainImage($equipmentId, $imageId) {
        $sql = <<<EOT
update equipment_image
set main = case when image_id = {$imageId} then 1 else 0 end
where equipment_id = {$equipmentId}
EOT;
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeUpdate($sql);        
    }
    public function removeImage($equipmentId, $imageId, $imageStorageDir) {
        $em = $this->getEntityManager();
        $eq = $em->getRepository('AppBundle:Equipment')->find($equipmentId);        
        $eimg = $em->getRepository('AppBundle:EquipmentImage')->findOneByImage($imageId);
        $img = $em->getRepository('AppBundle:Image')->find($imageId);
        $eq->removeImage($eimg);
        $em->remove($eimg);
        $em->getRepository('AppBundle:Image')->removeImage($img, $imageStorageDir);
        $em->remove($img);
        $em->flush();
        // set main image (if not exists)
        $sql = <<<EOT
update equipment_image
set main = 1
where equipment_id = {$equipmentId}
order by main desc, image_id asc
limit 1;
EOT;
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeUpdate($sql);        
        
        return $em->getRepository('AppBundle:Equipment')->getMainEquipmentImage($equipmentId);
    }
    public function getMainEquipmentImage($equipmentId) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ei', 'i')
            ->from('AppBundle:EquipmentImage', 'ei')
            ->join('ei.image', 'i')
            ->andWhere("ei.equipment = {$equipmentId}")
            ->andWhere("ei.main = 1");
        $q = $qb->getQuery();

        $eimg = null;
        try {
            $eimg = $q->getSingleResult();
        } catch (NoResultException $e) {}
        
        return $eimg;
    }
    
    /*
    public function getAll($categoryId = null) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $qb->select('e')
            ->from('AppBundle:Equipment', 'e')
            ->join('e.subcategory', 's');
        if ($categoryId != null) {
            $qb->andWhere("s.category = {$categoryId}");
        }
        
        $q = $qb->getQuery();
        return $q->getResult();
    }
    */
    public function getAll(SearchParams $params) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        /*
         * Please not this query uses "fetch join".
         * It fetches images and discounts (associated with equipments) immediately 
         * (instead of lazy loading them later).
         * Keep for optimum performance.
         */        
        $qb->select('e', 'i', 'd') // this line forces fetch join
            ->from('AppBundle:Equipment', 'e')
            ->join('e.subcategory', 's')
            ->join('e.user', 'u')
            ->leftJoin('e.images', 'i')
            ->leftJoin('e.discounts', 'd');
        
        $qb->andWhere("e.status = ". Equipment::STATUS_APPROVED);
        $qb->andWhere('u.status = '. User::STATUS_OK);
        
        if ($params->getCategoryId() != null) {
            $qb->andWhere("s.category = {$params->getCategoryId()}");
        }
        if ($params->getDiscount()) {
            $now = date('Y-m-d H:i:s');
            $qb->andWhere("d.createdAt <= '{$now}'")
                ->andWhere("d.expiresAt >= '{$now}'");
        }
        if ($params->getTestBuy()) {
            $qb->andWhere('e.priceBuy > 0');
        }
        
        
        if ($params->getSort() === 'date') {
            $qb->orderBy('e.createdAt', 'desc');
        }
        elseif ($params->getSort() === 'price') {
            $qb->orderBy ('e.price', 'asc');
        }
                
        $eqs = $qb->getQuery()->getResult();
        
        $repo = $this->getEntityManager()->getRepository('AppBundle:Equipment');
        
        foreach ($eqs as $eq) {
            $eq->setEquipmentImages($repo->getEquipmentImages($eq->getId()));
        }
        
        return $eqs;
    }
    public function getOne($equipmentId) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        /*
         * Please not this query uses "fetch join".
         * It fetches images and discounts (associated with equipments) immediately 
         * (instead of lazy loading them later).
         * Keep for optimum performance.
         */        
        $qb->select('e') // this line forces fetch join
            ->from('AppBundle:Equipment', 'e')
            ->join('e.user', 'u');
        
        $qb->andWhere("e.status = ". Equipment::STATUS_APPROVED);
        $qb->andWhere('u.status = '. User::STATUS_OK);
        $qb->andWhere("e.id = {$equipmentId}");
        
        $eq = null;
        try {
            $eq = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {}
        
        if ($eq !== null) {
            $repo = $this->getEntityManager()->getRepository('AppBundle:Equipment');
            $eq->setEquipmentImages($repo->getEquipmentImages($eq->getId()));
        }
        
        return $eq;
    }
    
    public function clearFeatures($equipmentId) {
        $sql = 'delete from AppBundle:EquipmentFeature ef where ef.equipment = :equipment';
        $q = $this->getEntityManager()->createQuery($sql);
        $q->setParameter(':equipment', $equipmentId);
        $q->execute();
    }
    public function saveFeatures($equipmentId, $features) {
        $this->clearFeatures($equipmentId);
        $em = $this->getEntityManager();
        foreach ($features as $id => $text) {
            $ef = new EquipmentFeature();
            $ef->setEquipment($em->getReference('AppBundle:Equipment', $equipmentId));
            $ef->setFeature($em->getReference('AppBundle:Feature', $id));
            $ef->setName($text);
            $em->persist($ef);
        }
        $em->flush();
        $em->clear();
    }
    public function getFeaturesAsArray($equipmentId) {
        $sql = "select * from equipment_feature where equipment_id = {$equipmentId}";
        $conn = $this->getEntityManager()->getConnection();
        $rows = $conn->query($sql)->fetchAll();
        $conn->close();
        
        $result = array();
        foreach ($rows as $row) {
            $result[$row['feature_id']] = $row['name'];
        }
        
        return $result;
    }
    public function getEquipmentFeatures($equipmentId) {
        /*
         * ef = equipment_feature
         * f = feature
         */
        $dql = <<<EOT
                select e, ef, f, fs
                from AppBundle:Equipment e
                    join e.features ef
                    join ef.feature f
                    join f.featureSection fs
                where e.id = :equipmentId
                order by fs.position asc, f.position asc
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(':equipmentId', $equipmentId);
        $eqs = $q->getResult();
        
        if (count($eqs) == 0) {
            return array();
        }
        
        $fts = array();
        $eq = $eqs[0];
        
        // iterate over features and assemble array        
        foreach ($eq->getFeatures() as $ef) {               // ef = equipment_feature
            $fs = $ef->getFeature()->getFeatureSection();   
            $name = $fs->getName();                         // feature_section name becomes array's key
            if (!array_key_exists($name, $fts)) {
                $fts[$name] = array();
            }
            if ($ef->getName() !== null) {                  // array of selected features becomes array's value
                $val = $ef->getName();
            } else {
                $val = $ef->getFeature()->getShortname();   
            }
            array_push($fts[$name], $val);
        }
        foreach ($fts as $key => $val) {                    // translate array of values into a string (comma-separated)
            $fts[$key] = implode(', ', $val);
        }
        return $fts;
    }

    /* score */
    public function addRating($equipmentRating) {
        $em = $this->getEntityManager();
        $em->persist($equipmentRating);
        $em->flush();
        $this->updateScore($equipmentRating->getEquipment()->getId());
    }
    public function updateScore($equipmentId) {
        $sql = <<<EOT
            update equipment
            set rating = (
                    select avg(rating)
                    from equipment_rating
                    where equipment_id = {$equipmentId}
            )
            where id = {$equipmentId}
EOT;
        $conn = $this->getEntityManager()->getConnection();
        $conn->exec($sql);
        $conn->close();        
    }
    
}
