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

/**
 * Course data manager for gradebook_xp gradebook report.
 *
 * Centralizes data fetching and management for course competencies, relations,
 * connections, and grades to provide easy access for visualization and reporting.
 *
 * @package    gradereport_gradebook_xp
 * @copyright  INB University of Luebeck
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradereport_gradebook_xp;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../db/competencies.php');
require_once(__DIR__ . '/../db/relations.php');
require_once(__DIR__ . '/../db/connections.php');
require_once(__DIR__ . '/../db/activities.php');

require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/report/user/lib.php');

/**
 * Manages and caches course-related data for competencies, relations, and connections.
 *
 * This class provides a centralized way to fetch and cache data for a specific course,
 * including competencies, their hierarchical relations, activity connections, and user grades.
 * It implements lazy loading and caching to optimize performance.
 */
class course_data_manager {

    /** @var int The course ID this manager is responsible for */
    private $courseid;

    /** @var int The user ID this manager is responsible for */
    private $userid;

    /** @var array|null Cached competencies for this course */
    private $competencies = null;

    /** @var array|null Cached relations for this course */
    private $relations = null;

    /** @var array|null Cached connections for this course */
    private $connections = null;

    /** @var array|null 2D array for direct child relationships. is_child[x][y] = true if y is a direct child of x */
    private $ischild = null;

    /** @var array|null 2D array for direct parent relationships. is_parent[x][y] = true if y is a direct parent of x */
    private $isparent = null;

    /** @var array|null 2D array for descendant relationships. is_descendant[x][y] = true if y is a descendant of x */
    private $isdescendant = null;

    /** @var array|null 2D array for ancestor relationships. is_ancestor[x][y] = true if y is an ancestor of x */
    private $isancestor = null;

    /** @var \stdClass|null Cached course object */
    private $course = null;

    /** @var \context_course|null Cached course context */
    private $context = null;

    /** @var object|null Cached user grades */
    private $usergrades = null;

    /** @var array|null Cached course activities */
    private $activities = null;

    /**
     * Constructor.
     *
     * @param int $courseid The ID of the course to manage data for.
     * @param int $userid The ID of the user to manage data for.
     */
    public function __construct(int $courseid, int $userid) {
        $this->courseid = $courseid;
        $this->userid = $userid;

        // Preload core data to reduce database calls.
        $this->preload_data();
    }

    /**
     * Preload core data on construction to optimize performance.
     *
     * @throws \dml_exception
     */
    private function preload_data(): void {
        // Preload competencies, relations, connections, and activities.
        $this->get_competencies();
        $this->get_relations();
        $this->get_connections();
        $this->get_activities();
        $this->get_user_grades();

        // Preload relationship arrays.
        $this->preload_relationship_arrays();
    }

    /**
     * Preload relationship arrays.
     */
    private function preload_relationship_arrays(): void {
        $competencies = $this->get_competencies();
        $relations = $this->get_relations();

        // Initialize arrays.
        $this->ischild = [];
        $this->isparent = [];
        $this->isdescendant = [];
        $this->isancestor = [];

        // Initialize all combinations to false for all competency IDs.
        $competencyids = array_keys($competencies);
        foreach ($competencyids as $x) {
            foreach ($competencyids as $y) {
                $this->ischild[$x][$y] = false;
                $this->isparent[$x][$y] = false;
                $this->isdescendant[$x][$y] = false;
                $this->isancestor[$x][$y] = false;
            }
        }

        // Fill direct child/parent relationships.
        foreach ($relations as $relation) {
            $this->ischild[$relation->parentid][$relation->childid] = true;
            $this->isparent[$relation->childid][$relation->parentid] = true;
        }

        // Calculate descendant/ancestor relationships using transitive closure.
        foreach ($competencyids as $x) {
            $descendantsx = $this->get_descendants($x);
            $ancestorsx = $this->get_ancestors($x);
            foreach ($competencyids as $y) {
                if ($x != $y) {
                    $this->isdescendant[$x][$y] = in_array($y, $descendantsx);
                    $this->isancestor[$x][$y] = in_array($y, $ancestorsx);
                }
            }
        }
    }

    /**
     * Get the course ID.
     *
     * @return int The course ID.
     */
    public function get_course_id(): int {
        return $this->courseid;
    }

    /**
     * Get the course object.
     *
     * @return \stdClass The course object.
     * @throws \dml_exception
     */
    public function get_course(): \stdClass {
        if ($this->course === null) {
            $this->course = get_course($this->courseid);
        }
        return $this->course;
    }

