<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\FeatureSection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;



class FeatureSectionController extends BaseAdminController {
    
    /**
     * 
     * @Route("/admin/feature-section", name="admin_feature_section_list")
     */
    public function indexAction() {
        
        return $this->render('admin/featureSection/index.html.twig');
    }
    
    /**
     * 
     * @Route("/admin/feature-section/new/", name="admin_feature_section_new")
     */
    public function newAction(Request $request) {
        $featureSection = new FeatureSection();
        $subcategory = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllOrderedByName();
        
        $form = $this->createFormBuilder($featureSection)
                ->add('subcategory', 'entity', array(
                  'class' => 'AppBundle:Subcategory',
                  'property' => 'name',
                  'data' => $subcategory
                  ))
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 256))
                    )
                ))
                ->add('exclusive', 'checkbox', array(
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
            //check if there is file
            $em = $this->getDoctrine()->getManager();
            
            // save to db
            $em->persist($featureSection);
            $em->flush();

            return $this->redirectToRoute("admin_feature_section_list");
        }
        
        
        return $this->render('admin/featureSection/new.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * 
     * @Route("/admin/feature-section/edit/{id}", name="admin_feature_section_edit")
     */
    public function editAction(Request $request, $id) {
        $featureSection = $this->getDoctrineRepo('AppBundle:FeatureSection')->find($id);

        if (!$featureSection) {
            throw $this->createNotFoundException('No feature section found for id '.$id);
        }
        
        $form = $this->createFormBuilder($featureSection)
                ->add('id', 'hidden')
                ->add('subcategory', 'entity', array(
                  'class' => 'AppBundle:Subcategory',
                  'property' => 'name'
                  ))
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 256))
                    )
                ))
                ->add('exclusive', 'checkbox', array(
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
            $em->persist($featureSection);
            $em->flush();

            return $this->redirectToRoute("admin_feature_section_list");
        }
        
        
        return $this->render('admin/featureSection/edit.html.twig', array(
            'form' => $form->createView(),
            'featureSection' => $featureSection
        ));
    }
    
    /**
     * 
     * @Route("/admin/feature-section/delete/{id}", name="admin_feature_section_delete")
     */
    public function deleteAction(Request $request, $id) {
        $featureSection = $this->getDoctrineRepo('AppBundle:FeatureSection')->find($id);

        if (!$featureSection) {
            throw $this->createNotFoundException('No feature section found for id '.$id);
        }
               
        $em = $this->getDoctrine()->getManager();
        $em->remove($featureSection);
        $em->flush();
        return $this->redirectToRoute("admin_feature_section_list");
    }
    
    
    /**
     * @Route("/admin/feature-section/jsondata", name="admin_feature_section_jsondata")
     */
    public function JsonData(Request $request) {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $fSubcategory = $request->get('s_name');
        $fName = $request->get('fs_name');
        $callback = $request->get('callback');
        
        $repo = $this->getDoctrineRepo('AppBundle:FeatureSection');
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, $fSubcategory, $fName);
        $rowsCount = $repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result
        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $i = 0;
            $cell[$i++] = '';
            $cell[$i++] = $dataRow->getSubcategory()->getName();
            $cell[$i++] = $dataRow->getName();
            $cell[$i++] = $dataRow->getExclusive();
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
