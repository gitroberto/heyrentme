<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Discount;
use AppBundle\Entity\Equipment;
use AppBundle\Entity\Image;
use AppBundle\Entity\User;
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
        $equipments = $this->getDoctrineRepo('AppBundle:Equipment')->getAllByUserId($user->getId());        
        $talents = $this->getDoctrineRepo('AppBundle:Talent')->getAllByUserId($user->getId());        
        
        return $this->render('provider/dashboard.html.twig', array( 
            'equipments'=> $equipments, 
            'talents' => $talents,
            'image_url_prefix'=> $this->getParameter('image_url_prefix'),
            'user' => $user
        ));
    }
    
    /**
     * @Route("/provider/profil", name="profil")
     */
    public function profilAction(Request $request) {
        /*
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
         */       
        return $this->render('provider/profil.html.twig');
    }
   
    /**
     * @Route("user-image", name="user-image")
     */
    public function userImage(Request $request) {                
        $file = $request->files->get('upl');
        if ($file->isValid()) {            
            $uuid = Utils::getUuid();
            $path = 
                $this->getParameter('image_storage_dir') .
                DIRECTORY_SEPARATOR .
                'user' .
                DIRECTORY_SEPARATOR;
            $name = sprintf("%s.%s", $uuid, $file->getClientOriginalExtension());
            $fullPath = $path . $name;
            $file->move($path, $name);
            
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            
            $img = new Image();
            $img->setUuid($uuid);
            $img->setName($file->getClientOriginalName());
            $img->setExtension($file->getClientOriginalExtension());
            $img->setPath('user');            

            $em->persist($img);
            $em->flush();
            
            $oldImg = $user->getImage();
            if ($oldImg != null) {
                $user->setImage(null);
                $this->getDoctrineRepo('AppBundle:Image')->removeImage($oldImg, $this->getParameter('image_storage_dir'));
            }
            
            $user->setImage($img);
            $em->flush();
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
                        'pattern' => '^[0-9]{1,10}$'),
                    'data' => $user->getPhone()
                ))
                ->add('phonePrefix', 'text', array(
                    'required' => false, 
                    'attr' => array('maxlength' => 3, 'pattern' => '^[0-9]{1,3}$'),
                    'data' => $user->getPhonePrefix() 
                ))
                ->add('iban', 'text', array('required' => false, 'data' => $user->getIban(), 
                            #'attr' => array('maxlength' => 3, 'pattern' => '^[0-9]{1,3}$'),
                            'constraints' => array(
                                new Callback(array($this, 'ValidateIBAN'))
                        ) ))
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
    
    function ValidateIBAN($data, ExecutionContextInterface $context) {
        $result = true;
        
        if ($this->formHelper != null && $this->formHelper['iban']->getData() != null) {
            $iban = $this->formHelper['iban']->getData();
            
            $iban = strtolower(str_replace(' ','',$iban));
            $Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
            $Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

            if (!array_key_exists(substr($iban,0,2), $Countries)) {
                $result = false;
            }
            
            if($result && strlen($iban) == $Countries[substr($iban,0,2)]){

                $MovedChar = substr($iban, 4).substr($iban,0,4);
                $MovedCharArray = str_split($MovedChar);
                $NewString = "";

                foreach($MovedCharArray AS $key => $value){
                    if(!is_numeric($MovedCharArray[$key])){
                        $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                    }
                    $NewString .= $MovedCharArray[$key];
                }

                if(bcmod($NewString, '97') != 1)
                {                   
                    $result = false;
                }
            }
            else{
                $result = false;
            }   
        }
        
        if (!$result) {
            $context->buildViolation('IBAN number is incorrect.')
                        ->atPath('iban')->addViolation();
        }
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
            'industrial' => $equipment->getIndustrial(),
            'ageId' => $equipment->getAge()->getId()
        );
        //</editor-fold>
        
        // build form
        //<editor-fold>
        $ageArr = $this->getDoctrineRepo('AppBundle:EquipmentAge')->getAllForDropdown();        
        $form = $this->createFormBuilder($data)
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
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            $age = $this->getDoctrineRepo('AppBundle:EquipmentAge')->find($data['ageId']);            

            // map fields, TODO: consider moving to Equipment's method
            //<editor-fold> map fields            
            $equipment->setName($data['name']);
            $equipment->setPrice($data['price']);
            $equipment->setValue($data['value']);
            $equipment->setDeposit($data['deposit']);
            $equipment->setPriceBuy($data['priceBuy']);
            $equipment->setInvoice($data['invoice']);
            $equipment->setIndustrial($data['industrial']);
            $equipment->setAge($age);
            //</editor-fold>
            
            // save to db
            $em = $this->getDoctrine()->getManager();
            if ($equipment->checkStatusOnSave()){
                $this->sendNewModifiedEquipmentInfoMessage($request, $equipment);
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
        $ageArr = $this->getDoctrineRepo('AppBundle:EquipmentAge')->getAllForDropdown();        
        $form = $this->createFormBuilder()
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 22))
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
            $age = $this->getDoctrineRepo('AppBundle:EquipmentAge')->find($data['ageId']);            
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
            $eq->setAge($age);
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
        if (!$eq) {
            throw $this->createNotFoundException();
        }        
        // security check
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        // initialize form data
        $user = $this->getUser();
        $data = array( 
            'description' => $eq->getDescription(),
            'street' => $eq->getAddrStreet(),
            'number' => $eq->getAddrNumber(),
            'postcode' => $eq->getAddrPostcode(),
            'place' => $eq->getAddrPlace(),
            'phonePrefix' => $user->getPhonePrefix(),
            'phone' => $user->getPhone()
        );
        
        $this->imageCount = count($eq->getImages());
        
        
        // validation form
        //<editor-fold>        
        $form = $this->createFormBuilder($data, array(
                'constraints' => array(
                    new Callback(array($this, 'validateImages')),
                    new Callback(array($this, 'validatePhone'))
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
            
            
            // update user
            $user->setPhonePrefix($data['phonePrefix']);
            $user->setPhone($data['phone']);
            $em->flush();
            
            // clean up
            $this->fileCount = null;
            
            return $this->redirectToRoute('equipment-edit-3', array('eqid' => $id));
        }
        
        // clean up
        $this->fileCount = null;
        
        return $this->render('provider\equipment_edit_step2.html.twig', array(
            'form' => $form->createView(),
            'equipment' => $eq
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

    private $imageCount = null; // num of existing images; necessary for image validation
    public function validateImages($data, ExecutionContextInterface $context) {
        if ($this->imageCount < 1) {
            $context->buildViolation('Please upload at least one image')->addViolation();
        }
        else if ($this->imageCount > 3) {
            $context->buildViolation('Please upload max. 3 images')->addViolation();
        }
    }
    
    /**
     * @Route("equipment-image", name="equipment-image")
     */
    public function equipmentImageAction(Request $request) {  
        // todo: check security
        // todo: validate jpg/png
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
            if ($size[0] < 1024 || $size[1] < 768) {
                $msg = "Die hochgeladene Bild ({$size[0]} x {$size[1]}) kleiner ist als erforderlich 1024 x 768";
            }
            
            $w = $file->getClientSize();
            if ($w > 10 * 1024 * 1024) { // 10 MB
                $msg = sprintf('Die hochgeladene Bild (%.2f MB) größer ist als erlaubt  10 MB', $w / 1024 / 1024);
            }

            if ($msg !== null) {
                unlink($filename);
                $resp = array('message' => $msg);
                return new JsonResponse($resp, Response::HTTP_NOT_ACCEPTABLE);
            }            

            $url = $this->getParameter('image_url_prefix') . 'temp/' . $uuid . '.' . $file->getClientOriginalExtension();
            $resp = array(
                'url' => $url,
                'name' => $name,
                'width' => $size[0],
                'height' => $size[1]
            );
            return new JsonResponse($resp);
        }
                
        return new JsonResponse(array('message' => 'Fehler beim Hochladen von Bild zu Server ...'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * @Route("equipment-image-save", name="equipment-image-save")
     */
    public function equipmentImageSaveAction(Request $request) { 
        // todo: check security
        $name = $request->get('name');
        $id = $request->get('id');
        $x = $request->get('x');
        $x2 = $request->get('x2');
        $y = $request->get('y');
        $y2 = $request->get('y2');
        $w = round($x2 - $x);
        $h = round($y2 - $y);
        
        $sep = DIRECTORY_SEPARATOR;
        $path = $this->getParameter('image_storage_dir') . $sep . 'temp' . $sep . $name;
        $arr = explode('.', $name);
        $uuid = $arr[0];
        $ext = $arr[1];
        
        $img = imagecreatefromstring(file_get_contents($path));
        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $img, 0, 0, $x, $y, $w, $h, $w, $h);

        $path1 = $this->getParameter('image_storage_dir') . $sep . 'equipment' . $sep . $uuid . '.' . $ext;
        $path2 = $this->getParameter('image_storage_dir') . $sep . 'equipment' . $sep . 'original' . $sep . $uuid . '.' . $ext;
        
        if ($ext === 'jpg' || $ext == 'jpeg') {
            imagejpeg($dst, $path1, 95);
        }
        else if ($ext === 'png') {
            imagepng($dst, $path1, 9);
        }
        else if ($ext === 'gif') {
            imagegif($dst, $path1);
        }        
        
        rename($path, $path2);

        // store entry in database
        $img = new Image();
        $img->setUuid($uuid);
        $img->setName($uuid);
        $img->setExtension($ext);
        $img->setPath('equipment');
        $img->setOriginalPath('equipment' . $sep . 'original');

        $em = $this->getDoctrine()->getManager();
        $em->persist($img);
        $em->flush();

        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
        $eq->addImage($img);
        $em->flush();
        
        $resp = array(
            'url' => $img->getUrlPath($this->getParameter('image_url_prefix')),
            'imgId' => $img->getId()
        );
        return new JsonResponse($resp);
    }
    /**
     * @Route("equipment-image-delete/{eid}/{iid}", name="equipment-image-delete")
     */
    public function equipmentImageDeleteAction(Request $request, $eid, $iid) {
        // todo: check security
        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($eid);
        $img = $this->getDoctrineRepo('AppBundle:Image')->find($iid);
        $eq->removeImage($img);
        $this->getDoctrineRepo('AppBundle:Image')->removeImage($img, $this->getParameter('image_storage_dir'));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        
        return new JsonResponse(Response::HTTP_OK);
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
            'timeMorning' => $eq->getTimeMorning(),
            'timeAfternoon' => $eq->getTimeAfternoon(),
            'timeEvening' => $eq->getTimeEvening(),
            'timeWeekend' => $eq->getTimeWeekend(),
            'descType' => $eq->getDescType(),
            'descSpecial' => $eq->getDescSpecial(),
            'descCondition' => $eq->getDescCondition()
        );
        
        // TODO: add server-side validation for features
        $form = $this->createFormBuilder($data)
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
      
        $form->handleRequest($request);
                        
        // TODO: add server-side validation
        if ($form->isValid()) {
            $data = $form->getData();
            /*
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
            */
            
            // map fields
            //<editor-fold>
            $eq->setTimeMorning($data['timeMorning']);
            $eq->setTimeAfternoon($data['timeAfternoon']);
            $eq->setTimeEvening($data['timeEvening']);
            $eq->setTimeWeekend($data['timeWeekend']);
            $eq->setDescType($data['descType']);
            $eq->setDescSpecial($data['descSpecial']);
            $eq->setDescCondition($data['descCondition']);
            //</editor-fold>
            
            // save
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            if ($eq->getStatus() == Equipment::STATUS_INCOMPLETE){   
                #following part was added because otherwise equipment status change was not save in db
                $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($eqid);
                if (!$eq) {
                    throw $this->createNotFoundException();
                }  
                
                $eq->changeStatus(Equipment::STATUS_NEW, null);
                $this->sendNewModifiedEquipmentInfoMessage($request, $eq);
                
                $em->flush();
            }         
           
            return $this->redirectToRoute('equipment-edit-4');
        }

        //$features = $this->getDoctrineRepo('AppBundle:Equipment')->getFeaturesAsArray($eq->getId());
        
        return $this->render('provider\equipment_edit_step3.html.twig', array(
            'form' => $form->createView()/*,
            'subcategory' => $eq->getSubcategory(),
            'features' => $features,
            'featureSectionRepo' => $this->getDoctrineRepo('AppBundle:FeatureSection')*/
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
     * @Route("/provider/delete", name="delete-user")
     */
    public function deleteUserAction(Request $request) {  
        // todo: move query to repo
        $user = $this->getUser();
        $id = $user->getId();
                
        if (!$user) {
            throw $this->createNotFoundException();
        }  
        
        $sql = <<<EOT
    delete from user_rating where user_id= {$id};
        
    delete ebc
    from equipment_booking_cancel ebc
        inner join equipment_booking eb on ebc.booking_id = eb.id
        inner join equipment_inquiry ei on eb.inquiry_id = ei.id
        inner join equipment e
    where e.user_id = {$id};

    delete er
    from equipment_rating er
        inner join equipment_booking eb on er.booking_id = eb.id
        inner join equipment_inquiry ei on eb.inquiry_id = ei.id
        inner join equipment e
    where e.user_id = {$id} or ei.user_id = {$id};

    delete eb
    from equipment_booking eb
        inner join equipment_inquiry ei on eb.inquiry_id = ei.id
        inner join equipment e on ei.equipment_id = e.id
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ei
    from equipment_inquiry ei
        inner join equipment e on ei.equipment_id = e.id
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ei
    from equipment_image ei
        inner join equipment e on ei.equipment_id = e.id
    where e.user_id = {$id};
    
    delete er
    from equipment_rating er
        inner join equipment e on er.equipment_id = e.id
    where e.user_id = {$id};
    
    delete from equipment where user_id = {$id};
    
    delete ebc
    from talent_booking_cancel ebc
        inner join talent_booking eb on ebc.talent_booking_id = eb.id
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
        inner join talent e
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete er
    from talent_rating er
        inner join talent_booking eb on er.booking_id = eb.id
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
        inner join talent e
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete eb
    from talent_booking eb
        inner join talent_inquiry ei on eb.talent_inquiry_id = ei.id
        inner join talent e on ei.talent_id = e.id
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ei
    from talent_inquiry ei
        inner join talent e on ei.talent_id = e.id
    where e.user_id = {$id} or ei.user_id = {$id};
    
    delete ei
    from talent_image ei
        inner join talent e on ei.talent_id = e.id
    where e.user_id = {$id};
    
    delete er
    from talent_rating er
        inner join talent e on er.talent_id = e.id
    where e.user_id = {$id};
    
    delete from talent where user_id = {$id};
    
    delete from equipment_booking_cancel where user_id = {$id};
    delete from talent_booking_cancel where user_id = {$id};
    delete from equipment_inquiry where user_id = {$id};
    delete from talent_inquiry where user_id = {$id};
    delete from discount_code where user_id = {$id};
    delete from user_rating where user_id = {$id};
    delete from fos_user where id = {$id};
EOT;
        
        $em = $this->getDoctrine()->getEntityManager();
        $conn = $em->getConnection();
        $conn->executeUpdate($sql);        
        
        
        return $this->redirectToRoute("fos_user_security_logout");
    }
    /** 
     * @Route("/provider/save-status", name="save-status")
     */
    public function saveStatusAction(Request $request) {
        $id = $request->get('id');
        $type = $request->get('type');
        $status = $request->get('status');
        
        if ($type === 'equipment') {
            $obj = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
        }
        else {
            $obj = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        }
        
        // security check
        if ($this->getUser()->getId() !== $obj->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        // save
        $em = $this->getDoctrine()->getManager();
        $obj->setOfferStatus($status);
        $em->persist($obj);
        $em->flush();            
        
        return new JsonResponse(array("status" => "ok"));
    }
    
    public function sendNewModifiedEquipmentInfoMessage(Request $request, Equipment $eq)
    {      
                        
        $to = $this->getParameter('admin_email');
        $template = 'Emails/Equipment/new_modified_item.html.twig';        
        
        $url = $request->getSchemeAndHttpHost() . $this->generateUrl('admin_equipment_moderate', array('id' => $eq->getId()));        
        
        $emailHtml = $this->renderView($template, array(                                    
            'equipment' => $eq,
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),            
            'url' => $url
        ));
        
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject('New/modified equipment notification.')
            ->setFrom($from)
            ->setTo($to)
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
    }

    
    /*
    public function saveStatus2Action(Request $request) {

        $id = $request->get('id');
        $text = $request->get('text');             
        $errors = array();
        
//        $discountType = $this->IsParamValidInt($errors, $request->get('discountType'), "discount type");
//        $percent = $this->IsParamValidInt($errors, $request->get('percent'), "percent");
//        $duration = $this->IsParamValidInt($errors, $request->get('duration'), "duration");
        //$discountType = (integer)$discountTypeStr;          
        //$percent = (integer)$request->get('percent');        
        //$duration = (integer)$request->get('duration');       
        
        $equipment = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
        
        // security check
        if ($this->getUser()->getId() !== $equipment->getUser()->getId()) {
            //return new Response($status = Response::HTTP_FORBIDDEN);
            $errors[count($errors)] = "Access denied.";
        }
        
//        if (count($errors) == 0 && $discountType != -1  && $discountType != 0 && $equipment->getActiveDiscount() != null) {
//            $errors[count($errors)] = "There already is active discount!";
//        } else if (count($errors) == 0 && $discountType != -1  && $discountType != 0){
//            if ($discountType != 1 && $discountType != 2){
//                $errors[count($errors)] = "Unknown discount selected.";
//            }
//            
//            if ($percent < -1 || $percent > 5 ) {
//                $errors[count($errors)] = "Unknown percent selected.";
//            }
//            
//            if ($duration < -1 || $duration > 24) {
//                $errors[count($errors)] = "Unknown duration selected.";
//            }
//            
//            
//            if ($percent == -1 || $percent == 0) {
//                $errors[count($errors)] = "Please select discount percent.";
//            }
//            
//            if ($duration == -1 || $duration == 0) {
//                $errors[count($errors)] = "Please select discount duration.";
//            }
//        }
        
        if (count($errors) > 0){
            $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;                    
            $resp = new JsonResponse($errors, $status);        
            return $resp; 
        }
        
        $result = "OK";
        $status = JsonResponse::HTTP_OK;
        try {
            

            $equipment->setOfferStatus($text);            
            $em = $this->getDoctrine()->getManager();
            $em->persist($equipment);
            $em->flush();            
            
            $activeDiscount = $equipment->getActiveDiscount();
            
            if ($discountType != -1 && $discountType != 0 && $activeDiscount == null){
                 
                $p = 0; // actual percentage
                switch ($percent) {
                    case 1: $p = 10; break;
                    case 2: $p = 15; break;
                    case 3: $p = 20; break;
                    case 4: $p = 25; break;
                    case 5: $p = 30; break;
                }
            
                $discount = new Discount();            
                $discount->setType($discountType);
                $discount->setPercent($p);
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
    */
    
    
}
