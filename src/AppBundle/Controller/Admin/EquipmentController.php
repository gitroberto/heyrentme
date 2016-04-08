<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use AppBundle\Entity\Equipment;
use AppBundle\Entity\EquipmentImage;
use AppBundle\Entity\Image;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ExecutionContextInterface;

class EquipmentController extends BaseAdminController {
    
    const NEW_EQUIPMENT_IDS = 'AppBundle\Controller\Admin\EquipmentController\NewEquipmentIds';
    
    private function addNewId(Request $request, $id) {
        $session = $request->getSession();
        $ids = $session->get(EquipmentController::NEW_EQUIPMENT_IDS, array());
        array_push($ids, $id);
        $session->set(EquipmentController::NEW_EQUIPMENT_IDS, $ids);
    }
    private function removeNewId(Request $request, $id) {
        $session = $request->getSession();
        $ids = $session->get(EquipmentController::NEW_EQUIPMENT_IDS, array());
        $key = array_search($id, $ids);
        if ($key !== FALSE)
            unset($ids[$key]);
        $session->set(EquipmentController::NEW_EQUIPMENT_IDS, $ids);
    }
    private function clearNewIds($request) {
        // remove "hanging" equipments (new but not saved)
        $session = $request->getSession();

        $ids = $session->get(EquipmentController::NEW_EQUIPMENT_IDS, array());        
        if (count($ids) === 0)
            return;
        
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrineRepo('AppBundle:Equipment');
        
        foreach($ids as $id) {
            $eq = $repo->find($id);
            if ($eq !== null)
                $em->remove($eq);
        }
        $em->flush();
        
        $session->set(EquipmentController::NEW_EQUIPMENT_IDS, array()); // clear session var
    }
    
