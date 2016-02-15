<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Talent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;



use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TalentController extends BaseAdminController {
    /**
     * 
     * @Route("/admin/talent", name="admin_talent_list")
     */
    public function indexAction() {
        return $this->render('admin/talent/index.html.twig');
    }
    
    /**
     * @Route("/admin/talent/jsondata", name="admin_talent_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        $sStatus = $request->get('e_status');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:Talent');        
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $sStatus);
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
            $cell[$i++] = $dataRow->getStatusStr();
            
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
     * @Route("/admin/talent/moderate/{id}", name="admin_talent_moderate")
     */
    public function moderateAction(Request $request, $id) {
        $talent = $this->getDoctrineRepo('AppBundle:Talent')->find($id);

        if (!$talent) {
            throw $this->createNotFoundException('No talent found for id '.$id);
        }
        
        $options = array();
        $options[0] = 
        
        $form = $this->createFormBuilder($talent, array(
                    'constraints' => array(
                        new Callback(array($this, 'validateReason'))
                    )
                ))
                ->add('id', 'hidden')
                ->add('status', 'choice', array(
                    'choices' => array(
                        'select status' => null,
                        'Approve' => Talent::STATUS_APPROVED,
                        'Reject' => Talent::STATUS_REJECTED
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
            $talent->changeStatus($form['status']->getData(), $form['reason']->getData());            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->sendApprovedRejectedInfoMessage($request, $talent, $form['reason']->getData());
            
            return $this->redirectToRoute("admin_talent_list");
        }
        
        return $this->render('admin/talent/moderate.html.twig', array(            
            "form" => $form->createView(),
            "talent" => $talent
            
        ));
    }
    protected $formHelper = null;
    public function validateReason($talent, ExecutionContextInterface $context) {
        if ($this->formHelper != null) {            
            $status = $this->formHelper['status']->getData();
            $reason = $this->formHelper['reason']->getData();
            if ($status == Talent::STATUS_REJECTED && ($reason == null || $reason == "") ){
            $context->buildViolation('You have to enter rejection reason.')
                        ->addViolation();
            }
        }
    }
    
    public function sendApprovedRejectedInfoMessage(Request $request, Talent $eq, $reason)
    {      
        $template = 'Emails/admin/item_approved.html.twig';       
        if ($eq->getStatus() == Talent::STATUS_REJECTED) {
            $template = 'Emails/admin/item_rejected.html.twig';
        }
        
        $userLink = $request->getSchemeAndHttpHost() . $this->generateUrl('dashboard');
        $eqLink = $request->getSchemeAndHttpHost() . $this->generateUrl('catchall', array('content' => $eq->getUrlPath()));                        
        
        $emailHtml = $this->renderView($template, array(                                    
            'item' => $eq,
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
            'reason' => $reason,
            'userLink' => $userLink,
            'status_approved' => Talent::STATUS_APPROVED,
            'status_rejected' => Talent::STATUS_REJECTED,
            'itemLink' => $eqLink
        ));
        
        $subject = $eq->getStatus() == Talent::STATUS_APPROVED ? "Dein Angebot wurde bestätigt!" : "Dein Angebot wurde noch nicht bestätigt";
        
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($eq->getUser()->getEmail())
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
    }
    
    
}
