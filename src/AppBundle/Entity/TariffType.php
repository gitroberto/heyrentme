<?php

namespace AppBundle\Entity;

class TariffType {
    
    const EINZELSTUNDEN = 1;
    const GRUPPENSTUNDEN = 2;
    const WORKSHOP = 3;
    const PERFORMANCE = 4;
    const _5ERBLOCK = 5;
    const _10ERBLOCK = 6;
    const TAGESSATZ = 7;

    public static $EINZELSTUNDEN;
    public static $GRUPPENSTUNDEN;
    public static $WORKSHOP;
    public static $PERFORMANCE;
    public static $_5ERBLOCK;
    public static $_10ERBLOCK;
    public static $TAGESSATZ;
    
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
        TariffType::$WORKSHOP = new TariffType(TariffType::WORKSHOP, 'Workshop');
        TariffType::$PERFORMANCE = new TariffType(TariffType::PERFORMANCE, 'Performance');
        TariffType::$_5ERBLOCK = new TariffType(TariffType::_5ERBLOCK, '5erblock');
        TariffType::$_10ERBLOCK = new TariffType(TariffType::_10ERBLOCK, '10erblock');
        TariffType::$TAGESSATZ = new TariffType(TariffType::TAGESSATZ, 'Tagessatz');
    }
    public static function getByType($type) {
        switch ($type) {
            case 1: return TariffType::$EINZELSTUNDEN;
            case 2: return TariffType::$GRUPPENSTUNDEN;
            case 3: return TariffType::$WORKSHOP;
            case 4: return TariffType::$PERFORMANCE;
            case 5: return TariffType::$_5ERBLOCK;
            case 6: return TariffType::$_10ERBLOCK;
            case 7: return TariffType::$TAGESSATZ;
        }
    }
    
    public static function getChoices() {
        return array(
            TariffType::$EINZELSTUNDEN->getId() => TariffType::$EINZELSTUNDEN->getName(),
            TariffType::$GRUPPENSTUNDEN->getId() => TariffType::$GRUPPENSTUNDEN->getName(),
            TariffType::$WORKSHOP->getId() => TariffType::$WORKSHOP->getName(),
            TariffType::$PERFORMANCE->getId() => TariffType::$PERFORMANCE->getName(),
            TariffType::$_5ERBLOCK->getId() => TariffType::$_5ERBLOCK->getName(),
            TariffType::$_10ERBLOCK->getId() => TariffType::$_10ERBLOCK->getName(),
            TariffType::$TAGESSATZ->getId() => TariffType::$TAGESSATZ->getName(),
        );
        
    }
}

TariffType::init();
