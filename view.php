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


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // tquiz instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('tquiz', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tquiz  = $DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $tquiz  = $DB->get_record('tquiz', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $tquiz->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('tquiz', $tquiz->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

//Diverge logging logic at Moodle 2.7
if($CFG->version<2014051200){
	add_to_log($course->id, 'tquiz', 'view', "view.php?id={$cm->id}", $tquiz->name, $cm->id);
}else{
	// Trigger module viewed event.
	$event = \mod_tquiz\event\course_module_viewed::create(array(
	   'objectid' => $tquiz->id,
	   'context' => $modulecontext
	));
	$event->add_record_snapshot('course_modules', $cm);
	$event->add_record_snapshot('course', $course);
	$event->add_record_snapshot('tquiz', $tquiz);
	$event->trigger();
} 

//are we a teacher or a student?
if(has_capability('mod/tquiz:preview',$modulecontext)){
	$mode = "preview";
}else{
	$mode= "view";
}



/// Set up the page header
$PAGE->set_url('/mod/tquiz/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($tquiz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
if($mode=="view"){
	$PAGE->set_pagelayout('base');
}else{
	$PAGE->set_pagelayout('course');
}
//Get an admin settings 
$config = get_config('mod_tquiz');

//This puts all our display logic into the renderer.php file in this plugin
//theme developers can override classes there, so it makes it customizable for others
//to do it this way.
$renderer = $PAGE->get_renderer('mod_tquiz');

//From here we prepare to display the page.
$frames = array();
$questiondivs = '';
$questions = $DB->get_records('tquiz_questions',array('tquiz'=>$tquiz->id));
$questionids = array();
$question_array=array();
foreach($questions as $question){
	$q = $renderer->fetch_question_div($question, $tquiz,$modulecontext);
	$question_array[] = $q;
	$questionids[]=$question->id;
	if($tquiz->shufflequestions){
		shuffle($question_array);
	}
	$questiondivs = implode(' ',$question_array);
}


//get our main javascript all ready to go
//===========================================
//We can omit $jsmodule, but its nice to have it here, 
//if for example we need to include some funky YUI stuff
$jsmodule = array(
	'name'     => 'mod_tquiz',
	'fullpath' => '/mod/tquiz/module.js',
	'requires' => array('transition','button','button-group','io')
);
//'requires' => array('transition','button','button-group')
$opts =Array();
$opts['cmid']=$cm->id;
$opts['quids']=$questionids;
$opts['preview']=$mode=='preview';
$opts['editmode']=false;
$opts['qcount']=count($questionids);
$opts['a_trans_time']=0.5;
$opts['q_trans_time']=2;

//this inits the M.mod_tquiz thingy, after the page has loaded.
$PAGE->requires->js_init_call('M.mod_tquiz.helper.init', array($opts),false,$jsmodule);
//===========================================

//get our soundmanager library
//===========================================
$require_css = '/mod/tquiz/font-awesome/css/font-awesome.min.css';
$PAGE->requires->css(new moodle_url($require_css));
$require_js = '/mod/tquiz/soundmanager/script/soundmanager2.js';
$PAGE->requires->js(new moodle_url($require_js));
//set it up and init it
$soundopts = Array();
$soundopts['swfurl']='/mod/tquiz/soundmanager/swf/';
//this inits the M.mod_tquiz thingy, after the page has loaded.
$PAGE->requires->js_init_call('M.mod_tquiz.sm2.init', array($soundopts),false,$jsmodule);
//===========================================

//get our quiz timer working
//===========================================
	$timeropts =Array();
	$timeropts['timelimit']=$tquiz->timelimit;
	$timeropts['showcountdown']=false;
	$PAGE->requires->js_init_call('M.mod_tquiz.timer.init', array($timeropts), false,$jsmodule);
//===========================================

//if we are teacher we see tabs. If student we just see the quiz
if($mode=='preview'){
	echo $renderer->header($tquiz, $cm, $mode, null, get_string('view', 'tquiz'));
}else{
	echo $renderer->notabsheader();
}

echo $renderer->fetch_intro($tquiz,$cm);
echo $questiondivs;
echo $renderer->fetch_feedback($tquiz,$cm,$modulecontext);
echo $renderer->fetch_progressbar();
echo $renderer->fetch_countdowntimer();
echo $renderer->footer();
