<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\DiscountCode;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DiscountCodeController extends BaseAdminController {
    /**
     * 
     * @Route("/admin/discount-code", name="admin_discount_code_list")
     */
    public function indexAction() {
        return $this->render('admin/discountCode/index.html.twig');
    }
    
    /**
     * 
     * @Route("/admin/generate-discount-code/{numberOfCodesToGenerate}", name="admin_genetate_discount_code")
     */
    public function generateDiscountCodeAction(Request $request, $numberOfCodesToGenerate) {
        $number = (integer)$numberOfCodesToGenerate;
        $em = $this->getDoctrine()->getManager();
        
        $chars = array_merge(range('A','Z'), range('a','z'), range('0','9'));
        
        for($i = 0; $i < $number; $i++){
            $dc = new DiscountCode();
            $dc->setStatus(DiscountCode::STATUS_NEW);
            $dc->setCode(DiscountCode::generateCode($chars));            
            $em->persist($dc);
        }
        $em->flush();
        
        return $this->redirectToRoute("admin_discount_code_list");
        //$this->render('admin/discountCode/index.html.twig');
    }
    
    /**
     * 
     * @Route("/admin/cancel-discount-code", name="admin_cancel_discount_code")
     */
    public function cancelDiscountCodeAction(Request $request) {
        $idsStr = $request->get('ids');        
        $ids =  explode(",", $idsStr);
        $em = $this->getDoctrine()->getManager();
        foreach($ids as $id){
            $dc = $this->getDoctrineRepo("AppBundle:DiscountCode")->find((integer)$id);
            if (!$dc){
                continue;
            }
            $dc->setStatus(DiscountCode::STATUS_CANCELLED);
            $em->persist($dc);
        }
        $em->flush();
        
        $result = "OK";
        $status = JsonResponse::HTTP_OK;
        $resp = new JsonResponse($result, $status);        
        return $resp; 
    }
    
    /**
     * @Route("/admin/discount-code/jsondata", name="admin_discount_code_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:DiscountCode');        
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page);
        $rowsCount = $repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $i = 0;
            $cell[$i++] = '';
            $cell[$i++] = $dataRow->getId();
            $cell[$i++] = $dataRow->getCode();
            $cell[$i++] = $dataRow->getStatusStr();
            $cell[$i++] = $dataRow->getCreatedAt()->format('Y-m-d H:i');            
            $cell[$i++] = $dataRow->getUser() ? $dataRow->getUser()->getUsername() : ""; 
            
            $row['cell'] = $cell;
            array_push($rows, $row);
        }
        
        $result = array( // main result object as json
            'records' => $rowsCount,
            'page' => $page,
            'total' => $pagesCount,
            'rows' => $rows
        );        
        
        $resp = new JsonResponse($result, JsonResponse::HTTP_OK);
        $resp->setCallback($callback);
        return $resp;
        
    }
}
