/* global spinner, queryUrl, currentProgress */

/**
 * BioCASe Monitor 2.1
 *
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @namespace Consistency
 * @file biocasemonitor/js/consistency.js
 * @brief javascript functions used in the consistency checker.
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
 * 
  * The variables biocaseUrl and filter are defined in calling script biocasemonitor/consistency/index.php
 */

/**
 * removes redundant elements in an array
 *
 * @param array $inputArray
 * @returns Array
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

/**
 * match a given rule, passed by name
 * @todo to be written
 * @param string $functionName
 * @param string $url
 * @returns boolean
 */
function matchRule(functionName, url) {
    // put here the calls to the functionName
    return false;
}


function isValidURI(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
}

/**
 * validates Citation  <Authors>. (<Publication_year>). <Title>. [Dataset]. <VersionNr>. Data Publisher: <Data_center_name>. <URI>.
 * @todo adapt to consensus document
 *
 * @param string $str
 * @returns boolean
 */
function isValidCitation(str) {
    return true;
     /*
     var authors = '([A-Za-z\s]*)',
     pubyear = '\(([0-9]*)\)',
     title = '(.*)',
     dataset = '(\[.*\])',
     version = '(.*)',
     datapublisher = ' Data Publisher: ([A-Za-z\s]*)',
     //uri = "(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?"
     uri = '(.*)'
     ;
     var sep = '\.\s+';
     
     var regexp = new RegExp(
     '^'
     + authors + sep
     + pubyear + sep
     + title + sep
     + dataset + sep
     + version + sep
     + datapublisher + sep
     + uri
     + '$'
     , 'i');
     console.log(str + " against regexp: " + regexp);
     console.log("yesorno: " + regexp.test(str));
     return regexp.test(str);
     */

}
////////////////////////////////////////////////



/**
 * check rules
 *
 * @param int $idProvider
 * @param string $dsa
 * @param string $filter
 * @param string $mapping
 * @returns object 
 */
