<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Equipment;
use AppBundle\Entity\ReportOffer;
use AppBundle\Entity\Talent;
use AppBundle\Entity\TariffType;
use AppBundle\Entity\Testimonial;
use AppBundle\Entity\User;
use AppBundle\Utils\SearchParams;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends BaseController {
    
    /**
     * @Route("/", name="start-page")
     */
    public function indexAction(Request $request) {
        
        $token = $request->query->get('token');
        
        $equipmentCats = $this->getCategoriesByType($request, Category::TYPE_EQUIPMENT);
        $talentCats = $this->getCategoriesByType($request, Category::TYPE_TALENT);
        
        //if param = 0 then get all from db
        $testimonials = $this->getDoctrineRepo("AppBundle:Testimonial")->getForMainPage(null);
        
        $confirmed= null;
        $confParam = $request->query->get('confirmed');
        if ($confParam != null){
            $confirmed = true;
        }
        
        $eqSc = $this->getDoctrineRepo("AppBundle:Equipment")->getShowcaseStart();
        $talSc = $this->getDoctrineRepo("AppBundle:Talent")->getShowcaseStart();
        $blogSc = $this->getDoctrineRepo("AppBundle:Blog")->getShowcaseStart();
        
        //newsletter message
        $session = $request->getSession();
        $newsletterMessage = null;
        if ($session->has('NewsletterMessage')) {
            $newsletterMessage = $session->get('NewsletterMessage');
            $session->remove('NewsletterMessage');
        }
        
        return $this->render('default/newStartPage.html.twig', array(
            'equipmentCategories' => $equipmentCats,
            'talentCategories' => $talentCats,
            'token' => $token,
            'confirmed' => $confirmed,
            'testimonials' => $testimonials,
            'newsletterMessage' => $newsletterMessage,
            'newsletterBar' => true,
            'equipmentSc' => $eqSc,
            'talentSc' => $talSc,
            'blogSc' => $blogSc
        ));
    }
    
    private function getShowcase() {
        $eqSc = $this->getDoctrineRepo("AppBundle:Equipment")->getShowcaseStart();
        $talSc = $this->getDoctrineRepo("AppBundle:Talent")->getShowcaseStart();
        return array_merge($eqSc, $talSc);
    }
    
    /**
     * @Route("/equipment/{token}", name="rentme")
     */
    public function rentmeAction(Request $request, $token=null) {      
        $cats = $this->getCategoriesByType($request, Category::TYPE_EQUIPMENT);
        
        
        //if param = 0 then get all from db
        $testimonials = $this->getDoctrineRepo("AppBundle:Testimonial")->getForMainPage(Testimonial::TYPE_EQUIPMENT);
        $eqSc = $this->getDoctrineRepo("AppBundle:Equipment")->getShowcaseEquipment();
        
        $confirmed= null;
        $confParam = $request->query->get('confirmed');
        if ($confParam != null){
            $confirmed = true;
        }
        
        return $this->render('default/equipment_mieten.html.twig', array(
            'categories' => $cats,
            'token' => $token,
            'confirmed' => $confirmed,
            'testimonials' => $testimonials,
            'equipmentSc' => $eqSc,
            'newsletterBar' => true
        ));
    }
    /**
     * @Route("/talente/{token}", name="bookme")
     */
    public function bookmeAction(Request $request, $token=null) {      
        $cats = $this->getCategoriesByType($request, Category::TYPE_TALENT);
        
        //if param = 0 then get all from db
        $testimonials = $this->getDoctrineRepo("AppBundle:Testimonial")->getForMainPage(Testimonial::TYPE_TALENT);
        $talSc = $this->getDoctrineRepo("AppBundle:Talent")->getShowcaseTalent();
        
        $confirmed= null;
        $confParam = $request->query->get('confirmed');
        if ($confParam != null){
            $confirmed = true;
        }
        
        return $this->render('default/talent_buchen.html.twig', array(
            'categories' => $cats,
            'token' => $token,
            'confirmed' => $confirmed,
            'testimonials' => $testimonials,
            'talentSc' => $talSc,
            'newsletterBar' => true
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
        return new Response(Response::HTTP_NOT_FOUND);
    }
    
    private function processCategory(Request $request, $content) {
        $cat = $this->getCategoryBySlug($request, $content);        
        $sp = $this->getSearchParams($request, $cat['type']);
        $sp->setCategoryId($cat['id']);
        $request->getSession()->set('SearchState', $sp);
        
        if ($cat != null) {
            //$equipments = $this->getDoctrineRepo('AppBundle:Equipment')->getAll($cat['id']);
            
            if ($cat['type'] === Category::TYPE_EQUIPMENT) {
                $tmpl = 'default/categorie.html.twig';
                $subcats = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllForEquipmentFilter($cat['id']);
            } else {
                $tmpl = 'default/categorie-talent.html.twig';
                $subcats = $this->getDoctrineRepo('AppBundle:Subcategory')->getAllForTalentFilter($cat['id']);
            }
            
            return $this->render($tmpl, array(
                'category' => $cat,
                'searchParams' => $sp,
                'newsletterBar' => true,
                'subcategories' => $subcats
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
            $eq = $this->getDoctrineRepo('AppBundle:Equipment')->getOne(intval($arr[0]));
        }
        if (ereg(DefaultController::RE_TALENT, $content)) {
            $arr = split('/', str_replace('T-', '', $content));
            $tal = $this->getDoctrineRepo('AppBundle:Talent')->getOne(intval($arr[0]));
        }
        
        if ($eq === null && $tal === null) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        
        
        
        return $this->displayItem($request, false, $eq, $tal);
    }
    
    /**
     * @Route("/preview/talent/{uuid}", name="preview_talent")
     */
    public function previewTalentAction(Request $request, $uuid) {
        $tal = $this->getDoctrineRepo('AppBundle:Talent')->getOneByUuid($uuid);
        if ($tal === null) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        return $this->displayItem($request, true, null, $tal);
    }
    
    /**
     * @Route("/preview/equipment/{uuid}", name="preview_equipment")
     */
    public function previewEquipmentAction(Request $request, $uuid) {
        $eq = $this->getDoctrineRepo('AppBundle:Equipment')->getOneByUuid($uuid);
        if ($eq === null) {
            return new Response(Response::HTTP_NOT_FOUND);
        }
        return $this->displayItem($request, true, $eq, null);
    }
    
    private function displayItem(Request $request, $isPreview, $eq = null, $tal = null){
        
        $loggedIn = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED'); // user logged in
        // determine prev/next
        //<editor-fold>
        if ($eq !== null) {
            if (!$isPreview && ($eq->getStatus() !== Equipment::STATUS_APPROVED || $eq->getUser()->getStatus() !== User::STATUS_OK)){
                return new Response(Response::HTTP_NOT_FOUND);
            }
            
            $repo = 'AppBundle:Equipment';
            $ratRepo = 'AppBundle:EquipmentRating';
            $tmpl = 'default/equipment.html.twig';
            $subcat = $eq->getSubcategories()[0];
            $id = $eq->getId();
            $type = ReportOffer::OFFER_TYPE_EQUIPMENT;
        }
        else {
            if (!$isPreview && ($tal->getStatus() !== Talent::STATUS_APPROVED || $tal->getUser()->getStatus() !== User::STATUS_OK)){
                return new Response(Response::HTTP_NOT_FOUND);
            }
            $repo = 'AppBundle:Talent';
            $ratRepo = 'AppBundle:TalentRating';
            $tmpl = 'default/talent.html.twig';
            $subcat = $tal->getSubcategories()[0];
            $id = $tal->getId();
            $type = ReportOffer::OFFER_TYPE_TALENT;
        }
                
        $prev = null;
        $next = null;
        if (!$isPreview) {
            $cat = $subcat->getCategory();
            $ids = $this->getSessionSearchList($request, $repo, $cat->getId(), $cat->getType());
            if (count($ids) > 1){
                $i = array_search($id, $ids);
                if ($i !== null) {            
                    $itemRepo = $repo = $this->getDoctrineRepo($repo);
                    if ($i > 0) {
                        $prev = $itemRepo->find($ids[$i - 1]);
                    } else {
                        $prev = $itemRepo->find($ids[count($ids) - 1]);
                    }

                    if ($i < count($ids) - 1) {
                        $next = $itemRepo->find($ids[$i + 1]);
                    } else {
                        $next = $itemRepo->find($ids[0]);
                    }
                }
            }
        }
        //</editor-fold>
        
        $tariffs = null;
        if ($tal != null)
            $tariffs = $this->getDoctrineRepo('AppBundle:TalentTariff')->getTariffsForTalent($tal->getId());
        
        //$featureSections = $this->getDoctrineRepo('AppBundle:Equipment')->getEquipmentFeatures($eq->getId());
        $post = $this->getDoctrineRepo('AppBundle:Blog')->getPostForEquipmentPage();
                
        //$equipments = $this->getDoctrineRepo('AppBundle:Equipment')->getSamplePreviewEquipmentsBySubcategory($eq->getSubcategory()->getId(), $eq->getId());
        $opinions = $this->getDoctrineRepo($ratRepo)->getAllSorted($id);
        
        return $this->render($tmpl, array(
            'item' => $eq !== null ? $eq : $tal,
            'tariffs' => $tariffs,
            /*'equipments' => $equipments,*/
            'category' => $subcat->getCategory(),
            'categories' => $this->getCategories($request),
            //'featureSections' => $featureSections,
            'next' => $next,
            'prev' => $prev,
            'post' => $post,
            'opinions' => $opinions,
            'type' => $type,
            'loggedIn' => $loggedIn,
            'isPreview' => $isPreview,
            'newsletterBar' => true
        ));
    }
    
    private function createSessionSearchList(Request $request, $items) {
        $ids = array();
        foreach ($items as $item)
            array_push($ids, $item->getId());

        $session = $request->getSession();
        $session->set('SearchList', $ids);        
    }
    
    private function getSessionSearchList(Request $request, $repo, $catId, $type) {
        $session = $request->getSession();
        if (!$session->has('SearchList')) {
            $sp = $this->getSearchParams($request, $type);            
            if (!$sp->getCategoryId()){
                $sp->setCategoryId($catId);
            }
            $items = $this->getDoctrineRepo($repo)->getAll($sp);
            $this->createSessionSearchList($request, $items);
        }
        return $session->get('SearchList');
    }

    /**
     * @Route("/equipment-list", name="equipment-list")
     */ 
    public function itemListAction(Request $request) {        
        $type = intval($request->get('type'));
        $sp = $this->getSearchParams($request, $type);
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
        $this->createSessionSearchList($request, $items);
        
        return $this->render($tmpl, array(
            'items' => $items
        ));
    }
    
    
    public function renderEquipmentListAction(Request $request, $equipments) {
        
        return $this->render('default/equipment-list-small.html.twig', array(
            'equipments' => $equipments
        ));
    }
    
    private function getSearchParams(Request $request, $type) {
        $session = $request->getSession();
        $name = $type == Category::TYPE_EQUIPMENT ? "Equipment_SearchParams" : "Talent_SearchParams";
        
        if ($session->has($name)) {
            $sp = $session->get($name);
        }
        else {
            $sp = new SearchParams();
            $session->set($name, $sp);
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