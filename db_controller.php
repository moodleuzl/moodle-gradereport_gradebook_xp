<?php

defined('MOODLE_INTERNAL') || die;

/**
 * Retrieves the parent options for a select element based on the provided record ID.
 *
 * @param int|null $currentRecordId The ID of the current record. Pass null if no record ID is available.
 * @return array An array of parent options for the select element.
 */
function get_parent_options($currentRecordId) {
    global $COURSE, $DB;
    $parentOptions = [0 => '---']; // Initialize with an empty option

    if (!empty($currentRecordId)) { // If record ID is provided
        $parentid = $currentRecordId;

        // SQL query to retrieve available parent options for the select element based on the current record's ID.
        $sql = "WITH RECURSIVE item_descendants AS (
                    SELECT id, parentid, name
                    FROM {gradereport_gradebook_xp_com}
                    WHERE id = ?
                    UNION
                    SELECT g.id, g.parentid, g.name
                    FROM {gradereport_gradebook_xp_com} g
                    JOIN item_descendants d ON g.parentid = d.id
                )
                SELECT id, name
                FROM {gradereport_gradebook_xp_com}
                WHERE courseid = ? AND id NOT IN (
                    SELECT id FROM item_descendants UNION
                    SELECT ? WHERE parentid IS NULL
                )
                AND id != ?
                ORDER BY id";

        $params = array($parentid, $COURSE->id, $parentid, $parentid);
        $availableParents = $DB->get_records_sql($sql, $params); // Get parent records
        $parentOptions += array_column($availableParents, 'name', 'id'); // Add parent options to array

    } else { // If no record ID is provided
        $sql = "SELECT id, name
                FROM {gradereport_gradebook_xp_com} 
                WHERE courseid = ?
                ORDER BY id";

        $params = array($COURSE->id);
        $availableParents = $DB->get_records_sql($sql, $params); // Get all parent records
        $parentOptions += array_column($availableParents, 'name', 'id'); // Add parent options to array
    }

    return $parentOptions;
}