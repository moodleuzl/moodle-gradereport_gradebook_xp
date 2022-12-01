<?php

/**
 * Defines capabilities for the Gradebook XP plugin
 *
 * @package gradebook_xp
 */

defined('MOODLE_INTERNAL') || die();

// Example:
// 'gradereport/gradebook_xp:view' => array(
//     'riskbitmask' => RISK_PERSONAL,
//     'captype' => 'read',
//     'contextlevel' => CONTEXT_COURSE,
//     'archetypes' => array(
//         'user' => CAP_ALLOW
//     )
// )

$capabilities = array(
    'gradereport/gradebook_xp:view' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'user' => CAP_ALLOW
        )
    )
);