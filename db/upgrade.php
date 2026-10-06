<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Gradebook XP upgrade steps.
 *
 * @package gradereport_gradebook_xp
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade Gradebook XP.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_gradereport_gradebook_xp_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026091600) {
        $dbman = $DB->get_manager();

        // Remove historical duplicate connections before enforcing uniqueness.
        $seen = [];
        $connections = $DB->get_records('gradereport_gradebook_xp_connections', null,
            'activityid ASC, competencyid ASC, id ASC');
        foreach ($connections as $connection) {
            $key = $connection->activityid . ':' . $connection->competencyid;
            if (isset($seen[$key])) {
                $DB->delete_records('gradereport_gradebook_xp_connections', ['id' => $connection->id]);
            } else {
                $seen[$key] = true;
            }
        }

        $connectiontable = new xmldb_table('gradereport_gradebook_xp_connections');
        $connectionindex = new xmldb_index('activity_competency_uix', XMLDB_INDEX_UNIQUE,
            ['activityid', 'competencyid']);
        if (!$dbman->index_exists($connectiontable, $connectionindex)) {
            $dbman->add_index($connectiontable, $connectionindex);
        }

        // Remove historical duplicate relations before enforcing uniqueness.
        $seen = [];
        $relations = $DB->get_records('gradereport_gradebook_xp_relations', null,
            'parentid ASC, childid ASC, id ASC');
        foreach ($relations as $relation) {
            $key = $relation->parentid . ':' . $relation->childid;
            if (isset($seen[$key])) {
                $DB->delete_records('gradereport_gradebook_xp_relations', ['id' => $relation->id]);
            } else {
                $seen[$key] = true;
            }
        }

        $relationtable = new xmldb_table('gradereport_gradebook_xp_relations');
        $relationindex = new xmldb_index('parent_child_uix', XMLDB_INDEX_UNIQUE, ['parentid', 'childid']);
        if (!$dbman->index_exists($relationtable, $relationindex)) {
            $dbman->add_index($relationtable, $relationindex);
        }

        upgrade_plugin_savepoint(true, 2026091600, 'gradereport', 'gradebook_xp');
    }

    if ($oldversion < 2026091601) {
        // UI terminology and calculation-method controls changed; no schema change is required.
        upgrade_plugin_savepoint(true, 2026091601, 'gradereport', 'gradebook_xp');
    }

    if ($oldversion < 2026091602) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('gradereport_gradebook_xp_connections');

        // Indexes involving a field must be removed before changing its nullability.
        // This also makes the step safe to retry after the original 2026091602 upgrade failed.
        $activityindex = new xmldb_index(
            'activity_competency_uix',
            XMLDB_INDEX_UNIQUE,
            ['activityid', 'competencyid']
        );
        if ($dbman->index_exists($table, $activityindex)) {
            $dbman->drop_index($table, $activityindex);
        }

        $activityfield = new xmldb_field('activityid', XMLDB_TYPE_INTEGER, '10', null, false, false, null, 'id');
        $dbman->change_field_notnull($table, $activityfield);

        $gradeitemfield = new xmldb_field(
            'gradeitemid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            false,
            false,
            null,
            'activityid'
        );
        if (!$dbman->field_exists($table, $gradeitemfield)) {
            $dbman->add_field($table, $gradeitemfield);
        }

        // Do not try to introspect the foreign key here: Moodle's database_manager has no corresponding
        // existence-check method. The composite gradeitem index below supports this upgrade path;
        // fresh installations receive the declared foreign key from install.xml.

        // Map historical course-module IDs to their primary activity grade item.
        $sql = "SELECT conn.id, conn.activityid, comp.courseid
                  FROM {gradereport_gradebook_xp_connections} conn
                  JOIN {gradereport_gradebook_xp_competencies} comp
                    ON comp.id = conn.competencyid
                 WHERE conn.gradeitemid IS NULL";
        $connections = $DB->get_recordset_sql($sql);
        foreach ($connections as $connection) {
            $mapsql = "SELECT gi.id
                         FROM {grade_items} gi
                         JOIN {modules} m ON m.name = gi.itemmodule
                         JOIN {course_modules} cm
                           ON cm.module = m.id
                          AND cm.instance = gi.iteminstance
                        WHERE cm.id = :cmid
                          AND gi.courseid = :courseid
                          AND gi.itemtype = :itemtype
                     ORDER BY gi.sortorder ASC, gi.id ASC";
            $gradeitems = $DB->get_records_sql($mapsql, [
                'cmid' => $connection->activityid,
                'courseid' => $connection->courseid,
                'itemtype' => 'mod',
            ], 0, 1);
            if ($gradeitems) {
                $gradeitem = reset($gradeitems);
                $DB->set_field(
                    'gradereport_gradebook_xp_connections',
                    'gradeitemid',
                    $gradeitem->id,
                    ['id' => $connection->id]
                );
            }
        }
        $connections->close();

        $gradeitemindex = new xmldb_index(
            'gradeitem_competency_uix',
            XMLDB_INDEX_UNIQUE,
            ['gradeitemid', 'competencyid']
        );
        if (!$dbman->index_exists($table, $gradeitemindex)) {
            $dbman->add_index($table, $gradeitemindex);
        }
        if (!$dbman->index_exists($table, $activityindex)) {
            $dbman->add_index($table, $activityindex);
        }

        upgrade_plugin_savepoint(true, 2026091602, 'gradereport', 'gradebook_xp');
    }

    if ($oldversion < 2026091603) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('gradereport_gradebook_xp_competencies');
        $field = new xmldb_field(
            'targetcomlvl',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'maxcomlvl'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $DB->execute('UPDATE {gradereport_gradebook_xp_competencies} SET targetcomlvl = maxcomlvl');
        }

        upgrade_plugin_savepoint(true, 2026091603, 'gradereport', 'gradebook_xp');
    }

    if ($oldversion < 2026091701) {
        // Packaging revision: the 2026091602 step is now retry-safe when changing activityid nullability.
        upgrade_plugin_savepoint(true, 2026091701, 'gradereport', 'gradebook_xp');
    }

    if ($oldversion < 2026091702) {
        // Radar styling changed; no schema change is required.
        upgrade_plugin_savepoint(true, 2026091702, 'gradereport', 'gradebook_xp');
    }

    if ($oldversion < 2026100600) {
        // Moodle 5.2 Bootstrap compatibility for the interactive management interface.
        upgrade_plugin_savepoint(true, 2026100600, 'gradereport', 'gradebook_xp');
    }

    if ($oldversion < 2026100601) {
        // Add the JSON competency import interface.
        upgrade_plugin_savepoint(true, 2026100601, 'gradereport', 'gradebook_xp');
    }

    return true;
}
