<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscriber;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewsletterController extends BaseController {
    
    public function formAction(Request $request) {
        if ($request->cookies->has('nl-close'))
            return new Response('');
        
        
        return $this->render('newsletter/form.html.twig');
    }
    
    /**
     * @Route("newsletter/submit", name="newsletter-submit")
     */
    public function submitAction(Request $request) {
        
        $email = $request->request->get('email');
        
        // validate email
        $validator = $this->get("validator");
        $errorList = $validator->validate($email, array(
                    new NotBlank(),
                    new Email(array('checkHost' => true)),
                    new Length(array('max' => 50))
                ));
        
        
        if (count($errorList) > 0) {
            $errs = array();
            foreach($errorList as $v) 
                array_push ($errs, $v->getMessage());
            $message = implode(';', $errs);
            return new JsonResponse(array('valid' => false, 'message' => $message));
        }
        
        // check database
        $em = $this->getDoctrine()->getManager();
        $sub = $this->getDoctrineRepo('AppBundle:Subscriber')->findOneByEmail($email);
        
        if ($sub !== null && $sub->getConfirmed() === 1) { // exists and already confirmed
            $message = "Sie haben sich erfolgreich für den hey! VIENNA Newsletter angemeldet";
            return new JsonResponse(array('valid' => false, 'message' => $message));
        }
        
        if ($sub !== null) { // exists, but not confirmed, send a new ticket
            $sub->setToken(Utils::getUuid() . "-" . Utils::getUuid());
        }
        else { // create and send a new ticket
            $sub = new Subscriber();
            $sub->setEmail($email);
            $sub->setConfirmed(0);
            $sub->setToken(Utils::getUuid() . "-" . Utils::getUuid());
            
            $em->persist($sub);
        }
        $em->flush();
                    
        // send email
        $url = $request->getSchemeAndHttpHost() . $this->generateUrl('newsletter-confirm', array('token' => $sub->getToken()));
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $emailHtml = $this->renderView('Emails/newsletter/confirm.html.twig', array(
            'url' => $url
        ));
        $sm = Swift_Message::newInstance()
            ->setSubject('Bitte bestätige Deine Anmeldung für den hey! VIENNA Newsletter')
            ->setFrom($from)
            ->setTo($sub->getEmail())
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($sm);
        
        $message = "Wir haben Ihnen eine Nachricht gesendet. Bitte bestätigen Sie darin Ihre E-Mail Adresse, um mit Ihrer Anmeldung für den Newsletter fortzufahren";        
        $resp = new JsonResponse(array('valid' => true, 'message' => $message));

        // set cookie to hide newsletter bar
        $this->addNewsletterCookie($resp);        
        return $resp;
    }
    
    /**
     * @Route("newsletter/close", name="newsletter-close")
     */
    public function closeAction() {
        $resp = new Response();
        $this->addNewsletterCookie($resp);
        return $resp;
    }

    /**
     * @Route("newsletter/confirm/{token}", name="newsletter-confirm")
     */
    public function confirmAction(Request $request, $token) {
        $sub = $this->getDoctrineRepo('AppBundle:Subscriber')->findOneByToken($token);
        
        if ($sub === null)
            $message = "Kann nicht Newsletter-Abonnement Anfrage mit dieser E-Mail-Adresse finden";
        else if ($sub->getConfirmed() === 1)
            $message = "Sie haben sich erfolgreich für den hey! VIENNA Newsletter angemeldet";
        else {
            $em = $this->getDoctrine()->getManager();
            $sub->setConfirmed(1);
            $em->flush();

            $dcode = $this->getDoctrineRepo('AppBundle:DiscountCode')->assignToSubscriber($sub, 10); // todo: unhardcode value
            
            $message = "Du hast Dich hiermit erfolgreich für den hey! VIENNA Newsletter angemeldet.";
            
            // send email
            $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
            $emailHtml = $this->renderView('Emails/newsletter/subscribed.html.twig', array(
                'dcode' => $dcode
            ));
            $sm = Swift_Message::newInstance()
                ->setSubject('Deine Anmeldung für den hey! VIENNA Newsletter')
                ->setFrom($from)
                ->setTo($sub->getEmail())
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($sm);            
        }
                    
        $session = $request->getSession();
        $session->set('NewsletterMessage', $message);
                
        
        $resp = $this->redirectToRoute('start-page');
        $this->addNewsletterCookie($resp);
        return $resp;
    }
    
    private function addNewsletterCookie($response) {
        $t = time() + (3600 * 24 * 28);
        $cookie = new Cookie('nl-close', '1', $t, $this->generateUrl('start-page'));
        $response->headers->setCookie($cookie);
    }
}
