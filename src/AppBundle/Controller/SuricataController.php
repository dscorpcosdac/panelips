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
        $archivo=$this->get('request')->request->get('archivo', '/etc/nsm/rules/local.rules');
        $sinnada=str_replace('/etc/nsm/rules/', '', $archivo);
       //$suricata= new Suricata();
       	$entidades=$em->getRepository('AppBundle:Suricata')->suricata_load_rules_map($archivo);
       //echo '<pre>';	print_r($entidades); echo '</pre>';
       $procesoent=$em->getRepository('AppBundle:Suricata')->rulestoArray($entidades, $sinnada);
      // echo '<pre>';	print_r($procesoent); echo '</pre>';
        return $this->render('AppBundle:suricata:index.html.twig', array(
            'entities' => $procesoent['datos'],
            'enabled' => $procesoent['enabled'],
            'disabled' => $procesoent['disabled'],
            'totalrules' => count($procesoent['datos']),
            'archivos' => $em->getRepository('AppBundle:Suricata')->readRules(),
        ));
    }

}