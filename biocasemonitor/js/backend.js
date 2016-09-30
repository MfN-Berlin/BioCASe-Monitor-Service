
/**
 * BioCASe Monitor 2.0
 *
 * @copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @package Bms
 * @file biocasemonitor/js/backend.js
 * @brief javascript functions used in the backend
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
 * spinner as a "worm" animated graphic
 *
 * @constant {string}
 */
spinner = '<div class="cssload-container"><div class="cssload-circle-1"><div class="cssload-circle-2"><div class="cssload-circle-3"><div class="cssload-circle-4"><div class="cssload-circle-5"><div class="cssload-circle-6"><div class="cssload-circle-7"></div></div></div></div></div></div></div>';


/**
 *  save Main Data of Provider
 *
 * @returns void
 */
function saveMainMetadata() {
    $.ajax({
        type: "POST",
        url: "../core/updateMainMetadata.php",
        data: $("#updateProvider").serialize(),
        dataType: "text"
    })
            .fail(function () {
                console.log("saveMainMetadata failed");
            })
            .always(function () {
                //console.log("saveMainMetadata: finished");
            })
            .done(function (data) {
                console.log("saveMainMetadata:  DB persistance ... " + data);
                displaySystemMessage("Main Metadata saved");
            });
}

/**
 *  get Data Source Access (DSA) Points and DataSet Titles
 *
 * @param {string} url complete Query URL incl. ?dsa=xxx
 * @param {integer} idDSA
 * @param {string} selectedValue previously selected DataSet
 * @param {string} dataSet
 * @returns void
 */
function getDataSourceAccessPoints(url, idDSA, selectedValue, dataSet) {
    $("#ds_accesspoint" + idDSA).html(spinner);
    $.ajax({
        type: "GET",
        url: "../core/getDataSources.php",
        dataType: "json",
        data: {"url": url, "idDSA": idDSA}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getDataSourceAccessPoints failed");
                console.log(url);
                console.log(textStatus + ": " + errorThrown);
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log("getDataSourceAccessPoints done: " + idDSA + " url=" + url);
                //console.log(data);
                var items = "";
                $.each(data, function (i, el) {
                    //console.log(el);
                    items += "<option value='" + el + "'";
                    if (el === selectedValue) {
                        items += " selected='selected'";
                    }
                    items += ">" + el + "</option>";
                });
                // console.log(items);
                $("#ds_accesspoint" + idDSA).html(items);

                console.log("filling in url: " + url + "/pywrapper.cgi?dsa=" + selectedValue);
                $('#ds_url' + idDSA).val(url + "/pywrapper.cgi?dsa=" + selectedValue);

                console.log("getting Datasets for " + url + "/pywrapper.cgi?dsa=" + selectedValue);
                getDataSetTitles(idDSA, url + "/pywrapper.cgi?dsa=" + selectedValue, dataSet);
            });
}

function getDataSetTitles(idDSA, url, dataset) {
    $("#ds_title_list" + idDSA).html(spinner);
    $.ajax({
        type: "GET",
        url: "../core/getDataSetTitles.php",
        dataType: "json",
        data: {"url": url, "idDSA": idDSA}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getDataSetTitles failed");
                console.log(url);
                console.log(textStatus + ": " + errorThrown);
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log("getDataSetTitles done: " + idDSA + " url=" + url);
                console.log("/core/getDataSetTitles.php?url=" + url + "&idDSA=" + idDSA);
                //console.log(data);
                dataset = dataset.trim();
                console.log("preselected dataset was: " + dataset);

                var cssClass = data[0];
                var selectbox = "<select class='" + cssClass + "'>";
                for (var i = 0; i < data.length; i++) {
                    selectbox += "<option";

                    if (data[i].trim() === dataset) {
                        selectbox += " selected='selected'";
                    }
                    selectbox += ">" + data[i].trim() + "</option>";
                }
                selectbox += "</select>";
                $("#ds_title_list" + idDSA).html(selectbox);
                $('#ds_title_list' + idDSA + ' select').on("change", function () {
                    var selectedVal = $(this).val();
                    console.log("dataset changed to selected option: " + selectedVal);
                    $('#ds_title_list' + idDSA + ' select').val(selectedVal);
                    var defaultFilter = '<like path="/DataSets/DataSet/Metadata/Description/Representation/Title">' + selectedVal + '</like>';
                    //@todo: set default only if empty
                    $('#ds_final_filter' + idDSA).val(defaultFilter);
                });
            });
}