    /**
     * Get the course context.
     *
     * @return \context_course The course context.
     */
    public function get_context(): \context_course {
        if ($this->context === null) {
            $this->context = \context_course::instance($this->courseid);
        }
        return $this->context;
    }

    /**
     * Get all competencies for this course.
     *
     * @return array Array of competency objects.
     * @throws \dml_exception
     */
    public function get_competencies(): array {
        if ($this->competencies === null) {
            $competenciesraw = get_competencies($this->courseid);
            $this->competencies = [];
            foreach ($competenciesraw as $competency) {
                $this->competencies[$competency->id] = $competency;
            }
        }
        return $this->competencies;
    }

    /**
     * Get a specific competency by ID.
     *
     * @param int $competencyid The competency ID.
     * @return \stdClass|false The competency object or false if not found.
     * @throws \dml_exception
     */
    public function get_competency(int $competencyid) {
        $competencies = $this->get_competencies();
        return $competencies[$competencyid] ?? false;
    }

    /**
     * Get all relations for this course.
     *
     * @return array Array of relation objects.
     * @throws \dml_exception
     */
    public function get_relations(): array {
        if ($this->relations === null) {
            $relationsraw = get_relations($this->courseid);
            $this->relations = [];
            foreach ($relationsraw as $relation) {
                $this->relations[$relation->id] = $relation;
            }
        }
        return $this->relations;
    }

    /**
     * Get all connections for this course.
     *
     * @return array Array of connection objects.
     * @throws \dml_exception
     */
    public function get_connections(): array {
        if ($this->connections === null) {
            $connectionsraw = get_connections($this->courseid);
            $this->connections = [];
            foreach ($connectionsraw as $connection) {
                $this->connections[$connection->id] = $connection;
            }
        }
        return $this->connections;
    }

    /**
     * Get connections for a specific competency.
     *
     * @param int $competencyid The competency ID.
     * @return array Array of connection objects for the competency.
     * @throws \dml_exception
     */
    public function get_competency_connections(int $competencyid): array {
        $connections = $this->get_connections();
        $competencyconnections = [];
        foreach ($connections as $connection) {
            if ($connection->competencyid == $competencyid) {
                $competencyconnections[] = $connection;
            }
        }
        return $competencyconnections;
    }

    /**
     * Get all descendants of a competency.
     *
     * @param int $competencyid The parent competency ID.
     * @return array Array of descendant competency IDs.
     * @throws \dml_exception
     */
    public function get_descendants(int $competencyid): array {
        if ($this->isdescendant !== null && isset($this->isdescendant[$competencyid])) {
            $descendants = [];
            foreach ($this->isdescendant[$competencyid] as $descendantid => $isdescendant) {
                if ($isdescendant) {
                    $descendants[] = $descendantid;
                }
            }
            return $descendants;
        }

        // Fallback to calculation if precomputed array is not available.
        $relations = $this->get_relations();

        // Build adjacency list for children.
        $children = [];
        foreach ($relations as $relation) {
            if (!isset($children[$relation->parentid])) {
                $children[$relation->parentid] = [];
            }
            $children[$relation->parentid][] = $relation->childid;
        }

        // Traverse hierarchy using depth-first search with cycle detection.
        $visited = [];
        $stack = [$competencyid];

        while (!empty($stack)) {
            $current = array_pop($stack);
            if (isset($children[$current])) {
                foreach ($children[$current] as $child) {
                    if (!isset($visited[$child])) {
                        $visited[$child] = true;
                        $stack[] = $child;
                    }
                }
            }
        }

        return array_map('intval', array_keys($visited));
    }

    /**
     * Get all ancestors of a competency.
     *
     * @param int $competencyid The child competency ID.
     * @return array Array of ancestor competency IDs.
     * @throws \dml_exception
     */
    public function get_ancestors(int $competencyid): array {
        if ($this->isancestor !== null && isset($this->isancestor[$competencyid])) {
            $ancestors = [];
            foreach ($this->isancestor[$competencyid] as $ancestorid => $isancestor) {
                if ($isancestor) {
                    $ancestors[] = $ancestorid;
                }
            }
            return $ancestors;
        }

        // Fallback to calculation if precomputed array is not available.
        $relations = $this->get_relations();

        // Build adjacency list for parents.
        $parents = [];
        foreach ($relations as $relation) {
            if (!isset($parents[$relation->childid])) {
                $parents[$relation->childid] = [];
            }
            $parents[$relation->childid][] = $relation->parentid;
        }

        // Traverse hierarchy using depth-first search with cycle detection.
        $visited = [];
        $stack = [$competencyid];

        while (!empty($stack)) {
            $current = array_pop($stack);
            if (isset($parents[$current])) {
                foreach ($parents[$current] as $parent) {
                    if (!isset($visited[$parent])) {
                        $visited[$parent] = true;
                        $stack[] = $parent;
                    }
                }
            }
        }

        return array_map('intval', array_keys($visited));
    }