function checkRules(idProvider, dsa, filter, mapping) {
    console.log("checking rules with mapping=" + mapping + " for named dsa=" + dsa);
    var startRequest = $.now();
    $("#supported-schemas").append(spinner);

    $.ajax({
        type: "GET",
        url: "../consistency/checkRules.php",
        data: {"dsa": dsa, "mapping": mapping},
        dataType: "json"
    })
            .fail(function () {
                console.log("checkRules failed");
            })
            .always(function () {
                //console.log("finished");
                $("#supported-schemas").append(($.now() - startRequest) + "ms");

            })
            .done(function (data) {
                console.log("checkRules done");
                console.log(data);

                if ($.now() - startRequest >= 60000) {
                    displaySystemMessage("timeout! giving up.", "danger", 5000);
                    $("#supported-schemas").html("<div class='alert alert-danger'>timeout! giving up.</div>");
                    return {};
                }

                //value of global variable is now known.
                sourceSchema = data.sourceSchema;
                console.log("global sourceSchema: " + sourceSchema);

                var supportedSchemas = "<table id='supported-schemas-table'>";
                for (var j = 0; j < data.supportedSchemas.length; j++) {
                    supportedSchemas += "<tr><td valign='top'><b>" + data.supportedSchemas[j][0] + "</b> <td>" + data.supportedSchemas[j][1];
                }
                supportedSchemas += "</table>";
                $("#supported-schemas").html(supportedSchemas);

                // populate missing mandatory elements
                $("#nb-missing-mandatory").html(data.missing.length);
                if (data.missing.length > 0) {
                    $("#missing-mandatory").addClass("error");
                    $("#missing-mandatory").html("");
                    for (var j = 0; j < data.missing.length; j++) {
                        $("#missing-mandatory").append("<div>" + data.missing[j] + "</div>");
                    }
                }

                //populate main table
                $("table#consistency tbody").html("");
                var records = Object.values(data.checkedRecords);

                console.log(records.length + " checked records displayed.");
                console.log(Object.values(data.mapped_elements).length + " mapped elements displayed.");

                $("#nb-total").text(records.length);
                $("#nb-mapped-with-rules").text(Object.values(data.mapped_elements).length);
                $("#nb-mapped").text(Object.values(data.allMappedElements).length);
                $("#nb-capabilities").text(Object.values(data.capabilities).length);

//                // get number of (not-)searchable records
//                nbSearchable = 0;
//                jQuery.map(data.checkedRecords, function (x) {
//                    if (x.searchable !== undefined)
//                        nbSearchable += parseInt(x.searchable);
//                    return false;
//                });
//
//                nbNotSearchable = records.length - nbSearchable;

                $("#nb-searchable").text(nbSearchable);
                $("#nb-notsearchable").text(nbNotSearchable);

                $("#debuginfo").html("MAPPED<ol></ol>");
                for (var j = 0; j < data.mapped_elements.length; j++) {
                     $("#debuginfo ol").append("<li>" + data.mapped_elements[j].source_element + " -> " + data.mapped_elements[j].target_element);
                }
    
                $("#debuginfo").append("<hr/>MAPPED W/O RULES<ol></ol>");
                for (var j = 0; j < Object.values(data.allMappedElements).length; j++) {
                     $("#debuginfo  ol").append("<li>" + Object.values(data.allMappedElements)[j].source_element + " -> " + data.mapped_elements[j].target_element);
                }


                for (var j = 0; j < records.length; j++) {

                    console.log("row " + j);
                    console.log(records[j]);
                    //console.log("target: " + records[j].target_element);
                    //console.log("searchable? " + records[j].searchable);

                    //if (records[j].schema_mapping)
                    if (records[j])
                    {
                        var isSearchable = "1";
                        if (records[j].searchable === "1")
                            isSearchable = "1";
                        else if (records[j].searchable === "0")
                            isSearchable = "0";
                        else if (records[j].searchable === undefined)
                            isSearchable = "--";

                        $("table#consistency").append("<tr id='row" + j + "' class='searchable-" + records[j].searchable + "'>"
                                + "<td><a name='row" + j + "'></a>" + j + "</td>"
                                + "<td id='concept" + j + "'>" + records[j].concept + "</td>"
                                + "<td id='moreinfo" + j + "'></td>"
                                + "<td>" + isSearchable + "</td>"
                                + "<td>" + (records[j].datatype ? records[j].datatype : "--") + "</td>"
                                + "<td class='target-element' id='target-concept" + j + "'>" + records[j].target_element + " </td>"
                                + "<td id='tag" + j + "'></td>"
                                + "<td id='rules" + j + "'></td>"
                                + "<td id='counter" + j + "' class='counter'>" + "<button type='button' class='btn btn-xs btn-primary' id='count" + j + "'>count</button></td>"
                                + "<td id='examplevalue" + j + "' class='example'><button type='button' class='btn btn-xs btn-info' id='example" + j + "'>show</button></td>"
                                + "</tr>");

                        // get rule
                        $("#rules" + j).html(records[j].rule ? records[j].rule : "--");

                        // get tag
                        $("#tag" + j).html(records[j].tag);

                        // get reference of source element
                        if (records[j].source_reference && records[j].source_reference !== undefined) {
                            //$("#concept" + j)
                            $("#moreinfo" + j)
                                    .append(" <a target='tdwg-terms' title='get more infos about this element' href='"
                                            + records[j].source_reference
                                            + "'><span  class='glyphicon glyphicon-info-sign  glyphicon-right-position'/></a> ");
                        }

                        // link to BPS query toool
                        var request = '<?xml version="1.0" encoding="UTF-8"?>\n\
                                        <request xmlns="http://www.biocase.org/schemas/protocol/1.3">\n\
                                                <header>\n\
                                                        <type>scan</type>\n\
                                                </header> \n\
                                                <scan>\n \
                                                        <requestFormat>' + sourceSchema + '</requestFormat>\n \
                                                        <concept>' + records[j].concept + '</concept>\n \
                                                        <filter>' + filter + '</filter>\n \
                                                </scan>\n \
                                        </request>';
                        $("#moreinfo" + j).append(" <a target='bps-response' data-toggle='tooltip' title='show XML response for SCAN request' href='"
                                + queryUrl + "&query="
                                + request + "'><span class='glyphicon glyphicon-eye-open glyphicon-right-position'/></a> ");

                        // not a capability
                        if (records[j].searchable === undefined) {
                            $("#examplevalue" + j).html("--");
                            $("#moreinfo" + j).append(" <span class='glyphicon glyphicon-warning-sign  glyphicon-right-position' title='not a capability of this dataset' /> ");
                        }

                        // rules and tags are filled in and checked here:
                        if (records[j].searchable === "1" || records[j].searchable === "true") {

                            console.log(j + ": check for errors: provider=" + idProvider + "  filter=" + filter + "  mapping=" + mapping + " schema=" + sourceSchema);

                            // CHECK FOR ERRORS
                            checkForErrors(idProvider, filter, records[j].source_element, sourceSchema, mapping, j, nbSearchable);

                            // GET EXAMPLE VALUES
                            var jdata = {"provider": idProvider, "filter": filter, "dataset": records[j].source_element, "row": j};
                            $("#example" + j).on("click", jdata, extractExampleValues);

                        } else {
                            $("#count" + j).hide();
                            $("#example" + j).hide();
                        }
                    }
                }

                console.log(" items  displayed.\n---------");

 
                $('#consistency').DataTable({
                    "order": [[2, "asc"]],
                    "paging": false,
                    "columnDefs": [
                        {"searchable": false, "targets": [-1, -2, -8]}
                    ]
                });

            });
}


