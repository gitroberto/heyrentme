<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Blog;
use AppBundle\Entity\BlogRelated;
use AppBundle\Entity\Image;
use AppBundle\Utils\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ExecutionContextInterface;

class BlogController  extends BaseAdminController {
    /**
     * 
     * @Route("/admin/blog", name="admin_blog_list")
     */
    public function indexAction() {
        return $this->render('admin/blog/index.html.twig');
    }
    
    protected $formHelper = null;
    protected $maximumNumberOfRelatedPosts = 10; 
    /**
     * 
     * @Route("/admin/blog/addrelated/{id}", name="admin_blog_add_related")
     */
    public function addRelatedAction(Request $request, $id) {
        
        
        $blog = $this->getDoctrineRepo('AppBundle:Blog')->find($id);
        if (!$blog) {
            return new Response(Response::HTTP_NOT_FOUND);
        }        
        $blogs = $this->getDoctrineRepo('AppBundle:Blog')->getListForRelated($id);
        
        $relatedBlogsCount = count($blog->getRelatedBlogs());
        
        $formTmp = $this->createFormBuilder(null, array(
                'constraints' => array(
                    new Callback(array($this, 'validateUniqueSelection'))
                )
        ));
        
        
        for($i = 0; $i < $this->maximumNumberOfRelatedPosts; $i++ ){
            $selectedValue = null;
            if ($i < $relatedBlogsCount ){
                $selectedValue = $blog->getRelatedBlogs()[$i]->getRelatedBlog();
            }
            
            
            $formTmp->add('position_'+$i, 'entity', array(
                  'class' => 'AppBundle:Blog',
                  'choices' => $blogs,
                  'empty_value' => 'Select related post',
                  'property' => 'extendedTitle',                 
                  'required' => false,
                  'data' => $selectedValue 
                  ));
        }        
        $form = $formTmp->getForm();
        
        $this->formHelper = $form;  
        $form->handleRequest($request);
        if ($form->isValid()) {
            //check if there is file
            $file = $request->files->get('upl');
            $em = $this->getDoctrine()->getManager();            
            $this->getDoctrineRepo('AppBundle:Blog')->cleanCurrentSelection($id);
            
            $pos = 1;
            $data = $form->getData();
            
            for($i = 0; $i< $this->maximumNumberOfRelatedPosts; $i++){
                $val = $data[$i];
                if ($val != null && $val != ""){
                    $br = new BlogRelated();
                    $br->setBlog($blog);
                    $br->setRelatedBlog($val);
                    $br->setPosition($pos++);
                    $em->persist($br);
                }
            }            
            $em->flush();
            return $this->redirectToRoute("admin_blog_list");
        }
        
        
        return $this->render('admin/blog/add_related.html.twig', array(
            "form" => $form->createView(),
            "blog" => $blog,
            "maximumNumberOfRelatedPosts" => $this->maximumNumberOfRelatedPosts
        ));
    }
    
    
    public function validateUniqueSelection($data, ExecutionContextInterface $context) {
        if ($this->formHelper != null) {
            
            $data = $this->formHelper->getData();
            $selectedIds = array();
            $notUniqueBlogs = array();
            for($i = 0; $i< $this->maximumNumberOfRelatedPosts; $i++){
                $val = $data[$i];
                if ($val != null && $val != ""){          
                    if (in_array($val->getId(), $selectedIds)){
                        if (!in_array($val->getTitle(), $notUniqueBlogs)){
                            $notUniqueBlogs[count($notUniqueBlogs)] = $val->getTitle();
                        }
                       
                    } else {
                        $selectedIds[count($selectedIds)] = $val->getId();
                    }
                }
            }
            
            if (count($notUniqueBlogs)){
                foreach($notUniqueBlogs as $notUniqueTitle)
                $context->buildViolation('Blog "' . $notUniqueTitle . '" selected more than once.')
                        ->addViolation();
            }
            
        }
    }
    
