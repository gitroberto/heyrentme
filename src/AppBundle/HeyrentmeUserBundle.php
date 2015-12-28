<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeyrentmeUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}