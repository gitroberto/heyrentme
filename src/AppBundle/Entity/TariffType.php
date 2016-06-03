<?php

namespace AppBundle\Entity;

class TariffType {
    
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
        TariffType::$EINZELSTUNDEN = new TariffType(1, 'Einzelstunden');
        TariffType::$GRUPPENSTUNDEN = new TariffType(2, 'Gruppenstunden');
        TariffType::$WORKSHOP = new TariffType(3, 'Workshop');
        TariffType::$PERFORMANCE = new TariffType(4, 'Performance');
        TariffType::$_5ERBLOCK = new TariffType(5, '5erblock');
        TariffType::$_10ERBLOCK = new TariffType(6, '10erblock');
        TariffType::$TAGESSATZ = new TariffType(7, 'Tagessatz');
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