/**
 * counts the number of entries satisfying a given concept
 *
 * @param int $idProvider
 * @param string $schema
 * @param string $filter   a complex filter: <like>....</like>
 * @param string $concept  a capability. e.g.: /DataSets/DataSet/Units/Unit/UnitID
 * @param int $j  row number
 * @returns boolean false
 */
function cardinalConcept(idProvider, schema, filter, concept, j) {
    var startRequest = $.now(); // microseconds
    $("#counter" + j).append(spinner);
    $.ajax({
        type: "GET",
        url: "./cardinalConcept.php",
        dataType: "json",
        data: {"idProvider": idProvider, "schema": schema, "url": queryUrl, "filter": filter, "concept": concept, "specifier": 7}
        //specifier = TOTAL + DISTINCT + DROPPED = 1+2+4 = 7
    })
            .fail(function () {
                console.log("cardinalConcept *** FAILED ***  url=" + queryUrl + " concept=" + concept + " filter=" + filter);
                $("#counter" + j).html();
            })
            .always(function () {
                //console.log("cardinalConcept finished");

            })
            .done(function (data) {
                console.log("cardinalConcept done");
                console.log(data);
                var timeElapsed = ($.now() - startRequest) / 1000; // seconds

                // display data
                $("#counter" + j).html("");
                if (data.hasOwnProperty("total"))
                    $("#counter" + j).append("<div class='total'>" + data.total + "</div>");
                if (data.hasOwnProperty("distinct"))
                    $("#counter" + j).append("<div class='distinct'>" + data.distinct + "</div>");
                if (data.hasOwnProperty("dropped"))
                    $("#counter" + j).append("<div class='dropped'>" + data.dropped + "</div>");

                $("#counter" + j).append(
                        '<a data-toggle="tooltip" title="data received after '
                        + timeElapsed +
                        's"><span class="glyphicon glyphicon-info-sign"/></a>');
            });
    return false;
}



/**
 * check concept entry for consistency
 *
 * @param int $idProvider
 * @param string $filter   a complex filter: <like>....</like>
 * @param string $concept  a capability. e.g.: /DataSets/DataSet/Units/Unit/UnitID
 * @param string $schema
 * @param string $mapping
 * @param int $j number of displayed row
 * @param int $total total number
 * @returns boolean false
 */
