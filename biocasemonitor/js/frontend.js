
/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
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
    timeout: 30000 //time in milliseconds 
});

/**
 * loads system messages into global variable "message"
 * @returns {boolean} false
 */
function getMessages() {
    $.ajax({
        type: "GET",
        dataType: "text",
        url: "index.php",
        data: {"action": "getMessages"}
    })
            .fail(function () {
                console.log("getMessages failed");
            })
            .always(function () {
            })
            .done(function (data) {
                //console.log(data);
                message = JSON.parse(data);
                console.log(message);
            });
    return false;
}

/**
 * build general User Interface
 *
 * @param {array} data The Data Provider list
 * @returns {string}
 */
function buildUI(data) {
    var output = "<ul id='providerList'>";
    for (var i = 0; i < data.length; i++) {
        output += '<li id="provider_' + data[i].id + '">'
                + '<h6>'
                + '<div style="display:inline-block;width:4em;">' + data[i].shortname + '</div>'

                + '    <div id="progressbar_' + data[i].id + '"'
                + '            class="progress_bar"'
                + '            style="width:4em;display:inline-block;height:24px;background:lightgray;"'
                + '            title="progress meter - number of calls of BPS">'
                + '            <div class="progress-label"></div>'
                + '    </div>'

                + '    <div style="display:inline-block;margin-left:1em;">' + data[i].name + '</div>'
                + '</h6>'
                + '<div>'
                + '    <table class="providerTable">'
                + '    </table>'
                + '</div>'
                + '</li>'
                ;
    }
    output += "</ul>";
    return output;
}

/**
 * allows multiple tabs of an accordion to be open
 *
 * @param {object} event
 * @param {object} ui
 * @returns {Boolean}
 */
function allowMultipleOpen(event, ui) {

    // The accordion believes a panel is being opened
    if (ui.newHeader[0]) {
        var currHeader = ui.newHeader;
        var currContent = currHeader.next('.ui-accordion-content');
        // The accordion believes a panel is being closed
    } else {
        var currHeader = ui.oldHeader;
        var currContent = currHeader.next('.ui-accordion-content');
    }
    // Since we  changed the default behavior, this detects the actual status
    var isPanelSelected = currHeader.attr('aria-selected') == 'true';

    // Toggle the panel's header
    currHeader.toggleClass('ui-corner-all', isPanelSelected).toggleClass('accordion-header-active ui-state-active ui-corner-top', !isPanelSelected).attr('aria-selected', ((!isPanelSelected).toString()));

    // Toggle the panel's icon
    currHeader.children('.ui-icon').toggleClass('ui-icon-triangle-1-e', isPanelSelected).toggleClass('ui-icon-triangle-1-s', !isPanelSelected);

    // Toggle the panel's content
    currContent.toggleClass('accordion-content-active', !isPanelSelected);
    if (isPanelSelected) {
        currContent.slideUp();
    } else {
        currContent.slideDown();
    }

    return false; // Cancels the default action
}

/**
 * get Number of Current Records
 *
 * @param {int} idProvider
 * @param {int} idDSA
 * @param {string} queryUrl
 * @param {string} filter complex filter <like>...</like>
 * @param {int} nocache 0 or 1
 * @returns {boolean} false
 */
