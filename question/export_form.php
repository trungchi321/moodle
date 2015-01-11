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
 * Defines the export questions form.
 *
 * @package    moodlecore
 * @subpackage questionbank
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

require_once($CFG->libdir . '/questionlib.php');

/**
 * Form to export questions from the question bank.
 *
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_export_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;

        $defaultcategory = $this->_customdata['defaultcategory'];
        $contexts = $this->_customdata['contexts'];

        // Choice of format, with help.
        $mform->addElement('header', 'fileformat', get_string('fileformat', 'question'));
        $radioarray = array();
        $fileformatnames = get_import_export_formats('export');
        $i = 0 ;
        foreach ($fileformatnames as $shortname => $fileformatname) {
            $currentgrp1 = array();
            $currentgrp1[] = $mform->createElement('radio', 'format', '', $fileformatname, $shortname);
            $mform->addGroup($currentgrp1, "formathelp[{$i}]", '', array('<br />'), false);

            if (get_string_manager()->string_exists('pluginname_help', 'qformat_' . $shortname)) {
                $mform->addHelpButton("formathelp[{$i}]", 'pluginname', 'qformat_' . $shortname);
            }

            $i++ ;
        }
        $mform->addRule("formathelp[0]", null, 'required', null, 'client');

        // Export options.
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('static', 'catlist', '',
            $this->get_all_block_categories(question_category_options($contexts), $defaultcategory));
        $mform->addHelpButton('catlist', 'exportcategory', 'question');

        $categorygroup = array();
        $categorygroup[] = $mform->createElement('checkbox', 'cattofile', '', get_string('tofilecategory', 'question'));
        $categorygroup[] = $mform->createElement('checkbox', 'contexttofile', '', get_string('tofilecontext', 'question'));
        $mform->addGroup($categorygroup, 'categorygroup', '', '', false);
        $mform->disabledIf('categorygroup', 'cattofile', 'notchecked');
        $mform->setDefault('cattofile', 1);
        $mform->setDefault('contexttofile', 1);

        // Set a template for the format select elements
        $renderer = $mform->defaultRenderer();
        $template = "{help} {element}\n";
        $renderer->setGroupElementTemplate($template, 'format');

        // Submit buttons.
        $this->add_action_buttons(false, get_string('exportquestions', 'question'));
    }

    /**
     * create html block string for array categories
     * @param array $contexts, categories
     * @param string $defaultcategory, defaul category
     * @return string,  format block html of list categories
     */
    public function get_all_block_categories($contexts, $defaultcategory) {
        $blockoutput = '';

        foreach ($contexts as $key => $categories) {
            $blockoutput .= html_writer::start_tag('li');
            $blockoutput .= html_writer::tag('strong', $key);
            $blockoutput .= html_writer::start_tag('ul');
            $blockoutput .= $this->block_categories($categories, $defaultcategory);
            $blockoutput .= html_writer::end_tag('ul');
            $blockoutput .= html_writer::end_tag('li');
        }

        global $OUTPUT;
        $result = '';
        $result .= $OUTPUT->box_start('boxwidthwide boxaligncenter generalbox questioncategories contextlevel');
        $result .= $blockoutput;
        $result .= $OUTPUT->box_end();

        return $result;
    }

    /**
     * create html string for array categories
     * @param array $categories, categories
     * @param string $defaultcategory, defaul category
     * @return string, format html of list categories
     */
    public function block_categories($categories, $defaultcategory) {
        $output = '';
        $ischecking = false;
        $levelchecking = -1;
        $levellastparent = -1;
        $previouscategory = null;

        foreach ($categories as $key => $value) {

            if ($ischecking && substr_count($value, '&nbsp;') <= $levelchecking) {
                $ischecking = false;
            }

            if ($key == $defaultcategory) {
                $levelchecking = substr_count($value, '&nbsp;');
                $ischecking = true;
            }

            if (substr_count($value, '&nbsp;') > substr_count($previouscategory, '&nbsp;')) {
                $output .= html_writer::start_tag('ul');
                $levellastparent = substr_count($previouscategory, '&nbsp;');
            } else if (substr_count($value, '&nbsp;') < substr_count( $previouscategory, '&nbsp;')) {
                $numparent = (substr_count( $previouscategory, '&nbsp;') - substr_count($value, '&nbsp;')) / 3;

                for ($i = 0; $i < $numparent; $i++) {
                    $output .= html_writer::end_tag('ul');
                    $output .= html_writer::end_tag('li');
                }

                $levellastparent = substr_count($value, '&nbsp;');
            }

            $output .= $this->item_html($value, $ischecking);
            $previouscategory = $value;
        }

        $output .= html_writer::end_tag('li');
        for ($i = 0; $i <= $levellastparent / 3; $i++) {
            $output .= html_writer::end_tag('ul');
            $output .= html_writer::end_tag('li');
        }

        return $output;
    }

    /**
     * create html string for item category
     * @param string $content, category
     * @param boolean $ischecked, checkbox is checked or not
     * @return string, format html of item category
     */
    public function item_html($content, $ischecked) {
        $output = '';

        $output .= html_writer::start_tag('li');
        $output .= html_writer::start_tag('label');

        if ($ischecked) {
            $output .= html_writer::checkbox('cat', 1, 'true', '',
                array('onclick' => 'checkChildren(this);'));
        } else {
            $output .= html_writer::checkbox('cat', 1, '', '',
                array('onclick' => 'checkChildren(this);'));
        }

        $output .= str_replace('&nbsp;', '', $content);
        $output .= html_writer::end_tag('label');

        return $output;
    }
}
