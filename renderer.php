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
require_once($CFG->dirroot.'/mod/tquiz/locallib.php');

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
	public function fetch_intro($tquiz,$cm){
		$ret = "";
		if (trim(strip_tags($tquiz->intro))) {
			$ret .= $this->output->box_start('mod_introbox');
			$ret .= format_module_intro('tquiz', $tquiz, $cm->id);
			$ret .= $this->output->box_end();
		}
		
		//start button
		$bigbuttonhtml = html_writer::tag('button','GO',  
		array('class'=>'mod_tquiz_bigbutton yui3-button',
		'id'=>'mod_tquiz_start_button','onclick'=>'M.mod_tquiz.helper.shownext()'));	
		$bigbuttoncontainer = html_writer::tag('div', $bigbuttonhtml  
			,array('class'=>'mod_tquiz_bigbutton_container'));
		
		$ret .= $bigbuttoncontainer;
		
		return html_writer::tag('div', $ret, array('class'=>'mod_tquiz_intro','id'=>'tquiz_intro_div'));
	}
	
		 /**
     *
     */
	public function fetch_feedback($tquiz,$cm, $context){
		$ret = "";
		if (trim(strip_tags($tquiz->feedback))) {
		$edoptions = tquiz_fetch_editor_options($context);
		//$edoptions = mod_tquiz_fetch_editor_options($COURSE,$context);
		$feedbacktext  = file_rewrite_pluginfile_urls($tquiz->feedback, 'pluginfile.php', $context->id, 
			'mod_tquiz', 'feedback', 0, 
			$edoptions);
		
		
			$ret .= $this->output->box_start('mod_introbox');
			$ret .= format_text($feedbacktext);
			$ret .= $this->output->box_end();
		}
		
		//start button
		/*
		$bigbuttonhtml = html_writer::tag('button','STARTY WARTY',  
		array('class'=>'mod_tquiz_bigbutton yui3-button',
		'id'=>'mod_tquiz_start_button','onclick'=>'M.mod_tquiz.helper.donext()'));	
		$bigbuttoncontainer = html_writer::tag('div', $bigbuttonhtml  
			,array('class'=>'mod_tquiz_bigbutton_container'));
		
		$ret .= $bigbuttoncontainer;
		*/
		return html_writer::tag('div', $ret, array('class'=>'mod_tquiz_feedback','id'=>'tquiz_feedback_div'));
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
     * Return HTML to display limited header
     */
      public function previewheader(){
      	return $this->output->header();
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
	function show_questions_list($questions,$tquiz,$cm){
	
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
			
			//$previewurl = new moodle_url($actionurl, array('id'=>$cm->id,'questionid'=>$question->id, 'action'=>'previewquestion'));
			//$previewlink = html_writer::link($previewurl, get_string('previewquestion', 'tquiz'));
			$previewlink = $this->fetch_preview_link($question->id,$tquiz->id);
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
	
	
	function fetch_preview_link($questionid, $tquizid){
		// print's a popup link to your custom page
		$link = new moodle_url('/mod/tquiz/preview.php',array('questionid'=>$questionid, 'tquizid'=>$tquizid));
		return  $this->output->action_link($link, get_string('preview','mod_tquiz'), 
			new popup_action('click', $link));
	
	}
	
	/**
	 * Return the html table of homeworks for a group  / course
	 * @param object question
	 * @param object course module
	 * @return string html of question
	 */
	function fetch_question_display($thequestion,$tquiz, $context){
			global $COURSE;
			//get question text div (easy)
			$questiontext  = html_writer::tag('div', $thequestion->{MOD_TQUIZ_TEXTQUESTION}, array('class' => 'mod_tquiz_questionbox'));
			$questiontext  = file_rewrite_pluginfile_urls($questiontext, 'pluginfile.php', $context->id, 
			'mod_tquiz', MOD_TQUIZ_TEXTQUESTION_FILEAREA, $thequestion->id, 
			mod_tquiz_fetch_editor_options($COURSE,$context));

			//get question audio div (not so easy)			
			$fs = get_file_storage();
			$files = $fs->get_area_files($context->id, 'mod_tquiz',MOD_TQUIZ_AUDIOQUESTION_FILEAREA,$thequestion->id);
			$audioplayer =false;
			$questionaudio='';
			foreach ($files as $file) {
				$filename = $file->get_filename();
				if($filename=='.'){continue;}
				$filepath = '/';//$file->get_filepath();
				$audiourl = moodle_url::make_pluginfile_url($context->id,'mod_tquiz',
						MOD_TQUIZ_AUDIOQUESTION_FILEAREA, $thequestion->id,
						$filepath, $filename);
				$audioplayer = html_writer::link($audiourl, $filename);
				break;
			}
			if($audioplayer){
				//$questionaudio =	$this->fetch_audio_button_player($audiourl,'question','question_' . $thequestion->id);
				 $questionaudio = html_writer::tag('div', $audioplayer, array('class' => 'tquiz_questionaudio yui3-button soundmanagerplayer'));
			}
			
			//return text + audio
			return format_text($questiontext . $questionaudio);
			
	}
	
	function fetch_audio_button_player($audiolink,$profile, $id){
		global $CFG;

		//Button template
		$bigbuttonhtml  = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'yui3-button mod_tquiz_big_button','id'=>'mod_tquiz_audiobutton_@@BUTTONID@@',
		  		'src'=>$CFG->wwwroot . '/mod/tquiz/pix/@@IMGSRC@@.png', 'onclick'=>'M.mod_tquiz.helper.answerclick(1)'));
				//'onclick'=>'M.mod_tquiz.helper.answerclick(@@QUESTIONID@@,@@ANSWERINDEX@@)'));	
				//'onclick'=>'M.mod_tquiz.sm2.handleaudioclick("@@BUTTONID@@")'));
		
		switch($profile){
			case 'question':
				$bigbuttonhtml  = str_replace('@@IMGSRC@@','questionplay',$bigbuttonhtml);
				$bigbuttonhtml  = str_replace('@@BUTTONID@@',$id,$bigbuttonhtml);
				break;
			case 'answer':
				$bigbuttonhtml  = str_replace('@@IMGSRC@@','questionplay',$bigbuttonhtml);
				$bigbuttonhtml  = str_replace('@@BUTTONID@@',$id,$bigbuttonhtml);
			default:
				break;
		
		}
		$sound= new stdClass();
		$sound->id = $id;
		$sound->url=$audiolink;
		$jsonsound =  json_encode($sound);
		$js='if(!m_mod_tquiz_sm2_sounds){var m_mod_tquiz_sm2_sounds = new Array();} m_mod_tquiz_sm2_sounds.push('.$jsonsound.');';
		$bigbuttonhtml .= html_writer::tag('script', $js ,array('type'=>'text/javascript'));
		
		$bigbuttoncontainer = html_writer::tag('div', $bigbuttonhtml  
			,array('class'=>'mod_tquiz_bigbutton_container'));
			
		//echo $bigbuttonhtml;
		return  $bigbuttoncontainer;
	
	}
	
		/**
	 * Return the html table of homeworks for a group  / course
	 * @param object question
	 * @param object course module
	 * @return string html of question
	 */
	function fetch_answers_display($thequestion,$tquiz, $context){
			global $COURSE;
			
			//GET url and hidden field for button forms.
		$actionurl = new moodle_url('/mod/tquiz/view.php');
	//	$h_action = '';//html_writer::tag('input',null,array('type'=>'hidden','name'=>'action', 'value'=>'add'));
		
		//Button template
		$bigbuttonhtml = html_writer::tag('button','@@BUTTONLABEL@@',  
		array('class'=>'mod_tquiz_bigbutton yui3-button',
		'id'=>'mod_tquiz_@@ANSWERINDEX@@_button','onclick'=>'M.mod_tquiz.helper.answerclick(@@QUESTIONID@@,@@ANSWERINDEX@@)'));	
		$bigbuttoncontainer = html_writer::tag('div', $bigbuttonhtml  
			,array('class'=>'mod_tquiz_bigbutton_container'));
		
	//	$bigbuttontemplate = html_writer::tag('form',$bigbuttoncontainer,array('action'=>$actionurl->out()));
	$bigbuttontemplate = html_writer::tag('div',$bigbuttoncontainer);
			
			
			$aindexes = array(1,2,3,4);
			$answers = array();
			foreach($aindexes as $aindex){
				switch($thequestion->qtype){
					case MOD_TQUIZ_QTYPE_MULTICHOICE:
						$theanswer = str_replace('@@BUTTONLABEL@@',$thequestion->{'answertext' . $aindex},$bigbuttontemplate);
						$theanswer = str_replace('@@QUESTIONID@@',$aindex,$theanswer);
						$theanswer = str_replace('@@ANSWERINDEX@@',$aindex,$theanswer);
						$answers[] =$theanswer;
						break;
					case MOD_TQUIZ_QTYPE_AUDIOCHOICE:
						//get question audio div (not so easy)			
						$fs = get_file_storage();
						$files = $fs->get_area_files($context->id, 'mod_tquiz',MOD_TQUIZ_AUDIOANSWER_FILEAREA . $aindex,$thequestion->id);
						$audioplayer =false;
						foreach ($files as $file) {
							$filename = $file->get_filename();
							if($filename=='.'){continue;}
							$filepath = '/';//$file->get_filepath();
							$audiourl = moodle_url::make_pluginfile_url($context->id,'mod_tquiz',
									MOD_TQUIZ_AUDIOANSWER_FILEAREA . $aindex, $thequestion->id,
									$filepath, $filename);
							//$audioplayer = html_writer::link($audiourl, $filename);
							$audioplayer =	$this->fetch_audio_button_player($audiourl,'answer','answer_' . $thequestion->id . '_' . $aindex);
							break;
						}
						if($audioplayer){
							$answers[] =  html_writer::tag('div', $audioplayer, array('class' => 'tquiz_answeraudio'));
						}
						break;
					default:
				} 			
			}//end of for each
			
			$allanswers =  implode(' ',$answers);
			
			//put a bounding box around the buttons to force them into a 2 x 2 centered grid
			$allanswerscontainer = html_writer::tag('div', $allanswers  
			,array('class'=>'mod_tquiz_allanswers_container'));
			
			
			return $allanswerscontainer; 
	}
	
	public function fetch_question_div($question, $tquiz,$modulecontext){
			$q = $this->fetch_question_display($question, $tquiz,$modulecontext);
			$q .= $this->fetch_answers_display($question, $tquiz,$modulecontext);
			return html_writer::tag('div', $q, array('class'=>'mod_tquiz_qdiv','id'=>'tquiz_qdiv_' . $question->id));
	}

}

