<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseAdminController {
    
    /**
     * 
     * @Route("/admin", name="admin_area")
     * @Route("/admin/", name="admin_area")
     */
    public function indexAction() {
        return $this->render('admin/default/index.html.twig');
    }
}
