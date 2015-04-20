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
       	$rules=$em->getRepository('AppBundle:Suricata')->suricata_load_rules_map(__DIR__.'/../../../web/rules/elbueno.txt');
       //echo '<pre>';	print_r($entidades); echo '</pre>';
       $procesoent=$em->getRepository('AppBundle:Suricata')->rulestoArray($entidades, $rules[1]);
      // echo '<pre>';	print_r($procesoent); echo '</pre>';
        return $this->render('AppBundle:suricata:index.html.twig', array(
            'entities' => $procesoent['datos'],
            'archivo' => $archivo,
            'sinnada' => $sinnada,
            'enabled' => $procesoent['enabled'],
            'disabled' => $procesoent['disabled'],
            'totalrules' => count($procesoent['datos']),
            'archivos' => $em->getRepository('AppBundle:Suricata')->readRules(),
        ));
    }

    /**
     * @Route("/ruleActive", name="Actuve_Rule")
     */
    public function ruleActiveAction()
    {
    	ini_set('display_erros', -1);
    	//echo __DIR__.'/../../../web/rules/enabled.txt';
    	$em = $this->getDoctrine()->getManager();	
    	$rule=$this->get('request')->request->get('rule', '');
    	$oper=$this->get('request')->request->get('oper', 1);
    	//$enabled=$em->getRepository('AppBundle:Suricata')->suricata_load_rules_map(__DIR__.'/../../../web/rules/enabled.txt');
    	//echo "<br>";print_r($enabled);echo "</pre>";

    	//$disabled=$em->getRepository('AppBundle:Suricata')->suricata_load_rules_map(__DIR__.'/../../../rules/disabled.txt');
    	//if($rule==''){
    	$rule=base64_decode($rule);
    	$rule=str_replace('#', '', $rule);
    	if($oper==1){
    		$activoArchive=__DIR__.'/../../../web/rules/enabled.txt';
    		$inactivoArchivo= __DIR__.'/../../../web/rules/disabled.txt';
    	}else{
    		$activoArchive= __DIR__.'/../../../web/rules/disabled.txt';
    		$inactivoArchivo=__DIR__.'/../../../web/rules/enabled.txt';
    	}
    		$archivo = $activoArchive;
            $abrir = fopen($archivo,'r+');
            $contenido = fread($abrir,filesize($archivo));
            fclose($abrir);        
            $contenido = explode("\n",$contenido);
            $i=$x=0;
	    	foreach ($contenido as $key ) {
	    		if($key==$rule){
	    			$x++;
	    		}
	    		
	    	}
	    	if($x==0){
	    		$file = fopen($archivo, "a");
		        fwrite($file, $rule . PHP_EOL);
		        fclose($file);
	    	}

	    	$archivo =$inactivoArchivo;
            $abrir = fopen($archivo,'r+');
            $contenido = fread($abrir,filesize($archivo));
            fclose($abrir);        
            $contenido = explode("\n",$contenido);
            $i=$x=0;
	    	foreach ($contenido as $key ) {
	    		if($key==$rule){
	    			$x=$i;
	    		}
	    		$i++;
	    	}
	    	if($x>0){
	    		unset($contenido[$x]);
	            $b = array_values($contenido);
	            $otro = implode("\n",$b); 
	            // Guardar Archivo
	            $abrir = fopen($archivo,'w');
	            fwrite($abrir,$otro);
	            fclose($abrir);
	    	}

	    	//unset($contenido[$puntero]);

	          
         $response = new Response(json_encode(array('funciono'=>true)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
      /*  }
        $response = new Response(json_encode(array('funciono'=>false)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;*/
    }
    /**
     * @Route("/applyChanges", name="Apply_Changes")
     */
    public function applyChangesAction()
    {
    	ini_set('display_erros', -1);
    	//echo __DIR__.'/../../../web/rules/enabled.txt';
    	$em = $this->getDoctrine()->getManager();	
    	//$rule=$this->get('request')->request->get('rule', 'pass tcp any any <> any any (content:"google.com"; msg:"GOOGLE"; sid:100006; rev:1;)');
    	//$oper=$this->get('request')->request->get('oper', 1);
    	//$enabled=$em->getRepository('AppBundle:Suricata')->suricata_load_rules_map(__DIR__.'/../../../web/rules/enabled.txt');
    	//echo "<br>";print_r($enabled);echo "</pre>";

    	//$disabled=$em->getRepository('AppBundle:Suricata')->suricata_load_rules_map(__DIR__.'/../../../rules/disabled.txt');
    		$activoArchive=__DIR__.'/../../../web/rules/enabled.txt';
    		$inactivoArchivo= __DIR__.'/../../../web/rules/disabled.txt';
    		$elbueno= __DIR__.'/../../../web/rules/elbueno.txt';

    		$tactivos=fopen($elbueno,'r+');
            $rules = fread($tactivos,filesize($elbueno));
            fclose($tactivos);        
            $rules = explode("\n",$rules);

    	
    		$archivo = $inactivoArchivo;
            $abrir = fopen($archivo,'r+');
            $contenido = fread($abrir,filesize($archivo));
            fclose($abrir);        
            $contenido = explode("\n",$contenido);
            $i=$x=0;            
	    	foreach ($contenido as $key ) {
	    		$Prules=array();
	    		foreach ($rules as $rule ) {
		    		if($key != $rule){
		    			$Prules[]=$rule;
		    		}
		    	}
	    		$rules=$Prules;
	    	}

	    	$archivo =$activoArchive;
            $abrir = fopen($archivo,'r+');
            $contenido = fread($abrir,filesize($archivo));
            fclose($abrir);        
            $contenido = explode("\n",$contenido);
            $i=$x=0;
            
	    	foreach ($contenido as $key ) {
	    		$Nrules=array();$x=0;
	    		foreach ($rules as $rule ) {
		    		if($key!=$rule){
		    			$Nrules[]=$rule;
		    		}
	    		}
	    			$Nrules[]=$key;
	    		
	    		$rules=$Nrules;
	    		//print_r($rules);
	    	}
	    	
	    	$b = array_values($rules);
            $otro = implode("\n",$b); 
            // Guardar Archivo
            $abrir = fopen($elbueno,'w');
            fwrite($abrir,$otro);
            fclose($abrir);
	    	//unset($contenido[$puntero]);

	          
            $response = new Response(json_encode(array('funciono'=>true)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
    }

        /**
     * @Route("/ruleAdd", name="rule_add")
     */
    public function ruleAddAction()
    {
    	  	    	//unset($contenido[$puntero]);
 
        return $this->render('AppBundle:suricata:new.html.twig', array(
            
        ));
    }
     /**
     * @Route("/ruleCreate", name="rule_create")
     */
    public function ruleCreateAction()
    {
        $rule=$this->get('request')->request->get('rule', '');
        $file=$this->get('request')->request->get('file', '');
       
            $archivo = $file;
            $abrir = fopen($archivo,'r+');
           
            if($rule!=''){
                $file = fopen($archivo, "a");
                fwrite($file, $rule . PHP_EOL);
                fclose($file);
            }
 
        return $this->redirect($this->generateUrl('suricata-homepage'));
        
    }

      /**
     * @Route("/ruleEdit", name="rule_edit")
     */
    public function ruleEditAction()
    {
             $rule=$this->get('request')->request->get('rule', '');
        $file=$this->get('request')->request->get('file', '');  
 echo base64_decode($rule);
        return $this->render('AppBundle:suricata:edit.html.twig', array(
           'file'=>$file,
           'rule'=>trim(base64_decode($rule)), 
        ));
    }

     /**
     * @Route("/ruleUpdate", name="rule_update")
     */
    public function ruleUpdateAction()
    {
        $rule=$this->get('request')->request->get('rule', '');
        $file=$this->get('request')->request->get('file', '');       
        $archivo = '/etc/nsm/rules/'.$file;
            $abrir = fopen($archivo,'r+');
            $contenido = fread($abrir,filesize($archivo));
            fclose($abrir);        
            $contenido = explode("\n",$contenido);
            $i=$x=0;
            foreach ($contenido as $key ) {
                if($key==$rule){
                    $x++;
                }
                
            }
            if($x==0){
                $file = fopen($archivo, "a");
                fwrite($file, $rule . PHP_EOL);
                fclose($file);
            }
        return $this->redirect($this->generateUrl('suricata-homepage'));
    }


}