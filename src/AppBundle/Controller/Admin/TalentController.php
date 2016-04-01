<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use AppBundle\Entity\Image;
use AppBundle\Entity\Talent;
use AppBundle\Entity\TalentImage;
use AppBundle\Utils\Utils;
use AppBundle\Entity\Video;
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
use Symfony\Component\Validator\ExecutionContextInterface;


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
        $res = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $sStatus);
        $dataRows = $res['rows'];
        $rowsCount = $res['count'];//$repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        foreach ($dataRows as $dataRow) { // build single row
            $subcat = $dataRow->getSubcategory();
            $cat = $subcat->getCategory();
            $user = $dataRow->getUser();

            $i=0;
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
            $cell[$i++] = $this->generateUrl('preview_talent', array('uuid'=>$dataRow->getUuid()));
            $cell[$i++] = $dataRow->getCreatedAt()->format('Y-m-d H:i');
            $cell[$i++] = $dataRow->getModifiedAt()->format('Y-m-d H:i');            
            $cell[$i++] = $this->generateUrl('admin_talent_edit', array('id'=>$dataRow->getId()));
            
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
    
    
    /**
     * @Route("/provider/talent/edit/{id}", name="admin_talent_edit")
     */
    public function talentEditAction(Request $request, $id) {        
        $talentRepo = $this->getDoctrineRepo('AppBundle:Talent');
        $talent = $talentRepo->find($id);
        $mainImage = $talentRepo->getTalentMainImage($id);
        $images = $talentRepo->getTalentButMainImages($id);
        
        if (!$talent) {
            return new Response(Response::HTTP_NOT_FOUND);
        }        
        $owner = $talent->getUser();
        // map fields, TODO: consider moving to Talent's method
        //<editor-fold> map fields            
        $data = array(
            'name' => $talent->getName(),
            'price' => $talent->getPrice(),
            'requestPrice' => $talent->getRequestPrice() > 0,
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
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 32))
                    )
                ))
                ->add('price', 'integer', array(
                    'required' => false
                ))
                ->add('requestPrice', 'checkbox', array(
                    'required' => false
                ))
                
                //edit 2
                ->add('description', 'textarea', array(
                    'attr' => array('maxlength' => 500),
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 500))
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
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        $mainImageValidation = null;
        $imagesValidation = null;        
        if ($request->getMethod() === 'POST') {
            $mainImageValidation = $this->mainImageValidation($mainImage);
            $imagesValidation = $this->imagesValidation($images);
        }
        
        if ($form->isValid()   && $mainImageValidation === null && $imagesValidation === null ) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            

            // check for modaration relevant changes
            $changed = $talent->getName() !== $data['name'];
            
            // map fields, TODO: consider moving to Talent's method
            //<editor-fold> map fields            
            $talent->setName($data['name']);
            $talent->setPrice($data['price']);
            $talent->setRequestPrice($data['requestPrice'] ? 1 : 0);
            
            
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
            
            $owner->setPhonePrefix($data['phonePrefix']);
            $owner->setPhone($data['phone']);
            
            if ($data['defaultAddress'] === true) {
                $owner->setAddrStreet($talent->getAddrStreet());
                $owner->setAddrNumber($talent->getAddrNumber());
                $owner->setAddrFlatNumber($talent->getAddrFlatNumber());
                $owner->setAddrPostcode($talent->getAddrPostcode());
                $owner->setAddrPlace($talent->getAddrPlace());
            }
            
            //</editor-fold>
            $em->flush();
            
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
            'megabytes' => $mb,
            'max_num_images' => $this->getParameter('equipment_max_num_images')
        ));
    }     
    
    /**
     * @Route("/admin/talent/new", name="admin_talent_new")     
     */
    public function talentAddAction(Request $request) {                
        $subcats = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllForDropdown2(Category::TYPE_TALENT);
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
            
            
            $em = $this->getDoctrine()->getManager();
            
            $talent = new Talent();
            $talent->setUser($user);
            $talent->setSubcategory($subcat);
            $talent->setStatus(Talent::STATUS_INCOMPLETE);         
            $talent->setName('');
            $talent->setUuid(Utils::getUuid());
            $talent->setAddrStreet($user->getAddrStreet());
            $talent->setAddrNumber($user->getAddrNumber());
            $talent->setAddrFlatNumber($user->getAddrFlatNumber());
            $talent->setAddrPostcode($user->getAddrPostcode());
            $talent->setAddrPlace($user->getAddrPlace());
            
            $em->persist($talent);
            $em->flush();
  
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
    
    
}
