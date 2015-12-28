<?php

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class BlogController extends BaseController {
    
    /**
     * @Route("/blog", name="blog")
     */
    public function indexAction(Request $request) {
        $posts = $this->getDoctrineRepo('AppBundle:Blog')->getAllOrderedByPosition();
       
        return $this->render('blog/blog.html.twig', array(
            'posts' => $posts
        ));
    }
    
    /**
     * @Route("/blog/{slug}", name="blog_detail")
     */
    public function detailAction(Request $request, $slug) {
        $post = $this->getDoctrineRepo('AppBundle:Blog')->getBySlug($slug);
        $posts = $this->getDoctrineRepo('AppBundle:Blog')->getAllOrderedByPosition();
       
        $nextPost = $this->getDoctrineRepo('AppBundle:Blog')->getByPosition($post->getPosition() + 1);
        $prevPost = $this->getDoctrineRepo('AppBundle:Blog')->getByPosition($post->getPosition() - 1);
        
        
        return $this->render('blog/blog_detail.html.twig', array(
            'post' => $post,
            'posts' => $posts,
            'nextPost' => $nextPost,
            'prevPost' => $prevPost
        ));
    }
    
  
}
