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
 * Internal library of functions for module tquiz
 *
 * All the tquiz specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_tquiz
 * @copyright  COPYRIGHTNOTICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

 /**
     * This method loads a question object from the database and returns it as a
     * 
     * @param int $id
     * @param tquiz $tquiz
     * @return tquiz_question Specialised tquiz_page object
     */
  function mod_tquiz_load_question($questionid) {
        global $DB;

            $question = $DB->get_record("tquiz_questions", array("id" => $questionid));
            if (!$question) {
                print_error('cannotfindpages', 'tquiz');
            }
        
        return $question;
   }
   
   function mod_tquiz_delete_question($tquiz, $questionid, $context) {
		global $DB;
		$ret = false;
		$qlogs = $DB->get_records("tquiz_attempt_log",array("tquizid"=>$tquiz->id,'questionid'=>$questionid));
        if ($qlogs){
            if(!$DB->delete_records("tquiz_attempt_log",array("tquizid"=>$tquiz->id,'questionid'=>$questionid))){
                print_error("Could not delete logs for this question");
				return $ret;
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
			return $ret;
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
		$ret = true;
		return $ret;
   } 
   
   
   function mod_tquiz_add_question($formdata, $tquiz) {
        global $DB;
        $newquestion = new stdClass;
        $newquestion->title = $formdata->title;
        $newquestion->contents = $formdata->contents_editor['text'];
        $newquestion->contentsformat = $formdata->contents_editor['format'];
        $newquestion->tquiz = $tquiz->id;
        $newquestion->timecreated = time();
        $newquestion->qtype = $formdata->qtype;
		$newquestion->shuffleanswers = $formdata->shuffleanswers;
        $newquestion->qoption = (isset($formdata->qoption))?1:0;
        $newquestion->layout = (isset($formdata->layout))?1:0;
        $newquestion->display = (isset($formdata->display))?1:0;
        $newquestion->prevquestionid = 0; // this is a first question
        $newquestion->nextquestionid = 0; // this is the only question

        if ($formdata->questionid) {
            $prevquestion = $DB->get_record("tquiz_questions", array("id" => $formdata->questionid), 'id, nextquestionid');
            if (!$prevquestion) {
                print_error('cannotfindquestions', 'tquiz');
            }
            $newquestion->prevquestionid = $prevquestion->id;
            $newquestion->nextquestionid = $prevquestion->nextquestionid;
        } else {
            $nextquestion = $DB->get_record('tquiz_questions', array('tquizid'=>$tquiz->id, 'prevquestionid'=>0), 'id');
            if ($nextquestion) {
                // This is the first question, there are existing questions put this at the start
                $newquestion->nextquestionid = $nextquestion->id;
            }
        }

        $newquestion->id = $DB->insert_record("tquiz_questions", $newquestion);

        $editor = new stdClass;
        $editor->id = $newquestion->id;
        $editor->contents_editor = $formdata->contents_editor;
        $editor = file_postupdate_standard_editor($editor, 'contents', array('noclean'=>true, 'maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$maxbytes), $context, 'mod_tquiz', 'question_contents', $editor->id);
        $DB->update_record("tquiz_questions", $editor);

        if ($newquestion->prevquestionid > 0) {
            $DB->set_field("tquiz_questions", "nextquestionid", $newquestion->id, array("id" => $newquestion->prevquestionid));
        }
        if ($newquestion->nextquestionid > 0) {
            $DB->set_field("tquiz_questions", "prevquestionid", $newquestion->id, array("id" => $newquestion->nextquestionid));
        }

        $answers = mod_tquiz_create_answers($formdata,$newquestion->id);

       // $tquiz->add_message(get_string('insertedquestion', 'tquiz').': '.format_string($newquestion->title, true), 'notifysuccess');

        return $question;
    }
	/*
	function mod_tquiz_update_answers($formdata, $questionid) {
        global $DB;

            $ret = $DB->delete_records("tquiz_answers", array("questionid" => $questionid));
            if (!$ret) {
                print_error('cannotdeleteanswers', 'tquiz');
            }
			//do we need to also delete in files in file areas?
			mod_tquiz_create_answers($formdata, $questionid);
    }
	
	function mod_tquiz_create_answers($formdata, $questionid) {
        global $DB;

        return $true;
    }
	
	
	 function mod_tquiz_update_question($formdata) {
        global $DB;

            $question = $DB->get_record("tquiz_questions", array("id" => $questionid));
            if (!$question) {
                print_error('cannotfindquestions', 'tquiz');
            }
        
        return $question;
    }
	*/
	function mod_tquiz_fetch_editor_options($course, $modulecontext){
		$maxfiles=99;
		$maxbytes=$course->maxbytes;
		return  array('trusttext'=>true, 'subdirs'=>true, 'maxfiles'=>$maxfiles,
							  'maxbytes'=>$maxbytes, 'context'=>$modulecontext);
	}

	function mod_tquiz_fetch_filemanager_options($course, $maxfiles=1){
		$maxbytes=$course->maxbytes;
		return array('subdirs'=>true, 'maxfiles'=>$maxfiles,'maxbytes'=>$maxbytes,'accepted_types' => array('audio'));
	}



