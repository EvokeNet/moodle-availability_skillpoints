<?php

/**
 * Activity completion condition.
 *
 * @package     availability_skillpoints
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace availability_skillpoints;

use core_availability\info;

defined('MOODLE_INTERNAL') || die();

/**
 * Activity completion condition.
 *
 * @package     availability_skillpoints
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class condition extends \core_availability\condition {
    /**
     * @var int $skillpoints - The desired course progress to enable the activity
     */
    protected $skill;
    protected $points;
    protected $skillname;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     */
    public function __construct($structure) {
        $this->skill = $structure->skill;
        $this->points = $structure->e;

        $this->skillname = $this->get_skill_name($structure->skill);
    }

    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     *
     * If implementations require a course or modinfo, they should use
     * the get methods in $info.
     *
     * The $not option is potentially confusing. This option always indicates
     * the 'real' value of NOT. For example, a condition inside a 'NOT AND'
     * group will get this called with $not = true, but if you put another
     * 'NOT OR' group inside the first group, then a condition inside that will
     * be called with $not = false. We need to use the real values, rather than
     * the more natural use of the current value at this point inside the tree,
     * so that the information displayed to users makes sense.
     *
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user)
     * @param int $userid User ID to check availability for
     *
     * @return bool True if available
     *
     * @throws \coding_exception
     */
    public function is_available($not, info $info, $grabthelot, $userid) {
        $modinfo = $info->get_modinfo();
        $course = $modinfo->get_course();

        $skillpoints = $this->get_user_skillpoints($course->id, $userid);

        if ($not) {
            return $skillpoints < $this->points;
        }

        if ($skillpoints >= $this->points) {
            return true;
        }

        return false;
    }

    /**
     * Obtains the course progress percentage
     *
     * @param $course
     * @param $userid
     *
     * @return bool|float|int|null
     */
    protected function get_user_skillpoints($courseid, $userid) {
        global $DB;

        $sql = 'SELECT sm.skillid as id, SUM(su.value) as value
                FROM {evokegame_skills_users} su
                INNER JOIN {evokegame_skills_modules} sm ON su.skillmoduleid = sm.id
                INNER JOIN {evokegame_skills} s ON s.id = sm.skillid
                WHERE s.courseid = :courseid AND su.userid = :userid AND sm.skillid = :skillid
                GROUP BY sm.skillid';

        $record = $DB->get_record_sql($sql, ['courseid' => $courseid, 'userid' => $userid, 'skillid' => $this->skill]);

        if (!$record) {
            return 0;
        }

        return $record->value;
    }

    protected function get_skill_name($skillid = null) {
        global $DB;

        if (!$skillid) {
            return '';
        }

        $skill = $DB->get_record('evokegame_skills', ['id' => $skillid], 'id, name');

        return $skill->name;
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * The $full parameter can be used to distinguish between 'staff' cases
     * (when displaying all information about the activity) and 'student' cases
     * (when displaying only conditions they don't meet).
     *
     * If implementations require a course or modinfo, they should use
     * the get methods in $info.
     *
     * The special string <AVAILABILITY_CMNAME_123/> can be returned, where
     * 123 is any number. It will be replaced with the correctly-formatted
     * name for that activity.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     *
     * @return string Information string (for admin) about all restrictions on
     *   this item
     *
     * @throws \coding_exception
     */
    public function get_description($full, $not, info $info) {
        $lang = new \stdClass();
        $lang->skill = $this->skillname;
        $lang->points = $this->points;

        if ($not) {
            return get_string('requires_notfinish', 'availability_skillpoints', $lang);
        }

        return get_string('requires_finish', 'availability_skillpoints', $lang);
    }

    /**
     * Obtains a representation of the options of this condition as a string,
     * for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string() {
        return gmdate('Y-m-d H:i:s');
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return \stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        return (object)['type' => 'skillpoints', 'skillpoints' => $this->skillpoints];
    }
}
