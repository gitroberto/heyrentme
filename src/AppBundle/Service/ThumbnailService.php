<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Exception\Exception;

class ThumbnailService {

    protected $em;
    protected $logger;
    protected $imageStorageDir;    
    
    public function __construct(EntityManager $em, Logger $logger, array $parameters) {
        $this->em = $em;
        $this->logger = $logger;
        $this->imageStorageDir = $parameters['image_storage_dir'];
    }
    
    public function run() {
        $sep = DIRECTORY_SEPARATOR;
        
        // equipment
        $eis = $this->em->getRepository("AppBundle:Equipment")->getAllThumbnailless();
        foreach ($eis as $ei) { // EquipmentImage objects            
            try {
                $img = $ei->getImage();
                $ext = $img->getExtension();
                $path = $this->imageStorageDir . $sep . "equipment" . $sep . $img->getUuid() . "." . $ext;
                $path2 = $this->imageStorageDir . $sep . "equipment" . $sep . "thumbnail" . $sep . $img->getUuid() . "." . $ext;
                
                $fs = new Filesystem();
                if (!$fs->exists($path)) {
                    continue;
                }
                
                $size = getimagesize($path);
                $w = $size[0];
                $h = $size[1];
                $nw = 360;
                if ($ei->getMain() === 1) {
                    $nh = 270;
                }
                else {
                    $nh = $h / $w * $nw;
                }
                
                $msg = "generating thumbnail for image id={$img->getId()}, size: {$w}x{$h} to {$nw}x{$nh}, path: {$path} to {$path2}";
                $this->logger->debug($msg);
                        
                $src = imagecreatefromstring(file_get_contents($path));
                $dst = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    imagejpeg($dst, $path2, 85);
                }
                else if ($ext === 'png') {
                    imagepng($dst, $path2, 9);
                }        

                imagedestroy($dst);        
                imagedestroy($src);
                
                $img->setThumbnailPath("equipment" . $sep . "thumbnail");
                $this->em->flush();                
            } catch (Exception $ex) {
                $msg = "ERROR generating thumbnail for image id={$ei->getImage()->getId()}";
                $this->logger->error($msg);
                $this->logger->error($ex->getTraceAsString());
            }
        }
        
        // talent
        $eis = $this->em->getRepository("AppBundle:Talent")->getAllThumbnailless();
        foreach ($eis as $ei) { // TalentImage objects            
            try {
                $img = $ei->getImage();
                $ext = $img->getExtension();
                $path = $this->imageStorageDir . $sep . "talent" . $sep . $img->getUuid() . "." . $ext;
                $path2 = $this->imageStorageDir . $sep . "talent" . $sep . "thumbnail" . $sep . $img->getUuid() . "." . $ext;
                
                $fs = new Filesystem();
                if (!$fs->exists($path)) {
                    continue;
                }

                $size = getimagesize($path);
                $w = $size[0];
                $h = $size[1];
                $nw = 360;
                if ($ei->getMain() === 1) {
                    $nh = 270;
                }
                else {
                    $nh = $h / $w * $nw;
                }
                
                $msg = "generating thumbnail for image id={$img->getId()}, size: {$w}x{$h} to {$nw}x{$nh}, path: {$path} to {$path2}";
                $this->logger->debug($msg);
                        
                $src = imagecreatefromstring(file_get_contents($path));
                $dst = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    imagejpeg($dst, $path2, 85);
                }
                else if ($ext === 'png') {
                    imagepng($dst, $path2, 9);
                }        

                imagedestroy($dst);        
                imagedestroy($src);
                
                $img->setThumbnailPath("talent" . $sep . "thumbnail");
                $this->em->flush();                
            } catch (Exception $ex) {
                $msg = "ERROR generating thumbnail for image id={$ei->getImage()->getId()}";
                $this->logger->error($msg);
                $this->logger->error($ex->getTraceAsString());
            }
        }
        
        // blog
        $blogs = $this->em->getRepository("AppBundle:Blog")->getAllThumbnailless();
        foreach ($blogs as $blog) { // TalentImage objects            
            try {
                $img = $blog->getImage();
                $ext = $img->getExtension();
                $path = $this->imageStorageDir . $sep . "blog" . $sep . $img->getUuid() . "." . $ext;
                $path2 = $this->imageStorageDir . $sep . "blog" . $sep . "thumbnail" . $sep . $img->getUuid() . "." . $ext;
                
                $fs = new Filesystem();
                if (!$fs->exists($path)) {
                    continue;
                }

                $size = getimagesize($path);
                $w = $size[0];
                $h = $size[1];
                $nw = 360;
                $nh = 270;
                
                $msg = "generating thumbnail for image id={$img->getId()}, size: {$w}x{$h} to {$nw}x{$nh}, path: {$path} to {$path2}";
                $this->logger->debug($msg);
                        
                $src = imagecreatefromstring(file_get_contents($path));
                $dst = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    imagejpeg($dst, $path2, 85);
                }
                else if ($ext === 'png') {
                    imagepng($dst, $path2, 9);
                }        

                imagedestroy($dst);        
                imagedestroy($src);
                
                $img->setThumbnailPath("blog" . $sep . "thumbnail");
                $this->em->flush();                
            } catch (Exception $ex) {
                $msg = "ERROR generating thumbnail for image id={$ei->getImage()->getId()}";
                $this->logger->error($msg);
                $this->logger->error($ex->getTraceAsString());
            }
        }
        
    }

}