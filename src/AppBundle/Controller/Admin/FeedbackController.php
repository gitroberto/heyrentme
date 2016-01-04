<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\FeatureSection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class FeedbackController extends BaseAdminController {
    /**
     * 
     * @Route("/admin/feedback", name="admin_feedback_list")
     */
    public function indexAction() {
        
        return $this->render('admin/feedback/index.html.twig');
    }
    
    /**
     * @Route("/admin/feedback/jsondata", name="admin_feedback_jsondata")
     */
    public function JsonData(Request $request) {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');        
        $callback = $request->get('callback');
        
        $repo = $this->getDoctrineRepo('AppBundle:Feedback');
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
            $cell[$i++] = $dataRow->getName();
            $cell[$i++] = $dataRow->getEmail();
            $cell[$i++] = $dataRow->getSubject();            
            $cell[$i++] = $dataRow->getMessage();
            $cell[$i++] = $dataRow->getCreatedAt()->format('Y-m-d H:i');
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