function addDSA(idProvider) {
    console.log("adding DSA for provider " + idProvider);
    //console.log("adding DSA for provider " + idProvider + ", with biocase-url=" + bioCaseUrl);
    $.ajax({
        type: "POST",
        url: "../core/addDSA.php",
        data: {"key": idProvider},
        dataType: "json"
    })
            .fail(function () {
                console.log("addDSA failed");
            })
            .always(function () {
                //console.log("addDSA: finished");
            })
            .done(function (data) {
                console.log("addDSA done. ");
                console.log(data);
                var newId = data.id;
                var newTitle = data.title;
                var newPywrapper = data.url;

                var listItem = $("<li/>");
                $(listItem).append("<a href='#dsa" + newId + "' "
                        + "title='" + newTitle + "'>"
                        + "<span>" + newTitle + "</span></a>");
                $("#DSAGroupDynamic ul").append(listItem);

                var collection = "<div id='dsa" + newId + "'" + " data-id='" + newId + "'>";

                // id
                collection += "<input name='ds[" + newId + "][id]' type='hidden' value='" + newId + "'/>";
                collection += "<table>";

                // Status
                collection += "<tr><td><td><label for='ds_status'>Status: </label>";
                collection += "<td><input name='ds[" + newId + "][active]' type='text' value='0'/>";

                // DataSource
                collection += "<tr><td><td><label for='ds_accesspoint'>Data-Source: </label>";
                collection += "<td><select id='ds_accesspoint" + newId + "' name='ds[" + newId + "][accesspoint]'/>";

                // DataSource Full Title
                collection += "<tr><td><td><label for='ds_title'>Title: </label>" + "<td><input id='ds_title" + newId + "'" + " name='ds[" + newId + "][title]' type='text' required='required'/>";

                // url
                collection += "<tr><td><td><label for='ds_url'>URL:</label>";
                collection += "<td><input id='ds_url" + newId + "'" + " name='ds[" + newId + "][url]' type='text' readonly='readonly'/>";
                //collection += "<br/><span class='small-info' id='ds_query_url" + newId + "'/>";

                // DataSet
                collection += "<tr><td><td><label for='ds_title_list'>Data Set: </label><td>";
                collection += "<div id='ds_title_list" + newId + "'>" + spinner + "</div>";

                // landingpage
                var preferred_0 = "checked='checked'";
                var preferred_1 = "";
                // automatic
                collection += "<tr><td><td colspan='2'>";
                collection += "<label for='ds_landingpage_concept" + newId + "'>Landing-Page, automatically generated:</label>";
                collection += "<tr>";
                collection += "<td>";
                collection += "<td>" + "<input id='ds_preferred_landingpage" + newId + "'"
                        + " name='ds[" + newId + "][preferred_landingpage]' type='radio' value='0' " + preferred_0 + "/>";
                collection += "<td id='ds_landingpage_concept" + newId + "'>";
                collection += "<span class='small-info'>builds a pleasant teaser page</span>";
                collection += "</td>";
                // userdefined url
                collection += "<tr><td><td colspan='2'>";
                collection += "<label for='ds_landingpage_url" + newId + "'>Landing-Page, user-defined URL: </label>";
                collection += "<tr>";
                collection += "<td><td>" + "<input id='ds_preferred_landingpage" + newId + "'"
                        + " name='ds[" + newId + "][preferred_landingpage]' type='radio' value='1'" + preferred_1 + "/>";
                collection += "<td><input id='ds_landingpage_url" + newId + "'" + " name='ds[" + newId + "][landingpage_url]' value='' type='text' />";

                // filter
                collection += "<tr><td colspan='3'><div id='collapsible_" + newId + "' class='collapsible' >";
                collection += "<h6>advanced</h6>";
                collection += "<div style='min-height: 120px;'>";
                // @todo: query builder
                collection += "<div id='query-builder" + newId + "' class='query-builder'></div>";
                // filter list coming via ajax
                collection += "<div id='ds-filter" + newId + "'></div>";
                collection += "<div class='short-hint'>" + message.filterSyntax + "</div>";
                collection += "<textarea id='ds_final_filter" + newId + "'" + " name='ds[" + newId + "][final_filter]'></textarea>";
                collection += "</div></td></tr>";

                collection += "<tr><td><td colspan='2'><fieldset class='useful-links' id='useful-links" + newId + "'><legend>Useful Links</legend>";
                collection += "<div class='short-hint'>" + message.archiveFirst + "</div><ol></ol></div></tr>";

                collection += "</table>";

                // title and buttons on a single line
                collection += "<div class='active-dsa'>";
                // full title
                collection += "<div class='active-dsa-title'>" + "please type in the full title" + "</div>";
                // save
                collection += "<div class='save' id='saveDSA" + newId + "'><a href='#'><img alt='saveDSAPoint' title='Save Data Source' src='../images/glyphicons/glyphicons-415-disk-save.png' /></a></div>";
                // remove
                collection += "<a href='#' class='active-dsa remove' onclick='hideDSA(" + newId + ")' title='Remove Data Source'>"
                        + "<img class='remove' alt='hideThisDSA' src='../images/glyphicons/glyphicons-208-remove-2.png' /> " + "</a> ";
                collection += "</div>";
                collection += "</div>";

                $("#DSAGroupDynamic").append(collection);

                // fill in DSA Point
                getDataSourceAccessPoints(newPywrapper, newId, "", "");
                // trigger changes to the DSA Point
                $('#ds_accesspoint' + newId).on("change", {datasource: data}, function (event) {

                    var myData = event.data.datasource;
                    console.log(myData);
                    console.log("set url to: " + myData.url + "/pywrapper.cgi?dsa=" + $(this).val());
                    $('#ds_url' + myData.id).val(myData.url + "/pywrapper.cgi?dsa=" + $(this).val());
                    //$('#ds_query_url' + myData.id).html(myData.url + "/querytool/details.cgi?dsa=" + $(this).val());

                    // compute the list of all possible datasets
                    getDataSetTitles(myData.id, $('#ds_url' + myData.id).val(), "");

                    // set DSA point as default full title
                    if ($('#ds_title' + myData.id).val() === "") {
                        $('#ds_title' + myData.id).val(myData.title);
                    }
                });
                // trigger value of accesspoint
                $('#ds_accesspoint' + newId).trigger("change", {datasource: data});


                $('#ds_url' + newId).html(newPywrapper + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + newId).val());

                // title and buttons on a single line
                collection += "<div class='active-dsa'>";
                // full title
                collection += "<div class='active-dsa-title'>new Title for DSA " + newId + "</div>";
                // save
                collection += "<div class='save' id='saveDSA" + newId + "'><a href='#'><img alt='saveDSAPoint' title='Save Data Source' src='../images/glyphicons/glyphicons-415-disk-save.png' /></a></div>";
                // remove
                collection += "<a href='#' class='active-dsa remove' onclick='hideDSA(" + newId + ")' title='Remove Data Source'>"
                        + "<img class='remove' alt='hideThisDSA' src='../images/glyphicons/glyphicons-208-remove-2.png' /> " + "</a> ";
                collection += "</div>";

                collection += "</div>";

                $('#ds_final_filter' + newId).val("");

                // trigger value of url and set title according to it
                $('#ds_url' + newId).on("change", function () {
                    // compute the list of all possible datasets
                    getDataSetTitles(newId, $(this).val(), "");
                    // set shortTitle as default full title
                    var tmptitle = $(this).val().split("dsa=")[1];
                    $('#ds_title' + newId).val(tmptitle);
                });

                // trigger SAVE button
                $("#saveDSA" + newId).on("click", function () {
                    console.log("triggering saveDSA button for record " + newId);

                    var myDSA = new Object();
                    myDSA.id = newId;
                    myDSA.title = $("#ds_title" + newId).val();
                    myDSA.url = $("#ds_url" + newId).val();
                    myDSA.title_slug = myDSA.url.split("dsa=")[1];
                    myDSA.dataset = $("#ds_title_list" + newId + " select").val();
                    myDSA.filter = $("#ds_final_filter" + newId).val();
                    myDSA.landingpage_url = "";
                    myDSA.preferred_landingpage = 0;

                    console.log(myDSA);
                    saveDSAPoint(myDSA);
                });

                $("#collapsible_" + newId).accordion({collapsible: true, active: false});

                $("#DSAGroupDynamic").tabs("refresh");

                displaySystemMessage("new Data Accesss Point created.<br/>Please click on the tab 'new title' and edit the URL");
            });
}

