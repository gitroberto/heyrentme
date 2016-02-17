<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\Talent;
use AppBundle\Entity\TalentImage;
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
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TalentController extends BaseController {

    /**
     * @Route("/provider/talent-add-1/{subcategoryId}", name="talent-add-1")
     */
    public function talentAdd1Action(Request $request, $subcategoryId) {
        
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder()
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 32))
                    )
                ))
                ->add('price', 'integer', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 10, 'max' => 500))
                    )
                ))
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            // get subcategory
            $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($subcategoryId);
            $user = $this->getUser();
            // map fields, TODO: consider moving to Talent's method
            //<editor-fold> map fields
            $eq = new Talent();
            $eq->setName($data['name']);
            $eq->setUser($user);
            $eq->setSubcategory($subcat);
            $eq->setPrice($data['price']);
            $eq->setStatus(Talent::STATUS_INCOMPLETE);
            //</editor-fold>
            // save to db
            $em = $this->getDoctrine()->getManager();
            if ($eq->checkStatusOnSave()){
                $this->sendNewModifiedTalentInfoMessage($request, $eq);
            }
            $em->persist($eq);
            $em->flush();
            
            $session = $request->getSession();
            $session->set('TalentEditId', $eq->getId());
            return $this->redirectToRoute('talent-edit-2', array('id' => $eq->getId()));
        }
        
        return $this->render('talent/talent_edit_step1.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/provider/talent-delete/{id}", name="talent-delete")
     */
    public function talentDeleteAction(Request $request, $id) {
        $talent = $this->getDoctrineRepo('AppBundle:Talent')->find($id);

        // security check
        if ($this->getUser()->getId() !== $talent->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        if (!$talent) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        
        $this->getDoctrineRepo('AppBundle:Image')->removeAllImages($talent, $this->getParameter('image_storage_dir'));
                
        $em = $this->getDoctrine()->getManager();
        $em->remove($talent);
        $em->flush();
        return $this->redirectToRoute("dashboard");
    }
    
    /**
     * @Route("/provider/talent-edit-1/{id}", name="talent-edit-1")
     */
    public function talentEdit1Action(Request $request, $id) {
        $talent = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        if (!$talent) {
            return new Response(Response::HTTP_NOT_FOUND);
        }        
        // security check
        if ($this->getUser()->getId() !== $talent->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        // map fields, TODO: consider moving to Talent's method
        //<editor-fold> map fields            
        $data = array(
            'name' => $talent->getName(),
            'price' => $talent->getPrice()
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
                ->add('price', 'integer', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 10, 'max' => 2500))
                    )
                ))
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();

            // map fields, TODO: consider moving to Talent's method
            //<editor-fold> map fields            
            $talent->setName($data['name']);
            $talent->setPrice($data['price']);
            //</editor-fold>
            
            // save to db
            $em = $this->getDoctrine()->getManager();
            if ($talent->checkStatusOnSave()){
                $this->sendNewModifiedTalentInfoMessage($request, $talent);
            }
            $em->persist($talent);
            $em->flush();
            
            return $this->redirectToRoute('talent-edit-2', array('id' => $id));
        }
        
        return $this->render('talent/talent_edit_step1.html.twig', array(
            'form' => $form->createView()
        ));
    }        

    private $currentVideo;
    
    /**
     * @Route("/provider/talent-edit-2/{id}", name="talent-edit-2")
     */
    public function talentEdit2Action(Request $request, $id) {
        $session = $request->getSession();
        
        $eqRepo = $this->getDoctrineRepo('AppBundle:Talent');
        $eq = $eqRepo->getOne($id);
        $mainImage = $eqRepo->getTalentMainImage($id);
        $images = $eqRepo->getTalentButMainImages($id);

        if (!$eq) {
            return new Response(Response::HTTP_NOT_FOUND);
        }        
        // security check
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }
        
        // initialize form data
        $user = $this->getUser();
        $data = array( 
            'description' => $eq->getDescription(),
            'videoUrl' => $eq->getVideo() !== null ? $eq->getVideo()->getOriginalUrl() : null,
            'street' => $eq->getAddrStreet(),
            'number' => $eq->getAddrNumber(),
            'postcode' => $eq->getAddrPostcode(),
            'place' => $eq->getAddrPlace(),
            'phonePrefix' => $user->getPhonePrefix(),
            'phone' => $user->getPhone()
        );
        
        $this->imageCount = count($eq->getTalentImages());
        
        
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
            ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            // update Talent object
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
                $this->sendNewModifiedTalentInfoMessage($request, $eq);
            }
            $em->flush();
                        
            // handle video url
            if ($this->currentVideo !== null) {
                if ($eq->getVideo() !== null) {
                    $v = $eq->getVideo();
                    $em->remove($v);
                    $em->persist($this->currentVideo);
                    $eq->setVideo($this->currentVideo);
                    $em->flush();
                }
                else {
                    $em->persist($this->currentVideo);
                    $eq->setVideo($this->currentVideo);
                    $em->flush();
                }                
            }
            else {
                if ($eq->getVideo() !== null) {
                    $v = $eq->getVideo();
                    $em->remove($v);
                    $eq->setVideo(null);
                    $em->flush();                    
                }
                else {
                    // do nothing
                }
            }
            
            // update user
            $user->setPhonePrefix($data['phonePrefix']);
            $user->setPhone($data['phone']);
            $em->flush();
            
            // clean up
            $session->remove('TalentAddFileArray');            
            $this->fileCount = null;
            
            return $this->redirectToRoute('talent-edit-3', array('eqid' => $id));
        }

        // clean up
        $this->fileCount = null;
        
        return $this->render('talent/talent_edit_step2.html.twig', array(
            'form' => $form->createView(),
            'talent' => $eq,
            'mainImage' => $mainImage,
            'images' => $images
        ));
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
    
    private $imageCount = null; // num of existing images; necessary for image validation
    public function validateImages($data, ExecutionContextInterface $context) {
        if ($this->imageCount < 1) {
            $context->buildViolation('Bitte lade zumindest ein Bild hoch')->addViolation();
        }
        else if ($this->imageCount > Talent::MAX_NUM_IMAGES) {
            $num = Talent::MAX_NUM_IMAGES;
            $context->buildViolation('Bitte lade max. {$num} Bilder hoch')->addViolation();
        }
    }
    private function handleImages($eqFiles, $eq, $em) {
        foreach ($eqFiles as $file) {
            // store the original, and image itself            
            $origFullPath = 
                $this->getParameter('image_storage_dir') .
                DIRECTORY_SEPARATOR .
                'talent' .
                DIRECTORY_SEPARATOR .
                'original' .
                DIRECTORY_SEPARATOR .
                $file[0] . '.' . $file[2];
            $imgFullPath =
                $this->getParameter('image_storage_dir') .
                DIRECTORY_SEPARATOR .
                'talent' .
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
            $img->setPath('talent');
            $img->setOriginalPath('talent' . DIRECTORY_SEPARATOR . 'original');

            $em->persist($img);
            $em->flush();

            $eq->addImage($img);
            $em->flush();
        }
    }

    /**
     * @Route("talent-image", name="talent-image")
     */
    public function talentImageAction(Request $request) {  
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
                $msg = "Das hochgeladene Bild ({$size[0]} x {$size[1]}) ist kleiner als erforderlich (bitte min. 750 px Breite)";
            }
            
            $w = $file->getClientSize();
            if ($w > 5 * 1024 * 1024) { // 5 MB
                $msg = sprintf('Das hochgeladene Bild (%.2f MB) darf nicht größer als 5 MB sein', $w / 1024 / 1024);
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

            $url = $this->getParameter('image_url_prefix') . 'temp/' . $uuid . '.' . $file->getClientOriginalExtension();
            $resp = array(
                'url' => $url,
                'name' => $name
            );
            return new JsonResponse($resp);
        }
                
        return new JsonResponse(array('message' => 'Es gab einen Fehler beim Hochladen der Bilder. Bitte versuch es noch einmal'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * @Route("talent-image-save", name="talent-image-save")
     */
    public function talentImageSaveAction(Request $request) { 
        $name = $request->get('name');
        $id = $request->get('id');
        $x = $request->get('x');
        $x2 = $request->get('x2');
        $y = $request->get('y');
        $y2 = $request->get('y2');
        $main = strtolower($request->get('main')) === 'true';
        $w = round($x2 - $x);
        $h = round($y2 - $y);
        
        // check security
        $eq = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }        

        // init vars 
        $sep = DIRECTORY_SEPARATOR;
        $path = $this->getParameter('image_storage_dir') . $sep . 'temp' . $sep . $name;
        $arr = explode('.', $name);
        $uuid = $arr[0];
        $ext = $arr[1];

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
        
        $img = imagecreatefromstring(file_get_contents($path));
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, $x, $y, $nw, $nh, $w, $h);

        $path1 = $this->getParameter('image_storage_dir') . $sep . 'talent' . $sep . $uuid . '.' . $ext;
        $path2 = $this->getParameter('image_storage_dir') . $sep . 'talent' . $sep . 'original' . $sep . $uuid . '.' . $ext;
        
        if ($ext === 'jpg' || $ext == 'jpeg') {
            imagejpeg($dst, $path1, 95);
        }
        else if ($ext === 'png') {
            imagepng($dst, $path1, 9);
        }
        
        rename($path, $path2);

        // store entry in database
        $em = $this->getDoctrine()->getManager();
        $cnt = $this->getDoctrineRepo('AppBundle:Talent')->getImageCount($id);        
        
        $img = new Image();
        $img->setUuid($uuid);
        $img->setName($uuid);
        $img->setExtension($ext);
        $img->setPath('talent');
        $img->setOriginalPath('talent' . $sep . 'original');
        $em->persist($img);
        $em->flush();
        
        $eimg = new TalentImage();
        $eimg->setImage($img);
        $eimg->setTalent($eq);
        $eimg->setMain($main ? 1 : 0);
        $em->persist($eimg);
        $em->flush();        
        
        $resp = array(
            'url' => $img->getUrlPath($this->getParameter('image_url_prefix')),
            'imgId' => $img->getId(),
            'main' => $eimg->getMain()
        );
        return new JsonResponse($resp);
    }
    /**
     * @Route("talent-image-delete/{eid}/{iid}", name="talent-image-delete")
     */
    public function talentImageDeleteAction(Request $request, $eid, $iid) {
        // check security
        $eq = $this->getDoctrineRepo('AppBundle:Talent')->find($eid);
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response($status = Response::HTTP_FORBIDDEN);
        }        

        $eimg = $this->getDoctrineRepo('AppBundle:Talent')->removeImage($eid, $iid, $this->getParameter('image_storage_dir'));
        
        return new JsonResponse(Response::HTTP_OK);
    }
    
    
    /**
     * @Route("/provider/talent-edit-3/{eqid}", name="talent-edit-3")
     */
    public function talentEdit3Action(Request $request, $eqid) {
        $session = $request->getSession();
        $user = $this->getUser();
        
        //$eqid = 118; // TODO: remove this; dev only!
        $eq = $this->getDoctrineRepo('AppBundle:Talent')->find($eqid);
        if (!$eq) {
            return new Response(Response::HTTP_NOT_FOUND);
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
            'optClient' => $eq->getOptClient(),
            'optGroup' => $eq->getOptGroup(),
            'descReference' => $eq->getDescReference(),
            'descScope' => $eq->getDescScope(),
            'descTarget' => $eq->getDescTarget(),
            'descCondition' => $eq->getDescCondition()
        );
        
        // TODO: add server-side validation for features
        $form = $this->createFormBuilder($data, array('constraints' => array(
                            new Callback(array($this, 'validateTime'))
                        ) )
            )
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
        $this->formHelper = $form;
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
            
            $this->getDoctrineRepo('AppBundle:Talent')->saveFeatures($eqid, $features);
            */
            
            // map fields
            //<editor-fold>
            $eq->setTimeMorning($data['timeMorning']);
            $eq->setTimeAfternoon($data['timeAfternoon']);
            $eq->setTimeEvening($data['timeEvening']);
            $eq->setTimeWeekend($data['timeWeekend']);
            $eq->setOptClient($data['optClient']);
            $eq->setOptGroup($data['optGroup']);
            $eq->setDescReference($data['descReference']);
            $eq->setDescTarget($data['descTarget']);
            $eq->setDescScope($data['descScope']);
            $eq->setDescCondition($data['descCondition']);
            //</editor-fold>
            
            // save
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            if ($eq->getStatus() == Talent::STATUS_INCOMPLETE){   
                #following part was added because otherwise talent status change was not save in db
                $eq = $this->getDoctrineRepo('AppBundle:Talent')->find($eqid);
                if (!$eq) {
                    return new Response(Response::HTTP_NOT_FOUND);
                }  
                
                $eq->changeStatus(Talent::STATUS_NEW, null);
                $this->sendNewModifiedTalentInfoMessage($request, $eq);
                
                $em->flush();
            }         
           
            return $this->redirectToRoute('equipment-edit-4');
        }

        //$features = $this->getDoctrineRepo('AppBundle:Talent')->getFeaturesAsArray($eq->getId());
        
        return $this->render('talent/talent_edit_step3.html.twig', array(
            'form' => $form->createView()/*,
            'subcategory' => $eq->getSubcategory(),
            'features' => $features,
            'featureSectionRepo' => $this->getDoctrineRepo('AppBundle:FeatureSection')*/
        ));
    }
    
    public function validateTime($data, ExecutionContextInterface $context) {
        if (!$data['timeMorning'] && !$data['timeAfternoon'] && !$data['timeEvening'] && !$data['timeWeekend'] ) {
            $context->buildViolation('Bitte wähle zumindest einen Zeitpunkt, an dem du verfügbar sein kannst')->addViolation();
        }
    }
    
    /**
     * @Route("/provider/talent-edit-4", name="talent-edit-4")
     */
    public function talentEdit4Action(Request $request) {
        return $this->render('provider\talent_edit_step4.html.twig');
    }    

    /**
     * @Route("/provider/saveStatus", name="talent-saveStatus")
     */
    public function saveStatusAction(Request $request) {

        $id = $request->get('id');
        $text = $request->get('text');             
        $errors = array();
        
//        $discountType = $this->IsParamValidInt($errors, $request->get('discountType'), "discount type");
//        $percent = $this->IsParamValidInt($errors, $request->get('percent'), "percent");
//        $duration = $this->IsParamValidInt($errors, $request->get('duration'), "duration");
        //$discountType = (integer)$discountTypeStr;          
        //$percent = (integer)$request->get('percent');        
        //$duration = (integer)$request->get('duration');       
        
        $talent = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        
        // security check
        if ($this->getUser()->getId() !== $talent->getUser()->getId()) {
            //return new Response($status = Response::HTTP_FORBIDDEN);
            $errors[count($errors)] = "Access denied.";
        }
        
//        if (count($errors) == 0 && $discountType != -1  && $discountType != 0 && $talent->getActiveDiscount() != null) {
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
            

            $talent->setOfferStatus($text);            
            $em = $this->getDoctrine()->getManager();
            $em->persist($talent);
            $em->flush();            
            
            /*
            $activeDiscount = $talent->getActiveDiscount();
            
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

                $discount->setTalent($talent);                

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
                
            */
        } catch (Exception $ex) {
            $result = "Error.";
            $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        }
        
        $resp = new JsonResponse($result, $status);        
        return $resp;        
    }    

    public function sendNewModifiedTalentInfoMessage(Request $request, Talent $eq)
    {      
                        
        $to = $this->getParameter('admin_email');
        $template = 'Emails\talent\new_modified_item.html.twig';        
        
        $url = $request->getSchemeAndHttpHost() . $this->generateUrl('admin_talent_moderate', array('id' => $eq->getId()));        
        
        $emailHtml = $this->renderView($template, array(                                    
            'talent' => $eq,
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),            
            'url' => $url
        ));
        
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject('New/modified talent notification.')
            ->setFrom($from)
            ->setTo($to)
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
    }
}