function getCurrentRecords(idProvider, idDSA, queryUrl, filter, nocache) {
    var startRequest = $.now(); // microseconds
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "index.php",
        data: {"action": "getCurrentRecords", "url": queryUrl, "filter": filter, "idProvider": idProvider, "nocache": nocache, "format": "json"}
    })
            .fail(function (data) {
                console.log("FAILED: getCurrentRecords for provider " + idProvider);
                console.log(data);
                displayErrorMessage($("#current-records" + idDSA), message.providerError, "html");
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log(data);
                var timeElapsed = Math.round(($.now() - startRequest) / 1000); // milliseconds
                globalTimeElapsed += timeElapsed;
                console.log("getCurrentRecords done in " + timeElapsed + "ms");

                nbAjaxCalls++;
                cardProviderCalls[idProvider]++;
                //console.log("total progression: " + nbAjaxCalls + "/" + maxCalls);
                //console.log("progression for provider " + idProvider + ": " + cardProviderCalls[idProvider] + "/" + maxProviderCalls[idProvider]);

                if (data.error.length > 0) {
                    $("#current-records" + idDSA).html("---");
                    $("#current-records" + idDSA).append(' <br/><a class="tooltip"><img src="./images/glyphicons/glyphicons-196-circle-info.png"/><span></span></a>');
                    $("#current-records" + idDSA + " a").show();
                    $("#current-records" + idDSA + " a").append('<span class="error-message">' + data.error);
                } else {
                    var cachedate = new Date(1000 * parseInt(data.cacheinfo));
                    //cachedate = cachedate.toDateString() + ' ' + cachedate.toTimeString();
                    cachedate = cachedate.toISOString();

                    $("#total-records" + idProvider).html(parseInt($("#total-records" + idProvider).html()) + data.cardinal);

                    $("#current-records" + idDSA).html(data.cardinal);
                    $("#current-records" + idDSA).append(' <br/><a class="tooltip"><img src="./images/glyphicons/glyphicons-196-circle-info.png"/><span></span></a>');
                    $("#current-records" + idDSA + " a").show();
                    $("#current-records" + idDSA + " a span").append("<div class='cached'>cached on <b>" + cachedate + "</b> <a>renew</a></div>");
                    if (verbose) {
                        $("#current-records" + idDSA + " a span").append("<div>data received after <b>" + timeElapsed + "ms</b></div>");
                    }
                    if (debugmode) {
                        $("#current-records" + idDSA + " a span").append("<div class='debug'>" + data.debuginfo + "</div>");
                    }

                    $("#current-records" + idDSA + " a span div.cached a").on("click", function () {
                        $("#current-records" + idDSA).html("<img alt='loading' src='../images/loading.gif'/>");
                        // reset the sum of total records
                        $("#total-records" + idProvider).html(parseInt($("#total-records" + idProvider).html()) - data.cardinal);
                        getCurrentRecords(idProvider, idDSA, queryUrl, filter, 1);
                    });
                }
            }
            );
    return false;
}

/**
 * count the occurrences of a given concept
 *
 * @param {int} idProvider
 * @param {int} idDSA
 * @param {string} queryUrl
 * @param {string}  concept  a capability, like /DataSets/DataSet/Units/Unit/UnitID
 * @param {int} specifier a bitmap of TOTAL, DISTINCT, DROPPED
 * @param {string}  filter complex filter: <like>....</like>
 * @param {int} nocache 1|0
 * @returns {boolean} false
 */
