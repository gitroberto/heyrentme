<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EquipmentBooking;
use AppBundle\Entity\EquipmentBookingCancel;
use AppBundle\Entity\DiscountCode;
use AppBundle\Entity\EquipmentRating;
use AppBundle\Entity\EquipmentInquiry;
use AppBundle\Entity\UserRating;
use AppBundle\Utils\Utils;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ExecutionContextInterface;

class BookingController extends BaseController {

    /**
     * @Route("/booking/inquiry/{id}/{dateFrom}/{dateTo}", name="booking-inquiry")
     */
    public function inquiryAction(Request $request, $id, $dateFrom, $dateTo) {
        $loggedIn = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED'); // user logged in
        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find($id);
        
        // init/calculate inquiry details
        //<editor-fold>
        $from = DateTime::createFromFormat('Y-m-d\TH:i+', $dateFrom);
        $to = DateTime::createFromFormat('Y-m-d\TH:i+', $dateTo);
        $days = $to->diff($from)->days;
        $price = ($days) * $eq->getActivePrice();
        $inquiry = array(
            'from' => $from,
            'to' => $to,
            'days' => $days,
            'price' => $price,
            'whereabouts' => $eq->getIncompleteAddressAsString()
        );
        //</editor-fold>
        
        // build form
        //<editor-fold>
        $url = $this->generateUrl('booking-inquiry', array(
            'id' => $id,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ));
        
        $builder = $this->createFormBuilder()
            ->setAction($url);
        if (!$loggedIn) {
            $builder->add('name', 'text', array(
                'attr' => array (
                    'max-length' => 128
                ),
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 128))
                )
            ))
            ->add('email', 'email', array(
                'attr' => array (
                    'max-length' => 128
                ),
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 128)),
                    new Email(array('checkHost' => true))
                )
            ));
        }
        $builder->add('message', 'textarea', array(
                'constraints' => array(
                    new NotBlank(),
                 )
            ));
        $form = $builder->getForm();
        //</editor-fold>
       
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            $inq = new EquipmentInquiry();
            // map fields & save
            //<editor-fold>
            $inq->setEquipment($eq);
            if (!$loggedIn) {
                $inq->setName($data['name']);
                $inq->setEmail($data['email']);
            }
            else {
                $inq->setUser($this->getUser());                
            }
            $inq->setMessage($data['message']);
            $inq->setFromAt($inquiry['from']);
            $inq->setToAt($inquiry['to']);
            $inq->setPrice($inquiry['price']);
            $inq->setDeposit($eq->getDeposit());
            $inq->setPriceBuy($eq->getPriceBuy());
            
            // TODO: filter out any contact data from the message (phone, email)
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($inq);
            $em->flush();
            //</editor-fold>
            
            // send email
            //<editor-fold>
            // prepare params
            $provider = $eq->getUser();
            $url = $request->getSchemeAndHttpHost() .
                    $this->generateUrl('booking-response', array('id' => $inq->getId()));
            $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
            $emailHtml = $this->renderView('Emails\mail_to_provider_offer_request.html.twig', array(
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                'provider' => $provider,
                'inquiry' => $inq,
                'equipment' => $eq,
                'url' => $url
            ));
            $message = Swift_Message::newInstance()
                ->setSubject('Du hast soeben eine Anfrage erhalten')
                ->setFrom($from)
                ->setTo($provider->getEmail())
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);
            //</editor-fold>
            
            return new JsonResponse(array('status' => 'ok'));
        }
        
        return $this->render('booking/inquiry.html.twig', array(
            'loggedIn' => $loggedIn,
            'inquiry' => $inquiry,
            'form' => $form->createView(),
            'equipment' => $eq           
        ));
    }

    /**
     * @Route("booking/response/{id}", name="booking-response")
     */
    public function responseAction(Request $request, $id) {
        $inq = $this->getDoctrineRepo('AppBundle:EquipmentInquiry')->find($id);
        $eq = $inq->getEquipment();
        
        // security check
        if ($this->getUser()->getId() !== $eq->getUser()->getId()) {
            return new Response('', Response::HTTP_FORBIDDEN);
        }
        // sanity check
        if ($inq->getAccepted() !== null) { // already responded
            return new Response('', Response::HTTP_FORBIDDEN);
        }
        
        if ($request->getMethod() === "POST") {
            $acc = intval($request->request->get('accept'));
            $msg = $request->request->get('message');
            
            $inq->setAccepted($acc);
            $inq->setResponse($msg);
            if ($acc > 0) {
                $inq->setUuid(Utils::getUuid());
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            // send email
            //<editor-fold>
            $provider = $eq->getUser();
            if ($inq->getUser() !== null) {
                $email = $inq->getUser()->getEmail();
            }
            else {
                $email = $inq->getEmail();
            }
            $url = null;
            if ($acc > 0) {
                $url = $request->getSchemeAndHttpHost() .
                    $this->generateUrl('booking-confirmation', array('uuid' => $inq->getUuid()));
            }
            $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
            $emailHtml = $this->renderView('Emails\mail_to_user_confirm_offer_accepted.html.twig', array(
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                'provider' => $provider,
                'inquiry' => $inq,
                'equipment' => $eq,
                'url' => $url
            ));
            $message = Swift_Message::newInstance()
                ->setSubject('Du hast soeben eine Anfrage erhalten')
                ->setFrom($from)
                ->setTo($email)
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);
            //</editor-fold>
            
            return $this->redirectToRoute('dashboard');
        }
        
        return $this->render('booking/response.html.twig', array(
            'equipment' => $eq,
            'inquiry' => $inq
        ));
    }
    
    /**
     * @Route("/booking/confirmation/{uuid}", name="booking-confirmation")
     */
    public function confirmationAction(Request $request, $uuid) {
        $inq = $this->getDoctrineRepo('AppBundle:EquipmentInquiry')->findOneByUuid($uuid);

        // sanity check
        if ($inq == null) {
            throw $this->createNotFoundException();
        }
        if ($inq->getBooking() !== null) { // booking already confirmed
            return new Response('', 403); // TODO: display a nice message to the user?
        }
        
        $data = array('uuid' => $uuid);
        
        $form = $this->createFormBuilder($data
                , array(
                    'constraints' => array(
                        new Callback(array($this, 'validateDiscountCode'))
                    )
                )
                )
                ->add('agree', 'checkbox', array(
                    'required' => false,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('discountCode', 'text', array(
                    'required' => false
                ))
                ->add('uuid', 'hidden')
                ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {            
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            
            // create booking object            
            $bk = new EquipmentBooking();
            $bk->setInquiry($inq);
            $bk->setStatus(EquipmentBooking::STATUS_BOOKED);
            $bk->setPrice($inq->getPrice());
            $bk->setDeposit($inq->getDeposit());
            
            // validate discount
            $discountCode = null;
            if (!empty($data['discountCode'])) {
                $dcode = $this->getDoctrineRepo('AppBundle:DiscountCode')->findOneByCode($data['discountCode']);
                if ($dcode !== null) {
                    $user = $inq->getUser();
                    if ($dcode->getStatus() === DiscountCode::STATUS_ASSIGNED && $dcode->getUser()->getId() === $user->getId()) {
                        $discountCode = $dcode; // only here the discount is valid
                    }
                }
            }
            
            // calculate discount, total price
            if ($discountCode !== null) {                
                $discountCode->setStatus(DiscountCode::STATUS_USED);
                $bk->setDiscountCode($discountCode);
                $p = $bk->getPrice() - 5;
                $bk->setTotalPrice($p);
            }
            else {
                $bk->setTotalPrice($bk->getPrice());
            }
            
            // save booking
            $em->persist($bk);
            $em->flush();                        
            
            // send email to provider & user
            //<editor-fold>
            $provider = $inq->getEquipment()->getUser();
            $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
            
            $url = $request->getSchemeAndHttpHost() . $this->generateUrl('einstellungen');
            $emailHtml = $this->renderView('Emails\mail_to_provider_confirm_booking.html.twig', array(
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                'provider' => $provider,
                'inquiry' => $inq,
                'booking' => $bk,
                'equipment' => $inq->getEquipment(),
                'discountCode' => $discountCode,
                'url' => $url
            ));
            $message = Swift_Message::newInstance()
                ->setSubject('Du hast soeben eine Anfrage erhalten')
                ->setFrom($from)
                ->setTo($provider->getEmail())
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);

            if ($inq->getUser() !== null) {
                $email = $inq->getUser()->getEmail();
            }
            else {
                $email = $inq->getEmail();
            }
            $url = $request->getSchemeAndHttpHost() . $this->generateUrl('booking-list');
            $emailHtml = $this->renderView('Emails\mail_to_user_confirm_booking.html.twig', array(
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                'provider' => $provider,
                'booking' => $bk,
                'inquiry' => $inq,
                'discountCode' => $discountCode,
                'equipment' => $inq->getEquipment(),
                'url' => $url
            ));
            $message = Swift_Message::newInstance()
                ->setSubject('Du hast soeben eine Anfrage erhalten')
                ->setFrom($from)
                ->setTo($email)
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);
            //</editor-fold>
        
            return $this->redirectToRoute('rentme');
        }
        
        return $this->render('booking/confirmation.html.twig', array(
            'inquiry' => $inq,
            'form' => $form->createView()
        ));
    }

    public function validateDiscountCode($data, ExecutionContextInterface $context) {
        if (empty($data['discountCode'])) {
            return;
        }
        
        $dcode = $this->getDoctrineRepo('AppBundle:DiscountCode')->findOneByCode($data['discountCode']);
        if ($dcode === null || $dcode->getStatus() != DiscountCode::STATUS_ASSIGNED) {
            $context->buildViolation('This is not a valid discount code')->atPath('discountCode')->addViolation();
            return;
        }
        
        $inq = $this->getDoctrineRepo('AppBundle:EquipmentInquiry')->findOneByUuid($data['uuid']);
        $user = $inq->getUser();
        if ($user === null || $user->getId() !== $dcode->getUser()->getId()) {
            $context->buildViolation('This is not a valid discount code')->atPath('discountCode')->addViolation();
        }
    }
    /**
     * @Route("/booking/check-code/{uuid}/{code}", name="booking-check-code")
     */
    public function checkCodeAction(Request $request, $uuid, $code) {
        /*
         *  We're checking combinaion of uuid and code, 
         * so it's not impossible to brute-force-hack discount codes
         */
        $dcode = $this->getDoctrineRepo('AppBundle:DiscountCode')->findOneByCode($code);
        if ($dcode === null || $dcode->getStatus() !== DiscountCode::STATUS_ASSIGNED) {
            return new Response('', Response::HTTP_FORBIDDEN);
        }
        
        $inq = $this->getDoctrineRepo('AppBundle:EquipmentInquiry')->findOneByUuid($uuid);
        $user = $inq->getUser();
        
        // security
        if ($user === null || $user->getId() !== $dcode->getUser()->getId()) {
            return new Response('', Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(array('result' => 'ok'));
    }
 
    /** 
     * @Route("/booking/rate-user/{uuid}", name="rate-user")
     */
    public function rateUserAction(Request $request, $uuid) {
        $bk = $this->getDoctrineRepo('AppBundle:EquipmentBooking')->findOneByRateUserUuid($uuid);
        
        if ($bk === null) {
            return new Response($status = Response::HTTP_FORBIDDEN);
            // todo: check user identity
        }
        
        $inq = $bk->getInquiry();
        $bkUser = $inq->getUser();
        
        // build form
        //<editor-fold>
            $form = $this->createFormBuilder()
                ->add('rating', 'hidden', array(
                    'required' => true,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('opinion', 'textarea', array(
                    'attr' => array(
                        'max-length' => 300
                    ),
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 300))
                    )
                ))
                ->getForm();
        //</editor-fold>
            
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            
            $ur = new UserRating();
            $ur->setUser($bkUser);
            $ur->setBooking($bk);
            $ur->setOpinion($data['opinion']);
            $ur->setRating($data['rating']);
            
            $this->getDoctrineRepo('AppBundle:User')->addRating($ur);
            
            $bk->setRateUserUuid(null);
            $em->flush();
            
            return $this->redirectToRoute("start-page");
        }
        
        
        return $this->render('booking/rate-user.html.twig', array(
            'user' => $bkUser,
            'form' => $form->createView()
        ));
    }
 
    /** 
     * @Route("/booking/rate-equipment/{uuid}", name="rate-equipment")
     */
    public function rateEquipmentAction(Request $request, $uuid) {
        $bk = $this->getDoctrineRepo('AppBundle:EquipmentBooking')->findOneByRateEquipmentUuid($uuid);
        
        if ($bk === null) {
            return new Response($status = Response::HTTP_FORBIDDEN);
            // todo: check user identity
        }
        
        $inq = $bk->getInquiry();
        $eq = $inq->getEquipment();
        
        // build form
        //<editor-fold>
            $form = $this->createFormBuilder()
                ->add('rating', 'hidden', array(
                    'required' => true,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('opinion', 'textarea', array(
                    'attr' => array(
                        'max-length' => 300
                        
                    ),
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 300))
                    )
                ))
                ->getForm();
        //</editor-fold>
            
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            
            $er = new EquipmentRating();
            $er->setEquipment($eq);
            $er->setBooking($bk);
            $er->setOpinion($data['opinion']);
            $er->setRating($data['rating']);
            
            $this->getDoctrineRepo('AppBundle:Equipment')->addRating($er);
            
            $bk->setRateEquipmentUuid(null);
            $em->flush();
            
            return $this->redirectToRoute("start-page");
        }
        
        
        return $this->render('booking/rate-equipment.html.twig', array(
            'equipment' => $eq,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/booking/list", name="booking-list")
     */
    public function listAction(Request $request) {
        $user = $this->getUser();
        $bookings = $this->getDoctrineRepo('AppBundle:EquipmentBooking')->getAllForUser($user->getId());
        $pBookings = $this->getDoctrineRepo('AppBundle:EquipmentBooking')->getAllForProvider($user->getId());
        $tBookings = $this->getDoctrineRepo('AppBundle:TalentBooking')->getAllForUser($user->getId());
        $ptBookings = $this->getDoctrineRepo('AppBundle:TalentBooking')->getAllForProvider($user->getId());
        
        $bookings = array_merge($bookings, $tBookings);
        $pBookings = array_merge($pBookings, $ptBookings);
        return $this->render("booking/booking-list.html.twig", array(
            'bookings' => $bookings,
            'pBookings' => $pBookings,
            'tBookings' => $tBookings,
            'ptBookings' => $ptBookings
        ));
    }
    
    /**
     * @Route("/booking/cancel/{id}", name="booking-cancel")
     */
    public function cancelAction(Request $request, $id) {
        $user = $this->getUser();
        $bk = $this->getDoctrineRepo('AppBundle:EquipmentBooking')->find($id);
        
        if ($bk->getInquiry()->getEquipment()->getUser()->getId() == $user->getId()) {
            return $this->redirectToRoute('booking-cancel-provider', array('id' => $id));
        }
        else {
            return $this->redirectToRoute('booking-cancel-user', array('id' => $id));
        }
    }
    
    /**
     * @Route("/booking/cancel/user/{id}", name="booking-cancel-user")
     */
    public function userCancelAction(Request $request, $id) {
        $bk = $this->getDoctrineRepo('AppBundle:EquipmentBooking')->find($id);
        $user = $this->getUser();
        
        // check security        
        if ($user->getId() !== $bk->getInquiry()->getUser()->getId()) {
            return new Response(Response::HTTP_FORBIDDEN);
        }
        
        $form = $this->createFormBuilder()
            ->add('reason', 'hidden', array(
                'required' => true,
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('description', 'text', array(
                'required' => false
            ))
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            
            $bc = new EquipmentBookingCancel();
            $bc->setBooking($bk);
            $bc->setUser($user);
            $bc->setProvider(0);
            $bc->setReason($data['reason']);
            $bc->setDescription($data['description']);
            
            $em->persist($bc);
            $bk->setStatus(EquipmentBooking::STATUS_USER_CANCELLED);
            
            $em->flush();
            
            // send email
            //<editor-fold>
            $inq = $bk->getInquiry();
            $eq = $inq->getEquipment();
            $provider = $eq->getUser();
            // to user
            if ($inq->getUser() !== null) {
                $email = $inq->getUser()->getEmail();
            }
            else {
                $email = $inq->getEmail();
            }
            $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
            $emailHtml = $this->renderView('Emails\mail_to_user_confirm_cancel.html.twig', array(
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                'provider' => $provider,
                'inquiry' => $inq
            ));
            $message = Swift_Message::newInstance()
                ->setSubject('Du hast soeben eine Anfrage erhalten')
                ->setFrom($from)
                ->setTo($email)
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);
            // to provider
            $email = $provider->getEmail();
            $emailHtml = $this->renderView('Emails\mail_to_provider_cancel.html.twig', array(
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                'provider' => $provider,
                'inquiry' => $inq
            ));
            $message = Swift_Message::newInstance()
                ->setSubject('Du hast soeben eine Anfrage erhalten')
                ->setFrom($from)
                ->setTo($email)
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);

            //</editor-fold>            
            return $this->redirectToRoute("booking-list");
        }
        
        return $this->render("booking/booking-user-cancel.html.twig", array(
            'booking' => $bk,
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/booking/cancel/provider/{id}", name="booking-cancel-provider")
     */
    public function providerCancelAction(Request $request, $id) {
        $bk = $this->getDoctrineRepo('AppBundle:EquipmentBooking')->find($id);
        $user = $this->getUser();
        
        // todo: check security
        if ($user->getId() !== $bk->getInquiry()->getEquipment()->getUser()->getId()) {
            return new Response(Response::HTTP_FORBIDDEN);
        }
        
        $form = $this->createFormBuilder()
            ->add('reason', 'hidden', array(
                'required' => true,
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('description', 'text', array(
                'required' => false
            ))
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            
            $bc = new EquipmentBookingCancel();
            $bc->setBooking($bk);
            $bc->setUser($user);
            $bc->setProvider(1);
            $bc->setReason($data['reason']);
            $bc->setDescription($data['description']);
            
            $em->persist($bc);
            $bk->setStatus(EquipmentBooking::STATUS_PROVIDER_CANCELLED);
            
            $em->flush();
            
            // send email
            //<editor-fold>
            $inq = $bk->getInquiry();
            $eq = $inq->getEquipment();
            $provider = $eq->getUser();
            // to user
            if ($inq->getUser() !== null) {
                $email = $inq->getUser()->getEmail();
            }
            else {
                $email = $inq->getEmail();
            }
            $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
            $emailHtml = $this->renderView('Emails\mail_to_user_cancel.html.twig', array(
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                'provider' => $provider,
                'inquiry' => $inq
            ));
            $message = Swift_Message::newInstance()
                ->setSubject('Du hast soeben eine Anfrage erhalten')
                ->setFrom($from)
                ->setTo($email)
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);
            // to provider
            $email = $provider->getEmail();
            $emailHtml = $this->renderView('Emails\mail_to_provider_confirm_cancel.html.twig', array(
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                'provider' => $provider
            ));
            $message = Swift_Message::newInstance()
                ->setSubject('Du hast soeben eine Anfrage erhalten')
                ->setFrom($from)
                ->setTo($email)
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);

            //</editor-fold>
            
            
            return $this->redirectToRoute("booking-list");
        }
        
        return $this->render("booking/booking-provider-cancel.html.twig", array(
            'booking' => $bk,
            'form' => $form->createView()
        ));
    }
    
}
