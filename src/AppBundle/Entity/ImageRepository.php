<?php

namespace AppBundle\Entity;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * ImageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImageRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteById($id) {
        $sql = "delete from AppBundle:Image i where i.id = :id";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('id', $id);
        return $query->getResult();        
    }
    
    public function removeImage($image, $image_storage_dir) {
        if ($image != null) {
            /*$fullPath = sprintf("%s%s\\%s",
                $image_storage_dir,
                $image->getPath(),
                $image->getName());*/
            $fullPath = 
                $image_storage_dir .
                    DIRECTORY_SEPARATOR .
                    $image->getPath() .
                    DIRECTORY_SEPARATOR .
                    $image->getUuid() . '.' . $image->getExtension();
            
            $originalPath = $image_storage_dir .
                    DIRECTORY_SEPARATOR .
                    $image->getPath() .
                    DIRECTORY_SEPARATOR .
                    "original" .
                    DIRECTORY_SEPARATOR .
                    $image->getUuid() . '.' . $image->getExtension();
            
            
            $fs = new Filesystem();
            if (file_exists($fullPath)){
                $fs->remove($fullPath);
            }
            if (file_exists($originalPath)){
                $fs->remove($originalPath);
            }
            $em = $this->getEntityManager();

            $em->remove($image);
            $em->flush();
        }
    }
    
    public function removeAllImages($object, $image_storage_dir) {
        $oldImages = $object->getImages();
        
        foreach($oldImages as $ol){

            /*$fullPath = sprintf("%s%s\\%s",
                $image_storage_dir,
                $oldImage->getPath(),
                $oldImage->getName());*/
            $fullPath = 
                $image_storage_dir .
                    DIRECTORY_SEPARATOR .
                    $ol->getPath() .
                    DIRECTORY_SEPARATOR .
                    $ol->getUuid() . '.' . $ol->getExtension();

            $fs = new Filesystem();
            if (file_exists($fullPath)){
                $fs->remove($fullPath);
            }
            $em = $this->getEntityManager();

            $object->removeImage($ol); 
            $em->remove($ol);
            $em->flush();
        }
        
        
        return $object;
    }
}
