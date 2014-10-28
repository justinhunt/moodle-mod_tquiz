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
 * Action for adding/editing a tquiz question. 
 *
 * @package mod_tquiz
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/tquiz/locallib.php');

// first get the preceeding page
$questionid = required_param('questionid', PARAM_INT);
$id     = required_param('id', PARAM_INT);         // Course Module ID
$qtype  = optional_param('qtype', 0, PARAM_INT);
$edit   = optional_param('edit', false, PARAM_BOOL);

$cm = get_coursemodule_from_id('tquiz', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$tquiz = new tquiz($DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST));

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/tquiz:edit', $context);

$PAGE->set_url('/mod/tquiz/editquestion.php', array('questionid'=>$questionid, 'id'=>$id, 'qtype'=>$qtype));

if ($edit) {
    $question = $DB->get_record('tquiz_question', array('id'=>$questionid,'tquiz' => $cm->instance), '*', MUST_EXIST);
    $qtype = $question->qtype;
    $edit = true;
} else {
    $edit = false;
}

$editoroptions = array('noclean'=>true, 'maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes);

// If the previous page was the Question type selection form, this form
// will have a different name (e.g. _qf__lesson_add_page_form_selection
// versus _qf__lesson_add_page_form_multichoice). This causes confusion
// in moodleform::_process_submission because the array key check doesn't
// tie up with the current form name, which in turn means the "submitted"
// check ends up evaluating as false, thus it's not possible to check whether
// the Question type selection was cancelled. For this reason, a dummy form
// is created here solely to check whether the selection was cancelled.
if ($qtype) {
    $mformdummy = $manager->get_page_form(0, array('editoroptions'=>$editoroptions, 'jumpto'=>$jumpto, 'lesson'=>$lesson, 'edit'=>$edit, 'maxbytes'=>$PAGE->course->maxbytes));
    if ($mformdummy->is_cancelled()) {
        redirect("$CFG->wwwroot/mod/lesson/edit.php?id=$id");
        exit;
    }
}

$mform = $manager->get_page_form($qtype, array('editoroptions'=>$editoroptions, 'jumpto'=>$jumpto, 'lesson'=>$lesson, 'edit'=>$edit, 'maxbytes'=>$PAGE->course->maxbytes));

if ($mform->is_cancelled()) {
    redirect("$CFG->wwwroot/mod/tquiz/edit.php?id=$id");
    exit;
}

if ($edit) {
    //$data = $question->properties();
    //$data = new stdClass;
	$data = $question;
	$data->questionid = $question->id;
    $data->id = $cm->id;
    $editoroptions['context'] = $context;
    $data = file_prepare_standard_editor($data, 'contents', $editoroptions, $context, 'mod_tquiz', 'page_contents',  $editquestion->id);
    $mform->set_data($data);
    $PAGE->navbar->add(get_string('edit'), new moodle_url('/mod/tquiz/edit.php', array('id'=>$id)));
    $PAGE->navbar->add(get_string('editingquestion', 'tquiz', get_string($mform->qtypestring, 'tquiz')));
} else {
    // Give the page type being created a chance to override the creation process
    // this is used by endofbranch, cluster, and endofcluster to skip the creation form.
    // IT SHOULD ALWAYS CALL require_sesskey();
    $mform->construction_override($questionid, $lesson);

    $data = new stdClass;
    $data->id = $cm->id;
    $data->questionid = $questionid;
    if ($qtype) {
        //TODO: the handling of form for the selection of question type is a bloody hack! (skodak)
        $data->qtype = $qtype;
    }
    $data = file_prepare_standard_editor($data, 'contents', $editoroptions, null);
    $mform->set_data($data);
    $PAGE->navbar->add(get_string('addingquestion', 'tquiz'), $PAGE->url);
    if ($qtype !== 'unknown') {
        $PAGE->navbar->add(get_string($mform->qtypestring, 'tquiz'));
    }
}

if ($data = $mform->get_data()) {
    require_sesskey();
    if ($edit) {
        $data->tquiz = $data->id;
        $data->id = $data->questionid;
        unset($data->questionid);
        unset($data->edit);
        $editquestion->update($data, $context, $PAGE->course->maxbytes);
    } else {
        $editquestion = lesson_page::create($data, $lesson, $context, $PAGE->course->maxbytes);
    }
    redirect(new moodle_url('/mod/tquiz/edit.php', array('id'=>$cm->id)));
}

$renderer = $PAGE->get_renderer('mod_tquiz');
echo $renderer->header($tquiz, $cm, '', null, get_string('edit', 'tquiz'));
$mform->display();
echo $renderer->footer();