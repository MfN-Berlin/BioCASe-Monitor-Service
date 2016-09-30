
/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @package Bms
 * 
 * @file biocasemonitor/js/general.js
 * @brief javascript general settings functions
 * 
 * @license GNU General Public License 3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


/////////////////////////////////
// global variables

/**
 * defines the biocase software endpoint
 *
 * @constant {string}
 */
biocaseQueryUrl = "utilities/queryforms/qf_manual.cgi?dsa=";

/**
 * defines the biocase software endpoint Local Query
 *
 * @constant {string}
 */
biocaseLocalQueryToolUrl = "querytool/main.cgi?dsa=";

/**
 * defines the system messages
 *
 * @constant {array}
 */
message = {};

/**
 * may be overwritten by scripts or GET-parameters
 *
 * @constant {boolean}
 */
debugmode = false;

/**
 * may be overwritten by scripts or GET-parameters
 *
 * @constant {integer}
 */
verbose = 0;



/**
 * loads system messages into global variable "message"
 * @returns {boolean} false
 */
function getMessages() {
    $.ajax({
        type: "GET",
        dataType: "text",
        url: "../lib/getMessages.php",
        data: {"filter": 3}
    })
            .fail(function () {
                console.log("getMessages failed");
            })
            .always(function () {
            })
            .done(function (data) {
                //console.log(data);
                message = JSON.parse(data);
                //console.log(message);
            });
    return false;
}


/**
 * displays a system message as an answer of an operation
 *
 * @param {string} msg the message text
 * @returns void
 */
function displaySystemMessage(msg) {
    $("#system-message").html(msg);
    $("#system-message").fadeIn(800).delay(800).fadeOut(800);
}

/**
 * hides a system message
 *
 * @returns void
 */
function hideSystemMessage() {
    $("#system-message").fadeOut(800);
}

/**
 * displays a system error message as an answer of an operation
 *
 * @param {string} elt the jQuery Element where the message is displayed
 * @param {string} msg the message text
 * @param {string} mode replace or add at end of the jQuery element elt
 * @returns void
 */
function displayErrorMessage(elt, msg, mode) {
    var formattedMsg = "<div class='error-message'>" + msg + "</div>";
    if (mode === "append")
        elt.append(formattedMsg);
    else
        elt.html(formattedMsg);
}

/**
 * copy selected text portion to Clipboard
 *
 * @param {object} elem  jQuery object
 * @returns {boolean} succeed
 */
function copyToClipboard(elem) {
// create hidden text element, if it doesn't already exist
    var targetId = "_hiddenCopyText_";
    // must use a temporary form element for the selection and copy
    target = document.getElementById(targetId);
    if (!target) {
        var target = document.createElement("textarea");
        target.style.position = "absolute";
        target.style.left = "-9999px";
        target.style.top = "0";
        target.id = targetId;
        document.body.appendChild(target);
    }
    target.textContent = elem.html();
    // select the content
    var currentFocus = document.activeElement;
    target.focus();
    target.setSelectionRange(0, target.value.length);
    // copy the selection
    var succeed;
    try {
        succeed = document.execCommand("copy");
    } catch (e) {
        succeed = false;
    }
// restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }
// clear temporary content
    target.textContent = "";
    return succeed;
}

/**
 * pretty prints a XML string
 *
 * @param {string} xml a raw XML string
 * @returns {String}
 */
function formatXml(xml) {
    var formatted = '';
    var reg = /(>)(<)(\/*)/g;
    xml = xml.replace(reg, '$1\r\n$2$3');
    var pad = 0;
    jQuery.each(xml.split('\r\n'), function (index, node) {
        var indent = 0;
        if (node.match(/.+<\/\w[^>]*>$/)) {
            indent = 0;
        } else if (node.match(/^<\/\w/)) {
            if (pad !== 0) {
                pad -= 1;
            }
        } else if (node.match(/^<\w[^>]*[^\/]>.*$/)) {
            indent = 1;
        } else {
            indent = 0;
        }

        var padding = '';
        for (var i = 0; i < pad; i++) {
            padding += '  ';
        }

        formatted += padding + node + '\r\n';
        pad += indent;
    });
    return formatted;
}




//////////////////////////////////////////////////////
$(function() {


    $(document).tooltip({
        tooltipClass: "tooltip-biocase"
    });

    $("a#footer-control").on("click", function () {
        $("#footer").toggle("slow");
    });

    $("#footer").on("click", function () {
        $(this).toggle("slow");
    });


// inhibit console log messages in production mode
    if (!debugmode) {
        console.log = function () {
        };
    }

// load system messages into variable "message"
    getMessages();
    
}
);

