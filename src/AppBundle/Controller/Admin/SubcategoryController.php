<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Image;
use AppBundle\Entity\Subcategory;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

class SubcategoryController extends BaseAdminController {
    
    /**
     * 
     * @Route("/admin/subcategory/{categoryID}", name="admin_subcategory_list")
     */
    public function indexAction($categoryID) {
        return $this->render('admin/subcategory/index.html.twig', array(
            'category' => $this->getDoctrine()->getManager()->getReference('AppBundle:Category', $categoryID)
        ));
    }
    
    /**
     * 
     * @Route("/admin/subcategory/new/{categoryID}", name="admin_subcategory_new")
     */
    public function newAction(Request $request, $categoryID) {
        $subcategory = new Subcategory();
        $category = $this->getDoctrine()->getManager()->getReference('AppBundle:Category', $categoryID);
        $form = $this->createFormBuilder($subcategory)
                ->add('category', 'entity', array(
                  'class' => 'AppBundle:Category',
                  'property' => 'name',
                  'data' => $category
                  ))
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 256))
                    )
                ))
                ->add('slug', 'text', array(
                    'constraints' => array(
                        // TODO: check for uniqueness of slug (category + subcategory; copy from blog)
                        new NotBlank(),
                        new Length(array('max' => 256)),
                        new Regex(array(
                            'pattern' => '/^[a-z][-a-z0-9]*$/',
                            'htmlPattern' => '/^[a-z][-a-z0-9]*$/',
                            'message' => 'This is not a valid slug'
                        ))
                    )
                ))
                ->add('emailBody', 'textarea', array(
                    'required' => false,
                    'constraints' => array(
                        new NotBlank()
                    )
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
            $file = $request->files->get('upl');
            
            $em = $this->getDoctrine()->getManager();
            
            if ($file != null && $file->isValid()) {
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                
                //$destDir = sprintf("%ssubcategory\\",$image_storage_dir);
                $destDir = 
                    $image_storage_dir .
                    DIRECTORY_SEPARATOR .
                    'subcategory' .
                    DIRECTORY_SEPARATOR;
                $ext = strtolower($file->getClientOriginalExtension());
                $destFilename = sprintf("%s.%s", $uuid, $ext);
                
                $file->move($destDir, $destFilename);
                
                // create object
                $img = new Image();
                $img->setUuid($uuid);
                $img->setName($destFilename);
                $img->setExtension($ext);
                $img->setOriginalPath($file->getClientOriginalName());
                $img->setPath('subcategory');
                              
                $em->persist($img);
                $em->flush();
                
                $subcategory->setImage($img);
            }
            
            // save to db
            $em->persist($subcategory);
            $em->flush();

            return $this->redirectToRoute("admin_subcategory_list", array( 'categoryID' => $categoryID ));
        }
        
        
        return $this->render('admin/subcategory/new.html.twig', array(
            'form' => $form->createView(),
            'category' => $category
        ));
    }
    
    /**
     * 
     * @Route("/admin/subcategory/edit/{id}", name="admin_subcategory_edit")
     */
    public function editAction(Request $request, $id) {
        $subcategory = $this->getDoctrineRepo('AppBundle:Subcategory')->find($id);
        if (!$subcategory) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        $category = $subcategory->getCategory();
        
        $form = $this->createFormBuilder($subcategory)
                ->add('id', 'hidden')
                ->add('category', 'entity', array(
                  'class' => 'AppBundle:Category',
                  'property' => 'name',
                  'data' => $category
                  ))
                ->add('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 256))
                    )
                ))
                ->add('slug', 'text', array(
                    'constraints' => array(
                        // TODO: check for uniqueness of slug (category + subcategory; copy from blog)
                        new NotBlank(),
                        new Length(array('max' => 256)),
                        new Regex(array(
                            'pattern' => '/^[a-z][-a-z0-9]*$/',
                            'htmlPattern' => '/^[a-z][-a-z0-9]*$/',
                            'message' => 'This is not a valid slug'
                        ))
                    )
                ))
                ->add('emailBody', 'textarea', array(
                    'required' => false,
                    'constraints' => array(
                        new NotBlank()
                    )
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
            $file = $request->files->get('upl');
            
            $em = $this->getDoctrine()->getManager();
            
            if ($file != null && $file->isValid()) {
                
                //remove old Image (both file from filesystem and entity from db)
                $this->getDoctrineRepo('AppBundle:Image')->removeImage($subcategory->getImage(), $this->getParameter('image_storage_dir'));
                $subcategory->setImage(null);
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                
                //$destDir = sprintf("%scategory\\",$image_storage_dir);
                $destDir = 
                        $image_storage_dir .
                        DIRECTORY_SEPARATOR .
                        'subcategory' .
                        DIRECTORY_SEPARATOR;
                $ext = strtolower($file->getClientOriginalExtension());
                $destFilename = sprintf("%s.%s", $uuid, $ext);
                
                $file->move($destDir, $destFilename);
                
                // create object
                $img = new Image();
                $img->setUuid($uuid);
                $img->setName($destFilename);
                $img->setExtension($ext);
                $img->setOriginalPath($file->getClientOriginalName());
                $img->setPath('category');
                              
                $em->persist($img);
                $em->flush();
                
                $subcategory->setImage($img);
            }            
            
            
            // save to db
            $em->persist($subcategory);
            $em->flush();

            return $this->redirectToRoute("admin_subcategory_list", array( 'categoryID' => $category->getId() ));
        }
        
        
        return $this->render('admin/subcategory/edit.html.twig', array(
            'form' => $form->createView(),
            'category' => $category,
            'subcategory' => $subcategory
        ));
    }
    
    /**
     * 
     * @Route("/admin/subcategory/delete/{id}", name="admin_subcategory_delete")
     */
    public function deleteAction(Request $request, $id) {
        $subcategory = $this->getDoctrineRepo('AppBundle:Subcategory')->find($id);

        if (!$subcategory) {
            return new Response(Response::HTTP_NOT_FOUND);
        
        }
        //remove old Image (both file from filesystem and entity from db)
        $this->getDoctrineRepo('AppBundle:Image')->removeImage($subcategory->getImage(), $this->getParameter('image_storage_dir'));
        $subcategory->setImage(null);
        
        $category = $subcategory->getCategory();
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($subcategory);
        $em->flush();
        return $this->redirectToRoute("admin_subcategory_list", array( 'categoryID' => $category->getId() ));
    }
    
    
    /**
     * @Route("/admin/subcategory/jsondata/{categoryID}", name="admin_subcategory_jsondata")
     */
    public function JsonData(Request $request, $categoryID)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        
        
        
        $repo = $this->getDoctrineRepo('AppBundle:Subcategory');
        $dataRows = $repo->getGridOverview($categoryID, $sortColumn, $sortDirection, $pageSize, $page);
        $rowsCount = $repo->countAllByCategoryId($categoryID);
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result
        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $cell[0] = null;
            $cell[1] = $dataRow->getId();
            $cell[2] = $dataRow->getCategory()->getName();
            $cell[3] = $dataRow->getName();
            $cell[4] = $dataRow->getSlug();
            $cell[5] = $dataRow->getPosition();
            
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
        
        
        
        
        
        
        
        /*
        $rows = $this->GetData($categoryID, $sortColumn, $sortDirection, $pageSize, $page);
        $rowsCount = $this->getDoctrineRepo('AppBundle:Subcategory')->countAllByCategoryId($categoryID);
        $pagesCount =   ceil($rowsCount/$pageSize);
        
        $rowsStr = "";
        $rowsTemplate = '{ "id": %s, "cell": [null, "%s", "%s", "%s", "%s", "%s" ] }';
        $i = 0;
        foreach($rows as $row){
            if ($i > 0) {
                $rowsStr .= ", ";
            }
            $rowsStr .= sprintf($rowsTemplate, $row->getId(), $row->getId(), $row->getCategory()->getName(), $row->getName(), $row->getSlug(), $row->getPosition() );
            $i .=1;
        }
        
        $json = sprintf('{ "records":%s,"page":%s ,"total":%s ,"rows": [ %s ] }', $rowsCount, $page, $pagesCount, $rowsStr );
        
        $response = new Response();
        $response->setContent('/**//*'.$method.'('. $json .')');
        $response->headers->set('Content-Type', 'text/javascript');
        return $response;
       */
    }
    /*
    public function GetData($categoryID, $sortColumn, $sortDirection, $pageSize, $page)
    {
        $cats = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllByCategoryId($categoryID, $sortColumn, $sortDirection, $pageSize, $page);
        return $cats;
    }*/


}
