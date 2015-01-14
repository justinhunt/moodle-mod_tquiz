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
 * @package   mod_tquiz
 * @copyright 2014 Justin Hunt poodllsupport@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 require_once($CFG->dirroot . '/mod/tquiz/lib.php');

/**
 * Define all the restore steps that will be used by the restore_tquiz_activity_task
 */

/**
 * Structure step to restore one tquiz activity
 */
class restore_tquiz_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();

        $userinfo = $this->get_setting_value('userinfo'); // are we including userinfo?

        ////////////////////////////////////////////////////////////////////////
        // XML interesting paths - non-user data
        ////////////////////////////////////////////////////////////////////////

        // root element describing tquiz instance
        $tquiz = new restore_path_element('tquiz', '/activity/tquiz');
        $paths[] = $tquiz;
		
		//questions
		$questions= new restore_path_element('tquiz_questions',
                                            '/activity/tquiz/questions/question');
		$paths[] = $questions;

		

        // End here if no-user data has been selected
        if (!$userinfo) {
            return $this->prepare_activity_structure($paths);
        }

        ////////////////////////////////////////////////////////////////////////
        // XML interesting paths - user data
        ////////////////////////////////////////////////////////////////////////
		//attempts
		 $attempts= new restore_path_element('tquiz_attempts',
                                            '/activity/tquiz/attempts/attempt');
		$paths[] = $attempts;
		 
		 //logs
		 $attempt_logs= new restore_path_element('tquiz_attempt_logs',
                                            '/activity/tquiz/attempt_logs/attempt_log');
		 $paths[] = $attempt_logs;


        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_tquiz($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);


        // insert the tquiz record
        $newitemid = $DB->insert_record('tquiz', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_tquiz_questions($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
		
		$data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $data->tquiz = $this->get_new_parentid('tquiz');
        $newitemid = $DB->insert_record('tquiz_questions', $data);
		$this->set_mapping('tquiz_question', $oldid, $newitemid, true); // Mapping with files
    }
	
	protected function process_tquiz_attempts($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
		
		$data->timefinished = $this->apply_date_offset($data->timefinished);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $data->tquizid = $this->get_new_parentid('tquiz');
        $newitemid = $DB->insert_record('tquiz_attempt', $data);
       $this->set_mapping('tquiz_attempt', $oldid, $newitemid, true); // Mapping with files
    }
	
	protected function process_tquiz_attempt_logs($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $data->tquizid = $this->get_new_parentid('tquiz');
		//$data->attemptid = $this->get_new_parentid('tquiz_attempt');
		$data->attemptid = $this->get_mappingid('tquiz_attempt',$data->attemptid);
		$data->questionid = $this->get_mappingid('tquiz_question', $data->questionid);
        $newitemid = $DB->insert_record('tquiz_attempt_log', $data);
       $this->set_mapping('tquiz_attempt_log', $oldid, $newitemid, true); // Mapping with files
    }
	
    protected function after_execute() {
        // Add tquiz related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_tquiz', 'intro', null);
		$this->add_related_files('mod_tquiz', 'feedback', null);

		//do question areas
		$this->add_related_files('mod_tquiz', MOD_TQUIZ_TEXTQUESTION_FILEAREA, 'tquiz_question');
		$this->add_related_files('mod_tquiz', MOD_TQUIZ_AUDIOQUESTION_FILEAREA, 'tquiz_question');

		//do answer areas
		for($i=1;$i<=MOD_TQUIZ_MAXANSWERS;$i++){
			$this->add_related_files('mod_tquiz', MOD_TQUIZ_TEXTANSWER_FILEAREA.$i, 'tquiz_question');
			$this->add_related_files('mod_tquiz', MOD_TQUIZ_AUDIOANSWER_FILEAREA.$i, 'tquiz_question');
		}
    }
}
