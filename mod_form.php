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
        $this->add_intro_editor();

		
		//Add a feedback form
		$edfield = 'feedback';
		$editoroptions = tquiz_fetch_editor_options($this->context);
		$editorname= $edfield . '_editor';
        $mform->addElement('editor', $editorname, get_string($edfield, 'tquiz'),array('rows' => 10),$editoroptions);
        $mform->setType($editorname, PARAM_RAW);
		$mform->addRule($editorname, get_string('required'), 'required', null, 'client');

		
        //-------------------------------------------------------------------------------
        // Adding the rest of tquiz settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic
        $mform->addElement('static', 'label1', 'tquizsettings', get_string('tquizsettings', 'tquiz'));
        $mform->addElement('text', 'someinstancesetting', get_string('someinstancesetting', 'tquiz'), array('size'=>'64'));
        $mform->addRule('someinstancesetting', null, 'required', null, 'client');
        $mform->setType('someinstancesetting', PARAM_TEXT);

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
