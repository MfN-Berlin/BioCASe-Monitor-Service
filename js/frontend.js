/* global message, globalTimeElapsed */

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/js/frontend.js
 * @brief javascript functions used in the frontend
 * @package Bms
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


$.ajaxSetup({
    timeout: 3 * 60 * 1000 // time in milliseconds
});


/**
 * display a date-time string
 *
 * @param {int} utime
 * @returns {string}
 */
function showDate(utime) {
    var formattedString;
    //formattedString = utime.toDateStrng() + ' ' + utime.toTimeString();
    //formattedString = utime.toISOString();
    formattedString = utime.toLocaleString();
    return formattedString;
}

/**
 * build general User Interface
 *
 * @param {array} data The Data Provider list
 * @returns {string}
 */
function buildUI(data) {
    // bootstrap
    var output = "";
    output += ' <div class="panel-group" id="providerList">';
    for (var i = 0; i < data.length; i++) {
        output += '<div class="panel panel-default" id="provider_' + data[i].id + '">'
                + '<div class="panel-heading">'
                + '  <h4 class="panel-title"><a data-toggle="collapse" data-parent="#providerList" href="#collapse' + data[i].id + '">' + data[i].shortname + '</a>'
                + '  </h4>'
                + '  <div style="display:inline-block;margin-left:1em;">' + data[i].name + '</div>'
                + '</div>'

                + '<div id="collapse' + data[i].id + '" class="panel-collapse collapse">'
                + '  <div class="panel-body">'
                + '    <table class="providerTable"></table>'
                + '  </div>'
                + '</div>'

                + '</div>'
                ;
    }
    output += "</div>";
    return output;
}


/**
 * get Schema shortname
 *
 * @param {string} schema
 * @returns {string}
 */
function getSchema(schema) {
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "index.php",
        data: {"action": "getSchema", "schema": schema}
    })
            .fail(function () {
                console.log("getSchema failed:  schema=" + schema);
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log(schema + " shortname=...");
                console.log(data);
                return data;
            });
}



/**
 * get Number of Current Records
 *
 * @param {int} idProvider
 * @param {int} idDSA
 * @param {string} schema
 * @param {string} queryUrl
 * @param {string} filter complex filter <like>...</like>
 * @param {int} nocache 0 or 1
 * @returns {boolean} false
 */
function getCurrentRecords(idProvider, idDSA, schema, queryUrl, filter, nocache) {
    var startRequest = $.now(); // milliseconds
    progressAjax[idDSA]["records"] = false;
    showProgress(idProvider, idDSA, "records", startRequest);
    nbAjaxCalls++;

    $.ajax({
        type: "GET",
        dataType: "json",
        url: "index.php",
        data: {"action": "getCurrentRecords", "idProvider": idProvider, "schema": schema, "url": queryUrl, "filter": filter, "nocache": nocache, "format": "json"}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getCurrentRecords failed:  provider " + idProvider + " schema=" + schema);
                $("#current-records" + idDSA).html('<div class="small-headline error"># current records</div>');
                $("#current-records" + idDSA).append('<br/><a data-toggle="tooltip" href="#current-records' + idDSA + '" title="' + errorThrown + '" class="glyphicon glyphicon-refresh refresh error-message"></a>');
                $("#current-records" + idDSA + " a.refresh").on("click", function () {
                    //$("#current-records" + idDSA).html(spinner);
                    getCurrentRecords(idProvider, idDSA, schema, queryUrl, filter, 1);
                });
            })
            .always(function (jqXHR) {
                //console.log("getCurrentRecords finished");
                console.log(jqXHR);
                progressAjax[idDSA]["records"] = true;
                var timeElapsed = $.now() - startRequest;
                globalTimeElapsed += timeElapsed;
                $("#global-time-elapsed").html(globalTimeElapsed.toLocaleString());
                logbook(idProvider, schema, idDSA, "", "getCurrentRecords", timeElapsed);
            })
            .done(function (data) {
                console.log(data);

                nbAjaxCalls--;
                //showConcurrentRequests(nbAjaxCalls);
                console.log("provider " + idProvider + " schema=" + schema + " : getCurrentRecords done in " + ($.now() - startRequest) + "ms");

                if (data.error.length > 0) {
                    progressAjax[idDSA]["records"] = "failed";
                    //$("#current-records" + idDSA).html('<div class="small-headline"># current records</div>');
                    $("#current-records" + idDSA).html('<div class="small-headline">&nbsp;</div>');
                    $("#current-records" + idDSA).append(
                            '<div class="error-message">'
                            + message[data.error]
                            + '<br/>tried for ' + data.timeout + 's, giving up.'
                            + '</div>');
                    $("#current-records" + idDSA).append('<a data-toggle="tooltip" href="#current-records' + idDSA + '" title="retry" class="glyphicon glyphicon-refresh refresh"></a>');
                } else {
                    var cachedate = new Date(1000 * parseInt(data.cacheinfo));
                    //cachedate = cachedate.toDateString() + ' ' + cachedate.toTimeString();

                    $("#total-records" + idProvider).html(parseInt($("#total-records" + idProvider).html()) + data.cardinal);

                    //$("#current-records" + idDSA).html('<div class="small-headline"># current records</div>');
                    $("#current-records" + idDSA).html('<div class="small-headline">&nbsp;</div>');
                    $("#current-records" + idDSA).append(data.cardinal);
                    $("#current-records" + idDSA).append('<br/><a data-toggle="tooltip" href="#current-records' + idDSA + '" title="cached on ' + showDate(cachedate) + '" class="glyphicon glyphicon-refresh refresh"></a>');
                }
                $("#current-records" + idDSA + " a.refresh").on("click", function () {
                    $(this).addClass("gly-spin");
                    // reset the sum of total records
                    $("#total-records" + idProvider).html(parseInt($("#total-records" + idProvider).html()) - data.cardinal);
                    getCurrentRecords(idProvider, idDSA, schema, queryUrl, filter, 1);
                });

            }
            );
    return false;
}

