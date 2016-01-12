<?php
          
namespace AppBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use AppBundle\Entity\Equipment;
      
class HeyrentmeMailer implements MailerInterface {
   
        
    public function __construct($fromEmail, UrlGeneratorInterface  $router,EngineInterface $templating, $mailer, array $parameters)
    {
        $this->router = $router;
        $this->fromEmail = $fromEmail;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->parameters = $parameters;
    }
    
    /**
     * {@inheritdoc}
     */
    public function sendNewModifiedEquipmentInfoMessage(Equipment $eq)
    {
        $from = $this->fromEmail;                
        $to = $this->parameters['admin_email'];
                        
        $template = 'Emails/Equipment/new_modified_item.html.twig';
        $url = $this->router->generate('admin_equipment_moderate', array('id' => $eq->getId()));        
        
        $rendered = $this->templating->render($template, array(                        
            'equipment_url' =>  $url,
            'equpment' => $eq,            
            'mailer_image_url_prefix' => $this->parameters['mailer_image_url_prefix']
        ));
        $this->sendEmailMessage($rendered, $from, $to, "New/modified equipment.");
        
    }
    /**
     * {@inheritdoc}
     */
    public function sendApprovedRejectedInfoMessage(Equipment $eq, $reason)
    {
        $from = $this->fromEmail;                
        $to = $eq->getUser()->getEmail();
                        
        $template = 'Emails/Equipment/equipment_approved_rejected.html.twig';        
        
        $rendered = $this->templating->render($template, array(                                    
            'equpment' => $eq,
            'mailer_image_url_prefix' => $this->parameters['mailer_image_url_prefix'],
            'reason' => $reason
        ));
        $this->sendEmailMessage($rendered, $from, $to, "New/modified equipment.");
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $from = $this->fromEmail;        
        
        //$username = $user->getName(). " ". $user->getSurname();
        $to = $user->getEmail();
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        //$url .= "?emailConfirmation";
        $template = 'Emails/registration_confirm.html.twig';
        
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'confirmationUrl' =>  $url,
            'mailer_image_url_prefix' => $this->parameters['mailer_image_url_prefix']
        ));
        $this->sendEmailMessage($rendered, $from, $to, "Heyrentme confirmation email.");
        
    }

    /**
     * {@inheritdoc}
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $from = $this->fromEmail;    
        $template = 'Emails/password_reseting_email.html.twig';
        $url = $this->router->generate('rentme', array(), true) . "/" . $user->getConfirmationToken();
                
        //$url = path('rentme') . "?passwordResetButton&token=" + $user->getConfirmationToken();
        $url .= "?passwordReset";
        $to = $user->getEmail();
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'resetUrl' => $url,
            'mailer_image_url_prefix' => $this->parameters['mailer_image_url_prefix']
        ));
        
        $this->sendEmailMessage($rendered, $from, $to, "Heyrentme password reset.");
    }

    /**
     * @param string $renderedTemplate
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendEmailMessage($rendered, $from, $to, $subject)
    {
       $message = \Swift_Message::newInstance()
        ->setSubject($subject)
        ->setFrom($from)
        ->setTo($to)
        ->setBody(
            $rendered, 'text/html'
        )/*
         * If you also want to include a plaintext version of the message
        ->addPart(
            $this->renderView(
                'Emails/registration.txt.twig',
                array('name' => $name)
            ),
            'text/plain'
        )
        */;
        $this->mailer->send($message);
    }
}
