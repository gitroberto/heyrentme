<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Candidate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class RentalController extends BaseController {
    
    /** 
     * @Route("/rental", name="rental")
     */
    public function rentalAction(Request $request) {
        $subcats = $this->getSubcategories($request);
        $route = $this->isGranted('IS_AUTHENTICATED_REMEMBERED') ? 'equipment-add-1' : 'rental-detail';
        
        return $this->render('rental/rental.html.twig', array(
            'subcategories' => $subcats,
            'route' => $route
        ));
    }
    
    /**
     * @Route("/rental-detail/{subcategoryId}", name="rental-detail")
     */
    public function rentalDetailAction(Request $request, $subcategoryId) {
        $subcategory = $this->getDoctrineRepo('AppBundle:Subcategory')->find($subcategoryId);

        // build form
        //<editor-fold>
        $form = $this->createFormBuilder()
            ->add('equipment', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 128))
                )
            ))
            ->add('name', 'text', array(
                'required' => false,
                'constraints' => array(                    
                    new Length(array('max' => 128))
                )
            ))
            ->add('email', 'email', array(
                'constraints' => array(
                    new Email(array('checkHost' => true))
                )
            ))
            ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();

            // create Candidate object
            $cand = new Candidate();
            $cand->setSubcategory($subcategory);
            $cand->setName($data['name']);
            $cand->setEmail($data['email']);
            $cand->setEquipment($data['equipment']);
            
            // save to database
            $em = $this->getDoctrine()->getManager();
            $em->persist($cand);
            $em->flush();
            
            // send email
            //<editor-fold>            
            $url = sprintf('%s%s?register', 
                    $request->getSchemeAndHttpHost(),
                    $this->get('router')->generate('rentme'));
            $emailHtml = $this->renderView('Emails/candidate.html.twig', array(
                'mailer_image_url_prefix' => $this->getParameter('mailer_image_url_prefix'),
                'custom_message' => $subcategory->getEmailBody(),
                'url' => $url
            ));
            $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
            $message = Swift_Message::newInstance()
                ->setSubject('Willkomen bei')
                ->setFrom($from)
                ->setTo($cand->getEmail())
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);
            //</editor-fold>
            
            return $this->redirectToRoute('rentme');
        }
        
        return $this->render('rental/rental_detail.html.twig', array(
            'subcategory' => $subcategory,
            'form' => $form->createView()
        ));
    }
}