/**
 * count the occurrences of a given concept
 *
 * @param {int} idProvider
 * @param {int} idDSA
 * @param {string} schema
 * @param {string} queryUrl
 * @param {string}  concept  a capability, like /DataSets/DataSet/Units/Unit/UnitID
 * @param {int} specifier a bitmap of TOTAL, DISTINCT, DROPPED
 * @param {string}  filter complex filter: <like>....</like>
 * @param {int} nocache 1|0
 * @returns {boolean} false
 */
function getCountConcept(idProvider, idDSA, schema, queryUrl, concept, specifier, filter, nocache) {
    var atmp = concept.split("/");
    var shortConcept = atmp[atmp.length - 1];
    var startRequest = $.now(); // milliseconds

    progressAjax[idDSA][shortConcept] = false;
    showProgress(idProvider, idDSA, shortConcept, startRequest);

    nbAjaxCalls++;
    //$("#" + shortConcept + idDSA).html(spinner);

    $.ajax({
        type: "GET",
        url: "index.php",
        data: {"action": "getCountConcepts", "idProvider": idProvider, "schema": schema, "concept": concept, "specifier": specifier, "url": queryUrl, "filter": filter, "nocache": nocache},
        dataType: "text"
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                var timeElapsed = $.now() - startRequest; // seconds
                console.log("getCountConcepts failed:  Provider=" + idProvider + " DSA=" + idDSA + " url=" + queryUrl + " concept=" + concept + " filter=" + filter + " specifier=" + specifier);
                var tmp = concept.split("/");
                var column = tmp[tmp.length - 1];
                console.log(column);
                displayErrorMessage($("#" + column + idDSA), message.providerError, "html");
                $("#" + column + idDSA).append(" <div class='error-message'>" + errorThrown + "</div>");
                $("#" + column + idDSA).append(" <div class='error-message'> tried for " + timeElapsed + "ms, giving up.</div>");
                $("#" + column + idDSA).append('<br/><a data-toggle="tooltip" href="#shortConcept' + idDSA + '" title="' + errorThrown + '" class="glyphicon glyphicon-refresh refresh error-message"></a>');
                $("#" + column + idDSA + " a.refresh").on("click", function () {
                    getCountConcept(idProvider, idDSA, schema, queryUrl, concept, specifier, filter, 1);
                });
            })
            .always(function (jqXHR) {
                //console.log("getCountConcepts finished");
                console.log(jqXHR);
                progressAjax[idDSA][shortConcept] = true;
                var timeElapsed = $.now() - startRequest;
                globalTimeElapsed += timeElapsed;
                $("#global-time-elapsed").html(globalTimeElapsed.toLocaleString());
                logbook(idProvider, schema, idDSA, concept, "getCountConcept", timeElapsed);
            })
            .done(function (datastring) {
                console.log("provider " + idProvider + " schema=" + schema + " concept=" + shortConcept + " : getCountConcepts done in " + ($.now() - startRequest) + "ms");

                var data = JSON.parse(datastring);
                console.log(data);

                nbAjaxCalls--;
                //showConcurrentRequests(nbAjaxCalls);

                var cachedate_search = new Date(1000 * parseInt(data.cacheinfo_search));
                //var cachedate_scan = new Date(1000 * parseInt(data.cacheinfo_scan));

                $("#" + shortConcept + idDSA).html("<div class='small-headline' title='" + concept + "'># " + shortConcept + "</div>");

                if (data.hasOwnProperty("total"))
                    $("#" + shortConcept + idDSA).append("total: " + data.total);
                if (data.hasOwnProperty("distinct"))
                    $("#" + shortConcept + idDSA).append("<br>distinct: " + data.distinct);
                if (data.hasOwnProperty("dropped"))
                    $("#" + shortConcept + idDSA).append("<br>dropped: " + data.dropped);

                $("#" + shortConcept + idDSA).append('<br/><a data-toggle="tooltip" href="#' + shortConcept + idDSA + '" title="cached on ' + showDate(cachedate_search) + '" class="glyphicon glyphicon-refresh refresh"></a>');

                $("#" + shortConcept + idDSA + " a.refresh").on("click", function () {
                    console.log("renewing cache for DSA=" + idDSA + " concept=" + concept + " filter=" + filter);
                    $(this).addClass("gly-spin");
                    getCountConcept(idProvider, idDSA, schema, queryUrl, concept, specifier, filter, 1);
                });
            });
    return false;
}