function checkForErrors(idProvider, filter, concept, schema, mapping, j, total) {
    $.ajax({
        type: "GET",
        url: "../consistency/checkForErrors.php",
        dataType: "json",
        data: {"idProvider": idProvider, "url": queryUrl, "filter": filter, "concept": concept, "schema": schema, "mapping": mapping}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("checkForErrors failed. concept: " + concept);
                $("#examplevalue" + j).html(textStatus + " <a data-toggle='tooltip' title='" + errorThrown + ": \n\n" + jqXHR.responseHtml + "'>" +
                        "<span class='glyphicon glyphicon-info-sign'/></a>");
                $("#examplevalue" + j).addClass("errormessage");
                $("#row" + j).addClass("error");
            })
            .always(function () {
                var request = '<?xml version="1.0" encoding="UTF-8"?>\n\
                            <request xmlns="http://www.biocase.org/schemas/protocol/1.3">\n\
                                <header>\n\
                                    <type>scan</type>\n\
                                </header> \n\
                                <scan>\n \
                                    <requestFormat>' + schema + '</requestFormat>\n \
                                    <concept>' + concept + '</concept>\n \
                                    <filter>' + filter + '</filter>\n \
                                </scan>\n \
                            </request>';
                /*
                 $("#moreinfo" + j).append(" <a target='bps-response' data-toggle='tooltip' title='show XML response for SCAN request' href='"
                 + queryUrl + "&query="
                 + request + "'><span class='glyphicon glyphicon-eye-open glyphicon-right-position'/></a> ");
                 */
            })
            .done(function (data) {

                //console.log("checkForErrors done");
                //console.log(j + ": checkForErrors done. concept=" + concept + " schema=" + schema);
                //console.log(data);
                var content = data.content;

                currentProgress++;
                var currentProgressPercentage = Math.ceil(100 * (currentProgress / total));
                $('.progress-bar').css('width', currentProgressPercentage + '%').attr('aria-valuenow', currentProgressPercentage).text("[" + j + "] " + concept);

                //console.log("progress: " + currentProgress + "/" + total);

                if (currentProgressPercentage >= 100) {
                    $('.progress-bar').text("all done");
//                    // moved to checkRules
//                    $('#consistency').DataTable({
//                        "order": [[2, "desc"], [1, "asc"]],
//                        "paging": false,
//                        "columnDefs": [
//                            {"searchable": false, "targets": [-1, -2, -8]}
//                        ]
//                    });
                }


                $("#count" + j).show();
                $("#count" + j).on("click", function () {
                    //console.log("counting values for --filter=" + filter + " --concept-count" + concept);
                    cardinalConcept(idProvider, schema, filter, concept, j);
                });
                var rules = data.rule;
                // matching the rules

// @todo  take functionNames als parameters of function
//
                // unused ! unique is a tag, not a rule.
//                    if (rules.search("unique") >= 0) {
//                        console.log(j + ": checking uniqueness");
//                        checkUnique(url, filter, concept, j);
//                    }
                if (rules && rules.search("isDateTime") >= 0) {
                    // @tdo tobe defined
                }

                if (rules && rules.search("notEmpty") >= 0) {

                    //console.log("processing rule notEmpty");
                    // console.log(data);
                    if (data.notEmpty == 0 || content == "") {
                        $("#examplevalue" + j).text(content);
                        if ($("#tag" + j).html() == "M") {
                            $("#row" + j).addClass("error");
                            $("#rules" + j).addClass("errormessage");
                            nbError++;
                        }
                        if ($("#tag" + j).html() == "H") {
                            $("#row" + j).addClass("warning");
                            $("#rules" + j).addClass("errormessage");
                            nbWarning++;
                        }
                        if ($("#tag" + j).html() == "R") {
                            $("#row" + j).addClass("warning");
                            $("#rules" + j).addClass("errormessage");
                            nbInfo++;
                        }

                    } else
                        $("#examplevalue" + j).text(content);
                }

                if (rules && rules.search("isURI") >= 0 && content.length > 0) {
                    $("#examplevalue" + j).text(content);
                    if (!isValidURI(content)) {
                        $("#row" + j).addClass("error");
                        $("#rules" + j).addClass("errormessage");
                        $("#examplevalue" + j).addClass("errormessage");
                        $("#examplevalue" + j).html("URL exception:<br/>***" + content + "***");
                        nbError++;
                    }
                }

                if (rules && rules.search("isCitation") >= 0 && content.length > 0) {
                    //console.log(j + ": checking if ***" + content + "*** is a valid Citation");
                    //console.log(isValidCitation(content));
                    $("#examplevalue" + j).text(content);
                    if (!isValidCitation(content)) {
                        $("#row" + j).addClass("error");
                        $("#rules" + j).addClass("errormessage");
                        $("#examplevalue" + j).addClass("errormessage");
                        $("#examplevalue" + j).html("Citation exception:<br/>***" + content + "***");
                        nbError++;
                    }
                }

                $("#infoline .error   .cardinal").text(nbError + parseInt($("#nb-missing-mandatory").text()));
                $("#infoline .warning .cardinal").text(nbWarning);
                $("#infoline .info    .cardinal").text(nbInfo);

            });
}

