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
     * Returns a big button template for audio/video or text
     *
     * @param string the type of button ie image, audio or text 
     * @return string
     */
	public function fetch_bigbutton($type){
		global $CFG;
	
		switch($type){
			case 'image':
				$bigbuttonhtml  = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'mod_tquiz_big_button yui3-button mod_tquiz_@@SIZECLASS@@_button','id'=>'mod_tquiz_@@ID@@_button@',
		  		'src'=>$CFG->wwwroot . '/mod/tquiz/pix/@@IMGSRC@@.png', 'onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));
				break;
			
			case 'toggle':
			/*
				$bigbuttonhtml = html_writer::tag('button','@@CAPTION@@',  
				array('class'=>'mod_tquiz_togglebutton yui3-button mod_tquiz_@@SIZECLASS@@_button radio','value'=>'@@ANSWERINDEX@@',
				'id'=>'mod_tquiz_@@ID@@_button','onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));	
				break;
			*/
				$bigbuttonhtml  = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'mod_tquiz_big_button yui3-button mod_tquiz_@@SIZECLASS@@_button radio','value'=>'@@ANSWERINDEX@@','id'=>'mod_tquiz_@@ID@@_button@',
		  		'src'=>$CFG->wwwroot . '/mod/tquiz/pix/check.png', 'onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));
				break;
				
			case 'text':
				$bigbuttonhtml = html_writer::tag('button','@@CAPTION@@',  
				array('class'=>'mod_tquiz_bigbutton yui3-button mod_tquiz_@@SIZECLASS@@_button',
				'id'=>'mod_tquiz_@@ID@@_button','onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));	
				break;
				
			case 'submit':
				$bigbuttonhtml = html_writer::tag('button','@@CAPTION@@',  
				array('class'=>'mod_tquiz_bigbutton yui3-button yui3-button-disabled mod_tquiz_@@SIZECLASS@@_button',
				'id'=>'mod_tquiz_@@ID@@_button','onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));	
				break;
			
			
			
			case 'audio':
				$bigbuttonhtml  = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'mod_tquiz_big_button yui3-button mod_tquiz_@@SIZECLASS@@_button','id'=>'mod_tquiz_@@ID@@_button@',
		  		'src'=>$CFG->wwwroot . '/mod/tquiz/pix/@@IMGSRC@@.png', 'onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));
				break;
			
		}
		
		$bigbuttoncontainer = html_writer::tag('div', $bigbuttonhtml  
					,array('class'=>'mod_tquiz_bigbutton_container mod_tquiz_bigbutton_@@SIZECLASS@@_container'));
					
		return $bigbuttoncontainer;
	
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
			
		$gobutton = $this->fetch_bigbutton('text');
		$gobutton = str_replace('@@CAPTION@@',get_string('startquiz','tquiz'),$gobutton);
		$gobutton = str_replace('@@ID@@','tquiz' .$tquiz->id . '_',$gobutton);
		$gobutton = str_replace('@@ONCLICK@@','startquiz()',$gobutton);
		$gobutton = str_replace('@@SIZECLASS@@','start',$gobutton);
		$ret .= $gobutton;
		
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
		
			$ret .= $this->output->box_start('mod_feedbackbox');
			$ret .= format_text($feedbacktext);
			$ret .= $this->output->box_end();
		}

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

        $addtextchoicequestionurl = new moodle_url('/mod/tquiz/managequestions.php',
			array('id'=>$this->page->cm->id, 'questionid'=>$questionid, 'qtype'=>MOD_TQUIZ_QTYPE_TEXTCHOICE));
        $links[] = html_writer::link($addtextchoicequestionurl, get_string('addtextchoicequestion', 'tquiz'));
        
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
				case MOD_TQUIZ_QTYPE_TEXTCHOICE:
					$questiontype = get_string('textchoice','tquiz');
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
				$questionaudio =	$this->fetch_audio_button_player($audiourl,'question','question_' . $thequestion->id,$thequestion->id,0);
				// $questionaudio = html_writer::tag('div', $audioplayer, array('class' => 'tquiz_questionaudio yui3-button soundmanagerplayer'));
			}
			
			//return text + audio
			$ret = format_text($questiontext);
			//i know its horrible, just for now ...
			$ret .= '<br/>' . $questionaudio;
			return $ret;
			
	}
	
	function fetch_toggle_button($questionid,$answerid){
		global $CFG;
		$bigbuttonhtml = $this->fetch_bigbutton('toggle');
		return $bigbuttonhtml;
		
	}
	
	function fetch_audio_button_player($audiolink,$profile, $id,$questionid,$answerid){
		global $CFG;

		//'onclick'=>'M.mod_tquiz.helper.answerclick(@@QUESTIONID@@,@@ANSWERINDEX@@)'));	
		//'onclick'=>'M.mod_tquiz.sm2.handleaudioclick("@@BUTTONID@@")'));
		$bigbuttonhtml = $this->fetch_bigbutton('audio');
		
		switch($profile){
			case 'question':
				$bigbuttonhtml  = str_replace('@@IMGSRC@@','questionplay',$bigbuttonhtml);
				$bigbuttonhtml  = str_replace('@@ID@@',$id,$bigbuttonhtml);
				$bigbuttonhtml  = str_replace('@@ONCLICK@@',"audio_question_click('". $id ."')",$bigbuttonhtml);
				$bigbuttonhtml   = str_replace('@@SIZECLASS@@','audioquestion',$bigbuttonhtml);
				break;
			case 'answer':
				$bigbuttonhtml  = str_replace('@@IMGSRC@@','answerplay',$bigbuttonhtml);
				$bigbuttonhtml  = str_replace('@@ID@@',$id,$bigbuttonhtml);
				$bigbuttonhtml  = str_replace('@@ONCLICK@@',"audio_answer_click('". $id ."')",$bigbuttonhtml);
				$bigbuttonhtml   = str_replace('@@SIZECLASS@@','audioanswer',$bigbuttonhtml);
			default:
				break;
		
		}
		$sound= new stdClass();
		$sound->id = $id;
		$sound->questionid = $questionid;
		$sound->answerid = $answerid;
		$sound->url=$audiolink->out();
		$jsonsound =  json_encode($sound);
		$js='if(!m_mod_tquiz_sm2_sounds){var m_mod_tquiz_sm2_sounds = new Array();} m_mod_tquiz_sm2_sounds.push('.$jsonsound.');';
		$bigbuttonhtml .= html_writer::tag('script', $js ,array('type'=>'text/javascript'));
		
		return  $bigbuttonhtml;
	
	}
	
		/**
	 * Return the html table of homeworks for a group  / course
	 * @param object question
	 * @param object course module
	 * @return string html of question
	 */
	function fetch_answers_display($thequestion,$tquiz, $context){
			global $COURSE;

	$bigbuttoncontainer = $this->fetch_bigbutton('text');
	$bigbuttontemplate = html_writer::tag('div',$bigbuttoncontainer);
				
			$aindexes = array(1,2,3,4);
			$answers = array();
			foreach($aindexes as $aindex){
				switch($thequestion->qtype){
					case MOD_TQUIZ_QTYPE_TEXTCHOICE:
						$theanswer = str_replace('@@CAPTION@@',$thequestion->{'answertext' . $aindex},$bigbuttontemplate);
						$theanswer  = str_replace('@@ID@@','textanswerbutton_' . $thequestion->id . '_' . $aindex,$theanswer);
						$theanswer = str_replace('@@ONCLICK@@','text_answer_click(' . $thequestion->id . ',' . $aindex . ')',$theanswer);
						$theanswer = str_replace('@@ANSWERINDEX@@','answer' . $aindex . '_',$theanswer);
						$theanswer = str_replace('@@SIZECLASS@@','shorttextanswer',$theanswer);
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
							$audioplayer =	$this->fetch_audio_button_player($audiourl,'answer','audioanswerplayer_' . $thequestion->id . '_' . $aindex,$thequestion->id,$aindex);
							
							$togglebutton= $this->fetch_toggle_button($thequestion->id, $aindex);
							$togglebutton = str_replace('@@CAPTION@@','ok',$togglebutton);
							$togglebutton = str_replace('@@ID@@','audioanswerbutton_' . $thequestion->id . '_' . $aindex,$togglebutton);
							$togglebutton = str_replace('@@ONCLICK@@','donothing()',$togglebutton);
							$togglebutton = str_replace('@@ANSWERINDEX@@', $aindex ,$togglebutton);
							$togglebutton = str_replace('@@SIZECLASS@@','selectaudioanswer',$togglebutton);
							
							break;
						}
						if($audioplayer){
							$answers[] =  html_writer::tag('div', $audioplayer . $togglebutton, array('class' => 'mod_tquiz_answeraudio'));
						}
						break;
					default:
				} 			
			}//end of for each
			
			$allanswers =  implode(' ',$answers);
			
			//put a bounding box around the buttons to force them into a 2 x 2 centered grid
			switch($thequestion->qtype){
					case MOD_TQUIZ_QTYPE_TEXTCHOICE:
						$qtypeclass = 'mod_tquiz_allanswers_texttype_container';
						$submitbutton ='';
						break;
					case MOD_TQUIZ_QTYPE_AUDIOCHOICE:
						$qtypeclass = 'mod_tquiz_allanswers_audiotype_container mod_tquiz_togglegroup';
						
						$submitbutton = $bigbuttoncontainer = $this->fetch_bigbutton('submit');
						$submitbutton = str_replace('@@CAPTION@@',get_string('ok','tquiz'),$submitbutton);
						$submitbutton  = str_replace('@@ID@@','submitbutton_' . $thequestion->id,$submitbutton);
						$submitbutton = str_replace('@@ONCLICK@@','submitbutton_click(' . $thequestion->id . ')',$submitbutton);
						$submitbutton = str_replace('@@SIZECLASS@@','submitanswer',$submitbutton);
						break;
			}
			
			$allanswerscontainer = html_writer::tag('div', $allanswers 
			,array('class'=>'mod_tquiz_allanswers_container ' . $qtypeclass ,'id'=>'mod_tquiz_allanswers_container_' . $thequestion->id));
			
			//add submit button
			$allanswerscontainer .= $submitbutton;
			
			//hidden answers div
			$hiddenanswerscontainer = html_writer::tag('div','' ,array('class'=>'mod_tquiz_hiddenanswers_container',
				'id'=>'mod_tquiz_hiddenanswers_container_' . $thequestion->id));
			
			return $hiddenanswerscontainer . $allanswerscontainer; 
	}
	
	public function fetch_question_div($question, $tquiz,$modulecontext){
			$q = $this->fetch_question_display($question, $tquiz,$modulecontext);
			$q .= $this->fetch_answers_display($question, $tquiz,$modulecontext);
			return html_writer::tag('div', $q, array('class'=>'mod_tquiz_qdiv','id'=>'tquiz_qdiv_' . $question->id));
	}

}

