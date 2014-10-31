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
 * Provides the interface for overall authoring of lessons
 *
 * @package mod_tquiz
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
//require_once($CFG->dirroot.'/mod/lesson/locallib.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('tquiz', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
//$tquiz = new tquiz($DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST));
$tquiz = $DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST);
$questions = $DB->get_records('tquiz_questions',array('tquiz'=>$tquiz->id));

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
//require_capability('mod/lesson:manage', $context);

//$mode    = optional_param('mode', get_user_preferences('lesson_view', 'collapsed'), PARAM_ALPHA);
$mode='edit';
$PAGE->set_url('/mod/tquiz/edit.php', array('id'=>$cm->id,'mode'=>$mode));
/*
if ($mode != get_user_preferences('lesson_view', 'collapsed') && $mode !== 'single') {
    set_user_preference('lesson_view', $mode);
}
*/

$renderer = $PAGE->get_renderer('mod_tquiz');
$PAGE->navbar->add(get_string('edit'));
echo $renderer->header($tquiz, $cm, $mode, null, get_string('edit', 'tquiz'));


    // There are no questions; give teacher some options
    require_capability('mod/tquiz:edit', $context);
    echo $renderer->add_edit_page_links($tquiz);

/*
    switch ($mode) {
        case 'collapsed':
           // echo $renderer->display_edit_collapsed($lesson, $lesson->firstpageid);
            break;
        case 'single':
            $pageid =  required_param('pageid', PARAM_INT);
            $PAGE->url->param('pageid', $pageid);
          //  echo $renderer->display_edit_full($lesson, $singlepage->id, $singlepage->prevpageid, true);
            break;
        case 'full':
         //   echo $renderer->display_edit_full($lesson, $lesson->firstpageid, 0);
            break;
    }
*/

if($questions){
echo $renderer->show_questions_list($questions,$cm);
}

echo $renderer->footer();
