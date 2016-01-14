<?php
namespace AppBundle\Mailer;

use AppBundle\Entity\User;
use Swift_Message;
use Symfony\Component\Templating\EngineInterface;

class GeneralMailer {
    protected $mailer;
    protected $templating;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, $doctrine, array $parameters) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->parameters = $parameters;
        $this->doctrine = $doctrine;
    }
    
    public function SendWelcomeEmail(User $user, $withDiscountCode)
    {
        $code = null;
        if ($withDiscountCode) {
            $codeRepo = $this->doctrine->getRepository('AppBundle:DiscountCode');
            $code = $codeRepo->assignToUser($user);
        }
        
        $from = $this->parameters['mailer_fromEmail'];        
        #$username = 'seba';
        $username = $user->getName(). " ". $user->getSurname();
        $to = $user->getEmail();
        #$to = 'sebastian0680@wp.pl'; 
        $message = \Swift_Message::newInstance()
        ->setSubject('Heyrentme Welcome Email.')
        ->setFrom($from)
        ->setTo($to)
        ->setBody(
            $this->templating->render(
                // app/Resources/views/Emails/registration.html.twig
                'Emails/registration_welcome.html.twig',
                array(
                    'name' => $username, 
                    'mailer_image_url_prefix' => $this->parameters['mailer_image_url_prefix'],
                    'discountCode' => $code
                )
            ),
            'text/html'
        );
        $this->mailer->send($message);
    }

}