function saveDSAPoint(dsa) {

    console.log("saveDSAPoint...");
    console.log(dsa);

    $.ajax({
        type: "POST",
        url: "../core/updateDSA.php",
        data: dsa,
        dataType: "json"
    })
            .fail(function () {
                console.log("updateDSA failed");
            })
            .always(function () {
                //console.log("updateDSA: finished");
            })
            .done(function (data) {
                console.log("updateDSA done.");
                console.log(data);
                var record = data[0];
                var msg = record.title;

                // current tab
                $("#DSAGroupDynamic ul li a[href=#dsa" + record.id + "] span").html(
                        record.title.substring(0, 12)
                        );
                $("#DSAGroupDynamic ul li a[href=#dsa" + record.id + "]").attr("title", record.title);
                if (record.title != record.dataset)
                    msg += " [" + record.dataset + "] ";

                // current tab titlecontent
                $("#DSAGroupDynamic ul li[aria-selected=true] span").html(record.title_slug.substring(0, 11));

                // title bar at bottom
                $("#DSAGroupDynamic div.active-dsa-title").html(record.title);

                // layout for inactive DSA
                if (record.active == 0)
                    $("#dsa" + record.id).addClass("inactive");
                if (record.active == 1)
                    $("#dsa" + record.id).removeClass("inactive");

                // full title input field
                $("#ds_title" + record.id).val(record.title);

                $("#DSAGroupDynamic").find("input").removeClass("unsaved");
                $("#DSAGroupDynamic").find("input").addClass("saved");
                $("#DSAGroupDynamic").tabs("refresh");

                displaySystemMessage("DSA Point saved: <br/><br/>" + msg);
            });

}

function showDSA() {
    $("#DSAGroupDynamic li[aria-selected='true']").show();
    $("#DSAGroupDynamic div[aria-hidden='false']").show();
    $("#system-message").removeClass("modal");
    displaySystemMessage("Removal of group has been cancelled.");

}

function hideDSA(id) {
    $("#DSAGroupDynamic li[aria-selected='true']").hide();
    $("#dsa" + id).hide();
    // on confirm deletion, remove DOM node
    $("#system-message").addClass("modal");
    $("#system-message").html("DSA temporarily removed. <p>Please <a href='#' onclick='showDSA()'>&nbsp;undo&nbsp;</a> the operation or <a href='#' onclick='removeDSA(" + id + ")'>&nbsp;confirm&nbsp;</a> the removal.</p>");
    $("#system-message").show();
    //
}

/**
 * removes a given DSA
 *
 * @param {integer} id idDSA
 * @returns void
 */
function removeDSA(id) {
    $("#DSAGroupDynamic li[aria-selected='true']").remove();
    $("#dsa" + id).remove();
    $.ajax({
        type: "POST",
        url: "../core/removeDSA.php",
        data: {"key": id},
        dataType: "text"
    })
            .fail(function () {
                console.log("removeDSA failed");
            })
            .always(function () {
                //console.log("removeDSA: DB persistance finished");
            })
            .done(function (data) {
                console.log("removeDSA:  DB persistance done: " + data);
                displaySystemMessage("DSA removed");
            });
}

/**
 * adds a Count Concept
 *
 * @returns void
 */
function addCount() {
    var listItem = $("<li/>");
    listItem.attr("data-id", 0);
    $(listItem).append(
            "<img alt='move' alt='moveItem' title='move this item up and down' src='../images/glyphicons/glyphicons-187-move.png' />"
            + "<input type='hidden' name='countId[]' value='0'/>"
            + "<input name='xpath[]' type='text'  placeholder='type in an xpath, like /DataSets/DataSet/Units/Unit/UnitID'/>"
            + "<select name='specifier[]' class='medium' size='3' multiple='multiple'>"
            + "<option value='1' >total</option>"
            + "<option value='2'>distinct</option>"
            + "<option value='4'>dropped</option>"
            + "</select>"
            + " <a href='#' class='remove' onclick='removeCount(0)'><img alt='remove' alt='remove this count concept' title='remove this count concept' src='../images/glyphicons/glyphicons-208-remove-2.png' /></a>"
            //+ "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span>"
            + " <a href='#' class='save' onclick='saveCount(0)'>"
            + "<img class='save' alt='saveCount' title='save this count concept' src='../images/glyphicons/glyphicons-415-disk-save.png' /></a>"
            );
    $(listItem).find("input").autocomplete({
        source: "../core/getAllCountConcepts.php",
        minLength: 2
    });

    $("#count-concepts ul").append(listItem);
    $.ajax({
        type: "POST",
        url: "../core/addCount.php",
        data: $("#updateProvider").serialize(),
        dataType: "json"
    })
            .fail(function () {
                console.log("addCount failed");
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                listItem.attr("data-id", data["id"]);
                listItem.find("input[type=hidden]").val(data["id"]);
                listItem.find("a.save").attr("onclick", "saveCount(" + data["id"] + ")");
                listItem.find("a.remove").attr("onclick", "removeCount(" + data["id"] + ")");
            });
}

