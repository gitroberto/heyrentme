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
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
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
    
    
    
    /**
     * @Route("/admin/equipment/edit/{id}", name="admin_equipment_edit")     
     */
    public function equipmentEditAction(Request $request, $id) {
        $equipment = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
        if (!$equipment) {
            return new Response(Response::HTTP_NOT_FOUND);
        }        
        // security check
        //if ($this->getUser()->getId() !== $equipment->getUser()->getId()) {
        //    return new Response($status = Response::HTTP_FORBIDDEN);
        //}
        
        //Get eq owner
        $owner = $equipment->getUser();
        
        // map fields, TODO: consider moving to Equipment's method
        //<editor-fold> map fields            
        $data = array(
            //edit 1
            'name' => $equipment->getName(),
            'price' => $equipment->getPrice(),
            'deposit' => $equipment->getDeposit(),
            'value' => $equipment->getValue(),
            'priceBuy' => $equipment->getPriceBuy(),
            'invoice' => $equipment->getInvoice(),
            'industrial' => $equipment->getIndustrial(),
            'ageId' => $equipment->getAge()->getId(),
            
            //edit 2
            'description' => $equipment->getDescription(),
            'phonePrefix' => $owner->getPhonePrefix(),
            'phone' => $owner->getPhone(),
            'make_sure' => $equipment->getFunctional() > 0,
            'accept' => $equipment->getAccept() > 0,
            'street' => $equipment->getAddrStreet(),
            'number' => $equipment->getAddrNumber(),
            'flatNumber' => $equipment->getAddrFlatNumber(),
            'postcode' => $equipment->getAddrPostcode(),
            'place' => $equipment->getAddrPlace(),
                
            //edit 3
            'timeMorning' => $equipment->getTimeMorning(),
            'timeAfternoon' => $equipment->getTimeAfternoon(),
            'timeEvening' => $equipment->getTimeEvening(),
            'timeWeekend' => $equipment->getTimeWeekend(),
            'descType' => $equipment->getDescType(),
            'descSpecial' => $equipment->getDescSpecial(),
            'descCondition' => $equipment->getDescCondition()
            
        );
        //</editor-fold>
        
        // build form
        //<editor-fold>
        $ageArr = $this->getDoctrineRepo('AppBundle:EquipmentAge')->getAllForDropdown();        
        $form = $this->createFormBuilder($data, array('constraints' => array(
                            new Callback(array($this, 'validateTime'))
                        ) )                
                
                )
                
                //edit 1                
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 256))
                    )
                ))
                ->add('price', 'integer', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 10, 'max' => 2500))
                    )
                ))
                ->add('deposit', 'integer', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 0, 'max' => 1000))
                    )
                ))
                ->add('value', 'integer', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 50, 'max' => 2000))
                    )
                ))
                ->add('priceBuy', 'integer', array(
                    'required' => false,
                    'constraints' => array(
                        new Range(array('min' => 0, 'max' => 20000))
                    )
                ))
                ->add('ageId', 'choice', array(
                    'choices' => $ageArr,
                    'choices_as_values' => false,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('invoice', 'checkbox', array('required' => false))
                ->add('industrial', 'checkbox', array('required' => false))
                
                
                //edit 2
                ->add('description', 'textarea', array(
                    'attr' => array('maxlength' => 500),
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 500))
                    )
                ))
                ->add('make_sure', 'checkbox', array(
                    'required' => false,
                    'constraints' => array(
                        new Callback(array($this, 'validateMakeSure'))                    
                    )
                ))
                ->add('street', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 128))
                    )
                ))
                ->add('number', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 16))
                    )
                ))
                ->add('flatNumber', 'text', array(
                    'required' => false,
                    'constraints' => array(
                        new Length(array('max' => 16))
                    )
                ))
                ->add('postcode', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 4)),
                        new Regex(array('pattern' => '/^\d{4}$/', 'message' => 'Bitte gib hier eine gültige PLZ ein'))
                    )
                ))
                ->add('place', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 128))
                    )
                ))
                ->add('defaultAddress', 'checkbox', array(
                    'required' => false
                ))
                ->add('accept', 'checkbox', array(
                    'required' => false,
                    'constraints' => array(
                        new Callback(array($this, 'validateAccept'))
                    )
                ))
                ->add('phone', 'text', array(
                    'required' => true,
                    'attr' => array(
                        'maxlength' => 10, 
                        'pattern' => '^[0-9]{1,10}$'),
                    'constraints' => array(
                        new Regex(array('pattern' => '/^\d{1,10}$/', 'message' => 'Bitte gib hier eine gültige Telefonnummer ein'))
                    )
                ))
                ->add('phonePrefix', 'text', array(
                    'required' => true, 
                    'attr' => array('maxlength' => 3, 'pattern' => '^[0-9]{1,3}$'),
                    'constraints' => array(
                        new Regex(array('pattern' => '/^\d{1,3}$/', 'message' => 'Bitte gib hier eine gültige Vorwahl ein'))
                    )
                ))

                //edit 3
                ->add('timeMorning', 'checkbox', array('required' => false))
                ->add('timeAfternoon', 'checkbox', array('required' => false))
                ->add('timeEvening', 'checkbox', array('required' => false))
                ->add('timeWeekend', 'checkbox', array('required' => false))
                ->add('descType', 'textarea', array(
                    'required' => false,
                    'attr' => array('maxlength' => 500),
                    'constraints' => array(new Length(array('max' => 500)))
                ))    
                ->add('descSpecial', 'textarea', array(
                    'required' => false,
                    'attr' => array('maxlength' => 500),
                    'constraints' => array(new Length(array('max' => 500)))
                ))    
                ->add('descCondition', 'textarea', array(
                    'required' => false,
                    'attr' => array('maxlength' => 1000),
                    'constraints' => array(new Length(array('max' => 1000)))
                ))              
                
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        $statusChanged = false; // change relevant for email notification
        
        if ($form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $age = $this->getDoctrineRepo('AppBundle:EquipmentAge')->find($data['ageId']);            

            // check for modaration relevant changes
            $changed = $equipment->getName() !== $data['name'];

            // map fields, TODO: consider moving to Equipment's method
            //<editor-fold> map fields            
            
            //EDIT 1
            $equipment->setName($data['name']);
            $equipment->setPrice($data['price']);
            $equipment->setValue($data['value']);
            $equipment->setDeposit($data['deposit']);
            $equipment->setPriceBuy($data['priceBuy']);
            $equipment->setInvoice($data['invoice']);
            $equipment->setIndustrial($data['industrial']);
            $equipment->setAge($age);
            //</editor-fold>
            
            //EDIT 2
            $equipment->setDescription($data['description']);
            $equipment->setAddrStreet($data['street']);
            $equipment->setAddrNumber($data['number']);
            $equipment->setAddrFlatNumber($data['flatNumber']);
            $equipment->setAddrPostcode($data['postcode']);
            $equipment->setAddrPlace($data['place']);            
            $equipment->setFunctional(intval($data['make_sure']));
            $equipment->setAccept(intval($data['accept']));
            //</editor-fold>
            $em->flush();
            
            // update user
            //if ($data['defaultAddress'] === true) {
            //    $user->setAddrStreet($eq->getAddrStreet());
            //    $user->setAddrNumber($eq->getAddrNumber());
            //    $user->setAddrFlatNumber($eq->getAddrFlatNumber());
            //    $user->setAddrPostcode($eq->getAddrPostcode());
            //    $user->setAddrPlace($eq->getAddrPlace());
            //}
            //$user->setPhonePrefix($data['phonePrefix']);
            //$user->setPhone($data['phone']);
            
            
            //EDIT 3
            $changed = $equipment->getDescType() !== $data['descType']
                || $equipment->getDescSpecial() !== $data['descSpecial']
                || $equipment->getDescCondition() !== $data['descCondition'];
            
            // map fields
            //<editor-fold>
            $equipment->setTimeMorning($data['timeMorning']);
            $equipment->setTimeAfternoon($data['timeAfternoon']);
            $equipment->setTimeEvening($data['timeEvening']);
            $equipment->setTimeWeekend($data['timeWeekend']);
            $equipment->setDescType($data['descType']);
            $equipment->setDescSpecial($data['descSpecial']);
            $equipment->setDescCondition($data['descCondition']);
            
            
            
            // save to db
            $em->flush();

            // handle status change and notification
            //if ($changed) {
            //    $statusChanged = $this->getDoctrineRepo('AppBundle:Equipment')->equipmentModified($id);
            //}
            //if ($statusChanged) {
            //    $this->sendNewModifiedEquipmentInfoMessage($request, $equipment); 
                // todo: refactor: notification sent by repository/service, etc.; consider mapping fields within the method
            //}
            
            //if (!$statusChanged) {            
            return $this->redirectToRoute('admin_equipment_list');
            //}
        }
        
        $complete = $equipment->getStatus() != Equipment::STATUS_INCOMPLETE;
        
        return $this->render('admin/equipment/edit.html.twig', array(
            'form' => $form->createView(),
            'complete' => $complete,
            'id' => $id,
            'statusChanged' => $statusChanged
            
        ));
    }
    
    
    public function validateTime($data, ExecutionContextInterface $context) {
        if (!$data['timeMorning'] && !$data['timeAfternoon'] && !$data['timeEvening'] && !$data['timeWeekend'] ) {
            $context->buildViolation('Bitte wähle zumindest einen Zeitpunkt an dem du verfügbar sein kannst')->addViolation();
        }
    }
    
    public function validateMakeSure($value, ExecutionContextInterface $context) {
        if (!$value) {
            $context->buildViolation('Bitte Checkbox bestätigen')->atPath('make_sure')->addViolation();
        }            
    }
    
    public function validateAccept($value, ExecutionContextInterface $context) {
        if (!$value) {
            $context->buildViolation('Bitte Checkbox bestätigen')->atPath('accept')->addViolation();
        }            
    }
}
