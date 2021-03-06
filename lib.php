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
 * Library of interface functions and constants for module tquiz
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the tquiz specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_tquiz
 * @copyright  COPYRIGHTNOTICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('MOD_TQUIZ_NONE', 0);
define('MOD_TQUIZ_QTYPE_TEXTCHOICE', 1);
define('MOD_TQUIZ_QTYPE_AUDIOCHOICE', 2);
define('MOD_TQUIZ_TEXTCHOICE', 'textchoice');
define('MOD_TQUIZ_AUDIOQUESTION', 'audioquestion');
define('MOD_TQUIZ_AUDIOANSWER', 'audioanswer');
define('MOD_TQUIZ_AUDIOQUESTION_FILEAREA', 'audioquestion');
define('MOD_TQUIZ_AUDIOANSWER_FILEAREA', 'audioanswer');
define('MOD_TQUIZ_TEXTQUESTION', 'questiontext');
define('MOD_TQUIZ_TEXTANSWER', 'answertext');
define('MOD_TQUIZ_TEXTQUESTION_FILEAREA', 'questionarea');
define('MOD_TQUIZ_TEXTANSWER_FILEAREA', 'answerarea');
define('MOD_TQUIZ_CORRECTANSWER','correctanswer');
define('MOD_TQUIZ_SHUFFLEANSWERS','shuffleanswers');
define('MOD_TQUIZ_ANSWERSINROW','answersinrow');
define('MOD_TQUIZ_ANSWERWIDTH','answerwidth');
define('MOD_TQUIZ_MAXANSWERS',4);


////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Get editor option for forms
 *
 * @param object context
 * @return array editoroptions 
 */
function tquiz_fetch_editor_options($context) {
	return array('maxfiles' => EDITOR_UNLIMITED_FILES,
               'noclean' => true, 'context' => $context, 'subdirs' => true);

}

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function tquiz_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
		case FEATURE_BACKUP_MOODLE2:    return true;

        default:                        return null;
    }
}

/**
 * Saves a new instance of the tquiz into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $tquiz An object from the form in mod_form.php
 * @param mod_tquiz_mod_form $mform
 * @return int The id of the newly inserted tquiz record
 */
function tquiz_add_instance(stdClass $tquiz, mod_tquiz_mod_form $mform = null) {
    global $DB;

    $tquiz->timecreated = time();

    # You may have to add extra stuff in here #
	$itemid=0;
	$cmid = $tquiz->coursemodule;
	$modulecontext = context_module::instance($cmid);
	$editoroptions = tquiz_fetch_editor_options($modulecontext);
	$tquiz = file_postupdate_standard_editor( $tquiz, 'feedback', $editoroptions, $modulecontext,
                                        'mod_tquiz', 'feedback', $itemid);

	
    return $DB->insert_record('tquiz', $tquiz);
}

/**
 * Updates an instance of the tquiz in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $tquiz An object from the form in mod_form.php
 * @param mod_tquiz_mod_form $mform
 * @return boolean Success/Fail
 */
function tquiz_update_instance(stdClass $tquiz, mod_tquiz_mod_form $mform = null) {
    global $DB;

    $tquiz->timemodified = time();
    $tquiz->id = $tquiz->instance;

    # You may have to add extra stuff in here #
    $itemid=0;
	$cmid = $tquiz->coursemodule;
	$modulecontext = context_module::instance($cmid);
	$editoroptions = tquiz_fetch_editor_options($modulecontext);
	$tquiz = file_postupdate_standard_editor( $tquiz, 'feedback', $editoroptions, $modulecontext,
                                        'mod_tquiz', 'feedback', $itemid);


    return $DB->update_record('tquiz', $tquiz);
}

/**
 * Removes an instance of the tquiz from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function tquiz_delete_instance($id) {
    global $DB;

    if (! $tquiz = $DB->get_record('tquiz', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('tquiz', array('id' => $tquiz->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function tquiz_user_outline($course, $user, $mod, $tquiz) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $tquiz the module instance record
 * @return void, is supposed to echp directly
 */
function tquiz_user_complete($course, $user, $mod, $tquiz) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in tquiz activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function tquiz_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link tquiz_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function tquiz_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see tquiz_get_recent_mod_activity()}

 * @return void
 */
function tquiz_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function tquiz_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function tquiz_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of tquiz?
 *
 * This function returns if a scale is being used by one tquiz
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $tquizid ID of an instance of this module
 * @return bool true if the scale is used by the given tquiz instance
 */
function tquiz_scale_used($tquizid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('tquiz', array('id' => $tquizid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of tquiz.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any tquiz instance
 */
function tquiz_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('tquiz', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give tquiz instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $tquiz instance object with extra cmidnumber and modname property
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void
 */
function tquiz_grade_item_update(stdClass $tquiz, $grades=null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($tquiz->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $tquiz->grade;
    $item['grademin']  = 0;

    grade_update('mod/tquiz', $tquiz->course, 'mod', 'tquiz', $tquiz->id, 0, null, $item);
}

/**
 * Update tquiz grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $tquiz instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function tquiz_update_grades(stdClass $tquiz, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/tquiz', $tquiz->course, 'mod', 'tquiz', $tquiz->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function tquiz_get_file_areas($course, $cm, $context) {
    return array('feedback');
}

/**
 * File browsing support for tquiz file areas
 *
 * @package mod_tquiz
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function tquiz_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the tquiz file areas
 *
 * @package mod_tquiz
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the tquiz's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function tquiz_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
	
	$itemid = (int)array_shift($args);

    require_course_login($course, true, $cm);

    if (!has_capability('mod/tquiz:view', $context)) {
        return false;
    }

    // $arg could be revision number or index.html
   // $arg = array_shift($args);
   //$itemid = (int)array_shift($args);

        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_tquiz/$filearea/$itemid/$relativepath";
		//error_log($fullpath);
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
		/*
            $page = $DB->get_record('webquest', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
            if ($page->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                return false;
            }
            if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_webquest', $filearea, $itemid)) {
                return false;
            }
			*/
          return false;
        }

        // finally send the file
        send_stored_file($file, null, 0, $forcedownload, $options);
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding tquiz nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the tquiz module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function tquiz_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the tquiz settings
 *
 * This function is called when the context for the page is a tquiz module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $tquiznode {@link navigation_node}
 */
function tquiz_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $tquiznode=null) {
}
