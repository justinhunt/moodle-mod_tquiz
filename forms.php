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
 * Forms for TQuiz Activity
 *
 * @package    mod_tquiz
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');

define('MOD_TQUIZ_NONE', 0);
define('MOD_TQUIZ_QTYPE_MULTICHOICE', 1);
define('MOD_TQUIZ_MULTICHOICE', 'multichoice');
define('MOD_TQUIZ_AUDIOQUESTION', 'audioquestion');
define('MOD_TQUIZ_AUDIOANSWER', 'audioanswer');
define('MOD_TQUIZ_AUDIOQUESTION_FILEAREA', 'audioquestion');
define('MOD_TQUIZ_AUDIOANSWER_FILEAREA', 'audioanswer');
define('MOD_TQUIZ_TEXTQUESTION', 'questiontext');
define('MOD_TQUIZ_TEXTANSWER', 'answertext');
define('MOD_TQUIZ_TEXTQUESTION_FILEAREA', 'questionarea');
define('MOD_TQUIZ_TEXTANSWER_FILEAREA', 'answerarea');

/**
 * Abstract class that question type's inherit from.
 *
 * This is the abstract class that add question type forms must extend.
 *
 * @abstract
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class tquiz_add_question_form_base extends moodleform {

    /**
     * This is the classic define that is used to identify this questiontype.
     * @var string
     */
    public $qtype;

    /**
     * The simple string that describes the question type e.g. truefalse, multichoice
     * @var string
     */
    public $qtypestring;

	
    /**
     * An array of options used in the htmleditor
     * @var array
     */
    protected $editoroptions = array();

	/**
     * An array of options used in the filemanager
     * @var array
     */
    protected $audiofilemanageroptions = array();
	
    /**
     * True if this is a standard question of false if it does something special.
     * Questions are standard questions, branch tables are not
     * @var bool
     */
    protected $standard = true;

    /**
     * Each question type can and should override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition() {}

    /**
     * Used to determine if this is a standard question or a special question
     * @return bool
     */
    public final function is_standard() {
        return (bool)$this->standard;
    }

    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        $mform = $this->_form;
        $this->editoroptions = $this->_customdata['editoroptions'];
		$this->audiofilemanageroptions = $this->_customdata['audiofilemanageroptions'];
	
        $mform->addElement('header', 'qtypeheading', get_string('createaquestion', 'tquiz', get_string($this->qtypestring, 'tquiz')));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'questionid');
        $mform->setType('questionid', PARAM_INT);

        if ($this->standard === true) {
            $mform->addElement('hidden', 'qtype');
            $mform->setType('qtype', PARAM_INT);
			
			$mform->addElement('hidden', 'order');
            $mform->setType('order', PARAM_INT);

            $mform->addElement('text', 'name', get_string('questiontitle', 'tquiz'), array('size'=>70));
            $mform->setType('name', PARAM_TEXT);
            $mform->addRule('name', get_string('required'), 'required', null, 'client');

            $mform->addElement('editor', MOD_TQUIZ_TEXTQUESTION . '_editor', get_string('questioncontents', 'tquiz'), null, $this->editoroptions);
            $mform->setType(MOD_TQUIZ_TEXTQUESTION . '_editor', PARAM_RAW);
            $mform->addRule(MOD_TQUIZ_TEXTQUESTION . '_editor', get_string('required'), 'required', null, 'client');
        }
		$mform->addElement('selectyesno', 'visible', get_string('visible'));
		
        $this->custom_definition();
		
		

		//add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('savequestion', 'tquiz'));

    }



    /**
     * Convenience function: Adds a score input element
     *
     * @param string $name
     * @param string|null $label
     * @param mixed $value The default value
     */
    protected final function add_score($name, $label=null, $value=null) {
        if ($label === null) {
            $label = get_string("score", "tquiz");
        }

        if (is_int($name)) {
            $name = "score[$name]";
        }
        $this->_form->addElement('text', $name, $label, array('size'=>5));
        $this->_form->setType($name, PARAM_INT);
        if ($value !== null) {
            $this->_form->setDefault($name, $value);
        }
        $this->_form->addHelpButton($name, 'score', 'tquiz');

        // Score is only used for custom scoring. Disable the element when not in use to stop some confusion.
        if (!$this->_customdata['tquiz']->custom) {
            $this->_form->freeze($name);
        }
    }
	
    protected final function add_audio_upload($name, $count=-1, $label = null, $required = false) {
		if($count>-1){
			$name = $name . $count ;
		}
		
		$this->_form->addElement('filemanager',
                           $name,
                           $label,
                           null,
						   $this->audiofilemanageroptions
                           );
		
	}

	protected final function add_audio_question_upload($label = null, $required = false) {
		return $this->add_audio_upload(MOD_TQUIZ_AUDIOQUESTION,-1,$label,$required);
	}
	protected final function add_audio_answer_upload($count,$label = null, $required = false) {
		return $this->add_audio_upload(MOD_TQUIZ_AUDIOANSWER,$count,$label,$required);
	}	
	
	
    /**
     * Convenience function: Adds an answer editor
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_answer($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('answer', 'tquiz');
        }
        $this->_form->addElement('editor', MOD_TQUIZ_TEXTANSWER . $count . '_editor', $label, array('rows'=>'4', 'columns'=>'80'), array('noclean'=>true));
        $this->_form->setDefault(MOD_TQUIZ_TEXTANSWER . $count . '_editor', array('text'=>'', 'format'=>FORMAT_MOODLE));
        if ($required) {
            $this->_form->addRule(MOD_TQUIZ_TEXTANSWER . $count . '_editor', get_string('required'), 'required', null, 'client');
        }
    }
    /**
     * Convenience function: Adds an response editor
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_response($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', 'tquiz');
        }
        $this->_form->addElement('editor', 'response_editor['.$count.']', $label, array('rows'=>'4', 'columns'=>'80'), array('noclean'=>true));
        $this->_form->setDefault('response_editor['.$count.']', array('text'=>'', 'format'=>FORMAT_MOODLE));
        if ($required) {
            $this->_form->addRule('response_editor['.$count.']', get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * A function that gets called upon init of this object by the calling script.
     *
     * This can be used to process an immediate action if required. Currently it
     * is only used in special cases by non-standard question types.
     *
     * @return bool
     */
    public function construction_override($questionid,  $tquiz) {
        return true;
    }
}

