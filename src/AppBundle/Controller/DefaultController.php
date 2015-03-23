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

         //echo $proceder.$resultado;
         return $this->render('default/result.html.twig', array(
           'resultado'      => $proceder.$resultado;,               
            ));
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

        return $this->render('default/result.html.twig', array(
           'resultado'      => $proceder.$resultado;,               
            ));
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

        return $this->render('default/result.html.twig', array(
           'resultado'      => $proceder.$resultado;,               
            ));
    }

}
