<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\ReportOffer;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommonController extends BaseController {
    
    public function commonsAction(Request $request) {
        return $this->render('common/commons.html.twig', array(
            'categories' => $this->getCategories($request)
        ));
    }

    public function categoryListAction(Request $request, $type, $mobile = false) {
        $eq = $type === Category::TYPE_EQUIPMENT;
        $cats1 = $this->getCategoriesByType($request, $eq ? 1 : 2);
        $cats2 = $this->getCategoriesByType($request, $eq ? 2 : 1);
        
        return $this->render(
            $mobile ? 'common/categoryListMob.html.twig' : 'common/categoryList.html.twig',
            array (
                'cats1' => $cats1,
                'cats2' => $cats2,
                'equipment' => $eq
            )
        );
    }
    
    
     /**
     * 
     * @Route("/feedback", name="feedback")
     */
    public function feedbackAction(Request $request) {
        $feedback = new Feedback();
        
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
            $feedback->setName(sprintf("%s %s", $user->getName(), $user->getSurname()));
            $feedback->setEmail($user->getEmail());
        }
        
        $form = $this->createFormBuilder($feedback)
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 100))
                    )
                ))
                ->add('email', 'email', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 100))
                    )
                ))
                ->add('subject', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 100))
                    )
                ))
                ->add('message', 'textarea', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 1000))
                    )
                ))
                ->getForm();
        //when the form is posted this method prefills entity with data from form
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();            
            // save to db
            
            $em->persist($feedback);
            $em->flush();

            
            return $this->userFeedbackSavedAction();
        }
        
        
        return $this->render('common/feedback.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    public function userFeedbackSavedAction(){        
        $response = new Response(json_encode("User_Feedback_Saved"));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * 
     * @Route("equipment/report/{type}/{id}", name="report")
     */
    public function reportOfferAction(Request $request, $type, $id) {
        $reportOffer = new ReportOffer();
        
        $form = $this->createFormBuilder($reportOffer)
                ->add('report', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 100))
                    )
                ))
                ->add('message', 'textarea', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 500))
                    )
                ))->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $reportOffer->setOfferType($type);
            
            if ($type == ReportOffer::OFFER_TYPE_EQUIPMENT){
                $equipment = $this->getDoctrineRepo("AppBundle:Equipment")->find($id);
                if (!$equipment) {
                    throw $this->createNotFoundException('No equipment found for id '.$id);
                }
                $reportOffer->setEquipment($equipment);
            } else {
                $talent = $this->getDoctrineRepo("AppBundle:Talent")->find($id);
                if (!$talent) {
                    throw $this->createNotFoundException('No talent found for id '.$id);
                }
                $reportOffer->setTalent($talent);
            }
            
                    
            $em->persist($reportOffer);
            $em->flush();
            
            return $this->ReportOfferSavedAction();      
        }
        
        return $this->render('default/report_offer.html.twig', array(
            'form' => $form->createView(),
            'type' => $type,
            'id' => $id
        ));
    }
    
    public function ReportOfferSavedAction(){        
        $response = new Response(json_encode("Report_Offer_Saved"));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function registerAction(Request $request) {     
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

       
        $user = $userManager->createUser();
        $user->setEnabled(true);
        
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();               
        
        $form->setData($user);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_registration_confirmed');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('FOSUserBundle:Registration:register.html.twig', array(
            'form' => $form->createView(),
        ));
               
    }
}
