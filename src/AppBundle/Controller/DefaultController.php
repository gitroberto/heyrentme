<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Utils\SearchParams;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController {
    
    /**
     * @Route("/", name="start-page")
     */
    public function indexAction(Request $request) {
        return $this->render('default/index.html.twig');
    }
    
     
    /**
     * @Route("/equipment/{token}", name="rentme")
     */
    public function rentmeAction(Request $request, $token=null) {      
        $cats = $this->getCategoriesByType($request, Category::TYPE_EQUIPMENT);
        
        //if param = 0 then get all from db
        $testimonials = $this->getDoctrineRepo("AppBundle:Testimonial")->getForMainPage(3);
        
        $confirmed= null;
        $confParam = $request->query->get('confirmed');
        if ($confParam != null){
            $confirmed = true;
        }
        
        return $this->render('default/equipment_mieten.html.twig', array(
            'categories' => $cats,
            'token' => $token,
            'confirmed' => $confirmed,
            'testimonials' => $testimonials
        ));
    }
    /**
     * @Route("/talente/{token}", name="bookme")
     */
    public function bookmeAction(Request $request, $token=null) {      
        $cats = $this->getCategoriesByType($request, Category::TYPE_TALENT);
        
        //if param = 0 then get all from db
        $testimonials = $this->getDoctrineRepo("AppBundle:Testimonial")->getForMainPage(3);
        
        $confirmed= null;
        $confParam = $request->query->get('confirmed');
        if ($confParam != null){
            $confirmed = true;
        }
        
        return $this->render('default/talent_buchen.html.twig', array(
            'categories' => $cats,
            'token' => $token,
            'confirmed' => $confirmed,
            'testimonials' => $testimonials
        ));
    }
    
        
    public function catchallAction(Request $request, $content) {
        /*
         * This controller tries to catch by url (in order):
         * - Category
         * - Subcategory
         * - Offered item
         * 
         * 2015-12-10: Subcategory gets suspended until further notice.
         */
        
        // Category
        $result = $this->processCategory($request, $content);
        if ($result != null) {
            return $result;
        }
        /* suspended
        // Subcategory
        $result = $this->processSubcategory($request, $content);
        if ($result != null) {
            return $result;
        }
        */      
        // Equipment
        $result = $this->processEquipment($request, $content);
        if ($result != null) {
            return $result;
        }
        // Nothing was matched, URL is invalid
        throw $this->createNotFoundException();
    }
    
    private function processCategory(Request $request, $content) {
        $cat = $this->getCategoryBySlug($request, $content);
        $sp = $this->getSearchParams($request);
        $sp->setCategoryId($cat['id']);
        $request->getSession()->set('SearchState', $sp);
        
        if ($cat != null) {
            //$equipments = $this->getDoctrineRepo('AppBundle:Equipment')->getAll($cat['id']);
            
            if ($cat['type'] === Category::TYPE_EQUIPMENT) {
                $tmpl = 'default/categorie.html.twig';
            } else {
                $tmpl = 'default/categorie-talent.html.twig';
            }

            return $this->render($tmpl, array(
                'category' => $cat,
                'searchParams' => $sp
                //'equipments' => $equipments
            ));
        }
        return null;
    }
    private function processSubcategory(Request $request, $content) {
        $subcat = $this->getSubcategoryBySlug($request, $content);
        
        if ($subcat != null) {            
            $equipments = $this->getDoctrineRepo('AppBundle:Equipment')->getAllBySubcategory($subcat->getId());
            
            return $this->render('default/categorie.html.twig', array(
                'subcategory' => $subcat,
                'equipments' => $equipments
            ));
        }
        return null;
    }

    const RE_EQUIPMENT = '^E-[[:digit:]]+/.+$';
    const RE_TALENT = '^T-[[:digit:]]+/.+$';
    
    private function processEquipment(Request $request, $content) {
        $eq = null;
        $tal = null;
        if (ereg(DefaultController::RE_EQUIPMENT, $content)) {
            $arr = split('/', str_replace('E-', '', $content));
            $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find(intval($arr[0]));
        }
        if (ereg(DefaultController::RE_TALENT, $content)) {
            $arr = split('/', str_replace('T-', '', $content));
            $tal = $this->getDoctrineRepo('AppBundle:Talent')->find(intval($arr[0]));
        }
        
        if ($eq === null && $tal === null) {
            throw $this->createNotFoundException();
        }
        
        // determine prev/next
        //<editor-fold>
        if ($eq !== null) {
            $repo = 'AppBundle:Equipment';
            $ratRepo = 'AppBundle:EquipmentRating';
            $tmpl = 'default/equipment.html.twig';
            $subcat = $eq->getSubcategory();
            $id = $eq->getId();
        }
        else {
            $repo = 'AppBundle:Talent';
            $ratRepo = 'AppBundle:TalentRating';
            $tmpl = 'default/talent.html.twig';
            $subcat = $tal->getSubcategory();
            $id = $tal->getId();
        }
        
        $session = $request->getSession();
        $prev = null;
        $next = null;
        if ($session->has('SearchList')) {
            $ids = $session->get('SearchList');
            $i = array_search($id, $ids);
            if ($i !== null) {
                $repo = $this->getDoctrineRepo($repo);
                if ($i > 0) {
                    $prev = $repo->find($ids[$i - 1]);
                }
                if ($i < count($ids) - 1) {
                    $next = $repo->find($ids[$i + 1]);
                }        
            }
        }
        //</editor-fold>
        
        
        //$featureSections = $this->getDoctrineRepo('AppBundle:Equipment')->getEquipmentFeatures($eq->getId());
        $post = $this->getDoctrineRepo('AppBundle:Blog')->getPostForEquipmentPage();
                
        //$equipments = $this->getDoctrineRepo('AppBundle:Equipment')->getSamplePreviewEquipmentsBySubcategory($eq->getSubcategory()->getId(), $eq->getId());
        $opinions = $this->getDoctrineRepo($ratRepo)->getAllSorted($id);
        
        return $this->render($tmpl, array(
            'item' => $eq !== null ? $eq : $tal,
            /*'equipments' => $equipments,*/
            'category' => $subcat->getCategory(),
            'categories' => $this->getCategories($request),
            //'featureSections' => $featureSections,
            'next' => $next,
            'prev' => $prev,
            'post' => $post,
            'opinions' => $opinions
        ));
    }

    /**
     * @Route("/equipment-list", name="equipment-list")
     */ 
    public function itemListAction(Request $request, $type) {
        $sp = $this->getSearchParams($request);
        $sp->updateFromRequest($request);
        
        if ($type === Category::TYPE_EQUIPMENT) {
            $items = $this->getDoctrineRepo('AppBundle:Equipment')->getAll($sp);
            $tmpl = 'default/equipment-list.html.twig';
        }
        else {
            $items = $this->getDoctrineRepo('AppBundle:Talent')->getAll($sp);
            $tmpl = 'default/talent-list.html.twig';
        }
        
        // store id list in session (for prev/next traversal)
        $ids = array();
        foreach ($items as $item) {
            array_push($ids, $item->getId());
        }
        $request->getSession()->set('SearchList', $ids);
        
        return $this->render($tmpl, array(
            'items' => $items
        ));
    }
    
    
    public function renderEquipmentListAction(Request $request, $equipments) {
        
        return $this->render('default/equipment-list-small.html.twig', array(
            'equipments' => $equipments
        ));
    }
    
    private function getSearchParams(Request $request) {
        $session = $request->getSession();
        if ($session->has('SearchParams')) {
            $sp = $session->get('SearchParams');
        }
        else {
            $sp = new SearchParams();
            $session->set('SearchParams', $sp);
        }
        return $sp;
    }
    
    /** 
     * @Route("/cats/{type}", name="cat")
     */
    public function categoriesAction(Request $request, $type) {
        return new JsonResponse($this->getCategoriesByType($request, intval($type)));
    }
    /**
     * @Route("/subcats/{id}", name="subcat")
     */
    public function subcategoriesAction(Request $request, $id) {
        return new JsonResponse($this->getSubcategories($request, $id));
    }

    /**
     * @Route("/test")
     */
    public function testAction(Request $request) {
        $request->attributes->set('_locale', 'de');
        $dt = new DateTime();
        return $this->render('default/test.html.twig', array('dt' => $dt));
        
    }
}