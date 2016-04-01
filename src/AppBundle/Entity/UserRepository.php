<?php

namespace AppBundle\Entity;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $email, $name, $surname, $status) {
        // count
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(u.id)')
            ->from('AppBundle:User', 'u');
        $this->gridOverviewParams($qb, $email, $name, $surname, $status);
        $count = $qb->getQuery()->getSingleScalarResult();
        
        // rows
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u')
            ->from('AppBundle:User', 'u');   
        // where
        $this->gridOverviewParams($qb, $email, $name, $surname, $status);
        
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
        
        return array('count' => $count, 'rows' => $rows);
    }
    private function gridOverviewParams($qb, $email, $name, $surname, $status) {
        if (!empty($email)) {
            $qb->andWhere($qb->expr()->like('u.username', ':email'));
            $qb->setParameter(':email', "%{$email}%");
        }
        if (!empty($name)) {
            $qb->andWhere($qb->expr()->like('u.name', ':name'));
            $qb->setParameter(':name', "%{$name}%");
        }
        if (!empty($surname)) {
            $qb->andWhere($qb->expr()->like('u.surname', ':surname'));
            $qb->setParameter(':surname', "%{$surname}%");
        }        
        if (!empty($status)) {
            $qb->andWhere($qb->expr()->eq('u.status', ':status'));
            $qb->setParameter(':status', $status);
        }
    }
    
    
    public function countAll() {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }   

    /* score */
    public function addRating($userRating) {
        $em = $this->getEntityManager();
        $em->persist($userRating);
        $em->flush();
        $this->updateScore($userRating->getUser()->getId());
    }
    public function updateScore($userId) {
        $sql = <<<EOT
            update fos_user
            set rating = (
                    select avg(rating)
                    from user_rating
                    where user_id = {$userId}
            )
            where id = {$userId}
EOT;
        $conn = $this->getEntityManager()->getConnection();
        $conn->exec($sql);
        $conn->close();        
    }
    
    public function getAllForWelcomeEmails() {
        $qb = $this->getEntityManager()->createQueryBuilder();        
        $qb->select('u')
            ->from('AppBundle:User', 'u');   
        
        $qb->orWhere('u.secondDayEmailSentAt is null');
        $qb->orWhere('u.thirdDayEmailSentAt is null');
                
        return $qb->getQuery()->getResult();        
    }
    public function getAllForDropdown() {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.status = :status')                
            ->setParameter('status', User::STATUS_OK)
            ->addOrderBy('u.email');
        $rows = $qb->getQuery()->getResult();
        
        $arr = array();
        foreach($rows as $u) {
            $arr[$u->getId()] = "{$u->getEmail()} ({$u->getName()} {$u->getSurname()})";
        }
        return $arr;
    }
    
    public function deleteUserAccount($user, $folder){
        // todo: adjust query
        $id = $user->getId();
        $m = $this->getEntityManager();        
        
        $equipments = $m->getRepository('AppBundle:Equipment')->getAllByUserId($user->getId());
        $talents = $m->getRepository('AppBundle:Talent')->getAllByUserId($user->getId());
        
        $image = $user->getImage();
        
        $user->setImage(null);        
        $this->RemoveAndDeleteRelatedImage($m, $image, $folder); // todo: why not Image::removeImage?
        $m->flush();
        
        foreach ($equipments as $eq) {
            foreach ($eq->getImages() as $ei) {
                $i = $ei->getImage();                
                $eq->removeImage($ei);                
                $m->remove($ei);
                $m->flush();
                $this->RemoveAndDeleteRelatedImage($m, $i, $folder);
            }
        }
        
        foreach ($talents as $tal) {
            foreach ($tal->getImages() as $ti) {
                
                $i = $ti->getImage();
                $tal->removeImage($ti);
                $m->remove($ti);
                $m->flush();
                $this->RemoveAndDeleteRelatedImage($m, $i, $folder);
                           
            }
        }
        
        $sql = <<<EOT
    delete from user_rating where user_id= {$id};
        
    delete ebc
    from equipment_booking_cancel ebc
        inner join equipment_booking eb on ebc.booking_id = eb.id
        inner join equipment_inquiry ei on eb.inquiry_id = ei.id
        inner join equipment e
    where e.user_id = {$id};

    delete er
    from equipment_rating er
        inner join equipment_booking eb on er.booking_id = eb.id
        inner join equipment_inquiry ei on eb.inquiry_id = ei.id
        inner join equipment e
    where e.user_id = {$id} or ei.user_id = {$id};

    delete ur
    from user_rating ur
            inner join equipment_booking eb on ur.booking_id = eb.id
            inner join equipment_inquiry ei on eb.inquiry_id = ei.id         
            inner join equipment e on ei.equipment_id = e.id     
    where e.user_id = {$id} or ei.user_id = {$id};

   delete eb
    from equipment_booking eb
        inner join equipment_inquiry ei on eb.inquiry_id = ei.id
        inner join equipment e on ei.equipment_id = e.id
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ei
    from equipment_inquiry ei
        inner join equipment e on ei.equipment_id = e.id
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ei
    from equipment_image ei
        inner join equipment e on ei.equipment_id = e.id
    where e.user_id = {$id};
    
    delete er
    from equipment_rating er
        inner join equipment e on er.equipment_id = e.id
    where e.user_id = {$id};
    
    delete eq
    from equipment_question eq
        inner join equipment e on eq.equipment_id = e.id
    where e.user_id = {$id};
    
    delete from equipment_question where user_id = {$id};
    
    delete from equipment where user_id = {$id};
    
    delete ebc
    from talent_booking_cancel ebc
        inner join talent_booking eb on ebc.talent_booking_id = eb.id
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
        inner join talent e
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete er
    from talent_rating er
        inner join talent_booking eb on er.booking_id = eb.id
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
        inner join talent e
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ur
    from user_rating ur
        inner join talent_booking eb on ur.talent_booking_id = eb.id
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id         
        inner join talent e on ei.talent_id = e.id     
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete eb
    from talent_booking eb
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
        inner join talent e on ei.talent_id = e.id
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ei
    from talent_inquiry ei
        inner join talent e on ei.talent_id = e.id
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ei
    from talent_image ei
        inner join talent e on ei.talent_id = e.id
    where e.user_id = {$id};
    
    delete er
    from talent_rating er
        inner join talent e on er.talent_id = e.id
    where e.user_id = {$id};
    
    delete tq
    from talent_question tq
        inner join talent tal on tq.talent_id = tal.id
    where tal.user_id = {$id};
    
    delete from talent_question where user_id = {$id};

   delete from talent where user_id = {$id};
    
    delete from equipment_booking_cancel where user_id = {$id};
    delete from talent_booking_cancel where user_id = {$id};
    delete from equipment_inquiry where user_id = {$id};
    delete from talent_inquiry where user_id = {$id};
    delete from discount_code where user_id = {$id};
    delete from user_rating where user_id = {$id};
    delete from fos_user where id = {$id};
EOT;
        
        
        $conn = $m->getConnection();
        $conn->executeUpdate($sql);
        //$this->get('monolog.logger.artur')->debug($sql);
    }
    
    public function RemoveAndDeleteRelatedImage($manager, $image, $folder){
        if ($image){
            $manager->getRepository('AppBundle:Image')->removeImage($image, $folder);
            $manager->getRepository('AppBundle:Image')->deleteById($image->getId());
        }    
    }
}
