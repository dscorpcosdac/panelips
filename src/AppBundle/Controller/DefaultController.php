<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/startips", name="startips")
     */
    public function startAction()
    {
        //$resultado=shell_exec('shorewall start');//echo  "hola" | sudo shorewall start
        $proceder=shell_exec('sudo /usr/sbin/nsm_sensor_ips-start');
        $pos = strpos($proceder, 'ERROR');
        if ($pos === false) {
            $resultado=shell_exec('sudo shorewall start');
            //$result2=shell_exec('sudo /usr/sbin/nsm_sensor_ips-start');

        } else {
            $resultado=$proceder;
        }
         $body=$this->renderView('default/result.html.twig', array(
           'resultado'      => $proceder.$resultado,               
            ));
         //echo $proceder.$resultado;
           $res=json_encode(array('funciono' =>true,'elcont'=> $body));
        $response = new Response($res);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

     /**
     * @Route("/stopips", name="stopips")
     */
    public function stopAction()
    {
        //$resultado=shell_exec('shorewall start');//echo  "hola" | sudo shorewall start
        $proceder=shell_exec('sudo shorewall check');
        $pos = strpos($proceder, 'ERROR');
        if ($pos === false) {
            $result1=shell_exec('sudo shorewall clear');
            $result2=shell_exec('sudo /usr/sbin/nsm_sensor_ips-stop');
             $resultado=$result1.$result2;

        } else {
            $resultado=$proceder;
        }
        $body=$this->renderView('default/result.html.twig', array(
           'resultado'      => $proceder.$resultado,               
            ));
          $res=json_encode(array('funciono' =>true,'elcont'=> $body));
        $response = new Response($res);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/restarsh", name="restarSh")
     */
    public function restarshAction()
    {
        //$resultado=shell_exec('shorewall start');//echo  "hola" | sudo shorewall start
        $proceder=shell_exec('sudo shorewall check');
        $pos = strpos($proceder, 'ERROR');
        if ($pos === false) {
            $resultado=shell_exec('sudo shorewall restart');
           // $result2=shell_exec('sudo /usr/sbin/nsm_sensor_ips-start');

        } else {
            $resultado=$proceder;
        }

        $body=$this->renderView('default/result.html.twig', array(
           'resultado'      => $proceder.$resultado,               
            ));
         $res=json_encode(array('funciono' =>true,'elcont'=> $body));
        $response = new Response($res);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
