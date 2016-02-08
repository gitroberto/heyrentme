<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

class TestController extends BaseController {

    /**
     * @Route("/testvg", name="testvg")
     */
    public function testvgAction(Request $request) {
        
        $form = $this->createFormBuilder(null, array(
            'validation_groups' => array('register', 'login')
        ))                
                ->add('text1', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'register'
                    ),
                    'constraints' => array(
                        new NotBlank(array(
                            'groups' => 'register'
                        ))
                    )
                ))
                ->add('text2', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'login'
                    ),
                    'constraints' => array(
                        new NotBlank(array(
                            'groups' => 'login'
                        ))
                    )
                ))
                ->add('register', 'submit', array(
                    'validation_groups' => array('register')
                ))
                ->add('login', 'submit', array(
                    'validation_groups' => array('login')
                ))
                ->getForm();
        
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            return new Response('OK');
        }            
        
        return $this->render('test/testvg.html.twig', array(
            'form' => $form->createView()
        ));
    }
}