    /**
     * 
     * @Route("/admin/equipment", name="admin_equipment_list")
     */
    public function indexAction(Request $request) {        
        $this->clearNewIds($request);
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
        $stats = $res['stats'];
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        foreach ($dataRows as $dataRow) { // build single row
            $subcat = $dataRow->getSubcategory();
            $cat = $subcat->getCategory();
            $user = $dataRow->getUser();
            $stat = $stats[$dataRow->getId()];
            
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
            $cell[$i++] = $this->generateUrl('admin_equipment_edit', array('id' => $dataRow->getId()));
            $cell[$i++] = $this->generateUrl('admin_equipment_moderate', array('id' => $dataRow->getId()));
            $cell[$i++] = $this->generateUrl('admin-equipment-log', array('id' => $dataRow->getId()));
            $cell[$i++] = $stat['questions'];
            $cell[$i++] = $stat['bookings'];
            $cell[$i++] = $stat['cancels'];
            $cell[$i++] = $stat['revenue'];
            $cell[$i++] = $stat['discount'];            
            $cell[$i++] = $this->generateUrl('admin-equipment-delete', array('id' => $dataRow->getId()));
            
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
        $eqRepo = $this->getDoctrineRepo('AppBundle:Equipment');
        $equipment = $eqRepo->find($id);
        $mainImage = $eqRepo->getEquipmentMainImage($id);
        $images = $eqRepo->getEquipmentButMainImages($id);
        if (!$equipment) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        // security check
        //if ($this->getUser()->getId() !== $equipment->getUser()->getId()) {
        //    return new Response($status = Response::HTTP_FORBIDDEN);
        //}
        
        //Get eq owner
        $owner = $equipment->getUser();
        $subcats = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllForDropdown2(Category::TYPE_EQUIPMENT);
        
        // map fields, TODO: consider moving to Equipment's method
        //<editor-fold> map fields            
        $data = array(
            'subcategoryId' => $equipment->getSubcategory()->getId(),
            
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
                ->add('subcategoryId', 'choice', array(
                    'choices' => $subcats,
                    'choices_as_values' => false,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                
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
        $mainImageValidation = null;
        $imagesValidation = null;        
        if ($request->getMethod() === 'POST') {
            $mainImageValidation = $this->mainImageValidation($mainImage);
            $imagesValidation = $this->imagesValidation($images);
        }
        
        if ($form->isValid()  && $mainImageValidation === null && $imagesValidation === null) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $age = $this->getDoctrineRepo('AppBundle:EquipmentAge')->find($data['ageId']);            
            $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($data['subcategoryId']);

            // check for modaration relevant changes
            $changed = $equipment->getName() !== $data['name'];

            // map fields, TODO: consider moving to Equipment's method
            //<editor-fold> map fields           
            $equipment->setSubcategory($subcat);
            
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
            
            //EDIT3
            // map fields
            //<editor-fold>
            $equipment->setTimeMorning($data['timeMorning']);
            $equipment->setTimeAfternoon($data['timeAfternoon']);
            $equipment->setTimeEvening($data['timeEvening']);
            $equipment->setTimeWeekend($data['timeWeekend']);
            $equipment->setDescType($data['descType']);
            $equipment->setDescSpecial($data['descSpecial']);
            $equipment->setDescCondition($data['descCondition']);
            
            $owner->setPhonePrefix($data['phonePrefix']);
            $owner->setPhone($data['phone']);
            
            if ($data['defaultAddress'] === true) {
                $owner->setAddrStreet($equipment->getAddrStreet());
                $owner->setAddrNumber($equipment->getAddrNumber());
                $owner->setAddrFlatNumber($equipment->getAddrFlatNumber());
                $owner->setAddrPostcode($equipment->getAddrPostcode());
                $owner->setAddrPlace($equipment->getAddrPlace());
            }
            
            // save to db
            $em->flush();
            
            $this->removeNewId($request, $id);
   
            return $this->redirectToRoute('admin_equipment_list');
            
        }
        
        $mb = intval($this->getParameter('image_upload_max_size'));
        return $this->render('admin/equipment/edit.html.twig', array(            
            'equipment' => $equipment,
            'form' => $form->createView(),            
            'id' => $id,
            'mainImage' => $mainImage,
            'images' => $images,
            'mainImageValidation' => $mainImageValidation,
            'imagesValidation' => $imagesValidation,
            'megabytes' => $mb,
            'max_num_images' => $this->getParameter('equipment_max_num_images')
                
                
        ));
    }
    
    public function mainImageValidation($mainImage) {
        return $mainImage !== null ? null : 'Bitte lade zumindest ein Bild hoch';
    }
    public function imagesValidation($images) {
        $max = $this->getParameter('equipment_max_num_images');
        return count($images) <= $max ? null : sprintf('Bitte lade max. %s Bilder hoch', $max);
    }
    
    /**
     * @Route("/admin/equipment/new", name="admin_equipment_new")     
     */
    public function equipmentAddAction(Request $request) {                
        $subcats = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllForDropdown2(Category::TYPE_EQUIPMENT);
        $users = $this->getDoctrineRepo('AppBundle:User')->getAllForDropdown();
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder()                                
            ->add('subcategoryId', 'choice', array(
                'choices' => $subcats,
                'choices_as_values' => false,
                'constraints' => array(
                    new NotBlank()
                )
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
            $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($data['subcategoryId']);
            $user = $this->getDoctrineRepo('AppBundle:User')->find($data['userId']);
            $age = $this->getDoctrineRepo('AppBundle:EquipmentAge')->find(1);
            
            
            $em = $this->getDoctrine()->getManager();
            
            $eq = new Equipment();
            $eq->setUser($user);
            $eq->setSubcategory($subcat);
            $eq->setName('');
            $eq->setPrice(0);
            $eq->setValue(0);
            $eq->setDeposit(0);
            $eq->setStatus(Equipment::STATUS_INCOMPLETE);
            $eq->setInvoice(0);
            $eq->setIndustrial(0);
            $eq->setAge($age);
            $eq->setUuid(Utils::getUuid());
            $eq->setAddrStreet($user->getAddrStreet());
            $eq->setAddrNumber($user->getAddrNumber());
            $eq->setAddrFlatNumber($user->getAddrFlatNumber());
            $eq->setAddrPostcode($user->getAddrPostcode());
            $eq->setAddrPlace($user->getAddrPlace());
            
            $em->persist($eq);
            $em->flush();
            
            $this->addNewId($request, $eq->getId());            
  
            return $this->redirectToRoute('admin_equipment_edit', array('id' => $eq->getId()));            
        }
        
        
        return $this->render('admin/equipment/new.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Route("admin-equipment-main-image", name="admin-equipment-main-image")
     */
    public function equipmentMainImageAction(Request $request) {  
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
     * @Route("admin-equipment-main-image-save", name="admin-equipment-main-image-save")
     */
    public function equipmentMainImageSaveAction(Request $request) { 
        $name = $request->get('name');
        $id = $request->get('id');
        $x = $request->get('x');
        $x2 = $request->get('x2');
        $y = $request->get('y');
        $y2 = $request->get('y2');
        $main = strtolower($request->get('main')) === 'true';
        $w = round($x2 - $x);
        $h = round($y2 - $y);
        
        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
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

        $path1 = $this->getParameter('image_storage_dir') . $sep . 'equipment' . $sep . $uuid . '.' . $ext;
        $path2 = $this->getParameter('image_storage_dir') . $sep . 'equipment' . $sep . 'original' . $sep . $uuid . '.' . $ext;
        
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

        $path2 = $this->getParameter('image_storage_dir') . $sep . 'equipment' . $sep . 'thumbnail' . $sep . $uuid . '.' . $ext;
        
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
        $cnt = $this->getDoctrineRepo('AppBundle:Equipment')->getImageCount($id);        
        
        $img = new Image();
        $img->setUuid($uuid);
        $img->setName($uuid);
        $img->setExtension($ext);
        $img->setPath('equipment');
        $img->setOriginalPath('equipment' . $sep . 'original');
        $img->setThumbnailPath('equipment' . $sep . 'thumbnail');
        $em->persist($img);
        $em->flush();
        
        $eimg = new EquipmentImage();
        $eimg->setImage($img);
        $eimg->setEquipment($eq);
        $eimg->setMain($main ? 1 : 0);
        $em->persist($eimg);
        $em->flush();        
        //</editor-fold>
        
        $resp = array(
            'url' => $img->getUrlPath($this->getParameter('image_url_prefix')),
            'imgId' => $img->getId(),
            'main' => $eimg->getMain()
        );
        return new JsonResponse($resp);
    }
    /**
     * @Route("admin-equipment-image-delete/{eid}/{iid}", name="admin-equipment-image-delete")
     */
    public function equipmentImageDeleteAction(Request $request, $eid, $iid) {
        // check security
        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($eid);
        /*
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        } 
         */       

        $eimg = $this->getDoctrineRepo('AppBundle:Equipment')->removeImage($eid, $iid, $this->getParameter('image_storage_dir'));
        
        return new JsonResponse(Response::HTTP_OK);
    }
    
    /**
     * @Route("admin-equipment-image/{eid}", name="admin-equipment-image")
     */
    public function equipmentImageAction(Request $request, $eid) {
        $file = $request->files->get('upl');
        if (!$file->isValid()) {
            return new JsonResponse(array('message' => 'Es gab einen Fehler beim Hochladen der Bilder. Bitte versuch es noch einmal'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $imgcnt = $this->getDoctrineRepo('AppBundle:Equipment')->getEquipmentButMainImageCount($eid);
        $max = $this->getParameter('equipment_max_num_images');
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

        $path1 = $this->getParameter('image_storage_dir') . $sep . 'equipment' . $sep . $uuid . '.' . $ext;
        $path2 = $this->getParameter('image_storage_dir') . $sep . 'equipment' . $sep . 'original' . $sep . $uuid . '.' . $ext;
        
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

        $path2 = $this->getParameter('image_storage_dir') . $sep . 'equipment' . $sep . 'thumbnail' . $sep . $uuid . '.' . $ext;
        
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
        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($eid);
        
        $img = new Image();
        $img->setUuid($uuid);
        $img->setName($file->getClientOriginalName());
        $img->setExtension($ext);
        $img->setPath('equipment');
        $img->setOriginalPath('equipment' . $sep . 'original');
        $img->setThumbnailPath('equipment' . $sep . 'thumbnail');
        $em->persist($img);
        $em->flush();
        
        $eimg = new EquipmentImage();
        $eimg->setImage($img);
        $eimg->setEquipment($eq);
        $eimg->setMain(0);
        $em->persist($eimg);
        $em->flush();        
        //</editor-fold>
        
        $resp = array(
            'url' => $img->getUrlPath($this->getParameter('image_url_prefix')),
            'thumbUrl' => $img->getThumbnailUrlPath($this->getParameter('image_url_prefix')),
            'imgId' => $img->getId()
        );
        return new JsonResponse($resp);    
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

    /**
     * @Route("admin-equipment-log/{id}", name="admin-equipment-log")
     */
    public function logAction(Request $request, $id) {
        $repo = $this->getDoctrineRepo('AppBundle:Equipment');
        $inqs = $repo->getEquipmentLog($id);
        $eq = $repo->find($id);
        
        return $this->render('admin/equipment/log.html.twig', array(
            'inquiries' => $inqs,
            'equipment' => $eq
        ));
    }    
    /**
     * @Route("admin-equipment-delete/{id}", name="admin-equipment-delete")
     */
    public function deleteAction(Request $request, $id) {                
        $dir = $this->getParameter('image_storage_dir');
        $this->getDoctrineRepo('AppBundle:Equipment')->delete($id, $dir);
        return new JsonResponse("ok");
    }    
}
