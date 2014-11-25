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
 * Prints a particular instance of tquiz
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_tquiz
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$questionid = optional_param('questionid', '', PARAM_TEXT); // eventkey
$eventkey = optional_param('eventkey', '', PARAM_TEXT); // eventkey
$eventvalue = optional_param('eventvalue', '', PARAM_TEXT); // eventkey

//call so that we know we are who we said we are
require_sesskey();

if ($id) {
    $cm         = get_coursemodule_from_id('tquiz', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tquiz  = $DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

global $DB,$USER;

$result = false;
$attempts = $DB->get_records('tquiz_attempt',array('tquizid'=>$tquiz->id, 'userid'=>$USER->id));//, 'id ASC'
if($attempts){
	$attempt = array_pop($attempts);
	if($attempt->status!='complete'){
		//add the log
		$updatetime = time();
		$log = new stdClass();
		$log->attemptid = $attempt->id;
		$log->questionid = $questionid;
		$log->eventkey = $eventkey;
		$log->eventvalue = $eventvalue;
		$log->timecreated = $updatetime;
		$result = $DB->insert_record('tquiz_attempt_log', $log);
	}
}

//check completion reqs against satisfied conditions
if($result){
	$return =array('success'=>true);
	echo json_encode($return);
}else{
	$return =array('success'=>false);
	echo json_encode($return);
}