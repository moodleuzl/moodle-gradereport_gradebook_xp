<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace gradereport_gb_xp_admin\output;

use core_grades\output\action_bar;
use core_grades\output\general_action_bar;
use moodle_url;
use single_button;

/**
 * Renderable class for the action bar elements in the gb_xp_admin index page.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright  INB University of Luebeck
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gb_xp_action_bar extends action_bar {

    /** @var int|null $userid The user ID. */
    protected $userid;

    /** @var int $courseid The course ID. */
    protected $courseid;

    /**
     * The class constructor.
     *
     * @param \context $context The context object.
     * @param int $courseid The course ID.
     * @param int|null $userid The user ID or null if displaying all users.
     */
    public function __construct(\context $context, int $courseid, ?int $userid = null) {
        parent::__construct($context);
        $this->courseid = $courseid;
        $this->userid = $userid;
    }

    /**
     * Returns the template for the action bar.
     *
     * @return string
     */
    public function get_template(): string {
        return 'gradereport_gb_xp_admin/action_bar';
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        global $PAGE;

        $data = [];

        // Only render action bar if user has viewall capability.
        if (!has_capability('moodle/grade:viewall', $this->context)) {
            return $data;
        }

        // If in the course context, we should display the general navigation selector in gradebook.
        if ($this->context->contextlevel === CONTEXT_COURSE) {
            // Get the data used to output the general navigation selector.
            $generalnavselector = new general_action_bar($this->context,
                new moodle_url('/grade/report/gb_xp_admin/index.php', ['id' => $this->courseid]),
                'gradereport', 'gb_xp_admin');
            $data = $generalnavselector->export_for_template($output);
        }

        $course = get_course($this->courseid);
        $baseurl = clone($PAGE->url);
        // Reset link removes user selection.
        $resetlink = clone($baseurl);
        $resetlink->remove_params(['userid', 'usersearch']);
        $PAGE->requires->js_call_amd('gradereport_gb_xp_admin/user', 'init', [$baseurl->out(false)]);
        $search = optional_param('usersearch', '', PARAM_RAW);

        $userselector = new \core_course\output\actionbar\user_selector(
            course: $course,
            resetlink: $resetlink,
            userid: $this->userid,
            groupid: null,
            usersearch: $search
        );
        $data['userselector'] = [
            'courseid' => $this->courseid,
            'content' => $userselector->export_for_template($output),
        ];

        // Add edit button if user has manage capability.
        if (has_capability('moodle/grade:manage', $this->context)) {
            $editbuttonlink = new moodle_url('/grade/report/gb_xp_admin/manage_competencies.php', ['id' => $this->courseid]);
            $editbutton = new single_button($editbuttonlink, get_string('edit'), 'get', single_button::BUTTON_PRIMARY);
            $data['editbutton'] = $editbutton->export_for_template($output);
        }

        return $data;
    }

}
