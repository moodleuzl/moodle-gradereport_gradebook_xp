<?php

defined('MOODLE_INTERNAL') || die;

/**
 * Retrieves the parent options for a select element based on the provided record ID.
 *
 * @param int|null $currentRecordId The ID of the current record. Pass null if no record ID is available.
 * @return array An array of parent options for the select element.
 */
function get_available_parents($currentRecordId) {
    global $COURSE, $DB;

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

    $params = array($currentRecordId, $COURSE->id, $currentRecordId, $currentRecordId);
    $availableParents = $DB->get_records_sql($sql, $params); // Get parent records

    return $availableParents;
}
function get_competency($id) {
    global $DB;

    $competency = $DB->get_record('gradereport_gradebook_xp_com', array('id' => $id));

    return $competency;
}
function get_all_competencies() {
    global $COURSE, $DB;

    $sql = "SELECT id, name
                FROM {gradereport_gradebook_xp_com} 
                WHERE courseid = ?
                ORDER BY id";
    $params = array($COURSE->id);
    $competencies = $DB->get_records_sql($sql, $params); // Get all parent records

    return $competencies;
}
function get_direct_children($id) {
    global $DB;

    $children_records = $DB->get_records('gradereport_gradebook_xp_com', array('parentid' => $id));

    return $children_records;
}
function get_all_children($id) {
    global $DB;

    // Use a recursive SQL query to get all children records of the parent
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
            FROM item_descendants
            WHERE id != ?";

    $params = array($id, $id);
    $children_records = $DB->get_records_sql($sql, $params);

    return $children_records;
}