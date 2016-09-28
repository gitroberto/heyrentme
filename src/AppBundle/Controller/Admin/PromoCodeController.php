<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\PromoCode;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ExecutionContextInterface;

class PromoCodeController extends BaseAdminController {
    /**
     * 
     * @Route("/admin/promo-code", name="admin_promo_code_list")
     */
    public function indexAction() {
        return $this->render('admin/promoCode/index.html.twig');
    }
    
    /**
     * 
     * @Route("/admin/promo-code/generate", name="admin_promo_code_generate")
     */
    public function generateAction(Request $request) {
        
        $form = $this->createFormBuilder()
                ->add('code', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('type', 'choice', array(
                    'choices' => array(
                        'Amount (EUR)' => PromoCode::TYPE_AMOUNT,
                        'Percent (%)' => PromoCode::TYPE_PERCENT
                    ),
                    'choices_as_values' => true,
                    'required' => true,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('value', 'integer', array(
                    'constraints' => array(
                        new NotBlank()
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
            $code = $form['code']->getData();
            $number = $form['number']->getData();
            $value = $form['value']->getData();
            $type = $form['type']->getData();
            $expirationDateStr = $form['expirationDate']->getData();
            
            if (empty($expirationDateStr)){
                $expirationDate = null;
            } else {
                $expirationDateStr = $expirationDateStr. " 00:00";
                $expirationDate = DateTime::createFromFormat('Y-m-d H:i', $expirationDateStr);
            }
            
            $repo = $this->getDoctrineRepo("AppBundle:PromoCode");
            $em = $this->getDoctrine()->getManager();        
            $chars = array_merge(range('A','Z'), range('a','z'), range('0','9'));                    
            for($i = 0; $i < $number; $i++){
                $pc = new PromoCode();
                $pc->setStatus(PromoCode::STATUS_NEW);
                $pc->setCode($code);  
                $pc->setType($type);
                $pc->setValue($value);
                $pc->setExpiresAt($expirationDate);
                $em->persist($pc);
                $em->flush();
            }
                        
            return $this->redirectToRoute("admin_promo_code_list");
        }
        
        return $this->render('admin/promoCode/generate.html.twig', array(
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
     * @Route("/admin/cancel-promo-code", name="admin_cancel_promo_code")
     */
    public function cancelAction(Request $request) {
        $idsStr = $request->get('ids');        
        $ids =  explode(",", $idsStr);
        $em = $this->getDoctrine()->getManager();
        foreach($ids as $id){
            $dc = $this->getDoctrineRepo("AppBundle:PromoCode")->find((integer)$id);
            if (!$dc){
                continue;
            }
            $dc->setStatus(PromoCode::STATUS_CANCELLED);
            $em->persist($dc);
        }
        $em->flush();
        
        $result = "OK";
        $status = JsonResponse::HTTP_OK;
        $resp = new JsonResponse($result, $status);        
        return $resp; 
    }
    
    /**
     * @Route("/admin/promo-code/jsondata", name="admin_promo_code_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:PromoCode');        
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
            $cell[$i++] = $dataRow->getType();
/*            $sub = $dataRow->getSubscriber();
            if ($sub !== null) {
                $cell[$i++] = $sub->getId();
                $cell[$i++] = $sub->getEmail(); 
            }
            else {
                $cell[$i++] = "";
                $cell[$i++] = "";
            }
 */
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