/**
 * removes a Count Concept
 *
 * @param {integer} id a Count Concept
 * @returns void
 */
function removeCount(id) {
    console.log("removing CountId=" + id);
    $("#count-concepts li[data-id=" + id + "]").remove();
    $.ajax({
        type: "GET",
        url: "../core/removeCount.php?key=" + id,
        dataType: "text"
    })
            .fail(function () {
                console.log("removeCount failed");
            })
            .always(function () {
            })
            .done(function (data) {
                console.log("removeCount:  DB persistance..." + data);
                displaySystemMessage("CountConcept removed.<br/>Recovering impossible. ");

            });
}

/**
 * saves a Count Concept
 *
 * @param {integer} id a Count Concept
 * @returns void
 */
function saveCount(id) {
    var data = {};
    data["id"] = id;
    data["xpath"] = $("#count-concepts li[data-id=" + id + "]").find("input[name*=xpath]").val();
    data["specifier"] = $("#count-concepts li[data-id=" + id + "]").find("select[name*=specifier]").val();
    $.ajax({
        type: "POST",
        url: "../core/updateCountConcept.php",
        data: data
    })
            .fail(function () {
                console.log("updateCountConcept failed");
            })
            .always(function () {
                //console.log("updateCountConcept finished");
            })
            .done(function (data) {
                console.log("updateCountConcept done: " + data);
                $("#count-concepts li[data-id=" + id + "]").find("input[name*=xpath]").removeClass("unsaved");
                $("#count-concepts li[data-id=" + id + "]").find("input[name*=xpath]").addClass("saved");
                $("#count-concepts li[data-id=" + id + "]").find("select[name*=specifier]").removeClass("unsaved");
                $("#count-concepts li[data-id=" + id + "]").find("select[name*=specifier]").addClass("saved");
                displaySystemMessage("Count Concept updated. ");
            });
}

/**
 * adds a Useful Link
 *
 * @param  idDSA  - ID of Data Source Access Point
 * @param  idProvider - ID of Data Center
 * @return void
 */
function addUsefulLink(idDSA, idProvider) {
    $.ajax({
        type: "POST",
        url: "../core/addUsefulLink.php",
        data: {"idDSA": idDSA, "idProvider": idProvider},
        dataType: "json"
    })
            .fail(function () {
                console.log("addUsefulLink failed");
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log("addUsefulLink: idDSA=" + idDSA + " idProvider=" + idProvider);
                console.log(data);
                var newId = data.id;
                var newPosition = $("#useful-links" + idDSA + " ol li").size();
                var listItem = $("<li/>");
                listItem.attr("data-id", data.id); // primary key
                listItem.attr("id", "item" + idDSA + "_" + data.id); // primary key
                listItem.attr("title", "move this item up and down");
                $(listItem).append(
                        "<img alt='move' alt='move' title='move this item up and down' src='../images/glyphicons/glyphicons-187-move.png' />"
                        + " <input id='link-title" + newId + "' name='link_title[]' type='text' value='' placeholder='title' class='short' list='link-categories'/>"
                        + "<datalist id='link-categories' class='link-categories'>"
                        + $("#global-link-categories").html()
                        + "</datalist>"
                        + "<div class='mini-logo'><img alt='logo' src='../images/GFBio_logo.png'></div>"
                        + " <input id='link" + newId + "' name='link_url[]' type='text' class='medium' value='' placeholder='type in an URL'/>"
                        + " <a href='#' onclick='hideUsefulLink(" + idDSA + "," + newId + ")'>"
                        + "<img alt='remove' alt='removeUsefulLink' title='remove this link " + newId + "' src='../images/glyphicons/glyphicons-208-remove-2.png' /></a>"
                        + " <div class='save' onclick='saveUsefulLink(" + idDSA + "," + newId + ")'><a href='#'><img alt='save' title='save this useful link' src='../images/glyphicons/glyphicons-415-disk-save.png' /></a></div>"
                        );
                $("#useful-links" + idDSA + " ol").append(listItem);
                $("#useful-links" + idDSA + " ol").sortable("refresh");
            });
}

/**
 * shows a Useful Link
 *
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Useful Link
 * @returns void
 */
function showUsefulLink(idDSA, id) {
    $("#useful-links" + idDSA + " ol li[data-id=" + id + "]").show();
    $("#system-message").fadeOut();
}

/**
 * hides a Useful Link
 *
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Useful Link
 * @returns void
 */
function hideUsefulLink(idDSA, id) {
    $("#useful-links" + idDSA + " ol li[data-id=" + id + "]").hide();
    $("#system-message").html("UsefulLink temporarily removed. \
        <br/>Please <a onclick='showUsefulLink(" + idDSA + "," + id + ")'>undo</a> the operation \n\
        or <a onclick='removeUsefulLink(" + idDSA + "," + id + ")'>confirm</a> the removal.");
    $("#system-message").fadeIn(800);
}

/**
 * removes a Useful Link
 *
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Useful Link
 * @returns void
 */
function removeUsefulLink(idDSA, id) {
    $("#useful-links" + idDSA + " ol li[data-id=" + id + "]").remove();
    var data = {idDSA: idDSA, id: id};
    $.ajax({
        type: "POST",
        url: "../core/removeUsefulLink.php",
        data: data,
        dataType: "text"
    })
            .fail(function () {
                console.log("removeUsefulLink failed");
            })
            .always(function () {
            })
            .done(function (data) {
                console.log("removeUsefulLink:  DB persistance: " + data);
                displaySystemMessage("UsefulLink removed.<br/>Recovering impossible.");

            });
}

