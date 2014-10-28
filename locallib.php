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
   
   function mod_tquiz_add_question($formdata, $tquiz) {
        global $DB;
        $newquestion = new stdClass;
        $newquestion->title = $formdata->title;
        $newquestion->contents = $formdata->contents_editor['text'];
        $newquestion->contentsformat = $formdata->contents_editor['format'];
        $newquestion->tquiz = $tquiz->id;
        $newquestion->timecreated = time();
        $newquestion->qtype = $formdata->qtype;
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


