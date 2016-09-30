/**
 * BioCASe Monitor 2.0
 *
 * @copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @package Bms
 *
 * @file biocasemonitor/js/consistency.js
 * @brief javascript functions used in the consistency checker
 *
 * variables url and filter are defined in calling script consistency.php
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

/**
 * removes redundant elements in an array
 *
 * @param {Array} inputArray
 * @returns {Array}
 */
function getUnique(inputArray) {
    var outputArray = [];
    for (var i = 0; i < inputArray.length; i++)
    {
        if ((jQuery.inArray(inputArray[i], outputArray)) === -1)
        {
            outputArray.push(inputArray[i]);
        }
    }
    return outputArray;
}



$(function() {

    maxCalls = 100; // default value
    nbAjaxCalls = 0;
    globalTimeElapsed = 0;

    /**
     * counts the number of entries satisfying a given concept
     *
     * @param {string} url - queryURL
     * @param {string} filter  - a complex filter: <like>....</like>
     * @param {string} concept - a capability. e.g.: /DataSets/DataSet/Units/Unit/UnitID
     * @param {int} j
     * @returns {boolean} false
     */
    function getCountConcept(url, filter, concept, j) {
        var startRequest = $.now(); // microseconds
        $("#counter" + j).append("<img alt='loading...' src='../images/loading.gif'/>");
        $.ajax({
            type: "GET",
            url: "../core/getCountConcepts.php",
            dataType: "json",
            data: {"url": url, "filter": filter, "concept": concept, "specifier": 7}
            //specifier = TOTAL + DISTINCT + DROPPED = 1+2+4 = 7
        })
                .fail(function () {
                    console.log("getCountConcepts *** FAILED *** url=" + url + " concept=" + concept + " filter=" + filter);
                    console.log("./services/getCountConcepts.php?url=" + url + "&filter=" + filter + "&concept=" + concept);
                    $("#counter" + j).html();
                })
                .always(function () {
                    //console.log("getCountConcepts finished");
                })
                .done(function (data) {
                    console.log("./services/getCountConcepts.php?url=" + url + "&filter=" + filter + "&concept=" + concept);
                    console.log("getCountConcepts done");
                    //console.log(data);
                    var timeElapsed = ($.now() - startRequest) / 1000; // milliseconds
                    nbAjaxCalls++;

                    // handle counters
                    $("#counter" + j).html("");
                    if (data.hasOwnProperty("total"))
                        $("#counter" + j).append("<div class='total'>" + data.total + "</div>");
                    if (data.hasOwnProperty("distinct"))
                        $("#counter" + j).append("<div class='distinct'>" + data.distinct + "</div>");
                    if (data.hasOwnProperty("dropped"))
                        $("#counter" + j).append("<div class='dropped'>" + data.dropped + "</div>");

                    $("#counter" + j).append('<a class="tooltip"><img src="../images/glyphicons/glyphicons-196-circle-info.png"/><span></span></a>');
                    $("#counter" + j).show();
                    $("#counter" + j + " a span").append("<div>data received after <b>" + timeElapsed + "ms</b></div>");

                });
        return false;
    }


    /**
     * gets example values of entries satisfying a given concept
     *
     * @param {string} url - queryURL
     * @param {string} filter  - a complex filter: <like>....</like>
     * @param {string} concept - a capability. e.g.: /DataSets/DataSet/Units/Unit/UnitID
     * @param {int} j
     * @returns {boolean} false
     */
    function getExampleValues(url, filter, concept, j) {
        $("#examplevalue" + j).html("<img alt='loading...' src='../images/loading.gif'/>");
        $.ajax({
            type: "GET",
            url: "../consistency/getExampleValues.php",
            dataType: "json",
            data: {"url": url, "filter": filter, "concept": concept}
        })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    console.log("getExampleValues failed");
                    console.log("getExampleValues.php?url=" + url + "&filter=" + filter + "&concept=" + concept);
                    console.log(textStatus + " *** " + errorThrown + " *** ");
                    console.log(jqXHR);
                    $("#examplevalue" + j).html("<span title='" + errorThrown + ": \n\n" + jqXHR.responseText + "'>" + textStatus + "</span>");
                    $("#row" + j).addClass("error");
                })
                .always(function () {
                    //console.log("finished");

                    var request = '<?xml version="1.0" encoding="UTF-8"?>\n\
                            <request xmlns="http://www.biocase.org/schemas/protocol/1.3">\n\
                                <header>\n\
                                    <type>scan</type>\n\
                                </header> \n\
                                <scan>\n \
                                    <requestFormat>http://www.tdwg.org/schemas/abcd/2.06</requestFormat>\n \
                                    <concept>' + concept + '</concept>\n \
                                    <filter>' + filter + '</filter>\n \
                                </scan>\n \
                            </request>';
                    $("#concept" + j).html("<a target='_blank' href='" + url + "&query=" + request + "'>" + concept + "</a>");

                    if (verbose >= 3) {
                        var requestFormatted = formatXml(request)
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                //.replace(/>/g,'&gt;')
                                //.replace(/\t/g,'    ')
                                //.replace(/\n/g,'<br/>')
                                ;
                        var requestSource = " <a href='#row" + j + "'><img src='../images/glyphicons/glyphicons-52-eye-open.png' alt='copy to clipboard' title='click to show/hide the xml request and copy it to the clipboard' width='20' /></a>";
                        requestSource += "<pre class='request' id='request" + j + "'>" + requestFormatted + "</pre>";
                        $("#concept" + j).append(requestSource);

                        $("#concept" + j + " img").on("click", function (event) {
                            event.preventDefault();
                            $("#concept" + j + " pre").toggle();
                            copyToClipboard($("#concept" + j + " pre"));
                            //displaySystemMessage("request copied to clipboard.");
                        });
                    }

                })
                .done(function (data) {
                    console.log("getExampleValues done");
                    //console.log("getExampleValues done for record " + j);
                    //console.log(data);
                    nbAjaxCalls++;
                    //console.log("calls so far: " + nbAjaxCalls);
                    //console.log(j + ": " + getUnique(data.examples).length + " examplevalues available: " + data.examples);
                    //console.log(getUnique(data.examples));

                    if (data.error) {
                        $("#examplevalue" + j).html("<span title='" + data.error + "'>---</span>");
                        return;
                    }
                    $("#examplevalue" + j).html("");
                    console.log("row " + j + " length=" + data.examples.length);
                    if (data.examples && data.examples.length > 1) {
                        var allExamples = getUnique(data.examples);
                        var examples = allExamples.slice(1, 10).sort();
                        $("#examplevalue" + j).html("<ul>");
                        $("#examplevalue" + j + " ul").append(examples.join("<li>"));
                        if (allExamples.length > 11) {

                            $("#examplevalue" + j).append(" <a>at least " + (allExamples.length - 1) + " values available.  See more...</a>");
                            $("#examplevalue" + j + " a").on("click",
                                    function () {
                                        var k = 100;
                                        do {
                                            var moreExamples = allExamples.slice(1, k).sort();
                                            $("#examplevalue" + j).html("<ul>");
                                            $("#examplevalue" + j + " ul").html(moreExamples.join("<li>"));
                                            //$("#examplevalue" + j).append(" <a>at least " + (allExamples.length - 1) + " values available.  See more...</a>");
                                            k += 100;
                                        } while (k < allExamples.length - 1);
                                    });
                        }
                        $("#count" + j).show();
                        $("#count" + j).on("click", function () {
                            console.log("counting values for url=" + url + " --filter=" + filter + " --concept=count" + concept);
                            getCountConcept(url, filter, concept, j);
                        });
                        $("#examplevalue" + j).append("</ul>");
                    } else {
                        if ($("#status-ABCD" + j).html() == "M") {
                            $("#row" + j).addClass("error");
                            $("#examplevalue" + j).addClass("errormessage");
                            $("#examplevalue" + j).html(" MANDATORY");
                        }
                        if ($("#status-ABCD" + j).html() == "H") {
                            $("#row" + j).addClass("error");
                            $("#examplevalue" + j).addClass("errormessage");
                            $("#examplevalue" + j).html(" HIGHLY RECOMMENDED within the GFBIO context");
                        }
                        if ($("#status-ABCD" + j).html() == "R") {
                            $("#row" + j).addClass("error");
                            $("#examplevalue" + j).addClass("errormessage");
                            $("#examplevalue" + j).html(" RECOMMENDED within the GFBIO context");
                        }
                    }


                });
        return false;
    }


    /**
     * gets all capabilities and populates the data table
     *
     * @param {int} idProvider
     * @param {string} dsa
     * @returns {boolean} false
     */
    function processCapabilities(idProvider, dsa) {
        $.ajax({
            type: "GET",
            url: "../services/capabilities/index.php",
            dataType: "json",
            //data: {"url": encodeURI(url), "output": "json", "localId": ""}
            data: {"provider": idProvider, "dsa": dsa, "format": "json", "localId": ""}
        })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    console.log("capabilities failed");
                    console.log("provider=" + idProvider + " dsa=" + dsa);
                    //console.log(encodeURI(url));
                    console.log(textStatus + ": " + errorThrown);
                })
                .always(function () {
                    //console.log("finished");
                })
                .done(function (data) {
                    console.log("capabilities done.");
                    console.log(abcd);

                    //console.log(data);
                    maxCalls = data.concepts.length - 1;

                    $("#menuInfo div").append(data.url);
                    for (var j = 1; j < data.concepts.length; j++) {
                        $("#consistency").append("<tr id='row" + j + "'>"
                                + "<td><a name='row" + j + "'></a>" + j + "</td>"
                                + "<td id='concept" + j + "'>" + data.concepts[j].dataset + "</td>"
                                + "<td class='searchable" + data.concepts[j].searchable + "'>" + data.concepts[j].searchable + "</td>"
                                + "<td>" + data.concepts[j].datatype + "</td>"
                                + "<td id='status-ABCD" + j + "'></td>"
                                + "<td id='target-concept" + j + "'></td>"
                                + "<td id='examplevalue" + j + "'></td>"
                                + "<td id='counter" + j + "' class='counter'>" + "<button id='count" + j + "' class='counter'>count</button></td>"
                                + "<td id='checkall" + j + "'></td>"
                                + "</tr>");
                        var myConcept = data.concepts[j].dataset;
                        console.log(abcd[myConcept]);
                        $("#status-ABCD" + j).html(abcd[myConcept].status);
                        $("#row" + j).addClass(abcd[myConcept].status);

                        if (data.concepts[j].searchable == "1") {
                            getExampleValues(data.url, filter, data.concepts[j].dataset, j);
                        } else {
                            maxCalls--;
                            $("#count" + j).hide();
                            console.log(j + " is not a searchable dataset: " + data.concepts[j].dataset);
                            console.log("maxCalls revised = " + maxCalls);
                        }
                    }
                    console.log("maxCalls final = " + maxCalls);
                    $("#max-calls").text(maxCalls);

                });
        return false;
    }


    // launch processing
    processCapabilities(idProvider, dsa);

    ///////////////////////////////
    // global progressbar
    //

    globalProgressbar = $("#progressbar");
    globalProgressLabel = $("#progressbar .progress-label");
    maxCalls = parseInt($("#max-calls").text());

    globalProgressbar.progressbar({
        max: 100,
        value: false,
        change: function () {
            globalProgressLabel.text(
                    globalProgressbar.progressbar("value") + "%"
                    );
        },
        complete: function () {
            globalProgressLabel.text(
                    "all done"
                    );
        }
    });

    function progress() {
        var val = globalProgressbar.progressbar("value") || 0;
        var currentVal = Math.round(100 * (nbAjaxCalls / maxCalls));
        //console.log("global progression: " + nbAjaxCalls + "/" + maxCalls);
        globalProgressbar.progressbar("value", currentVal)
                .removeClass("beginning middle end")
                //.addClass(currentVal < 34 ? "beginning" : currentVal < 67 ? "middle" : "end")
                .addClass("progress-gradient")
                ;
        if (val < 100)
        {
            setTimeout(progress, 200);
        }
    }


    setTimeout(progress, 2000);
});
