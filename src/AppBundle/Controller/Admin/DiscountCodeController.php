<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\DiscountCode;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ExecutionContextInterface;

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
     * @Route("/admin/discount-code/generate", name="admin_discount_code_generate")
     */
    public function generateAction(Request $request) {
        
        $form = $this->createFormBuilder()
                ->add('value', 'text', array(
                    'required' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new Callback(array($this, 'validateValue'))
                    )
                ))
                
                ->add('number', 'integer', array(
                    'constraints' => array(
                        new NotBlank()
                        
                    )
                ))
                
                ->add('expirationDate', 'date', array(
                    'required' => false,
                    'input'  => 'string',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy'
                ))
               
                ->getForm();
        //when the form is posted this method prefills entity with data from form
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            
            $number = $form['number']->getData();
            $value = $form['value']->getData();
            $expirationDateStr = $form['expirationDate']->getData();
            
            if (empty($expirationDateStr)){
                $expirationDate = null;
            } else {
                $expirationDateStr = $expirationDateStr. " 00:00";
                $expirationDate = DateTime::createFromFormat('Y-m-d H:i', $expirationDateStr);
            }
            
            $repo = $this->getDoctrineRepo("AppBundle:DiscountCode");
            $em = $this->getDoctrine()->getManager();        
            $chars = array_merge(range('A','Z'), range('a','z'), range('0','9'));                    
            for($i = 0; $i < $number; $i++){
                $dc = new DiscountCode();
                $dc->setStatus(DiscountCode::STATUS_NEW);
                do {
                    $newCode = DiscountCode::generateCode($chars);                        
                } while (!$repo->isCodeUnique($newCode));                 
                $dc->setCode($newCode);    
                $dc->setValue($value);
                $dc->setExpiresAt($expirationDate);
                $em->persist($dc);
                $em->flush();
            }
                        
            return $this->redirectToRoute("admin_discount_code_list");
        }
        
        return $this->render('admin/discountCode/generate.html.twig', array(
            'form' => $form->createView()
        ));
    }    
    
    public function validateValue($data, ExecutionContextInterface $context) {
        if (!ctype_digit($data)){
            $context->buildViolation('Value should be an integer.')->atPath('value')->addViolation();
        }
        
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
            $cell[$i++] = $dataRow->getModifiedAt()->format('Y-m-d H:i');  
            if ($dataRow->getExpiresAt() !== null){
                $cell[$i++] =  $dataRow->getExpiresAt()->format('Y-m-d H:i');  
            } else {
                $cell[$i++] =  "";
            }
            $cell[$i++] = $dataRow->getValue();
            $sub = $dataRow->getSubscriber();
            if ($sub !== null) {
                $cell[$i++] = $sub->getId();
                $cell[$i++] = $sub->getEmail(); 
            }
            else {
                $cell[$i++] = "";
                $cell[$i++] = "";
            }
            $user = $dataRow->getUser();
            if ($user !== null) {
                $cell[$i++] = $user->getId();
                $cell[$i++] = $user->getUsername(); 
                $cell[$i++] = $this->generateUrl('admin-user-details', array('id' => $user->getId()));
            }
            else {
                $cell[$i++] = "";
                $cell[$i++] = "";
                $cell[$i++] = "";
            }
            $eb = $dataRow->getEquipmentBooking();
            $tb = $dataRow->getTalentBooking();            
            $provider = null;
            if ($eb !== null) {
                $eq = $eb->getInquiry()->getEquipment();
                $provider = $eq->getUser();
                $cell[$i++] = $eq->getId();
                $cell[$i++] = $eq->getName();
                $cell[$i++] = $this->generateUrl('admin_equipment_moderate', array('id' => $eq->getId()));
            }
            else if ($tb !== null) {
                $tal = $tb->getInquiry()->getTalent();
                $provider = $tal->getUser();
                $cell[$i++] = $tal->getId();
                $cell[$i++] = $tal->getName();
                $cell[$i++] = $this->generateUrl('admin_talent_moderate', array('id' => $tal->getId()));
            }
            else {
                $cell[$i++] = "";
                $cell[$i++] = "";
                $cell[$i++] = "";
            }
            if ($provider !== null) {
                $cell[$i++] = $provider->getId();
                $cell[$i++] = $provider->getEmail();
                $cell[$i++] = $this->generateUrl('admin-user-details', array('id' => $provider->getId()));
            }
            else {
                $cell[$i++] = "";
                $cell[$i++] = "";
                $cell[$i++] = "";
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