     /**
     * 
     * @Route("/admin/blog/new", name="admin_blog_new")
     */
    public function newAction(Request $request) {
        $blog = new Blog();
        
        $form = $this->createFormBuilder($blog, array(
                    'constraints' => array(
                        new Callback(array($this, 'validateSlug'))
                    )
                ))
                ->add('title', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 128))
                    )
                ))
                ->add('slug', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 128)),
                        new Regex(array(
                            'pattern' => '/^[a-z][-a-z0-9]*$/',
                            'htmlPattern' => '/^[a-z][-a-z0-9]*$/',
                            'message' => 'This is not a valid slug'
                        ))
                    )
                ))
                ->add('content', 'textarea', array(
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
            $file2 = $request->files->get('upl_big');
            //$date = new DateTime();            
            //$blog->setCreationDate($date);
            //$blog->setModificationDate($date);
            
            $em = $this->getDoctrine()->getManager();
            
            if ($file != null && $file->isValid()) {
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                $ext = strtolower($file->getClientOriginalExtension());
                
                $destDir = 
                    $image_storage_dir .
                    DIRECTORY_SEPARATOR .
                    'blog' .
                    DIRECTORY_SEPARATOR;
                $destFilename = sprintf("%s.%s", $uuid, $ext);
                
                $file->move($destDir, $destFilename);                
                
                // create thumbnail
                //<editor-fold>
                $fullpath = $destDir . DIRECTORY_SEPARATOR . $destFilename;
                $fullpath2 = $image_storage_dir . DIRECTORY_SEPARATOR . "blog" . DIRECTORY_SEPARATOR . "thumbnail" . DIRECTORY_SEPARATOR . $uuid . "." . $ext;
                
                $size = getimagesize($fullpath);
                $w = $size[0];
                $h = $size[1];                
                $nw = 360;
                $nh = 270;
                
                $src = imagecreatefromstring(file_get_contents($fullpath));
                $dst = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    imagejpeg($dst, $fullpath2, 85);
                }
                else if ($ext === 'png') {
                    imagepng($dst, $fullpath2, 9);
                }        

                imagedestroy($dst);        
                imagedestroy($src);
                //</editor-fold>
                
                // create object
                $img = new Image();
                $img->setUuid($uuid);
                $img->setName($file->getClientOriginalName());
                $img->setExtension($ext);
                $img->setPath('blog');
                $img->setThumbnailPath('blog' . DIRECTORY_SEPARATOR . 'thumbnail');
                              
                $em->persist($img);
                $em->flush();
                
                $blog->setImage($img);
            }
            if ($file2 != null && $file2->isValid()) {
                
                // TODO: save image code is redundant: make it a method of ImageRepository
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                
                $destDir = 
                    $image_storage_dir .
                    DIRECTORY_SEPARATOR .
                    'blog' .
                    DIRECTORY_SEPARATOR;
                $ext2 = strtolower($file2->getClientOriginalExtension());
                $destFilename = sprintf("%s.%s", $uuid, $ext2);
                
                $file2->move($destDir, $destFilename);
                
                // create object
                $img = new Image();
                $img->setUuid($uuid);
                $img->setName($file2->getClientOriginalName());
                $img->setExtension($ext2);
                $img->setPath('blog');
                              
                $em->persist($img);
                $em->flush();
                
                $blog->setBigImage($img);
            }
            
            // save to db
            
            $blog->setUuid(Utils::getUuid());
            $em->persist($blog);
            $em->flush();

            return $this->redirectToRoute("admin_blog_list");
        }
        
        
        return $this->render('admin/blog/new.html.twig', array(
            'form' => $form->createView()
        ));
    }
    public function validateSlug($blog, ExecutionContextInterface $context) {
        $unique = $this->getDoctrineRepo('AppBundle:Blog')->isSlugUnique($blog->getSlug(), $blog->getId());
        if (!$unique) {
            $context->buildViolation('The slug is not unique')->addViolation();
        }
    }
    
    /**
     * 
     * @Route("/admin/blog/edit/{id}", name="admin_blog_edit")
     */
    public function editAction(Request $request, $id) {
        $blog = $this->getDoctrineRepo('AppBundle:Blog')->find($id);

        if (!$blog) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        
        $form = $this->createFormBuilder($blog, array(
                    'constraints' => array(
                        new Callback(array($this, 'validateSlug'))
                    )
                ))
                ->add('id', 'hidden')
                ->add('title', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 128))
                    )
                ))
                ->add('slug', 'text', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('max' => 128)),
                        new Regex(array(
                            'pattern' => '/^[a-z][-a-z0-9]*$/',
                            'htmlPattern' => '/^[a-z][-a-z0-9]*$/',
                            'message' => 'This is not a valid slug'
                        ))
                    )
                ))
                ->add('content', 'textarea', array(
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
             
            //$blog->setModificationDate(new \DateTime());
            //check if there is file
            $file = $request->files->get('upl');
            $file2 = $request->files->get('upl_big');
            
            $em = $this->getDoctrine()->getManager();
            
            if ($file != null && $file->isValid()) {
                
                //remove old Image (both file from filesystem and entity from db)
                $this->getDoctrineRepo('AppBundle:Image')->removeImage($blog->getImage(), $this->getParameter('image_storage_dir'));
                $blog->setImage(null);                
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                $ext = strtolower($file->getClientOriginalExtension());
                
                //$destDir = sprintf("%sblog\\",$image_storage_dir);                
                $destDir = 
                        $image_storage_dir .
                        DIRECTORY_SEPARATOR .
                        'blog' .
                        DIRECTORY_SEPARATOR;
                $destFilename = sprintf("%s.%s", $uuid, $ext);
                
                $file->move($destDir, $destFilename);
                
                // create thumbnail
                //<editor-fold>
                $fullpath = $destDir . DIRECTORY_SEPARATOR . $destFilename;
                $fullpath2 = $image_storage_dir . DIRECTORY_SEPARATOR . "blog" . DIRECTORY_SEPARATOR . "thumbnail" . DIRECTORY_SEPARATOR . $uuid . "." . $ext;
                
                $size = getimagesize($fullpath);
                $w = $size[0];
                $h = $size[1];
                $nw = 360;
                $nh = 270;
                
                $src = imagecreatefromstring(file_get_contents($fullpath));
                $dst = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    imagejpeg($dst, $fullpath2, 85);
                }
                else if ($ext === 'png') {
                    imagepng($dst, $fullpath2, 9);
                }        

                imagedestroy($dst);        
                imagedestroy($src);
                //</editor-fold>

                // create object
                $img = new Image();
                $img->setUuid($uuid);
                $img->setName($file->getClientOriginalName());
                $img->setExtension($ext);
                $img->setPath('blog');
                $img->setThumbnailPath('blog' . DIRECTORY_SEPARATOR . 'thumbnail');
                              
                $em->persist($img);
                $em->flush();
                
                $blog->setImage($img);
            }            
            if ($file2 != null && $file2->isValid()) {
                
                //remove old Image (both file from filesystem and entity from db)
                $this->getDoctrineRepo('AppBundle:Image')->removeImage($blog->getBigImage(), $this->getParameter('image_storage_dir'));
                $blog->setBigImage(null);
                
                // save file
                $uuid = Utils::getUuid();
                $image_storage_dir = $this->getParameter('image_storage_dir');
                
                //$destDir = sprintf("%sblog\\",$image_storage_dir);                
                $destDir = 
                        $image_storage_dir .
                        DIRECTORY_SEPARATOR .
                        'blog' .
                        DIRECTORY_SEPARATOR;
                
                $ext2 = strtolower($file2->getClientOriginalExtension());
                $destFilename = sprintf("%s.%s", $uuid, $ext2);
                
                $file2->move($destDir, $destFilename);
                
                // create object
                $img = new Image();
                $img->setUuid($uuid);
                $img->setName($file2->getClientOriginalName());
                $img->setExtension($ext2);
                $img->setPath('blog');
                              
                $em->persist($img);
                $em->flush();
                
                $blog->setBigImage($img);
            }            
            
            // save to db

            $em->persist($blog);
            $em->flush();

            return $this->redirectToRoute("admin_blog_list");
        }
        
        
        return $this->render('admin/blog/edit.html.twig', array(
            'form' => $form->createView(),
            'blog' => $blog
        ));
    }
    
    /**
     * @Route("/admin/blog/delete/{id}", name="admin_blog_delete")
     */
    public function deleteAction(Request $request, $id) {
        $blog = $this->getDoctrineRepo('AppBundle:Blog')->find($id);

        if (!$blog) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        
        //remove old Image (both file from filesystem and entity from db)
        $this->getDoctrineRepo('AppBundle:Image')->removeImage($blog->getImage(), $this->getParameter('image_storage_dir'));
        $blog->setImage(null);
                
        $em = $this->getDoctrine()->getManager();
        $em->remove($blog);
        $em->flush();
        return $this->redirectToRoute("admin_blog_list");
    }
    /**
     * @Route("/admin/blog/publish/{id}", name="admin_blog_publish")
     */
    public function publishAction($id) {
        $blog = $this->getDoctrineRepo('AppBundle:Blog')->find($id);

        if (!$blog) {
            return new Response(Response::HTTP_NOT_FOUND);
        }

        $blog->setPublished(!$blog->getPublished());
                
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        
        return new JsonResponse(array('published' => $blog->getPublished()));
    }
    
    
    /**
     * @Route("/admin/blog/jsondata", name="admin_blog_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        
        $repo = $this->getDoctrineRepo('AppBundle:Blog');        
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page);
        $rowsCount = $repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $i = 0;
            $row['id'] = $dataRow->getId();
            $cell = array();
            $cell[$i++] = null;
            $cell[$i++] = $dataRow->getId();
            $cell[$i++] = $dataRow->getPublished();
            $cell[$i++] = $dataRow->getTitle();
            $cell[$i++] = $dataRow->getCreatedAt()->format('Y-m-d H:i');
            $cell[$i++] = $dataRow->getModifiedAt()->format('Y-m-d H:i');
            $cell[$i++] = $dataRow->getPosition();
            $cell[$i++] = $this->generateUrl('blog_preview', array('uuid'=>$dataRow->getUuid()));
            
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
