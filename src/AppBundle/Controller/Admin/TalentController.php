<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use AppBundle\Entity\Common;
use AppBundle\Entity\Image;
use AppBundle\Entity\Talent;
use AppBundle\Entity\TalentImage;
use AppBundle\Entity\TalentTariff;
use AppBundle\Entity\TariffType;
use AppBundle\Form\Type\Tariff\TariffType1;
use AppBundle\Form\Type\Tariff\TariffType2;
use AppBundle\Form\Type\Tariff\TariffType3;
use AppBundle\Form\Type\Tariff\TariffType4;
use AppBundle\Form\Type\Tariff\TariffType5;
use AppBundle\Form\Type\Tariff\TariffType6;
use AppBundle\Form\Type\Tariff\TariffType7;
use AppBundle\Form\Type\Tariff\TariffType8;
use AppBundle\Form\Type\Tariff\TariffType9;
use AppBundle\Entity\Video;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\Exception;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ExecutionContextInterface;


class TalentController extends BaseAdminController {

    const NEW_TALENT_IDS = 'AppBundle\Controller\Admin\TalentController\NewTalentIds';
    
    private function addNewId(Request $request, $id) {
        $session = $request->getSession();
        $ids = $session->get(TalentController::NEW_TALENT_IDS, array());
        array_push($ids, $id);
        $session->set(TalentController::NEW_TALENT_IDS, $ids);
    }
    private function removeNewId(Request $request, $id) {
        $session = $request->getSession();
        $ids = $session->get(TalentController::NEW_TALENT_IDS, array());
        $key = array_search($id, $ids);
        if ($key !== FALSE)
            unset($ids[$key]);
        $session->set(TalentController::NEW_TALENT_IDS, $ids);
    }
    private function clearNewIds($request) {
        // remove "hanging" equipments (new but not saved)
        $session = $request->getSession();

        $ids = $session->get(TalentController::NEW_TALENT_IDS, array());        
        if (count($ids) === 0)
            return;
        
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrineRepo('AppBundle:Talent');
        
        foreach($ids as $id) {
            $eq = $repo->find($id);
            if ($eq !== null)
                $em->remove($eq);
        }
        $em->flush();
        
        $session->set(TalentController::NEW_TALENT_IDS, array()); // clear session var
    }
    
    
    
