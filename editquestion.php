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
 * Action for adding/editing a tquiz question. 
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
$questionid = optional_param('questionid',0 ,PARAM_INT);
$id     = required_param('id', PARAM_INT);         // Course Module ID
$qtype  = optional_param('qtype', MOD_TQUIZ_NONE, PARAM_INT);

// get the objects we need
$cm = get_coursemodule_from_id('tquiz', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$tquiz = $DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST);

//make sure we are logged in and can see this form
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/tquiz:edit', $context);

//set up the page object
$PAGE->set_url('/mod/tquiz/editquestion.php', array('questionid'=>$questionid, 'id'=>$id, 'qtype'=>$qtype));
$PAGE->set_title(format_string($tquiz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');

//are we in new or edit mode?
if ($questionid !=0) {
    $question = $DB->get_record('tquiz_questions', array('id'=>$questionid,'tquiz' => $cm->instance), '*', MUST_EXIST);
    $qtype = $question->qtype;
    $edit = true;
} else {
    $edit = false;
}

//get filechooser and html editor options
$editoroptions = mod_tquiz_fetch_editor_options($course, $context);
$audiofilemanageroptions = mod_tquiz_fetch_filemanager_options($course,1);

//get the mform for our question
switch($qtype){
	case MOD_TQUIZ_QTYPE_MULTICHOICE:
		$mform = new tquiz_add_question_form_multichoice(null,
			array('editoroptions'=>$editoroptions, 
			'audiofilemanageroptions'=>$audiofilemanageroptions)
		);
		break;
	case MOD_TQUIZ_NONE:
	default:
		print_error('No question type specifified');

}

//we always head back to the tquiz questions page
$redirecturl = new moodle_url('/mod/tquiz/edit.php', array('id'=>$cm->id));

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the quiz edit page
if ($data = $mform->get_data()) {
		require_sesskey();
		$thequestion = new stdClass;
        $thequestion->tquiz = $tquiz->id;
        $thequestion->id = $data->questionid;
		$thequestion->visible = $data->visible;
		$thequestion->order = $data->order;
		$thequestion->qtype = $data->qtype;
		$thequestion->name = $data->name;
		$thequestion->modifiedby=$USER->id;
		$thequestion->timemodified=time();
		
		//first insert a new question if we need to
		//that will give us a questionid, we need that for saving files
		if(!$edit){
			
			$thequestion->{MOD_TQUIZ_TEXTQUESTION} = '';
			$thequestion->{MOD_TQUIZ_TEXTQUESTION.'format'} = 0;
			$thequestion->timecreated=time();			
			$thequestion->createdby=$USER->id;
			switch($data->qtype){
				case MOD_TQUIZ_QTYPE_MULTICHOICE:
					$maxanswers = 4;
					for($i=1;$i<=$maxanswers;$i++){
						$thequestion->{MOD_TQUIZ_TEXTANSWER . $i}='';
						$thequestion->{MOD_TQUIZ_TEXTANSWER . $i . 'format'}=0;
					}
				
			}
			
			if (!$thequestion->id = $DB->insert_record("tquiz_questions",$thequestion)){
					error("Could not insert tquiz question!");
					redirect($redirecturl);
			}
		}
		
		//handle all the files
		//save the question text editor files (common to all qtypes)
		$data = file_postupdate_standard_editor( $data, MOD_TQUIZ_TEXTQUESTION, $editoroptions, $context,
								'mod_tquiz', MOD_TQUIZ_TEXTQUESTION_FILEAREA, $thequestion->id);
		$thequestion->{MOD_TQUIZ_TEXTQUESTION} = $data->{MOD_TQUIZ_TEXTQUESTION} ;
		$thequestion->{MOD_TQUIZ_TEXTQUESTION.'format'} = $data->{MOD_TQUIZ_TEXTQUESTION.'format'} ;
					
		//save files dependant on qtype
		switch($data->qtype){
			case MOD_TQUIZ_QTYPE_MULTICHOICE:
				//save question audio files
				file_save_draft_area_files($data->{MOD_TQUIZ_AUDIOQUESTION}, $context->id, 'mod_tquiz', MOD_TQUIZ_AUDIOQUESTION_FILEAREA,
					   $thequestion->id, $audiofilemanageroptions);
				
				// Save answer data
				$maxanswers = 4;
				for($i=1;$i<=$maxanswers;$i++){
					//saving files from text editor
					$data = file_postupdate_standard_editor( $data, MOD_TQUIZ_TEXTANSWER . $i, $editoroptions, $context,
                                        'mod_tquiz', MOD_TQUIZ_TEXTANSWER_FILEAREA.$i, $thequestion->id);
					$thequestion->{MOD_TQUIZ_TEXTANSWER . $i} = $data->{MOD_TQUIZ_TEXTANSWER . $i} ;
					$thequestion->{MOD_TQUIZ_TEXTANSWER . $i .'format'} = $data->{MOD_TQUIZ_TEXTANSWER . $i .'format'} ;
					//saving audio files
					/*
					file_save_draft_area_files($data->{MOD_TQUIZ_AUDIOANSWER . $i}, $context->id, 'mod_tquiz', MOD_TQUIZ_AUDIOQUESTION_FILEAREA . $i,
					   $question->id, $audiofilemanageroptions);
					   */
				}
										
			default:
				break;
		
		}
		
		//now update the db once we have saved files and stuff
		if (!$DB->update_record("tquiz_questions",$thequestion)){
				print_error("Could not update tquiz question!");
				redirect($redirecturl);
		}

		
		//go back to edit quiz page
		redirect($redirecturl);
}


//if  we got here, there was no cancel, and no form data, so we are showing the form
//if edit mode load up the question into a data object
if ($edit) {
	$data = $question;		
	$data->questionid = $question->id;
}else{
	$data=new stdClass;
	$data->questionid = null;
	$data->visible = 1;
	$data->qtype=$qtype;
}
		
	//init our question, we move the id fields around a little 
    $data->id = $cm->id;
    $data = file_prepare_standard_editor($data, MOD_TQUIZ_TEXTQUESTION, $editoroptions, $context, 'mod_tquiz', 
		MOD_TQUIZ_TEXTQUESTION_FILEAREA,  $data->questionid);	
	
	//Set up the question type specific parts of the form data
	switch($qtype){
		case MOD_TQUIZ_QTYPE_MULTICHOICE:
			//prepare audio file areas
			$draftitemid = file_get_submitted_draft_itemid(MOD_TQUIZ_AUDIOQUESTION);
			file_prepare_draft_area($draftitemid, $context->id, 'mod_tquiz', MOD_TQUIZ_AUDIOQUESTION_FILEAREA, $data->questionid,
								$audiofilemanageroptions);
			$data->{MOD_TQUIZ_AUDIOQUESTION} = $draftitemid;
			
	
			//prepare answer areas
			$maxanswers = 4;
			for($i=1;$i<=$maxanswers;$i++){
				//text editor
				$data = file_prepare_standard_editor($data, MOD_TQUIZ_TEXTANSWER . $i, $editoroptions, $context, 'mod_tquiz', MOD_TQUIZ_TEXTANSWER_FILEAREA . $i,  $data->questionid);
				//audio editor
				/*
				$draftitemid = file_get_submitted_draft_itemid(MOD_TQUIZ_AUDIOANSWER . $i);
				file_prepare_draft_area($draftitemid, $context->id, 'mod_tquiz', MOD_TQUIZ_AUDIOANSWER_FILEAREA . $i, $question->id,
									$audiofilemanageroptions);
				$data->{MOD_TQUIZ_AUDIOCONTENT . $i} = $draftitemid;
				*/
			
			}
			
			break;
		default:
	}
    $mform->set_data($data);
    $PAGE->navbar->add(get_string('edit'), new moodle_url('/mod/tquiz/edit.php', array('id'=>$id)));
    $PAGE->navbar->add(get_string('editingquestion', 'tquiz', get_string($mform->qtypestring, 'tquiz')));
	$renderer = $PAGE->get_renderer('mod_tquiz');
	echo $renderer->header($tquiz, $cm, '', null, get_string('edit', 'tquiz'));
	$mform->display();
	echo $renderer->footer();