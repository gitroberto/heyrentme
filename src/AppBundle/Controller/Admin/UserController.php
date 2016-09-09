<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Image;
use AppBundle\Entity\User;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ExecutionContextInterface;

class UserController extends BaseAdminController {
    
    protected $USER_STATUS_CHOICES = array(                        
        'OK' => User::STATUS_OK,
        'BLOCKED' => User::STATUS_BLOCKED
    );

    /**
     * @Route("/admin/users", name="admin_users_list")
     */
    public function indexAction(Request $request) {
        $code = null;
        
        $session = $request->getSession();
        if ($session->has('AdminNewUserCodeId')) {
            $id = $session->get('AdminNewUserCodeId');
            $code = $this->getDoctrineRepo('AppBundle:DiscountCode')->find($id);
            $session->remove('AdminNewUserCodeId');
        }
        
        return $this->render('admin/user/index.html.twig', array(
            'code' => $code
        ));
    }
    
    public function sendUserBlockedMessage(Request $request, User $user)
    {      
                        
        $template = 'Emails/User/mail_user_blocked.html.twig';       
        
        $emailHtml = $this->renderView($template, array(                                    
            'user' => $user,
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix')            
        ));
        
        $subject = "Dein Account wurde blockiert";
        
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($user->getEmail())
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
    }
    