    /**
     * @Route("/admin/talent", name="admin_talent_list")
     */
    public function indexAction(Request $request) {
        $this->clearNewIds($request);
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
        $res = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $sStatus);
        $dataRows = $res['rows'];
        $rowsCount = $res['count'];//$repo->countAll();
        $stats = $res['stats'];
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        foreach ($dataRows as $dataRow) { // build single row
            $user = $dataRow->getUser();
            $stat = $stats[$dataRow->getId()];

            $i = 0;
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $cell[$i++] = null;
            $cell[$i++] = $dataRow->getId();
            $cell[$i++] = $dataRow->getSubcategoriesAsString();
            $cell[$i++] = $dataRow->getName();
            $cell[$i++] = $dataRow->getPrice();
            $cell[$i++] = $user !== null ? $user->getUsername() : '';
            $cell[$i++] = $dataRow->getStatusStr();
            $cell[$i++] = $this->generateUrl('preview_talent', array('uuid'=>$dataRow->getUuid()));
            $cell[$i++] = $dataRow->getCreatedAt()->format('Y-m-d H:i');
            $cell[$i++] = $dataRow->getModifiedAt()->format('Y-m-d H:i');            
            $cell[$i++] = $this->generateUrl('admin_talent_edit', array('id'=>$dataRow->getId()));
            $cell[$i++] = $stat['questions'];
            $cell[$i++] = $stat['bookings'];
            $cell[$i++] = $stat['cancels'];
            $cell[$i++] = $stat['revenue'];
            $cell[$i++] = $stat['discount'];
            $cell[$i++] = $this->generateUrl('admin-talent-delete', array('id' => $dataRow->getId()));
            $cell[$i++] = $this->generateUrl('admin-talent-log', array('id' => $dataRow->getId()));
            $cell[$i++] = $this->generateUrl('admin_talent_moderate', array('id' => $dataRow->getId()));
            $cell[$i++] = $dataRow->getShowcaseStart();
            $cell[$i++] = $dataRow->getShowcaseTalent();
            $cell[$i++] = $dataRow->getFeatured();
            $cell[$i++] = $dataRow->anyCategoryActive();
            
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
        $talent = $this->getDoctrineRepo('AppBundle:Talent')->getOne($id);

        if (!$talent) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        
        $options = array();
        $options[0] = 
        
        $data = array(
            'id' => $talent->getId(),
            'status' => $talent->getStatus(),
            'reason' => $talent->getReason()
        );
        $form = $this->createFormBuilder($data, array(
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
                ->add('sendNot', 'checkbox', array('required' => false))
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
            $talent->setReason($form['reason']->getData());
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $send = !$form['sendNot']->getData();
            if ($send)
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
    
    
    /**
     * @Route("/provider/talent/edit/{id}", name="admin_talent_edit")
     */
    public function talentEditAction(Request $request, $id) {        
        $talentRepo = $this->getDoctrineRepo('AppBundle:Talent');
        $subcatRepo = $this->getDoctrineRepo('AppBundle:Subcategory');
        $talent = $talentRepo->find($id);
        $mainImage = $talentRepo->getTalentMainImage($id);
        $images = $talentRepo->getTalentButMainImages($id);
        
        if (!$talent) {
            return new Response(Response::HTTP_NOT_FOUND);
        }        
        $owner = $talent->getUser();
        $subcats = $subcatRepo->getAllForDropdown2(Category::TYPE_TALENT);
        
        // map fields, TODO: consider moving to Talent's method
        //<editor-fold> map fields            
        $data = array(
            'inquiryEmail' => $talent->getInquiryEmail(),
            'name' => $talent->getName(),
            //edit 2
            'description' => $talent->getDescription(),
            'videoUrl' => $talent->getVideo() !== null ? $talent->getVideo()->getOriginalUrl() : null,
            'street' => $talent->getAddrStreet(),
            'flatNumber' => $talent->getAddrFlatNumber(),
            'number' => $talent->getAddrNumber(),
            'postcode' => $talent->getAddrPostcode(),
            'place' => $talent->getAddrPlace(),
            'phonePrefix' => $owner->getPhonePrefix(),
            'phone' => $owner->getPhone(),
            'make_sure' => $talent->getLicence() > 0,
            'accept' => $talent->getAccept() > 0,
            //edit 3
            'timeMorning' => $talent->getTimeMorning(),
            'timeAfternoon' => $talent->getTimeAfternoon(),
            'timeEvening' => $talent->getTimeEvening(),
            'timeWeekend' => $talent->getTimeWeekend(),
            'optClient' => $talent->getOptClient(),
            'optGroup' => $talent->getOptGroup(),
            'descReference' => $talent->getDescReference(),
            'descScope' => $talent->getDescScope(),
            'descTarget' => $talent->getDescTarget(),
            'descCondition' => $talent->getDescCondition()
            
        );
        $i = 1;
        foreach ($talent->getSubcategories() as $sc) {
            $data["subcategory{$i}Id"] = $sc->getId();
            $i++;
            if ($i === 4)
                break;
        }
                        
        if (empty($talent->getAddrStreet())) {
            $data['street'] = $owner->getAddrStreet();
            $data['number'] = $owner->getAddrNumber();
            $data['flatNumber'] = $owner->getAddrFlatNumber();
            $data['postcode'] = $owner->getAddrPostcode();
            $data['place'] = $owner->getAddrPlace();      
        }
        //</editor-fold>
        
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder($data, array(
                'error_bubbling' => false,
                'constraints' => array(
                    new Callback(array($this, 'validateTime'))
                )))
                ->add('subcategory1Id', 'choice', array(
                    'choices' => $subcats,
                    'choices_as_values' => false,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('subcategory2Id', 'choice', array(
                    'required' => false,
                    'choices' => $subcats,
                    'choices_as_values' => false
                ))
                ->add('subcategory3Id', 'choice', array(
                    'required' => false,
                    'choices' => $subcats,
                    'choices_as_values' => false
                ))
                ->add('subcategory4Id', 'choice', array(
                    'required' => false,
                    'choices' => $subcats,
                    'choices_as_values' => false
                ))
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 32))
                    )
                ))
                
                //edit 2
                ->add('description', 'textarea', array(
                    'attr' => array(
                        'maxlength' => 2500,
                        'placeholder' => 'Maximal 2500 Zeichen verfügbar'
                    ),
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 2500))
                    )
                ))
                ->add('videoUrl', 'text', array(
                    'required' => false,
                    'attr' => array('maxlength' => 100),
                    'constraints' => array(
                        new Length(array('max' => 100)),
                        new Callback(array($this, 'validateVideoUrl'))
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
                //Edit 3
                ->add('timeMorning', 'checkbox', array('required' => false))
                ->add('timeAfternoon', 'checkbox', array('required' => false))
                ->add('timeEvening', 'checkbox', array('required' => false))
                ->add('timeWeekend', 'checkbox', array('required' => false))
                ->add('optClient', 'checkbox', array('required' => false))
                ->add('optGroup', 'checkbox', array('required' => false))
                ->add('descReference', 'textarea', array(
                    'required' => false,
                    'attr' => array('maxlength' => 500),
                    'constraints' => array(new Length(array('max' => 500)))
                ))    
                ->add('descScope', 'textarea', array(
                    'required' => false,
                    'attr' => array('maxlength' => 500),
                    'constraints' => array(new Length(array('max' => 500)))
                ))    
                ->add('descTarget', 'textarea', array(
                    'required' => false,
                    'attr' => array('maxlength' => 500),
                    'constraints' => array(new Length(array('max' => 500)))
                ))    
                ->add('descCondition', 'textarea', array(
                    'required' => false,
                    'attr' => array('maxlength' => 1000),
                    'constraints' => array(new Length(array('max' => 1000)))
                ))              
                ->add('inquiryEmail', 'email', array(
                    'required' => false,
                    'constraints' => array(
                        new \Symfony\Component\Validator\Constraints\Email(array('checkHost' => true))
                    )
                ))
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        $mainImageValidation = null;
        $imagesValidation = null;        
        $tariffsValidation = null;        
        $tariffs = $this->getDoctrineRepo('AppBundle:TalentTariff')->getTariffs($id);
        if ($request->getMethod() === 'POST') {
            $mainImageValidation = $this->mainImageValidation($mainImage);
            $imagesValidation = $this->imagesValidation($images);
            $tariffsValidation = $this->tariffsValidation($tariffs);;        
        }
        
        if ($form->isValid()   && $mainImageValidation === null && $imagesValidation === null && $tariffsValidation === null ) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $arr = array();
            for ($i = 1; $i <= 4; $i++)
                if ($data["subcategory{$i}Id"] !== '')
                    array_push ($arr, $subcatRepo->find($data["subcategory{$i}Id"]));
            

            
            // map fields, TODO: consider moving to Talent's method
            //<editor-fold> map fields            
            $talent->setName($data['name']);
            
            
            //edit2
            $talent->setDescription($data['description']);
            $talent->setAddrStreet($data['street']);
            $talent->setAddrNumber($data['number']);
            $talent->setAddrFlatNumber($data['flatNumber']);
            $talent->setAddrPostcode($data['postcode']);
            $talent->setAddrPlace($data['place']);   
            $talent->setLicence(intval($data['make_sure']));
            $talent->setAccept(intval($data['accept']));
            
            // handle video url
            if ($this->currentVideo !== null) {
                if ($talent->getVideo() !== null) {
                    $v = $talent->getVideo();
                    $em->remove($v);
                    $em->persist($this->currentVideo);
                    $talent->setVideo($this->currentVideo);
                    $em->flush();
                }
                else {
                    $em->persist($this->currentVideo);
                    $talent->setVideo($this->currentVideo);
                    $em->flush();
                }                
            }
            else {
                if ($talent->getVideo() !== null) {
                    $v = $talent->getVideo();
                    $em->remove($v);
                    $talent->setVideo(null);
                    $em->flush();                    
                }
                else {
                    // do nothing
                }
            }
            //Edit 3
            $talent->setTimeMorning($data['timeMorning']);
            $talent->setTimeAfternoon($data['timeAfternoon']);
            $talent->setTimeEvening($data['timeEvening']);
            $talent->setTimeWeekend($data['timeWeekend']);
            $talent->setOptClient($data['optClient']);
            $talent->setOptGroup($data['optGroup']);
            $talent->setDescReference($data['descReference']);
            $talent->setDescTarget($data['descTarget']);
            $talent->setDescScope($data['descScope']);
            $talent->setDescCondition($data['descCondition']);
            
            $talent->setInquiryEmail($data['inquiryEmail']);

            $owner->setPhonePrefix($data['phonePrefix']);
            $owner->setPhone($data['phone']);
            // subcats
            foreach ($talent->getSubcategories() as $sc)
                $talent->removeSubcategory($sc);
            foreach ($arr as $sc)
                $talent->addSubcategory($sc);            

            if ($data['defaultAddress'] === true) {
                $owner->setAddrStreet($talent->getAddrStreet());
                $owner->setAddrNumber($talent->getAddrNumber());
                $owner->setAddrFlatNumber($talent->getAddrFlatNumber());
                $owner->setAddrPostcode($talent->getAddrPostcode());
                $owner->setAddrPlace($talent->getAddrPlace());
            }
            
            //</editor-fold>
            $em->flush();
            $this->removeNewId($request, $id);
            
            return $this->redirectToRoute('admin_talent_list', array('id' => $id));
            
        }
        
        $mb = intval($this->getParameter('image_upload_max_size'));
        
        return $this->render('admin/talent/edit.html.twig', array(
            'talent' => $talent,
            'form' => $form->createView(),            
            'id' => $id,            
            'mainImage' => $mainImage,
            'images' => $images,
            'mainImageValidation' => $mainImageValidation,
            'imagesValidation' => $imagesValidation,
            'tariffsValidation' => $tariffsValidation,
            'megabytes' => $mb,
            'max_num_images' => $this->getParameter('equipment_max_num_images'),
            'type' => TariffType::$EINZELSTUNDEN->getId(),
            'tariffs' => $tariffs
        ));
    }     
    
    /**
     * @Route("/admin/talent/new", name="admin_talent_new")     
     */
    public function talentAddAction(Request $request) {                
        $subcatRepo = $this->getDoctrineRepo('AppBundle:Subcategory');
        $subcats = $subcatRepo->getAllForDropdown2(Category::TYPE_TALENT);
        $users = $this->getDoctrineRepo('AppBundle:User')->getAllForDropdown();
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder()                                
            ->add('subcategory1Id', 'choice', array(
                'choices' => $subcats,
                'choices_as_values' => false,
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('subcategory2Id', 'choice', array(
                'required' => false,
                'choices' => $subcats,
                'choices_as_values' => false
            ))
            ->add('subcategory3Id', 'choice', array(
                'required' => false,
                'choices' => $subcats,
                'choices_as_values' => false
            ))
            ->add('subcategory4Id', 'choice', array(
                'required' => false,
                'choices' => $subcats,
                'choices_as_values' => false
            ))
            ->add('userId', 'choice', array(
                'choices' => $users,
                'choices_as_values' => false,
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            $user = $this->getDoctrineRepo('AppBundle:User')->find($data['userId']);
            // handle multiple subcats
            $arr = array();
            for ($i = 1; $i <= 4; $i++)
                if ($data["subcategory{$i}Id"] !== '')
                    array_push ($arr, $subcatRepo->find($data["subcategory{$i}Id"]));
            
            
            $em = $this->getDoctrine()->getManager();
            
            $talent = new Talent();
            $talent->setUser($user);
            $talent->setStatus(Talent::STATUS_INCOMPLETE);         
            $talent->setName('');
            $talent->setUuid(Utils::getUuid());
            $talent->setAddrStreet($user->getAddrStreet());
            $talent->setAddrNumber($user->getAddrNumber());
            $talent->setAddrFlatNumber($user->getAddrFlatNumber());
            $talent->setAddrPostcode($user->getAddrPostcode());
            $talent->setAddrPlace($user->getAddrPlace());

            foreach ($arr as $sc)
                $talent->addSubcategory($sc);            
            
            $em->persist($talent);
            $em->flush();
            
            $this->addNewId($request, $talent->getId());            
  
            return $this->redirectToRoute('admin_talent_edit', array('id' => $talent->getId()));            
        }
        
        
        return $this->render('admin/talent/new.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    public function mainImageValidation($mainImage) {
        return $mainImage !== null ? null : 'Bitte lade zumindest ein Bild hoch';
    }
    public function imagesValidation($images) {
        $max = $this->getParameter('equipment_max_num_images');
        return count($images) <= $max ? null : sprintf('Bitte lade max. %s Bilder hoch', $max);
    }
    
    public function tariffsValidation($tariffs) {
        return count($tariffs) > 0 ? null : "Bitte definieren Sie mindestens eine Tarifoption";
    }
    
    public function validateTime($data, ExecutionContextInterface $context) {
        if (!$data['timeMorning'] && !$data['timeAfternoon'] && !$data['timeEvening'] && !$data['timeWeekend'] ) {
            $context->buildViolation('Bitte wähle zumindest einen Zeitpunkt, an dem du verfügbar sein kannst')->addViolation();
        }
    }
    
    public function validateAccept($value, ExecutionContextInterface $context) {
        if (!$value) {
            $context->buildViolation('Bitte Checkbox bestätigen')->atPath('accept')->addViolation();
        }            
    }
    public function validateMakeSure($value, ExecutionContextInterface $context) {
        if (!$value) {
            $context->buildViolation('Bitte Checkbox bestätigen')->atPath('make_sure')->addViolation();
        }            
    }
    
    public function validateVideoUrl($value, ExecutionContextInterface $context) {
        $this->currentVideo = null;
        if ($value == "") {
            return;
        }
        // vimeo
        preg_match(Video::RE_VIMEO, $value, $m);
        if (sizeof($m) !== 0) {
            $v = new Video();
            $v->setType(Video::TYPE_VIMEO);
            $v->setOriginalUrl($value);
            $v->setVideoId($m[1]);
            $v->setEmbedUrl("http://player.vimeo.com/video/{$m[1]}?api=1&player_id=player");            
            // try obtain thumbnail url
            try {
                $json = file_get_contents("http://vimeo.com/api/v2/video/{$m[1]}.json");
                $desc = json_decode($json, true);                        
                $v->setThumbnailUrl($desc[0]['thumbnail_small']);
            } catch (Exception $e) {
                $msg = sprintf("FAIL fetching thumbnail for '%s'", $value);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }            
            $this->currentVideo = $v;
            return;
        }        
        // youtube
        preg_match(Video::RE_YOUTUBE, $value, $m);
        if (sizeof($m) > 1) {
            $v = new Video();
            $v->setType(Video::TYPE_YOUTUBE);
            $v->setOriginalUrl($value);
            $v->setVideoId($m[1]);
            $v->setEmbedUrl("https://www.youtube.com/embed/{$m[1]}");
            $v->setThumbnailUrl("https://i.ytimg.com/vi/{$m[1]}/default.jpg");
            $this->currentVideo = $v;
            return;
        }
        $context->buildViolation('Bitte gib hier eine gültige Youtube- oder Vimeo-URL ein')->atPath('videoUrl')->addViolation();        
    }
    
    /**
     * @Route("admin-talent-main-image", name="admin-talent-main-image")
     */
    public function talentMainImageAction(Request $request) {  
        $file = $request->files->get('upl');
        if ($file->isValid()) {
            $uuid = Utils::getUuid();
            $path = 
                $this->getParameter('image_storage_dir') .
                DIRECTORY_SEPARATOR .
                'temp' .
                DIRECTORY_SEPARATOR;
            $ext = strtolower($file->getClientOriginalExtension());
            $name = sprintf("%s.%s", $uuid, $ext);
            $filename = $path . DIRECTORY_SEPARATOR . $name;

            $file->move($path, $name);

            $msg = null;

            $size = getimagesize($filename);
            if ($size[0] < 750 || $size[1] < 563) {
                $msg = "Das hochgeladene Bild ({$size[0]} x {$size[1]}) ist kleiner als erforderlich (bitte min. 750 x 563 px)";
            }
            
            $w = $file->getClientSize();
            $mb = intval($this->getParameter('image_upload_max_size'));
            if ($w > $mb * 1024 * 1024) { // 5 MB
                $msg = sprintf('Das hochgeladene Bild (%.2f MB) darf nicht größer als %d MB sein', $w / 1024 / 1024, $mb);
            }
            $exif = exif_imagetype($filename);
            if ($exif != IMAGETYPE_JPEG && $exif != IMAGETYPE_PNG) {
                $msg = 'Das hochgeladene Bildformat wurde nicht erkannt. Bitte nur die Bildformate JPG oder PNG verwenden';
            }
            

            if ($msg !== null) {
                unlink($filename);
                $resp = array('message' => $msg);
                return new JsonResponse($resp, Response::HTTP_NOT_ACCEPTABLE);
            }            

            $url = $this->getParameter('image_url_prefix') . 'temp/' . $uuid . '.' . $ext;
            $resp = array(
                'url' => $url,
                'name' => $name
            );
            return new JsonResponse($resp);
        }
                
        return new JsonResponse(array('message' => 'Es gab einen Fehler beim Hochladen der Bilder. Bitte versuch es noch einmal'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * @Route("admin-talent-main-image-save", name="admin-talent-main-image-save")
     */
    public function talentMainImageSaveAction(Request $request) { 
        $name = $request->get('name');
        $id = $request->get('id');
        $x = $request->get('x');
        $x2 = $request->get('x2');
        $y = $request->get('y');
        $y2 = $request->get('y2');
        $main = strtolower($request->get('main')) === 'true';
        $w = round($x2 - $x);
        $h = round($y2 - $y);
        
        $talent = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        // check security
        /*
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }        
        */
        // init vars 
        $sep = DIRECTORY_SEPARATOR;
        $path = $this->getParameter('image_storage_dir') . $sep . 'temp' . $sep . $name;
        $arr = explode('.', $name);
        $uuid = $arr[0];
        $ext = $arr[1];

        // scale image
        // <editor-fold>
        // check and calcualte size
        if ($w === 0 || $h == 0) {
            $size = getimagesize($path);
            $w = $size[0];
            $h = $size[1];
        }
        
        $nw = 750;
        if ($main) {
            $nh = 563;
        }
        else {
            $nh = $h / $w * $nw;
        }        
        
        // scale image
        $img = imagecreatefromstring(file_get_contents($path));
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, $x, $y, $nw, $nh, $w, $h);

        $path1 = $this->getParameter('image_storage_dir') . $sep . 'talent' . $sep . $uuid . '.' . $ext;
        $path2 = $this->getParameter('image_storage_dir') . $sep . 'talent' . $sep . 'original' . $sep . $uuid . '.' . $ext;
        
        if ($ext === 'jpg' || $ext == 'jpeg') {
            imagejpeg($dst, $path1, intval($this->getParameter('jpeg_compression_value')));
        }
        else if ($ext === 'png') {
            imagepng($dst, $path1, 9);
        }
        
        rename($path, $path2);
        
        imagedestroy($dst);
        // </editor-fold>
        
        // create thumbnail
        //<editor-fold>
        $nw = 360;
        if ($main) {
            $nh = 270;
        }
        else {
            $nh = $h / $w * $nw;
        }
        
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, $x, $y, $nw, $nh, $w, $h);

        $path2 = $this->getParameter('image_storage_dir') . $sep . 'talent' . $sep . 'thumbnail' . $sep . $uuid . '.' . $ext;
        
        if ($ext === 'jpg' || $ext == 'jpeg') {
            imagejpeg($dst, $path2, 85);
        }
        else if ($ext === 'png') {
            imagepng($dst, $path2, 9);
        }        
        imagedestroy($dst);        
        //</editor-fold>
        
        imagedestroy($img);

        // store entry in database
        //<editor-fold>
        $em = $this->getDoctrine()->getManager();
        $cnt = $this->getDoctrineRepo('AppBundle:Talent')->getImageCount($id);        
        
        $img = new Image();
        $img->setUuid($uuid);
        $img->setName($uuid);
        $img->setExtension($ext);
        $img->setPath('talent');
        $img->setOriginalPath('talent' . $sep . 'original');
        $img->setThumbnailPath('talent' . $sep . 'thumbnail');
        $em->persist($img);
        $em->flush();
        
        $timg = new TalentImage();
        $timg->setImage($img);
        $timg->setTalent($talent);
        $timg->setMain($main ? 1 : 0);
        $em->persist($timg);
        $em->flush();        
        //</editor-fold>
        
        $resp = array(
            'url' => $img->getUrlPath($this->getParameter('image_url_prefix')),
            'imgId' => $img->getId(),
            'main' => $timg->getMain()
        );
        return new JsonResponse($resp);
    }
    
    /**
     * @Route("admin-talent-image-delete/{tid}/{iid}", name="admin-talent-image-delete")
     */
    public function talentImageDeleteAction(Request $request, $tid, $iid) {
        // check security
        $talent = $this->getDoctrineRepo('AppBundle:Talent')->find($tid);
        /*
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        } 
         */       

        $timg = $this->getDoctrineRepo('AppBundle:Talent')->removeImage($tid, $iid, $this->getParameter('image_storage_dir'));
        
        return new JsonResponse(Response::HTTP_OK);
    }
    
    /**
     * @Route("admin-talent-image/{tid}", name="admin-talent-image")
     */
    public function talentImageAction(Request $request, $tid) {
        $file = $request->files->get('upl');
        if (!$file->isValid()) {
            return new JsonResponse(array('message' => 'Es gab einen Fehler beim Hochladen der Bilder. Bitte versuch es noch einmal'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $imgcnt = $this->getDoctrineRepo('AppBundle:Talent')->getTalentButMainImageCount($tid);
        $max = $this->getParameter('talent_max_num_images');
        if ($imgcnt >= $max) {
            $resp = array('message' => "Bitte lade max. {$max} Bilder hoch");
            return new JsonResponse($resp, Response::HTTP_NOT_ACCEPTABLE);
        }

        // validate
        $sep = DIRECTORY_SEPARATOR;
        $uuid = Utils::getUuid();
        $path = 
            $this->getParameter('image_storage_dir') . $sep . 'temp' . $sep;
        $ext = strtolower($file->getClientOriginalExtension());
        $origName = $file->getClientOriginalName();
        $name = sprintf("%s.%s", $uuid, $ext);
        $filename = $path . $sep . $name;

        $file->move($path, $name);

        $msg = null;

        $size = getimagesize($filename);
        $w = $size[0];
        $h = $size[1];
        if ($w < 650) {
            $msg = "{$origName}: das hochgeladene Bild ({$w} x {$h}) ist kleiner als erforderlich (bitte min. 650 px Breite)";
        }

        $wght = $file->getClientSize();
        $mb = intval($this->getParameter('image_upload_max_size'));
        if ($wght > $mb * 1024 * 1024) { // 5 MB
            $msg = sprintf('%s: das hochgeladene Bild (%.2f MB) darf nicht größer als %d MB sein', $origName, $wght / 1024 / 1024, $mb);
        }
        $exif = exif_imagetype($filename);
        if ($exif != IMAGETYPE_JPEG && $exif != IMAGETYPE_PNG) {
            $msg = "{$origName}: das hochgeladene Bildformat wurde nicht erkannt. Bitte nur die Bildformate JPG oder PNG verwenden";
        }

        // not valid, return error
        if ($msg !== null) {
            unlink($filename);
            $resp = array('message' => $msg);
            return new JsonResponse($resp, Response::HTTP_NOT_ACCEPTABLE);
        }
        
        // scale
        // check and calcualte size
        $nw = $w > 750 ? 750 : $w;
        $nh = $h / $w * $nw;
        // scale image
        $img = imagecreatefromstring(file_get_contents($filename));
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $path1 = $this->getParameter('image_storage_dir') . $sep . 'talent' . $sep . $uuid . '.' . $ext;
        $path2 = $this->getParameter('image_storage_dir') . $sep . 'talent' . $sep . 'original' . $sep . $uuid . '.' . $ext;
        
        if ($ext === 'jpg' || $ext == 'jpeg') {
            imagejpeg($dst, $path1, intval($this->getParameter('jpeg_compression_value')));
        }
        else if ($ext === 'png') {
            imagepng($dst, $path1, 9);
        }
        
        rename($filename, $path2);
        
        imagedestroy($dst);
        // create thumbnail
        //<editor-fold>
        $nw = 113;
        $nh = $h / $w * $nw;
        
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $path2 = $this->getParameter('image_storage_dir') . $sep . 'talent' . $sep . 'thumbnail' . $sep . $uuid . '.' . $ext;
        
        if ($ext === 'jpg' || $ext == 'jpeg') {
            imagejpeg($dst, $path2, 85);
        }
        else if ($ext === 'png') {
            imagepng($dst, $path2, 9);
        }        
        imagedestroy($dst);        
        //</editor-fold>
        
        imagedestroy($img);

        // store entry in database
        //<editor-fold>
        $em = $this->getDoctrine()->getManager();
        $talent = $this->getDoctrineRepo('AppBundle:Talent')->find($tid);
        
        $img = new Image();
        $img->setUuid($uuid);
        $img->setName($file->getClientOriginalName());
        $img->setExtension($ext);
        $img->setPath('talent');
        $img->setOriginalPath('talent' . $sep . 'original');
        $img->setThumbnailPath('talent' . $sep . 'thumbnail');
        $em->persist($img);
        $em->flush();
        
        $timg = new TalentImage();
        $timg->setImage($img);
        $timg->setTalent($talent);
        $timg->setMain(0);
        $em->persist($timg);
        $em->flush();        
        //</editor-fold>
        
        $resp = array(
            'url' => $img->getUrlPath($this->getParameter('image_url_prefix')),
            'thumbUrl' => $img->getThumbnailUrlPath($this->getParameter('image_url_prefix')),
            'imgId' => $img->getId()
        );
        return new JsonResponse($resp);    
    }
    
    /**
     * @Route("admin-talent-log/{id}", name="admin-talent-log")
     */
    public function logAction(Request $request, $id) {
        $repo = $this->getDoctrineRepo('AppBundle:Talent');
        $inqs = $repo->getTalentLog($id);
        $eq = $repo->find($id);
        
        return $this->render('admin/talent/log.html.twig', array(
            'inquiries' => $inqs,
            'talent' => $eq
        ));
    }    
    /**
     * @Route("admin-talent-delete/{id}", name="admin-talent-delete")
     */
    public function deleteAction(Request $request, $id) {                
        $dir = $this->getParameter('image_storage_dir');
        $this->getDoctrineRepo('AppBundle:Talent')->delete($id, $dir);
        return new JsonResponse("ok");
    }    

    /**
     * @Route("admin-talent-showcase-start/{id}/{value}", name="admin-talent-showcase-start")
     */
    public function showcaseStartAction($id, $value) {
        $repo = $this->getDoctrineRepo('AppBundle:Talent');
        $eq = $repo->find($id);
        
        // validate status
        if ($value == 1 && $eq->getStatus() !== Talent::STATUS_APPROVED)
            return new JsonResponse(array('type' => 'error', 'message' => "The item is not APPROVED."));
            
        $count = $repo->getShowcaseStartCount();
        if ($value == 1 && $count >= Common::TALENT_SHOWCASE_COUNT)
            return new JsonResponse(array('type' => 'error', 'message' => "There are already {$count} items selected"));
            
        // set value & save
        $eq->setShowcaseStart($value);
        $this->getDoctrine()->getManager()->flush();
                
        // check for warning
        $count = $repo->getShowcaseStartCount();
        $type = Common::TALENT_SHOWCASE_COUNT == $count ? "info" : "warning";
        
        return new JsonResponse(array('type' => $type, 'message' => "<strong>Start page</strong>: <strong>{$count}</strong> selected."));
    }    
    /**
     * @Route("admin-talent-showcase-talent/{id}/{value}", name="admin-talent-showcase-talent")
     */
    public function showcaseTalentAction($id, $value) {
        $repo = $this->getDoctrineRepo('AppBundle:Talent');
        $eq = $repo->find($id);
        $cnt = $repo->getShowcaseTalentCount();
        
        // validate status
        if ($value == 1 && $eq->getStatus() !== Talent::STATUS_APPROVED)
            return new JsonResponse(array('type' => 'error', 'message' => "The item is not APPROVED."));
            
        // validate max
        $max = Common::SHOWCASE_MAX;
        if ($value == 1 && $cnt == $max)
            return new JsonResponse(array('type' => 'error', 'message' => "<strong>Talent page</strong>: <strong>{$cnt}</strong> selected.<br/><strong>Maximum</strong>: <strong>{$max}</strong>."));
            
        // set value & save
        $eq->setShowcaseTalent($value);
        $this->getDoctrine()->getManager()->flush();
                
        // check for warning
        $min = Common::SHOWCASE_MIN;
        $cnt = $repo->getShowcaseTalentCount();
        if ($cnt < $min)
            return new JsonResponse(array('type' => 'warning', 'message' => "<strong>Talent page</strong>: <strong>{$cnt}</strong> selected.<br/><strong>Minimum</strong>: <strong>{$min}</strong>."));
        
        return new JsonResponse(array('type' => 'info', 'message' => "<strong>Talent page</strong>: <strong>{$cnt}</strong> selected."));
    }    
    /**
     * @Route("admin-talent-featured/{id}", name="admin-talent-featured")
     */
    public function featuredAction($id) {
        $tal = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        
        $tal->setFeatured(!$tal->getFeatured()); // toggle
        $this->getDoctrine()->getManager()->flush();                
        
        return new JsonResponse(array('message' => 'ok'));
    }    
    
    
    /**
     * @Route("/admin-talent-detail-form/{id}/{type}", name="admin-talent-detail-form")
     */
    public function formTariffAction(Request $request, $id, $type) {
        $repo = $this->getDoctrineRepo('AppBundle:TalentTariff');
        $tal = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        $tariff = $repo->getTariff($id, $type);
        $url = $this->generateUrl('admin-talent-detail-form', array('id' => $id, 'type' => $type));
        $form = $this->getTariffForm(intval($type), $tariff, $url);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
/*
 * merge leftover: remove if unnecessary
 * 
            // get subcategory
            $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($subcategoryId);
            $user = $this->getUser();
            // map fields, TODO: consider moving to Talent's method
            //<editor-fold> map fields
            $eq = new Talent();
            $eq->setUuid(Utils::getUuid());  
            $eq->setName($data['name']);
            $eq->setUser($user);
            $eq->addSubcategory($subcat);
            $eq->setPrice($data['price']);
            $eq->setRequestPrice($data['requestPrice'] ? 1 : 0);
            $eq->setStatus(Talent::STATUS_INCOMPLETE);
            //</editor-fold>
            // save to db
*/
            $em = $this->getDoctrine()->getManager();
                        
            if ($tariff === null) {
                $tariff = new TalentTariff();
                $tariff->setTalent($tal);
                $tariff->setType($type);
                $this->collectTariffFormData($tariff, $data, $type);
                $repo->insert($tariff, $tal->getId());
            }
            else {
                $this->collectTariffFormData($tariff, $data);
                $em->flush();
            }
        }
        
        $tmpl = sprintf('admin/talent/form_tariff%d.html.twig', $type);
        return $this->render($tmpl, array(
            'form' => $form->createView(),
            'tariffId' => $tariff !== null ? $tariff->getId() : null
        ));
    }
    
    private function getTariffForm($type, $tariff, $url) {
        $data = array();
        $data['type'] = strval($type);
        
        if ($type === TariffType::$EINZELSTUNDEN->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['requestPrice'] = $tariff->getRequestPrice() > 0;
                $data['discount'] = $tariff->getDiscount();
                $data['discountMinNum'] = $tariff->getDiscountMinNum();
                $data['discountPrice'] = $tariff->getDiscountPrice();                
            }
            $form = $this->createForm(new TariffType1(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$GRUPPENSTUNDEN->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['minNum'] = $tariff->getMinNum();
                $data['discount'] = $tariff->getDiscount();
                $data['discountMinNum'] = $tariff->getDiscountMinNum();
                $data['discountPrice'] = $tariff->getDiscountPrice();
            }
            $form = $this->createForm(new TariffType2(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$TOUR->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['minNum'] = $tariff->getMinNum();
                $data['discount'] = $tariff->getDiscount();
                $data['discountMinNum'] = $tariff->getDiscountMinNum();
                $data['discountPrice'] = $tariff->getDiscountPrice();
            }
            $form = $this->createForm(new TariffType3(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$_5ERBLOCK->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['duration'] = $tariff->getDuration();
            }
            $form = $this->createForm(new TariffType5(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$_10ERBLOCK->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['duration'] = $tariff->getDuration();
            }
            $form = $this->createForm(new TariffType6(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$TAGESSATZ->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['discount'] = $tariff->getDiscount();
                $data['discountMinNum'] = $tariff->getDiscountMinNum();
                $data['discountPrice'] = $tariff->getDiscountPrice();
            }
            $form = $this->createForm(new TariffType7(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$_20ERBLOCK->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['duration'] = $tariff->getDuration();
            }
            $form = $this->createForm(new TariffType8(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$WORKSHOP->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['minNum'] = $tariff->getMinNum();
                $data['discount'] = $tariff->getDiscount();
                $data['discountMinNum'] = $tariff->getDiscountMinNum();
                $data['discountPrice'] = $tariff->getDiscountPrice();
            }
            $form = $this->createForm(new TariffType9(), $data, array('action' => $url));            
        }
        
        return $form;        
    }
    
    private function collectTariffFormData($tariff, $data) {
        $tariff->setPrice(array_key_exists('price', $data) ? $data['price'] : null);
        $tariff->setMinNum(array_key_exists('minNum', $data) ? $data['minNum'] : null);
        $tariff->setDiscount(array_key_exists('discount', $data) ? ($data['discount'] ? 1 : 0) : null);
        $tariff->setDiscountMinNum(array_key_exists('discountMinNum', $data) ? $data['discountMinNum'] : null);
        $tariff->setDiscountPrice(array_key_exists('discountPrice', $data) ? $data['discountPrice'] : null);
        //$tariff->setOwnPlace(array_key_exists('ownPlace', $data) ? ($data['ownPlace'] ? 1 : 0) : null);
        $tariff->setDuration(array_key_exists('duration', $data) ? $data['duration'] : null);
        $tariff->setRequestPrice(array_key_exists('requestPrice', $data) ? ($data['requestPrice'] ? 1 : 0) : null);
        //$tariff->setAddrStreet(array_key_exists('addrStreet', $data) ? $data['addrStreet'] : null);
        //$tariff->setAddrNumber(array_key_exists('addrNumber', $data) ? $data['addrNumber'] : null);
        //$tariff->setAddrFlatNumber(array_key_exists('addrFlatNumber', $data) ? $data['addrFlatNumber'] : null);
        //$tariff->setAddrPostcode(array_key_exists('addrPostcode', $data) ? $data['addrPostcode'] : null);
        //$tariff->setAddrPlace(array_key_exists('addrPlace', $data) ? $data['addrPlace'] : null);
    }
    
    /**
     * @Route("/admin-talent-tariffs/{id}", name="admin-talent-tariffs")
     */
    public function tariffsAction(Request $request, $id) {
        $tariffs = $this->getDoctrineRepo('AppBundle:TalentTariff')->getTariffs($id);
        
        $arr = array();
        $i = 0;
        foreach ($tariffs as $t)
            $arr[] = array(
                'id' => $t->getId(),
                'name' => $t->getTypeName()
            );
        $result = array('list' => $arr);
      
        return new JsonResponse($result);
    }
    
}
