<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/restartips", name="restartips")
     */
    public function restartipsAction()
    {
        $resultado=shell_exec('shorrewall clear');
        return $this->render('default/result.html.twig', array(
            'resultado'      => $resultado,
           
        ));
    }

}
