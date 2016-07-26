<?php

namespace AppBundle\Entity;

class TariffType {
    
    const EINZELSTUNDEN = 1;
    const GRUPPENSTUNDEN = 2;
    const TOUR = 3;
    const _5ERBLOCK = 5;
    const _10ERBLOCK = 6;
    const TAGESSATZ = 7;
    const _20ERBLOCK = 8;

    public static $EINZELSTUNDEN;
    public static $GRUPPENSTUNDEN;
    public static $TOUR;
    public static $_5ERBLOCK;
    public static $_10ERBLOCK;
    public static $TAGESSATZ;
    public static $_20ERBLOCK;
    
    private $id;
    private $name;
    
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
    
    
    public function getId() {
        return $this->id;
    }
    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }
    
    public static function init() {
        TariffType::$EINZELSTUNDEN = new TariffType(TariffType::EINZELSTUNDEN, 'Einzelstunden');
        TariffType::$GRUPPENSTUNDEN = new TariffType(TariffType::GRUPPENSTUNDEN, 'Gruppenstunden');
        TariffType::$TOUR = new TariffType(TariffType::TOUR, 'Tour');
        TariffType::$_5ERBLOCK = new TariffType(TariffType::_5ERBLOCK, '5erblock');
        TariffType::$_10ERBLOCK = new TariffType(TariffType::_10ERBLOCK, '10erblock');
        TariffType::$TAGESSATZ = new TariffType(TariffType::TAGESSATZ, 'Tagessatz');
        TariffType::$_20ERBLOCK = new TariffType(TariffType::_20ERBLOCK, '20erblock');
    }
    public static function getByType($type) {
        switch ($type) {
            case 1: return TariffType::$EINZELSTUNDEN;
            case 2: return TariffType::$GRUPPENSTUNDEN;
            case 3: return TariffType::$TOUR;
            case 5: return TariffType::$_5ERBLOCK;
            case 6: return TariffType::$_10ERBLOCK;
            case 7: return TariffType::$TAGESSATZ;
            case 8: return TariffType::$_20ERBLOCK;
        }
    }
    
    public static function getChoices() {
        return array(
            TariffType::$EINZELSTUNDEN->getId() => TariffType::$EINZELSTUNDEN->getName(),
            TariffType::$GRUPPENSTUNDEN->getId() => TariffType::$GRUPPENSTUNDEN->getName(),
            TariffType::$TOUR->getId() => TariffType::$TOUR->getName(),
            TariffType::$_5ERBLOCK->getId() => TariffType::$_5ERBLOCK->getName(),
            TariffType::$_10ERBLOCK->getId() => TariffType::$_10ERBLOCK->getName(),
            TariffType::$_20ERBLOCK->getId() => TariffType::$_20ERBLOCK->getName(),
            TariffType::$TAGESSATZ->getId() => TariffType::$TAGESSATZ->getName()
        );
        
    }
}

TariffType::init();
