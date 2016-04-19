<?php

namespace AppBundle\Utils;

use Cocur\Slugify\Slugify;
use DateTime;
use AppBundle\Entity\DiscountCode;

class Utils {
    
    private static $slugifier;
    
    static function init() {
        self::$slugifier = new Slugify();
    }

    public static function getUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
    
    public static function slugify($s) {
        return self::$slugifier->slugify($s);
    }
    
    public static function checkExpiresDateOfDiscountCode($dcode){
        $now = new DateTime();
        $expiresDate = $dcode->getExpiresAt();
        $interval = $now->diff($expiresDate);
        //invert = 1 if value is negative, otherwise invert = 0;
        return $interval->invert === 1; 
    }
}

Utils::init();