    /**
     * Check if one competency is a descendant of another.
     *
     * @param int $childid The potential child competency ID.
     * @param int $parentid The potential parent competency ID.
     * @return bool True if child is a descendant of parent.
     * @throws \dml_exception
     */
    public function is_descendant(int $childid, int $parentid): bool {
        return $this->isdescendant[$parentid][$childid] ?? false;
    }

    /**
     * Check if one competency is an ancestor of another.
     *
     * @param int $parentid The potential parent competency ID.
     * @param int $childid The potential child competency ID.
     * @return bool True if parent is an ancestor of child.
     */
    public function is_ancestor(int $parentid, int $childid): bool {
        return $this->isancestor[$childid][$parentid] ?? false;
    }

    /**
     * Check if one competency is a direct child of another.
     *
     * @param int $childid The potential child competency ID.
     * @param int $parentid The potential parent competency ID.
     * @return bool True if child is a direct child of parent.
     */
    public function is_direct_child(int $childid, int $parentid): bool {
        return $this->ischild[$parentid][$childid] ?? false;
    }

    /**
     * Check if one competency is a direct parent of another.
     *
     * @param int $parentid The potential parent competency ID.
     * @param int $childid The potential child competency ID.
     * @return bool True if parent is a direct parent of child.
     */
    public function is_direct_parent(int $parentid, int $childid): bool {
        return $this->isparent[$childid][$parentid] ?? false;
    }

    /**
     * Get direct children of a competency or root competencies if null.
     *
     * @param int|null $competencyid The parent competency ID or null for root competencies.
     * @return array Array of direct child competency IDs or root competency IDs.
     */
    public function get_direct_children(?int $competencyid = null): array {
        if ($competencyid === null) {
            // Get root competencies (those without parents).
            $competencies = $this->get_competencies();
            $rootcompetencies = [];
            foreach ($competencies as $competency) {
                // Check if this competency has no parents (is a root).
                $hasparent = false;
                $relations = $this->get_relations();
                foreach ($relations as $relation) {
                    if ($relation->childid == $competency->id) {
                        $hasparent = true;
                        break;
                    }
                }
                if (!$hasparent) {
                    $rootcompetencies[] = $competency->id;
                }
            }
            return $rootcompetencies;
        }

        $children = [];
        if (isset($this->ischild[$competencyid])) {
            foreach ($this->ischild[$competencyid] as $childid => $ischild) {
                if ($ischild) {
                    $children[] = $childid;
                }
            }
        }
        return $children;
    }

    /**
     * Get all activities for this course.
     *
     * @return array Array of activity objects indexed by course module ID.
     * @throws \dml_exception
     */
    public function get_activities(): array {
        if ($this->activities === null) {
            $activitiesraw = get_all_activities($this->courseid);
            $this->activities = [];
            foreach ($activitiesraw as $activity) {
                $this->activities[$activity->id] = $activity;
            }
        }
        return $this->activities;
    }

    /**
     * Get a specific activity by course module ID.
     *
     * @param int $cmid The course module ID.
     * @return \stdClass|false The activity object or false if not found.
     * @throws \dml_exception
     */
    public function get_activity(int $cmid) {
        $activities = $this->get_activities();
        return $activities[$cmid] ?? false;
    }

