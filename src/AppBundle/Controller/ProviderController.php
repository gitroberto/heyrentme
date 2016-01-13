<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Discount;
use AppBundle\Entity\Equipment;
use AppBundle\Entity\Image;
use AppBundle\Utils\Utils;
use DateInterval;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\Exception;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProviderController extends BaseController {
            
    /**
     * @Route("/provider", name="provider")
     * @Route("/provider/")
     * @Route("/provider/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request) {
        $user = $this->getUser();        
        $offers = $this->getDoctrineRepo('AppBundle:Equipment')->getAllByUserId($user->getId());        
        
        return $this->render('provider/dashboard.html.twig', array( 
            'offers'=> $offers, 
            'image_url_prefix'=> $this->getParameter('image_url_prefix')            
        ));
    }
    
    /**
     * @Route("/provider/profil", name="profil")
     */
    public function profilAction(Request $request) {
        
        $session = $request->getSession();
        if ($request->getMethod() == "GET") {
            $session->set('UserAddFile', null);
        }
        else {
            $this->fileCount = count($session->get('UserAddFile'));
        }
        
        #$form = $this->createFormBuilder()->getForm();
        $user = $this->getUser();
        $form = $this->createFormBuilder(null)
                ->add('aboutMyself', 'textarea', array('required' => false, 'max_length' => 255 , 'data' => $user->getAboutMyself() ))
                ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            
            $ef = $session->get('UserAddFile');                        
            $em = $this->getDoctrine()->getManager();
            
            if ($ef != null){
                $oldImg = $user->getImage();
                if ($oldImg != null){
                    $img = $this->getDoctrineRepo('AppBundle:Image')->find($oldImg->getId());
                    $oldImgPath = 
                        $this->getParameter('image_storage_dir') .
                        DIRECTORY_SEPARATOR . 
                        'user' .
                        DIRECTORY_SEPARATOR .
                        $img->getUuid() .
                        '.' .
                        $img->getExtension();
                    if(file_exists($oldImgPath)) {
                        unlink($oldImgPath);
                    }

                    $em->remove($img);
                    $user->setImage(null);
                    $em->flush();
                }

                $fullpath = $ef[3];
                $destinationPath = 
                        $this->getParameter('image_storage_dir') .
                        DIRECTORY_SEPARATOR .
                        'user' .
                        DIRECTORY_SEPARATOR .
                        basename($fullpath);
                
                if (file_exists($fullpath)){
                    rename($fullpath, $destinationPath);
                }



                $img = new Image();
                $img->setUuid($ef[0]);
                $img->setName($ef[1]);
                $img->setExtension($ef[2]);
                $img->setPath('user');            

                $em->persist($img);
                $em->flush();

                $user->setImage($img);
                $session->set('UserAddFile', null);
            }
            $aboutMyself = $form["aboutMyself"]->getData();
            #if ( != null && $form["aboutMyself"] != ""){
            #    $aboutMyself = $form["aboutMyself"];
            #}
            
            $user->setAboutMyself($aboutMyself);
            $em->flush();
            
            
            
        }
                
        return $this->render('provider/profil.html.twig', array( 'form'=> $form->createView() ) );
    }
   
    /**
     * @Route("user-image", name="user-image")
     */
    public function userImage(Request $request) {                
        $file = $request->files->get('upl');
        if ($file->isValid()) {
            $session = $request->getSession();
            $eqFiles = $session->get('UserAddFile');
            
            $uuid = Utils::getUuid();
            $path = 
                $this->getParameter('image_storage_dir') .
                DIRECTORY_SEPARATOR .
                'temp' .
                DIRECTORY_SEPARATOR;
            $name = sprintf("%s.%s", $uuid, $file->getClientOriginalExtension());
            $fullPath = $path . $name;

            $file->move($path, $name);
            
            $ef = array(
                $uuid,
                $file->getClientOriginalName(),
                strtolower($file->getClientOriginalExtension()),
                $fullPath
            );

            $session->set('UserAddFile', $ef);
            
        }
        return new Response($status = 200);
    }
    
    protected $formHelper = null;
    /**
     * @Route("/provider/einstellungen", name="einstellungen")
     */
    public function einstellungenAction(Request $request) {
        
        $user = $this->getUser();
        
        //$form = $this->createForm(EinstellungenType::class, $user);
        // TODO: add server-side validation (zob. equipmentEdit3Action for phone)
        // TODO: remove max_length, see phone
        $form = $this->createFormBuilder(null)
                ->add('password', 'password', array( 'required'=>false, 'constraints' => array(
                            new Callback(array($this, 'validateOldPassword'))
                        ) ))
                ->add('newPassword', 'password', array( 'required'=>false, 'constraints' => array(
                            new Callback(array($this, 'validateNewPassword'))
                        ) ))
                ->add('repeatedPassword', 'password', array('required' => false))
                ->add('name', 'text', array('max_length' => 255 , 'data' => $user->getName() ))
                ->add('surname', 'text', array('max_length' => 255 , 'data' => $user->getSurname() ))
                ->add('phone', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'maxlength' => 10, 
                        'pattern' => '^[0-9]{1,10}$'),
                    'data' => $user->getPhone()
                ))
                ->add('phonePrefix', 'text', array(
                    'required' => false, 
                    'attr' => array('maxlength' => 3, 'pattern' => '^[0-9]{1,3}$'),
                    'data' => $user->getPhonePrefix() 
                ))
                ->add('iban', 'text', array('required' => false, 'data' => $user->getIban() ))
                ->add('bic', 'text', array('required' => false, 'data' => $user->getBic() ))
                ->getForm();
        $this->formHelper = $form;        
        $form->handleRequest($request);      
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $newPassword = $form['newPassword']->getData();
            if ($newPassword != null && $newPassword != ""){
                $encoder_service = $this->get('security.encoder_factory');
                $encoder = $encoder_service->getEncoder($user);                            
                $user->setPassword($encoder->encodePassword($newPassword, $user->getSalt()));
            }
            
            $user->setName($form['name']->getData());
            $user->setSurname($form['surname']->getData());
            
            $user->setPhone($form['phone']->getData());
            $user->setPhonePrefix($form['phonePrefix']->getData());
            
            $user->setIban($form['iban']->getData());
            $user->setBic($form['bic']->getData());
            
            $em->flush();
        }
      
        return $this->render('provider/einstellungen.html.twig', array(  
            'form' => $form->createView()
        ));
    }
    
    public function validateOldPassword($data, ExecutionContextInterface $context) {
        if ($this->formHelper != null && $this->formHelper['newPassword']->getData() != null) {
            $user = $this->getUser();            
            
            $encoder_service = $this->get('security.encoder_factory');
            $encoder = $encoder_service->getEncoder($user);            
            $providedOldPassword = $this->formHelper['password']->getData();
            $encoded_pass = $encoder->encodePassword($providedOldPassword, $user->getSalt());
            if ($encoded_pass != $user->getPassword()){
                $context->buildViolation('Provided password is incorrect. Please enter your current password.')
                        ->atPath('password')->addViolation();
            }
        }
    }
     
    public function validateNewPassword($data, ExecutionContextInterface $context) {
        if ($this->formHelper != null && $this->formHelper['newPassword']->getData() != null) {
            $newPassword = $this->formHelper['newPassword']->getData();
            $repeatedPassword = $this->formHelper['repeatedPassword']->getData();
            
            if ($newPassword != $repeatedPassword) {
                $context->buildViolation('Repeated password and password doesn\'t mach.')
                        ->atPath('repeatedPassword')->addViolation();
            }
            
            if (strlen($newPassword) < 6 ){
                $context->buildViolation('Password have to have at least 6 chars.')
                        ->atPath('newPassword')->addViolation();
            }            
        }
    }

    /**
     * @Route("/provider/equipment-delete/{id}", name="equipment-delete")
     */
    public function equipmentDeleteAction(Request $request, $id) {
        $equipment = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);

        // security check
        if ($this->getUser()->getId() !== $equipment->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        if (!$equipment) {
            throw $this->createNotFoundException('No equipment found for id '.$id);
        }
        
        $this->getDoctrineRepo('AppBundle:Image')->removeAllImages($equipment, $this->getParameter('image_storage_dir'));
                
        $em = $this->getDoctrine()->getManager();
        $em->remove($equipment);
        $em->flush();
        return $this->redirectToRoute("dashboard");
    }
    
    /**
     * @Route("/provider/equipment-edit-1/{id}", name="equipment-edit-1")
     */
    public function equipmentEdit1Action(Request $request, $id) {
        $equipment = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
        if (!$equipment) {
            throw $this->createNotFoundException();
        }        
        // security check
        if ($this->getUser()->getId() !== $equipment->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        // map fields, TODO: consider moving to Equipment's method
        //<editor-fold> map fields            
        $data = array(
            'name' => $equipment->getName(),
            'price' => $equipment->getPrice(),
            'deposit' => $equipment->getDeposit(),
            'value' => $equipment->getValue(),
            'priceBuy' => $equipment->getPriceBuy(),
            'invoice' => $equipment->getInvoice(),
            'industrial' => $equipment->getIndustrial()
        );
        //</editor-fold>
        
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder($data)
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 256))
                    )
                ))
                ->add('price', 'number', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 0))
                    )
                ))
                ->add('deposit', 'number', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 0))
                    )
                ))
                ->add('value', 'number', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 0))
                    )
                ))
                ->add('priceBuy', 'number', array(
                    'required' => false,
                    'constraints' => array(
                        new Range(array('min' => 0))
                    )
                ))
                ->add('invoice', 'checkbox', array('required' => false))
                ->add('industrial', 'checkbox', array('required' => false))
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();

            // map fields, TODO: consider moving to Equipment's method
            //<editor-fold> map fields            
            $equipment->setName($data['name']);
            $equipment->setPrice($data['price']);
            $equipment->setValue($data['value']);
            $equipment->setDeposit($data['deposit']);
            $equipment->setPriceBuy($data['priceBuy']);
            $equipment->setInvoice($data['invoice']);
            $equipment->setIndustrial($data['industrial']);
            //</editor-fold>
            
            // save to db
            $em = $this->getDoctrine()->getManager();
            if ($equipment->checkStatusOnSave()){
                $this->sendNewModifiedEquipmentInfoMessage($request, $eq);
            }
            $em->persist($equipment);
            $em->flush();
            
            return $this->redirectToRoute('equipment-edit-2', array('id' => $id));
        }
        
        return $this->render('provider\equipment_edit_step1.html.twig', array(
            'form' => $form->createView()
        ));
    }        
    /**
     * @Route("/provider/equipment-add-1/{subcategoryId}", name="equipment-add-1")
     */
    public function equipmentAdd1Action(Request $request, $subcategoryId) {
        
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder()
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 256))
                    )
                ))
                ->add('price', 'number', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 0))
                    )
                ))
                ->add('deposit', 'number', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 0))
                    )
                ))
                ->add('value', 'number', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 0))
                    )
                ))
                ->add('priceBuy', 'number', array(
                    'required' => false,
                    'constraints' => array(
                        new Range(array('min' => 0))
                    )
                ))
                ->add('invoice', 'checkbox', array('required' => false))
                ->add('industrial', 'checkbox', array('required' => false))
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            // get subcategory
            $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($subcategoryId);
            $user = $this->getUser();
            // map fields, TODO: consider moving to Equipment's method
            //<editor-fold> map fields            
            $eq = new Equipment();
            $eq->setName($data['name']);
            $eq->setUser($user);
            $eq->setSubcategory($subcat);
            $eq->setPrice($data['price']);
            $eq->setValue($data['value']);
            $eq->setDeposit($data['deposit']);
            $eq->setPriceBuy($data['priceBuy']);
            $eq->setInvoice($data['invoice']);
            $eq->setIndustrial($data['industrial']);
            //</editor-fold>
            // save to db
            $em = $this->getDoctrine()->getManager();
            if ($eq->checkStatusOnSave()){
                $this->sendNewModifiedEquipmentInfoMessage($request, $eq);
            }
            $em->persist($eq);
            $em->flush();
            
            $session = $request->getSession();
            $session->set('EquipmentEditId', $eq->getId());
            return $this->redirectToRoute('equipment-edit-2', array('id' => $eq->getId()));
        }
        
        return $this->render('provider\equipment_edit_step1.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/provider/equipment-edit-2/{id}", name="equipment-edit-2")
     */
    public function equipmentEdit2Action(Request $request, $id) {
        $session = $request->getSession();
        
        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
        //$eq = $this->getDoctrineRepo('AppBundle:Equipment')->find(117); //TODO: dev only! remove
        if (!$eq) {
            throw $this->createNotFoundException();
        }        
        // security check
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        // initialize form data
        $data = array( 
            'description' => $eq->getDescription(),
            'street' => $eq->getAddrStreet(),
            'number' => $eq->getAddrNumber(),
            'postcode' => $eq->getAddrPostcode(),
            'place' => $eq->getAddrPlace()
        );
        
        if ($request->getMethod() == "GET") {
            $session->set('EquipmentAddFileArray', array()); //initialize array of currently uploaded images
        }
        else {
            $this->fileCount = count($session->get('EquipmentAddFileArray'));
            $this->imageCount = count($eq->getImages());
        }
        
        
        // validation form
        //<editor-fold>        
        $form = $this->createFormBuilder($data, array(
                'constraints' => array(
                    new Callback(array($this, 'validateImages'))
                )
            ))
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
            ->add('postcode', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 4)),
                    new Regex(array('pattern' => '/^\d{4}$/', 'message' => 'Please fill in a valid postal code'))
                )
            ))
            ->add('place', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 128))
                )
            ))
            ->add('accept', 'checkbox', array(
                'required' => false,
                'constraints' => array(
                    new Callback(array($this, 'validateAccept'))
                )
            ))
            ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            // update Equipment object
            $data = $form->getData();
            // map fields
            //<editor-fold>
            $eq->setDescription($data['description']);
            $eq->setAddrStreet($data['street']);
            $eq->setAddrNumber($data['number']);
            $eq->setAddrPostcode($data['postcode']);
            $eq->setAddrPlace($data['place']);            
            //</editor-fold>
            $em = $this->getDoctrine()->getManager();
            if ($eq->checkStatusOnSave()){
                $this->sendNewModifiedEquipmentInfoMessage($request, $eq);
            }
            $em->flush();
            
            // store images
            $eqFiles = $session->get('EquipmentAddFileArray');
            $this->handleImages($eqFiles, $eq, $em);
            
            // clean up
            $session->remove('EquipmentAddFileArray');            
            $this->fileCount = null;
            
            return $this->redirectToRoute('equipment-edit-3', array('eqid' => $id));
        }
        
        return $this->render('provider\equipment_edit_step2.html.twig', array(
            'form' => $form->createView()
        ));
    }
    public function validateAccept($value, ExecutionContextInterface $context) {
        if (!$value) {
            $context->buildViolation('You must check this box')->atPath('accept')->addViolation();
        }            
    }
    public function validateMakeSure($value, ExecutionContextInterface $context) {
        if (!$value) {
            $context->buildViolation('You must check this box')->atPath('make_sure')->addViolation();
        }            
    }

    private $fileCount = null; // num of uploaded images; necessary for image validation
    private $imageCount = null; // num of existing images; necessary for image validation
    public function validateImages($data, ExecutionContextInterface $context) {
        $cnt = 0;
        if ($this->fileCount != null) {
            $cnt += $this->fileCount;
        }            
        if ($this->imageCount != null) {
            $cnt += $this->imageCount;
        }
        if ($cnt = 0) {
            $context->buildViolation('Please upload at least one image')->addViolation();
        }
    }
    private function handleImages($eqFiles, $eq, $em) {
        foreach ($eqFiles as $file) {
            // store the original, and image itself            
            $origFullPath = 
                $this->getParameter('image_storage_dir') .
                DIRECTORY_SEPARATOR .
                'equipment' .
                DIRECTORY_SEPARATOR .
                'original' .
                DIRECTORY_SEPARATOR .
                $file[0] . '.' . $file[2];
            $imgFullPath =
                $this->getParameter('image_storage_dir') .
                DIRECTORY_SEPARATOR .
                'equipment' .
                DIRECTORY_SEPARATOR .
                $file[0] . '.' . $file[2];
            rename($file[3], $origFullPath);
                
            
            // check image size
            $imgInfo = getimagesize($origFullPath);
            $ow = $imgInfo[0]; // original width
            $oh = $imgInfo[1]; // original height
            $r = $ow / $oh; // ratio
            $nw = $ow; // new width
            $nh = $oh; // new height
            $scale = False;
            
            if ($r > 1) {
                if ($ow > 1024) {
                    $nw = 1024;
                    $m = $nw / $ow; // multiplier
                    $nh = $oh * $m;
                    $scale = True;
                }
            }
            else {
                if ($oh > 768) {
                    $nh = 768;
                    $m = $nh / $oh; // multiplier
                    $nw = $ow * $m;
                    $scale = True;
                }
            }
            
            // scale the image
            if ($scale) {
                if ($file[2] == 'png') {
                    $img = imagecreatefrompng($origFullPath);
                }
                else {
                    $img = imagecreatefromjpeg($origFullPath);
                }
                $sc = imagescale($img, intval(round($nw)), intval(round($nh)), IMG_BICUBIC_FIXED);
                if ($file[2] == 'png') {
                    imagepng($sc, $imgFullPath);
                }
                else {
                    imagejpeg($sc, $imgFullPath);
                }
            }
            else {
                copy($origFullPath, $imgFullPath);
            }        

            // store entry in database
            $img = new Image();
            $img->setUuid($file[0]);
            $img->setName($file[1]);
            $img->setExtension($file[2]);
            $img->setPath('equipment');
            $img->setOriginalPath('equipment' . DIRECTORY_SEPARATOR . 'original');

            $em->persist($img);
            $em->flush();

            $eq->addImage($img);
            $em->flush();
        }
    }
    
    /**
     * @Route("equipment-image", name="equipment-image")
     */
    public function equipmentImage(Request $request) {        
        $file = $request->files->get('upl');
        if ($file->isValid()) {
            $session = $request->getSession();
            $eqFiles = $session->get('EquipmentAddFileArray');
            if (count($eqFiles) < 3) {
                $uuid = Utils::getUuid();
                $path = 
                    $this->getParameter('image_storage_dir') .
                    DIRECTORY_SEPARATOR .
                    'temp' .
                    DIRECTORY_SEPARATOR;
                $name = sprintf("%s.%s", $uuid, $file->getClientOriginalExtension());
                $fullPath = $path . $name;
                
                $f = $file->move($path, $name);
                
                $ef = array(
                    $uuid,
                    $file->getClientOriginalName(),
                    strtolower($file->getClientOriginalExtension()),
                    $fullPath
                );
                
                array_push($eqFiles, $ef);
                $session->set('EquipmentAddFileArray', $eqFiles);
            }
        }
        return new Response($status = Response::HTTP_OK);
    }
    
    /**
     * @Route("/provider/equipment-edit-3/{eqid}", name="equipment-edit-3")
     */
    public function equipmentEdit3Action(Request $request, $eqid) {
        $session = $request->getSession();
        $user = $this->getUser();
        
        //$eqid = 118; // TODO: remove this; dev only!
        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($eqid);
        if (!$eq) {
            throw $this->createNotFoundException();
        }        
        // security check
        if ($user->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        $data = array(
            'phone' => $user->getPhone(),
            'phonePrefix' => $user->getPhonePrefix()
        );
        
        // TODO: add server-side validation for features
        $form = $this->createFormBuilder($data, array(
                'constraints' => array(
                    new Callback(array($this, 'validatePhone'))
                )
            ))
            ->add('phone', 'text', array(
                'required' => false,
                'attr' => array(
                    'maxlength' => 10, 
                    'pattern' => '^[0-9]{1,10}$'),
                'constraints' => array(
                    new Regex(array('pattern' => '/^\d{1,10}$/', 'message' => 'Please fill in a valid phone number'))
                )
            ))
            ->add('phonePrefix', 'text', array(
                'required' => false, 
                'attr' => array('maxlength' => 3, 'pattern' => '^[0-9]{1,3}$'),
                'constraints' => array(
                    new Regex(array('pattern' => '/^\d{1,3}$/', 'message' => 'Please fill in a valid phone number'))
                )
            ))
            ->getForm();
      
        $form->handleRequest($request);
                        
        // TODO: add server-side validation
        if ($form->isValid()) {
            $data = $form->getData();
            // parse params
            //<editor-fold>
            $params = $request->request->all();
            $features = array();
            // first detect checkboxes and radios
            foreach ($params as $key => $val) {
                if (is_string($val) && strpos($key, 'section_') === 0) {
                    $id = intval($val);
                    $features[$id] = null;
                }
                else if (is_array($val) && strpos($key, 'section_') === 0) {
                    foreach ($val as $v) {
                        $id = intval($v);
                        $features[$id] = null;
                    }
                }
                
            }          
            // next, detect input[text]
            foreach ($params as $key => $val) {
                if (is_string($val) && strpos($key, 'text_') === 0 && !empty($val)) {                    
                    $id = intval(str_replace('text_', '', $key));                    
                    $features[$id] = $val;
                }
            }          
            //</editor-fold>
            
            $this->getDoctrineRepo('AppBundle:Equipment')->saveFeatures($eqid, $features);
            
            // save phone
            if (!empty($data['phone']) and !empty($data['phone'])) {
                $em = $this->getDoctrine()->getManager();
                $u = $em->getRepository('AppBundle:User')->find($user->getId());
                $u->setPhone($data['phone']);
                $u->setPhonePrefix($data['phonePrefix']);
                $em->flush();
            }
            
            

            if ($eq->getStatus() == Equipment::STATUS_INCOMPLETE){   
                #following part was added because otherwise equipment status change was not save in db
                $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($eqid);
                if (!$eq) {
                    throw $this->createNotFoundException();
                }  
                
                $em = $this->getDoctrine()->getManager();                
                $eq->changeStatus(Equipment::STATUS_NEW, null);
                $this->sendNewModifiedEquipmentInfoMessage($request, $eq);
                
                $em->flush();
            }         
           
            return $this->redirectToRoute('equipment-edit-4');
        }

        $features = $this->getDoctrineRepo('AppBundle:Equipment')->getFeaturesAsArray($eq->getId());
        
        return $this->render('provider\equipment_edit_step3.html.twig', array(
            'form' => $form->createView(),
            'subcategory' => $eq->getSubcategory(),
            'features' => $features,
            'featureSectionRepo' => $this->getDoctrineRepo('AppBundle:FeatureSection')
        ));
    }
    
    public function validatePhone($data, ExecutionContextInterface $context) {
        if (!empty($data['phone']) xor !empty($data['phonePrefix'])) {
            $context->buildViolation('Please provide phone number (both prefix and number)')->addViolation();
        }
    }

    /**
     * @Route("/provider/equipment-edit-4", name="equipment-edit-4")
     */
    public function equipmentEdit4Action(Request $request) {
        return $this->render('provider\equipment_edit_step4.html.twig');
    }    
    
    private function testIfStringConstainsInt($s)
    {
        return filter_var($s, FILTER_VALIDATE_INT) !== false;
    }
    
    private function IsParamValidInt(&$errors, $strvalue, $fieldName){
        
        $result = 0;
        if ($this->testIfStringConstainsInt($strvalue))
        {
            $result = (integer)$strvalue;
        } else {
            $errors[count($errors)] = "Please provide integer value for ".$fieldName;            
        }
        
        return $result;
    }
    
    /**
     * @Route("/provider/saveStatus", name="equipment-saveStatus")
     */
    public function saveStatusAction(Request $request) {

        $id = $request->get('id');
        $text = $request->get('text');             
        $errors = array();
        
        $discountType = $this->IsParamValidInt($errors, $request->get('discountType'), "discount type");
        $percent = $this->IsParamValidInt($errors, $request->get('percent'), "percent");
        $duration = $this->IsParamValidInt($errors, $request->get('duration'), "duration");
        //$discountType = (integer)$discountTypeStr;          
        //$percent = (integer)$request->get('percent');        
        //$duration = (integer)$request->get('duration');       
        
        $equipment = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
        
        // security check
        if ($this->getUser()->getId() !== $equipment->getUser()->getId()) {
            //return new Response($status = Response::HTTP_FORBIDDEN);
            $errors[count($errors)] = "Access denied.";
        }
        
        if (count($errors) == 0 && $discountType != -1  && $discountType != 0 && $equipment->getActiveDiscount() != null) {
            $errors[count($errors)] = "There already is active discount!";
        } else if (count($errors) == 0 && $discountType != -1  && $discountType != 0){
            if ($discountType != 1 && $discountType != 2){
                $errors[count($errors)] = "Unknown discount selected.";
            }
            
            if ($percent < -1 || $percent > 6 ) {
                $errors[count($errors)] = "Unknown percent selected.";
            }
            
            if (($discountType == 1 && ($duration < -1 || $duration > 5)) ||
                ($discountType == 2 && ($duration < -1 || $duration > 24))) {
                $errors[count($errors)] = "Unknown duration selected.";
            }
            
            
            if ($percent == -1 || $percent == 0) {
                $errors[count($errors)] = "Please select discount percent.";
            }
            
            if ($duration == -1 || $duration == 0) {
                $errors[count($errors)] = "Please select discount duration.";
            }
        }
        
        if (count($errors) > 0){
            $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;                    
            $resp = new JsonResponse($errors, $status);        
            return $resp; 
        }
        
        $result = "OK";
        $status = JsonResponse::HTTP_OK;
        try {
            

            $equipment->setStatus($text);            
            $em = $this->getDoctrine()->getManager();
            $em->persist($equipment);
            $em->flush();            
            
            $activeDiscount = $equipment->getActiveDiscount();
            
            if ($discountType != -1 && $discountType != 0 && $activeDiscount == null){
                 
            
                $discount = new Discount();            
                $discount->setType($discountType);
                $discount->setPercent($percent);
                $discount->setDuration($duration);

                $discount->setEquipment($equipment);                

                $now = new DateTime();                        
                $endDate = new DateTime();                        
                $discount->setCreatedAt($now);                
                $inverval = "";
                if ($discountType == 1) {
                    $inverval = "P".($duration*7) ."D" ;
                } else if ($discountType == 2){
                    $inverval = "PT". $duration ."H";
                }
                $endDate->add(new DateInterval($inverval));
                $discount->setExpiresAt($endDate);

                $em->persist($discount);
                $em->flush();
            } else if (($discountType == -1 || $discountType == 0) && $activeDiscount != null){
                $now = new DateTime();    
                $activeDiscount->setExpiresAt($now);
                $em->persist($activeDiscount);
                $em->flush();
            }
                
       
        } catch (Exception $ex) {
            $result = "Error.";
            $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        }
        
        $resp = new JsonResponse($result, $status);        
        return $resp;        
    }    
    
    public function sendNewModifiedEquipmentInfoMessage(Request $request, Equipment $eq)
    {      
                        
        $template = 'Emails/Equipment/new_modified_item.html.twig';        
        
        $url = $request->getSchemeAndHttpHost() . $this->generateUrl('admin_equipment_moderate', array('id' => $eq->getId()));        
        
        $emailHtml = $this->renderView($template, array(                                    
            'equipment' => $eq,
            'mailer_image_url_prefix' => $this->getParameter('mailer_image_url_prefix'),            
            'url' => $url
        ));
        
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject('New/modified equipment notification.')
            ->setFrom($from)
            ->setTo($eq->getUser()->getEmail())
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
    }
}
