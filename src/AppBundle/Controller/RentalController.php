<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Candidate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;


class RentalController extends BaseController {
    
    /** 
     * @Route("/rental", name="rental")
     */
    public function rentalAction(Request $request) {
        $subcats = $this->getCategories($request);
        
        return $this->render('rental/rental.html.twig', array(
            'categories' => $subcats
        ));
    }
    
    /**
     * @Route("/rental-detail/{categoryId}", name="rental-detail")
     */
    public function rentalDetailAction(Request $request, $categoryId) {
        $category = $this->getDoctrineRepo('AppBundle:Category')->find($categoryId);
        
        return $this->render('rental/rental_detail.html.twig', array(
            'category' => $category
        ));
    }
    
    /**
    * @Route("/rental-form/{categoryId}", name="rental-form")
    */
    public function rentalFormAction(Request $request, $categoryId) {
        $category = $this->getDoctrineRepo('AppBundle:Category')->find($categoryId);
        

        // build form
        //<editor-fold>
        $form = $this->createRentalForm($category->getId());
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            
            $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find(intval($data['subcategoryId']));

            // create Candidate object
            $cand = new Candidate();
            $cand->setSubcategory($subcat);
            $cand->setEmail($data['email']);
            
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
                'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
                //'custom_message' => $subcategory->getEmailBody(),
                'url' => $url
            ));
            $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
            $message = Swift_Message::newInstance()
                ->setSubject('Willkommen bei hey! VIENNA')
                ->setFrom($from)
                ->setTo($cand->getEmail())
                ->setBody($emailHtml, 'text/html');
            $this->get('mailer')->send($message);
            //</editor-fold>
            
            // successful submission, reset values
            $form = $this->createRentalForm($category->getId(), array('success' => 1));
            
            return $this->render('rental/rental_form.html.twig', array(
                'category' => $category,
                'form' => $form->createView()
            ));
        }
        
        return $this->render('rental/rental_form.html.twig', array(
            'category' => $category,
            'form' => $form->createView()
        ));
    }
    
    private function createRentalForm($categoryId, $data = array()) {
        $subcatsArr = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllForDropdown($categoryId);
        $url = $this->generateUrl('rental-form', array('categoryId' => $categoryId));        

        return $this->createFormBuilder($data)
            ->setAction($url)
            ->add('subcategoryId', 'choice', array(
                'choices' => $subcatsArr,
                'choices_as_values' => false,
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('email', 'email', array(
                'constraints' => array(
                    new Email(array('checkHost' => true))
                )
            ))
            ->add('success', 'hidden')
            ->getForm();
    }

    /**
     * @Route("/rental-guidelines/{subcategoryId}", name="rental-guidelines")
     */
    public function guidelinesAction(Request $request, $subcategoryId) {
        $user = $this->getUser();
        //$subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($subcategoryId);
        
        // todo: anleitung depends on subcategory id?
        
        $url = sprintf('%s%s?register', 
                $request->getSchemeAndHttpHost(),
                $this->get('router')->generate('provider'));
        $emailHtml = $this->renderView('Emails/candidate.html.twig', array(
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
            //'custom_message' => $subcategory->getEmailBody(),
            'url' => $url
        ));
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject('Anleitung hey! VIENNA')
            ->setFrom($from)
            ->setTo($user->getEmail())
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
        return new JsonResponse(array('status' => 'ok'));
    }
}