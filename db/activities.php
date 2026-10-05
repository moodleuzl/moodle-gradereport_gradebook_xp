<?php
// This file is part of Moodle - http://moodle.org/.

/**
 * Grade-item queries used by Gradebook XP.
 *
 * @package    gradereport_gradebook_xp
 * @copyright  INB University of Luebeck
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');

/**
 * Return selectable activity and manual grade items for a course.
 *
 * @param int $courseid Course ID.
 * @return array Grade-item records indexed by grade-item ID.
 * @throws dml_exception
 */
function get_all_activities($courseid) {
    global $DB;

    $sql = "SELECT gi.id,
                   gi.courseid,
                   gi.itemname AS name,
                   gi.itemtype,
                   gi.itemmodule AS module,
                   gi.gradepass,
                   gi.gradetype,
                   gi.hidden,
                   gi.sortorder,
                   cm.id AS cmid,
                   s.name AS section_name
              FROM {grade_items} gi
         LEFT JOIN {modules} m
                ON m.name = gi.itemmodule
         LEFT JOIN {course_modules} cm
                ON cm.module = m.id
               AND cm.instance = gi.iteminstance
               AND cm.course = gi.courseid
         LEFT JOIN {course_sections} s
                ON s.id = cm.section
             WHERE gi.courseid = :courseid
               AND (gi.itemtype = :modtype OR gi.itemtype = :manualtype)
               AND gi.gradetype <> :gradetypenone
          ORDER BY gi.sortorder ASC, gi.id ASC";

    return $DB->get_records_sql($sql, [
        'courseid' => $courseid,
        'modtype' => 'mod',
        'manualtype' => 'manual',
        'gradetypenone' => GRADE_TYPE_NONE,
    ]);
}
