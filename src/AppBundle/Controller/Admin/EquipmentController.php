<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Equipment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
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
        $sStatus = $request->get('e_status');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:Equipment');        
        $res = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $sStatus);
        $dataRows = $res['rows'];
        $rowsCount = $res['count'];//$repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        foreach ($dataRows as $dataRow) { // build single row
            $subcat = $dataRow->getSubcategory();
            $cat = $subcat->getCategory();
            $user = $dataRow->getUser();
            
            $i = 0;
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $cell[$i++] = null;
            $cell[$i++] = $dataRow->getId();
            $cell[$i++] = sprintf("%s | %s", $cat->getName(), $subcat->getName());
            $cell[$i++] = $dataRow->getName();
            $cell[$i++] = $dataRow->getPrice();
            $cell[$i++] = $user !== null ? $user->getUsername() : '';
            $cell[$i++] = $dataRow->getStatusStr();
            $cell[$i++] = $this->generateUrl('preview_equipment', array('uuid'=>$dataRow->getUuid()));
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
     * 
     * @Route("/admin/equipment/moderate/{id}", name="admin_equipment_moderate")
     */
    public function moderateAction(Request $request, $id) {
        $equipment = $this->getDoctrineRepo('AppBundle:Equipment')->getOne($id);

        if (!$equipment) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        
        $options = array();
        $options[0] = 
        
        $form = $this->createFormBuilder($equipment, array(
                    'constraints' => array(
                        new Callback(array($this, 'validateReason'))
                    )
                ))
                ->add('id', 'hidden')
                ->add('status', 'choice', array(
                    'choices' => array(
                        'select status' => null,
                        'Approve' => Equipment::STATUS_APPROVED,
                        'Reject' => Equipment::STATUS_REJECTED
                    ),
                    'choices_as_values' => true,
                    'required' => true,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('reason', 'textarea', array(
                    'required' => false,
                    'constraints' => array(                        
                        new Length(array('max' => 500))
                    )
                ))
                ->getForm();

        $this->formHelper = $form;  
        //when the form is posted this method prefills entity with data from form
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $equipment->changeStatus($form['status']->getData(), $form['reason']->getData());            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->sendApprovedRejectedInfoMessage($request, $equipment, $form['reason']->getData());
            
            return $this->redirectToRoute("admin_equipment_list");
        }
        
        return $this->render('admin/equipment/moderate.html.twig', array(            
            "form" => $form->createView(),
            "equipment" => $equipment
            
        ));
    }
    protected $formHelper = null;
    public function validateReason($equipment, ExecutionContextInterface $context) {
        if ($this->formHelper != null) {            
            $status = $this->formHelper['status']->getData();
            $reason = $this->formHelper['reason']->getData();
            if ($status == Equipment::STATUS_REJECTED && ($reason == null || $reason == "") ){
            $context->buildViolation('You have to enter rejection reason.')
                        ->addViolation();
            }
        }
    }
    
    public function sendApprovedRejectedInfoMessage(Request $request, Equipment $eq, $reason)
    {      
        $template = 'Emails/admin/item_approved.html.twig';       
        if ($eq->getStatus() == Equipment::STATUS_REJECTED) {
            $template = 'Emails/admin/item_rejected.html.twig';
        }
        
        $userLink = $request->getSchemeAndHttpHost() . $this->generateUrl('dashboard');
        $eqLink = $request->getSchemeAndHttpHost() . $this->generateUrl('catchall', array('content' => $eq->getUrlPath()));                        
        
        $emailHtml = $this->renderView($template, array(                                    
            'item' => $eq,
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
            'reason' => $reason,
            'userLink' => $userLink,
            'status_approved' => Equipment::STATUS_APPROVED,
            'status_rejected' => Equipment::STATUS_REJECTED,
            'itemLink' => $eqLink
        ));
        
        $subject = $eq->getStatus() == Equipment::STATUS_APPROVED ? "Dein Angebot wurde bestätigt!" : "Dein Angebot konnte noch nicht bestätigt werden";
        
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($eq->getUser()->getEmail())
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
    }
    
    
}
