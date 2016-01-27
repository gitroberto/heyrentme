<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


class SchedulerController extends BaseController {
    
    /**
     * @Route("/6pGmCndfSy9Km6uV/4UEXNQxRstDqB65Q", name="scheduler-run")
     */
    public function runAction() {
        $this->get("scheduler")->run();
        return new Response(Response::HTTP_OK);
    }
}
