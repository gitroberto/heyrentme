<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Candidate;
use AppBundle\Entity\Category;
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
        $subcats = $this->getCategoriesByType($request, Category::TYPE_EQUIPMENT);
        
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
            $this->sendGuidelinesEmail($request, $cand->getEmail(), Category::TYPE_EQUIPMENT);
            
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
        $cat = $this->getDoctrineRepo('AppBundle:Category')->find($categoryId);
        $subcatsArr = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllForDropdown($categoryId);
        $url = $this->generateUrl(
            $cat->getType() === Category::TYPE_EQUIPMENT ? 'rental-form' : 'offer-form',
            array('categoryId' => $categoryId)
        );        

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
        $subcat = $this->getDoctrineRepo('AppBundle:Subcategory')->find($subcategoryId);        
        
        $this->sendGuidelinesEmail($request, $user->getEmail(), $subcat->getCategory()->getType());
                
        return new JsonResponse(array('status' => 'ok'));
    }

    /** 
     * @Route("/offer", name="offer")
     */
    public function offerAction(Request $request) {
        $subcats = $this->getCategoriesByType($request, Category::TYPE_TALENT);
        
        return $this->render('rental/offer.html.twig', array(
            'categories' => $subcats
        ));
    }    
    
    /**
     * @Route("/offer-detail/{categoryId}", name="offer-detail")
     */
    public function offerDetailAction(Request $request, $categoryId) {
        $category = $this->getDoctrineRepo('AppBundle:Category')->find($categoryId);
        
        return $this->render('rental/offer_detail.html.twig', array(
            'category' => $category
        ));
    }
    
    /**
    * @Route("/offer-form/{categoryId}", name="offer-form")
    */
    public function offerFormAction(Request $request, $categoryId) {
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
            $this->sendGuidelinesEmail($request, $cand->getEmail(), Category::TYPE_TALENT);
            
            // successful submission, reset values
            $form = $this->createRentalForm($category->getId(), array('success' => 1));
            
            return $this->render('rental/offer_form.html.twig', array(
                'category' => $category,
                'form' => $form->createView()
            ));
        }
        
        return $this->render('rental/offer_form.html.twig', array(
            'category' => $category,
            'form' => $form->createView()
        ));
    }
    
    private function sendGuidelinesEmail($request, $emailTo, $type) {
        $url = sprintf('%s%s?register', 
                $request->getSchemeAndHttpHost(),
                $this->get('router')->generate('bookme'));
        $videoUrl = $request->getSchemeAndHttpHost() .
                $this->get('router')->generate('tutorial-video');
        $photoUrl = $request->getSchemeAndHttpHost() .
                $this->get('router')->generate('tutorial-photo');

        $tmpl = $type === Category::TYPE_EQUIPMENT ? 'Emails/candidate.html.twig' : 'Emails/candidate-talent.html.twig';
        $emailHtml = $this->renderView($tmpl, array(
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix'),
            'url' => $url,
            'videoUrl' => $videoUrl,
            'photoUrl' => $photoUrl
        ));
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject('Willkommen bei hey! VIENNA')
            ->setFrom($from)
            ->setTo($emailTo)
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
    }
}