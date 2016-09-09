<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    public function getUser() {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
    /**
     * Just a shortcut for $this->getDoctrine()->getRepository(...)
     * @param string $name
     */
    public function getDoctrineRepo($name) {
        return $this->getDoctrine()->getRepository($name);
    }
    /*
     * We cache categories and subcategories in user session,
     * since they won't change often.
     */
    protected function initCategories($session) {
        if (!$session->has('CategoryList')) {
            $cats = $this->getDoctrineRepo('AppBundle:Category')->getActiveOrderedByPosition();
            $categories = array();
            foreach ($cats as $cat) {
                $categories[$cat->getSlug()] = array(
                    'id' => $cat->getId(),
                    'name' => $cat->getName(),
                    'descriptionMobile' => $cat->getDescriptionMobile(),
                    'type' => $cat->getType(),
                    'slug' => $cat->getSlug(),
                    'imageUrl' => $cat->getImage() !== null ? $cat->getImage()->getUrlPath($this->getParameter('image_url_prefix')) : null,
                    'bigImageUrl' => $cat->getBigImage() !== null ? $cat->getBigImage()->getUrlPath($this->getParameter('image_url_prefix')) : null
                );
            }
            $session->set('CategoryList', $categories);
        }
    }
    protected function getCategories(Request $request) {
        $session = $request->getSession();
        $this->initCategories($session);
        return $session->get('CategoryList');
    }
    protected function getCategoriesByType(Request $request, $type) {
        $session = $request->getSession();
        $this->initCategories($session);
        $arr = $session->get('CategoryList');
        return array_filter($arr, function($c) use(&$type) { return $c['type'] === $type; });
    }
    protected function getCategoryBySlug(Request $request, $slug) {
        $session = $request->getSession();
        $this->initCategories($session);
        $cats = $session->get('CategoryList');
        if (array_key_exists($slug, $cats)) {
            return $cats[$slug];
        } else {
            return null;
        }
    }
    protected function initSubcategories($session) {
        if (!$session->has('SubcategoryList')) {
            $subcats = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllOrderedByPosition();
            $subcategories  = array();
            $subcategoriesDict = array();
            foreach ($subcats as $s) {
                $sa = array(
                    'id' => $s->getId(),
                    'name' => $s->getName(),
                    'slug' => $s->getSlug(),
                    'imageUrl' => $s->getImage() !== null ? $s->getImage()->getUrlPath($this->getParameter('image_url_prefix')) : null
                );

                $subcategories[$s->getSlug()] = $sa;
                $cat = $s->getCategory();
                if (!array_key_exists($cat->getId(), $subcategoriesDict)) {
                    $subcategoriesDict[$cat->getId()] = array();
                }
                array_push($subcategoriesDict[$cat->getId()], $sa);
            }
            $session->set('SubcategoryList', $subcategories);
            $session->set('SubcategoryDict', $subcategoriesDict);
        }
    }
    protected function getSubcategories(Request $request, $categoryId = null) {
        $session = $request->getSession();
        $this->initSubcategories($session);
        if ($categoryId != null) {
            return $session->get('SubcategoryDict')[$categoryId];
        }
        else {
            return $session->get('SubcategoryList');
        }
    }
    protected function getSubcategoryBySlug(Request $request, $slug) {
        $session = $request->getSession();
        $this->initSubcategories($session);
        $cats = $session->get('SubcategoryList');
        if (array_key_exists($slug, $cats)) {
            return $cats[$slug];
        } else {
            return null;
        }
    }
    
    
}
