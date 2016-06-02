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
        $posts = $this->getDoctrineRepo('AppBundle:Blog')->getPublishedOrderedByPosition();
        
        $this->createSessionBlogList($request, $posts);
        
        return $this->render('blog/blog.html.twig', array(
            'posts' => $posts,
            'newsletterBar' => true
        ));
    }
    
    /**
     * @Route("/blog/{slug}", name="blog_detail")
     */
    public function detailAction(Request $request, $slug) {
        $post = $this->getDoctrineRepo('AppBundle:Blog')->getBySlug($slug);        
        return $this->display($request, $post, false);
    }
    
    /**
     * @Route("/blog/preview/{uuid}", name="blog_preview")
     */
    public function previewAction(Request $request, $uuid) {
        $post = $this->getDoctrineRepo('AppBundle:Blog')->getOneByUuid($uuid);
        return $this->display($request, $post, true);
    }
    
    protected function display(Request $request, $post, $isPreview){
        $posts = array();
        
        $related = $this->getDoctrineRepo('AppBundle:Blog')->getRelated($post->getId());
        
        foreach ($related as $rp){
            array_push($posts, $rp);
            //$posts[count($posts)] = $rp->getRelatedBlog();
        }
        
        // determine prev/next post for navigation
        $ids = $this->getSessionBlogList($request);
        $i = array_search($post->getId(), $ids);
        if ($i !== null) {
            $repo = $this->getDoctrineRepo('AppBundle:Blog');
            if ($i > 0) {
                $prevPost = $repo->find($ids[$i - 1]);
            } else {
                $prevPost = $repo->find($ids[count($ids) - 1]);
            }

            if ($i < count($ids) - 1) {
                $nextPost = $repo->find($ids[$i + 1]);
            } else {
                $nextPost = $repo->find($ids[0]);
            }
        }
        
        return $this->render('blog/blog_detail.html.twig', array(
            'post' => $post,
            'posts' => $posts,
            'nextPost' => $nextPost,
            'prevPost' => $prevPost,
            'isPreview' => $isPreview,
            'newsletterBar' => true
        ));
    
    }
    
    private function createSessionBlogList(Request $request, $posts) {
        $ids = array();
        foreach ($posts as $post)
            array_push($ids, $post->getId());

        $session = $request->getSession();
        $session->set('BlogsList', $ids);        
    }
    private function getSessionBlogList(Request $request) {
        $session = $request->getSession();
        if (!$session->has('BlogList')) {
            $posts = $this->getDoctrineRepo('AppBundle:Blog')->getPublishedOrderedByPosition();
            $this->createSessionBlogList($request, $posts);
        }
        return $session->get('BlogsList');
    }
}
