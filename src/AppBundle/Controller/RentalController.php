<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Candidate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
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
        $subcatsArr = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllAsArray($category->getId());
        $subcatArr = array_merge(array('' => 'Detailkategorie Wählen'), $subcatsArr);

        // build form
        //<editor-fold>
        $form = $this->createFormBuilder()
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
            ->getForm();
        //</editor-fold>
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            
            $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($data['subcategoryId']);

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
                'mailer_image_url_prefix' => $this->getParameter('mailer_image_url_prefix'),
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
            
            return $this->redirectToRoute('rentme');
        }
        
        return $this->render('rental/rental_detail.html.twig', array(
            'category' => $category,
            'form' => $form->createView()
        ));
    }
}