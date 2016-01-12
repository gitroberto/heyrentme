<?php

namespace AppBundle\Service;

use AppBundle\Utils\Utils;
use DateTime;
use Doctrine\ORM\EntityManager;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\Exception;


class SchedulerService {
    
    protected $em;
    protected $mailer;
    protected $templating;
    protected $parameters;
    protected $logger;
    protected $router;
    
    protected $from;
    protected $imageUrlPrefix;
    
    public function __construct(EntityManager $em, Swift_Mailer $mailer, TwigEngine $templating, Logger $logger, UrlGeneratorInterface $router, array $parameters) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->router = $router;
        $this->parameters = $parameters;
        $this->logger = $logger;
    }
    
    public function run() {
        // common params
        $this->from = array($this->parameters['mailer_fromemail'] => $this->parameters['mailer_fromname']);
        $this->imageUrlPrefix = $this->parameters['mailer_image_url_prefix'];

        $now = new DateTime();
        $this->sendRentReminders($now);
        $this->sendAllOkReminders($now);
        $this->sendReturnReminders($now);
        $this->sendRateReminders($now);
    }
    
    protected function sendRentReminders(DateTime $datetime) {  
        // users
        $this->logger->debug('sending RENT reminders for USERS');
        $bookings = $this->em->getRepository('AppBundle:Booking')->getAllForRentUserReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                $eq = $inq->getEquipment();
                $provider = $eq->getUser();
                $discountCode = $bk->getDiscountCode();

                if ($inq->getUser() !== null) {
                    $email = $inq->getUser()->getEmail();
                }
                else {
                    $email = $inq->getEmail();
                }
                $emailHtml = $this->templating->render('Emails\mail_to_user_reminder_start_booking.html.twig', array(
                    'mailer_image_url_prefix' => $this->imageUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'discountCode' => $discountCode,
                    'equipment' => $eq
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeRentUserAt(new DateTime());
                $this->em->flush();
                
                $msg = sprintf("\t%s, from-date: %s", $email, $inq->getFromAt()->format("Y-m-d H:i:s"));
                $this->logger->debug($msg);
            } catch (Exception $e) {
                $msg = sprintf("\t%s, FAILED", $email);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }
        }
        
        // providers
        $this->logger->debug('sending RENT reminders for PROVIDERS');
        $bookings = $this->em->getRepository('AppBundle:Booking')->getAllForRentProviderReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                $eq = $inq->getEquipment();
                $provider = $eq->getUser();
                $discountCode = $bk->getDiscountCode();

                $email = $provider->getEmail();
                $emailHtml = $this->templating->render('Emails\mail_to_provider_reminder_start_booking.html.twig', array(
                    'mailer_image_url_prefix' => $this->imageUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'discountCode' => $discountCode,
                    'equipment' => $eq
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeRentProviderAt(new DateTime());
                $this->em->flush();
                            
                $msg = sprintf("\t%s, from-date: %s", $email, $inq->getFromAt()->format("Y-m-d H:i:s"));
                $this->logger->debug($msg);
            } catch (Exception $e) {
                $msg = sprintf("\t%s, FAILED", $email);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }
            
        }
    }
    protected function sendAllOkReminders(DateTime $datetime) {        
        // users
        $this->logger->debug('sending ALL OK reminders for USERS');
        $bookings = $this->em->getRepository('AppBundle:Booking')->getAllForAllOkUserReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();

                if ($inq->getUser() !== null) {
                    $email = $inq->getUser()->getEmail();
                }
                else {
                    $email = $inq->getEmail();
                }
                $emailHtml = $this->templating->render('Emails\mail_to_user_everything_ok.html.twig', array(
                    'mailer_image_url_prefix' => $this->imageUrlPrefix,
                    'inquiry' => $inq
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeAllOkUserAt(new DateTime());
                $this->em->flush();
                
                $msg = sprintf("\t%s, from-date: %s", $email, $inq->getFromAt()->format("Y-m-d H:i:s"));
                $this->logger->debug($msg);
            } catch (Exception $e) {
                $msg = sprintf("\t%s, FAILED", $email);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }
        }
        
        // providers
        $this->logger->debug('sending ALL OK reminders for PROVIDERS');
        $bookings = $this->em->getRepository('AppBundle:Booking')->getAllForAllOkProviderReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                $eq = $inq->getEquipment();
                $provider = $eq->getUser();

                $email = $provider->getEmail();
                $emailHtml = $this->templating->render('Emails\mail_to_provider_everything_ok.html.twig', array(
                    'mailer_image_url_prefix' => $this->imageUrlPrefix,
                    'provider' => $provider
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeAllOkProviderAt(new DateTime());
                $this->em->flush();
                
                $msg = sprintf("\t%s, from-date: %s", $email, $inq->getFromAt()->format("Y-m-d H:i:s"));
                $this->logger->debug($msg);
            } catch (Exception $e) {
                $msg = sprintf("\t%s, FAILED", $email);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }
        }
    }
    protected function sendReturnReminders(DateTime $datetime) {        
        // users
        $this->logger->debug('sending RETURN reminders for USERS');
        $bookings = $this->em->getRepository('AppBundle:Booking')->getAllForReturnUserReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                $eq = $inq->getEquipment();
                $provider = $eq->getUser();
                $discountCode = $bk->getDiscountCode();

                if ($inq->getUser() !== null) {
                    $email = $inq->getUser()->getEmail();
                }
                else {
                    $email = $inq->getEmail();
                }
                $emailHtml = $this->templating->render('Emails\mail_to_user_reminder_return_offer.html.twig', array(
                    'mailer_image_url_prefix' => $this->imageUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'discountCode' => $discountCode,
                    'equipment' => $eq
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeReturnUserAt(new DateTime());
                $this->em->flush();
                
                $msg = sprintf("\t%s, from-date: %s", $email, $inq->getFromAt()->format("Y-m-d H:i:s"));
                $this->logger->debug($msg);
            } catch (Exception $e) {
                $msg = sprintf("\t%s, FAILED", $email);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }
        }
        
        // providers
        $this->logger->debug('sending RETURN reminders for PROVIDERS');
        $bookings = $this->em->getRepository('AppBundle:Booking')->getAllForReturnProviderReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                $eq = $inq->getEquipment();
                $provider = $eq->getUser();
                $discountCode = $bk->getDiscountCode();

                $email = $provider->getEmail();
                $emailHtml = $this->templating->render('Emails\mail_to_provider_reminder_return_offer.html.twig', array(
                    'mailer_image_url_prefix' => $this->imageUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'discountCode' => $discountCode,
                    'equipment' => $eq
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeReturnProviderAt(new DateTime());
                $this->em->flush();
                
                $msg = sprintf("\t%s, from-date: %s", $email, $inq->getFromAt()->format("Y-m-d H:i:s"));
                $this->logger->debug($msg);
            } catch (Exception $e) {
                $msg = sprintf("\t%s, FAILED", $email);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }
        }
    }
    protected function sendRateReminders(DateTime $datetime) {        
        // users
        $this->logger->debug('sending RATE reminders for USERS');
        $bookings = $this->em->getRepository('AppBundle:Booking')->getAllForRateUserReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                $eq = $inq->getEquipment();
                $provider = $eq->getUser();
                $uuid = Utils::getUuid();

                if ($inq->getUser() !== null) {
                    $email = $inq->getUser()->getEmail();
                }
                else {
                    $email = $inq->getEmail();
                }
                // TODO: build url with uuid
                $emailHtml = $this->templating->render('Emails\mail_to_user_rate_provider.html.twig', array(
                    'mailer_image_url_prefix' => $this->imageUrlPrefix,
                    'inquiry' => $inq,
                    'provider' => $provider,
                    'equipment' => $eq
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeRateUserAt(new DateTime());
                $bk->setRateProviderUuid($uuid);
                $this->em->flush();
                
                $msg = sprintf("\t%s, from-date: %s", $email, $inq->getFromAt()->format("Y-m-d H:i:s"));
                $this->logger->debug($msg);
            } catch (Exception $e) {
                $msg = sprintf("\t%s, FAILED", $email);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }
        }
        
        // providers
        $this->logger->debug('sending RATE reminders for PROVIDERS');
        $bookings = $this->em->getRepository('AppBundle:Booking')->getAllForRateProviderReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                $eq = $inq->getEquipment();
                $provider = $eq->getUser();
                $uuid = Utils::getUuid();

                // TODO: create url with uuid
                $email = $provider->getEmail();
                $emailHtml = $this->templating->render('Emails\mail_to_provider_rate_user.html.twig', array(
                    'mailer_image_url_prefix' => $this->imageUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeRateProviderAt(new DateTime());
                $bk->setRateUserUuid($uuid);
                $this->em->flush();
                
                $msg = sprintf("\t%s, from-date: %s", $email, $inq->getFromAt()->format("Y-m-d H:i:s"));
                $this->logger->debug($msg);
            } catch (Exception $e) {
                $msg = sprintf("\t%s, FAILED", $email);
                $this->logger->error($msg);
                $this->logger->error($e->getTraceAsString());
            }
        }
    }
    
    public function test() {
        $s = 'service and command test ' . (new DateTime())->format('r');
        $usr = $this->em->getRepository('AppBundle:User')->findOneBy(array('email' => 'arturm2@o2.pl'));
        $msg = Swift_Message::newInstance()
            ->setSubject($s)
            ->setFrom('heyrentme@o2.pl')
            ->setTo($usr->getEmail())
            ->setBody($s, 'text/plain');
        $this->mailer->send($msg);
    }
}
