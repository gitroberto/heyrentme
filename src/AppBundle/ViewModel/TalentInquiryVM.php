<?php

namespace AppBundle\ViewModel;

use AppBundle\Entity\TariffType;
use DateInterval;
use Symfony\Component\HttpFoundation\Request;

class TalentInquiryVM {
    
    public $type;
    public $dateFrom;
    public $dateTo;
    public $num;
    public $price = null;
    public $requestPrice;
    public $diff;
    
    public function parse(Request $req) {
        if ($req->request->has('form')) {
            $form = $req->request->get('form');
            $from = $form['dateFrom'];
            $to = $form['dateTo'];
            $num = $form['num'];
        }
        else {
            $from = $req->get('dateFrom');
            $to = $req->get('dateTo');
            $num = $req->get('num');
        }
        $this->dateFrom = ($from === null || trim($from) === '') ? null : \DateTime::createFromFormat('Y-m-d\TH:i+', $from);
        $this->dateTo = ($to === null || trim($to) === '') ? null : \DateTime::createFromFormat('Y-m-d\TH:i+', $to);
        $this->num = ($num === null || trim($num) === '') ? null : intval($num);            
    }
    
    public function calculate($tariff) {
        $this->type = $tariff->getType();
        $this->requestPrice = $tariff->getRequestPrice();
        
        if ($this->type === TariffType::EINZELSTUNDEN)
            $this->calculate1($tariff);
        else if ($this->type === TariffType::GRUPPENSTUNDEN || $this->type === TariffType::TOUR)
            $this->calculate23($tariff);
        else if ($this->type === TariffType::TAGESSATZ)
            $this->calculate7($tariff);
        else if ($this->type === TariffType::_5ERBLOCK || $this->type === TariffType::_10ERBLOCK || $this->type === TariffType::_20ERBLOCK)
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
