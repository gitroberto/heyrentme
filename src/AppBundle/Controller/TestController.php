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
    /**
     * @Route("/test-pdf", name="test-pdf")
     */
    public function testPdfAction(Request $request) {
        $pdf = $this->get("white_october.tcpdf")->create(
            'LANDSCAPE',
            PDF_UNIT,
            PDF_PAGE_FORMAT,
            true,
            'UTF-8',
            false
        );
        $pdf->SetAuthor('Hey Sharing');
        $pdf->SetTitle('TCPDF try');
        $pdf->SetSubject('Your client');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->setFontSubsetting(true);

        $pdf->SetFont('helvetica', '', 11, '', true);
        $pdf->AddPage();

        $html = '<h1>Working on Symfony</h1>';

        $pdf->writeHTMLCell(
            $w = 0,
            $h = 0,
            $x = '',
            $y = '',
            $html,
            $border = 0,
            $ln = 1,
            $fill = 0,
            $reseth = true,
            $align = '',
            $autopadding = true
        );
        
        return new \Symfony\Component\HttpFoundation\StreamedResponse(function() use ($pdf) {
            $pdf->Output("symfony.pdf");
        });
    }
    
}