<?php

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StaticController extends BaseController {
    
    #FAQ 
    /**
     * @Route("/breadcrumb/{pageName}", name="breadcrumb")
     */
    public function breadcrumbAction(Request $request, $pageName) {               
        return $this->render('static/faq/breadcrumb.html.twig', array('pageName' => $pageName));
    }
    
    /**
     * @Route("/leftmenu/{pageName}", name="leftmenu")
     */
    public function leftmenuAction(Request $request, $pageName) {               
        return $this->render('static/faq/left_menu.html.twig', array('pageName' => $pageName));
    }
    
    
    /**
     * @Route("/hilfe-faq/equipment-mieten", name="hilfe_faq_euipment_mieten")
     * @Route("/hilfe-faq/")
     * @Route("/hilfe-faq")
     * @Route("/hilfe/")
     * @Route("/hilfe")
     * @Route("/faq/")
     * @Route("/faq")
     */
    public function hilfeFaqEuipmentMietenAction(Request $request) {               
        return $this->render('static/faq/hilfe_faq_euipment_mieten.html.twig');
    }
    
    /**
     * @Route("/hilfe-faq/equipment-anbieten", name="hilfe_faq_euipment_anbieten")
     */
    public function hilfeFaqEuipmentAnbietenAction(Request $request) {               
        return $this->render('static/faq/hilfe_faq_euipment_anbieten.html.twig');
    }
    
    /**
     * @Route("/hilfe-faq/talent-buchen", name="hilfe_faq_talent_buchen")
     */
    public function hilfeFaqTalentBuchenAction(Request $request) {               
        return $this->render('static/faq/hilfe_faq_talent_buchen.html.twig');
    }
    
    /**
     * @Route("/hilfe-faq/talent-anbieten", name="hilfe_faq_talent_anbieten")
     */
    public function hilfeFaqTalentAnbietenAction(Request $request) {               
        return $this->render('static/faq/hilfe_faq_talent_anbieten.html.twig');
    }
    
    
    /**
     * @Route("/hilfe-faq/faq", name="hilfe_faq_faq")
     */
    public function hilfeFaqFaqAnbieter(Request $request) {               
        return $this->render('static/faq/hilfe_faq_faq.html.twig');
    }    

    #ClientInfo
    /**
     * @Route("/clientInfoBreadcrumb/{pageName}/{pageNameShort}", name="client_info_breadcrumb")
     */
    public function clientInfoBreadcrumbAction(Request $request, $pageName, $pageNameShort) {               
        return $this->render('static/clientInfo/client_info_breadcrumb.html.twig', array('pageName' => $pageName, 'pageNameShort' => $pageNameShort));
    }
    
    /**
     * @Route("/clientInfoLeftmenu/{pageName}", name="client_info_leftmenu")
     */
    public function clientInfoLeftmenuAction(Request $request, $pageName) {               
        return $this->render('static/clientInfo/client_info_leftmenu.html.twig', array('pageName' => $pageName));
    }
    
    
    /**
     * @Route("/kundeninfos/agb", name="kundeninfos_agb")
     * @Route("/kundeninfos")
     * @Route("/kundeninfos/")
     */
    public function kundeninfosAgbAction(Request $request) {               
        return $this->render('static/clientInfo/kundeninfos_agb.html.twig');
    }
    
    /**
     * @Route("/kundeninfos/presse", name="kundeninfos_presse")
     */
    public function kundeninfosPresseAgbAction(Request $request) {               
        return $this->render('static/clientInfo/kundeninfos_presse.html.twig');
    }
    
    /**
     * @Route("/kundeninfos/kontakt", name="kundeninfos_kontakt")
     */
    public function kundeninfosKontaktAction(Request $request) {               
        return $this->render('static/clientInfo/kundeninfos_kontakt.html.twig');
    }
    
    /**
     * @Route("/kundeninfos/impressum", name="kundeninfos_impressum")
     */
    public function kundeninfosImpressumAction(Request $request) {               
        return $this->render('static/clientInfo/kundeninfos_impressum.html.twig');
    }
    
    /** 
     * @Route("/tutorial/photo", name="tutorial-photo")
     */
    public function photoTutorialAction() {
        return $this->render('static/tutorial/photo.html.twig');
    }

    /** 
     * @Route("/tutorial/video", name="tutorial-video")
     */
    public function videoTutorialAction() {
        return $this->render('static/tutorial/video.html.twig');
    }
}