    /**
     * Get user grades for this course and user.
     *
     * Returns processed grades data with grades and gradeinfos indexed for fast lookup.
     *
     * @return array|null Array containing 'grades' and 'gradeinfos' or null if no grades available.
     * @throws \dml_exception
     */
    public function get_user_grades(): ?array {
        global $USER;

        if ($this->usergrades === null) {
            $course = $this->get_course();
            $context = $this->get_context();

            // Get grades.
            if (!empty($course->showgrades)) {
                $gpr = new \grade_plugin_return(['type' => 'report',
                    'plugin' => 'user',
                    'courseid' => $course->id,
                    'userid' => $this->userid]);
                $report = new \gradereport_user\report\user($course->id, $gpr, $context, $this->userid);

                if ($report->fill_table()) {
                    // Process grades.
                    $grades = [];
                    $gradesbycmid = [];
                    if (!empty($report->gradeitemsdata)) {
                        foreach ($report->gradeitemsdata as $rawgrade) {
                            if (array_key_exists('id', $rawgrade)) {
                                $grades[$rawgrade['id']] = $rawgrade;
                            }
                            if (array_key_exists('cmid', $rawgrade)) {
                                $gradesbycmid[$rawgrade['cmid']] = $rawgrade;
                            }
                        }
                    }

                    $gradeinfos = [];
                    if (!empty($report->gtree)) {
                        foreach ($report->gtree->get_items() as $rawgradeinfo) {
                            $gradeinfos[$rawgradeinfo->id] = $rawgradeinfo;
                        }
                    }

                    $this->usergrades = [
                        'grades' => $grades,
                        'gradesbycmid' => $gradesbycmid,
                        'gradeinfos' => $gradeinfos,
                    ];
                }
            }
        }

        return $this->usergrades;
    }

    /**
     * Clear all cached data.
     *
     * Forces fresh data to be loaded on next access. Useful when data has been modified.
     */
    public function clear_cache(): void {
        $this->competencies = null;
        $this->relations = null;
        $this->connections = null;
        $this->hierarchy = null;
        $this->course = null;
        $this->context = null;
        $this->usergrades = null;
        $this->activities = null;
        $this->ischild = null;
        $this->isparent = null;
        $this->isdescendant = null;
        $this->isancestor = null;
    }

    /**
     * Calculate competency activity data for a single competency.
     *
     * @param int $competencyid The competency ID.
     * @return array Array containing activity info and calculated levels.
     * @throws \dml_exception
     */
    public function calculate_competency_data(int $competencyid): array {
        // TODO this is a mess, clean it up.
        $competency = $this->get_competency($competencyid);
        if (!$competency) {
            return [
                'activities' => [],
                'user_level' => 0,
                'max_reachable_level' => 0
            ];
        }

        $connections = $this->get_competency_connections($competencyid);
        $usergrades = $this->get_user_grades();

        if (!$usergrades) {
            return [
                'activities' => [],
                'user_level' => 0,
                'max_reachable_level' => 0
            ];
        }

        $grades = $usergrades['grades'];
        $gradesbycmid = $usergrades['gradesbycmid'];
        $gradeinfos = $usergrades['gradeinfos'];

        $maxreachablecompetencylevel = 0;
        $userlevel = 0;
        $competencyactivities = [];

        foreach ($connections as $connection) {
            $gradeitemid = (int)($connection->gradeitemid ?? 0);
            $grade = $gradeitemid > 0 ? ($grades[$gradeitemid] ?? null) : null;
            if (!$grade && !empty($connection->activityid)) {
                $grade = $gradesbycmid[$connection->activityid] ?? null;
            }
            if (!$grade) {
                continue;
            }

            $gradeinfo = $gradeinfos[$grade["id"]] ?? null;

            if (!$gradeinfo) {
                continue;
            }

            $gradepass = (float)$gradeinfo->gradepass;
            $passconfigured = $gradepass > 0;

            $activityinfo = new \stdClass();
            $activityinfo->gradeitemid = (int)$gradeinfo->id;
            $activityinfo->activityid = (int)($grade['cmid'] ?? 0);
            $activityinfo->name = $gradeinfo->itemname;
            $activityinfo->type = $gradeinfo->itemtype;
            $activityinfo->module = $gradeinfo->itemmodule;
            $activityinfo->level = $connection->level;
            $activityinfo->passconfigured = $passconfigured;
            $activityinfo->hasurl = $activityinfo->activityid > 0 && !empty($gradeinfo->itemmodule);
            if ($activityinfo->hasurl) {
                $activityinfo->url = (new \moodle_url(
                    '/mod/' . $gradeinfo->itemmodule . '/view.php',
                    ['id' => $activityinfo->activityid]
                ))->out(false);
            }

            // Calculate max reachable level.
            if ($competency->islevelsummed) {
                $maxreachablecompetencylevel += $connection->level;
            } else {
                $maxreachablecompetencylevel = max($maxreachablecompetencylevel, $connection->level);
            }

            // Check if user passed this activity.
            $passed = $passconfigured && $grade["graderaw"] !== null &&
                (float)$grade["graderaw"] >= $gradepass;
            $activityinfo->passed = $passed;

            if ($passed) {
                if ($competency->islevelsummed) {
                    $userlevel += $connection->level;
                } else {
                    $userlevel = max($userlevel, $connection->level);
                }
            }

            $competencyactivities[] = $activityinfo;
        }

        // Sort activities by level and passed status.
        usort($competencyactivities, function ($a, $b) {
            if ($a->level == $b->level) {
                return $a->passed === true ? -1 : 1;
            } else {
                return ($a->level < $b->level) ? 1 : -1;
            }
        });

        // Assign colors based on status.
        foreach ($competencyactivities as $activityinfo) {
            if ($activityinfo->passed) {
                $activityinfo->color = '#0F7C09'; // Green for passed.
            } else if ($activityinfo->level <= $userlevel) {
                $activityinfo->color = '#848484'; // Grey for within level but not passed.
            } else {
                $activityinfo->color = '#7C0205'; // Red for above user level.
            }
        }

        return [
            'activities' => $competencyactivities,
            'user_level' => $userlevel,
            'max_reachable_level' => $maxreachablecompetencylevel
        ];
    }

