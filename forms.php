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

/**
 * Abstract class that page type's MUST inherit from.
 *
 * This is the abstract class that ALL add page type forms must extend.
 * You will notice that all but two of the methods this class contains are final.
 * Essentially the only thing that extending classes can do is extend custom_definition.
 * OR if it has a special requirement on creation it can extend construction_override
 *
 * @abstract
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class lesson_add_page_form_base extends moodleform {

    /**
     * This is the classic define that is used to identify this pagetype.
     * Will be one of LESSON_*
     * @var int
     */
    public $qtype;

    /**
     * The simple string that describes the page type e.g. truefalse, multichoice
     * @var string
     */
    public $qtypestring;

    /**
     * An array of options used in the htmleditor
     * @var array
     */
    protected $editoroptions = array();

    /**
     * True if this is a standard page of false if it does something special.
     * Questions are standard pages, branch tables are not
     * @var bool
     */
    protected $standard = true;

    /**
     * Each page type can and should override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition() {}

    /**
     * Used to determine if this is a standard page or a special page
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
        $editoroptions = $this->_customdata['editoroptions'];

        $mform->addElement('header', 'qtypeheading', get_string('createaquestionpage', 'lesson', get_string($this->qtypestring, 'lesson')));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'pageid');
        $mform->setType('pageid', PARAM_INT);

        if ($this->standard === true) {
            $mform->addElement('hidden', 'qtype');
            $mform->setType('qtype', PARAM_INT);

            $mform->addElement('text', 'title', get_string('pagetitle', 'lesson'), array('size'=>70));
            $mform->setType('title', PARAM_TEXT);
            $mform->addRule('title', get_string('required'), 'required', null, 'client');

            $this->editoroptions = array('noclean'=>true, 'maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$this->_customdata['maxbytes']);
            $mform->addElement('editor', 'contents_editor', get_string('pagecontents', 'lesson'), null, $this->editoroptions);
            $mform->setType('contents_editor', PARAM_RAW);
            $mform->addRule('contents_editor', get_string('required'), 'required', null, 'client');
        }

        $this->custom_definition();

        if ($this->_customdata['edit'] === true) {
            $mform->addElement('hidden', 'edit', 1);
            $mform->setType('edit', PARAM_BOOL);
            $this->add_action_buttons(get_string('cancel'), get_string('savepage', 'lesson'));
        } else if ($this->qtype === 'questiontype') {
            $this->add_action_buttons(get_string('cancel'), get_string('addaquestionpage', 'lesson'));
        } else {
            $this->add_action_buttons(get_string('cancel'), get_string('savepage', 'lesson'));
        }
    }

    /**
     * Convenience function: Adds a jumpto select element
     *
     * @param string $name
     * @param string|null $label
     * @param int $selected The page to select by default
     */
    protected final function add_jumpto($name, $label=null, $selected=LESSON_NEXTPAGE) {
        $title = get_string("jump", "lesson");
        if ($label === null) {
            $label = $title;
        }
        if (is_int($name)) {
            $name = "jumpto[$name]";
        }
        $this->_form->addElement('select', $name, $label, $this->_customdata['jumpto']);
        $this->_form->setDefault($name, $selected);
        $this->_form->addHelpButton($name, 'jumps', 'lesson');
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
            $label = get_string("score", "lesson");
        }

        if (is_int($name)) {
            $name = "score[$name]";
        }
        $this->_form->addElement('text', $name, $label, array('size'=>5));
        $this->_form->setType($name, PARAM_INT);
        if ($value !== null) {
            $this->_form->setDefault($name, $value);
        }
        $this->_form->addHelpButton($name, 'score', 'lesson');

        // Score is only used for custom scoring. Disable the element when not in use to stop some confusion.
        if (!$this->_customdata['lesson']->custom) {
            $this->_form->freeze($name);
        }
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
            $label = get_string('answer', 'lesson');
        }
        $this->_form->addElement('editor', 'answer_editor['.$count.']', $label, array('rows'=>'4', 'columns'=>'80'), array('noclean'=>true));
        $this->_form->setDefault('answer_editor['.$count.']', array('text'=>'', 'format'=>FORMAT_MOODLE));
        if ($required) {
            $this->_form->addRule('answer_editor['.$count.']', get_string('required'), 'required', null, 'client');
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
            $label = get_string('response', 'lesson');
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
     * is only used in special cases by non-standard page types.
     *
     * @return bool
     */
    public function construction_override($pageid, lesson $lesson) {
        return true;
    }
}

class lesson_add_page_form_multichoice extends lesson_add_page_form_base {

    public $qtype = 'multichoice';
    public $qtypestring = 'multichoice';

    public function custom_definition() {

        $this->_form->addElement('checkbox', 'qoption', get_string('options', 'lesson'), get_string('multianswer', 'lesson'));
        $this->_form->setDefault('qoption', 0);
        $this->_form->addHelpButton('qoption', 'multianswer', 'lesson');

        for ($i = 0; $i < $this->_customdata['lesson']->maxanswers; $i++) {
            $this->_form->addElement('header', 'answertitle'.$i, get_string('answer').' '.($i+1));
            $this->add_answer($i, null, ($i<2));
            $this->add_response($i);
            $this->add_jumpto($i, null, ($i == 0 ? LESSON_NEXTPAGE : LESSON_THISPAGE));
            $this->add_score($i, null, ($i===0)?1:0);
        }
    }
}

class lesson_display_answer_form_multichoice_singleanswer extends moodleform {

    public function definition() {
        global $USER, $OUTPUT;
        $mform = $this->_form;
        $answers = $this->_customdata['answers'];
        $lessonid = $this->_customdata['lessonid'];
        $contents = $this->_customdata['contents'];
        if (array_key_exists('attempt', $this->_customdata)) {
            $attempt = $this->_customdata['attempt'];
        } else {
            $attempt = new stdClass();
            $attempt->answerid = null;
        }

        $mform->addElement('header', 'pageheader');

        $mform->addElement('html', $OUTPUT->container($contents, 'contents'));

        $hasattempt = false;
        $disabled = '';
        if (isset($USER->modattempts[$lessonid]) && !empty($USER->modattempts[$lessonid])) {
            $hasattempt = true;
            $disabled = array('disabled' => 'disabled');
        }

        $options = new stdClass;
        $options->para = false;
        $options->noclean = true;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'pageid');
        $mform->setType('pageid', PARAM_INT);

        $i = 0;
        foreach ($answers as $answer) {
            $mform->addElement('html', '<div class="answeroption">');
            $mform->addElement('radio','answerid',null,format_text($answer->answer, $answer->answerformat, $options),$answer->id, $disabled);
            $mform->setType('answer'.$i, PARAM_INT);
            if ($hasattempt && $answer->id == $USER->modattempts[$lessonid]->answerid) {
                $mform->setDefault('answerid', $USER->modattempts[$lessonid]->answerid);
            }
            $mform->addElement('html', '</div>');
            $i++;
        }

        if ($hasattempt) {
            $this->add_action_buttons(null, get_string("nextpage", "lesson"));
        } else {
            $this->add_action_buttons(null, get_string("submit", "lesson"));
        }
    }

}