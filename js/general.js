/** 
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @namespace Bms
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
 * defines the biocase software URL response for a given data source
 *
 */
biocaseResponseUrl = "/pywrapper.cgi?dsa=";

/**
 * defines the biocase software endpoint GUI
 *
 */
biocaseQueryUrl = "utilities/queryforms/qf_manual.cgi?dsa=";

/**
 * defines the biocase software endpoint Local Query GUI
 *
 */
biocaseLocalQueryToolUrl = "querytool/main.cgi?dsa=";

/**
 * defines the system messages
 *
 */
message = {};

/**
 * may be overwritten by scripts or GET-parameters
 * 
 */
debugmode = true;

/**
 * may be overwritten by scripts or GET-parameters
 *
 */
verbose = 0;

/**
 * may be overwritten by js/custom.js
 *
 */
spinner = "<span class='glyphicon glyphicon-refresh gly-spin'/>";

/**
 * number of pending calls to the BPS
 * is overwritten dynamically
 *
 */
nbAjaxCalls = 0;

/**
 * loads system messages into global variable "message"
 *
 * @param   {string}  path
 * @returns {boolean}      false
 */
function getMessages(path) {
    $.ajax({
            type: "GET",
            dataType: "text",
            url: path + "./lib/getMessages.php"
        })
        .fail(function () {
            console.log("getMessages failed");
        })
        .always(function () {})
        .done(function (data) {
            //console.log(data);
            message = JSON.parse(data);
            //console.log(message);
            //displaySystemMessage(message["warning"], "warning", 10000);
        });
    return false;
}


/**
 * displays a system message as an answer of an operation
 *
 * @param   {string} msg      the message text
 * @param   {string} alert    info,warning,success,danger
 * @param   {int}    duration
 * @returns {void}
 */
function displaySystemMessage(msg, alert, duration) {
    if (duration === undefined)
        duration = 800;
    $("#system-message").hide();
    $("#system-message").removeClass("success");
    $("#system-message").removeClass("info");
    $("#system-message").removeClass("warning");
    $("#system-message").removeClass("danger");
    $("#system-message").addClass(alert);
    $("#system-message").html(msg);
    $("#system-message").fadeIn(200).delay(duration).fadeOut(200);
}

/**
 * hides a system message
 *
 * @returns {void}
 */
function hideSystemMessage() {
    $("#system-message").fadeOut(200);
}

/**
 * displays a system error message as an answer of an operation
 *
 * @param string $elt the jQuery Element where the message is displayed
 * @param string $msg the message text
 * @param string $mode replace or add at end of the jQuery element elt
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
 * shows number of concurrent requests
 *
 * @returns {void}
 */
function showConcurrentRequests() {
    //    if ($.active > 10)
    //    {
    //        $(".progress-bar").css('width', 20 * $.active + 'px').attr('aria-valuenow', $.active);
    //        $(".progress-bar ").html($.active + message.concurrentRequests);
    //    }

    //console.log("active connections: " + $.active);

    //
    //if ($.active > 3 || nbAjaxCalls > 4)
    //    if (nbAjaxCalls > 3)
    //    {
    //        $("#progress-message").removeClass("success");
    //        $("#progress-message").removeClass("info");
    //        $("#progress-message").removeClass("warning");
    //        $("#progress-message").removeClass("danger");
    //        $("#progress-message").addClass("info");
    //        //$("#system-message").html($.active + " " + message.concurrentRequests);
    //        $("#progress-message").html(nbAjaxCalls + message.concurrentRequests);
    //        //$("#system-message").fadeIn();
    //        $("#progress-message").fadeIn().delay(800).fadeOut();
    //    } else {
    //        $("#progress-message").delay(800).fadeOut();
    //    }
    return;
}

/**
 * writes selected infos to logbook
 *
 * @param   {int}     $idProvider
 * @param   {string}  $schema
 * @param   {int}     $dsa
 * @param   {string}  $concept
 * @param   {string}  $action
 * @param   {float}   $timeElapsed
 * @returns {boolean}              false
 */
function logbook(idProvider, schema, dsa, concept, action, timeElapsed) {
    $.ajax({
            type: "GET",
            url: "./logbook.php",
            data: {
                idProvider: idProvider,
                schema: schema,
                dsa: dsa,
                concept: concept,
                action: action,
                timeElapsed: timeElapsed
            }
        })
        .fail(function () {
            console.log("logbook failed");
        })
        .always(function () {})
        .done(function (data) {
            //console.log(data);
        });
    return false;
}

/**
 * copy selected text portion to Clipboard
 *
 * @param   {object}  $elem jQuery object
 * @returns {boolean}
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
 * @param   {string} $xml a raw XML string
 * @returns {string}
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
$(document).ready(function () {

    // legal infos
    $("a#footer-control").on("click", function () {
        $("#footer").toggle("slow");
    });
    $("#footer").on("click", function () {
        $(this).toggle("slow");
    });

    // system message : general warning
    $("a.warning").on("click", function () {
        displaySystemMessage(message["warning"], "warning", 10000);
    });

    // inhibit console log messages in production mode
    if (!debugmode) {
        console.log = function () {};
    }

});