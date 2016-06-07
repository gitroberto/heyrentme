<?php

namespace AppBundle\Entity;

use AppBundle\Utils\SearchParams;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use AppBundle\ViewModel\Admin\EventNode;
use AppBundle\ViewModel\Admin\LogEvent;

/**
 * TalentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TalentRepository extends EntityRepository
{
    public function getAllBySubcategory($subcategoryId) {
        $sql = "select e from AppBundle:Talent e where e.subcategory = :subcategoryId and e.status = :approved";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('subcategoryId', $subcategoryId);
        $query->setParameter('approved', Talent::STATUS_APPROVED);
        return $query->getResult();        
    }   
    
    public function getAllForOtherCategories($subcategoryId) {
        $sql = "select e from AppBundle:Talent e where e.subcategory != :subcategoryId and e.status = :approved";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('subcategoryId', $subcategoryId);
        $query->setParameter('approved', Talent::STATUS_APPROVED);
        return $query->setMaxResults(4)->getResult();        
    }   
    
    public function getSamplePreviewTalentsBySubcategory($subcategoryId, $eqId) {
        #TODO: Correct query, remove hardcoded number of items
        // TODO: refactor: write query with proper "order by" and take first four items
        //TODO: add user status = ok 
        $sql = "select e from AppBundle:Talent e where e.subcategory = :subcategoryId and e.id != :id and e.status = :approved";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('subcategoryId', $subcategoryId);
        $query->setParameter('id', $eqId);
        $query->setParameter('approved', Talent::STATUS_APPROVED);        
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
            ->from('AppBundle:Talent', 'e')
            ->join('e.user', 'u');
        $qb->andWhere("u.id = {$userId}");

        $eqs = $qb->getQuery()->getResult();
        
        $repo = $this->getEntityManager()->getRepository('AppBundle:Talent');
        
        foreach ($eqs as $eq) {
            $eq->setTalentImages($repo->getTalentImages($eq->getId()));
        }
        
        return $eqs;
    }
    
    public function getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $sStatus) {
        // count
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(e.id)')
            ->from('AppBundle:Talent', 'e');
        $this->gridOverviewParams($qb, $sStatus);
        $count = $qb->getQuery()->getSingleScalarResult();
        
        // result
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e, u, s, c')
            ->from('AppBundle:Talent', 'e')
            ->leftJoin('e.subcategory', 's')
            ->leftJoin('s.category', 'c')
            ->leftJoin('e.user', 'u');
        $this->gridOverviewParams($qb, $sStatus);
        
        // sort by
        if (!empty($sortColumn)) {
            $qb->orderBy($sortColumn, $sortDirection);
        }

        // page and page size
        if (!empty($pageSize)) {
            $qb->setMaxResults($pageSize);
        }
        if (!empty($page) && $page != 1) {
            $qb->setFirstResult(($page - 1) * $pageSize);
        }
        $rows = $qb->getQuery()->getResult();
        
        // add stats
        $stats = $this->gridOverviewStats($rows);
        
        return array('count' => $count, 'rows' => $rows, 'stats' => $stats);
    }
    private function gridOverviewParams($qb, $sStatus) {
        if (!empty($sStatus)) {
            $qb->andWhere($qb->expr()->eq('e.status', ':status'));
            $qb->setParameter('status', $sStatus);
        }
    }
    private function gridOverviewStats($talents) {
        $ids = array();
        foreach ($talents as $eq) {
            array_push($ids, $eq->getId());
        }
        
        // query
        //<editor-fold>
        $sql = <<<EOT
select e.id, t1.questions, t2.bookings, t3.cancels, t4.revenue, t5.discount
from
	talent e
	left join (
		select talent_id, count(*) as questions
		from talent_question
		group by talent_id
	) as t1 on e.id = t1.talent_id
	left join (
		select ei.talent_id, count(*) as bookings
		from talent_booking eb
			inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
		group by ei.talent_id
	) as t2 on e.id = t2.talent_id
	left join (
		select ei.talent_id, count(*) as cancels
		from talent_booking_cancel ebc
			inner join talent_booking eb on ebc.talent_booking_id = eb.id
			inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
		group by ei.talent_id
	) as t3 on e.id = t3.talent_id
	left join (
		select ei.talent_id, sum(ei.price) as revenue
		from talent_booking eb
			inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
		where eb.status not in (2, 3) -- not cancelled
		group by ei.talent_id
	) as t4 on e.id = t4.talent_id
	left join (
		select ei.talent_id, count(*) * 5.0 as discount
		from talent_booking eb
			inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
		where eb.status not in (2, 3) -- not cancelled
			and eb.discount_code_id is not null
		group by ei.talent_id
	) as t5 on e.id = t5.talent_id
where e.id in (#IDS#)
EOT;
        $sql = str_replace("#IDS#", implode(", ", $ids), $sql);
        //</editor-fold>
        
        $conn = $this->getEntityManager()->getConnection();
        $rows = $conn->executeQuery($sql)->fetchAll();
        
        $result = array();
        foreach ($rows as $row) {
            $id = $row['id'];
            $result[$id] = $row;
        }

        return $result;
    }    
    public function countAll() {
        return $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getImageCount($talentId) {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('count(ei.image)')
            ->from('AppBundle:TalentImage', 'ei')
            ->andWhere("ei.talent = {$talentId}")
            ->getQuery()
            ->getSingleScalarResult();            
    }
 
    public function getTalentMainImage($talentId) { // only main image, return: image or null
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ei', 'i')
            ->from('AppBundle:TalentImage', 'ei')
            ->join('ei.image', 'i')
            ->andWhere("ei.talent = {$talentId}")
            ->andWhere('ei.main = 1');

        $ei = null;
        try {
            $ei = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {}
        
        return $ei;
    }
    public function getTalentButMainImages($talentId) { // all except main
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ei', 'i')
            ->from('AppBundle:TalentImage', 'ei')
            ->join('ei.image', 'i')
            ->andWhere("ei.talent = {$talentId}")
            ->andWhere('ei.main = 0')
            ->addOrderBy('i.id');

        return $qb->getQuery()->getResult();
    }
    public function getTalentButMainImageCount($talentId) { // all except main
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(ei.image)')
            ->from('AppBundle:TalentImage', 'ei')
            ->join('ei.image', 'i')
            ->andWhere("ei.talent = {$talentId}")
            ->andWhere('ei.main = 0');

        return $qb->getQuery()->getSingleScalarResult();
    }
    public function getTalentImages($talentId) {
        // main first
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ei', 'i')
            ->from('AppBundle:TalentImage', 'ei')
            ->join('ei.image', 'i')
            ->andWhere("ei.talent = {$talentId}")
            ->addOrderBy('ei.main', 'desc')
            ->addOrderBy('i.id');

        $q = $qb->getQuery();
        
        return $q->getResult();        
    }
    public function setMainImage($talentId, $imageId) {
        $sql = <<<EOT
update talent_image
set main = case when image_id = {$imageId} then 1 else 0 end
where talent_id = {$talentId}
EOT;
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeUpdate($sql);        
    }
    public function removeImage($talentId, $imageId, $imageStorageDir) {
        $em = $this->getEntityManager();
        $eq = $em->getRepository('AppBundle:Talent')->find($talentId);        
        $eimg = $em->getRepository('AppBundle:TalentImage')->findOneByImage($imageId);
        $img = $em->getRepository('AppBundle:Image')->find($imageId);
        $eq->removeImage($eimg);
        $em->remove($eimg);
        $em->getRepository('AppBundle:Image')->removeImage($img, $imageStorageDir);
        $em->remove($img);
        $em->flush();
    }
    public function getMainTalentImage($talentId) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ei', 'i')
            ->from('AppBundle:TalentImage', 'ei')
            ->join('ei.image', 'i')
            ->andWhere("ei.talent = {$talentId}")
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
            ->from('AppBundle:Talent', 'e')
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
         * It fetches images and discounts (associated with talents) immediately 
         * (instead of lazy loading them later).
         * Keep for optimum performance.
         */        
        $qb->select('e', 'i'/*, 'd'*/) // this line forces fetch join
            ->from('AppBundle:Talent', 'e')
            ->join('e.subcategory', 's')
            ->join('e.user', 'u')
            ->leftJoin('e.images', 'i')/*
            ->leftJoin('e.discounts', 'd')*/;
        
        $qb->andWhere("e.status = ". Talent::STATUS_APPROVED);
        
        if ($params->getCategoryId() != null) {
            $qb->andWhere("s.category = {$params->getCategoryId()}");
        }
        /*
        if ($params->getDiscount()) {
            $now = date('Y-m-d H:i:s');
            $qb->andWhere("d.createdAt <= '{$now}'")
                ->andWhere("d.expiresAt >= '{$now}'");
        }
        if ($params->getTestBuy()) {
            $qb->andWhere('e.priceBuy > 0');
        }
         */
        $qb->andWhere('u.status = '. User::STATUS_OK);
        
        if ($params->getSort() === 'date') {
            $qb->orderBy('e.createdAt', 'desc');
        }
        elseif ($params->getSort() === 'price') {
            $qb->orderBy ('e.price', 'asc');
        }
        
        $eqs = $qb->getQuery()->getResult();
        
        $repo = $this->getEntityManager()->getRepository('AppBundle:Talent');
        
        foreach ($eqs as $eq) {
            $eq->setTalentImages($repo->getTalentImages($eq->getId()));
        }
        
        return $eqs;
    }
    public function getOne($talentId) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        /*
         * Please not this query uses "fetch join".
         * It fetches images and discounts (associated with talents) immediately 
         * (instead of lazy loading them later).
         * Keep for optimum performance.
         */        
        $qb->select('e') // this line forces fetch join
            ->from('AppBundle:Talent', 'e')
            ->join('e.user', 'u');
        
        //$qb->andWhere("e.status = ". Talent::STATUS_APPROVED);
        //$qb->andWhere('u.status = '. User::STATUS_OK);
        $qb->andWhere("e.id = {$talentId}");
        
        $eq = null;
        try {
            $eq = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {}
        
        if ($eq !== null) {
            $repo = $this->getEntityManager()->getRepository('AppBundle:Talent');
            $eq->setTalentImages($repo->getTalentImages($eq->getId()));
        }
        
        return $eq;
    }
    public function getOneByUuid($uuid) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        /*
         * Please not this query uses "fetch join".
         * It fetches images and discounts (associated with talents) immediately 
         * (instead of lazy loading them later).
         * Keep for optimum performance.
         */        
        $qb->select('t') // this line forces fetch join
            ->from('AppBundle:Talent', 't');      
        
        //$qb->andWhere("e.status = ". Talent::STATUS_APPROVED);
        //$qb->andWhere('u.status = '. User::STATUS_OK);
        $qb->andWhere($qb->expr()->eq('t.uuid', ':uuid'));
        
        $eq = null;
        try {
            $q = $qb->getQuery();
            $q->setParameter(':uuid', "{$uuid}");
            $eq = $q->getSingleResult();
        } catch (NoResultException $e) {}
        
        if ($eq !== null) {
            $repo = $this->getEntityManager()->getRepository('AppBundle:Talent');
            $eq->setTalentImages($repo->getTalentImages($eq->getId()));
        }
        
        return $eq;
    }
    public function clearFeatures($talentId) {
        $sql = 'delete from AppBundle:TalentFeature ef where ef.talent = :talent';
        $q = $this->getEntityManager()->createQuery($sql);
        $q->setParameter(':talent', $talentId);
        $q->execute();
    }
    public function saveFeatures($talentId, $features) {
        $this->clearFeatures($talentId);
        $em = $this->getEntityManager();
        foreach ($features as $id => $text) {
            $ef = new TalentFeature();
            $ef->setTalent($em->getReference('AppBundle:Talent', $talentId));
            $ef->setFeature($em->getReference('AppBundle:Feature', $id));
            $ef->setName($text);
            $em->persist($ef);
        }
        $em->flush();
        $em->clear();
    }
    public function getFeaturesAsArray($talentId) {
        $sql = "select * from talent_feature where talent_id = {$talentId}";
        $conn = $this->getEntityManager()->getConnection();
        $rows = $conn->query($sql)->fetchAll();
        $conn->close();
        
        $result = array();
        foreach ($rows as $row) {
            $result[$row['feature_id']] = $row['name'];
        }
        
        return $result;
    }
    public function getTalentFeatures($talentId) {
        /*
         * ef = talent_feature
         * f = feature
         */
        $dql = <<<EOT
                select e, ef, f, fs
                from AppBundle:Talent e
                    join e.features ef
                    join ef.feature f
                    join f.featureSection fs
                where e.id = :talentId
                order by fs.position asc, f.position asc
EOT;
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(':talentId', $talentId);
        $eqs = $q->getResult();
        
        if (count($eqs) == 0) {
            return array();
        }
        
        $fts = array();
        $eq = $eqs[0];
        
        // iterate over features and assemble array        
        foreach ($eq->getFeatures() as $ef) {               // ef = talent_feature
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

    public function talentModified($talentId) {
        // todo: possibly call a service to send notification
        $talent = $this->getEntityManager()->getRepository('AppBundle:Talent')->find($talentId);
        $status = $talent->getStatus();
        $changed = false;
        
        if ($status === Talent::STATUS_APPROVED || $status === Talent::STATUS_REJECTED) {
            $talent->setStatus(Talent::STATUS_MODIFIED);
            $talent->setShowcaseStart(0);
            $talent->setShowcaseTalent(0);
            $this->getEntityManager()->flush();
            $changed = true;
        }
        
        return $changed;
    }
    /* score */
    public function addRating($talentRating) {
        $em = $this->getEntityManager();
        $em->persist($talentRating);
        $em->flush();
        $this->updateScore($talentRating->getTalent()->getId());
    }
    public function updateScore($talentId) {
        $sql = <<<EOT
            update talent
            set rating = (
                    select avg(rating)
                    from talent_rating
                    where talent_id = {$talentId}
            )
            where id = {$talentId}
EOT;
        $conn = $this->getEntityManager()->getConnection();
        $conn->exec($sql);
        $conn->close();        
    }
    
    public function getAllThumbnailless() {
        $sql = <<<EOT
            select ei, i
            from AppBundle:TalentImage ei
                join ei.image i
            where i.thumbnailPath is null
EOT;
        return $this->getEntityManager()->createQuery($sql)->getResult();
    }
    
    public function getAllWithoutUuid() {
        $sql = <<<EOT
            select t
            from AppBundle:Talent t                
            where t.uuid is null
EOT;
        return $this->getEntityManager()->createQuery($sql)->getResult();
    }

    public function getTalentLog($talentId) {

        $eventNodes = array();
        
        // inquiries
        //<editor-fold>        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i', 'b', 'u', 'c', 'r', 'ur', 'dc')
            ->from('AppBundle:TalentInquiry', 'i')
            ->leftJoin('i.booking', 'b')
            ->leftJoin('i.user', 'u')
            ->leftJoin('b.cancels', 'c')
            ->leftJoin('b.rating', 'r')
            ->leftJoin('b.userRating', 'ur')
            ->leftJoin('b.discountCode', 'dc')
            ->where('i.talent = :talentId')
            ->setParameter('talentId', $talentId);
        
        $rows = $qb->getQuery()->getResult();
        
        
        foreach ($rows as $inq) {
            $user = $inq->getUser();
            $node = new EventNode();
            $node->name = "Inquiry";
            $node->desc = sprintf("%s (%s %s, id: %d)", $user->getEmail(), $user->getName(), $user->getSurname(), $user->getid());
            
            $ev = new LogEvent();
            $ev->date = $inq->getCreatedAt();
            $ev->status = "Inquiry";
            $ev->desc1 = $inq->getDescAsStr();
            $node->addEvent($ev);
            
            if ($inq->getResponse() !== null) {
                $ev = new LogEvent();
                $ev->date = $inq->getModifiedAt();                
                $ev->status = "Resp. " . ($inq->getAccepted() ? "Accept" : "Reject");
                $ev->desc1 = $inq->getResponse();
                $node->addEvent($ev);
                $node->name =  $inq->getAccepted() ? "Accepted" : "Rejected";
            }
            
            $bk = $inq->getBooking();
            if ($bk !== null) {
                $dc = $bk->getDiscountCode();
                
                $ev = new LogEvent();
                $ev->date = $bk->getCreatedAt();
                $ev->status = "Booking";
                if ($dc !== null)
                    $ev->desc1 = "Discount code: <span style=\"background-color: #faa; padding: 0 4px;\">{$dc->getCode()} (id: {$dc->getId()})</span>";
                else
                    $ev->desc1 = "Discount code: -";
                $node->addEvent($ev);
                $node->name = "Booked";
                
                $cancels = $bk->getCancels();
                
                foreach ($cancels as $cancel) {
                    $ev = new LogEvent();
                    $ev->date = $cancel->getCreatedAt();
                    $ev->status = $cancel->getProvider() === 1 ? 'Cancelled by provider' : 'Cancelled by user';
                    $ev->desc1 = "{$cancel->getReason()}: {$cancel->getDescription()}";
                    $node->addEvent($ev);
                    $node->name = "Cancelled";
                }
                
                $rating = $bk->getRating();
                if ($rating !== null) {
                    $ev = new LogEvent();
                    $ev->date = $rating->getCreatedAt();
                    $ev->status = "Equip. rating";
                    $ev->desc1 = sprintf("%.1f: %s", $rating->getRating(), $rating->getOpinion());
                    $node->addEvent($ev);
                }
                
                $userRating = $bk->getUserRating();
                if ($userRating !== null) {
                    $ev = new LogEvent();
                    $ev->date = $userRating->getCreatedAt();
                    $ev->status = "User rating";
                    $ev->desc1 = sprintf("%.1f: %s", $userRating->getRating(), $userRating->getOpinion());
                    $node->addEvent($ev);
                }
            }            
                        
            array_push($eventNodes, $node);
        }
        //</editor-fold>
        
        // questions
        //<editor-fold>        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('q', 'u')
            ->from('AppBundle:TalentQuestion', 'q')
            ->leftJoin('q.user', 'u')
            ->where('q.talent = :talentId')
            ->setParameter('talentId', $talentId);
        
        $rows = $qb->getQuery()->getResult();
                
        foreach ($rows as $quest) {
            $user = $quest->getUser();
            $node = new EventNode();
            $node->name = "Question";
            if ($user !== null)
                $node->desc = sprintf("%s (%s %s, id: %d)", $user->getEmail(), $user->getName(), $user->getSurname(), $user->getid());
            else 
                $node->desc = sprintf("%s (%s)", $quest->getEmail(), $quest->getName());
            
            $ev = new LogEvent();
            $ev->date = $quest->getCreatedAt();
            $ev->status = "Question";
            $ev->desc1 = $quest->getMessage();
            $node->addEvent($ev);
            
            if ($quest->getReply() !== null) {
                $ev = new LogEvent();
                $ev->date = $quest->getModifiedAt();                
                $ev->status = "Reply";
                $ev->desc1 = $quest->getReply();
                $node->addEvent($ev);
            }
                        
            array_push($eventNodes, $node);
        }
        //</editor-fold>
        
        usort($eventNodes, "AppBundle\\ViewModel\\Admin\\EventNode::cmp");
        $eventNodes = array_reverse($eventNodes);
        
        return $eventNodes;
    }

    public function delete($id, $imageStorageDir) {
        $em = $this->getEntityManager();
        $eq = $em->find('AppBundle:Talent', $id);

        // remove images
        //<editor-fold>
        $imgRepo = $em->getRepository('AppBundle:Image');
        
        $eimgs = $eq->getImages();
        foreach ($eimgs as $eimg) {
            $img = $eimg->getImage();
            $eq->removeImage($eimg);
            $em->remove($eimg);
            $imgRepo->removeImage($img, $imageStorageDir);
        }
        $em->flush();
        //</editor-fold>

        // remove related objects and equipment itself
        //<editor-fold>        
        $sql = <<<EOT
    delete ebc
    from talent_booking_cancel ebc
        inner join talent_booking eb on ebc.talent_booking_id = eb.id
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
    where ei.talent_id = {$id};

    delete from talent_rating where talent_id = {$id};

    delete ur
    from user_rating ur
        inner join talent_booking eb on ur.talent_booking_id = eb.id
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id         
    where ei.talent_id = {$id};

    delete eb
    from talent_booking eb
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
    where ei.talent_id = {$id};
    
    delete from talent_inquiry where talent_id = {$id};
        
    delete from talent_question where talent_id = {$id};
        
    delete from talent where id = {$id};        
EOT;
    
        $em->getConnection()->executeUpdate($sql);
        //</editor-fold>
    }
    /* ------------------------------------------------------------------------- showcase */
    public function getShowcaseStartCount() {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('count(t.id)')
            ->from('AppBundle:Talent', 't')
            ->andWhere('t.showcaseStart = 1')
            ->andWhere('t.status = :status')
            ->setParameter('status', Equipment::STATUS_APPROVED)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getShowcaseStart() {
        $tals = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('AppBundle:Talent', 't')
            ->andWhere('t.showcaseStart = 1')
            ->andWhere('t.status = :status')
            ->setParameter('status', Equipment::STATUS_APPROVED)
            ->addOrderBy('t.createdAt', 'desc')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        foreach ($tals as $tal)
            $tal->setTalentImages($this->getTalentImages($tal->getId()));
        
        return $tals;
    }    
    public function getShowcaseTalentCount() {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('count(t.id)')
            ->from('AppBundle:Talent', 't')
            ->andWhere('t.showcaseTalent = 1')
            ->andWhere('t.status = :status')
            ->setParameter('status', Equipment::STATUS_APPROVED)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getShowcaseTalent() {
        $tals = $this->getEntityManager()->createQueryBuilder()
            ->select('t', 'i')
            ->from('AppBundle:Talent', 't')
            ->leftJoin('t.images', 'i')
            ->andWhere('t.showcaseTalent = 1')
            ->andWhere('t.status = :status')
            ->setParameter('status', Equipment::STATUS_APPROVED)
            ->addOrderBy('t.createdAt', 'desc')
            ->getQuery()
            ->getResult();

        foreach ($tals as $tal)
            $tal->setTalentImages($this->getTalentImages($tal->getId()));
        
        return $tals;
    }

    /* -------------------------------------------------------------- Tariffs */
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
        $q = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from('AppBundle:TalentTariff', 't')
                ->andWhere('t.talent = :talentId')
                ->setParameter('talentId', $talentId)
                ->addOrderBy('t.position', 'asc')
                ->getQuery();
        $rows = $q->getResult();
        if (count($rows) === 1)
            return $rows[0];
        else 
            return null;
    }
    
}

