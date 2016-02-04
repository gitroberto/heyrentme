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
    protected $appUrlPrefix;
    
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
        $this->appUrlPrefix = $this->parameters['mailer_app_url_prefix'];

        $now = new DateTime();
        $this->logger->debug("now: " . $now->format('Y-m-d H:i:s'));
        $this->sendRentReminders($now);
        $this->sendAllOkReminders($now);
        $this->sendReturnReminders($now);
        $this->sendRateReminders($now);
    }
    
    protected function sendRentReminders(DateTime $datetime) {  
        // users
        $this->logger->debug('sending RENT reminders for USERS');
        $es = $this->em->getRepository('AppBundle:EquipmentBooking')->getAllForRentUserReminder($datetime);        
        $ts = $this->em->getRepository('AppBundle:TalentBooking')->getAllForRentUserReminder($datetime);        
        $bookings = array_merge($es, $ts);
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                if (get_class($bk) === 'AppBundle\\Entity\\EquipmentBooking') {
                    $tmpl = 'Emails\mail_to_user_reminder_start_booking.html.twig';
                    $eq = $inq->getEquipment();
                }
                else {
                    $tmpl = 'Emails\talent\mail_to_user_reminder_start_booking.html.twig';
                    $eq = $inq->getTalent();
                }
                $provider = $eq->getUser();
                $discountCode = $bk->getDiscountCode();

                if ($inq->getUser() !== null) {
                    $email = $inq->getUser()->getEmail();
                }
                else {
                    $email = $inq->getEmail();
                }
                $emailHtml = $this->templating->render($tmpl, array(
                    'mailer_app_url_prefix' => $this->appUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'booking' => $bk,
                    'discountCode' => $discountCode,
                    'item' => $eq
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
        $es = $this->em->getRepository('AppBundle:EquipmentBooking')->getAllForRentProviderReminder($datetime);        
        $ts = $this->em->getRepository('AppBundle:TalentBooking')->getAllForRentProviderReminder($datetime);        
        $bookings = array_merge($es, $ts);
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                if (get_class($bk) === 'AppBundle\\Entity\\EquipmentBooking') {
                    $eq = $inq->getEquipment();
                    $tmpl = 'Emails\mail_to_provider_reminder_start_booking.html.twig';
                }
                else {
                    $eq = $inq->getTalent();
                    $tmpl = 'Emails\talent\mail_to_provider_reminder_start_booking.html.twig';
                }
                $provider = $eq->getUser();
                $discountCode = $bk->getDiscountCode();
                $email = $provider->getEmail();
                $emailHtml = $this->templating->render($tmpl, array(
                    'mailer_app_url_prefix' => $this->appUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'booking' => $bk,
                    'discountCode' => $discountCode,
                    'item' => $eq
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
        $es = $this->em->getRepository('AppBundle:EquipmentBooking')->getAllForAllOkUserReminder($datetime);        
        $ts = $this->em->getRepository('AppBundle:TalentBooking')->getAllForAllOkUserReminder($datetime);        
        $bookings = array_merge($es, $ts);
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();

                if ($inq->getUser() !== null) {
                    $email = $inq->getUser()->getEmail();
                }
                else {
                    $email = $inq->getEmail();
                }
                if (get_class($bk) === 'AppBundle\\Entity\\EquipmentBooking') {
                    $tmpl = 'Emails\mail_to_user_everything_ok.html.twig';
                }
                else {
                    $tmpl = 'Emails\talent\mail_to_user_everything_ok.html.twig';
                }
                $emailHtml = $this->templating->render($tmpl, array(
                    'mailer_app_url_prefix' => $this->appUrlPrefix,
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
        $es = $this->em->getRepository('AppBundle:EquipmentBooking')->getAllForAllOkProviderReminder($datetime);        
        $ts = $this->em->getRepository('AppBundle:TalentBooking')->getAllForAllOkProviderReminder($datetime);        
        $bookings = array_merge($es, $ts);
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                if (get_class($bk) === 'AppBundle\\Entity\\EquipmentBooking') {
                    $eq = $inq->getEquipment();
                    $tmpl = 'Emails\mail_to_provider_everything_ok.html.twig';
                }
                else {
                    $eq = $inq->getTalent();
                    $tmpl = 'Emails\talent\mail_to_provider_everything_ok.html.twig';
                }
                $provider = $eq->getUser();

                $email = $provider->getEmail();
                $emailHtml = $this->templating->render($tmpl, array(
                    'mailer_app_url_prefix' => $this->appUrlPrefix,
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
        $bookings = $this->em->getRepository('AppBundle:EquipmentBooking')->getAllForReturnUserReminder($datetime);        
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
                    'mailer_app_url_prefix' => $this->appUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'discountCode' => $discountCode,
                    'item' => $eq
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
        $bookings = $this->em->getRepository('AppBundle:EquipmentBooking')->getAllForReturnProviderReminder($datetime);        
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                $eq = $inq->getEquipment();
                $provider = $eq->getUser();
                $discountCode = $bk->getDiscountCode();

                $email = $provider->getEmail();
                $emailHtml = $this->templating->render('Emails\mail_to_provider_reminder_return_offer.html.twig', array(
                    'mailer_app_url_prefix' => $this->appUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'discountCode' => $discountCode,
                    'item' => $eq
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
        $es = $this->em->getRepository('AppBundle:EquipmentBooking')->getAllForRateUserReminder($datetime);        
        $ts = $this->em->getRepository('AppBundle:TalentBooking')->getAllForRateUserReminder($datetime);        
        $bookings = array_merge($es, $ts);
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                if (get_class($bk) === 'AppBundle\\Entity\\EquipmentBooking') {
                    $eq = $inq->getEquipment();
                    $tmpl = 'Emails\mail_to_user_rate_provider.html.twig';
                    $route = 'rate-equipment';
                }
                else {
                    $eq = $inq->getTalent();
                    $tmpl = 'Emails\talent\mail_to_user_rate_provider.html.twig';
                    $route = 'talent-rate-talent';
                }
                $provider = $eq->getUser();
                $uuid = Utils::getUuid();

                if ($inq->getUser() !== null) {
                    $email = $inq->getUser()->getEmail();
                }
                else {
                    $email = $inq->getEmail();
                }
                $url = $this->appUrlPrefix . $this->router->generate($route, array('uuid' => $uuid));
                $emailHtml = $this->templating->render($tmpl, array(
                    'mailer_app_url_prefix' => $this->appUrlPrefix,
                    'inquiry' => $inq,
                    'provider' => $provider,
                    'item' => $eq,
                    'url' => $url
                ));
                $message = Swift_Message::newInstance()
                    ->setSubject('Du hast soeben eine Anfrage erhalten')
                    ->setFrom($this->from)
                    ->setTo($email)
                    ->setBody($emailHtml, 'text/html');
                $this->mailer->send($message);

                $bk->setNoticeRateUserAt(new DateTime());
                if (get_class($bk) === 'AppBundle\\Entity\\EquipmentBooking') {
                    $bk->setRateEquipmentUuid($uuid);
                }
                else {
                     $bk->setRateTalentUuid($uuid);
                }
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
        $es = $this->em->getRepository('AppBundle:EquipmentBooking')->getAllForRateProviderReminder($datetime);        
        $ts = $this->em->getRepository('AppBundle:TalentBooking')->getAllForRateProviderReminder($datetime);        
        $bookings = array_merge($es, $ts);
        foreach ($bookings as $bk) {
            try {
                $inq = $bk->getInquiry();
                if (get_class($bk) === 'AppBundle\\Entity\\EquipmentBooking') {
                    $eq = $inq->getEquipment();
                    $tmpl = 'Emails\mail_to_provider_rate_user.html.twig';
                    $route = 'rate-user';
                }
                else {
                    $eq = $inq->getTalent();
                    $tmpl = 'Emails\talent\mail_to_provider_rate_user.html.twig';
                    $route = 'talent-rate-user';
                }
                $provider = $eq->getUser();
                $uuid = Utils::getUuid();

                $url = $this->appUrlPrefix . $this->router->generate($route, array('uuid' => $uuid));
                $email = $provider->getEmail();
                $emailHtml = $this->templating->render($tmpl, array(
                    'mailer_app_url_prefix' => $this->appUrlPrefix,
                    'provider' => $provider,
                    'inquiry' => $inq,
                    'url' => $url
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