/**
 * saves a Useful Link
 *
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Useful Link
 * @returns void
 */
function saveUsefulLink(idDSA, id) {
    var data = {};
    data["id"] = id;
    data["title"] = $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_title]").val();
    data["link"] = $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_url]").val();
    //console.log(data);
    $.ajax({
        type: "POST",
        url: "../core/updateUsefulLink.php",
        data: data
    })
            .fail(function () {
                console.log("updateUsefulLink failed");
            })
            .always(function () {
                //console.log("saveUsefulLink finished");
            })
            .done(function (linkdata) {
                // returns the logo blob
                console.log("updateUsefulLink " + id + " done.");
                $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_title]").removeClass("unsaved");
                $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_title]").addClass("saved");
                $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_url]").removeClass("unsaved");
                $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_url]").addClass("saved");
                if (data.length > 0) {
                    $("#useful-links" + idDSA + " li[data-id=" + id + "] span").html("<img src='" + linkdata + "' height='20'/>");
                }
                displaySystemMessage("Useful Link " + id + " updated.");
            });
}

/**
 * get all link catageories and populates the appropriate Html selectbox
 *
 * @returns void
 */
function getLinkCategories() {
    $.ajax({
        type: "GET",
        url: "../core/getLinkCategories.php",
        dataType: "json"
    })
            .fail(function () {
                console.log("getLinkCategories failed");
            })
            .always(function () {
                //console.log("getLinkCategories finished");
            })
            .done(function (data) {
                var result = "";
                for (i = 0; i < data.length; i++) {
                    result += "<option>" + data[i].name + "</option>";
                }
                $("#global-link-categories").html(result);
            });
}


