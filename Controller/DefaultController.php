<?php

namespace Querdos\QFileEncryptionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('QFileEncryptionBundle:Default:index.html.twig');
    }
}
