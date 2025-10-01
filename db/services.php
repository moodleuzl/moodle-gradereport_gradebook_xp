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
 * Gradebook XP external functions and service definitions.
 *
 * @package gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    // Competencies CRUD
    'gradereport_gb_xp_admin_get_competencies' => [
        'classname' => 'gradereport_gb_xp_admin\external\get_competencies',
        'description' => 'Get all competencies for a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'moodle/grade:view',
    ],
    'gradereport_gb_xp_admin_create_competency' => [
        'classname' => 'gradereport_gb_xp_admin\external\create_competency',
        'description' => 'Create a new competency',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/grade:manage',
    ],
    'gradereport_gb_xp_admin_update_competency' => [
        'classname' => 'gradereport_gb_xp_admin\external\update_competency',
        'description' => 'Update an existing competency',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/grade:manage',
    ],
    'gradereport_gb_xp_admin_delete_competency' => [
        'classname' => 'gradereport_gb_xp_admin\external\delete_competency',
        'description' => 'Delete a competency',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/grade:manage',
    ],

    // Relations CRUD
    'gradereport_gb_xp_admin_get_relations' => [
        'classname' => 'gradereport_gb_xp_admin\external\get_relations',
        'description' => 'Get all relations for a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'moodle/grade:view',
    ],
    'gradereport_gb_xp_admin_create_relation' => [
        'classname' => 'gradereport_gb_xp_admin\external\create_relation',
        'description' => 'Create a new relation between competencies',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/grade:manage',
    ],
    'gradereport_gb_xp_admin_delete_relation' => [
        'classname' => 'gradereport_gb_xp_admin\external\delete_relation',
        'description' => 'Delete a relation',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/grade:manage',
    ],

    // Connections CRUD
    'gradereport_gb_xp_admin_get_connections' => [
        'classname' => 'gradereport_gb_xp_admin\external\get_connections',
        'description' => 'Get all connections for a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'moodle/grade:view',
    ],
    'gradereport_gb_xp_admin_create_connection' => [
        'classname' => 'gradereport_gb_xp_admin\external\create_connection',
        'description' => 'Create a new activity-competency connection',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/grade:manage',
    ],
    'gradereport_gb_xp_admin_delete_connection' => [
        'classname' => 'gradereport_gb_xp_admin\external\delete_connection',
        'description' => 'Delete a connection',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/grade:manage',
    ],

    // Activities API
    'gradereport_gb_xp_admin_get_activities' => [
        'classname' => 'gradereport_gb_xp_admin\external\get_activities',
        'description' => 'Get all activities for a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'moodle/grade:view',
    ],
];

// Define services
$services = [
    'gradereport_gb_xp_admin_service' => [
        'functions' => [
            'gradereport_gb_xp_admin_get_competencies',
            'gradereport_gb_xp_admin_create_competency',
            'gradereport_gb_xp_admin_update_competency',
            'gradereport_gb_xp_admin_delete_competency',
            'gradereport_gb_xp_admin_get_relations',
            'gradereport_gb_xp_admin_create_relation',
            'gradereport_gb_xp_admin_delete_relation',
            'gradereport_gb_xp_admin_get_connections',
            'gradereport_gb_xp_admin_create_connection',
            'gradereport_gb_xp_admin_delete_connection',
            'gradereport_gb_xp_admin_get_activities',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'gb_xp_admin_api',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ],
];