/**
 * get Citation Text of given Data Set
 *
 * @param {int} idProvider
 * @param {string} schema
 * @param {int} dsa
 * @param {string} url
 * @param {string} filter
 * @param {string} concept
 * @param {int} j index for row in UI
 * @param {int} cached 1|0
 * @returns {boolean} false
 */
function getCitation(idProvider, schema, dsa, url, filter, concept, j, cached) {
    var startRequest = $.now(); // milliseconds
    nbAjaxCalls++;
    progressAjax[dsa]["citation"] = false;
    showProgress(idProvider, dsa, "citation", startRequest);
    //$("#title" + j + " .citation").html(spinner);
    $.ajax({
        type: "GET",
        url: "index.php",
        dataType: "json",
        data: {"action": "getCitation", "idProvider": idProvider, "schema": schema, "url": url, "filter": filter, "concept": concept, "cached": cached}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getCitation failed");
                $("#title" + j + " .citation").html("<span class='error-message' title='" + errorThrown + "'>" + textStatus + "</span>");
                $("#title" + j + " .citation").append('<br/><a href="#" data-toggle="tooltip" title="retry" class="glyphicon glyphicon-refresh refresh"/></a>');
            })
            .always(function (jqXHR) {
                //console.log("finished");
                console.log(jqXHR);
                progressAjax[dsa]["citation"] = true;
                var timeElapsed = $.now() - startRequest;
                globalTimeElapsed += timeElapsed;
                //logbook(idProvider, url.split("dsa=")[1], "", "getCitation", timeElapsed);
                logbook(idProvider, schema, dsa, "", "getCitation", timeElapsed);
                $("#global-time-elapsed").html(globalTimeElapsed.toLocaleString());
                $("#title" + j + " .citation" + " a.refresh").on("click", function () {
                    //$("#title" + j + " .citation").html(spinner);
                    getCitation(idProvider, schema, dsa, url, filter, concept, j, 0);
                });
            })
            .done(function (data) {
                console.log(data);

                nbAjaxCalls--;
                //showConcurrentRequests(nbAjaxCalls);
                console.log("provider " + idProvider + ": getCitation done in " + ($.now() - startRequest) + "ms");

                if (data.error) {
                    progressAjax[dsa]["citation"] = "failed";
                    $("#title" + j + " .citation-text").html("<span class='error-message' title='" + data.error.replace(/'/g, '\\\'') + "'>" + data.error.replace(/'/g, '\\\'') + "</span>");
                    $("#title" + j + " .citation-text").append('<br/><a href="#" data-toggle="tooltip" title="retry" class="glyphicon glyphicon-refresh refresh"/></a>');
                } else if (data.citation) {
                    var cachedate = new Date(1000 * parseInt(data.cacheinfo));
                    $("#title" + j + " .citation-text").text(data.citation);
                    $("#title" + j + " .citation-text").append('<br/><a href="#" data-toggle="tooltip" title="' + cachedate + '" class="glyphicon glyphicon-refresh refresh"/></a>');
                } else {
                    $("#title" + j + " .citation-text").html("<span class='error-message' title='empty element: /DataSets/DataSet/Metadata/IPRStatements/Citations/Citation/Text'>" + message.noCitation + "</span>");
                    $("#title" + j + " .citation-text").append(' <br/><a href="#" data-toggle="tooltip" title="retry" class="glyphicon glyphicon-refresh refresh"/></a>');
                }

                $("#title" + j + " .citation-text" + " a.refresh").on("click", function () {
                    $(this).addClass("gly-spin");
                    getCitation(idProvider, schema, dsa, url, filter, concept, j, 0);
                });
            });
    return false;
}

