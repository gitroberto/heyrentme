<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\Talent;
use AppBundle\Entity\TalentImage;
use AppBundle\Entity\TalentTariff;
use AppBundle\Entity\TariffType;
use AppBundle\Entity\Video;
use AppBundle\Form\Type\Tariff\TariffType1;
use AppBundle\Form\Type\Tariff\TariffType2;
use AppBundle\Form\Type\Tariff\TariffType3;
use AppBundle\Form\Type\Tariff\TariffType4;
use AppBundle\Form\Type\Tariff\TariffType5;
use AppBundle\Form\Type\Tariff\TariffType6;
use AppBundle\Form\Type\Tariff\TariffType7;
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
     * @Route("/provider/talent-basic-form/{id}", name="talent-basic-form")
     */
    public function formBasicAction(Request $request, $id) {
        $success = false;
        $tal = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        $data = array(
            'name' => $tal->getName(),
            'id' => $tal->getId()
        );
        
        $action = $this->generateUrl('talent-basic-form', array('id' => $id));
        $form = $this->createFormBuilder($data, array(
                    'constraints' => array(
                        new Callback(array($this, 'formBasicValidation'))
                    )
                ))
                ->setAction($action)
                ->add('name', 'text', array(
                    'required' => false,
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 32))
                    )
                ))
                ->add('id', 'hidden')
                ->getForm();
        
        $form->handleRequest($request);
        $statusChanged = false; // change relevant for email notification
        if ($form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            
            // check for modaration relevant changes
            $changed = $tal->getName() !== $data['name'];
            
            $tal->setName($data['name']);
            $em->flush();
            $success = true;            

            // handle status change and notification
            if ($changed) {
                $statusChanged = $this->getDoctrineRepo('AppBundle:Talent')->talentModified($id);
            }
            if ($statusChanged) {
                $this->sendNewModifiedTalentInfoMessage($request, $tal); 
                // todo: refactor: notification sent by repository/service, etc.; consider mapping fields within the method
            }            
        }
        
        return $this->render('talent/form_basic.html.twig', array(
            'form' => $form->createView(),
            'success' => $success,
            'statusChanged' => $statusChanged,
            'id' => $id
        ));
    }
    
    public function formBasicValidation($data, ExecutionContextInterface $context) {
        $count = $this->getDoctrineRepo('AppBundle:TalentTariff')->getTariffCount($data['id']);
        if ($count === 0) 
            $context->addViolation('Bitte definieren Sie mindestens eine Tarifoption');
    }
    
    /**
     * @Route("/provider/talent-detail-form/{id}/{type}", name="talent-detail-form")
     */
    public function formTariffAction(Request $request, $id, $type) {
        $repo = $this->getDoctrineRepo('AppBundle:TalentTariff');
        $tal = $this->getDoctrineRepo('AppBundle:Talent')->find($id);
        $tariff = $repo->getTariff($id, $type);
        $url = $this->generateUrl('talent-detail-form', array('id' => $id, 'type' => $type));
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
        
        $tmpl = sprintf('talent/form_tariff%d.html.twig', $type);
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
                $data['ownPlace'] = $tariff->getOwnPlace();
            }
            $form = $this->createForm(new TariffType2(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$WORKSHOP->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['minNum'] = $tariff->getMinNum();
                $data['discount'] = $tariff->getDiscount();
                $data['discountMinNum'] = $tariff->getDiscountMinNum();
                $data['discountPrice'] = $tariff->getDiscountPrice();
                $data['ownPlace'] = $tariff->getOwnPlace();
            }
            $form = $this->createForm(new TariffType3(), $data, array('action' => $url));            
        }
        else if ($type === TariffType::$PERFORMANCE->getId()) {
            if ($tariff !== null) {
                $data['price'] = $tariff->getPrice();
                $data['duration'] = $tariff->getDuration();
                $data['requestPrice'] = $tariff->getRequestPrice();
            }
            $form = $this->createForm(new TariffType4(), $data, array('action' => $url));            
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
        
        return $form;        
    }
    /**
     * @Route("/provider/talent-tariff-order/{id}/{ids}", name="talent-tariff-order")
     */
    public function tariffDeleteAction(Request $request, $id, $ids) {
        $arr = array_map('intval', explode(',', $ids));
        $this->getDoctrineRepo('AppBundle:TalentTariff')->saveOrder($id, $arr);
        return new JsonResponse("ok");
    }
    /**
     * @Route("/provider/talent-tariff-delete/{id}", name="talent-tariff-delete")
     */
    public function tariffOrderAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $tt = $this->getDoctrineRepo('AppBundle:TalentTariff')->find($id);
        $em->remove($tt);
        $em->flush();
        return new JsonResponse("ok");
    }
    private function collectTariffFormData($tariff, $data) {
        $tariff->setPrice(array_key_exists('price', $data) ? $data['price'] : null);
        $tariff->setMinNum(array_key_exists('minNum', $data) ? $data['minNum'] : null);
        $tariff->setDiscount(array_key_exists('discount', $data) ? ($data['discount'] ? 1 : 0) : null);
        $tariff->setDiscountMinNum(array_key_exists('discountMinNum', $data) ? $data['discountMinNum'] : null);
        $tariff->setDiscountPrice(array_key_exists('discountPrice', $data) ? $data['discountPrice'] : null);
        $tariff->setOwnPlace(array_key_exists('ownPlace', $data) ? ($data['ownPlace'] ? 1 : 0) : null);
        $tariff->setDuration(array_key_exists('duration', $data) ? $data['duration'] : null);
        $tariff->setRequestPrice(array_key_exists('requestPrice', $data) ? ($data['requestPrice'] ? 1 : 0) : null);
    }
    
    /**
     * @Route("/provider/talent-tariffs/{id}", name="talent-tariffs")
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
    
    
    /**
     * @Route("/provider/talent-add-1/{subcategoryId}", name="talent-add-1")
     */
    public function addAction(Request $request, $subcategoryId) {
        $em = $this->getDoctrine()->getManager();
        $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($subcategoryId);
        $user = $this->getUser();

        $tal = new Talent();
        $tal->setUuid(Utils::getUuid());  
        $tal->setName('');
        $tal->setUser($user);
        $tal->addSubcategory($subcat);
        $tal->setStatus(Talent::STATUS_INCOMPLETE);

        $em->persist($tal);
        $em->flush();
        
        $id = $tal->getId();
        $this->addNewId($request, $id);
        
        return $this->redirectToRoute('talent-edit-1', array('id' => $id));
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
        
        $tariffs = $this->getDoctrineRepo('AppBundle:TalentTariff')->getTariffs($id);
        return $this->render('talent/talent_edit_step1.html.twig', array(
            'complete' => false,
            'id' => $id,
            'statusChanged' => false,
            'type' => TariffType::$EINZELSTUNDEN->getId(),
            'tariffs' => $tariffs
        ));
    }        
    /**
     * @Route("/provider/talent-edit-1-old/{id}", name="talent-edit-1-old")
     */
    public function talentEdit1OldAction(Request $request, $id) {
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
            'price' => $talent->getPrice(),
            'requestPrice' => $talent->getRequestPrice() > 0
        );
        //</editor-fold>
        
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder($data, array(
                'error_bubbling' => false,
                'constraints' => array(
                    /*new Callback(array($this, 'validateStep1'))*/
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
                ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        $statusChanged = false; // change relevant for email notification
        if ($form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            

            // check for modaration relevant changes
            $changed = $talent->getName() !== $data['name'];
            
            // map fields, TODO: consider moving to Talent's method
            //<editor-fold> map fields            
            $talent->setName($data['name']);
            $talent->setPrice($data['price']);
            $talent->setRequestPrice($data['requestPrice'] ? 1 : 0);
            //</editor-fold>
            $em->flush();
            
            // handle status change and notification
            if ($changed) {
                $statusChanged = $this->getDoctrineRepo('AppBundle:Talent')->talentModified($id);
            }
            if ($statusChanged) {
                $this->sendNewModifiedTalentInfoMessage($request, $talent); 
                // todo: refactor: notification sent by repository/service, etc.; consider mapping fields within the method
            }
                        
            if (!$statusChanged) {            
                return $this->redirectToRoute('talent-edit-2', array('id' => $id));
            }
        }
        $complete = $talent->getStatus() != Talent::STATUS_INCOMPLETE;
        return $this->render('talent/talent_edit_step1.html.twig', array(
            'form' => $form->createView(),
            'complete' => $complete,
            'id' => $id,
            'statusChanged' => $statusChanged
        ));
    }        
    
    
    /**
     * @Route("/provider/talent-add-1-old/{subcategoryId}", name="talent-add-1-old")
     */
    public function talentAdd1Action(Request $request, $subcategoryId) {
        
        // build form
        //<editor-fold>
        $form = $this->createFormBuilder(null, array(
                'error_bubbling' => false,
                'constraints' => array(
                    /*new Callback(array($this, 'validateStep1'))*/
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
            $eq->setUuid(Utils::getUuid());  
            $eq->setName($data['name']);
            $eq->setUser($user);
            $eq->addSubcategory($subcat);
            $eq->setPrice($data['price']);
            $eq->setRequestPrice($data['requestPrice'] ? 1 : 0);
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
            'form' => $form->createView(),
            'complete' => false,
            'id' => $subcategoryId,
            'statusChanged' => false
        ));
    }
    
    
    
    public function validateStep1($data, ExecutionContextInterface $context) {
        $p = $data['price'];
        $rp = $data['requestPrice'];
        
        if ($p === null xor $rp) {
            $context->buildViolation('Sie müssen entweder Preis beim Check Preis auf Anfrage füllen.')->atPath('price')->addViolation();
        }
        if ($p !== null and ($p < 10 or $p > 500)) {
            $context->buildViolation('Preis muss eine Zahl zwischen 10 und 500 sein.')->atPath('price')->addViolation();
        }
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
        
        $this->getDoctrineRepo('AppBundle:Talent')->delete($talent->getId(), $this->getParameter('image_storage_dir'));
                
        return $this->redirectToRoute("dashboard");
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
            'flatNumber' => $eq->getAddrFlatNumber(),
            'number' => $eq->getAddrNumber(),
            'postcode' => $eq->getAddrPostcode(),
            'place' => $eq->getAddrPlace(),
            'phonePrefix' => $user->getPhonePrefix(),
            'phone' => $user->getPhone(),
            'make_sure' => $eq->getLicence() > 0,
            'accept' => $eq->getAccept() > 0
        );        
        if (empty($eq->getAddrStreet())) {
            $data['street'] = $user->getAddrStreet();
            $data['number'] = $user->getAddrNumber();
            $data['flatNumber'] = $user->getAddrFlatNumber();
            $data['postcode'] = $user->getAddrPostcode();
            $data['place'] = $user->getAddrPlace();      
        }
        
        // validation form
        //<editor-fold>        
        $form = $this->createFormBuilder($data)
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
            ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        $mainImageValidation = null;
        $imagesValidation = null;
        if ($request->getMethod() === 'POST') {
            $mainImageValidation = $this->mainImageValidation($mainImage);
            $imagesValidation = $this->imagesValidation($images);
        }
        
        $statusChanged = false; // change relevant for email notification
        if ($form->isValid() && $mainImageValidation === null && $imagesValidation === null) {
            // update Talent object
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();

            // check for modaration relevant changes
            $changed = $eq->getDescription() !== $data['description'];
            
            // map fields
            //<editor-fold>
            $eq->setDescription($data['description']);
            $eq->setAddrStreet($data['street']);
            $eq->setAddrNumber($data['number']);
            $eq->setAddrFlatNumber($data['flatNumber']);
            $eq->setAddrPostcode($data['postcode']);
            $eq->setAddrPlace($data['place']);   
            $eq->setLicence(intval($data['make_sure']));
            $eq->setAccept(intval($data['accept']));
            //</editor-fold>
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
            if ($data['defaultAddress'] === true) {
                $user->setAddrStreet($eq->getAddrStreet());
                $user->setAddrNumber($eq->getAddrNumber());
                $user->setAddrFlatNumber($eq->getAddrFlatNumber());
                $user->setAddrPostcode($eq->getAddrPostcode());
                $user->setAddrPlace($eq->getAddrPlace());
            }
            $user->setPhonePrefix($data['phonePrefix']);
            $user->setPhone($data['phone']);
            $em->flush();
            
            // handle status change and notification
            if ($changed) {
                $statusChanged = $this->getDoctrineRepo('AppBundle:Talent')->talentModified($id);
            }
            if ($statusChanged) {
                $this->sendNewModifiedTalentInfoMessage($request, $eq); 
                // todo: refactor: notification sent by repository/service, etc.; consider mapping fields within the method
            }            
            
            // clean up
            $this->fileCount = null;
            
            if (!$statusChanged) {
                return $this->redirectToRoute('talent-edit-3', array('eqid' => $id));
            }
        }

        // clean up
        $this->fileCount = null;
        $complete = $eq->getStatus() != Talent::STATUS_INCOMPLETE;
        $mb = intval($this->getParameter('image_upload_max_size'));
        return $this->render('talent/talent_edit_step2.html.twig', array(
            'form' => $form->createView(),
            'talent' => $eq,
            'mainImage' => $mainImage,
            'images' => $images,
            'mainImageValidation' => $mainImageValidation,
            'imagesValidation' => $imagesValidation,
            'complete' => $complete,
            'id' => $id,
            'statusChanged' => $statusChanged,
            'megabytes' => $mb,
            'max_num_images' => $this->getParameter('talent_max_num_images')
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
    public function mainImageValidation($mainImage) {
        return $mainImage !== null ? null : 'Bitte lade zumindest ein Bild hoch';
    }
    public function imagesValidation($images) {
        $max = $this->getParameter('talent_max_num_images');
        return count($images) <= $max ? null : sprintf('Bitte lade max. %s Bilder hoch', $max);
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
                    imagejpeg($sc, $imgFullPath, intval($this->getParameter('jpeg_compression_value')));
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
     * @Route("talent-main-image", name="talent-main-image")
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
     * @Route("talent-main-image-save", name="talent-main-image-save")
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
        // 
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
            imagejpeg($dst, $path2, intval($this->getParameter('jpeg_compression_value')));
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
        
        $eimg = new TalentImage();
        $eimg->setImage($img);
        $eimg->setTalent($eq);
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
     * @Route("talent-image/{eid}", name="talent-image")
     */
    public function talentImageAction(Request $request, $eid) {
        $file = $request->files->get('upl');
        if (!$file->isValid()) {
            return new JsonResponse(array('message' => 'Es gab einen Fehler beim Hochladen der Bilder. Bitte versuch es noch einmal'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $imgcnt = $this->getDoctrineRepo('AppBundle:Talent')->getTalentButMainImageCount($eid);
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
            imagejpeg($dst, $path2, intval($this->getParameter('jpeg_compression_value')));
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
        $eq = $this->getDoctrineRepo('AppBundle:Talent')->find($eid);
        
        $img = new Image();
        $img->setUuid($uuid);
        $img->setName($file->getClientOriginalName());
        $img->setExtension($ext);
        $img->setPath('talent');
        $img->setOriginalPath('talent' . $sep . 'original');
        $img->setThumbnailPath('talent' . $sep . 'thumbnail');
        $em->persist($img);
        $em->flush();
        
        $eimg = new TalentImage();
        $eimg->setImage($img);
        $eimg->setTalent($eq);
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
    
    /**
     * @Route("/provider/talent-edit-3/{eqid}", name="talent-edit-3")
     */
    public function talentEdit3Action(Request $request, $eqid) {
        $session = $request->getSession();
        $user = $this->getUser();
        
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
                        
        $statusChanged = false; // change relevant for email notification
        if ($form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            
            
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

            // check for modaration relevant changes
            $status = $eq->getStatus();
            $changed = $status !== Talent::STATUS_INCOMPLETE && (
                $eq->getDescReference() !== $data['descReference']
                || $eq->getDescTarget() !== $data['descTarget']
                || $eq->getDescScope() !== $data['descScope']
                || $eq->getDescCondition() !== $data['descCondition']
            );
            
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
            $em->flush();
            
            // handle status change and notification
            if ($status === Talent::STATUS_INCOMPLETE) {
                $eq->setStatus(Talent::STATUS_NEW);
                $em->flush();
                $statusChanged = true;
            }
            if ($changed) {
                $statusChanged = $this->getDoctrineRepo('AppBundle:Talent')->talentModified($eqid);
            }
            if ($statusChanged) {
                $this->sendNewModifiedTalentInfoMessage($request, $eq); 
                // todo: refactor: notification sent by repository/service, etc.; consider mapping fields within the method
            }            
           
            if (!$statusChanged) {            
                return $this->redirectToRoute('talent-edit-4', array('id' => $eqid));
            }
        }

        //$features = $this->getDoctrineRepo('AppBundle:Talent')->getFeaturesAsArray($eq->getId());
        $complete = $eq->getStatus() != Talent::STATUS_INCOMPLETE;
        return $this->render('talent/talent_edit_step3.html.twig', array(
            'form' => $form->createView(),
            'complete' => $complete,
            'id' => $eqid,
            'statusChanged' => $statusChanged/*,
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
     * @Route("/provider/talent-edit-4/{id}", name="talent-edit-4")
     */
    public function talentEdit4Action(Request $request, $id) {
        return $this->render('talent/talent_edit_step4.html.twig', array(
            'complete' => true,
            'id' => $id
        ));
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

    public function sendNewModifiedTalentInfoMessage(Request $request, Talent $eq) {                              
        $to = $this->getParameter('admin_email');
        $template = 'Emails\talent\new_modified_item.html.twig';        
        $parts = array();
        
        $url = $request->getSchemeAndHttpHost() . $this->generateUrl('admin_talent_moderate', array('id' => $eq->getId()));        
        // subject parts
        if ($eq->getStatus() === Talent::STATUS_NEW) {
            array_push($parts, "New");
        }
        else {
            array_push($parts, "Modified");
        }
        array_push($parts, "talent in");

        $subcat = $eq->getSubcategoriesAsString();// $eq->getSubcategory();
        $cat = $subcat->getCategory();
        array_push($parts, "{$cat->getName()} / {$subcat->getName()}"); 

        $subject = join(" ", $parts);
        
        $emailHtml = $this->renderView($template, array(                                    
            'talent' => $eq,
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),            
            'url' => $url
        ));
        
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
    }

    const NEW_TALENT_IDS = 'AppBundle\Controller\TalentController\NewTalentIds';
    
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
}
