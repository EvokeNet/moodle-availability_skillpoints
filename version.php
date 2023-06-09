<?php

/**
 * Version file
 *
 * @package     availability_skillpoints
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023051900;
$plugin->requires = 2022041200;
$plugin->component = 'availability_skillpoints';
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0.0';
$plugin->dependencies = [
    'local_evokegame' => 2023051900
];