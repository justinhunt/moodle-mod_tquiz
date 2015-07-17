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
 * Defines all the backup steps that will be used by {@link backup_tquiz_activity_task}
 *
 * @package     mod_tquiz
 * @category    backup
 * @copyright   2014 Justin Hunt <poodllsupport@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/tquiz/lib.php');

/**
 * Defines the complete tquiz structure for backup, with file and id annotations
 *
 */
class backup_tquiz_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the 'tquiz' element inside the tquiz.xml file
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // are we including userinfo?
        $userinfo = $this->get_setting_value('userinfo');

        ////////////////////////////////////////////////////////////////////////
        // XML nodes declaration - non-user data
        ////////////////////////////////////////////////////////////////////////

        // root element describing tquiz instance
        $tquiz = new backup_nested_element('tquiz', array('id'), array(
            'course', 'name','intro', 'introformat', 'feedback', 'feedbackformat','timelimit','shufflequestions','attemptsallowed', 'timemodified', 'timecreated'
			));

		//questions
        $questions = new backup_nested_element('questions');
        $question = new backup_nested_element('question', array('id'),array(
		  'name', 'qtype','tquiz','questiontext', 'questiontextformat', 'answertext1', 'answertext1format','answertext2', 'answertext2format',
		  'answertext3', 'answertext3format','answertext4', 'answertext4format','visible','questionaudiofname',
		  'correctanswer','shuffleanswers','answercount','answersinrow','answerwidth','createdby','modifiedby','timecreated','timemodified'
		));
		
		
		//attempts
        $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', array('id'),array(
			 'type', 'tquizid', 'userid', 'status','score','timecreated','timemodified'
		));
		
		//attempt_logs
        $attempt_logs = new backup_nested_element('attempt_logs');
        $attempt_log = new backup_nested_element('attempt_log', array('id'),array(
			'attemptid', 'questionid','tquizid','userid','eventkey', 'eventvalue','eventtime','timecreated' 
		));
		
		  // Build the tree.
        $tquiz->add_child($questions);
        $questions->add_child($question);
        $tquiz->add_child($attempts);
        $attempts->add_child($attempt);
        $tquiz->add_child($attempt_logs);
        $attempt_logs->add_child($attempt_log);
		


        // Define sources.
        $tquiz->set_source_table('tquiz', array('id' => backup::VAR_ACTIVITYID));
        $question->set_source_table('tquiz_questions',
                                        array('tquiz' => backup::VAR_PARENTID));
        //sources if including user info
        if ($userinfo) {
			$attempt->set_source_table('tquiz_attempt',
											array('tquizid' => backup::VAR_PARENTID));
			$attempt_log->set_source_table('tquiz_attempt_log',
											array('tquizid' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $attempt->annotate_ids('user', 'userid');
		$attempt_log->annotate_ids('user', 'userid');


        // Define file annotations.
        // intro file area has 0 itemid.
        $tquiz->annotate_files('mod_tquiz', 'intro', null);
        $tquiz->annotate_files('mod_tquiz', 'feedback', null);
		
		//other file areas use tquizid
		$question->annotate_files('mod_tquiz', MOD_TQUIZ_TEXTQUESTION_FILEAREA, 'id');
		$question->annotate_files('mod_tquiz', MOD_TQUIZ_AUDIOQUESTION_FILEAREA, 'id');
		for($i=1;$i<=MOD_TQUIZ_MAXANSWERS;$i++){
			$question->annotate_files('mod_tquiz', MOD_TQUIZ_TEXTANSWER_FILEAREA.$i, 'id');
			$question->annotate_files('mod_tquiz', MOD_TQUIZ_AUDIOANSWER_FILEAREA.$i, 'id');
		}

        // Return the root element (choice), wrapped into standard activity structure.
        return $this->prepare_activity_structure($tquiz);
		

    }
}
