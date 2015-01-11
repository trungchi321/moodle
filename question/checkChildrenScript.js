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
 * JavaScript library for dealing with the question flags.
 *
 * This script, and the YUI libraries that it needs, are inluded by
 * the $PAGE->requires->js calls in question_get_html_head_contributions in lib/questionlib.php.
 *
 * @package    moodlecore
 * @subpackage questionengine
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @author Pham Quoc Dai - VSTU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function checkChildren(node) {

    var children = node.parentNode.childNodes;
    var firstInput;

    console.log("parent: " + node.parentNode.parentNode.nodeName);

    for (var i = 0; i < children.length; i++) {
        if (children[i].tagName == "INPUT") {
            firstInput = children[i];
        }
    }

    checkAll(node.parentNode.parentNode, firstInput.checked);
}


function checkAll(node, checked) {
    var children = node.childNodes;
    for (var i = 0; i < children.length; i++) {
        if (children[i].tagName == "INPUT") {
            children[i].checked = checked;
        } else {
            checkAll(children[i], checked);
        }
    }
}