function getCountConcept(idProvider, idDSA, queryUrl, concept, specifier, filter, nocache) {
    var atmp = concept.split("/");
    var shortConcept = atmp[atmp.length - 1];
    var startRequest = $.now(); // microseconds
    $.ajax({
        type: "GET",
        url: "index.php",
        data: {"action": "getCountConcepts", "idProvider": idProvider, "url": queryUrl, "concept": concept, "specifier": specifier, "filter": filter, "nocache": nocache},
        dataType: "text"
    })
            .fail(function (data) {
                console.log("getCountConcepts *** FAILED *** Provider=" + idProvider + " DSA=" + idDSA + " url=" + queryUrl + " concept=" + concept + " filter=" + filter + " specifier=" + specifier);
                var tmp = concept.split("/");
                var column = tmp[tmp.length - 1];
                console.log(column);
                console.log(data);
                displayErrorMessage($("#" + column + idDSA), message.providerError, "html");
                $("#" + column + idDSA).append(" <a>renew</a>");
                $("#" + column + idDSA + " a").on("click", function () {
                    getCountConcept(idProvider, idDSA, queryUrl, concept, specifier, filter, 1);
                });
            })
            .always(function () {
                //console.log("getCountConcepts finished");
            })
            .done(function (datastring) {
                //console.log("getCountConcepts done");
                //console.log(datastring);
                var data = JSON.parse(datastring);
                //console.log(data);
                nbAjaxCalls++;
                cardProviderCalls[idProvider]++;

                var timeElapsed = Math.round(($.now() - startRequest) / 1000); // milliseconds
                globalTimeElapsed += timeElapsed;
                console.log("getCountConcepts done in " + timeElapsed + "ms");
                //console.log("total progression: " + nbAjaxCalls + "/" + maxCalls);
                //console.log("progression for provider " + idProvider + ": " + cardProviderCalls[idProvider] + "/" + maxProviderCalls[idProvider]);

                var cachedate_search = new Date(1000 * parseInt(data.cacheinfo_search));
                var cachedate_scan = new Date(1000 * parseInt(data.cacheinfo_scan));
                $("#" + shortConcept + idDSA).html("");

                if (data.hasOwnProperty("total"))
                    $("#" + shortConcept + idDSA).append("total: " + data.total);
                if (data.hasOwnProperty("distinct"))
                    $("#" + shortConcept + idDSA).append("<br>distinct: " + data.distinct);
                if (data.hasOwnProperty("dropped"))
                    $("#" + shortConcept + idDSA).append("<br>dropped: " + data.dropped);

                $("#" + shortConcept + idDSA).append(' <br/><a class="tooltip"><img src="./images/glyphicons/glyphicons-196-circle-info.png"/><span></span></a>');
                $("#" + shortConcept + idDSA + " a").show();

                if (data.cacheinfo_search) {
                    // either toLocaleString() or toISOString()
                    $("#" + shortConcept + idDSA + " a span").append("<div class='cached'>total cached at <b>" + cachedate_search.toISOString() + "</b> <a>renew</a></div>");
                    if (verbose) {
                        //$("#" + shortConcept + idDSA + " a span").append("<div class='cached debug'>" + data.debuginfo_search + "</div>");
                    }
                }
                if (data.cacheinfo_scan) {
                    $("#" + shortConcept + idDSA + " a span").append("<div class='cached'>distinct/dropped cached at <b>" + cachedate_scan.toISOString() + "</b> <a>renew</a></div>");
                    if (verbose) {
                        //$("#" + shortConcept + idDSA + " a span").append("<div class='cached debug'>" + data.debuginfo_scan + "</div>");
                    }
                }
                if (verbose) {
                    $("#" + shortConcept + idDSA + " a span").append("<div>data received after <b>" + timeElapsed + "ms</b></div>");
                    $("#" + shortConcept + idDSA + " a span").append("<div class='cached debug'>" + data.cachefile + "</div>");
                }
                $("#" + shortConcept + idDSA + " a span div.cached a").on("click", function () {
                    console.log("renewing cache for DSA=" + idDSA + " concept=" + concept + " filter=" + filter);
                    $("#" + shortConcept + idDSA).html("<img alt='loading' src='../images/loading.gif'/>");

                    getCountConcept(idProvider, idDSA, queryUrl, concept, specifier, filter, 1);
                });
            })
            ;
    return false;
}

/**
 * get Citation Text of given Data Set
 *
 * @param {int} idProvider
 * @param {string} url
 * @param {string} filter
 * @param {string} concept
 * @param {int} j index for row in UI
 * @param {int} cached 1|0
 * @returns {boolean} false
 */
