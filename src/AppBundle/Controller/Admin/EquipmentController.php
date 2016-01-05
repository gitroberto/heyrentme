<?php
namespace AppBundle\Controller\Admin;

use Symfony\Component\Routing\Annotation\Route;

class EquipmentController extends BaseAdminController {
    /**
     * 
     * @Route("/admin/equipment", name="admin_equipment_list")
     */
    public function indexAction() {
        return $this->render('admin/equipment/index.html.twig');
    }
    
    /**
     * @Route("/admin/equipment/jsondata", name="admin_blog_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:Blog');        
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page);
        $rowsCount = $repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $cell[0] = null;
            $cell[1] = $dataRow->getId();
            $cell[2] = $dataRow->getTitle();
            $cell[3] = $dataRow->getCreatedAt()->format('Y-m-d H:i');
            $cell[4] = $dataRow->getModifiedAt()->format('Y-m-d H:i');
            $cell[5] = $dataRow->getPosition();
            
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
