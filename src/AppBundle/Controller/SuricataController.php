<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\File;
class SuricataController extends Controller
{
	 /**
     * @Route("/suricata", name="suricata-homepage")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();	
       //$suricata= new Suricata();
       	$entidades=$em->getRepository('AppBundle:Suricata')->suricata_load_rules_map('/etc/nsm/rules/local.rules');
       	print_r($entidades);
        return $this->render('AppBundle:suricata:new.html.twig', array(
            'entities' => $entidades,
            'archivos' => $em->getRepository('AppBundle:Suricata')->readRules(),
        ));
    }

}