    /**
     * 
     * @Route("/admin/user/details/{id}", name="admin-user-details")
     */
    public function detailsAction(Request $request, $id) {
        $user = $this->getDoctrineRepo('AppBundle:User')->find($id);

        if (!$user) {
            return new Response(Response::HTTP_NOT_FOUND);
        }        
        
        $form = $this->createFormBuilder($user)
                ->add('status', 'choice', array(
                    'choices' => $this->USER_STATUS_CHOICES,
                    'choices_as_values' => true,
                    'required' => true,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->getForm();

       
        //when the form is posted this method prefills entity with data from form
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            if ($user->getStatus() == User::STATUS_BLOCKED){
                $this->sendUserBlockedMessage($request, $user);
            }

            return $this->redirect($this->generateUrl('admin_users_list' ));
                    
        }
        
        
        return $this->render('admin/user/details.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }
    
    /**
     * @Route("/admin/user/add", name="admin-user-add")
     */
    public function addAction(Request $request) {
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder()
            ->add('status', 'choice', array(
                'choices' => $this->USER_STATUS_CHOICES,
                'choices_as_values' => true
            ))
            ->add('email', 'text', array(
                'required' => false,
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 255))
                )
            ))
            ->add('password', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 6, 'max' => 255))
                )
            ))
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 255))
                )
            ))
            ->add('surname', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 255))
                )
            ))
            ->add('aboutMyself', 'text', array(
                'required' => false,
                'constraints' => array(
                    new Length(array('max' => 255))
                )
            ))
            ->add('phone', 'text', array(
                'required' => false,
                'attr' => array(
                    'maxlength' => 10, 
                    'pattern' => '^[0-9]{1,10}$'),
                'constraints' => array(
                    new Regex(array('pattern' => '/^\d{1,10}$/', 'message' => 'Bitte gib hier eine gültige Telefonnummer ein'))
                )
            ))
            ->add('phonePrefix', 'text', array(
                'required' => false, 
                'attr' => array('maxlength' => 3, 'pattern' => '^[0-9]{1,3}$'),
                'constraints' => array(
                    new Regex(array('pattern' => '/^\d{1,3}$/', 'message' => 'Bitte gib hier eine gültige Vorwahl ein'))
                )
            ))
            ->add('iban', 'text', array(
                'required' => false,
                'constraints' => array(
                    new Callback(array($this, 'validateIBAN'))
                )
            ))
            ->add('bic', 'text', array(
                'required' => false,
                'constraints' => array(
                    new Regex(array('pattern' => '/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$$/', 'message' => 'BIC-Code ist nicht korrekt.'))
                )
            ))
            ->add('discount', 'checkbox', array(
                'required' => false
            ))
            ->getForm();
        //</editor-fold>
        
            $imageValidation = null; 
            $file = $request->files->get('upload');
            if ($request->getMethod() === 'POST' && $file === null) {
                $imageValidation = "Bitte laden Sie Ihr Bild";
            }
        
            $form->handleRequest($request);
            if ($form->isValid() && $imageValidation === null) {
                $data = $form->getData();
                
                $userMgr = $this->get('fos_user.user_manager');
                $user = $userMgr->createUser();
                $user->setStatus($data['status']);
                $user->setUsername($data['email']);
                $user->setEmail($data['email']);
                $user->setPlainPassword($data['password']);
                $user->setName($data['name']);
                $user->setSurname($data['surname']);
                $user->setPhonePrefix($data['phonePrefix']);
                $user->setPhone($data['phone']);
                $user->setIban($data['iban']);
                $user->setBic($data['bic']);
                $user->setAboutMyself($data['aboutMyself']);
                $user->setEnabled(true);

                $userMgr->updateCanonicalFields($user);                
                $userMgr->updateUser($user);                
                
                $em = $this->getDoctrine()->getManager();
                $file = $request->files->get('upload');
                if ($file != null && $file->isValid()) {
                    // save file
                    $uuid = Utils::getUuid();
                    $image_storage_dir = $this->getParameter('image_storage_dir');

                    $destDir = 
                            $image_storage_dir .
                            DIRECTORY_SEPARATOR .
                            'user' .
                            DIRECTORY_SEPARATOR;
                    $ext = strtolower($file->getClientOriginalExtension());
                    $destFilename = sprintf("%s.%s", $uuid, $ext);

                    $file->move($destDir, $destFilename);

                    // create object
                    $img = new Image();
                    $img->setUuid($uuid);
                    $img->setName($destFilename);
                    $img->setExtension($ext);
                    $img->setOriginalPath($file->getClientOriginalName());
                    $img->setPath('user');

                    $em->persist($img);
                    $user->setImage($img);
                }
                $em->flush();
                
                $code = null;
                if ($data['discount']) {
                    $code = $this->getDoctrineRepo('AppBundle:DiscountCode')->assignToUser($user);
                    $request->getSession()->set('AdminNewUserCodeId', $code->getId());
                }                
                
                return $this->redirectToRoute("admin_users_list");
            }
        
        return $this->render('admin/user/new.html.twig', array(
            'form' => $form->createView(),
            'imageValidation' => $imageValidation
        ));
    }
    /**
     * @Route("/admin/user/delete/{id}", name="admin-user-delete")
     */
    public function deleteUserAction(Request $request, $id) {
        $user = $this->getDoctrineRepo('AppBundle:User')->find($id);
        if (!$user) {
            return new Response(Response::HTTP_NOT_FOUND);
        }          
        $this->getDoctrineRepo('AppBundle:User')->deleteUserAccount($user, $this->getParameter('image_storage_dir'));
        
        return $this->redirectToRoute("admin_users_list");
    }
    
    /**
     * @Route("/admin/user/edit/{id}", name="admin-user-edit")
     */
    public function editAction(Request $request, $id) {
        $user = $this->getDoctrineRepo('AppBundle:User')->find($id);
        
        $data = array(
            'email' => $user->getEmail(),
            'password' => '',
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'phonePrefix' => $user->getPhonePrefix(),
            'phone' => $user->getPhone(),
            'iban' => $user->getIban(),
            'bic' => $user->getBic(),
            'aboutMyself' => $user->getAboutMyself(),
            'status' => $user->getStatus()
        );
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder($data)
            ->add('status', 'choice', array(
                'choices' => $this->USER_STATUS_CHOICES,
                'choices_as_values' => true
            ))
            ->add('email', 'text', array(
                'required' => false,
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 255))
                )
            ))
            ->add('password', 'text', array(
                'required' => false,
                'constraints' => array(
                    new Length(array('min' => 6, 'max' => 255))
                )
            ))
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 255))
                )
            ))
            ->add('surname', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 255))
                )
            ))
            ->add('aboutMyself', 'text', array(
                'required' => false,
                'constraints' => array(
                    new Length(array('max' => 255))
                )
            ))
            ->add('phone', 'text', array(
                'required' => false,
                'attr' => array(
                    'maxlength' => 10, 
                    'pattern' => '^[0-9]{1,10}$'),
                'constraints' => array(
                    new Regex(array('pattern' => '/^\d{1,10}$/', 'message' => 'Bitte gib hier eine gültige Telefonnummer ein'))
                )
            ))
            ->add('phonePrefix', 'text', array(
                'required' => false, 
                'attr' => array('maxlength' => 3, 'pattern' => '^[0-9]{1,3}$'),
                'constraints' => array(
                    new Regex(array('pattern' => '/^\d{1,3}$/', 'message' => 'Bitte gib hier eine gültige Vorwahl ein'))
                )
            ))
            ->add('iban', 'text', array(
                'required' => false,
                'constraints' => array(
                    new Callback(array($this, 'validateIBAN'))
                )
            ))
            ->add('bic', 'text', array(
                'required' => false,
                'constraints' => array(
                    new Regex(array('pattern' => '/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$$/', 'message' => 'BIC-Code ist nicht korrekt.'))
                )
            ))
            ->getForm();
        //</editor-fold>
        
            $imageValidation = null; 
            $file = $request->files->get('upload');
            $oldimg = $user->getImage();
            if ($request->getMethod() === 'POST' && $oldimg === null && $file === null) {
                $imageValidation = "Bitte laden Sie Ihr Bild";
            }
        
            $form->handleRequest($request);
            if ($form->isValid() && $imageValidation === null) {
                $data = $form->getData();
                
                $userMgr = $this->get('fos_user.user_manager');
                $user->setUsername($data['email']);
                $user->setEmail($data['email']);
                if (!empty($data['password'])) {
                    $user->setPlainPassword($data['password']);
                }
                $user->setStatus($data['status']);
                $user->setName($data['name']);
                $user->setSurname($data['surname']);
                $user->setPhonePrefix($data['phonePrefix']);
                $user->setPhone($data['phone']);
                $user->setIban($data['iban']);
                $user->setBic($data['bic']);
                $user->setAboutMyself($data['aboutMyself']);
                $user->setEnabled(true);

                $userMgr->updateCanonicalFields($user);                
                $userMgr->updateUser($user);                
                
                $em = $this->getDoctrine()->getManager();
                $file = $request->files->get('upload');
                if ($file != null && $file->isValid()) {

                    //remove old Image (both file from filesystem and entity from db)
                    
                    if ($oldimg !== null) {
                        $this->getDoctrineRepo('AppBundle:Image')->removeImage($oldimg, $this->getParameter('image_storage_dir'));
                        $user->setImage(null);
                        $em->flush();
                    }

                    // save file
                    $uuid = Utils::getUuid();
                    $image_storage_dir = $this->getParameter('image_storage_dir');

                    $destDir = 
                            $image_storage_dir .
                            DIRECTORY_SEPARATOR .
                            'user' .
                            DIRECTORY_SEPARATOR;
                    $ext = strtolower($file->getClientOriginalExtension());
                    $destFilename = sprintf("%s.%s", $uuid, $ext);

                    $file->move($destDir, $destFilename);

                    // create object
                    $img = new Image();
                    $img->setUuid($uuid);
                    $img->setName($destFilename);
                    $img->setExtension($ext);
                    $img->setOriginalPath($file->getClientOriginalName());
                    $img->setPath('user');

                    $em->persist($img);
                    $user->setImage($img);
                }
                $em->flush();

                return $this->redirectToRoute("admin_users_list");
            }
        
        return $this->render('admin/user/edit.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
            'imageValidation' => $imageValidation
        ));
    }
    
    function validateIBAN($data, ExecutionContextInterface $context) {
        $result = true;
        
        if ($data != null) {
            $iban = $data;
            
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
    
    /**
     * @Route("/admin/users/jsondata", name="admin_users_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        $email = $request->get('u_email');
        $name = $request->get('u_name');
        $surname = $request->get('u_surname');
        $status = $request->get('u_status');        
        
        $repo = $this->getDoctrineRepo('AppBundle:User');
        $res = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, 
                $email, $name, $surname, $status);
        $dataRows = $res['rows'];
        $rowsCount = $res['count'];//$repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result
        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $i = 0;
            $cell[$i++] = "";
            $cell[$i++] = $dataRow->getId();
            $cell[$i++] = $dataRow->getUsername();
            $cell[$i++] = $dataRow->getName();
            $cell[$i++] = $dataRow->getSurname();
            $cell[$i++] = $dataRow->getStatusStr();
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
}
