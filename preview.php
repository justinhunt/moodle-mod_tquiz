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
 * @copyright  COPYRIGHTNOTICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');


$questionid = optional_param('questionid', 0, PARAM_INT); // questionid
$tquizid  = required_param('tquizid', PARAM_INT);  // tquiz instance ID

if ($tquizid) {
    $tquiz  = $DB->get_record('tquiz', array('id' => $tquizid), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $tquiz->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('tquiz', $tquiz->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a TQuiz ID');
}

//if we have a question id
if($questionid){
	$question = $DB->get_record('tquiz_questions',array('id'=>$questionid),'*', MUST_EXIST);
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);


/// Set up the page header
$PAGE->set_url('/mod/tquiz/preview.php', array('tquizid' => $tquizid, 'questionid'=>$questionid));
$PAGE->set_title(format_string($tquiz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('popup');

	//Get an admin settings 
	$config = get_config('mod_tquiz');

//get our javascript all ready to go
//We can omit $jsmodule, but its nice to have it here, 
//if for example we need to include some funky YUI stuff
$jsmodule = array(
	'name'     => 'mod_tquiz',
	'fullpath' => '/mod/tquiz/module.js',
	'requires' => array('transition','button','button-group')
);
$opts =Array();
$opts['cmid']=$cm->id;
$opts['quids']=array($questionid);
$opts['preview']=true;
$opts['a_trans_time']=0.5;
$opts['q_trans_time']=2;

//this inits the M.mod_tquiz thingy, after the page has loaded.
$PAGE->requires->js_init_call('M.mod_tquiz.helper.init', array($opts),false,$jsmodule);


//get our soundmanager library
//===========================================
$require_js = '/filter/videoeasy/players/soundmanagerv297a/script/soundmanager2.js';
$PAGE->requires->js(new moodle_url($require_js));
//set it up and init it
$soundopts = Array();
$soundopts['swfurl']='/filter/videoeasy/players/soundmanagerv297a/swf/';
//this inits the M.mod_tquiz thingy, after the page has loaded.
$PAGE->requires->js_init_call('M.mod_tquiz.sm2.init', array($soundopts),false,$jsmodule);
//===========================================


//From here we actually display the page.
$mode = "preview";
//echo $renderer->header($tquiz, $cm, $mode, null, get_string('preview', 'tquiz'));
$renderer = $PAGE->get_renderer('mod_tquiz');
echo $renderer->previewheader();
if($questionid){
	echo $renderer->fetch_question_display($question, $tquiz,$modulecontext);
	echo $renderer->fetch_answers_display($question, $tquiz,$modulecontext);
}else{
	echo 'nothing to preview';
}
echo $renderer->footer();