function getCitation(idProvider, url, filter, concept, j, cached) {
    var startRequest = $.now(); // microseconds

    $("#title" + j + " .citation").html("<img alt='loading' src='../images/loading.gif'/>");
    var loading = "<div class='ds-title-loading'>";
    loading += '<div class="cssload-container"><div class="cssload-circle-1"><div class="cssload-circle-2"><div class="cssload-circle-3"><div class="cssload-circle-4"><div class="cssload-circle-5"><div class="cssload-circle-6"><div class="cssload-circle-7"></div></div></div></div></div></div></div>';
    //loading += "<img src='../images/loading.gif'/ alt='loading...'>";
    loading += "</div>";
    $("#title" + j + " .citation").html(loading);
    $.ajax({
        type: "GET",
        url: "index.php",
        dataType: "json",
        data: {"action": "getCitation", "idProvider": idProvider, "url": url, "filter": filter, "concept": concept, "cached": cached}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getCitation failed");
                console.log(textStatus + " *** " + errorThrown + " *** ");
                console.log(jqXHR.responseText);
                console.log(jqXHR);
                $("#title" + j + " .citation").html("<span class='error-message' title='" + errorThrown + ": \n\n" + jqXHR.responseText + "'>" + textStatus + "</span>");
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log(data);
                nbAjaxCalls++;
                cardProviderCalls[idProvider]++;

                var timeElapsed = Math.round(($.now() - startRequest) / 1000); // milliseconds
                globalTimeElapsed += timeElapsed;
                console.log("getCitation done in +" + timeElapsed + "ms");
                //console.log("total progression: " + nbAjaxCalls + "/" + maxCalls);
                //console.log("progression for provider " + idProvider + ": " + cardProviderCalls[idProvider] + "/" + maxProviderCalls[idProvider]);

                if (data.error) {
                    $("#title" + j + " .citation").html("<span class='error-message' title='" + data.error.replace(/'/g, '\\\'') + "'>-!-</span>");
                    return;
                }
                if (data.examples && data.examples.length > 1) {
                    //examples = getUnique(examples).slice(1, 10).sort();
                    console.log(data);
                    $("#title" + j + " .citation").html(data.examples.join(" "));

                    var cachedate = new Date(1000 * parseInt(data.cacheinfo));
                    //cachedate = cachedate.toDateString() + ' ' + cachedate.toTimeString();
                    cachedate = cachedate.toISOString();
                    $("#title" + j + " .citation").append(' <br/><a class="tooltip"><img src="./images/glyphicons/glyphicons-196-circle-info.png"/><span></span></a>');
                    $("#title" + j + " .citation" + " a").show();
                    $("#title" + j + " .citation" + " a span").append("<div class='cached'>cached on <b>" + cachedate + "</b> <a>renew</a></div>");

                } else {

                    $("#title" + j + " .citation").html("<span class='error-message' title='empty ABCD field: /DataSets/DataSet/Metadata/IPRStatements/Citations/Citation/Text'>" + message.noCitation + "</span>");
                    $("#title" + j + " .citation").append(' <br/><a class="tooltip"><img src="./images/glyphicons/glyphicons-196-circle-info.png"/><span></span></a>');
                    $("#title" + j + " .citation" + " a").show();
                    $("#title" + j + " .citation" + " a span").append("<div class='cached'><a>renew</a></div>");
                }

                $("#title" + j + " .citation" + " a span div.cached a").on("click", function () {
                    $("#title" + j + " .citation").html("<img alt='loading' src='../images/loading.gif'/>");
                    getCitation(idProvider, url, filter, concept, j, 0);
                });

            });
    return false;
}

/**
 * Main function: populates the UI
 *
 * @returns {boolean} false
 */
