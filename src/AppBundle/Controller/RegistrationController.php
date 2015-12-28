<?php

namespace AppBundle\Controller;


use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class RegistrationController extends BaseRegistrationController
{
    public function registerAction(Request $request)
    {
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
            
            
            //$this->SendConfirmationEmail($user);

            //$this->SendWelcomeEmail($user);
            
            
            //return $response;
            return $this->userIsRegisteredAction();
        }
        
        return $this->render('FOSUserBundle:Registration:register.html.twig', array(
            'form' => $form->createView(),));
    }
    
   public function confirmAction(Request $request, $token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);
        
        $codeRepo = $this->getDoctrine()->getRepository('AppBundle:DiscountCode');
        $code = $codeRepo->assignToUser($user);
        $this->getDoctrine()->getRepository('AppBundle:Inquiry')->updateInquiries($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('rentme') . "?confirmed=1";
            $response = new RedirectResponse($url);
        }

        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        $this->SendWelcomeEmail($user, $code);
        
        return $response;
    }
    
    public function SendWelcomeEmail($user, $code)
    {
        $from = $this->getParameter('mailer_fromEmail');        
        #$username = 'seba';
        $username = $user->getName(). " ". $user->getSurname();
        $to = $user->getEmail();
        #$to = 'sebastian0680@wp.pl'; 
        $message = \Swift_Message::newInstance()
        ->setSubject('Heyrentme Welcome Email.')
        ->setFrom($from)
        ->setTo($to)
        ->setBody(
            $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                'Emails/registration_welcome.html.twig',
                array(
                    'name' => $username, 
                    'mailer_image_url_prefix' => $this->getParameter('mailer_image_url_prefix'),
                    'discountCode' => $code
                )
            ),
            'text/html'
        )
        /*
         * If you also want to include a plaintext version of the message
        ->addPart(
            $this->renderView(
                'Emails/registration.txt.twig',
                array('name' => $name)
            ),
            'text/plain'
        )
        */
        ;
        $this->get('mailer')->send($message);
    }
    
    
    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('FOSUserBundle:Registration:emailconfirmed.html.twig', array(
            'user' => $user,
            'targetUrl' => "",
            'confirmed' => 1
        ));
    }

    public function userIsRegisteredAction(){        
        $response = new Response(json_encode("User_Is_Registered"));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}