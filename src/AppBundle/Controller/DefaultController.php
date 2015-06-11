<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\File;
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $contenido='';
        $archivo = '/etc/warriorsips/ips_ip';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
        //print_r($contenido );
        for($i=0;$i<count($contenido);$i++){   
            if($contenido[$i]!=''){         
                $macsips[]=array('ip'=>$contenido[$i],'id'=>$i.'_ip');
              }
        }

        $contenido='';
        $archivo = '/etc/warriorsips/ips_mac';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
       // print_r($contenido );
        for($i=0;$i<count($contenido);$i++){ 
              if($contenido[$i]!=''){            
                $macsips[]=array('mac'=>$contenido[$i],'id'=>$i.'_mac');
              }
        }

        $contenido='';
        $archivo = '/etc/warriorsips/ips_mac_ip';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
        for($i=0;$i<count($contenido);$i++){
            $datos=explode('  ', $contenido[$i]);
            if(count($datos)>1){
              $macsips[]=array('ip'=>$datos[1],'mac'=>$datos[0],'id'=>$i.'_macip');
            }
        }

        $contenido='';
        $archivo = '/etc/warriorsips/ips_ecep';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
        for($i=0;$i<count($contenido);$i++){
            //$datos=explode('  ', $contenido[$i]);
            if($contenido !=''){
              $ips_ecep[]=array('ip'=>$contenido,'id'=>$i.'_esep');
            }
        }

        return $this->render('default/index.html.twig', array(
            'entities' => $macsips,
            'esepciones'=>$ips_ecep,
        ));
    }

    /**
     * @Route("/startips", name="startips")
     */
    public function startAction()
    {
        //$resultado=shell_exec('shorewall start');//echo  "hola" | sudo shorewall start 
        $proceder=shell_exec('sudo sh /etc/warriorsips/ips-start.sh');
        $pos = strpos($proceder, 'ERROR');
        $resultado='';
       /* if ($pos === false) {
            $resultado=shell_exec('sudo shorewall start');
            //$result2=shell_exec('sudo /usr/sbin/nsm_sensor_ips-start');

        } else {
            $resultado=$proceder;
        }*/
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
        $proceder=shell_exec('sudo sh /etc/warriorsips/ips-stop.sh');
        $pos = strpos($proceder, 'ERROR');
        $resultado='';
       /* if ($pos === false) {
            $result1=shell_exec('sudo shorewall clear');
            $result2=shell_exec('sudo /usr/sbin/nsm_sensor_ps-stop');
             $resultado=$result1.$result2;

        } else {
            $resultado=$proceder;
        }*/
        $body=$this->renderView('default/result.html.twig', array(
           'resultado'      => $proceder.$resultado,               
            ));
          $res=json_encode(array('funciono' =>true,'elcont'=> $body));
        $response = new Response($res);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

     /**
     * @Route("/applyips", name="applyips")
     */
    public function applyipsAction()
    {
        //$resultado=shell_exec('shorewall start');//echo  "hola" | sudo shorewall start
        $proceder=shell_exec('sudo sh /etc/warriorsips/ips-applych.sh');
        $pos = strpos($proceder, 'ERROR');
        $resultado='';
       /* if ($pos === false) {
            $result1=shell_exec('sudo shorewall clear');
            $result2=shell_exec('sudo /usr/sbin/nsm_sensor_ps-stop');
             $resultado=$result1.$result2;

        } else {
            $resultado=$proceder;
        }*/
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
        $proceder=shell_exec('sudo sh /etc/warriorsips/ips-restart.sh');
        $pos = strpos($proceder, 'ERROR');
        $resultado='';
       /* if ($pos === false) {
            $resultado=shell_exec('sudo shorewall restart');
           // $resultado=shell_exec('sudo /usr/sbin/nsm_sensor_ips-restart');

        } else {
            $resultado=$proceder;
        }*/

        $body=$this->renderView('default/result.html.twig', array(
           'resultado'      => $proceder.$resultado,               
            ));
         $res=json_encode(array('funciono' =>true,'elcont'=> $body));
        $response = new Response($res);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/maclist", name="maclist")
     */
    public function maclistAction()
    {
    /*
        $file = fopen("/etc/shorewall/maclist", "r") or exit("Unable to open file!");
        //Output a line of the file until the end is reached
        $i=0;
        while(!feof($file))
        {
            if($i>9){
                $datos=explode('  ', fgets($file));
                $todo[]=$datos;
            }else{
                fgets($file);
            }
            $i++;
           // echo fgets($file).'|'. $i."<br />";
        }
        fclose($file);
       */
        $contenido='';
        $archivo = '/etc/warriorsips/ips_ip';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
        //print_r($contenido );
        for($i=0;$i<count($contenido);$i++){   
            if($contenido[$i]!=''){         
                $macsips[]=array('ip'=>$contenido[$i],'id'=>$i.'_ip');
              }
        }

        $contenido='';
        $archivo = '/etc/warriorsips/ips_mac';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
       // print_r($contenido );
        for($i=0;$i<count($contenido);$i++){ 
              if($contenido[$i]!=''){            
                $macsips[]=array('mac'=>$contenido[$i],'id'=>$i.'_mac');
              }
        }

        $contenido='';
        $archivo = '/etc/warriorsips/ips_mac_ip';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
        for($i=0;$i<count($contenido);$i++){
            $datos=explode('  ', $contenido[$i]);
            if(count($datos)>1){
              $macsips[]=array('ip'=>$datos[1],'mac'=>$datos[0],'id'=>$i.'_macip');
            }
        }
        $contenido='';
        $archivo = '/etc/warriorsips/ips_ecep';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
        for($i=0;$i<count($contenido);$i++){
            //$datos=explode('  ', $contenido[$i]);
            if($contenido !=''){
              $ips_ecep[]=array('ip'=>$contenido,'id'=>$i.'_esep');
            }
        }
        //array_push($todo, $ips, $macs,$macsips);
        $entity  = new File();
        $form = $this->createFormBuilder($entity)
        ->setAction($this->generateUrl('maclist_import'))
        /*->add('nombre', 'choice', array(
            'choices'   => array('1' => 'Reemplazar todo', '2' => 'Al final del archivo', '3' => 'Al inicio del archivo')
            
        ))*/
        ->add('file')
        ->getForm();
        return $this->render('AppBundle:Etiqueta:index.html.twig', array(
            'entities' => $macsips,
            'esepciones' => $ips_ecep,
            'form' => $form->createView(),
        ));
    }

     /**
     * @Route("/maclist/new", name="maclist_new")
     */
    public function maclistnewAction()
    {
       return $this->render('AppBundle:Etiqueta:new.html.twig');
    }

         /**
     * @Route("/exeption/new", name="exeption_new")
     */
    public function exeptionnewAction()
    {
       return $this->render('AppBundle:Etiqueta:exeption.html.twig');
    }

     /**
     * @Route("/maclist/create", name="maclist_create")
     */
    public function maclistcreateAction()
    {
                
       $txtMac=trim($this->get('request')->request->get('txtMac', ''));
       $txtIp=trim($this->get('request')->request->get('txtIp', ''));            
       //$contenido=trim($txtAction)."  ".trim($txtInterface)."     ".trim($txtMac)."       ".trim($txtIp)."\n";
       if($txtMac!='' && $txtIp==''){
          $archivo = '/etc/warriorsips/ips_mac';
          $contenido=trim($txtMac)."\n";
       }

       if($txtMac=='' && $txtIp!=''){
          $archivo = '/etc/warriorsips/ips_ip';
          $contenido=trim($txtIp)."\n";
       }

       if($txtMac!='' && $txtIp!=''){
          $archivo = '/etc/warriorsips/ips_mac_ip';
          $contenido=trim($txtMac)."  ".trim($txtIp)."\n";
       }

        //echo $archivo;
        $file = fopen($archivo, "a");
        fwrite($file, $contenido . PHP_EOL);
      //  fwrite($file, "Añadimos línea 2" . PHP_EOL);
        fclose($file);
        return $this->redirect($this->generateUrl('maclist'));
    }

     /**
     * @Route("/exeption/create", name="exeption_create")
     */
    public function exeptioncreateAction()
    {
       $txtIp=trim($this->get('request')->request->get('txtIp', ''));            
      if($txtIp!=''){
          $archivo = '/etc/warriorsips/ips_ecep';
          $contenido=trim($txtIp)."\n";
       }

        //echo $archivo;
        $file = fopen($archivo, "a");
        fwrite($file, $contenido . PHP_EOL);
      //  fwrite($file, "Añadimos línea 2" . PHP_EOL);
        fclose($file);
        return $this->redirect($this->generateUrl('maclist'));
    }

     /**
     * @Route("/maclist/delete", name="maclist_delete")
     */
    public function maclistdeleteAction()
    {
        $puntero=$this->get('request')->request->get('cualid', '');
        //$puntero=$this->get('request')->request->get('txtElid', '');
        $operaciones=explode('_', $puntero);
        $res=false;
         //if($operaciones[0]){ 
          switch ($operaciones[1]) {
            case 'ip':$archivo = '/etc/warriorsips/ips_ip'; break;
            case 'mac':$archivo = '/etc/warriorsips/ips_mac'; break;
            case 'macip':$archivo = '/etc/warriorsips/ips_mac_ip'; break;
          }
        // Separar linea por linea
        //if($puntero>0){ 
           // $archivo = '/etc/shorewall/maclist';
            $abrir = fopen($archivo,'r+');
            $contenido = fread($abrir,filesize($archivo));
            fclose($abrir);        
            $contenido = explode("\n",$contenido);
            unset($contenido[$operaciones[0]]);
            $b = array_values($contenido);
            $otro = implode("\n",$b); 
            // Guardar Archivo
            $abrir = fopen($archivo,'w');
            fwrite($abrir,$otro);
            fclose($abrir);
            $res=true;
        //}
        return $this->redirect($this->generateUrl('maclist'));
        /*$response = new Response(json_encode(array('funciono'=>$res)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;*/
    }

     /**
     * @Route("/maclist/import", name="maclist_import")
     */
    public function maclistimportAction()
    {
         $entity  = new File();
        $form = $this->createFormBuilder($entity)
        /*->add('nombre', 'choice', array(
            'choices'   => array('1' => 'Reemplazar todo', '2' => 'Al final del archivo', '3' => 'Al inicio del archivo'),
            'required'  => false,
        ))*/
        ->add('file')
        ->getForm();
//$form['file']->getData()->move('/var/www/mike/seatcrm/seatcrm/src/L2a/FileBundle/Entity/../../../../web/uploads/documents', 'algo.xls');
    if ($this->getRequest()->isMethod('POST')) {
        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->upload();
           // $em->persist($entity);

            $respuesta = new response();
            $data = new \Readexcel_Readexcel();

            $data->setOutputEncoding('CP1251');
            $contenidoMac= array();
            $contenidoIp= array();
            $contenidoTodo= array();

            $data->read($entity->getUploadRootDir().'/'.$entity->getPath());
            for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
               // $contenido[]=trim($data->sheets[0]['cells'][$i][1])."  br0     ".trim($data->sheets[0]['cells'][$i][3])."       ".trim($data->sheets[0]['cells'][$i][4]);
               // echo $data->sheets[0]['cells'][$i][1].'<br>';
              if (array_key_exists(1, $data->sheets[0]['cells'][$i])) {
                  $txtMac=trim($data->sheets[0]['cells'][$i][1]);
                }else{
                  $txtMac='';
                }
              if (array_key_exists(2, $data->sheets[0]['cells'][$i])) {
                $txtIp=trim($data->sheets[0]['cells'][$i][2]);
              }else{
                $txtIp='';
              }

              if($txtMac!='' && $txtIp==''){
                  $contenidoMac[]=trim($txtMac)."\n";
               }

               if($txtMac=='' && $txtIp!=''){
                  $contenidoIp[]=trim($txtIp)."\n";
               }

               if($txtMac!='' && $txtIp!=''){
                  $contenidoTodo[]=trim($txtMac)."  ".trim($txtIp)."\n";
               }
            }
          /* switch ($entity->getNombre()) {
                        case 1:
                            $todo = implode("\n",$contenido); 
                            break;
                        case 2:
                            $archivo = '/etc/shorewall/maclist';
                            $abrir = fopen($archivo,'r+');
                            $content = fread($abrir,filesize($archivo));
                            fclose($abrir);                                   
                            $otro = implode("\n",$contenido); 
                            $todo =  $content .$otro;
                            break;
                        case 3:
                            $archivo = '/etc/shorewall/maclist';
                            $abrir = fopen($archivo,'r+');
                            $content = fread($abrir,filesize($archivo));
                            fclose($abrir);                                   
                            $otro = implode("\n",$contenido); 
                            $todo =  $otro.$content;
                            break;
                        
                        default:
                            # code...
                            break;
                    }   
*/
            // Guardar Archivo
                   /// print_r($contenidoMac);
            $txtMac = implode("\n",$contenidoMac);
            $txtIp = implode("\n",$contenidoIp);
            $txtTodo = implode("\n",$contenidoTodo);  

            $archivo = '/etc/warriorsips/ips_mac';
            $abrir = fopen($archivo,'w');
            fwrite($abrir,$txtMac);
            fclose($abrir);   

            $archivo = '/etc/warriorsips/ips_ip';
            $abrir = fopen($archivo,'w');
            fwrite($abrir,$txtIp);
            fclose($abrir);

            $archivo = '/etc/warriorsips/ips_mac_ip';
            $abrir = fopen($archivo,'w');
            fwrite($abrir,$txtTodo);
            fclose($abrir);   
    
        }
    }

    return $this->redirect($this->generateUrl('maclist'));
        /*return $this->render('AppBundle:Etiqueta:new.html.twig', array(
            'entities' => $todo,
        ));*/
    }

     /**
     * @Route("/maclist/export", name="maclist_export")
     */
    public function maclistexportAction()
    {       
       $contenido='';
        $archivo = '/etc/warriorsips/ips_ip';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
       // print_r($contenido );
        for($i=0;$i<count($contenido);$i++){   
            if($contenido[$i]!=''){         
                $macsips[]=array('ip'=>$contenido[$i],'id'=>$i.'_ip');
              }
        }

        $contenido='';
        $archivo = '/etc/warriorsips/ips_mac';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
       // print_r($contenido );
        for($i=0;$i<count($contenido);$i++){ 
              if($contenido[$i]!=''){            
                $macsips[]=array('mac'=>$contenido[$i],'id'=>$i.'_mac');
              }
        }

        $contenido='';
        $archivo = '/etc/warriorsips/ips_mac_ip';
        $abrir = fopen($archivo,'r+');
        $contenido = fread($abrir,filesize($archivo));
        fclose($abrir);
        $contenido = explode("\n",$contenido);
        for($i=0;$i<count($contenido);$i++){
            $datos=explode('  ', $contenido[$i]);
            if(count($datos)>1){
              $macsips[]=array('ip'=>$datos[1],'mac'=>$datos[0],'id'=>$i.'_macip');
            }
        }
         
        // Separar linea por linea
        $nombre = "maclist".date('YmdHis');
       /*         header("Content-Type: application/vnd.ms-excel");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("content-disposition: attachment;filename=".$nombre.'.xls');
               
      /*           header("Content-type: application/vnd.ms-excel");
header("Content-disposition: csv" . date("Y-m-d") . ".csv");
header( "Content-disposition: filename=".$nombre.".csv"); */
               $response = new response();

            $contenido= '<!DOCTYPE html>
                        <html>
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        </head>
                        <body>

                        <table>
                          ';
                           // echo $content;
                              //$otros = explode("\n",$content);
                             // print_r($otros);
                           foreach ($macsips as $key ) {                          
                               //print_r($datos);
                              if (array_key_exists('mac', $key)) {
                                  $contenido.= '<tr><td>'.trim($key['mac']).'</td>';
                              }else{
                                  $contenido.= '<tr><td></td>';
                              }
                              if (array_key_exists('ip', $key)) {
                                   $contenido.= '<td>'.trim($key['ip']).'</td></tr>';
                              }else{
                                     $contenido.= '<td></td></tr>';
                              }                              
                               
                            }

                             $contenido.='
                              
                            </table>

                            </body>
                            </html>';
                            //echo  $contenido;
                       //return new Response($contenido);
                       $response->setContent($contenido);                    // the headers public attribute is a ResponseHeaderBag
                    $response->headers->set('Content-Type', 'application/vnd.ms-excel');
                    $response->headers->set('Expires', '0');
                    $response->headers->set('Cache-Control', ' must-revalidate, post-check=0, pre-check=0');
                    $response->headers->set('content-disposition', "attachment;filename=".$nombre.".xls");
                    return $response;
    }

      /**
     * @Route("/maclist/edit", name="maclist_edit")
     */
    public function maclisteditAction()
    {
        return $this->render('AppBundle:Etiqueta:new.html.twig', array(
            'entities' => $todo,
        ));
    }

     /**
     * @Route("/maclist/update", name="maclist_update")
     */
    public function maclistupdateAction()
    {
        $puntero=$this->get('request')->request->get('txtElid', '');
        $operaciones=explode('_', $puntero);
        $res=false;
        // Separar linea por linea
        $txtMac=$this->get('request')->request->get('txtMac', '');
        $txtIp=$this->get('request')->request->get('txtIp', '');
        //if($operaciones[0]){ 
          switch ($operaciones[1]) {
            case 'ip':$archivoOri = '/etc/warriorsips/ips_ip'; break;
            case 'mac':$archivoOri = '/etc/warriorsips/ips_mac'; break;
            case 'macip':$archivoOri = '/etc/warriorsips/ips_mac_ip'; break;
          }
              if($txtMac!='' && $txtIp==''){
                  $archivo = '/etc/warriorsips/ips_mac';
                  $linea=trim($txtMac)."\n";
               }

               if($txtMac=='' && $txtIp!=''){
                  $archivo = '/etc/warriorsips/ips_ip';
                  $linea=trim($txtIp)."\n";
               }

               if($txtMac!='' && $txtIp!=''){
                  $archivo = '/etc/warriorsips/ips_mac_ip';
                  $linea=trim($txtMac)."  ".trim($txtIp)."\n";
               }

            if($archivo==$archivoOri){
                  //$archivo = '/etc/shorewall/maclist';
                  $abrir = fopen($archivo,'r+');
                  $contenido = fread($abrir,filesize($archivo));
                  fclose($abrir);        
                  $contenido = explode("\n",$contenido);
                  
                  $contenido[$operaciones[0]]=trim($linea);
                 // $b = array_values($contenido);
                  $otro = implode("\n",$contenido); 
                  // Guardar Archivo
                  $abrir = fopen($archivo,'w');
                  fwrite($abrir,$otro);
                  fclose($abrir);
                  $res=true;
            }else{
                  $abrir = fopen($archivoOri,'r+');
                  $contenido = fread($abrir,filesize($archivo));
                  fclose($abrir);        
                  $contenido = explode("\n",$contenido);
                  unset($contenido[$operaciones[0]]);
                  $array = array_values($contenido);
                  $otro = implode("\n",$array); 
                  // Guardar Archivo
                  $abrir = fopen($archivoOri,'w');
                  fwrite($abrir,$otro);
                  fclose($abrir);

                   $file = fopen($archivo, "a");
                   fwrite($file, $linea . PHP_EOL);
                  //  fwrite($file, "Añadimos línea 2" . PHP_EOL);
                   fclose($file);

                  $res=true;

               
            }
           
      //  }
       // return $this->redirect($this->generateUrl('maclist'));
        
        $response = new Response(json_encode(array('funciono'=>$res)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
    }


     /**
     * @Route("/exeption/update", name="exeption_update")
     */
    public function exeptionupdateAction()
    {
        $puntero=$this->get('request')->request->get('txtElid', '');
        $operaciones=explode('_', $puntero);
        $res=false;
        $txtIp=$this->get('request')->request->get('txtIp', '');
        $archivo = '/etc/warriorsips/ips_mac';
        $linea=trim($txtIp)."\n";
           
                  //$archivo = '/etc/shorewall/maclist';
                  $abrir = fopen($archivo,'r+');
                  $contenido = fread($abrir,filesize($archivo));
                  fclose($abrir);        
                  $contenido = explode("\n",$contenido);
                  
                  $contenido[$operaciones[0]]=trim($linea);
                 // $b = array_values($contenido);
                  $otro = implode("\n",$contenido); 
                  // Guardar Archivo
                  $abrir = fopen($archivo,'w');
                  fwrite($abrir,$otro);
                  fclose($abrir);
                  $res=true;
           
      //  }
       // return $this->redirect($this->generateUrl('maclist'));
        
        $response = new Response(json_encode(array('funciono'=>$res)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
    }


}
