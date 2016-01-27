<?php

namespace AppBundle\Controller;

use AppBundle\Utils\SearchParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseController {
    
    /**
     * @Route("/", name="start-page")
     */
    public function indexAction(Request $request) {
        return $this->render('default/index.html.twig');
    }
    
     
    /**
     * @Route("/rentme/{token}", name="rentme")
     */
    public function rentmeAction(Request $request, $token=null) {      
        $cats = $this->getCategories($request);
        
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
            
            return $this->render('default/categorie.html.twig', array(
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
    private function processEquipment(Request $request, $content) {
        $eq = null;
        $pat = '^[[:digit:]]+/.+$';
        if (ereg($pat, $content)) {
            $arr = split('/', $content);
            $eq = $this->getDoctrineRepo('AppBundle:Equipment')->find(intval($arr[0]));
        }
        
        if ($eq == null) {
            throw $this->createNotFoundException();
        }
        
        // determine prev/next
        //<editor-fold>
        $session = $request->getSession();
        $prev = null;
        $next = null;
        if ($session->has('SearchList')) {
            $ids = $session->get('SearchList');
            $i = array_search($eq->getId(), $ids);
            if ($i !== null) {
                $repo = $this->getDoctrineRepo('AppBundle:Equipment');
                if ($i > 0) {
                    $prev = $repo->find($ids[$i - 1]);
                }
                if ($i < count($ids) - 1) {
                    $next = $repo->find($ids[$i + 1]);
                }        
            }
        }
        //</editor-fold>
        
        $subcat = $eq->getSubcategory();
        
        //$featureSections = $this->getDoctrineRepo('AppBundle:Equipment')->getEquipmentFeatures($eq->getId());
        $post = $this->getDoctrineRepo('AppBundle:Blog')->getPostForEquipmentPage();
                
        $equipments = $this->getDoctrineRepo('AppBundle:Equipment')->getSamplePreviewEquipmentsBySubcategory($eq->getSubcategory()->getId(), $eq->getId());
        $opinions = $this->getDoctrineRepo('AppBundle:EquipmentRating')->getAllSorted($eq->getId());
        
        if ($eq != null) {
            return $this->render('default/equipment.html.twig', array(
                'equipment' => $eq,
                'equipments' => $equipments,
                'category' => $subcat->getCategory(),
                'categories' => $this->getCategories($request),
                //'featureSections' => $featureSections,
                'next' => $next,
                'prev' => $prev,
                'post' => $post,
                'opinions' => $opinions
            ));
        }
        return null;
    }

    /**
     * @Route("/equipment-list", name="equipment-list")
     */ 
    public function equipmentListAction(Request $request) {
        $sp = $this->getSearchParams($request);
        $sp->updateFromRequest($request);
        
        $equipments = $this->getDoctrineRepo('AppBundle:Equipment')->getAll($sp);
        
        // store id list in session (for prev/next traversal)
        $ids = array();
        foreach ($equipments as $eq) {
            array_push($ids, $eq->getId());
        }
        $request->getSession()->set('SearchList', $ids);
        
        return $this->render('default/equipment-list.html.twig', array(
            'equipments' => $equipments
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
     * @Route("/subcats/{id}", name="subcat")
     */
    public function subcategoriesAction(Request $request, $id) {
        return new JsonResponse($this->getSubcategories($request, $id));
    }

    /**
     * @Route("/test")
     */
    public function testAction() {
        return new Response('ok');
    }
}