/**
 * gets example values of entries satisfying a given concept
 *
 * @param int $provider  idProvider
 * @param string $schema  data schema
 * @param string $url  queryURL
 * @param string $filter   a complex filter: <like>....</like>
 * @param string $concept  a capability. e.g.: /DataSets/DataSet/Units/Unit/UnitID
 * @param int $j row number
 * @returns boolean false
 */
function getExampleValues(provider, schema, url, filter, concept, j) {
    $("#examplevalue" + j).html(spinner);
    $.ajax({
        type: "GET",
        url: "../consistency/getExampleValues.php",
        dataType: "json",
        data: {"provider": provider, "schema": schema, "url": url, "filter": filter, "concept": concept}
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
            })
            .done(function (data) {
                console.log("getExampleValues done");
                if (data.error) {
                    $("#examplevalue" + j).html("<span title='" + data.error + "'>---</span>");
                    return false;
                }
                $("#examplevalue" + j).text("");
                console.log("row " + j + " length=" + data.examples.length);
                console.log(data.examples);
                if (data.examples && data.examples.length > 1) {

                    var allExamples = getUnique(data.examples);
                    var examples = allExamples.slice(1, 10).sort();

                    $("#examplevalue" + j).text("");
                    $("#examplevalue" + j).append(examples.join("<br/>"));
                    if (allExamples.length > 11) {

                        $("#examplevalue" + j).append(" <br/><a>at least " + (allExamples.length - 1) + " values available.  See more...</a>");
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
                } else {
                    if ($("#tag" + j).html() == "M") {
                        $("#row" + j).addClass("error");
                        $("#tag" + j).addClass("errormessage");
                    }
                    if ($("#tag" + j).html() == "H") {
                        $("#row" + j).addClass("error");
                        $("#tag" + j).addClass("errormessage");
                    }
                    if ($("#tag" + j).html() == "R") {
                        $("#row" + j).addClass("error");
                        $("#tag" + j).addClass("errormessage");
                    }
                }

            });
    return false;
}

/**
 * gets some example values of entries , called onClick.
 *
 * @param {object} event
 * @returns boolean false
 */
function extractExampleValues(event) {
    console.log(event.data.row + ": getting example values for source_element " + event.data.dataset);
    console.log(event.data);

    getExampleValues(event.data.provider, sourceSchema, queryUrl, event.data.filter, event.data.dataset, event.data.row);
    return false;
}


/**
 * gets all capabilities, checks against mandatory concepts, and populates the data table
 *
 * 1. gets schema mapppings from DB
 * 2. gets capabilities
 * 3. extracts schemas from capabilities
 *
 * @param int $idProvider
 * @param string $dsa
 * @param string $filter
 * @returns boolean false
 *
 */
function fire(idProvider, dsa, filter) {
    var currentMapping = $("select#mapping option:selected").val();

    console.log("processing with selected mapping: " + currentMapping);

    checkRules(idProvider, dsa, filter, currentMapping);
    return false;
}



$(document).ready(function () {

    mandatoryConcepts = "";
    globalTimeElapsed = 0;

    nbError = 0;
    nbWarning = 0;
    nbInfo = 0;

    nbSearchable = 0;
    nbNotSearchable = 0;

// load system messages into variable "message"
    getMessages("../");

// launch processing
    console.log($("form").serialize());

    fire(
            $("form input[name=provider]").val(),
            $("form input[name=dsa]").val(),
            $("form input[name=filter]").val()
            );

    $("form select#mapping").unbind("change").on("change", function () {
        fire(
                $("form input[name=provider]").val(),
                $("form input[name=dsa]").val(),
                $("form input[name=filter]").val());
    });


});
