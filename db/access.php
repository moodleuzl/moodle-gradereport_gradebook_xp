<?php

/**
 * Defines capabilities for the Gradebook XP plugin
 *
 * @package gradebook_xp_admin
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'gradereport/gradebook_xp_admin:view' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
        )
    )
);