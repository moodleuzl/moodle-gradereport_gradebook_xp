<?php

/**
 * Gradebook XP upgrade steps.
 *
 * @package gradebook_xp
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade Gradebook XP.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_gradereport_gradebook_xp_upgrade($oldversion) {
    global $DB;

    return true;
}
