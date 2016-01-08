<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Equipment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;

class EquipmentController extends BaseAdminController {
    /**
     * 
     * @Route("/admin/equipment", name="admin_equipment_list")
     */
    public function indexAction() {
        return $this->render('admin/equipment/index.html.twig');
    }
    
    /**
     * @Route("/admin/equipment/jsondata", name="admin_equipment_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:Equipment');        
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page);
        $rowsCount = $repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        foreach ($dataRows as $dataRow) { // build single row
            $i=0;
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $cell[$i++] = null;
            $cell[$i++] = $dataRow->getId();
            $cell[$i++] = $dataRow->getName();
            $cell[$i++] = $dataRow->getDescription();
            $cell[$i++] = $dataRow->getPrice();
            $cell[$i++] = $dataRow->getUser()->getUsername();
            $cell[$i++] = $dataRow->getStatus();
            
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