function getProviders() {
    startRequest = $.now(); // microseconds
    globalTimeElapsed = 0;
    maxCalls = 0;
    nbAjaxCalls = 0;
    cardProviderCalls = [];
    maxProviderCalls = [];

    var loading = "<div>";
    loading += '<div class="cssload-container"><div class="cssload-circle-1"><div class="cssload-circle-2"><div class="cssload-circle-3"><div class="cssload-circle-4"><div class="cssload-circle-5"><div class="cssload-circle-6"><div class="cssload-circle-7"></div></div></div></div></div></div></div>';
    //loading += "<img src='./images/loading.gif'/ alt='loading...'>";
    loading += "</div>";
    $("#still-loading").html(loading);
    $("#still-loading").show();

    $.ajax({
        type: "GET",
        url: "./services/providers/index.php",
        dataType: "json"
    })
            .fail(function () {
                console.log("getProviders failed");
                displayErrorMessage($("#main"), message.providerError, "html");
                $("#still-loading").hide();
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {

                console.log(data);

                $("#main").html(buildUI(data));

                // loop over providers
                for (var i = 0; i < data.length; i++) {

                    // get Concepts for provider data[i].id
                    var conceptsStartRequest = $.now(); // microseconds
                    var concepts = [];
                    $.ajax({
                        type: "GET",
                        url: "./index.php",
                        data: {action: "getConcepts", idProvider: data[i].id},
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
                                var timeElapsed = Math.round(($.now() - conceptsStartRequest) / 1000); // milliseconds
                                globalTimeElapsed += timeElapsed;
                                console.log("getConcepts done for provider " + conceptData[0].institution_id + ", in " + timeElapsed + "ms");
                            });

                    var providerMainDataStartRequest = $.now(); // microseconds
                    // get DSA Points for provider data[i].id
                    $.ajax({
                        type: "GET",
                        url: "./index.php",
                        data: {action: "getProviderMainData", idProvider: data[i].id},
                        dataType: "json"
                    })
                            .fail(function () {
                                console.log("getProviderMainData failed");
                                displayErrorMessage($("#providerList"), message.providerError, "append");
                            })
                            .always(function () {
                                //console.log("finished");
                            })
                            .done(function (mainData) {
                                console.log(mainData);
                                var timeElapsed = Math.round(($.now() - providerMainDataStartRequest) / 1000); // milliseconds
                                globalTimeElapsed += timeElapsed;
                                console.log("getProviderMainData done for " + mainData[0].institution_id + ", in " + timeElapsed + "ms");

                                var totalRecords = 0;

                                for (var k = 0; k < mainData.length; k++) {
                                    //console.log(mainData[k]);
                                    console.log(" DSA -> " + mainData[k].id + " title: " + mainData[k].title + " url: " + mainData[k].url + " active:" + mainData[k].active);
                                    // if not yet created (inhibit duplicates)
                                    if ($("#dsa-record" + mainData[k].id).length == 0) {

                                        var header = "";
                                        if (k == 0) {
                                            header = "<tr><th>title</th><th>landingpages</th><th># current records</th>";
                                            for (var j = 0; j < concepts.length; j++) {
                                                atmp = concepts[j]["xpath"].split("/");
                                                header += "<th>#&nbsp;" + atmp[atmp.length - 1] + "</th>";
                                            }
                                            header += "<th> consistency </th><th>BioCASe</th><th > useful links </th>";
                                            header += "<th class='active'> active </th>";
                                            header += "</tr>";
                                        }

                                        var columns = "<tr id='dsa-record" + mainData[k].id + "' class='active" + mainData[k].active + "'>";

                                        // Data Source Access Point
                                        columns += "<td id='title" + mainData[k].id + "' class='titleDSA'>";
                                        columns += "<a href='#dsa-record" + mainData[k].id + "'>" + mainData[k].title;
                                        columns += " <img src='images/glyphicons/glyphicons-512-copy.png' alt='click to copy URL to clipboard' title='click to copy URL to clipboard'/>";
                                        columns += "<div class='dsa-point' id='dsa-point" + mainData[k].id + "'>" + mainData[k].url + "</div>";
                                        columns += "</a>";

                                        // Citation
                                        columns += "<div class='citation'></div>";
                                        columns += "</td>";

                                        getCitation(
                                                mainData[k].institution_id,
                                                mainData[k].url,
                                                mainData[k].filter,
                                                '/DataSets/DataSet/Metadata/IPRStatements/Citations/Citation/Text',
                                                mainData[k].id,
                                                1
                                                );

                                        // Landing Pages
                                        columns += "<td>";
                                        var isPreferred = "";

                                        // automatic landingpage
                                        if (mainData[k].preferred_landingpage == 0) {
                                            isPreferred = " isPreferred";
                                        }
                                        columns += "<div class='landingpage'><a target='_blank' href='../landingpage.php?provider=" + mainData[k].institution_id + "&file=" + mainData[k].url + "&filter=" + mainData[k].filter + "' title='ABCD Dataset Landing Page'><img width='36' src='images/landingpage.svg' alt='ABCD Dataset Landing Page'/></a><br/>ABCD Dataset</div>";

                                        // Userdefined Landing Page
                                        isPreferred = "";
                                        if (mainData[k].preferred_landingpage == 1) {
                                            isPreferred = " isPreferred";
                                        }
                                        if (mainData[k].landingpage_url) {
                                            columns += "<div class='landingpage" + isPreferred + "'><a target='_blank' href='" + mainData[k].landingpage_url + "' title='User defined Landingpage " + isPreferred + ": " + mainData[k].landingpage_url + "'><img width='36' src='images/landingpage.svg' alt='User defined Landingpage'/></a><br/>User defined</div>";
                                        }
                                        columns += "</td>";

                                        // Counts
                                        columns += "<td id='current-records" + mainData[k].id + "' class='cardinal'></td>";

                                        for (var j = 0; j < concepts.length; j++) {
                                            atmp = concepts[j]["xpath"].split("/");
                                            columns += "<td id='" + atmp[atmp.length - 1] + mainData[k].id + "' class='cardinal'></td>";
                                        }
                                        columns += "<td id='mapping-check" + mainData[k].id + "' class='consistency'></td>";
                                        columns += "<td id='biocase" + mainData[k].id + "'></td>";
                                        columns += "<td id='useful-links" + mainData[k].id + "' class='useful-links'></td>";
                                        columns += "<td id='active" + mainData[k].id + "' class='active" + mainData[k].active + "'>" + mainData[k].active + "</td>";
                                        columns += "</tr>";


                                        // ACCORDION of DSA Points
                                        $("#provider_" + mainData[k].institution_id + " table").append(header + columns);

                                        // on click on DSA: copy to clipboard
                                        $("#title" + mainData[k].id + " img").on("click", function (event)
                                        {
                                            event.preventDefault();
                                            copyToClipboard($(this).parent().find("div"));
                                            displaySystemMessage("URL copied to clipboard. ");
                                        });


                                        // get USEFUL LINKS for current DSA
                                        $.ajax({
                                            type: "GET",
                                            url: "./services/useful-links/index.php",
                                            data: {"provider": mainData[k].institution_id, "dsa": mainData[k].id},
                                            dataType: "json"
                                        })
                                                .fail(function (jqXHR, textStatus, errorThrown) {
                                                    console.log("getUsefulLinks failed:" + textStatus);
                                                    displayErrorMessage($(".accordion-content-active .useful-links"), message.providerError, "append");
                                                })
                                                .always(function () {
                                                    //console.log("finished");
                                                })
                                                .done(function (linkData) {
                                                    console.log("getUsefulLinks done.");
                                                    // is only a DB call, no Ajax Call involving CURL
                                                    //nbAjaxCalls++;

                                                    //console.log(linkData);
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

                                                        $("#useful-links" + linkData[j].dsa).
                                                                append("<div class='useful-link'><a target='customlink-" + linkData[j].collection_id + "' href='" + linkData[j].link + "' title='" + linkData[j].title + ": " + shortlink + "'>" + logo + "</a></div> ");
                                                    }
                                                });

                                        // COUNT CURRENT RECORDS
                                        totalRecords += getCurrentRecords(mainData[k].institution_id, mainData[k].id, mainData[k].url, mainData[k].filter, 0);
                                    }

                                    // COUNT CONCEPTS
                                    getCountConcept(mainData[k].institution_id, mainData[k].id, mainData[k].url, mainData[k].xpath, mainData[k].specifier, mainData[k].filter);

                                    // CHECK CONSISTENCY
                                    var mappingUrl = "consistency/consistency.php?"
                                            + "&provider=" + mainData[k].institution_id
                                            + "&dsa=" + mainData[k].url.split("dsa=")[1].split("&")[0]
                                            // + "&url=" + mainData[k].url
                                            + "&filter=" + encodeURIComponent(mainData[k].filter);
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

                                $("#provider_" + mainData[0].institution_id + " table").append(
                                        "<tr><td align='right'> SUM:</td><td></td><td id='total-records" + mainData[0].institution_id + "' class='cardinal'>0</td></tr>"
                                        );
                            });

                } // end of loop over providers

                // make it an accordion
                $("#providerList").accordion({
                    collapsible: true,
                    active: false,
                    heightStyle: "content",
                    beforeActivate: allowMultipleOpen
                });

                $('.progress_bar').each(function () {

                    var localProgressbar = $(this);
                    var localProgressLabel = $(this).find(".progress-label");
                    var localProgressId = $(this).attr("id").split("_")[1];

                    var j = localProgressId;
                    maxProviderCalls[j] = parseInt($("#maxProviderCalls_" + j).text());
                    cardProviderCalls[j] = 0;

                    localProgressbar.progressbar({
                        max: 100,
                        value: 0,
                        change: function () {
                            //localProgressLabel.text(localProgressbar.progressbar("value") + "%");
                            //localProgressLabel.html("");
                        },
                        complete: function () {
                            localProgressLabel.html("");
                            localProgressLabel.addClass("completed");
                        }
                    });

                    function progress() {
                        var val = localProgressbar.progressbar("value") || 0;
                        localProgressbar.progressbar("value", Math.ceil(100 * (cardProviderCalls[j] / maxProviderCalls[j])))
                                .removeClass("beginning middle end")
                                .addClass(val / maxCalls < 0.3333 ? "beginning" : val / maxCalls < 0.6666 ? "middle" : "end");
                        if (val < 100)
                        {
                            setTimeout(progress, 20);
                        }
                    }
                    setTimeout(progress, 100);
                });
            });
    return false;
}

/**
 * get total maximal number of calls to the BPS software
 * UNUSED
 *
 * @returns {int} total
 */
function getTotalMaxCalls() {
    $.ajax({
        type: "GET",
        url: "index.php",
        data: {"action": "getTotalMaxCalls"},
        dataType: "json"
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getTotalMaxCalls failed");
                console.log(textStatus + " *** " + errorThrown + " *** ");
                //console.log(jqXHR.responseText);
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log("getTotalMaxCalls done.");
                //console.log(data);
                $("#maxCalls").text(data);
                console.log(data);
            });
    return false;
}



$(function() {

    getProviders();

    ///////////////////////////////
    // global progressbar
    //
    maxCalls = parseInt($("#cardAllCalls").text());
    //maxCalls = getTotalMaxCalls();
    console.log("maxCalls=" + maxCalls);

    globalProgressbar = $("#progressbar");
    globalProgressLabel = $("#progressbar .progress-label");

    globalProgressbar.progressbar({
        max: maxCalls,
        value: false,
        change: function () {
            var percentage = Math.round(100 * parseInt(globalProgressbar.progressbar("value")) / maxCalls);
            globalProgressLabel.text(
                    //globalProgressbar.progressbar("value") + "/" + maxCalls + " calls "
                    percentage + "%"
                    + " (" + globalTimeElapsed + "ms)"
                    );
        },
        complete: function () {
            //globalProgressLabel.text(maxCalls + " calls made to BioCASe Provider Software.");
            globalProgressLabel.text("all done");
            console.log(maxCalls + " total calls made to BioCASe Provider Software.");
            $("#still-loading").hide();
        }
    });

    function progress() {
        var val = globalProgressbar.progressbar("value") || 0;
        console.log("global progression: " + nbAjaxCalls + "/" + maxCalls);
        globalProgressbar.progressbar("value", nbAjaxCalls)
                .removeClass("beginning middle end")
                .addClass(val / maxCalls < 0.3333 ? "beginning" : val / maxCalls < 0.6666 ? "middle" : "end");
        if (val < maxCalls)
        {
            setTimeout(progress, 80);
        }
    }

    setTimeout(progress, 1000);

});


