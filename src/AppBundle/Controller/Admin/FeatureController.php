<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Feature;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;



class FeatureController extends BaseAdminController {
    
    /**
     * 
     * @Route("/admin/feature/{featureSectionId}", name="admin_feature_list")
     */
    public function indexAction($featureSectionId) {
        
        return $this->render('admin/feature/index.html.twig', array(
            'featureSectionId' => $featureSectionId
        ));
    }
    
    /**
     * 
     * @Route("/admin/feature/new/{featureSectionId}", name="admin_feature_new")
     */
    public function newAction(Request $request, $featureSectionId) {
        $feature = new Feature();
        $featureSection = $this->getDoctrineRepo('AppBundle:FeatureSection')->find($featureSectionId);
        
        $feature->setFeatureSection($featureSection);
        
        
        //when the form is posted this method prefills entity with data from form
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            //check if there is file
            $em = $this->getDoctrine()->getManager();
            
            // save to db
            $em->persist($feature);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_feature_list', array( 'featureSectionId' => $featureSectionId )));
        }
        
        
        return $this->render('admin/feature/new.html.twig', array(
            'form' => $form->createView(),
            'featureSectionId' => $featureSectionId
        ));
    }
    
    /**
     * 
     * @Route("/admin/feature/edit/{id}", name="admin_feature_edit")
     */
    public function editAction(Request $request, $id) {
        $feature = $this->getDoctrineRepo('AppBundle:Feature')->find($id);

        if (!$feature) {
            throw $this->createNotFoundException('No feature found for id '.$id);
        }        
        
        $form = $this->createFormBuilder($feature)
                ->add('id', 'hidden')
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 128))
                    )
                ))
                ->add('shortName', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 128))
                    )
                ))
                ->add('freetext', 'checkbox', array(
                    'required' => false
                ))
                ->add('position', 'integer', array(
                    'required' => false,
                    'constraints' => array(
                        new Type(array('type' => 'integer'))
                    )
                ))
                ->getForm();

       
        //when the form is posted this method prefills entity with data from form
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($feature);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_feature_list', array( 'featureSectionId' => $feature->getFeatureSection()->getId() )));
        }
        
        
        return $this->render('admin/feature/edit.html.twig', array(
            'form' => $form->createView(),
            'feature' => $feature,
            'featureSectionId' =>  $feature->getFeatureSection()->getId()
        ));
    }
    
    /**
     * 
     * @Route("/admin/feature/delete/{id}", name="admin_feature_delete")
     */
    public function deleteAction(Request $request, $id) {
        $feature = $this->getDoctrineRepo('AppBundle:Feature')->find($id);
        
        $featureSectionId =  $feature->getFeatureSection()->getId();
        
        if (!$feature) {
            throw $this->createNotFoundException('No feature found for id '.$id);
        }
               
        $em = $this->getDoctrine()->getManager();
        $em->remove($feature);
        $em->flush();
       // return $this->redirectToRoute("admin_feature_list");
        return $this->redirect($this->generateUrl('admin_feature_list', array( 'featureSectionId' => $featureSectionId )));
    }
    
    
    /**
     * @Route("/admin/feature/jsondata/{featureSectionId}", name="admin_feature_jsondata")
     */
    public function JsonData(Request $request, $featureSectionId) {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');                
        $callback = $request->get('callback');
        
        $repo = $this->getDoctrineRepo('AppBundle:Feature');
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $featureSectionId);
        $rowsCount = $repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result
        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $i = 0;
            $cell[$i++] = '';
            $cell[$i++] = $dataRow->getFeatureSection()->getName();
            $cell[$i++] = $dataRow->getName();
            $cell[$i++] = $dataRow->getShortName();
            $cell[$i++] = $dataRow->getFreetext();
            $cell[$i++] = $dataRow->getPosition();
            $row['cell'] = $cell;
            array_push($rows, $row);
        }
        
        $result = array( // main result object as json
            'records' => $rowsCount,
            'page' => $page,
            'total' => $pagesCount,
            'rows' => $rows
        );        
        
        $resp = new JsonResponse($result, JsonResponse::HTTP_OK);
        $resp->setCallback($callback);
        return $resp;
    }    
}
