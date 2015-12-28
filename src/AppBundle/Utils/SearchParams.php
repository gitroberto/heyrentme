<?php
    
namespace AppBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
    

class SearchParams {
    
    private $sort = "date";
    private $categoryId = null;
    private $testBuy = false;
    private $discount = false;
    
    public function updateFromRequest(Request $request) {
        if ($request->request->has('sort')) {
            $this->sort = $request->get('sort');
        }
        if ($request->request->has('categoryId')) {
            $this->categoryId = $request('categoryId');
        }
        if ($request->request->has('testBuy')) {
            $this->testBuy = intval($request->get('testBuy')) > 0;
        }
        if ($request->request->has('discount')) {
            $this->discount = intval($request->get('discount')) > 0;
        }
    }
    
    public function getSort() {
        return $this->sort;
    }

    public function getCategoryId() {
        return $this->categoryId;
    }

    public function getTestBuy() {
        return $this->testBuy;
    }

    public function getDiscount() {
        return $this->discount;
    }

    public function setSort($sort) {
        $this->sort = $sort;
    }

    public function setCategoryId($categoryId) {
        $this->categoryId = $categoryId;
    }

    public function setTestBuy($testBuy) {
        $this->testBuy = $testBuy;
    }

    public function setDiscount($discount) {
        $this->discount = $discount;
    }
}