/**
 * get useful Links of given Data Set
 *
 * @param {int} idProvider
 * @param {id} idDSA
 * @returns {boolean} false
 */
function getUsefulLinks(idProvider, idDSA) {
    var requestStarted = $.now(); // milliseconds
    $.ajax({
        type: "GET",
        url: "./services/useful-links/index.php",
        data: {"provider": idProvider, "dsa": idDSA}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getUsefulLinks failed: provider " + idProvider + ": " + textStatus + " " + Math.round(($.now() - requestStarted) / 1000) + "s");
                displayErrorMessage($(".accordion-content-active .useful-links"), message.providerError, "html");
                //displayErrorMessage($(".accordion-content-active .useful-links"), idDSA + ': ' + message.providerError, "append");
            })
            .always(function () {
                //console.log("getUsefulLinks finished");
            })
            .done(function (linkData) {
                console.log("provider " + idProvider + ": getUsefulLinks done in " + Math.round(($.now() - requestStarted) / 1000) + "s");
                console.log(linkData);

                // add custom useful links
                for (var j = 0; j < linkData.length; j++) {
                    var logo = "";
                    if (linkData[j].logo) {
                        logo = "<img alt='" + linkData[j].title + "' src='" + linkData[j].logo + "' height='24'/>";
                    } else {
                        logo = linkData[j].title;
                    }

                    var atmp = linkData[j].link.split('/');
                    var shortlink = atmp[atmp.length - 1];

                    if (linkData[j].title == "BioCASe Archive") {
//                        if (linkData[j].is_latest == "1") {
//                            $("#archives" + linkData[j].dsa)
//                                    .append("<div class='isLatest'><a target='customlink-" + linkData[j].id + "' href='" + linkData[j].link + "' title='" + linkData[j].title + ": " + shortlink + "'>" + logo + "</a></div> ");
//                            $("#archives" + linkData[j].dsa)
//                                    .append(' <a class="toggle"><span class="glyphicon glyphicon-plus-sign plus"/><span class="glyphicon glyphicon-minus-sign minus"></a>');
//                        } else {
//                            $("#archives" + linkData[j].dsa)
//                                    .append("<div class='archive'><a target='customlink-" + linkData[j].id + "' href='" + linkData[j].link + "' title='" + linkData[j].title + ": " + shortlink + "'>" + logo + "</a></div> ");
//                        }
//
//                        $("#archives" + linkData[j].dsa + " a.toggle").unbind("click").on("click", function () {
//                            $(this).find(".plus").toggle();
//                            $(this).find(".minus").toggle();
//                            $(this).parent().find(".archive").toggle();
//
//                        });
                    } else
                    {
                        // USEFUL LINKS
                        $("#useful-links" + linkData[j].dsa)
                                .append("<div class='useful-link'><a target='customlink-" + linkData[j].id + "' href='" + linkData[j].link + "' title='" + linkData[j].title + ": " + shortlink + "'>" + logo + "</a></div> ");
                    }
                }
                return false;
            });
    return false;
}


/**
 * get xml archives of given Data Set
 *
 * @param {int} idProvider
 * @param {id} idDSA
 * @returns {boolean} false
 */
function getArchives(idProvider, idDSA) {
    var requestStarted = $.now(); // milliseconds
    $.ajax({
        type: "GET",
        url: "./services/xml-archives/index.php",
        data: {"provider": idProvider, "dsa": idDSA}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getArchives failed: provider " + idProvider + ": " + textStatus + " " + Math.round(($.now() - requestStarted) / 1000) + "s");
            })
            .always(function () {
                //console.log("getArchives finished");
            })
            .done(function (data) {
                console.log("provider " + idProvider + ": getArchives done in " + Math.round(($.now() - requestStarted) / 1000) + "s");
                console.log(data);
                if (data.length > 0 && data[0].xml_archives) {
                    var linkData = data[0].xml_archives;
                    console.log(linkData.length + " archives for provider " + idProvider + "  dsa=" + idDSA);

                    //var logo = "<img alt='" + linkData[j].title + "' src='" + linkData[j].logo + "' height='24'/>";
                    var logo = "<img alt='xml archive' src='images/file-xml.png' height='24'/>";

                    for (var j = 0; j < linkData.length; j++) {
                        var atmp = linkData[j].xml_archive.split('/');
                        var shortlink = atmp[atmp.length - 1];
                        console.log("idDSA=" + idDSA + " shortlink=" + shortlink);
                        console.log(linkData[j]);
                        console.log("archive: " + $("#archives" + idDSA).text());
                        console.log("archive is latest: " + linkData[j].latest);
                        if (linkData[j].latest) {
                            console.log("archive is latest !");
                            $("#archives" + idDSA)
                                    .append("<div class='isLatest'><a target='customlink-" + linkData[j].id + "' href='" + linkData[j].xml_archive + "' title='latest archive: " + shortlink + "'>" + logo + "</a></div> ");
                            $("#archives" + idDSA)
                                    .append(' <a class="toggle"><span class="glyphicon glyphicon-plus-sign plus"/><span class="glyphicon glyphicon-minus-sign minus"></a>');
                        } else {
                            console.log("archive is NOT latest !");
                            $("#archives" + idDSA)
                                    .append("<div class='archive'><a target='customlink-" + linkData[j].id + "' href='" + linkData[j].xml_archive + "' title='older archive: " + shortlink + "'>" + logo + "</a></div> ");
                        }


                        $("#archives" + idDSA + " a.toggle").unbind("click").on("click", function () {
                            $(this).find(".plus").toggle();
                            $(this).find(".minus").toggle();
                            $(this).parent().find(".archive").toggle();

                        });
                    }
                }
                return false;
            });
    return false;
}