    /**
     * Build data for selected competency and its children.
     *
     * @param int|null $selectedcompetencyid The selected competency ID or null for root competencies.
     * @return array Array of competency data with activities and levels.
     * @throws \dml_exception
     */
    public function build_data_for_selected_competency(?int $selectedcompetencyid = null): array {
        // Get child competencies using updated get_direct_children method.
        $childcompetencies = $this->get_direct_children($selectedcompetencyid);

        // Build data for each child competency.
        $competencydata = [];
        foreach ($childcompetencies as $competencyid) {
            $competency = $this->get_competency($competencyid);
            if ($competency) {
                $data = $this->calculate_competency_data($competencyid);
                $competencydata[$competency->id] = [
                    'id' => $competency->id,
                    'name' => $competency->name,
                    'user_level' => $data['user_level'],
                    'max_reachable_level' => $data['max_reachable_level'],
                    'max_level' => $competency->maxcomlvl ?? 0,
                    'target_level' => $competency->targetcomlvl ?? $competency->maxcomlvl ?? 0,
                    'activities' => $data['activities'],
                    'has_subcompetencies' => !empty($this->get_direct_children($competencyid)),
                    'is_level_summed' => $competency->islevelsummed ?? false,
                ];
            }
        }

        return $competencydata;
    }

    /**
     * Build template data for selected competency based on gradereport_gradebook_xp_generate_chart_data from lib.php.
     *
     * Generates template data for visualization, similar to the original function but using
     * the cached data from the course_data_manager for improved performance.
     *
     * @param int|null $selectedcompetencyid The ID of the competency for which to generate template data.
     * @return \stdClass An object containing the generated template data.
     * @throws \dml_exception
     */
    public function build_template_data_for_selected_competency(?int $selectedcompetencyid = null): \stdClass {
        $currentcompetency = $selectedcompetencyid == null ? null : $this->get_competency($selectedcompetencyid);
        $chartcompetencies = $this->build_data_for_selected_competency($selectedcompetencyid);

        // Prepare chart data arrays.
        $charcompetenciesname = array_map(fn($competency) => "'" . addslashes($competency['name']) . "'", $chartcompetencies);
        $charcompetenciesuser = array_map(fn($competency) => $competency['user_level'], $chartcompetencies);
        $charcompetenciesmaxreachablelevel = array_map(fn($competency) => $competency['max_reachable_level'], $chartcompetencies);
        $charcompetenciesmaxlevel = array_map(fn($competency) => $competency['max_level'], $chartcompetencies);
        $charcompetenciestarget = array_map(fn($competency) => $competency['target_level'], $chartcompetencies);

        return (object)[
            'competencies' => "[" . implode(",", $charcompetenciesname) . "]",
            'data_user' => "[" . implode(",", $charcompetenciesuser) . "]",
            'data_max_reachable_level' => "[" . implode(",", $charcompetenciesmaxreachablelevel) . "]",
            'data_max_level' => "[" . implode(",", $charcompetenciesmaxlevel) . "]",
            'data_target' => "[" . implode(",", $charcompetenciestarget) . "]",
            'chart_scale_max' => empty($charcompetenciesmaxlevel) ? 1 : max($charcompetenciesmaxlevel),
            'chart_competencies' => array_values($chartcompetencies),
            'competencyid' => $selectedcompetencyid,
            'competencyname' => $currentcompetency ? $currentcompetency->name : null,
            'courseid' => $this->courseid,
            'competencycount' => count($chartcompetencies),
            'showchart' => count($chartcompetencies) >= 3,
        ];
    }
}
