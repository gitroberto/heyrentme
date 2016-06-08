<?php
    
namespace AppBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
    

class SearchParams {
    
    private $sort = "date";
    private $categoryId = null;
    private $testBuy = false;
    private $testDrive = false;
    private $subcategoryIds = array();
    private $discount = false;
    
    public function updateFromRequest(Request $request) {
        if ($request->request->has('sort')) {
            $this->sort = $request->get('sort');
        }
        if ($request->request->has('categoryId')) {
            $this->categoryId = $request('categoryId');
        }
        if ($request->request->has('options')) {
            $opts = $request->get('options');
            $this->testDrive = in_array('testDrive', $opts);
            $this->testBuy = in_array('priceBuy', $opts);
            $this->subcategoryIds = array();
            foreach ($opts as $opt)
                if (is_numeric($opt))
                    array_push ($this->subcategoryIds, intval ($opt));            
        }
        /*
        if ($request->request->has('discount')) {            
            $this->discount = intval($request->get('discount')) > 0;
        }
         */
    }
    
    public function getOptionsAsArray() {
        $arr = array();
        if ($this->testBuy)
            $arr[] = "priceBuy";
        if ($this->testDrive)
            $arr[] = "testDrive";
        foreach ($this->subcategoryIds as $id)
            $arr[] = strval ($id);
        return json_encode($arr);
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
    
    public function getTestDrive() {
        return $this->testDrive;
    }
    public function setTestDrive($testDrive) {
        $this->testDrive = $testDrive;
    }
    
    public function getSubcategoryIds() {
        return $this->subcategoryIds;
    }
    public function setSubcategoryIds($subcategoryIds) {
        $this->subcategoryIds = $subcategoryIds;
    }
}