//this is the standard form for creating a multi choice question
class tquiz_add_question_form_multichoice extends tquiz_add_question_form_base {

    public $qtype = 'multichoice';
    public $qtypestring = 'multichoice';

    public function custom_definition() {

		/*
        $this->_form->addElement('checkbox', 'qoption', get_string('options', 'tquiz'), get_string('multianswer', 'tquiz'));
        $this->_form->setDefault('qoption', 0);
        $this->_form->addHelpButton('qoption', 'multianswer', 'tquiz');
		*/
		
		$this->add_audio_question_upload(get_string('audioquestionfile','tquiz'));
		$maxanswers = 4;
        for ($i = 1; $i <= $maxanswers; $i++) {
            $this->_form->addElement('header', 'answertitle'.$i, get_string('answer').' '. $i);
            $this->add_answer($i, null, true);
           // $this->add_response($i);
           // $this->add_jumpto($i, null, ($i == 0 ? LESSON_NEXTPAGE : LESSON_THISPAGE));
           // $this->add_score($i, null, ($i===0)?1:0);
        }
    }
}

//this is for responding to questions, just for reference
class tquiz_display_answer_form_multichoice_singleanswer extends moodleform {

    public function definition() {
        global $USER, $OUTPUT;
        $mform = $this->_form;
        $answers = $this->_customdata['answers'];
        $tquizid = $this->_customdata['tquizid'];
        $contents = $this->_customdata['contents'];
        if (array_key_exists('attempt', $this->_customdata)) {
            $attempt = $this->_customdata['attempt'];
        } else {
            $attempt = new stdClass();
            $attempt->answerid = null;
        }

        $mform->addElement('header', 'questionheader');

        $mform->addElement('html', $OUTPUT->container($contents, 'contents'));

        $hasattempt = false;
        $disabled = '';
        if (isset($USER->modattempts[$tquizid]) && !empty($USER->modattempts[$tquizid])) {
            $hasattempt = true;
            $disabled = array('disabled' => 'disabled');
        }

        $options = new stdClass;
        $options->para = false;
        $options->noclean = true;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'questionid');
        $mform->setType('questionid', PARAM_INT);

        $i = 0;
        foreach ($answers as $answer) {
            $mform->addElement('html', '<div class="answeroption">');
            $mform->addElement('radio','answerid',null,format_text($answer->answer, $answer->answerformat, $options),$answer->id, $disabled);
            $mform->setType('answer'.$i, PARAM_INT);
            if ($hasattempt && $answer->id == $USER->modattempts[$tquizid]->answerid) {
                $mform->setDefault('answerid', $USER->modattempts[$tquizid]->answerid);
            }
            $mform->addElement('html', '</div>');
            $i++;
        }

        if ($hasattempt) {
            $this->add_action_buttons(null, get_string("nextquestion", "tquiz"));
        } else {
            $this->add_action_buttons(null, get_string("submit", "tquiz"));
        }
    }

}