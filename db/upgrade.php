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
 * @package gradereport_gradebook_xp_admin
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade Gradebook XP.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_gradereport_gradebook_xp_admin_upgrade($oldversion) {
    global $DB;

    return true;
}
