<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\ReportOffer;
use AppBundle\Entity\Image;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ExecutionContextInterface;

class ReportOfferController  extends BaseAdminController {
    /**
     * 
     * @Route("/admin/report-offer", name="admin_report_offer_list")
     */
    public function indexAction() {
        return $this->render('admin/reportOffer/index.html.twig');
    }
    
    /**
     * 
     * @Route("/admin/report-offer/delete/{id}", name="admin_report_offer_delete")
     */
    public function deleteAction(Request $request, $id) {
        $reportOffer = $this->getDoctrineRepo('AppBundle:ReportOffer')->find($id);

        if (!$reportOffer) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
          
        $em = $this->getDoctrine()->getManager();
        $em->remove($reportOffer);
        $em->flush();
        return $this->redirectToRoute("admin_report_offer_list");
    }
    
    public function GetLinkText($item){
        return str_replace(" ", "-",  strtolower($item->getName()));
    }
    
    /**
     * @Route("/admin/report-offer/jsondata", name="admin_report_offer_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:ReportOffer');        
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
            $cell[2] = $dataRow->getReport();
            $cell[3] = $dataRow->getMessage();
            $cell[4] = $dataRow->getCreatedAt()->format('Y-m-d H:i');            
            $cell[5] = $dataRow->getOfferTypeStr();            
            if ($dataRow->getOfferType() == ReportOffer::OFFER_TYPE_EQUIPMENT){
                $cell[6] = $request->getSchemeAndHttpHost() . $this->generateUrl('catchall', array('content' => $dataRow->getEquipment()->getUrlPath()));
                $cell[7] = $request->getSchemeAndHttpHost() . $this->generateUrl("admin_equipment_moderate", array('id'=> $dataRow->getEquipment()->getId()));
            } else {
                $cell[6] = $request->getSchemeAndHttpHost() . $this->generateUrl('catchall', array('content' => $dataRow->getTalent()->getUrlPath()));
                $cell[7] = $request->getSchemeAndHttpHost() . $this->generateUrl("admin_talent_moderate", array('id'=> $dataRow->getTalent()->getId()));
            }
            
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
