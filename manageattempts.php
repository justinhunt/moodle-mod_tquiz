<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Action for adding/editing a tquiz attempt. 
 *
 * @package mod_tquiz
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/tquiz/forms.php');
require_once($CFG->dirroot.'/mod/tquiz/locallib.php');

global $USER,$DB;

// first get the nfo passed in to set up the page
$attemptid = optional_param('attemptid',0 ,PARAM_INT);
$id     = required_param('id', PARAM_INT);         // Course Module ID
$action = optional_param('action','view',PARAM_TEXT);

// get the objects we need
$cm = get_coursemodule_from_id('tquiz', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$tquiz = $DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST);

//make sure we are logged in and can see this form
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/tquiz:edit', $context);

//set up the page object
$PAGE->set_url('/mod/tquiz/manageattempts.php', array('attemptid'=>$attemptid, 'id'=>$id));
$PAGE->set_title(format_string($tquiz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');

//are we in new or edit mode?
if ($attemptid) {
    $attempt = $DB->get_record('tquiz_attempt', array('id'=>$attemptid,'tquizid' => $cm->instance), '*', MUST_EXIST);
	if(!$attempt){
		print_error('could not find attempt of id:' . $attemptid);
	}
} else {
    $edit = false;
}

//we always head back to the tquiz attempts page
$redirecturl = new moodle_url('/mod/tquiz/reports.php', array('id'=>$cm->id));

//handle delete actions
switch($action){
	case 'confirmdelete':
		$renderer = $PAGE->get_renderer('mod_tquiz');
		echo $renderer->header($tquiz, $cm, '', null, get_string('confirmattemptdeletetitle', 'tquiz'));
		echo $renderer->confirm(get_string("confirmattemptdelete","tquiz"), 
			new moodle_url('manageattempts.php', array('action'=>'delete','id'=>$cm->id,'attemptid'=>$attemptid)), 
			$redirecturl);
		echo $renderer->footer();
		return;

/////// Delete attempt NOW////////
	case 'delete':
		require_sesskey();
		if (!$DB->delete_records("tquiz_attempt", array('id'=>$attemptid))){
			print_error("Could not delete attempt");
			if (!$DB->delete_records("tquiz_attempt_log", array('attemptid'=>$attemptid))){
				print_error("Could not delete logs");
			}
		}
		redirect($redirecturl);
	
	case 'confirmdeleteall':
		$renderer = $PAGE->get_renderer('mod_tquiz');
		echo $renderer->header($tquiz, $cm, '', null, get_string('confirmattemptdeletealltitle', 'tquiz'));
		echo $renderer->confirm(get_string("confirmattemptdeleteall","tquiz"), 
			new moodle_url('manageattempts.php', array('action'=>'deleteall','id'=>$cm->id)), 
			$redirecturl);
		echo $renderer->footer();
		return;
	
	/////// Delete ALL attempts ////////
	case 'deleteall':
		require_sesskey();
		if (!$DB->delete_records("tquiz_attempt", array('tquizid'=>$tquiz->id))){
			print_error("Could not delete attempts (all)");
			if (!$DB->delete_records("tquiz_attempt_log", array('tquizid'=>$tquiz->id))){
				print_error("Could not delete logs (all)");
			}
		}
		redirect($redirecturl);

}

//if  we got here we are in view mode

    $PAGE->navbar->add(get_string('view'), new moodle_url('/mod/tquiz/manageattempts.php', array('id'=>$cm->id,'action'=>'view','attemptid'=>$attempt->id)));
    $PAGE->navbar->add(get_string('viewingattempt', 'tquiz'));
	$renderer = $PAGE->get_renderer('mod_tquiz');
	$mode='reports';
	echo $renderer->header($tquiz, $cm,$mode, null, get_string('view', 'tquiz'));
	$logs = $DB->get_records('tquiz_attempt_log',array('attemptid'=>$attempt->id));
	echo $renderer->show_logs_list($logs);
	echo $renderer->show_attempts_link($cm->id);
	echo $renderer->footer();