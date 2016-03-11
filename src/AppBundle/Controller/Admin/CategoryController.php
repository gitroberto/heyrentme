<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use AppBundle\Entity\Image;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;


class CategoryController extends BaseAdminController {
    
    /**
     * 
     * @Route("/admin/category", name="admin_category_list")
     */
    public function indexAction() {
        return $this->render('admin/category/index.html.twig');
    }
    
    /**
     * 
     * @Route("/admin/category/new", name="admin_category_new")
     */
    public function newAction(Request $request) {
        $category = new Category();
        
        $form = $this->createFormBuilder($category)
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
            $file2 = $request->files->get('uplBig');
            
            $em = $this->getDoctrine()->getManager();
            
            if ($file != null && $file->isValid()) {
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                
                $destDir = 
                    $image_storage_dir .
                    DIRECTORY_SEPARATOR .
                    'category' .
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
                
                $category->setImage($img);
            }
            if ($file2 != null && $file2->isValid()) {
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                
                $destDir = 
                    $image_storage_dir .
                    DIRECTORY_SEPARATOR .
                    'category' .
                    DIRECTORY_SEPARATOR;
                $ext2 = strtolower($file2->getClientOriginalExtension());
                $destFilename = sprintf("%s.%s", $uuid, $ext2);
                
                $file2->move($destDir, $destFilename);
                
                // create object
                $img = new Image();
                $img->setUuid($uuid);
                $img->setName($destFilename);
                $img->setExtension($ext2);
                $img->setOriginalPath($file2->getClientOriginalName());
                $img->setPath('category');
                              
                $em->persist($img);
                $em->flush();
                
                $category->setBigImage($img);
            }
            
            // save to db
            
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute("admin_category_list");
        }
        
        
        return $this->render('admin/category/new.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * 
     * @Route("/admin/category/edit/{id}", name="admin_category_edit")
     */
    public function editAction(Request $request, $id) {
        $category = $this->getDoctrineRepo('AppBundle:Category')->find($id);

        if (!$category) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        
        $form = $this->createFormBuilder($category)
                ->add('id', 'hidden')
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
            $file2 = $request->files->get('uplBig');
            
            $em = $this->getDoctrine()->getManager();
            
            if ($file != null && $file->isValid()) {
                
                //remove old Image (both file from filesystem and entity from db)
                $this->getDoctrineRepo('AppBundle:Image')->removeImage($category->getImage(), $this->getParameter('image_storage_dir'));
                $category->setImage(null);                
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                
                //$destDir = sprintf("%scategory\\",$image_storage_dir);                
                $destDir = 
                        $image_storage_dir .
                        DIRECTORY_SEPARATOR .
                        'category' .
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
                
                $category->setImage($img);
            }            
            if ($file2 != null && $file2->isValid()) {
                
                //remove old Image (both file from filesystem and entity from db)
                $old = $category->getBigImage();
                if ($old !== null) {
                    $this->getDoctrineRepo('AppBundle:Image')->removeImage($old, $this->getParameter('image_storage_dir'));
                    $category->setBigImage(null);                
                }
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                
                //$destDir = sprintf("%scategory\\",$image_storage_dir);                
                $destDir = 
                        $image_storage_dir .
                        DIRECTORY_SEPARATOR .
                        'category' .
                        DIRECTORY_SEPARATOR;
                $ext2 = strtolower($file2->getClientOriginalExtension());
                $destFilename = sprintf("%s.%s", $uuid, $ext2);
                
                $file2->move($destDir, $destFilename);
                
                // create object
                $img = new Image();
                $img->setUuid($uuid);
                $img->setName($destFilename);
                $img->setExtension($ext2);
                $img->setOriginalPath($file2->getClientOriginalName());
                $img->setPath('category');
                              
                $em->persist($img);
                $em->flush();
                
                $category->setBigImage($img);
            }            
            
            // save to db

            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute("admin_category_list");
        }
        
        
        return $this->render('admin/category/edit.html.twig', array(
            'form' => $form->createView(),
            'category' => $category
        ));
    }
    
    /**
     * 
     * @Route("/admin/category/delete/{id}", name="admin_category_delete")
     */
    public function deleteAction(Request $request, $id) {
        $category = $this->getDoctrineRepo('AppBundle:Category')->find($id);

        if (!$category) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        
        //remove old Image (both file from filesystem and entity from db)
        $this->getDoctrineRepo('AppBundle:Image')->removeImage($category->getImage(), $this->getParameter('image_storage_dir'));
        $category->setImage(null);
        
        //remove subcategories
        $this->getDoctrineRepo('AppBundle:Category')->removeSubcategoriesFromCategory($category);
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();
        return $this->redirectToRoute("admin_category_list");
    }
    
    
    /**
     * @Route("/admin/category/jsondata", name="admin_category_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        
        
        $repo = $this->getDoctrineRepo('AppBundle:Category');
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page);
        $rowsCount = $repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result
        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();            
            $cell[0] = null;
            $cell[1] = $dataRow->getId();
            $cell[2] = $dataRow->getName();
            $cell[3] = $dataRow->getSlug();
            $cell[4] = $dataRow->getPosition();
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
        
        $rows = $this->GetData($sortColumn, $sortDirection, $pageSize, $page);
        $rowsCount = $this->getDoctrineRepo('AppBundle:Category')->countAll();
        $pagesCount =   ceil($rowsCount/$pageSize);
        
        $rowsStr = "";
        $rowsTemplate = '{ "id": %s, "cell": [null, "%s", "%s", "%s", "%s" ] }';
        $i = 0;
        foreach($rows as $row){
            if ($i > 0) {
                $rowsStr .= ", ";
            }
            $rowsStr .= sprintf($rowsTemplate, $row->getId(), $row->getId(), $row->getName(), $row->getSlug(), $row->getPosition() );
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
    public function GetData($sortColumn, $sortDirection, $pageSize, $page)
    {
        $cats = $this->getDoctrineRepo('AppBundle:Category')->getAll($sortColumn, $sortDirection, $pageSize, $page);
        return $cats;
    }*/


}