/**
 * Main function: populates the UI
 *
 * @returns {boolean} false
 */
function populateUI() {

    globalTimeElapsed = 0;
    nbAjaxCalls = 0;
    progressAjax = [];

    $.ajax({
        type: "GET",
        url: "./services/providers/index.php",
        dataType: "json"
    })
            .fail(function () {
                console.log("getProviders failed");
                displayErrorMessage($("#main"), message.providerError, "html");
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {

                console.log("providers:");
                console.log(data);

                $("#main").html(buildUI(data));
                // loop over providers

//                var randomizedData = data.sort(function (a, b) {
//                    return 0.5 - Math.random();
//                });
//                $.each(randomizedData, function (i)
                for (var i = 0; i < data.length; i++)
                {
                    console.log("provider " + data[i].id + ": processing " + data[i].name);
                    $("#provider_" + data[i].id + " h4 a").on("click", {providerId: data[i].id}, function (event)
                    {
                        var currentProvider = event.data.providerId;

                        //var is_opened = !$(this).hasClass("collapsed");
                        //if (is_opened)
                        {
                            // get Concepts for currentProvider
                            var conceptsStartRequest = $.now(); // milliseconds
                            var concepts = [];
                            $.ajax({
                                type: "GET",
                                url: "./index.php",
                                data: {action: "getConcepts", idProvider: currentProvider},
                                dataType: "json"
                            })
                                    .fail(function () {
                                        console.log("getConcepts failed");
                                    })
                                    .always(function () {
                                        //console.log("finished");
                                    })
                                    .done(function (conceptData) {
                                        //console.log(conceptData);
                                        concepts = conceptData;
                                        var timeElapsed = $.now() - conceptsStartRequest;
                                        globalTimeElapsed += timeElapsed;
                                        console.log("provider " + currentProvider + ": getConcepts done  in " + timeElapsed + "s");
                                        console.log(conceptData);

                                        // get DSA Points for currentProvider
                                        console.log("get DSA Points for currentProvider " + currentProvider);
                                        var providerMainDataStartRequest = $.now(); // milliseconds
                                        $.ajax({
                                            type: "GET",
                                            url: "./index.php",
                                            data: {action: "getProviderMainData", idProvider: currentProvider},
                                            dataType: "json"
                                        })
                                                .fail(function () {
                                                    console.log("getProviderMainData failed");
                                                    displayErrorMessage($("#providerList"), message.providerError, "append");
                                                })
                                                .always(function () {
                                                    //console.log("getProviderMainData finished");
                                                    $("#system-message").fadeOut();
                                                })
                                                .done(function (mainData) {
                                                    console.log("mainData:");
                                                    console.log(mainData);
                                                    var timeElapsed = $.now() - providerMainDataStartRequest;
                                                    globalTimeElapsed += timeElapsed;
                                                    console.log("provider " + mainData[0].institution_id + ": getProviderMainData done in " + timeElapsed + "s");

                                                    var totalRecords = 0;

                                                    for (var k = 0; k < mainData.length; k++) {

                                                        progressAjax[mainData[k].id] = [];

                                                        // if not yet created (inhibit duplicates)
                                                        if ($("#dsa-record" + mainData[k].id).length == 0) {
                                                            var header = "", atmp = [];
                                                            if (k == 0) {
                                                                header = "<tr>";
                                                                header += "<th>title</th><th>schema</th><th>landingpages</th>";
                                                                header += "<th># current records</th><th># Concepts</th>";
                                                                header += "<th>consistency</th>";
                                                                header += "<th>BioCASe</th><th>archives</th><th>useful links</th>";
                                                                header += "<th class='active'>active</th>";
                                                                header += "</tr>";
                                                            }

                                                            var columns = "<tr id='dsa-record" + mainData[k].id + "' class='active" + mainData[k].active + "'>";

                                                            // first column
                                                            //
                                                            // Data Source Access Point
                                                            columns += "<td id='title" + mainData[k].id + "' class='titleDSA'><span class='dsa-title'>" + mainData[k].title + "</span>";
                                                            columns += " <a href='#dsa-record" + mainData[k].id + "'>";
                                                            columns += " <span class='glyphicon glyphicon-copy' title='click to copy URL to clipboard'/>";
                                                            columns += "<div class='dsa-point' id='dsa-point" + mainData[k].id + "'>" + mainData[k].url + "</div>";
                                                            columns += "</a>";

                                                            // Citation
                                                            columns += "<div class='citation-text'></div>";
                                                            columns += "</td>";

                                                            getCitation(
                                                                    mainData[k].institution_id,
                                                                    mainData[k].schema,
                                                                    mainData[k].id,
                                                                    mainData[k].url,
                                                                    mainData[k].filter,
                                                                    '/DataSets/DataSet/Metadata/IPRStatements/Citations/Citation/Text',
                                                                    mainData[k].id,
                                                                    1
                                                                    );

                                                            // 2nd column
                                                            // Schema
                                                            columns += "<td><div class='schema' title='" + mainData[k].schema + "'>";
                                                            columns += mainData[k].shortSchema;
                                                            columns += "</div></td>";

                                                            // 3rd column
                                                            // Landing Pages
                                                            columns += "<td>";
                                                            var isPreferred = "";

                                                            // Automatic Landingpage
                                                            if (mainData[k].preferred_landingpage == 0) {
                                                                isPreferred = " isPreferred";
                                                            }
                                                            columns += "<div class='landingpage'><a target='_blank' href='./landingpage.php?provider=" + mainData[k].institution_id + "&file=" + mainData[k].url + "&filter=" + mainData[k].filter + "' title='Landing Page'><img width='36' src='images/landingpage.svg' alt='Landing Page'/></a><br/>system-generated ABCD landingpage</div>";

                                                            // Userdefined Landing Page
                                                            isPreferred = "";
                                                            if (mainData[k].preferred_landingpage == 1) {
                                                                isPreferred = " isPreferred";
                                                            }
                                                            if (mainData[k].landingpage_url) {
                                                                columns += "<div class='landingpage" + isPreferred + "'><a target='_blank' href='" + mainData[k].landingpage_url + "' title='User defined Landingpage " + isPreferred + ": " + mainData[k].landingpage_url + "'><img width='36' src='images/landingpage.svg' alt='User defined Landingpage'/></a><br/>User defined</div>";
                                                            }
                                                            columns += "</td>";

                                                            // 4th column
                                                            // Current Records
                                                            columns += "<td id='current-records" + mainData[k].id + "' class='cardinal'>";
                                                            columns += '</td>';

                                                            // 5th column
                                                            // Count Concepts
                                                            columns += "<td>";
                                                            columns += "<table class='count-concepts'><tr>";
                                                            for (var j = 0; j < concepts.length; j++) {
                                                                console.log(concepts[j]["xpath"]);
                                                                atmp = concepts[j]["xpath"].split("/");
                                                                columns += "<td id='" + atmp[atmp.length - 1] + mainData[k].id + "' class='cardinal'>";
                                                                columns += "</td>";
                                                            }
                                                            columns += "</tr></table></td>";

                                                            // other Columns
                                                            columns += "<td id='mapping-check" + mainData[k].id + "' class='consistency'></td>";
                                                            columns += "<td id='biocase" + mainData[k].id + "'></td>";
                                                            columns += "<td id='archives" + mainData[k].id + "'></td>";
                                                            columns += "<td id='useful-links" + mainData[k].id + "' class='useful-links'></td>";
                                                            columns += "<td id='active" + mainData[k].id + "' class='active" + mainData[k].active + "'>" + mainData[k].active + "</td>";
                                                            columns += "</tr>";

                                                            ///////////////
                                                            // progressbars

                                                            columns += '<tr id="info-dsa-record' + mainData[k].id + '" class="info-line">';
                                                            // progressbar citation
                                                            columns += '<td colspan="1">';
                                                            columns += '<div class="progress citation" style="width:98%;margin: auto 1%">';
                                                            columns += '  <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100000" >';
                                                            columns += '        <span class="citation"><span class="cardinal milliseconds"><span class="glyphicon"/></span></span></div>';
                                                            columns += '</div>';
                                                            columns += '<td colspan="2">&nbsp;</td>';
                                                            // progressbar current records
                                                            columns += '<td colspan="1">';
                                                            columns += '<div class="progress records" style="width:98%;margin: auto 1%">';
                                                            columns += '  <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100000" >';
                                                            columns += '        <span class="records"><span class="cardinal milliseconds"><span class="glyphicon"/></span></span></div>';
                                                            columns += '</div>';
                                                            // progressbar concept
                                                            columns += '<td colspan="1"><table width="100%"><tr>';
                                                            for (var j = 0; j < concepts.length; j++) {
                                                                atmp = concepts[j]["xpath"].split("/");

                                                                columns += '<td>';
                                                                columns += '<div class="progress ' + atmp[atmp.length - 1] + '" style="width:98%;margin: auto 1%">';
                                                                columns += '  <div class="progress-bar" role="progressbar progress-bar-striped active" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100000" >';
                                                                columns += '        <span class="' + atmp[atmp.length - 1] + '"><span class="cardinal milliseconds"><span class="glyphicon"/></span></span></div>';
                                                                columns += '</div>';
                                                                columns += "</td>";
                                                            }
                                                            columns += "</table></td></tr>";


                                                            // ACCORDION of DSA Points
                                                            $("#provider_" + mainData[k].institution_id + " table.providerTable").append(header + columns);
                                                            //console.log($("#provider_" + mainData[k].institution_id + " table.providerTable table.count-concepts").html());

                                                            // on click on DSA: copy to clipboard
                                                            $("#title" + mainData[k].id + " a").on("click", function (event)
                                                            {
                                                                event.preventDefault();
                                                                copyToClipboard($(this).parent().find("div.dsa-point"));
                                                                displaySystemMessage("URL copied to clipboard.", "info");
                                                            });

                                                            // COUNT CONCEPTS
                                                            for (var j = 0; j < concepts.length; j++) {
                                                                //console.log("XPATH: " + concepts[j]["xpath"]);
                                                                atmp = concepts[j]["xpath"].split("/");
                                                                getCountConcept(mainData[k].institution_id, mainData[k].id, mainData[k].schema, mainData[k].url, concepts[j]["xpath"], mainData[k].specifier, mainData[k].filter);
                                                                columns += "<td id='" + atmp[atmp.length - 1] + mainData[k].id + "' class='cardinal'>";
                                                                columns += "</td>";
                                                            }

                                                            // GET USEFUL LINKS
                                                            getUsefulLinks(mainData[k].institution_id, mainData[k].id);

                                                            // GET ARCHIVES
                                                            getArchives(mainData[k].institution_id, mainData[k].id);

                                                            // COUNT AND SUM UP CURRENT RECORDS
                                                            totalRecords += getCurrentRecords(mainData[k].institution_id, mainData[k].id, mainData[k].schema, mainData[k].url, mainData[k].filter, 0);
                                                        }


                                                        // CHECK CONSISTENCY
                                                        var mappingUrl = "consistency/index.php?"
                                                                + "&provider=" + mainData[k].institution_id
                                                                + "&dsa=" + mainData[k].url.split("dsa=")[1].split("&")[0]
                                                                + "&filter=" + encodeURIComponent(mainData[k].filter)
                                                                + "&source_schema=" + mainData[k].schema
                                                                ;
                                                        $("#mapping-check" + mainData[k].id).
                                                                html("<a target='dsa-" + mainData[k].id + "' title='check consistency' href='" + mappingUrl + "'><img alt='BioCASe' src='images/consistency-check3.png' height='24'/></a>");

                                                        // BIOCASE
                                                        var biocaseUrl = mainData[k].url.split("pywrapper.cgi")[0];
                                                        var titleSlug = mainData[k].url.split("dsa=")[1].split("&")[0];
                                                        $("#biocase" + mainData[k].id).
                                                                html("<div class='useful-link biocase'><a target='biocase-" + mainData[k].id + "' title='BioCASe query: " + mainData[k].title_slug + "' href='" + biocaseUrl + biocaseQueryUrl + titleSlug + "'><figure><img alt='BioCASe' src='images/biocase_icon.gif' height='36'/><figcaption>Query Form</figcaption></a></figure></div>");
                                                        $("#biocase" + mainData[k].id).
                                                                append("<div class='useful-link biocase'><a target='biocase-tool-" + mainData[k].id + "' title='BioCASe Local Query Tool: " + mainData[k].title_slug + "' href='" + biocaseUrl + biocaseLocalQueryToolUrl + titleSlug + "'><figure><img alt='BioCASe' src='images/biocase_icon.gif' height='36'/><figcaption>Local Query Tool</figcaption></a></figure></div>");

                                                    } // END LOOP PROVIDER DSAs

                                                    //$("#provider_" + mainData[0].institution_id + " table.providerTable").append("<tr><td colspan='3'/><td class='cardinal total' id='total-records" + mainData[0].institution_id + "'>0</td></tr>");
                                                    if ($("#total-records" + mainData[0].institution_id).length == 0)
                                                        $("#provider_" + mainData[0].institution_id + " table.providerTable").append("<tr><td colspan='3'/><td class='cardinal total' id='total-records" + mainData[0].institution_id + "'>0</td></tr>");
                                                });
                                    });
                        } // end of loop over providers
                    });
                }
            }
            );
    return false;
}


/**
 * shows waiting time for http requests to BPS
 *
 * @param {int} idProvider
 * @param {string} dsa
 * @param {string} action
 * @param {float}  startTime
 * @returns {boolean} false
 */
function showProgress(idProvider, dsa, action, startTime) {

    var domPath = '#info-dsa-record' + dsa + ' .' + action + ' .progress-bar ';

    var barMaxWidth = Math.min($('#info-dsa-record' + dsa + ' .progress').width(), 150);
    if (action == "citation")
        barMaxWidth = 400;
    if (!barMaxWidth)
        barMaxWidth = 150;
    //console.log(action + ":barmaxwidth=" + barMaxWidth);

    var ticktack = setInterval(progressbar, 10);

    function progressbar() {


        if (progressAjax[dsa][action] == "failed") {

            //$(domPath + ' .' + action + ' .cardinal span').removeClass('glyphicon-refresh gly-spin');
            $(domPath + ' .' + action + ' .cardinal span').addClass('glyphicon-remove');
            //$(domPath).css('width', '100%');
            $(domPath).addClass('progress-bar-danger');
            $(domPath).removeClass('progress-bar-info');
            $(domPath).removeClass('progress-bar-warning');
            $(domPath).removeClass('progress-bar-striped');
            $(domPath).removeClass('progress-bar-success');

            clearInterval(ticktack);

        } else if (progressAjax[dsa][action]) { // done

            //$(domPath + ' .' + action + ' .cardinal span').removeClass('glyphicon-refresh gly-spin');
            $(domPath + ' .' + action + ' .cardinal span').addClass('glyphicon-ok');
            //$(domPath).css('width', '100%');
            $(domPath).removeClass('progress-bar-danger');
            $(domPath).removeClass('progress-bar-info');
            $(domPath).removeClass('progress-bar-warning');
            $(domPath).removeClass('progress-bar-striped');
            $(domPath).addClass('progress-bar-success');

            clearInterval(ticktack);

        } else {
            var timeElapsed = ($.now() - startTime);
            var barWidth = Math.min(0.4 * timeElapsed, barMaxWidth);

            //console.log(dsa+"/"+action + ":" + timeElapsed + "barmaxwidth=" + barMaxWidth);
            //console.log(idProvider + "/" + dsa + "/" + action  + ": TIME=" + timeElapsed + "=" + barWidth + " barMaxWIDTH=" + barMaxWidth);

            //$(domPath + ' .' + action + ' .cardinal').html(timeElapsed + 'ms ' + ' <span class="glyphicon glyphicon-refresh gly-spin"/>');
            $(domPath + ' .' + action + ' .cardinal').html(timeElapsed);
            $(domPath).css('width', barWidth + 'px').attr('aria-valuenow', timeElapsed);

            if (timeElapsed > 20000) {
                $(domPath).addClass('progress-bar-danger');
                $(domPath).removeClass('progress-bar-warning');
                $(domPath).removeClass('progress-bar-success');
                $(domPath).removeClass('progress-bar-info');
            } else if (timeElapsed > 10000) {
                $(domPath).addClass('progress-bar-warning');
                $(domPath).removeClass('progress-bar-danger');
                $(domPath).removeClass('progress-bar-success');
                $(domPath).removeClass('progress-bar-info');
            } else {
                $(domPath).addClass('progress-bar-info');
                $(domPath).removeClass('progress-bar-warning');
                $(domPath).removeClass('progress-bar-success');
                $(domPath).removeClass('progress-bar-danger');
            }
        }

    }
}

$(document).ready(function () {

// load system messages into variable "message"
    getMessages("./");

    populateUI();

    $("a#verbose-control").on("click", function () {
        $(".info-line").toggle("slow");
        $("#global-time-elapsed").toggle("slow");
        if ($(this).hasClass("glyphicon-eye-open")) {
            $(this).addClass("glyphicon-eye-close");
            $(this).removeClass("glyphicon-eye-open");
            $(this).html(" off");
        } else {
            $(this).removeClass("glyphicon-eye-close");
            $(this).addClass("glyphicon-eye-open");
            $(this).html(" on");
        }
    });

// enable htnml formatting in tooltips
    $('[data-toggle="tooltip"]').tooltip({"html": true});

});
