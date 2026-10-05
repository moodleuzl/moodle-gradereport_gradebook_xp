<?php
// This file is part of Moodle - http://moodle.org/.

/**
 * Data-integrity tests for Gradebook XP.
 *
 * @package    gradereport_gradebook_xp
 * @copyright  INB University of Luebeck
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradereport_gradebook_xp;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../db/competencies.php');
require_once(__DIR__ . '/../db/connections.php');
require_once(__DIR__ . '/../db/activities.php');
require_once($CFG->libdir . '/gradelib.php');

/**
 * Tests database consistency for competencies and connections.
 */
class data_integrity_test extends \advanced_testcase {

    /**
     * Manual grade items are available for competency mapping.
     */
    public function test_manual_grade_item_is_selectable(): void {
        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $gradeitem = new \grade_item((object)[
            'courseid' => $course->id,
            'itemtype' => 'manual',
            'itemname' => 'Project work',
            'gradetype' => GRADE_TYPE_VALUE,
            'gradepass' => 50,
        ]);
        $gradeitem->insert();

        $items = get_all_activities($course->id);

        $this->assertArrayHasKey($gradeitem->id, $items);
        $this->assertEquals('manual', $items[$gradeitem->id]->itemtype);
        $this->assertEquals('Project work', $items[$gradeitem->id]->name);
    }

    /**
     * Deleting a competency must remove every dependent plugin record.
     */
    public function test_delete_competency_removes_relations_and_connections(): void {
        global $DB;

        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $gradeitem = new \grade_item((object)[
            'courseid' => $course->id,
            'itemtype' => 'manual',
            'itemname' => 'Project work',
            'gradetype' => GRADE_TYPE_VALUE,
        ]);
        $gradeitem->insert();

        $parentid = $DB->insert_record('gradereport_gradebook_xp_competencies', (object)[
            'courseid' => $course->id,
            'name' => 'Parent',
            'description' => '',
            'maxcomlvl' => 1,
            'islevelsummed' => 1,
        ]);
        $childid = $DB->insert_record('gradereport_gradebook_xp_competencies', (object)[
            'courseid' => $course->id,
            'name' => 'Child',
            'description' => '',
            'maxcomlvl' => 1,
            'islevelsummed' => 1,
        ]);
        $DB->insert_record('gradereport_gradebook_xp_relations', (object)[
            'parentid' => $parentid,
            'childid' => $childid,
        ]);
        $DB->insert_record('gradereport_gradebook_xp_connections', (object)[
            'activityid' => null,
            'gradeitemid' => $gradeitem->id,
            'competencyid' => $childid,
            'level' => 1,
        ]);

        delete_competency($childid);

        $this->assertFalse($DB->record_exists('gradereport_gradebook_xp_competencies', ['id' => $childid]));
        $this->assertFalse($DB->record_exists('gradereport_gradebook_xp_relations', ['childid' => $childid]));
        $this->assertFalse($DB->record_exists('gradereport_gradebook_xp_connections', ['competencyid' => $childid]));
    }

    /**
     * Reconnecting the same activity updates its level instead of creating a duplicate.
     */
    public function test_insert_connection_updates_existing_level(): void {
        global $DB;

        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $gradeitem = new \grade_item((object)[
            'courseid' => $course->id,
            'itemtype' => 'manual',
            'itemname' => 'Project work',
            'gradetype' => GRADE_TYPE_VALUE,
        ]);
        $gradeitem->insert();
        $competencyid = $DB->insert_record('gradereport_gradebook_xp_competencies', (object)[
            'courseid' => $course->id,
            'name' => 'Test',
            'description' => '',
            'maxcomlvl' => 3,
            'islevelsummed' => 1,
        ]);

        $firstid = insert_connection((object)[
            'activityid' => null,
            'gradeitemid' => $gradeitem->id,
            'competencyid' => $competencyid,
            'level' => 1,
        ]);
        $secondid = insert_connection((object)[
            'activityid' => null,
            'gradeitemid' => $gradeitem->id,
            'competencyid' => $competencyid,
            'level' => 2,
        ]);

        $this->assertSame($firstid, $secondid);
        $this->assertSame(1, $DB->count_records('gradereport_gradebook_xp_connections'));
        $this->assertEquals(2, $DB->get_field('gradereport_gradebook_xp_connections', 'level', ['id' => $firstid]));
    }
}
