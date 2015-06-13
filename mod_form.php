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
 * The main tquiz configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_tquiz
 * @copyright  COPYRIGHTNOTICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('lib.php');

/**
 * Module instance settings form
 */
class mod_tquiz_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('tquizname', 'tquiz'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'tquizname', 'tquiz');

        // Adding the standard "intro" and "introformat" fields
          // Adding the standard "intro" and "introformat" fields
        if($CFG->version < 2015051100){
        	$this->add_intro_editor();
        }else{
        	$this->standard_intro_elements();
		}
		
		//Add a feedback form
		$edfield = 'feedback';
		$editoroptions = tquiz_fetch_editor_options($this->context);
		$editorname= $edfield . '_editor';
        $mform->addElement('editor', $editorname, get_string($edfield, 'tquiz'),array('rows' => 10),$editoroptions);
        $mform->setType($editorname, PARAM_RAW);
		$mform->addRule($editorname, get_string('required'), 'required', null, 'client');

		
        //-------------------------------------------------------------------------------
        // Adding the tquiz time limit field
         $mform->addElement('duration', 'timelimit', get_string('timelimit', 'tquiz')); 
		 $mform->addElement('selectyesno', 'shufflequestions', get_string('shufflequestions', 'tquiz'));

		$options =array(0=>get_string('unlimited'),1=>1,2=>2,3=>3,4=>4,5=>5);
        $mform->addElement('select', 'attemptsallowed', get_string('attemptsallowed', 'tquiz'), $options,array('size'=>'1'));
        $mform->setType('attemptsallowed', PARAM_INT);
		$mform->setDefault('attemptsallowed', '1');
        $mform->addRule('attemptsallowed', null, 'required', null, 'client');


        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
    
    
    function data_preprocessing(&$form_data) {
    
    	 if ($this->current->instance) {
			$editoroptions = tquiz_fetch_editor_options($this->context);
			$itemid = 0;
			$form_data = file_prepare_standard_editor((object)$form_data, 'feedback', $editoroptions, $this->context,
								 'mod_tquiz','feedback', $itemid);
		}

    }
    
}
