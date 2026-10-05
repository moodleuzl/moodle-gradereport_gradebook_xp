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
 * @package gradereport_gradebook_xp
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    // Competencies CRUD.
    'gradereport_gradebook_xp_get_competencies' => [
        'classname' => 'gradereport_gradebook_xp\external\get_competencies',
        'description' => 'Get all competencies for a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:view',
    ],
    'gradereport_gradebook_xp_create_competency' => [
        'classname' => 'gradereport_gradebook_xp\external\create_competency',
        'description' => 'Create a new competency',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:manage',
    ],
    'gradereport_gradebook_xp_update_competency' => [
        'classname' => 'gradereport_gradebook_xp\external\update_competency',
        'description' => 'Update an existing competency',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:manage',
    ],
    'gradereport_gradebook_xp_delete_competency' => [
        'classname' => 'gradereport_gradebook_xp\external\delete_competency',
        'description' => 'Delete a competency',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:manage',
    ],

    // Relations CRUD.
    'gradereport_gradebook_xp_get_relations' => [
        'classname' => 'gradereport_gradebook_xp\external\get_relations',
        'description' => 'Get all relations for a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:view',
    ],
    'gradereport_gradebook_xp_create_relation' => [
        'classname' => 'gradereport_gradebook_xp\external\create_relation',
        'description' => 'Create a new relation between competencies',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:manage',
    ],
    'gradereport_gradebook_xp_delete_relation' => [
        'classname' => 'gradereport_gradebook_xp\external\delete_relation',
        'description' => 'Delete a relation',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:manage',
    ],

    // Connections CRUD.
    'gradereport_gradebook_xp_get_connections' => [
        'classname' => 'gradereport_gradebook_xp\external\get_connections',
        'description' => 'Get all connections for a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:view',
    ],
    'gradereport_gradebook_xp_create_connection' => [
        'classname' => 'gradereport_gradebook_xp\external\create_connection',
        'description' => 'Create a new activity-competency connection',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:manage',
    ],
    'gradereport_gradebook_xp_delete_connection' => [
        'classname' => 'gradereport_gradebook_xp\external\delete_connection',
        'description' => 'Delete a connection',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:manage',
    ],

    // Activities API.
    'gradereport_gradebook_xp_get_activities' => [
        'classname' => 'gradereport_gradebook_xp\external\get_activities',
        'description' => 'Get selectable activity and manual grade items for a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'gradereport/gradebook_xp:manage',
    ],
];

// Define services.
$services = [
    'gradereport_gradebook_xp_service' => [
        'functions' => [
            'gradereport_gradebook_xp_get_competencies',
            'gradereport_gradebook_xp_create_competency',
            'gradereport_gradebook_xp_update_competency',
            'gradereport_gradebook_xp_delete_competency',
            'gradereport_gradebook_xp_get_relations',
            'gradereport_gradebook_xp_create_relation',
            'gradereport_gradebook_xp_delete_relation',
            'gradereport_gradebook_xp_get_connections',
            'gradereport_gradebook_xp_create_connection',
            'gradereport_gradebook_xp_delete_connection',
            'gradereport_gradebook_xp_get_activities',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'gradebook_xp_api',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ],
];
