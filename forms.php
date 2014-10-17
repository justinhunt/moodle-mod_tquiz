<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Forms for Homework Block
 *
 * @package    mod_tquiz
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');



class mod_tquiz_quickadd_form extends moodleform {
	
	protected $action = 'quickadd';
	
    public function definition() {
        global $CFG, $USER, $OUTPUT, $COURSE;
        $strrequired = get_string('required');
        $mform = & $this->_form;
		
		
		//Activity Information
		$mods = get_array_of_activities($COURSE->id);
		$cmid = $this->_customdata['cmid'];
		$activityname ='';
		if($cmid){
			$activityname =$mods[$cmid]->name;
		 }
        $mform->addElement('static', 'activityname',  '',get_string('activitystyle','tquiz',$activityname));
        $mform->addElement('hidden', 'cmid', 0);
        $mform->setType('cmid', PARAM_INT);
        
		
		//show a group form
		$bmh = new mod_tquiz_manager($COURSE->id, $USER->id);
		$groups = $bmh->get_grouplist();
		$options =array();
		foreach($groups as $group){
			$options[$group->id] = $group->name;
		}
		$mform->addElement('select', 'groupid', get_string('selectgroup','tquiz'),$options);
		$mform->setType('groupid', PARAM_INT);


        //add the start date
        $mform->addElement('date_selector', 'startdate', get_string('startdate','tquiz'));
        $mform->setType('startdate', PARAM_INT); //what is the type for date?
		

		$mform->addElement('hidden', 'homeworkid', 0);
        $mform->setType('homeworkid', PARAM_INT);
		
		
		$mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);
		
		$mform->addElement('hidden', 'action', 'do' . $this->action);
        $mform->setType('action', PARAM_TEXT);

		//$mform->addElement('submit', 'submitbutton', get_string('do' . $this->action . '_label', 'tquiz'));
		$this->add_action_buttons(true,get_string('do' . $this->action . '_label', 'tquiz', $activityname));
    }
}

class mod_tquiz_add_question extends moodleform {
	
	protected $action = 'addquestion';
	
    public function definition() {
        global $CFG, $USER, $OUTPUT, $COURSE;
        $strrequired = get_string('required');
        $mform = & $this->_form;

		$groupid = $this->_customdata['groupid'];


        //add the course id (of the context)
        $mform->addElement('date_selector', 'startdate', get_string('startdate','tquiz'));
        $mform->setType('startdate', PARAM_INT); //what is the type for date?
		
		//is it better to use get_fast_modinfo() and get_module_types_names() ?
		$mods = get_array_of_activities($COURSE->id);
		$options =array();
		$config = get_config('tquiz');
		$homeworktypes =explode(',' ,$config->homeworktypes);
		foreach($mods as $mod){
			if(in_array($mod->mod,$homeworktypes)){
				$options[$mod->cm] = $mod->name;
			}
		}
		//print_r($options);
		$mform->addElement('select', 'cmid', get_string('homeworkactivity','tquiz'),$options);
        $mform->setType('cmid', PARAM_INT);
        
		$mform->addElement('hidden', 'homeworkid', 0);
        $mform->setType('homeworkid', PARAM_INT);
		
		$mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);
		
		
		$mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);
		
		$mform->addElement('hidden', 'action', 'do' . $this->action);
        $mform->setType('action', PARAM_TEXT);

		//$mform->addElement('submit', 'submitbutton', get_string('do' . $this->action . '_label', 'tquiz'));
		$this->add_action_buttons(true,get_string('do' . $this->action . '_label', 'tquiz'));
    }
	
	/*

    function validation($data, $files) {
        global $CFG;

        $errors = array();

        if (empty($this->_form->_submitValues['startdate'])) {
            $errors['startdate'] = get_string('nostartdate', 'tquiz');
        }
		if (empty($this->_form->_submitValues['cmid'])) {
            $errors['cmid'] = get_string('nocmid', 'tquiz');
        }
		if (empty($this->_form->_submitValues['groupid'])) {
            $errors['groupid'] = get_string('nogroupid', 'tquiz');
        }

        return $errors;
    }
*/
}
	
class mod_tquiz_edit_form extends mod_tquiz_add_form {

	protected $action = 'edit';
/*	
	public function definition_after_data() {
		parent::definition_after_data();
		//$homeworkid =   optional_param('homeworkid',0, PARAM_INT); //the id of the group
		$homeworkid=$this->_customdata['homeworkid'];
		if($homeworkid > 0){
			$bmh = new mod_tquiz_manager();
			$hdata = $bmh->get_homework($homeworkid);
		}

			$mform =& $this->_form;
			$homeworkid =& $mform->getElement('homeworkid');
			$startdate =& $mform->getElement('startdate');
			$cmid =& $mform->getElement('cmid');
			$courseid =& $mform->getElement('courseid');
			$groupid =& $mform->getElement('groupid');
			
			$homeworkid->setValue($hdata->id);
			$startdate->setValue($hdata->startdate);
			$cmid->setValue($hdata->cmid);
			$courseid->setValue($hdata->courseid);
			$groupid->setValue($hdata->groupid);

	}//end of function
	*/
}//end of class
	
class mod_tquiz_delete_form extends moodleform {

    public function definition() {
        global $CFG, $USER, $OUTPUT, $COURSE;
        $strrequired = get_string('required');
        $mform = & $this->_form;

		$groupid = $this->_customdata['groupid'];
	//	$groupname = groups_get_group_name($groupid);
		
      //  $mform->addElement('header', 'site', get_string('deletehomework', 'tquiz', $groupname));
		
		$mform->addElement('hidden', 'homeworkid', 0);
        $mform->setType('homeworkid', PARAM_INT);
		
		$mform->addElement('hidden', 'courseid', 0);
        $mform->setType('courseid', PARAM_INT);
		
		$mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);
		
		$mform->addElement('static', 'startdate', get_string('startdate','tquiz'));
		$mform->addElement('static', 'activityname', get_string('homeworkactivity','tquiz'));
		
		$mform->addElement('hidden', 'action', 'dodelete');
        $mform->setType('action', PARAM_TEXT);

		
		//$mform->addElement('submit', 'submitbutton', get_string('dodelete_label', 'tquiz'));
		$this->add_action_buttons(true,get_string('dodelete_label', 'tquiz'));
	}

}

class mod_tquiz_group_form extends moodleform {

    public function definition() {
        global $CFG, $USER, $OUTPUT, $COURSE;
        $strrequired = get_string('required');
        $mform = & $this->_form;
		$bmh = new mod_tquiz_manager($this->_customdata['courseid'], $USER->id);

		$mform->addElement('hidden', 'courseid', 0);
        $mform->setType('courseid', PARAM_INT);
		
		$groups = $bmh->get_grouplist();
		$options =array();
		foreach($groups as $group){
			$options[$group->id] = $group->name;
		}

		$mform->addElement('select', 'groupid', get_string('selectgroup','tquiz'),$options);
        $mform->setType('groupid', PARAM_INT);
	

		
		$mform->addElement('hidden', 'action', 'dogroup');
        $mform->setType('action', PARAM_TEXT);

		
		$mform->addElement('submit', 'submitbutton', get_string('dogroup_label', 'tquiz'));
		//$this->add_action_buttons(true,get_string('dogroup_label', 'tquiz'));
	}

}


