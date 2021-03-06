<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SuricataRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SuricataRepository extends EntityRepository
{
	function readRules(){
		$rules_path ='/etc/nsm/rules/';
		$rule_files = glob($rules_path . "*.rules");
		return $rule_files;
	}
	function readRulesact(){
		$rules_path ='/etc/nsm/rules/';
		$rule_files = glob($rules_path . "*.rules");
		$activoArchive=__DIR__.'/../../../web/rules/categoriaActiva.txt';
                $archivo = $activoArchive;
                $abrir = fopen($archivo,'r+');
                 $i=$x=0;
                 $partes=array();
                if(filesize($archivo)>0){
                        $contenido = fread($abrir,filesize($archivo));
                        fclose($abrir);        
                        $contenido = explode("\n",$contenido);
//print_r( $contenido );
                        foreach ($rule_files as $value) {
                        	$activo=0;
	                      	foreach ($contenido as $conte ) {
	                      		//echo $value.' | '.$conte.'</br>';
	                      		if($conte==$value){
	                      			$activo++;
	                      		}
	                      	}
                        
                        	$partes[]=array('value'=>$value,'conte'=>$conte,'activo'=>$activo);
                        	//$partes[]['value']=$value;
                    	}
                }else{
                	 foreach ($rule_files as $value) {
                	 	$partes[]=array('value'=>$value,'activo'=>0);
                        //$partes[]['value']=$value;
                	 }
                }
              
		return $partes;
	}

function suricata_load_rules_map($rules_path) {

	/***************************************************************/
	/* This function loads and returns an array with all the rules */
	/* found in the *.rules files in the passed rules path.        */
	/*                                                             */
	/* $rules_path can be:                                         */
	/*      a directory (assumed to contain *.rules files)         */
	/*      a filename (identifying a specific *.rules file)       */
	/*      an array of filenames (identifying *.rules files)      */
	/***************************************************************/

	$map_ref = array();
	$rule_files = array();

	if (empty($rules_path))
		return $map_ref;

	/************************************************************************************
	 * Read all the rules into the map array.
	 * The structure of the map array is:
	 *
	 *  map[gid][sid]['rule']['category']['action']['disabled']['managed']['flowbits']
	 *
	 *  where:
	 *   gid      = Generator ID from rule, or 1 if general text 
	 *              rule
	 *   sid      = Signature ID from rule
         *   rule     = Complete rule text
	 *   category = File name of file containing the rule
	 *   action   = alert, drop, reject or pass
	 *   disabled = 1 if rule is disabled (commented out), 0 if 
	 *              rule is enabled
	 *    managed = 1 if rule is auto-managed by SID MGMT process,
	 *              0 if not auto-managed 
	 *   flowbits = Array of applicable flowbits if rule contains 
	 *              flowbits options
	 ************************************************************************************/

	// First check if we were passed a directory, a single file
	// or an array of filenames to read. Set our $rule_files
	// variable accordingly. If we can't figure it out, return
	// an empty rules map array.
	if (is_string($rules_path)) {
		if (is_dir($rules_path))
			$rule_files = glob($rules_path . "*.rules");
		elseif (is_file($rules_path))
			$rule_files = (array)$rules_path;
	}
	elseif (is_array($rules_path))
		$rule_files = $rules_path;
	else
		return $map_ref;

        // Read the rule files into an array, then iterate the list
	// to process the rules from the files one-by-one.

	foreach ($rule_files as $file) {

		// Don't process files with "deleted" in the filename.
		if (stristr($file, "deleted"))
			continue;

		// Read the file contents into an array, skipping
		// missing files.
		if (!file_exists($file))
			continue;

		$rules_array = file($file, FILE_SKIP_EMPTY_LINES);
		$record = "";
		$b_Multiline = false;
//print_r($rules_array);
		// Read and process each line from the rules in the
		// current file into an array.
		foreach ($rules_array as $rule) {

			// Skip any lines that may be just spaces.
			if (trim($rule, " \n") == "")
				continue;

			// Skip any non-rule lines unless we're in
			// multiline mode.
			if (!preg_match('/^\s*#*\s*(alert|drop|pass|reject)/i', $rule) && !$b_Multiline)
				continue;

			// Test for a multi-line rule; loop and reassemble
			// the pieces back into a single line.
			if (preg_match('/\\\\s*[\n]$/m', $rule)) {
				$rule = substr($rule, 0, strrpos($rule, '\\'));
				$record .= $rule;
				$b_Multiline = true;
				continue;
			}
			// If the last segment of a multiline rule, then
			// append it onto the previous parts to form a
			// single-line rule for further processing below.
			elseif (!preg_match('/\\\\s*[\n]$/m', $rule) && $b_Multiline) {
				$record .= $rule;
				$rule = $record;
			}

			// We have an actual single-line rule, or else a
			// re-assembled multiline rule that is now a
			// single-line rule, so store it in our rules map.

			// Get and test the SID.  If we don't find one,
			// ignore and skip this rule as it is invalid.
			$sid = $this->suricata_get_sid($rule);
			if (empty($sid)) {
				$b_Multiline = false;
				$record = "";
				continue;
			}

			$gid = $this->suricata_get_gid($rule);
			if (!isset($map_ref[$gid]))
				$map_ref[$gid] = array();
			if (!isset($map_ref[$gid][$sid]))
				$map_ref[$gid][$sid] = array();
			$map_ref[$gid][$sid]['rule'] = $rule;
			$map_ref[$gid][$sid]['category'] = basename($file, ".rules");

			// Grab the rule action
			$matches = array();
			if (preg_match('/^\s*#*\s*(alert|drop|pass|reject)/i', $rule, $matches))
				$map_ref[$gid][$sid]['action'] = $matches[1];
			else
				$map_ref[$gid][$sid]['action'] = "";

			// Determine if default state is "disabled"
			if (preg_match('/^\s*\#+/', $rule))
				$map_ref[$gid][$sid]['disabled'] = 1;
			else
				$map_ref[$gid][$sid]['disabled'] = 0;

			// Grab any associated flowbits from the rule.
			$map_ref[$gid][$sid]['flowbits'] = $this->suricata_get_flowbits($rule);
			
			// Reset our local flag and record variables
			// for the next rule in the set.
			$b_Multiline = false;
			$record = "";
		}

		// Zero out our processing array and get the next file.
		unset($rules_array);
	}
	return $map_ref;
}

function suricata_get_sid($rule) {

	/***************************************************************/
	/* If a sid is defined, then return it, else default to an     */
	/* empty value.                                                */
	/***************************************************************/

	if (preg_match('/\bsid\s*:\s*(\d+)\s*;/i', $rule, $matches))
		return trim($matches[1]);
	else
		return "";
}
function suricata_get_gid($rule) {

	/****************************************************************/
	/* If a gid is defined, then return it, else default to "1" for */
	/* general text rules match.                                    */
	/****************************************************************/

	if (preg_match('/\bgid\s*:\s*(\d+)\s*;/i', $rule, $matches))
		return trim($matches[1]);
	else
		return "1";
}
function suricata_get_flowbits($rule) {

	/*************************************************************/
	/* This will pull out "flowbits:" options from the rule text */
	/* and return them in an array (minus the "flowbits:" part). */
	/*************************************************************/

	$flowbits = array();

	// Grab any "flowbits:set, setx, unset, isset or toggle" options first.
	// Examine flowbits targets for logical operators to capture all targets. 
	if (preg_match_all('/flowbits\b\s*:\s*(set|setx|unset|toggle|isset|isnotset)\s*,([^;]+)/i', $rule, $matches)) {
		$i = -1;
		while (++$i < count($matches[1])) {
			$action = trim($matches[1][$i]);
			$target = preg_split('/[&|]/', $matches[2][$i]);
			foreach ($target as $t)
				$flowbits[] = "{$action}," . trim($t);
		}
	}

	// Include the "flowbits:noalert or reset" options, if present.
	if (preg_match_all('/flowbits\b\s*:\s*(noalert|reset)\b/i', $rule, $matches)) {
		$i = -1;
		while (++$i < count($matches[1])) {
			$flowbits[] = trim($matches[1][$i]);
		}
	}

	return $flowbits;
}

function mwexec_bg($command, $clearsigmask = false) {
	global $g;

	if ($g['debug']) {
		if (!$_SERVER['REMOTE_ADDR'])
			echo "mwexec(): $command\n";
	}

	if ($clearsigmask) {
		$oldset = array();
		pcntl_sigprocmask(SIG_SETMASK, array(), $oldset);
	}
	$_gb = exec("/usr/bin/nohup $command > /dev/null 2>&1 &");
	if ($clearsigmask) {
		pcntl_sigprocmask(SIG_SETMASK, $oldset);
	}
	unset($_gb);
}
function add_title_attribute($tag, $title) {

	/********************************
	 * This function adds a "title" *
	 * attribute to the passed tag  *
	 * and sets the value to the    *
	 * value specified by "$title". *
	 ********************************/
	$result = "";
	if (empty($tag)) {
		// If passed an empty element tag, then
		// just create a <span> tag with title
		$result = "<span title=\"" . $title . "\">";
	}
	else {
		// Find the ending ">" for the element tag
		$pos = strpos($tag, ">");
		if ($pos !== false) {
			// We found the ">" delimter, so add "title"
			// attribute and close the element tag
			$result = substr($tag, 0, $pos) . " title=\"" . $title . "\">";
		}
		else {
			// We did not find the ">" delimiter, so
			// something is wrong, just return the
			// tag "as-is"
			$result = $tag;
		}
	}
	return $result;
}

function suricata_get_msg($rule) {

	/**************************************************************/
	/* Return the MSG section of the passed rule as a string.     */
	/**************************************************************/

	$msg = "";
	if (preg_match('/\bmsg\s*:\s*"(.+?)"\s*;/i', $rule, $matches))
		$msg = trim($matches[1]);
	return $msg;
}
function rulestoArray($rules_map,$currentruleset){
	$datos = array();
	$counter = $enable_cnt = $disable_cnt = $user_enable_cnt = $user_disable_cnt = $managed_count = 0;
	//echo '<pre>';print_r($rules_map );echo '</pre>';
foreach ($rules_map as $k1 => $rulem) {
							foreach ($rulem as $k2 => $v) {
								$sid = $this->suricata_get_sid($v['rule']);
								$dato['id']=$sid;
								$gid = $this->suricata_get_gid($v['rule']);
								$laz=$lax=0;
								foreach ($currentruleset as $key) {
									$osid = $this->suricata_get_sid($key['rule']);
									if($sid==$osid)
									{
										$laz++;
										//$lax=
									}
									# code...
								}
								if($laz > 0){
									$v['disabled']=0;
								}else{
									$v['disabled']=1;
								}
								//echo '<pre>';print_r($rules_map );echo '</pre>'
								
								//$ruleset = $currentruleset;
								$style = "";
							/*	print_r($v);
								if ($v['managed'] == 1) {
									if ($v['disabled'] == 1) {
										$dato['textss'] = "<span class=\"gray\">";
										$dato['textse'] = "</span>";
										$dato['style']= "style=\"opacity: 0.4; filter: alpha(opacity=40);\"";
										$dato['title'] = gettext("Auto-disabled by settings on SID Mgmt tab");
									}
									else {
										$dato['textss'] = $textse = "";
										$dato['ruleset'] = "suricata.rules";
										$dato['title'] = gettext("Auto-managed by settings on SID Mgmt tab");
									}
									$dato['iconb'] = "icon_advanced.gif";
									$managed_count++;
								}
								elseif (isset($disablesid[$gid][$sid])) {
									$dato['textss'] = "<span class=\"gray\">";
									$dato['textse'] = "</span>";
									$dato['iconb'] = "icon_reject_d.gif";
									$disable_cnt++;
									$user_disable_cnt++;
									$dato['title'] = gettext("Disabled by user. Click to toggle to enabled state");
								}*/
								if ($v['disabled'] == 1) {
									$textss= $dato['textss'] = "<span class=\"gray\">";
									$dato['textse'] = "</span>";
									$dato['iconb'] = "inactivo";
									//echo $dato['iconb'];
									$disable_cnt++;
									$dato['title'] = gettext("Disabled by default. Click to toggle to enabled state");
								}
								elseif ($v['disabled'] == 0) {
									$textss= $dato['textss'] = $textse = "";
									$dato['iconb'] = "activo";
									$enable_cnt++;
									$enable_cnt++;
									$dato['title'] = gettext("Enabled by user. Click to toggle to disabled state");
								}
								else {
									$textss=$dato['textss'] = $dato['textse'] = "";
									$dato['iconb'] = "activo";
									$enable_cnt++;
									$dato['title'] = gettext("Enabled by default. Click to toggle to disabled state");
								}

								// Pick off the first section of the rule (prior to the start of the MSG field),
								// and then use a REGX split to isolate the remaining fields into an array.
								$tmp = substr($v['rule'], 0, strpos($v['rule'], "("));
								$tmp = trim(preg_replace('/^\s*#+\s*/', '', $tmp));
								$rule_content = preg_split('/[\s]+/', $tmp);

								// Create custom <span> tags for some of the fields so we can 
								// have a "title" attribute for tooltips to show the full string.
								//$dato['srcspan'] = $this->add_title_attribute($textss, $rule_content[2]);
								//$dato['srcprtspan'] = $this->add_title_attribute($textss, $rule_content[3]);
								//$dato['dstspan'] =$this->add_title_attribute($textss, $rule_content[5]);
								//$dato['dstprtspan'] = $this->add_title_attribute($textss, $rule_content[6]);
								$dato['protocol'] = $rule_content[1]; //protocol field
								$dato['source'] = $rule_content[2]; //source field
								$dato['source_port'] = $rule_content[3]; //source port field
								$dato['destination'] = $rule_content[5]; //destination field
								$dato['destination_port'] = $rule_content[6]; //destination port field
								$dato['message'] = $this->suricata_get_msg($v['rule']);
								//$dato['sid_tooltip'] = gettext("View the raw text for this rule");
								$dato['rule']=$v['rule'];
								$masked = $v['rule'];
								//$masked = "U|".$v['rule']."|a|b";
								//echo $v['rule'].'<br>';

			                    $masked = base64_encode($masked);
			                    //echo  $masked .'<br>';
			                    $masked = urlencode($masked);
			                    //echo  $masked .'<br>';
			                    $masked = preg_replace('/=$/','',$masked);
			                    $dato['ruleencode'] = $masked;
							$datos[]=$dato;
						}
					}
return array('datos'=>$datos,'enabled'=>$enable_cnt,'disabled'=>$disable_cnt);

}
function rulestomapArray($rules_map){
	$datos = array();
	$counter = $enable_cnt = $disable_cnt = $user_enable_cnt = $user_disable_cnt = $managed_count = 0;
	//echo '<pre>';print_r($rules_map );echo '</pre>';
foreach ($rules_map as $k1 => $rulem) {
							foreach ($rulem as $k2 => $v) {
								$sid = $this->suricata_get_sid($v['rule']);
								$dato['id']=$sid;
								$gid = $this->suricata_get_gid($v['rule']);
								
								if ($v['disabled'] != 1) {
									$dato['rule']=$v['rule'];
								}
								
								
								
						}
					}
return array('datos'=>$datos,'enabled'=>$enable_cnt,'disabled'=>$disable_cnt);

}
}
