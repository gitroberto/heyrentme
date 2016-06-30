<?php

namespace AppBundle\ViewModel;

use AppBundle\Entity\TariffType;
use DateInterval;
use Symfony\Component\HttpFoundation\Request;

class TalentInquiryVM {
    
    public $type;
    public $tariffId;
    public $dateFrom;
    public $dateTo;
    public $num;
    public $price = null;
    public $requestPrice;
    public $diff;
    
    public function parse(Request $req) {
        $this->tariffId = intval($req->get('tariffId'));
        
        $from = $req->get('dateFrom', $req->get('form[dateFrom]'));
        $this->dateFrom = $from === null ? null : \DateTime::createFromFormat('Y-m-d\TH:i+', $from);
                        
        $to = $req->get('dateTo', $req->get('form[dateTo]'));
        $this->dateTo = $to === null ? null : \DateTime::createFromFormat('Y-m-d\TH:i+', $to);
                
        $num = $req->get('num');
        $this->num = $num === null ? null : intval($num);
                
    }
    
    public function calculate($tariff) {
        $this->type = $tariff->getType();
        $this->requestPrice = $tariff->getRequestPrice();
        
        if ($this->type === TariffType::EINZELSTUNDEN)
            $this->calculate1($tariff);
        else if ($this->type === TariffType::GRUPPENSTUNDEN || $this->type === TariffType::WORKSHOP)
            $this->calculate23($tariff);
        else if ($this->type === TariffType::TAGESSATZ)
            $this->calculate7($tariff);
        else if ($this->type === TariffType::_5ERBLOCK || $this->type === TariffType::_10ERBLOCK)
            $this->price = $tariff->getPrice();

        }
    private function calculate1($tariff) {
        if ($tariff->getRequestPrice()) {
            $this->price = null;
            return;
        }
        
        $this->diff = $this->dateTo->diff($this->dateFrom);
        $d = $tariff->getDiscount();
        $dp = $tariff->getDiscountPrice();
        $dm = $tariff->getDiscountMinNum();
        
        if ($d && $dp !== null && $dm !== null && $this->diff->h >= $dm)
            $this->price = $this->diff->h * $dp;
        else
            $this->price = $this->diff->h * $tariff->getPrice();        
    }
    private function calculate23($tariff) {
        $d = $tariff->getDiscount();
        $dp = $tariff->getDiscountPrice();
        $dm = $tariff->getDiscountMinNum();
        
        if ($d && $dp !== null && $dm !== null && $this->num !== null &&  $this->num >= $dm)
            $this->price = $dp * $this->num;
        else
            $this->price = $tariff->getPrice() * $this->num;
    }
    private function calculate7($tariff) {
        $d = $tariff->getDiscount();
        $dp = $tariff->getDiscountPrice();
        $dm = $tariff->getDiscountMinNum();
        
        $this->dateTo = clone $this->dateFrom;
        $this->dateTo->add(new DateInterval(sprintf("P%dD", $this->num - 1)));
        
        if ($d && $dp !== null && $dm !== null && $this->num !== null &&  $this->num >= $dm)
            $this->price = $dp * $this->num;
        else
            $this->price = $tariff->getPrice() * $this->num;
    }

    public function getAsArray() {
        return array(
            'type' => $this->type,
            'from' => $this->dateFrom,
            'to' => $this->dateTo,
            'price' => $this->price,
            'diff' => $this->diff,
            'requestPrice' => $this->requestPrice,
            'num' => $this->num
        );
    }    
}
