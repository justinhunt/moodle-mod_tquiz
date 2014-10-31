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


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/tquiz/forms.php');

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package mod_tquiz
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tquiz_renderer extends plugin_renderer_base {

    /**
     *
     */
    public function show_something($showtext) {
		$ret = $this->output->box_start();
		$ret .= $this->output->heading($showtext, 4, 'main');
		$ret .= $this->output->box_end();
        return $ret;
    }

	 /**
     *
     */
	public function show_intro($tquiz,$cm){
		$ret = "";
		if (trim(strip_tags($tquiz->intro))) {
			echo $this->output->box_start('mod_introbox');
			echo format_module_intro('tquiz', $tquiz, $cm->id);
			echo $this->output->box_end();
		}
	}
	
	  /**
     * Returns the header for the tquiz module
     *
     * @param lesson $tquiz a TQuiz Object.
     * @param string $currenttab current tab that is shown.
     * @param int    $question id of the question that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function header($tquiz, $cm, $currenttab = '', $questionid = null, $extrapagetitle = null) {
        global $CFG;

        $activityname = format_string($tquiz->name, true, $tquiz->course);
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname.": ".$activityname;
        } else {
            $title = $this->page->course->shortname.": ".$activityname.": ".$extrapagetitle;
        }

        // Build the buttons
        $context = context_module::instance($cm->id);

    /// Header setup
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
       // lesson_add_header_buttons($cm, $context, $extraeditbuttons, $lessonpageid);
        $output = $this->output->header();

        if (has_capability('mod/tquiz:manage', $context)) {
            $output .= $this->output->heading_with_help($activityname, 'overview', 'tquiz');

            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot.'/mod/tquiz/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        } else {
            $output .= $this->output->heading($activityname);
        }
		/*
        foreach ($tquiz->messages as $message) {
            $output .= $this->output->notification($message[0], $message[1], $message[2]);
        }
		*/

        return $output;
    }
	
	 /**
     * Return HTML to display add first page links
     * @param lesson $lesson
     * @return string
     */
 public function add_edit_page_links($tquiz) {
		global $CFG;
        $questionid = 0;

        $output = $this->output->heading(get_string("whatdonow", "tquiz"), 3);
        $links = array();

        $addmultichoicequestionurl = new moodle_url('/mod/tquiz/managequestions.php',
			array('id'=>$this->page->cm->id, 'questionid'=>$questionid, 'qtype'=>MOD_TQUIZ_QTYPE_MULTICHOICE));
        $links[] = html_writer::link($addmultichoicequestionurl, get_string('addmultichoicequestion', 'tquiz'));
        
        $addaudiochoicequestionurl = new moodle_url('/mod/tquiz/managequestions.php',
			array('id'=>$this->page->cm->id, 'questionid'=>$questionid, 'qtype'=>MOD_TQUIZ_QTYPE_AUDIOCHOICE));
        $links[] = html_writer::link($addaudiochoicequestionurl, get_string('addaudiochoicequestion', 'tquiz'));
		
		
        return $this->output->box($output.'<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }
	
/**
	 * Return the html table of homeworks for a group  / course
	 * @param array homework objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	function show_questions_list($questions,$cm){
	
		if(!$questions){
			return $this->output->heading(get_string('noquestions','tquiz'), 3, 'main');
		}
	
		$table = new html_table();
		$table->id = 'mod_tquiz_qpanel';
		$table->head = array(
			get_string('questionname', 'tquiz'),
			get_string('questiontype', 'tquiz'),
			get_string('actions', 'tquiz')
		);
		$table->headspan = array(1,1,3);
		$table->colclasses = array(
			'questionname', 'questiontitle', 'edit','preview','delete'
		);

		//sort by start date
		core_collator::asort_objects_by_property($questions,'timecreated',core_collator::SORT_NUMERIC);

		//loop through the homoworks and add to table
		foreach ($questions as $question) {
			$row = new html_table_row();
		
		
			$questionnamecell = new html_table_cell($question->name);	
			switch($question->qtype){
				case MOD_TQUIZ_QTYPE_MULTICHOICE:
					$questiontype = get_string('multichoice','tquiz');
					break;
				case MOD_TQUIZ_QTYPE_AUDIOCHOICE:
					$questiontype = get_string('audiochoice','tquiz');
					break;
				default:
			} 
			$questiontypecell = new html_table_cell($questiontype);
		
			$actionurl = '/mod/tquiz/managequestions.php';
			$editurl = new moodle_url($actionurl, array('id'=>$cm->id,'questionid'=>$question->id));
			$editlink = html_writer::link($editurl, get_string('editquestion', 'tquiz'));
			$editcell = new html_table_cell($editlink);
			
			$previewurl = new moodle_url($actionurl, array('id'=>$cm->id,'questionid'=>$question->id));
			$previewlink = html_writer::link($previewurl, get_string('previewquestion', 'tquiz'));
			$previewcell = new html_table_cell($previewlink);
		
			$deleteurl = new moodle_url($actionurl, array('id'=>$cm->id,'questionid'=>$question->id,'action'=>'confirmdelete'));
			$deletelink = html_writer::link($deleteurl, get_string('deletequestion', 'tquiz'));
			$deletecell = new html_table_cell($deletelink);

			$row->cells = array(
				$questionnamecell, $questiontypecell, $editcell, $previewcell, $deletecell
			);
			$table->data[] = $row;
		}

		return html_writer::table($table);

	}


  
}

