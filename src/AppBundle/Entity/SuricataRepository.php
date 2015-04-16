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
			if (!is_array($map_ref[$gid]))
				$map_ref[$gid] = array();
			if (!is_array($map_ref[$gid][$sid]))
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
			$map_ref[$gid][$sid]['flowbits'] = suricata_get_flowbits($rule);
			
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
}