$(function() {

    // highlight active menuItem
    var url = window.location.href;
    $("ul.mainMenu li a").each(function () {
        if (url == (this.href)) {
            $(this).closest("a").addClass("active");
        }
    });

    // change provider
    $('#pr_name').change(function () {
        var selectedVal = $('#pr_name').val();
        selectedVal = parseInt(selectedVal);
        if (selectedVal != -1) {
            deleteOldValues();
            getAllMetadata(selectedVal);
            getCountConcepts(selectedVal);
        } else {
            deleteOldValues();
        }
    });

    // load Link Categories (gbif, etc.)
    getLinkCategories();

    /**
     * removes displayed infos from the form
     *
     * @returns {boolean} false
     */
    function deleteOldValues() {
        $('#pr_name_edit').val('');
        $('#pr_css').val('');
        $('#pr_ui').val('');
        $('#pr_draggable').val('');
        $('#main-metadata table').hide();
        $("#main-metadata .save").html("");
        $("#count-concepts ul").html("");
        $("#count-concepts img").remove();
        $("#DSAGroupDynamic div").remove();
        $("#DSAGroupDynamic ul").html("");
        return false;
    }

    /**
     * get the number of elements satisfying a given concept
     *
     * @param {integer} idProvider
     * @returns {undefined}
     */
    function getCountConcepts(idProvider) {
        $.ajax({
            type: "GET",
            url: "../core/getConcepts.php?key=" + idProvider,
            dataType: "json"
        })
                .fail(function () {
                    console.log("getCountConcepts failed");
                })
                .always(function () {
                    //console.log("finished");
                })
                .done(function (data) {
                    //console.log(data);
                    for (i = 0; i < data.length; i++) {
                        var listItem = $("<li/>");
                        listItem.attr("data-id", data[i]["id"]);
                        $(listItem).append(
                                "<img alt='move' alt='moveItem' title='move this item up and down' src='../images/glyphicons/glyphicons-187-move.png' />"

                                + "<input type='hidden' name='countId[]' value='" + data[i]["id"] + "'/>"
                                + "<input id='xpath_" + data[i]["id"] + "' name='xpath[]' type='text' value='" + data[i]["xpath"] + "'/>"
                                + "<select class='medium' id='specifier_" + data[i]["id"] + "' name='specifier[]' multiple='multiple' size='3'>"
                                + "<option value='1' " + (((data[i]["specifier"] & 1) == 1) ? "selected" : "") + ">total</option>"
                                + "<option value='2' " + (((data[i]["specifier"] & 2) == 2) ? "selected" : "") + ">distinct</option>"
                                + "<option value='4' " + (((data[i]["specifier"] & 4) == 4) ? "selected" : "") + ">dropped</option>"
                                + "</select>"

                                + " <a href='#' onclick='removeCount(" + data[i]["id"] + ")'>"
                                + "<img class='remove' alt='removeCount' title='remove this count concept' src='../images/glyphicons/glyphicons-208-remove-2.png' /></a>"

                                + " <a href='#' onclick='saveCount(" + data[i]["id"] + ")'><img class='save' alt='saveCount' title='save this count concept' src='../images/glyphicons/glyphicons-415-disk-save.png' /></a>"
                                );
                        $("#count-concepts ul").append(listItem);

                        $("#xpath_" + data[i]["id"]).autocomplete({
                            source: "../core/getAllCountConcepts.php",
                            minLength: 2
                        });

                        $("#xpath_" + data[i]["id"]).on("change", function () {
                            //console.log("editing current listitem ");
                            $(this).addClass("unsaved");
                        });
                        $("#specifier_" + data[i]["id"]).on("change", function () {
                            //console.log("changed value in dropdown list ");
                            $(this).addClass("unsaved");
                        });
                    }
                    $("#count-concepts").append("<a href='#' onclick='addCount()'><img alt='addCount' title='add a count concept' src='../images/glyphicons/glyphicons-433-plus.png' /></a>");

                    $("#count-concepts ul").sortable({
                        axis: 'y',
                        start: function (event, ui) {
                            //console.log("starting");
                        },
                        update: function (event, ui) {
                            var data;
                            data = $('#updateProvider').serialize();
                            $.ajax({
                                data: data,
                                type: 'POST',
                                url: '../core/sortCountConcepts.php'
                            })
                                    .fail(function () {
                                        console.log("sortCountConcepts failed");
                                    })
                                    .always(function () {
                                        //console.log("always");
                                    })
                                    .done(function (data) {
                                        console.log("sortCountConcepts done: " + data);
                                    });
                        }
                    });

                });
        return false;
    }

    /**
     * gets the capabilities
     *
     * @param {integer} provider
     * @param {integer} idDSA
     * @param {string} dsa
     * @returns {boolean} false
     */
    function getCapabilities(provider, idDSA, dsa) {
        var filters = $("#all-filters section[data-id=" + idDSA + "]").html();
        if (!filters) {

            $.ajax({
                type: "GET",
                url: "../services/capabilities/index.php",
                dataType: "html",
                data: {"provider": provider, "dsa": dsa, "format": "html", "localId": ""}
            })
                    .fail(function () {
                        console.log(idDSA + ": getCapabilities failed");
                    })
                    .always(function () {
                        //console.log("finished");
                    })
                    .done(function (data) {
                        console.log("capabilities done, rendering html for dsa=" + idDSA);
                        //console.log(data);
                        $("#ds-filter" + idDSA).html(data);
                        // build a cache in sitio:
                        $("#all-filters").append("<section class='cache' data-id='" + idDSA + "'>" + data + "</section>");

                        $('#ds-filter' + idDSA + ' select').on("change", function () {
                            var selectedVal = $(this).val();
                            var currentFilter = $('#ds_final_filter' + idDSA).val();
                            $('#ds_final_filter' + idDSA).val(currentFilter + " <like path='/DataSets/DataSet/Metadata/Description/Representation/Title'>" + selectedVal + "</like>");
                            $('#ds_title' + idDSA + " select").val("");
                        });

                    });
        } else {
            console.log("getCapabilities from cache");
            $("#ds-filter" + idDSA).html(filters);
        }
        return false;
    }

    /**
     * gets the useful Linksof a given DSA
     *
     * @param {integer} idProvider
     * @param {integer} idDSA
     * @returns {boolean} false
     */
    function getUsefulLinks(idProvider, idDSA) {
        $.ajax({
            type: "GET",
            url: "../services/useful-links/index.php?dsa=" + idDSA + "&provider=" + idProvider,
            dataType: "json"
        })
                .fail(function () {
                    console.log("service get useful-links failed");
                })
                .always(function () {
                    //console.log("finished");
                })
                .done(function (data) {
                    console.log("==> getUsefulLinks");
                    console.log("collection " + idDSA + " has " + data.length + " useful links");
                    console.log(data);
                    if (data.length == 0) {
                        $("#useful-links" + idDSA).append("<input type='hidden' name='ds_current_id' value='" + idDSA + "'/>");
                        $("#useful-links" + idDSA).append("<a href='#' onclick='addUsefulLink(" + idDSA + "," + idProvider + ")'><img alt='addUsefulLink' title='add a useful link' src='../images/glyphicons/glyphicons-433-plus.png' /></a>");
                        return;
                    }
                    var idProvider = data[0]["institution_id"];
                    for (i = 0; i < data.length; i++) {
                        var listItem = $("<li/>");
                        listItem.attr("data-id", data[i]["id"]);
                        listItem.attr("id", "item" + idDSA + "_" + data[i]["id"]);
                        //listItem.attr("title", "move this item up and down");
                        var logo = "";
                        if (data[i]["logo"]) {
                            logo = "<img src='" + data[i]["logo"] + "'  height='20'/>";
                        }
                        $(listItem).append(
                                "<img alt='move' alt='moveItem' title='move this item up and down' src='../images/glyphicons/glyphicons-187-move.png' />"

                                + " <input name='link_title[]' id='link_title" + data[i]["id"] + "' type='text' value='" + data[i]["title"] + "' class='short' list='link-categories'/>"
                                + " <datalist id='link-categories' class='link-categories'>" + $("#global-link-categories").html() + "</datalist>"
                                + " <div class='mini-logo'>"
                                + "<img src='" + data[i]["logo"] + "'/>"
                                + "</div>"

                                + " <input name='link_url[]' id='link_url" + data[i]["id"] + "' type='text' class='medium' value='" + data[i]["link"] + "'/>"
                                + " <a href='#' onclick='hideUsefulLink(" + idDSA + "," + data[i]["id"] + ")'>"
                                + " <img class='remove' alt='removeLink' title='remove this link' src='../images/glyphicons/glyphicons-208-remove-2.png' /></a>"
                                + " <div class='save' onclick='saveUsefulLink(" + idDSA + "," + data[i]["id"] + ")'><a href='#'><img alt='save' title='save this useful link' src='../images/glyphicons/glyphicons-415-disk-save.png' /></a></div>"
                                );
                        $("#useful-links" + idDSA + " ol").append(listItem);
                    }
                    $("#useful-links" + idDSA).append("<input type='hidden' name='ds_current_id' value='" + idDSA + "'/>");
                    $("#useful-links" + idDSA).append("<a href='#' onclick='addUsefulLink(" + idDSA + "," + idProvider + ")'><img alt='addUsefulLink' title='add a useful link' src='../images/glyphicons/glyphicons-433-plus.png' /></a>");
                    $("#useful-links" + idDSA + " ol").sortable({
                        axis: 'y',
                        start: function (event, ui) {
                            //console.log("starting");
                        },
                        update: function (event, ui) {
                            var data = $(this).sortable('serialize');
                            console.log("sorting: serialized data: " + data);
                            console.log("sorting: making persistant...");
                            $.ajax({
                                data: data + '&key=' + idDSA,
                                type: 'GET',
                                url: 'sortUsefulLinks.php'
                            })
                                    .fail(function () {
                                        console.log("sortUsefulLinks failed");
                                    })
                                    .always(function () {
                                        //console.log("always");
                                    })
                                    .done(function (data) {
                                        console.log("sortUsefulLinks done: " + data);
                                    });
                        }
                    });
                    console.log("<== getUsefulLinks");
                });
        return false;
    }

    /**
     * main function: gets all metadata of given provider
     * and populates the html form
     *
     * @param {integer} idProvider
     * @returns {boolean} false
     */
    function getAllMetadata(idProvider) {
        $.ajax({
            type: "GET",
            url: "../core/getProviderMainData.php?key=" + idProvider,
            dataType: "json"
        })
                .fail(function () {
                    console.log("getProviderMainData failed");
                })
                .always(function () {
                    //console.log("getProviderMainData finished");
                })
                .done(function (data) {
                    if (data.length == 0) {
                        console.log("no data, just display button ADD");
                        // empty DSA List
                        $("#DSAGroupDynamic ul").html("");
                    } else {
                        console.log("getProviderMainData done.");
                        console.log(data);
                        console.log(message);

                        $('#main-metadata table').show();
                        $('#pr_name_edit').val(data[0]["name"]);
                        $('#pr_shortname_edit').val(data[0]["shortname"]);
                        $('#pr_url_edit').val(data[0]["providerUrl"]);
                        $('#pr_pywrapper').val(data[0]["pywrapper"]);

                        $("#main-metadata .save").html(
                                "<a href='#' onclick='saveMainMetadata()'><img alt='save main metadata' title='save Main Metadata' src='../images/glyphicons/glyphicons-415-disk-save.png' /></a>"
                                );

                        $("#DSAGroupDynamic ul").html("");

                        for (i = 0; i < data.length; i++) {
                            if (i > 0 && data[i]["id"] == data[i - 1]["id"])
                                continue;

                            console.log(data[i]["id"] + ": " + data[i]["title"]);
                            console.log("preferred_landingpage: " + data[i]["preferred_landingpage"]);

                            // display current DSA
                            displayedTitle = data[i]["title_slug"].substring(0, 11);
                            var listItem = $("<li/>");
                            $(listItem).append("<a href='#dsa" + data[i]["id"] + "' "
                                    + "title='" + data[i]["title_slug"] + " - " + data[i]["title"] + "'>"
                                    + "<span>" + displayedTitle + "</span></a>");
                            $("#DSAGroupDynamic ul").append(listItem);

                            var collection = "<div id='dsa" + data[i]["id"] + "'" + " data-id='" + data[i]["id"] + "' " + (data[i]["active"] == 0 ? "class='inactive'" : "") + ">";

                            // ID
                            var myId = parseInt(data[i]["id"]);
                            collection += "<input name='ds[" + myId + "][id]' type='hidden' value='" + data[i]["id"] + "'/>";
                            collection += "<table>";

                            // Last Modified
                            collection += "<tr><td><td><label for='ds_lastaccess'>last edit: </label>";
                            collection += "<td class='small-info'>" + data[i]["timestamp"];

                            // Status
                            collection += "<tr><td><td><label for='ds_active'>Status: </label>";

                            collection += "<td><select id='ds_active" + data[i]["id"] + "' name='ds[" + myId + "][active]' >";
                            collection += "<option value='0' " + (data[i]["active"] == 0 ? "selected='selected'" : "") + ">inactive</option>";
                            collection += "<option value='1' " + (data[i]["active"] == 1 ? "selected='selected'" : "") + ">active</option>";
                            collection += "</select>";

                            // DataSource
                            collection += "<tr><td><td><label for='ds_accesspoint'>Data-Source: </label>";
                            collection += "<td><select id='ds_accesspoint" + data[i]["id"] + "' name='ds[" + myId + "][accesspoint]'/>";

                            // DataSource Full Title
                            collection += "<tr><td><td><label for='ds_title'>Title: </label>" + "<td><input id='ds_title" + data[i]["id"] + "'" + " name='ds[" + myId + "][title]' type='text' required='required'/>";

                            // URL
                            collection += "<tr><td><td><label for='ds_url'>URL:</label>";
                            collection += "<td><input id='ds_url" + data[i]["id"] + "'" + " name='ds[" + myId + "][url]' type='text' readonly='readonly'/>";

                            // DataSet
                            collection += "<tr><td><td><label for='ds_title_list'>Data Set: </label><td>";
                            collection += "<div id='ds_title_list" + data[i]["id"] + "'>" + spinner + "</div>";

                            // landingpage
                            var preferred_0 = (data[i]["preferred_landingpage"] == 0 ? "checked='checked'" : "");
                            var preferred_1 = (data[i]["preferred_landingpage"] == 1 ? "checked='checked'" : "");
                            // automatic
                            collection += "<tr><td><td colspan='2'>";
                            collection += "<label for='ds_landingpage_concept" + data[i]["id"] + "'>Landing-Page, automatically generated:</label>";
                            collection += "<tr>";
                            collection += "<td>";
                            collection += "<td>" + "<input id='ds_preferred_landingpage" + data[i]["id"] + "'"
                                    + " name='ds[" + myId + "][preferred_landingpage]' type='radio' value='0' " + preferred_0 + "/>";
                            collection += "<td id='ds_landingpage_concept" + data[i]["id"] + "'>";
                            collection += "<span class='small-info'>builds a pleasant teaser page</span>";
                            collection += "</td>";
                            // userdefined url
                            collection += "<tr><td><td colspan='2'>";
                            collection += "<label for='ds_landingpage_url" + data[i]["id"] + "'>Landing-Page, user-defined URL: </label>";
                            collection += "<tr>";
                            collection += "<td><td>" + "<input id='ds_preferred_landingpage" + data[i]["id"] + "'"
                                    + " name='ds[" + myId + "][preferred_landingpage]' type='radio' value='1'" + preferred_1 + "/>";
                            collection += "<td><input id='ds_landingpage_url" + data[i]["id"] + "'" + " name='ds[" + myId + "][landingpage_url]' value='" + data[i]["landingpage_url"] + "' type='text' />";

                            // filter
                            collection += "<tr><td colspan='3'><div id='collapsible_" + data[i]["id"] + "' class='collapsible' >";
                            collection += "<h6>advanced</h6>";
                            collection += "<div style='min-height: 120px;'>";
                            // @todo: query builder
                            collection += "<div id='query-builder" + data[i]["id"] + "' class='query-builder'></div>";
                            // filter list coming via ajax
                            collection += "<div id='ds-filter" + data[i]["id"] + "'></div>";
                            collection += "<div class='short-hint'>" + message.filterSyntax + "</div>";
                            collection += "<textarea id='ds_final_filter" + data[i]["id"] + "'" + " name='ds[" + myId + "][final_filter]'></textarea>";
                            collection += "</div></td></tr>";

                            collection += "<tr><td><td colspan='2'><fieldset class='useful-links' id='useful-links" + data[i]["id"] + "'><legend>Useful Links</legend>";
                            collection += "<div class='short-hint'>" + message.archiveFirst + "</div><ol></ol></div></tr>";

                            collection += "</table>";

                            // title and buttons on a single line
                            collection += "<div class='active-dsa'>";
                            // full title
                            collection += "<div class='active-dsa-title'>" + data[i]["title"] + "</div>";
                            // save
                            collection += "<div class='save' id='saveDSA" + data[i]["id"] + "'><a href='#'><img alt='saveDSAPoint' title='Save Data Source " + data[i]["title"] + "' src='../images/glyphicons/glyphicons-415-disk-save.png' /></a></div>";
                            // remove
                            collection += "<a href='#' class='active-dsa remove' onclick='hideDSA(" + data[i]["id"] + ")' title='Remove Data Source " + data[i]["title"] + "'>"
                                    + "<img class='remove' alt='hideThisDSA' src='../images/glyphicons/glyphicons-208-remove-2.png' /> " + "</a> ";
                            collection += "</div>";
                            collection += "</div>";

                            $("#DSAGroupDynamic").append(collection);

                            // fill in DSA Point
                            getDataSourceAccessPoints(data[i]["pywrapper"], data[i]["id"], data[i]["title_slug"], data[i]["dataset"]);

                            // trigger changes to the DSA Point
                            $('#ds_accesspoint' + data[i]["id"]).on("change", {datasource: data[i]}, function (event) {

                                var myData = event.data.datasource;
                                console.log(myData);
                                console.log("existing dataset was: " + myData.dataset);
                                console.log("==> begin trigger value change of ds_accesspoint" + myData.id);

                                console.log("set url to: " + myData.pywrapper + "/pywrapper.cgi?dsa=" + $(this).val());
                                $('#ds_url' + myData.id).val(myData.pywrapper + "/pywrapper.cgi?dsa=" + $(this).val());

                                // compute the list of all possible datasets
                                console.log("computing datasets for: " + myData.dataset);
                                getDataSetTitles(myData.id, $('#ds_url' + myData.id).val(), myData.dataset);

                                // set DSA point as default full title
                                if ($('#ds_title' + myData.id).val() === "") {
                                    $('#ds_title' + myData.id).val(myData.title);
                                }
                                console.log("set title to " + $('#ds_title' + myData.id).val());
                                console.log("<== end trigger DSA Point " + myData.id);
                            });
                            // trigger value of accesspoint
                            $('#ds_accesspoint' + data[i]["id"]).trigger("change", {datasource: data[i]});

                            // trigger SAVE button
                            $("#saveDSA" + data[i]["id"]).on("click", function () {

                                var myId = this.id.split("saveDSA")[1];
                                console.log("triggering saveDSA button for record " + myId);

                                var myDSA = new Object();
                                myDSA.id = myId;
                                myDSA.url = $("#ds_url" + myId).val();
                                myDSA.title = $("#ds_title" + myId).val();
                                myDSA.title_slug = $("#ds_accesspoint" + myId).val();
                                myDSA.dataset = $("#ds_title_list" + myId + " select").val();
                                myDSA.filter = $("#ds_final_filter" + myId).val();
                                myDSA.preferred_landingpage = $("#ds_preferred_landingpage" + myId + ":checked").val();
                                myDSA.landingpage_url = $("#ds_landingpage_url" + myId).val();
                                myDSA.active = $("#ds_active" + myId).val();

                                saveDSAPoint(myDSA);
                            });

                            $('#ds_url' + data[i]["id"]).html(data[i]["pywrapper"] + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + data[i]["id"]).val());
                            $('#ds_title' + data[i]["id"]).val(data[i]["title"]);
                            $('#ds_final_filter' + data[i]["id"]).val(decodeURI(decodeURIComponent(data[i]["filter"])));

                            // preparing filter
                            getCapabilities(idProvider, data[i]["id"], data[i]["title_slug"]);
                            // get all useful links
                            getUsefulLinks(idProvider, data[i]["id"]);

                            $("#collapsible_" + data[i]["id"]).accordion({collapsible: true, active: false});
                        }
                    }

                    // @todo: rather define an onclick-Handler;
                    $("#DSAGroupDynamic ul").append("<a href='#' onclick='addDSA(" + idProvider + ")'><img alt='addDSA' title='add a data source' src='../images/glyphicons/glyphicons-433-plus.png' style='padding-top:5px;'/></a>");

                    $("#DSAGroupDynamic").tabs();
                    $("#DSAGroupDynamic").tabs("refresh");
                }
                );
        return false;
    }

    $(document).tooltip({
        tooltipClass: "tooltip-biocase"
    });

    $("#footer-control a").on("click", function () {
        $("#footer").toggle("slow");
    });

});