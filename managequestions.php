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
$action = optional_param('action','edit',PARAM_TEXT);

// get the objects we need
$cm = get_coursemodule_from_id('tquiz', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$tquiz = $DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST);

//make sure we are logged in and can see this form
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/tquiz:edit', $context);

//set up the page object
$PAGE->set_url('/mod/tquiz/managequestions.php', array('questionid'=>$questionid, 'id'=>$id, 'qtype'=>$qtype));
$PAGE->set_title(format_string($tquiz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');

//are we in new or edit mode?
if ($questionid) {
    $question = $DB->get_record('tquiz_questions', array('id'=>$questionid,'tquiz' => $cm->instance), '*', MUST_EXIST);
	if(!$question){
		print_error('could not find question of id:' . $questionid);
	}
    $qtype = $question->qtype;
    $edit = true;
} else {
    $edit = false;
}

//we always head back to the tquiz questions page
$redirecturl = new moodle_url('/mod/tquiz/edit.php', array('id'=>$cm->id));

	//handle delete actions
    if($action == 'confirmdelete'){
		$renderer = $PAGE->get_renderer('mod_tquiz');
		echo $renderer->header($tquiz, $cm, '', null, get_string('confirmquestiondeletetitle', 'tquiz'));
		echo $renderer->confirm(get_string("confirmquestiondelete","tquiz",$question->name), 
			new moodle_url('managequestions.php', array('action'=>'delete','id'=>$cm->id,'questionid'=>$questionid)), 
			$redirecturl);
		echo $renderer->footer();
		return;

	/////// Delete Question NOW////////
    }elseif ($action == 'delete'){
    	require_sesskey();
		/*
		//later we will need to delete from the attempts table
		$qlogs = $DB->get_records("tquiz_attempt_log",array("tquizid"=>$tquiz->id,'questionid'=>$questionid));
        if ($qlogs){
            if(!$DB->delete_records("tquiz_attempt_log",array("tquizid"=>$tquiz->id,'question'=>$questionid))){
                print_error("Could not delete logs for this question");
            }
            //must be a better way than this, ... later
            $attemptids=array();
            foreach($qlogs as $qlog){
            	if(!array_key_exists($qlog->attemptid,$attemptids)){
            		$attemptids[$qlog->attemptid]=0; 
            		$DB->delete_records("tquiz_attempt",array('id'=>$qlog->attemptid));
            	}
            }
        }
        

        if (!$DB->delete_records("tquiz_questions", array('id'=>$questionid))){
            print_error("Could not delete question");
        }
		//remove files
		$fs= get_file_storage();
		
		$fileareas = array(MOD_TQUIZ_TEXTQUESTION_FILEAREA,
		MOD_TQUIZ_TEXTANSWER_FILEAREA . '1',
		MOD_TQUIZ_TEXTANSWER_FILEAREA . '2',
		MOD_TQUIZ_TEXTANSWER_FILEAREA . '3',
		MOD_TQUIZ_TEXTANSWER_FILEAREA . '4',
		MOD_TQUIZ_AUDIOQUESTION_FILEAREA,
		MOD_TQUIZ_AUDIOANSWER_FILEAREA . '1',
		MOD_TQUIZ_AUDIOANSWER_FILEAREA . '2',
		MOD_TQUIZ_AUDIOANSWER_FILEAREA . '3',
		MOD_TQUIZ_AUDIOANSWER_FILEAREA . '4');
		foreach ($fileareas as $filearea){
			$fs->delete_area_files($context->id,'mod_tquiz',$filearea,$questionid);
		}
		*/
		$success = mod_tquiz_delete_question($tquiz,$questionid,$context);
        redirect($redirecturl);
	
    }



//get filechooser and html editor options
$editoroptions = mod_tquiz_fetch_editor_options($course, $context);
$audiofilemanageroptions = mod_tquiz_fetch_filemanager_options($course,1);


//get the mform for our question
switch($qtype){
	case MOD_TQUIZ_QTYPE_TEXTCHOICE:
		$mform = new tquiz_add_question_form_textchoice(null,
			array('editoroptions'=>$editoroptions, 
			'audiofilemanageroptions'=>$audiofilemanageroptions)
		);
		break;
	case MOD_TQUIZ_QTYPE_AUDIOCHOICE:
		$mform = new tquiz_add_question_form_audiochoice(null,
			array('editoroptions'=>$editoroptions, 
			'audiofilemanageroptions'=>$audiofilemanageroptions)
		);
		break;
	case MOD_TQUIZ_NONE:
	default:
		print_error('No question type specifified');

}

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
		$thequestion->shuffleanswers = $data->shuffleanswers;
		$thequestion->correctanswer = $data->correctanswer;
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
				case MOD_TQUIZ_QTYPE_TEXTCHOICE:
					for($i=1;$i<=MOD_TQUIZ_MAXANSWERS;$i++){
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
		
		//save question audio files
		file_save_draft_area_files($data->{MOD_TQUIZ_AUDIOQUESTION}, $context->id, 'mod_tquiz', MOD_TQUIZ_AUDIOQUESTION_FILEAREA,
			   $thequestion->id, $audiofilemanageroptions);
					
		//do things dependant on qtype
		switch($data->qtype){
			case MOD_TQUIZ_QTYPE_TEXTCHOICE:
				
				// Save answertext/files data
				$answercount=0;
				for($i=1;$i<=MOD_TQUIZ_MAXANSWERS;$i++){
					//saving files from text editor
					$data = file_postupdate_standard_editor( $data, MOD_TQUIZ_TEXTANSWER . $i, $editoroptions, $context,
                                        'mod_tquiz', MOD_TQUIZ_TEXTANSWER_FILEAREA.$i, $thequestion->id);
					$thequestion->{MOD_TQUIZ_TEXTANSWER . $i} = $data->{MOD_TQUIZ_TEXTANSWER . $i} ;
					$thequestion->{MOD_TQUIZ_TEXTANSWER . $i .'format'} = $data->{MOD_TQUIZ_TEXTANSWER . $i .'format'};	
					if(trim($thequestion->{MOD_TQUIZ_TEXTANSWER . $i}) !=''){
						$answercount=$i;
					}
				}
				
				//save answer layout data
				$thequestion->{MOD_TQUIZ_ANSWERSINROW}=$data->{MOD_TQUIZ_ANSWERSINROW};
				$thequestion->{MOD_TQUIZ_ANSWERWIDTH}=$data->{MOD_TQUIZ_ANSWERWIDTH};
				$thequestion->answercount=$answercount;
				break;
				
			case MOD_TQUIZ_QTYPE_AUDIOCHOICE:
				// Save answer data
				for($i=1;$i<=MOD_TQUIZ_MAXANSWERS;$i++){
					file_save_draft_area_files($data->{MOD_TQUIZ_AUDIOANSWER . $i}, $context->id, 'mod_tquiz', MOD_TQUIZ_AUDIOANSWER_FILEAREA . $i,
					   $thequestion->id, $audiofilemanageroptions);
				}
				
				//save answer layout data. We ignore this here
				$thequestion->{MOD_TQUIZ_ANSWERSINROW}=0;
				$thequestion->{MOD_TQUIZ_ANSWERWIDTH}=0;
				//its hard to tell from here how many audio files were added. 
				$thequestion->answercount=MOD_TQUIZ_MAXANSWERS;
				//$thequestion->answercount=$answercount;			
				break;
										
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
		
	//prepare audio file areas
	$draftitemid = file_get_submitted_draft_itemid(MOD_TQUIZ_AUDIOQUESTION);
	file_prepare_draft_area($draftitemid, $context->id, 'mod_tquiz', MOD_TQUIZ_AUDIOQUESTION_FILEAREA, $data->questionid,
						$audiofilemanageroptions);
	$data->{MOD_TQUIZ_AUDIOQUESTION} = $draftitemid;
	
	//Set up the question type specific parts of the form data
	switch($qtype){
		case MOD_TQUIZ_QTYPE_TEXTCHOICE:			
			//prepare answer areas
			for($i=1;$i<=MOD_TQUIZ_MAXANSWERS;$i++){
				//text editor
				$data = file_prepare_standard_editor($data, MOD_TQUIZ_TEXTANSWER . $i, $editoroptions, $context, 'mod_tquiz', MOD_TQUIZ_TEXTANSWER_FILEAREA . $i,  $data->questionid);
			}
			
			break;
		case MOD_TQUIZ_QTYPE_AUDIOCHOICE:
			
			//prepare answer areas
			for($i=1;$i<=MOD_TQUIZ_MAXANSWERS;$i++){
				//audio editor
				$draftitemid = file_get_submitted_draft_itemid(MOD_TQUIZ_AUDIOANSWER . $i);
				file_prepare_draft_area($draftitemid, $context->id, 'mod_tquiz', MOD_TQUIZ_AUDIOANSWER_FILEAREA . $i, $data->questionid,
									$audiofilemanageroptions);
				$data->{MOD_TQUIZ_AUDIOANSWER . $i} = $draftitemid;
			
			}
			
			break;
		default:
	}
    $mform->set_data($data);
    $PAGE->navbar->add(get_string('edit'), new moodle_url('/mod/tquiz/edit.php', array('id'=>$id)));
    $PAGE->navbar->add(get_string('editingquestion', 'tquiz', get_string($mform->qtypestring, 'tquiz')));
	$renderer = $PAGE->get_renderer('mod_tquiz');
	$mode='edit';
	echo $renderer->header($tquiz, $cm,$mode, null, get_string('edit', 'tquiz'));
	$mform->display();
	echo $renderer->footer();