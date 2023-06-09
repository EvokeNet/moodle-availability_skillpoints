<?php

/**
 * Front-end class.
 *
 * @package     availability_skillpoints
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace availability_skillpoints;

defined('MOODLE_INTERNAL') || die();

/**
 * Front-end class.
 *
 * @package    availability_skillpoints
 * @copyright  2020 onwards Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {
    /**
     * Gets a list of string identifiers (in the plugin's language file) that
     * are required in JavaScript for this plugin. The default returns nothing.
     *
     * You do not need to include the 'title' string (which is used by core) as
     * this is automatically added.
     *
     * @return array Array of required string identifiers
     */
    protected function get_javascript_strings() {
        return ['label_skill', 'label_points'];
    }

    protected $cachekey = '';

    protected function get_javascript_init_params($course, \cm_info $cm = null,
                                                  \section_info $section = null) {
        global $DB;

        // Use cached result if available. The cache is just because we call it
        // twice (once from allow_add) so it's nice to avoid doing all the
        // print_string calls twice.
        $cachekey = 'skillpoints-' . $course->id;

        if ($cachekey !== $this->cachekey) {
            $records = $DB->get_records('evokegame_skills', ['courseid' => $course->id], 'name', 'id, name');

            $this->cachekey = $cachekey;

            if (!$records) {
                return $this->cacheinitparams = [];
            }

            $this->cacheinitparams = [array_values($records)];
        }

        return $this->cacheinitparams;
    }

    protected function allow_add($course, \cm_info $cm = null,
                                 \section_info $section = null) {

        $isgameenabledincourse = get_config('local_evokegame', 'isgameenabledincourse-' . $course->id);

        if (is_null($isgameenabledincourse) || $isgameenabledincourse == 1) {
            return !empty($this->get_javascript_init_params($course, $cm, $section));
        }

        return false;
    }
}
