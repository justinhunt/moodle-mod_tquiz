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
			
				$bigbuttonhtml = html_writer::tag('button','<i class="fa fa-check fa-2x"></i>',  
				array('class'=>'mod_tquiz_bigbutton yui3-button mod_tquiz_@@SIZECLASS@@_button radio','value'=>'@@ANSWERINDEX@@',
				'id'=>'mod_tquiz_@@ID@@_button','onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));	
				break;
			
			/*
				$bigbuttonhtml  = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'mod_tquiz_big_button yui3-button mod_tquiz_@@SIZECLASS@@_button radio','value'=>'@@ANSWERINDEX@@','id'=>'mod_tquiz_@@ID@@_button',
		  		'src'=>$CFG->wwwroot . '/mod/tquiz/pix/check.png', 'onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));
				break;
			*/	
				
			case 'text':
				$bigbuttonhtml = html_writer::tag('button','@@CAPTION@@',  
				array('class'=>'mod_tquiz_bigbutton yui3-button mod_tquiz_@@SIZECLASS@@_button',
				'id'=>'mod_tquiz_@@ID@@_button','onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));	
				break;
				
			case 'textanswer':
				$bigbuttonhtml = html_writer::tag('button','@@CAPTION@@',  
				array('class'=>'mod_tquiz_bigbutton yui3-button mod_tquiz_@@SIZECLASS@@_button',
				'id'=>'mod_tquiz_@@ID@@_button','onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));	
				break;
				
			case 'submit':
				$bigbuttonhtml = html_writer::tag('button','@@CAPTION@@',  
				array('class'=>'mod_tquiz_bigbutton yui3-button yui3-button-disabled mod_tquiz_@@SIZECLASS@@_button',
				'id'=>'mod_tquiz_@@ID@@_button','onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));	
				break;
			
			
			
			case 'audioimage':
				$bigbuttonhtml  = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'mod_tquiz_big_button yui3-button mod_tquiz_@@SIZECLASS@@_button','id'=>'mod_tquiz_@@ID@@_button@',
		  		'src'=>$CFG->wwwroot . '/mod/tquiz/pix/@@IMGSRC@@.png', 'onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));
				break;
			
			case 'audiofa':
				$bigbuttonhtml = html_writer::tag('button','<i class="fa @@IMGSRC@@ fa-2x"></i>',  
				array('class'=>'mod_tquiz_bigbutton yui3-button mod_tquiz_@@SIZECLASS@@_button',
				'id'=>'mod_tquiz_@@ID@@_button','onclick'=>'M.mod_tquiz.helper.@@ONCLICK@@'));	
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
	
	public function fetch_countdowntimer() {
		return html_writer::tag('div', get_string('timeleft', 'tquiz') . ' ' .
			html_writer::tag('span', '', array('id' => 'tquiz-time-left')),
			array('id' => 'tquiz-timer', 'role' => 'timer',
				'aria-atomic' => 'true', 'aria-relevant' => 'text'));
	}
    
    /**
     * Return HTML to display limited header
     */
      public function notabsheader(){
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
	/*
	function show_attempts_header($tquiz,$cm){
		global $DB;
		$ret = $this->output->heading(get_string('showingallattempts','tquiz'), 3, 'main');
		return $ret;
		
	}
	*/
	
	/**
	 * Return the html table of attempts
	 * @param array attempt objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	 /*
	function show_attempts_list($attempts,$tquiz,$cm){
		global $DB;
		$ret="";
		
		if(!$attempts){
			return $this->output->heading(get_string('noattempts','tquiz'), 3, 'main');
		}else{
			$deleteallbutton = new single_button(
				new moodle_url('/mod/tquiz/manageattempts.php',array('id'=>$cm->id,'action'=>'confirmdeleteall')), 
				get_string('deleteallattempts','tquiz'), 'get');
				$ret .=  html_writer::div( $this->render($deleteallbutton) ,'mod_tquiz_actionbuttons');
		}
	
		$table = new html_table();
		$table->id = 'mod_tquiz_attemptspanel';
		$table->head = array(
			get_string('starttime', 'tquiz'),
			get_string('username', 'tquiz'),
			get_string('status', 'tquiz'),
			get_string('actions', 'tquiz')
		);
		$table->headspan = array(1,1,1,3);
		$table->colclasses = array(
			'starttime', 'username','status','details','logs','delete'
		);

		//sort by start date
		core_collator::asort_objects_by_property($attempts,'timecreated',core_collator::SORT_NUMERIC);
		$users =array();
		//loop through the attempts and add to table
		foreach ($attempts as $attempt) {
			$row = new html_table_row();
			//start time cell
			$starttimecell = new html_table_cell(date("Y-m-d H:i:s",$attempt->timecreated));
			
			//fullname cell
			if(array_key_exists($attempt->userid,$users)){
				$user = $users[$attempt->userid];
				$fullname=fullname($user);
			}else{	
				$user=$DB->get_record('user',array('id'=>$attempt->userid));
				if($user){
					$users[$attempt->userid]=$user;
					$fullname=fullname($user);
				}else{
					$fullname='unknown';
				}
			}
			$usernamecell = new html_table_cell($fullname);
			
			//attempt status cell
			$statuscell =  new html_table_cell($attempt->status);
			
			//view attempt report cell
			$actionurl = '/mod/tquiz/reports.php';
			$detailsurl = new moodle_url($actionurl, array('id'=>$cm->id,'n'=>$tquiz->id,'report'=>'attempt','userid'=>$user->id,'attemptid'=>$attempt->id));
			$detailslink = html_writer::link($detailsurl, get_string('viewreport', 'tquiz'));
			$detailscell = new html_table_cell($detailslink);
		
			//manageattempts link ->log
			$actionurl = '/mod/tquiz/manageattempts.php';
			$logsurl = new moodle_url($actionurl, array('id'=>$cm->id,'attemptid'=>$attempt->id));
			$logslink = html_writer::link($logsurl, get_string('logs', 'tquiz'));
			$logscell = new html_table_cell($logslink);

			//manageattempts link ->delete
			$actionurl = '/mod/tquiz/manageattempts.php';
			$deleteurl = new moodle_url($actionurl, array('id'=>$cm->id,'attemptid'=>$attempt->id,'action'=>'confirmdelete'));
			$deletelink = html_writer::link($deleteurl, get_string('deleteattempt', 'tquiz'));
			$deletecell = new html_table_cell($deletelink);

			$row->cells = array(
				$starttimecell, $usernamecell, $statuscell, $detailscell, $logscell, $deletecell
			);
			$table->data[] = $row;
		}
		$ret .= html_writer::table($table);
		return $ret;

	}
	
	function show_attempts_link($cmid){
		// print's a popup link to your custom page
		$link = new moodle_url('/mod/tquiz/reports.php',array('id'=>$cmid));
		return  html_writer::link($link, get_string('returntoattemptsmanager','mod_tquiz'));
	}
	*/
	
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
			$questiontext  = html_writer::tag('div',get_string('question','tquiz'), array('class' => 'mod_tquiz_questionheading'));
			$questiontext  .= html_writer::tag('div', $thequestion->{MOD_TQUIZ_TEXTQUESTION}, array('class' => 'mod_tquiz_questionbox'));
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
	
	function fetch_progressbar(){
		return html_writer::tag('div','', array('class' => 'mod_tquiz_progressbar', 'id'=> 'mod_tquiz_progressbar'));
	}
	
	function fetch_audio_button_player($audiolink,$profile, $id,$questionid,$answerid){
		global $CFG;
		
		/*$bigbuttonhtml = $this->fetch_bigbutton('audioimage');
		$qimage='questionplay';
		$aimage='answerplay';
		*/
		$bigbuttonhtml = $this->fetch_bigbutton('audiofa');
		$qimage='fa-rotate-right';
		$aimage='fa-play-circle';
		
		switch($profile){
			case 'question':
				$bigbuttonhtml  = str_replace('@@IMGSRC@@',$qimage,$bigbuttonhtml);
				$bigbuttonhtml  = str_replace('@@ID@@',$id,$bigbuttonhtml);
				$bigbuttonhtml  = str_replace('@@ONCLICK@@',"audio_question_click('". $id ."')",$bigbuttonhtml);
				$bigbuttonhtml   = str_replace('@@SIZECLASS@@','audioquestion',$bigbuttonhtml);
				break;
			case 'answer':
				$bigbuttonhtml  = str_replace('@@IMGSRC@@',$aimage,$bigbuttonhtml);
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
				
			$aindexes = array();
			for ($i=1;$i<=$thequestion->answercount;$i++){
				$aindexes[]=$i;
			}
			$answers = array();
			foreach($aindexes as $aindex){
				switch($thequestion->qtype){
					case MOD_TQUIZ_QTYPE_TEXTCHOICE:
						switch($thequestion->answerwidth){
							case 0: $sizeclass="shorttextanswer";break;
							case 1: $sizeclass="mediumtextanswer";break;
							case 2: $sizeclass="longtextanswer";break;
							default:
								$sizeclass="shorttextanswer";
						}
						$theanswer = str_replace('@@CAPTION@@',$thequestion->{'answertext' . $aindex},$bigbuttontemplate);
						$theanswer  = str_replace('@@ID@@','textanswerbutton_' . $thequestion->id . '_' . $aindex,$theanswer);
						$theanswer = str_replace('@@ONCLICK@@','text_answer_click(' . $thequestion->id . ',' . $aindex . ')',$theanswer);
						$theanswer = str_replace('@@ANSWERINDEX@@','answer' . $aindex . '_',$theanswer);
						$theanswer = str_replace('@@SIZECLASS@@',$sizeclass,$theanswer);
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
							//$togglebutton = str_replace('@@CAPTION@@','ok',$togglebutton);
							$togglebutton = str_replace('@@CAPTION@@','',$togglebutton);
							$togglebutton = str_replace('@@ID@@','audioanswer' . $thequestion->id . '_' . $aindex,$togglebutton);
							$togglebutton = str_replace('@@ONCLICK@@','selectaudioanswer_click('. $thequestion->id . ','. $aindex. ')',$togglebutton);
							//$togglebutton = str_replace('@@ONCLICK@@','donothing('. $thequestion->id . ','. $aindex. ')',$togglebutton);
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
			
			//if we need to shuffle .... ok
			if($thequestion->shuffleanswers){
				shuffle($answers);
			}
			//turn array into string
			$allanswers =  implode(' ',$answers);
			
			//put a bounding box around the buttons to force them into a 2 x 2 centered grid
			switch($thequestion->qtype){
					case MOD_TQUIZ_QTYPE_TEXTCHOICE:
						$qtypeclass = 'mod_tquiz_allanswers_texttype_container';
						$submitbutton ='';
						break;
					case MOD_TQUIZ_QTYPE_AUDIOCHOICE:
						$qtypeclass = 'mod_tquiz_allanswers_audiotype_container mod_tquiz_togglegroup';
						
						$submitbutton = $this->fetch_bigbutton('submit');
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
			
			$answersheading = html_writer::tag('div',get_string('answers','tquiz'), array('class' => 'mod_tquiz_answersheading'));
			return $answersheading . $hiddenanswerscontainer . $allanswerscontainer; 
	}
	
	public function fetch_question_div($question, $tquiz,$modulecontext){
			$q = $this->fetch_question_display($question, $tquiz,$modulecontext);
			$q .= $this->fetch_answers_display($question, $tquiz,$modulecontext);
			return html_writer::tag('div', $q, array('class'=>'mod_tquiz_qdiv','id'=>'tquiz_qdiv_' . $question->id));
	}

}


/**
 * Renderer for tquiz reports.
 *
 * @package    mod_tquiz
 * @copyright  2014 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tquiz_report_renderer extends plugin_renderer_base {


	public function render_reportmenu($tquiz,$cm, $questions) {
		
		$allattempts = new single_button(
			new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'allattempts')), 
			get_string('attemptsmanager','tquiz'), 'get');
		/*
		$allsummary = new single_button(
			new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'summary')), 
			get_string('allsummary','tquiz'), 'get');
		*/
		$allusers = new single_button(
			new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'allusers')), 
			get_string('allusers','tquiz'), 'get');
			
		$ret = html_writer::div( $this->render($allattempts) . $this->render($allusers) ,'mod_tquiz_listbuttons');
		
		foreach($questions as $question){	
			$qdetails = new single_button(
				new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'questiondetails', 'questionid'=>$question->id)), 
				get_string('questiondetails','tquiz', $question->name), 'get');
			/*
			$qsummary= new single_button(
				new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'questionsummary', 'questionid'=>$question->id)), 
				get_string('questionsummary','tquiz', $question->name), 'get');
				
			$ret .= html_writer::div( $this->render($qsummary) . $this->render($qdetails),'mod_tquiz_listbuttons');
			*/
			$ret .= html_writer::div( $this->render($qdetails),'mod_tquiz_listbuttons');
		}

		return $ret;
	}


	public function render_reporttitle_html($course,$username) {
		$ret = $this->output->heading(format_string($course->fullname),2);
		$ret .= $this->output->heading(get_string('reporttitle','tquiz',$username),3);
		return $ret;
	}

	public function render_empty_section_html($sectiontitle) {
		global $CFG;
		return $this->output->heading(get_string('nodataavailable','tquiz'),3);
	}
	
	public function render_exportbuttons_html($cm,$formdata,$showreport){
		//convert formdata to array
		$formdata = (array) $formdata;
		$formdata['id']=$cm->id;
		$formdata['report']=$showreport;
		
		$formdata['format']='pdf';
		$pdf = new single_button(
			new moodle_url('/mod/tquiz/reports.php',$formdata),
			get_string('exportpdf','tquiz'), 'get');
		
		$formdata['format']='csv';
		$excel = new single_button(
			new moodle_url('/mod/tquiz/reports.php',$formdata), 
			get_string('exportexcel','tquiz'), 'get');

		//return html_writer::div( $this->render($pdf) . $this->render($excel),'mod_tquiz_actionbuttons');
		return html_writer::div( $this->render($excel),'mod_tquiz_actionbuttons');
	}
	
	public function render_continuebuttons_html($course){
		$backtocourse = new single_button(
			new moodle_url('/course/view.php',array('id'=>$course->id)), 
			get_string('backtocourse','tquiz'), 'get');
		
		$selectanother = new single_button(
			new moodle_url('/mod/tquiz/index.php',array('id'=>$course->id)), 
			get_string('selectanother','tquiz'), 'get');
			
		return html_writer::div($this->render($backtocourse) . $this->render($selectanother),'tquiz_listbuttons');
	}
	
	public function render_section_csv($sectiontitle, $report, $head, $rows, $fields) {

        // Use the sectiontitle as the file name. Clean it and change any non-filename characters to '_'.
        $name = clean_param($sectiontitle, PARAM_FILE);
        $name = preg_replace("/[^A-Z0-9]+/i", "_", trim($name));
		$quote = '"';
		$delim= ",";//"\t";
		$newline = "\r\n";

		header("Content-Disposition: attachment; filename=$name.csv");
		header("Content-Type: text/comma-separated-values");

		//echo header
		$heading="";	
		foreach($head as $headfield){
			$heading .= $quote . $headfield . $quote . $delim ;
		}
		echo $heading. $newline;
		
		//echo data rows
        foreach ($rows as $row) {
			$datarow = "";
			foreach($fields as $field){
				$datarow .= $quote . $row->{$field} . $quote . $delim ;
			}
			 echo $datarow . $newline;
		}
        exit();
        break;
	}

	public function render_delete_allattempts($cm){
		$deleteallbutton = new single_button(
				new moodle_url('/mod/tquiz/manageattempts.php',array('id'=>$cm->id,'action'=>'confirmdeleteall')), 
				get_string('deleteallattempts','tquiz'), 'get');
		$ret =  html_writer::div( $this->render($deleteallbutton) ,'mod_tquiz_actionbuttons');
		return $ret;
	}
	
	public function render_section_html($sectiontitle, $report, $head, $rows, $fields) {
		global $CFG;
		if(empty($rows)){
			return $this->render_empty_section_html($sectiontitle);
		}
		
		//set up our table and head attributes
		$tableattributes = array('class'=>'generaltable tquiz_table');
		$headrow_attributes = array('class'=>'tquiz_headrow');
		
		$htmltable = new html_table();
		$htmltable->attributes = $tableattributes;
		
		
		$htr = new html_table_row();
		$htr->attributes = $headrow_attributes;
		foreach($head as $headcell){
			$htr->cells[]=new html_table_cell($headcell);
		}
		$htmltable->data[]=$htr;
		
		foreach($rows as $row){
			$htr = new html_table_row();
			//set up descrption cell
			$cells = array();
			foreach($fields as $field){
				$cell = new html_table_cell($row->{$field});
				$cell->attributes= array('class'=>'tquiz_cell_' . $report . '_' . $field);
				$htr->cells[] = $cell;
			}

			$htmltable->data[]=$htr;
		}
		$html = $this->output->heading($sectiontitle, 4);
		$html .= html_writer::table($htmltable);
		return $html;
		
	}
	
	function show_reports_footer($tquiz,$cm, $formdata,$showreport){
		// print's a popup link to your custom page
		$link = new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id));
		$ret =  html_writer::link($link, get_string('returntoreports','mod_tquiz'));
		$ret .= $this->render_exportbuttons_html($cm,$formdata,$showreport);
		return $ret;
	}

}


