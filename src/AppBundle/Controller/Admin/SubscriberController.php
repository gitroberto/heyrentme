<?php

namespace AppBundle\Controller\Admin;

use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SubscriberController extends BaseAdminController {
    /**
     * @Route("/admin/subscriber", name="admin-subscribers-list")
     */
    public function indexAction() {
        return $this->render('admin/newsletter/index.html.twig');
    }
    /**
     * @Route("/admin/subscriber/jsondata", name="admin-subscriber-jsondata")
     */
    public function JsonData(Request $request) {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        $sConfirmed = $request->get('s_confirmed');
        $sUnsubscribed = $request->get('s_unsubscribed');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:Subscriber');        
        $res = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $sConfirmed, $sUnsubscribed);
        $rowsCount = $res['count'];
        $dataRows = $res['rows'];
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $i = 0;
            $cell[$i++] = '';
            $cell[$i++] = $dataRow->getId();
            $cell[$i++] = $dataRow->getEmail();
            $cell[$i++] = $dataRow->getConfirmed();
            $cell[$i++] = $dataRow->getUnsubscribed();
            $cell[$i++] = $dataRow->getCreatedAt()->format('Y-m-d H:i');  
            $cell[$i++] = $dataRow->getModifiedAt()->format('Y-m-d H:i');  
            
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

    /**
     * @Route("/admin/subscriber/csv", name="admin-subscriber-csv")
     */
    public function csvAction() {
        $csv = $this->getDoctrineRepo('AppBundle:Subscriber')->exportAsCsv();
        $now = new DateTime();
        $name = sprintf("subscribers-%s.csv", $now->format("Ymd"));
        
        $resp = new Response();
        $resp->headers->set('Content-type', 'text/csv');
        $cs = $resp->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);
        $resp->headers->set('Content-disposition', $cs);
        $resp->setContent($csv);
        return $resp;
    }
}
