<?php
namespace AppBundle\Mailer;

use AppBundle\Entity\User;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Templating\EngineInterface;

class GeneralMailer {
    
    protected $mailer;
    protected $templating;
    protected $router;

    public function __construct(Swift_Mailer $mailer, EngineInterface $templating, Router $router, $doctrine, array $parameters) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->router = $router;
        $this->parameters = $parameters;
        $this->doctrine = $doctrine;
    }
    
    public function SendWelcomeEmail(User $user, $discountCode) {        
        $from = array($this->parameters['mailer_fromEmail'] => $this->parameters['mailer_fromName']);
        #$username = 'seba';
        $username = $user->getName(). " ". $user->getSurname();
        $to = $user->getEmail();
        #$to = 'sebastian0680@wp.pl'; 
        $message = Swift_Message::newInstance()
        ->setSubject('Willkommen bei hey! VIENNA')
        ->setFrom($from)
        ->setTo($to)
        ->setBody(
            $this->templating->render(
                // app/Resources/views/Emails/registration.html.twig
                'Emails/registration_welcome.html.twig',
                array(
                    'name' => $username, 
                    'mailer_app_url_prefix' => $this->parameters['mailer_app_url_prefix'],
                    'discountCode' => $discountCode
                )
            ),
            'text/html'
        );
        $this->mailer->send($message);
    }

    public function AdmItemInquiryCC($inq) {
        
        $eq = get_class($inq) === 'AppBundle\\Entity\\EquipmentInquiry';
        if ($eq)
            $item = $inq->getEquipment();
        else
            $item = $inq->getTalent();            
        $provider = $item->getUser();
        
        $from = array($this->parameters['mailer_fromEmail'] => $this->parameters['mailer_fromName']);
        $to = $item->getInquiryEmail();
        $subject = "Du hast soeben eine Anfrage erhalten!";        
        $copy = $this->templating->render('Emails/admin/item_inquiry_notification.html.twig', array(
            'inquiry' => $inq,
            'eq' => $eq,
            'item' => $item,
            'provider' => $provider
        ));        
        
        $msg = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($from)
                ->setTo($to)
                ->setBody($copy, 'text/html');        
        $this->mailer->send($msg);
    }
}
