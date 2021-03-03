
/* global message, userProvider, spinner, userName, userRights */

/**
 * BioCASe Monitor 2.1
 *
 * @copyright (C) 2013-2017 www.mfn-berlin.de
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
 * get list of providers
 *
 * @returns {boolean} false
 */
function getProviders() {
    $.ajax({
        type: "GET",
        url: "../services/providers/index.php",
        dataType: "json"
    })
            .fail(function () {
                console.log("getProviders failed");
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log(data);
                var result = "";
                result += "<option value='-1'>---</option>";
                for (var i = 0; i < data.length; i++) {
                    if (userProvider === data[i].provider_id || userRights === 31)
                        result += "<option value='" + data[i].provider_id + "'>" + data[i].provider_shortname + " &mdash; " + data[i].provider_name + "</option>";
                }
                $("#pr_name").html(result);

                if (userProvider !== "0")
                    $("#pr_name").val(userProvider);
                else
                    $("#pr_name").val("1");

                var selectedVal = parseInt($("#pr_name").val());
                if (selectedVal != -1) {
                    deleteOldValues();
                    getAllMetadata(selectedVal);
                    getCountConcepts(selectedVal);
                } else {
                    deleteOldValues();
                }

            });
    return false;
}


/**
 *  save Main Data of Provider
 *
 * @returns void
 */
function saveMainMetadata() {
    $.ajax({
        type: "POST",
        url: "../admin/updateMainMetadata.php",
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
                displaySystemMessage("Basic Metadata saved.", "success");
            });
}

/**
 *  get schemas via capabilities request
 *
 * @param {int} idProvider
 * @param {int} idDSA
 * @param {string} dsa
 * @param {string} selectedValue
 * @returns void
 */
function getSchemas(idProvider, idDSA, dsa, selectedValue) {
    $("#ds_schema" + idDSA).html(spinner);
    $.ajax({
        type: "GET",
        url: "../admin/getDatasourceSchema.php",
        dataType: "json",
        data: {"provider": idProvider, "dsa": idDSA}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getDatasourceSchema failed");
                console.log(textStatus + ": " + errorThrown);
            })
            .always(function () {
                //console.log("getDatasourceSchemas finished");              
            })
            .done(function (data) {
                myDSA = data;

                $.ajax({
                    type: "GET",
                    url: "../services/capabilities/index.php",
                    dataType: "json",
                    data: {"provider": idProvider, "dsa": dsa}
                })
                        .fail(function (jqXHR, textStatus, errorThrown) {
                            console.log("getSchemas via getCapabilities failed");
                            console.log(textStatus + ": " + errorThrown);
                        })
                        .always(function () {
                            //console.log("getSchemas finished");
                        })
                        .done(function (data) {
                            console.log("getSchemas via getCapabilities done.");
                            console.log(data);
                            if (data.schemas) {
                                $('#ds_schema' + idDSA).html("");
                                $('#ds_schema' + idDSA).append("<option value=''>---</option>");
                                var selected = "";
                                for (var j = 1; j < data.schemas.length; j++) {
                                    if (data.schemas[j] === myDSA.schema)
                                        selected = " selected='selected'";
                                    else
                                        selected = " ";
                                    $('#ds_schema' + idDSA).append("<option " + selected + ">" + data.schemas[j] + "</option>");
                                }
                                $('#ds_schema' + idDSA + ' select').on("change", function () {
                                    var selectedVal = $(this).val();
                                    console.log("schema changed to selected option: " + selectedVal);
                                    $('#ds_schema' + idDSA).val(selectedVal);
                                 });
                            }
                        });
            });
}



/**
 *  get Data Source Access (DSA) Points and DataSet Titles
 *
 * @param {int} idProvider
 * @param {string} url complete Query URL incl. ?dsa=xxx
 * @param {int} idDSA
 * @param {string} selectedValue  previoulsy selected DataSet
 * @param {string} dataSet
 * @returns void
 */
function getDataSourceAccessPoints(idProvider, url, idDSA, selectedValue, dataSet) {
    console.log("calling getDSA with parameters: ");
    console.log(idProvider + "/ url=" + url + "/ dsa=" + idDSA + "/ selectedValue=" + selectedValue + "/ dataset=" + dataSet);
    $("#ds_accesspoint" + idDSA).html(spinner);
    $.ajax({
        type: "GET",
        url: "../admin/getDataSources.php",
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
                console.log(data);

                var items = "";
                $.each(data, function (i, el) {
                    //console.log(el);
                    items += "<option value='" + el + "'";
                    if (el === selectedValue) {
                        items += " selected='selected'";
                    }
                    items += ">" + el + "</option>";
                });
                $("#ds_accesspoint" + idDSA).html(items);

                //console.log("filling in URL: " + url + "/pywrapper.cgi?dsa=" + selectedValue);
                $('#ds_url' + idDSA).val(url + "/pywrapper.cgi?dsa=" + selectedValue);

                console.log("getting Datasets for " + url + "/pywrapper.cgi?dsa=" + selectedValue);
                getDataSetTitles(idDSA, url + "/pywrapper.cgi?dsa=" + selectedValue, dataSet);

                //console.log("getting Schemas for dsa=" + idDSA + " and provider=" + idProvider);
                getSchemas(idProvider, idDSA, selectedValue, "");
            });
}

/**
 *  get Dataset titles
 *
 * @param {int} idDSA
 * @param {string} url complete Query URL incl. ?dsa=xxx
 * @param {int} idDSA
 * @param {string} dataSet
 * @returns void
 */
function getDataSetTitles(idDSA, url, dataset) {
    $("#ds_title_list" + idDSA).html(spinner);
    var dsa=url.split("dsa=")[1];
    $.ajax({
        type: "GET",
        url: "../admin/getDataSetTitles.php",
        dataType: "json",
        data: {"url": url, "idDSA": dsa}
    })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("getDataSetTitles failed" + errorThrown);
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log("getDataSetTitles done: id=" + idDSA + " dsa=" + dsa + " url=" + url);
                console.log(data);

                var cssClass = data[0];
				var defaultFilter = [""];
                var selectbox = "<select class='" + cssClass + "'>";
                selectbox += "<option>" + dataset + "</option>";
                for (var i = 0; i < data.length; i++) {
                    selectbox += "<option";

                    if (data[i].trim() === dataset) {
                        selectbox += " selected='selected'";
                    }
                    selectbox += ">" + data[i].trim() + "</option>";
					// remember list of potential filters to check on modifications
					defaultFilter.push('<like path="/DataSets/DataSet/Metadata/Description/Representation/Title">' + data[i].trim() + '</like>');
                }
                selectbox += "</select>";
                $("#ds_title_list" + idDSA).html(selectbox);

                //var defaultFilter = '<like path="/DataSets/DataSet/Metadata/Description/Representation/Title">' + $("#ds_title" + idDSA).val() + '</like>';
                //$('#ds_final_filter' + idDSA).val(defaultFilter);
				
                // trigger to change final filter 
                $('#ds_title_list' + idDSA + ' select').on("change", function () {
					//console.log("selected dataset for idDSA=" + idDSA + ": " + $(this).val());
					
					// if not dataset is selected filter is empty by default
					var newFilter = '';
					if($(this).val()!="" && $(this).val()!="---")
						newFilter = '<like path="/DataSets/DataSet/Metadata/Description/Representation/Title">' + $(this).val() + '</like>';
					
					/*console.log("currentFilter: "+$('#ds_final_filter' + idDSA).val());
					console.log("newFilter: "+newFilter);
					console.log("defaultFilter: ");	console.log(defaultFilter);*/
					
					// check on modifications in the filter field
					if(	defaultFilter.includes($('#ds_final_filter' + idDSA).val()) 
						|| $('#ds_final_filter' + idDSA).val()==newFilter 
						|| $('#ds_final_filter' + idDSA).val()==""
					   )
					{
						$('#ds_final_filter' + idDSA).val( newFilter );
					}else 
					{
						$('#query-builder' + idDSA).collapse('show');
						if(confirm("The filter of this dataset was modified earlier. Do you want to set it to the default value?"))
							$('#ds_final_filter' + idDSA).val( newFilter );
					}
                });
            });
}


/**
 * creates new DSA record
 *
 * @param {int} idProvider
 * @returns {boolean} false
 */
function addDSA(idProvider) {
    var waitMessage = "new Data Accesss Point being created.<br/>Please wait a few moments..." + spinner;
    $("#system-message").html(waitMessage);
    $("#system-message").show();
    $.ajax({
        type: "POST",
        url: "../admin/addDSA.php",
        data: {"key": idProvider},
        dataType: "json"
    })
            .fail(function () {
                console.log("addDSA failed");
            })
            .always(function () {
                //console.log("addDSA: finished");
                $("#system-message").hide();
            })
            .done(function (data) {
                $("#system-message").hide();
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

                collection += "<table class='table table-condensed'>";

                // Status
                collection += "<tr><td><label for='ds_status'>Status: </label>";
                collection += "<td><select id='ds_active" + newId + "' name='ds[" + newId + "][active]' >";
                collection += "<option value='0' selected='selected'>inactive</option>";
                collection += "<option value='1'>active</option>";
                collection += "</select>";

                // alternative pywrapper on dataset level
                collection += "<tr><td><label for='ds_pywrapper'>alternative BioCASe URL:</label>";
                collection += "<td><input id='ds_pywrapper" + newId + "' name='ds[" + newId + "][pywrapper]' type='text' value=''/>";

                // DataSource
                collection += "<tr><td><label for='ds_accesspoint'>Data-Source: </label>";
                collection += "<td><select id='ds_accesspoint" + newId + "' name='ds[" + newId + "][accesspoint]'/>";

                // DataSource Full Title
                collection += "<tr><td><label for='ds_title'>Title: </label>";
                collection += "<td><input id='ds_title" + newId + "' name='ds[" + newId + "][title]' type='text' required='required'/>";

                // URL
                collection += "<tr><td><label for='ds_url'>URL:</label>";
                collection += "<td><input id='ds_url" + newId + "' name='ds[" + newId + "][url]' type='text' readonly='readonly'/>";

                // DataSet
                collection += "<tr><td><label for='ds_title_list'>Data Set: </label>";
                collection += "<td><div id='ds_title_list" + newId + "'>" + spinner + "</div>";

                // Schema
                collection += "<tr><td><label for='ds_schema'>Schema: </label>";
                collection += "<td><select id='ds_schema" + newId + "' name='ds[" + newId + "][schema]'/>";

                // Landingpage
                var preferred_0 = "checked='checked'";
                var preferred_1 = "";

                collection += "<tr><td colspan='1'>";
                collection += "<label>Landing-Page:</label></td>";

                collection += "<td>";
                collection += "<input id='ds_preferred_landingpage" + newId + "'"
                        + " name='ds[" + newId + "][preferred_landingpage]' type='radio' value='0'" + preferred_0 + "/>";
                collection += "<label> automatic</label>   ";

                collection += "<input id='ds_preferred_landingpage" + newId + "'"
                        + " name='ds[" + newId + "][preferred_landingpage]' type='radio' value='1'" + preferred_1 + "/>";
                collection += "<label for='ds_landingpage_url" + newId + "'> user-defined:</label></tr>";

                collection += "<tr>";
                collection += "<td></td>";
                collection += "<td><input id='ds_landingpage_url" + newId + "'" + " name='ds[" + newId + "][landingpage_url]' value='' type='text' />";

                // filter
                collection += "<tr><td colspan='2'>";
//                collection += "<div id='collapsible_" + newId + "' class='collapsible' >";
//                collection += "<a data-toggle='collapse' data-target='ds-filter" + +newId + "'> advanced</a>";
//                collection += "<div style='min-height: 120px;'>";
                // @todo: query builder
                collection += "<div id='query-builder" + newId + "' class='query-builder collapse' style='min-height:120px;'>";
                // list of capabilities for constructing the filter obtained via ajax
                collection += "<label>final filter:</label> <span class='short-hint'>" + message.filterSyntax + "</span>";
                collection += "<div id='ds-filter" + newId + "' style='display:none'></div>";
                collection += "<textarea id='ds_final_filter" + newId + "'" + " name='ds[" + newId + "][final_filter]'></textarea>";
                collection += "</div>";
//                collection += "</div></div>";

                // filter list coming via ajax
//                collection += "<div id='ds-filter" + newId + "'></div><label for='ds_final_filter" + newId + "'>final filter: </label>";
//                collection += "<div class='short-hint'>" + message.filterSyntax + "</div>";
//                collection += "<textarea id='ds_final_filter" + newId + "'" + " name='ds[" + newId + "][final_filter]'></textarea>";
//                //collection += "</div>";
                collection += "</td></tr>";

//                // archives
//                collection += "<tr><td colspan='2'>";
//                collection += "<div class='grouped'>";
//                collection += "<h4><a data-toggle='collapse' href='#archives" + newId + "'>";
//                collection += "<span class='glyphicon glyphicon-triangle-bottom'></span>";
//                collection += "<span class='glyphicon glyphicon-triangle-right'></span>";
//                collection += "&nbsp;&nbsp;Archives</a></h4>";
//                collection += "<div id='archives" + newId + "' class='archives collapse'>";
//                collection += "<ol></ol>";
//                collection += "</div></div>";
//                collection += "</td></tr>";
//
//                // useful links
//                collection += "<tr><td colspan='2'>";
//                collection += "<div class='grouped'>";
//                collection += "<h4><a data-toggle='collapse' href='#useful-links" + newId + "'>";
//                collection += "<span class='glyphicon glyphicon-triangle-bottom'></span>";
//                collection += "<span class='glyphicon glyphicon-triangle-right'></span>";
//                collection += "&nbsp;&nbsp;Useful Links</a></h4>";
//                collection += "<div id='useful-links" + newId + "' class='useful-links collapse'>";
//                collection += "<ol></ol>";
//                collection += "</div></div>";
//                collection += "</td></tr>";

                collection += "</table><br/>";

                // TITLE AND BUTTONS on a single line
                collection += "<div class='active-dsa'>";
                // full title
                collection += "<div class='active-dsa-title'>" + "please type in the full title" + "</div>";
                collection += "</div>";
                // buttons
                collection += "<div style='float:right'>";
                // remove
                collection += " <a href='#' class='btn btn-danger btn-lg' onclick='hideDSA(" + newId + ")'>"
                        + "<span class='glyphicon glyphicon-remove' title='Remove this Data Source'/></a>";
                // save
                collection += " <a href='#' class='btn btn-success btn-lg'><span id='saveDSA" + newId + "'>"
                        + "<span class='glyphicon glyphicon-save' title='Save this Data Source' /></a>";
                collection += "</div>";
                collection += "</div>";



                $("#DSAGroupDynamic").append(collection);
                // refresh the tabs and activate the last one
                $("#DSAGroupDynamic").tabs();
                $("#DSAGroupDynamic").tabs("refresh");
                $("#DSAGroupDynamic").tabs("option", "active", $("#DSAGroupDynamic ul li").length - 1);

                console.log("Form built, new tab created and activated, tabs refreshed");
                console.log(($("#DSAGroupDynamic ul li").length - 1) + "th tab selected");

                // trigger changes of status (active/inactive)
                $('#ds_active' + newId).on("change", {datasource: data}, function (event) {
                    if ($(this).val() === "1")
                        $('#dsa' + newId).addClass('active');
                    else
                        $('#dsa' + newId).addClass('inactive');
                });
                // trigger value of status
                $('#ds_active' + newId).trigger("change", {datasource: data});

                // trigger changes of full title
                $('#ds_title' + newId).on("change", {datasource: data}, function (event) {
                    $('#dsa' + newId + ' div.active-dsa-title').text($(this).val());
                });
                // trigger value of full title
                $('#ds_title' + newId).trigger("change", {datasource: data});

                // trigger changes of alternative pywrapper
                $('#ds_pywrapper' + newId).on("change", {datasource: data}, function (event) {

                    var myData = event.data.datasource;
                    console.log("==> begin trigger value change of DS_PYWRAPPER" + myData.id);

                    console.log("general pywrapper: " + $('#ds_url' + myData.id).val());
                    console.log("alternative pywrapper: " + $(this).val());

                    if ($(this).val().trim().length > 0) {
                        console.log("this.val: " + $(this).val());
                        console.log("set url to alternative pywrapper: " + $(this).val() + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + myData.id).val());
                        $('#ds_url' + myData.id).val($(this).val() + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + myData.id).val());
                    } else {
                        console.log("rollback to general pywrapper: " + $('#pr_pywrapper').val());
                        $('#ds_url' + myData.id).val($('#pr_pywrapper').val() + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + myData.id).val());
                    }

                    var myPywrapper = $(this).val() ? $(this).val() : myData.pywrapper;

                    // compute the list of all possible Access Points
                    getDataSourceAccessPoints(idProvider, myPywrapper, myData.id, myData.title_slug, myData.dataset);

                    // recompute the list of all possible datasets
                    getDataSetTitles(myData.id, $('#ds_url' + myData.id).val(), myData.dataset);

                    console.log("computing schemas for: " + myData.id);
                    console.log("idProvider=" + idProvider + " idDSA=" + myData.id);
                    getSchemas(idProvider, myData.id, $('#ds_accesspoint' + myData.id).val(), myData.schema);

                    console.log("<== end trigger pywrapper " + myData.id);
                });
                // trigger value of alternative pywrapper
                $('#ds_pywrapper' + newId).trigger("change", {datasource: data});


                // fill in DSA Point
                getDataSourceAccessPoints(idProvider, data.url, newId, "", "");
                // trigger changes to the DSA Point
                $('#ds_accesspoint' + newId).on("change", {datasource: data}, function (event) {

                    var myData = event.data.datasource;
                    console.log(myData);
                    console.log("set url to: " + myData.url + "/pywrapper.cgi?dsa=" + $(this).val());
                    $('#ds_url' + myData.id).val(myData.url + "/pywrapper.cgi?dsa=" + $(this).val());

                    // compute the list of all possible datasets
                    getDataSetTitles(myData.id, $('#ds_url' + myData.id).val(), "");

                    // get Schemas
                    var schema = getSchemas(idProvider, myData.id, $(this).val(), "");
                    $('#ds_schema' + myData.id).append("<option>" + schema + "</option>");

                    // set DSA point as default full title
                    if ($('#ds_title' + myData.id).val() === "") {
                        $('#ds_title' + myData.id).val(myData.title);
                    }
                });
                // trigger value of accesspoint
                $('#ds_accesspoint' + newId).trigger("change", {datasource: data});


                $('#ds_url' + newId).html(newPywrapper + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + newId).val());


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
					console.log("Filter: "); console.log(myDSA.filter);
                    myDSA.schema = $("#ds_schema" + newId).val();
                    myDSA.active = $("#ds_active" + newId).val();
                    myDSA.landingpage_url = "";
                    myDSA.preferred_landingpage = 0;

                    console.log(myDSA);
                    saveDSAPoint(myDSA, true);
                    
                   

                });

                $("#collapsible_" + newId).accordion({collapsible: true, active: false});

                displaySystemMessage("new Data Accesss Point created.", "success");
                return false;
            });
}


/**
 * stores a given DSA in the database
 *
 * @param {object} dsa object holding all fields
 * @param {boolean} reload page?
 * @returns void
 */
function saveDSAPoint(dsa, reload) {
    console.log("calling save DSA");
    console.log(dsa);
    $.ajax({
        type: "POST",
        url: "../admin/updateDSA.php",
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
                console.log(dsa);
                var record = dsa;
                var msg = record.title;

                // current tab
                if (record.title) {
                    $("#DSAGroupDynamic ul li a[href=#dsa" + record.id + "] span").html(record.title.substring(0, 12));
                } else {
                    $("#DSAGroupDynamic ul li a[href=#dsa" + record.id + "] span").html('--');
                }
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

                displaySystemMessage("DSA Point saved: <br/><br/>" + msg, "success");
                console.log("DSA Point saved: " + msg);
                
                if (reload) location.reload();
            });

}

function showDSA() {
    $("#DSAGroupDynamic li[aria-selected='true']").show();
    $("#DSAGroupDynamic div[aria-hidden='false']").show();
    $("#system-message").removeClass("modal");
    displaySystemMessage("Removal of group has been cancelled.", "info");

}

function hideDSA(id) {
    $("#DSAGroupDynamic li[aria-selected='true']").hide();
    $("#dsa" + id).hide();
    // on confirm deletion, remove DOM node
    $("#system-message").addClass("modal");
    $("#system-message").html("<p>DSA temporarily removed.</p><p>Please <a href='#' onclick='showDSA()'>&nbsp;undo&nbsp;</a> the operation or <a href='#' onclick='removeDSA(" + id + ")'>&nbsp;confirm&nbsp;</a> the removal.</p>");
    $("#system-message").show();
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
        url: "../admin/removeDSA.php",
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
                displaySystemMessage("DSA removed", "success");
            });
}

/**
 * adds a Count Concept
 * @todo: attach count concepts to each DSA, since dDSAs may have distinct schemas
 * @returns void
 */
function addCount() {
    var listItem = $("<li/>");
    listItem.attr("data-id", 0);
    $(listItem).append(
            "<span class='glyphicon glyphicon-move' title='move this item up and down'/>"

            + "<input type='hidden' name='countId[]' value='0'/>"

            + "<input type='text' name='xpath[]' class='large' placeholder='type in an xpath, like /DataSets/DataSet/Units/Unit/UnitID'/>"

            + "<select class='medium' name='specifier[]' multiple='multiple' size='3'>"
            + "<option value='1'>total</option>"
            + "<option value='2'>distinct</option>"
            + "<option value='4'>dropped</option>"
            + "</select>"

            + " <a href='#' class='btn btn-danger btn-sm' onclick='removeCount(0)'>"
            + "<span class='glyphicon glyphicon-remove' title='remove this count concept'/></a>"

            + " <a href='#' class='btn btn-success btn-sm' onclick='saveCount(0)'>"
            + "<span class='glyphicon glyphicon-save' title='save this count concept'/></a>"
            );
    $(listItem).find("input[type=text]").bootcomplete({
        url: "../admin/getAllCountConcepts.php",
        method: "get",
        idFieldName: "xpath",
        minLength: 2,
        dataParams: {schema: "ABCD2.06"}
        //dataParams: {schema : allSchemas[0]}
    });

//    $(listItem).find("input").autocomplete({
//        // @todo  take the schema attached to the DSA workaround
//        source: "../admin/getAllCountConcepts.php,
//        minLength: 2
//    });

    $("#count-concepts ul").append(listItem);
    $.ajax({
        type: "POST",
        url: "../admin/addCount.php",
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
        url: "../admin/removeCount.php?key=" + id,
        dataType: "text"
    })
            .fail(function () {
                console.log("removeCount failed");
            })
            .always(function () {

            })
            .done(function (data) {
                console.log("removeCount:  DB persistance..." + data);
                displaySystemMessage("CountConcept removed.<br/>Recovering impossible. ", "success");

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
    data["xpath"] = $("#count-concepts-list li[data-id=" + id + "]").find("input[name*=xpath]").val();
    data["specifier"] = $("#count-concepts-list li[data-id=" + id + "]").find("select[name*=specifier]").val();
    console.log(data);
    $.ajax({
        type: "POST",
        url: "../admin/updateCountConcept.php",
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
                displaySystemMessage("Count Concept updated. ", "success");
            });
}


/**
 * adds an Archive
 *
 * @param {int} idDSA  ID of Data Source Access Point
 * @param {int} idProvider ID of Data Center
 * @returns void
 */
function addArchive(idDSA, idProvider) {
    $.ajax({
        type: "POST",
        url: "../admin/addArchive.php",
        data: {"idDSA": idDSA, "idProvider": idProvider},
        dataType: "json"
    })
            .fail(function () {
                console.log("addArchive failed");
            })
            .always(function () {
                //console.log("finished");
            })
            .done(function (data) {
                console.log("addArchive: idDSA=" + idDSA + " idProvider=" + idProvider);
                console.log(data);
                var newId = data.id;
                var listItem = $("<li/>");
		var countItems = $(".is_latest").length;
		console.log("count is latest: "+countItems);
                listItem.attr("data-id", data.id); // primary key
                listItem.attr("id", "item" + idDSA + "_" + data.id); // primary key
                listItem.attr("title", "move this item up and down");
                $(listItem).append(
                        "<span class='glyphicon glyphicon-move' title='move this item up and down'/>"

                        + "<div class='small-explanation'>"
                        + "<label>is latest:</label><input name='is_latest" + idDSA + "[]' class='is_latest' id='is_latest" + newId + "' type='radio' "+( countItems <1 ? " checked = 'checked' " : "" )+" />"
                        + "</div>"

                        + "<input name='archive_url[]' id='archive_url" + newId + "' type='text' class='large' value=''/>"

                        + " <a href='#' class='btn btn-danger btn-sm' onclick='hideArchive(" + idDSA + "," + newId + "," + $("#archives" + idDSA + " ol").children().length + ")'>"
                        + "<span class='glyphicon glyphicon-remove' title='remove this archive'/></a>"

                        + " <a href='#' class='btn btn-success btn-sm' onclick='saveArchive(" + idProvider + "," + idDSA + "," + newId + ")'>"
                        + "<span class='glyphicon glyphicon-save' title='save this archive' /></a>"
                        );
                $("#archives" + idDSA + " > ol").append(listItem);
                //$("#archives" + idDSA + " ol").sortable("refresh");
                //$("#archives" + idDSA + " > ol").sortable();
            });
}

/**
 * saves an Archive
 *
 * @param {integer} idProvider  ID of Provider
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Archive
 * @returns void
 */
function saveArchive(idProvider, idDSA, id) {
    var data = {};
    data["provider"] = idProvider;
    data["dsa"] = idDSA;
    data["id"] = id;
    data["is_latest"] = ($("#is_latest" + id).val() ? 1 : 0);
    data["link"] = $("#archive_url" + id).val();
    console.log("now saving archive " + id);
    console.log(data);
    $.ajax({
        type: "POST",
        url: "../admin/updateArchive.php",
        data: data
    })
            .fail(function () {
                console.log("saveArchive failed");
            })
            .always(function () {
                //console.log("saveArchive finished");

            })
            .done(function (data) {
                console.log("updateArchive " + id + " done.");
                console.log(data);
                console.log(data[0]);
                if (data[0] == "error") {
                    console.log("oh, ERROR ");
                    displaySystemMessage("Update Error for Archive " + id, "danger");
                    return;
                }
                $("#archives" + idDSA + " li[data-id=" + id + "]").find("input[name*=archive_url]").removeClass("unsaved");
                $("#archives" + idDSA + " li[data-id=" + id + "]").find("input[name*=archive_url]").addClass("saved");
                displaySystemMessage("Archive " + id + " updated: <br/>" + $("#archive_url" + id).val(), "success");
            });
}


/**
 * hides an Archive
 *
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Archive
 * @param {integer} counter  list item counter
 * @returns void
 */
function hideArchive(idDSA, id, counter) {
    $("#archives" + idDSA + " ol li[data-id=" + counter + "]").hide();
    $("#system-message").html("<p>Archive temporarily removed.</p> \
        <p>Please <a onclick='showArchive(" + idDSA + "," + id + ")'>undo</a> the operation \n\
        or <a onclick='removeArchive(" + idDSA + "," + id + ")'>confirm</a> the removal.</p>");
    $("#system-message").addClass("warning");
    $("#system-message").fadeIn();
}

/**
 * shows an Archive
 *
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Archive
 * @returns void
 */
function showArchive(idDSA, id) {
    $("#archives" + idDSA + " ol li[data-id=" + id + "]").show();
    $("#system-message").fadeOut();
}

/**
 * removes an Archive
 *
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Archive
 * @returns void
 */
function removeArchive(idDSA, id) {
    $("#archives" + idDSA + " ol li[data-id=" + id + "]").remove();
    $.ajax({
        type: "POST",
        url: "../admin/removeArchive.php",
        data: {"idDSA": idDSA, "id": id},
        dataType: "text"
    })
            .fail(function () {
                console.log("removeArchive failed");
                displaySystemMessage("Removing of Archive failed.", "danger");
            })
            .always(function () {

            })
            .done(function (data) {
                console.log("removeArchive:  DB persistance: " + data);
                displaySystemMessage("Archive removed.<br/>Recovering impossible.", "success");
            });
}

/**
 * adds a Useful Link
 *
 * @param {int} idDSA  ID of Data Source Access Point
 * @param {int} idProvider ID of Data Center
 * @returns void
 */
function addUsefulLink(idDSA, idProvider) {
    $.ajax({
        type: "POST",
        url: "../admin/addUsefulLink.php",
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
                var listItem = $("<li/>");
                listItem.attr("data-id", data.id); // primary key
                listItem.attr("id", "item" + idDSA + "_" + data.id); // primary key
                listItem.attr("title", "move this item up and down");
                $(listItem).append(
                        "<span class='glyphicon glyphicon-move' title='move this item up and down'/>"
                        + " <input id='link-title" + newId + "' name='link_title[]' type='text' value='' placeholder='title' class='short' list='link-categories'/>"
                        + "<datalist id='link-categories' class='link-categories'>"
                        + $("#global-link-categories").html()
                        + "</datalist>"

                        + "<div class='mini-logo'><img alt='logo' src='../images/GFBio_logo.png'></div>"
                        + " <input id='link" + newId + "' name='link_url[]' type='text' class='large' value='' placeholder='type in an URL'/>"

                        + " <a href='#' class='btn btn-danger btn-sm' onclick='hideUsefulLink(" + idDSA + "," + newId + ")'>"
                        + "<span class='glyphicon glyphicon-remove' title='remove this link'/></a>"

                        + " <a href='#' class='btn btn-success btn-sm' onclick='saveUsefulLink(" + idProvider + "," + idDSA + "," + newId + ")'>"
                        + "<span class='glyphicon glyphicon-save' title='save this link'/></a>"
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
    $("#system-message").html("<p>UsefulLink temporarily removed.</p> \
        <p>Please <a onclick='showUsefulLink(" + idDSA + "," + id + ")'>undo</a> the operation \n\
        or <a onclick='removeUsefulLink(" + idDSA + "," + id + ")'>confirm</a> the removal.</p>");
    $("#system-message").addClass("warning");
    $("#system-message").fadeIn();
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
    $.ajax({
        type: "POST",
        url: "../admin/removeUsefulLink.php",
        data: {"idDSA": idDSA, "id": id},
        dataType: "text"
    })
            .fail(function () {
                console.log("removeUsefulLink failed");
                displaySystemMessage("Removing of Useful Link failed.", "danger");
            })
            .always(function () {

            })
            .done(function (data) {
                console.log("removeUsefulLink:  DB persistance: " + data);
                displaySystemMessage("UsefulLink removed.<br/>Recovering impossible.", "success");
            });
}

/**
 * saves a Useful Link
 *
 * @param {integer} idProvider  ID of Provider
 * @param {integer} idDSA  ID of Data Source Access Point
 * @param {integer} id  ID of Useful Link
 * @returns void
 */
function saveUsefulLink(idProvider, idDSA, id) {
    var data = {};
    data["provider"] = idProvider;
    data["id"] = id;
    data["title"] = $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_title]").val();
    data["is_latest"] = $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=is_latest]").is(":checked");
    data["link"] = $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_url]").val();
    console.log("now saving useful link " + id);
    console.log(data);
    $.ajax({
        type: "POST",
        url: "../admin/updateUsefulLink.php",
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
                console.log(linkdata);
                $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_title]").removeClass("unsaved");
                $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_title]").addClass("saved");
                $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_url]").removeClass("unsaved");
                $("#useful-links" + idDSA + " li[data-id=" + id + "]").find("input[name*=link_url]").addClass("saved");
                if (data.length > 0) {
                    $("#useful-links" + idDSA + " li[data-id=" + id + "] span").html("<img src='" + linkdata + "' height='20'/>");
                }
                displaySystemMessage("Useful Link " + id + " updated.", "success");
            });
}

/**
 * get all link categories 
 *
 * @returns {void} 
 */
function getLinkCategories() {
    $.ajax({
        type: "GET",
        url: "../admin/getLinkCategories.php",
        dataType: "json"
    })
            .fail(function () {
                console.log("getLinkCategories failed");
            })
            .always(function () {
                //console.log("getLinkCategories finished");

            })
            .done(function (data) {
                console.log("getLinkCategories done");
                console.log(data);

                var result = "";
                var resultlist = ""
                var i;
                for (i = 0; i < data.length; i++) {
                    result += "<option data-thumbnail='../images/" + data[i].logofile + "'>" + data[i].name + "</option>";
                    resultlist += "<li><a href='#'><img width='30' src='../images/" + data[i].logofile + "'>" + data[i].name + "</a></li>";
                }
                $("#global-link-categories").html(result);
                console.log(result);
                linkCategories = result;
                //console.log(resultlist);
                //linkCategories = resultlist;
            });
}


/*************************************************/




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
 * get the number of CountConcepts for a given provider
 *
 * @param {int} idProvider
 * @returns {boolean} false
 */
function getCountConcepts(idProvider) {
	console.log("getCountConcepts()");
	
    $.ajax({
        type: "GET",
        url: "../admin/getCountConcepts.php?key=" + idProvider,
        dataType: "json"
    })
            .fail(function () {
                console.log("getCountConcepts failed");
            })
            .always(function () {
                //console.log("finished");


            })
            .done(function (data) {
                console.log("getCountConcepts:");
                console.log(data);
                for (var i = 0; i < data.length; i++) {
                    var listItem = $("<li/>");
                    listItem.attr("data-id", data[i]["id"]);
                    $(listItem).append(
                            "<span class='glyphicon glyphicon-move' title='move this item up and down'/>"

                            + "<input type='hidden' name='countId[]' value='" + data[i]["id"] + "'/>"
                            + "<input id='xpath_" + data[i]["id"] + "' name='xpath[]' type='text' class='large' value='" + data[i]["xpath"] + "'/>"
                            + "<select class='medium' id='specifier_" + data[i]["id"] + "' name='specifier[]' multiple='multiple' size='3'>"
                            + "<option value='1' " + (((data[i]["specifier"] & 1) == 1) ? "selected" : "") + ">total</option>"
                            + "<option value='2' " + (((data[i]["specifier"] & 2) == 2) ? "selected" : "") + ">distinct</option>"
                            + "<option value='4' " + (((data[i]["specifier"] & 4) == 4) ? "selected" : "") + ">dropped</option>"
                            + "</select>"

                            + " <a href='#' class='btn btn-danger btn-sm' onclick='removeCount(" + data[i]["id"] + ")'>"
                            + "<span class='glyphicon glyphicon-remove' title='remove this count concept'/></a>"

                            + " <a href='#' class='btn btn-success btn-sm' onclick='saveCount(" + data[i]["id"] + ")'>"
                            + "<span class='glyphicon glyphicon-save' title='save this count concept'/></a>"
                            );
                    $("#count-concepts ul").append(listItem);

// @todo horrible workaround to use the constant ABCD2.06. we should attach count concepts to each DSA
                    $("#xpath_" + data[i]["id"]).bootcomplete({
                        url: "../admin/getAllCountConcepts.php",
                        method: "get",
                        minLength: 2,
                        dataParams: {schema: "ABCD2.06"}
                    });

//                    $("#xpath_" + data[i]["id"]).autocomplete({
//                        source: "../admin/getAllCountConcepts.php",
//                        minLength: 2
//                    });

                    $("#xpath_" + data[i]["id"]).on("change", function () {
                        //console.log("editing current listitem ");
                        $(this).addClass("unsaved");
                    });
                    $("#specifier_" + data[i]["id"]).on("change", function () {
                        //console.log("changed value in dropdown list ");
                        $(this).addClass("unsaved");
                    });
                }
                var addConceptButton = "<a class='btn btn-info btn-md' href='#count-concepts' onclick='addCount()'>"
                        + "<span class='glyphicon glyphicon-plus-sign' title='add a count concept'/> add a count concept</a>";
                $("#count-concepts-content").append(addConceptButton);

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
                            url: '../admin/sortCountConcepts.php'
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
 * @param {int} provider
 * @param {int} idDSA
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
                    console.log("capabilities done, rendering html for use with advanced filter, dsa=" + idDSA);
                    var jsondata = JSON.parse(data);
                    console.log(jsondata);
                    var selectbox = "<select>";
                    if (jsondata.concepts != undefined) {
                        for (var i = 1; i < jsondata.concepts.length; i++) {
                            selectbox += "<option>" + jsondata.concepts[i].dataset + "</option>";
                        }
                    }
                    selectbox += "</select>";
                    $("#ds-filter" + idDSA).html(selectbox);


                    // build a cache in sitio:
                    $("#all-filters").append("<section class='cache' data-id='" + idDSA + "'>" + selectbox + "</section>");

                    $('#ds-filter' + idDSA + ' select').on("change", function () {
                        var selectedVal = $(this).val();
                        var currentFilter = $('#ds_final_filter' + idDSA).val();
                        // always keep the default filter, since the query builder is not yet implemented
                       // $('#ds_final_filter' + idDSA).val(currentFilter + " <like path='/DataSets/DataSet/Metadata/Description/Representation/Title'>" + selectedVal + "</like>");
                        //$('#ds_title' + idDSA + " select").val("");
                    });

                });
    } else {
        console.log("getCapabilities from cache, for dsa=" + idDSA);
        //$("#ds-filter" + idDSA).html(filters);
    }
    return false;
}

/**
 * gets the XML archives for a given DSA
 *
 * @param {int} idProvider
 * @param {int} idDSA
 * @returns {boolean} false
 */
function getArchives(idProvider, idDSA) {
	console.log("getArchives()");
	
	$.ajax({
        type: "GET",
        url: "../services/xml-archives/index.php?dataset_id=" + idDSA + "&provider_id=" + idProvider,
        dataType: "json"
    })
            .fail(function () {
                console.log("service getArchives failed");
            })
            .always(function () {
                //console.log("finished");

            })
            .done(function (data) {
                console.log("==> getArchives");
                console.log(data.length + " archives");
                console.log(data);

                if (data.length == 0) {

                    $("#archives" + idDSA).append("<input type='hidden' name='ds_current_id' value='" + idDSA + "'/>");

                    var addButton = "<a class='btn btn-info btn-md' href='#archives" + idDSA + "' onclick='addArchive(" + idDSA + "," + idProvider + ")'>"
                            + "<span class='glyphicon glyphicon-plus-sign' title='add an archive'/> add an archive</a>";
                    $("#archives" + idDSA).append(addButton);

                    //$("#archives" + idDSA).append("<ol></ol>");
                    return;
                }
                for (i = 0; i < data[0].xml_archives.length; i++) {

                    var myArchive = data[0].xml_archives[i];
                    console.log(data[0].xml_archives[i]);
                    console.log("Archive number " + myArchive.archive_id);

                    var listItem = $("<li/>");
                    listItem.attr("data-id", i);
                    listItem.attr("id", "item" + idDSA + "_" + myArchive.archive_id);
                    //listItem.attr("title", "move this item up and down");
                    $(listItem).append(
                            "<span class='glyphicon glyphicon-move' title='move this item up and down'/>"

                            + "<div class='small-explanation'>"
                            + "<label>is latest:</label><input name='is_latest" + idDSA + "[]' id='is_latest" + myArchive.archive_id + "' type='radio' " + (myArchive.latest ? " checked='checked'" : " ") + "/>"
                            + "</div>"
                            + "<input name='archive_url[]' id='archive_url" + myArchive.archive_id + "' type='text' class='large' value='" + myArchive.xml_archive + "'/>"

                            + " <a href='#' class='btn btn-danger btn-sm'  onclick='hideArchive(" + idDSA + "," + myArchive.archive_id + "," + i + ")'>"
                            + " <span class='glyphicon glyphicon-remove' title='remove this archive' /></a>"

                            + " <a href='#' class='btn btn-success btn-sm' onclick='saveArchive(" + idProvider + "," + idDSA + "," + myArchive.archive_id + ")'><span class='glyphicon glyphicon-save' title='save this archive' /></a>"
                            );
                    $("#archives" + idDSA + " > ol").append(listItem);
                }
                $("#archives" + idDSA).append("<input type='hidden' name='ds_current_id' value='" + idDSA + "'/>");
                $("#archives" + idDSA).append("<a href='#archives' class='btn btn-info btn-md' onclick='addArchive(" + idDSA + "," + idProvider + ")'><span title='add an archive' class='glyphicon glyphicon-plus-sign' /> add an archive</a>");
                $("#archives" + idDSA + " > ol").sortable({
                    axis: 'y',
                    start: function (event, ui) {
                        //console.log("starting");
                    },
                    update: function (event, ui) {
                        var params = $(this).sortable('serialize');
                        console.log("sorting: serialized data: " + params + '&key=' + idDSA);
                        console.log("sorting: making persistant...");
                        $.ajax({
                            data: params + '&key=' + idDSA,
                            type: 'GET',
                            url: 'sortArchives.php'
                        })
                                .fail(function () {
                                    console.log("sortArchives failed");
                                })
                                .always(function () {
                                    //console.log("always");
                                })
                                .done(function (data) {
                                    console.log("sortArchives done: " + data);
                                });
                    }
                });
                console.log("<== getArchives");
            });
    return false;
}

/**
 * gets the useful Links of a given DSA
 *
 * @param {int} idProvider
 * @param {int} idDSA
 * @returns {boolean} false
 */
function getUsefulLinks(idProvider, idDSA) {
	console.log("getUsefulLinks()");
	
    $.ajax({
        type: "GET",
        url: "../services/useful-links/index.php?dataset_id=" + idDSA + "&provider_id=" + idProvider,
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
                    $("#useful-links" + idDSA).append(
                            "<a class='btn btn-info btn-md' href='#useful-links' onclick='addUsefulLink(" + idDSA + "," + idProvider + ")'>"
                            + "<span class='glyphicon glyphicon-plus-sign' title='add a link'/> add a useful link</a>"
                            );
                    if (!$("#useful-links" + idDSA + " ol").length)
                        $("#useful-links" + idDSA).append("<ol></ol>");
                    //return false;
                }
                for (i = 0; i < data.length; i++) {

                    if (data[i]["title"] != "BioCASe Archive") {

                        var listItem = $("<li/>");
                        listItem.attr("data-id", data[i]["link_id"]);
                        listItem.attr("id", "item" + idDSA + "_" + data[i]["link_id"]);
                        $(listItem).append(
                                "<span class='glyphicon glyphicon-move' title='move this item up and down'/>"
                                + " <input name='link_title[]' id='link_title" + data[i]["link_id"] + "' type='text' value='" + data[i]["title"] + "' class='short' list='link-categories'/>"
                                + " <datalist id='link-categories' class='link-categories'>" + $("#global-link-categories").html() + "</datalist>"

//                               + " <select name='link_title[]' id='link_title" + data[i]["link_id"] + "' id='link-categories' class='selectpicker link-categories'><option>--</option>" + linkCategories + "</select>"

                                + " <div class='mini-logo'>"
                                + "<img src='" + data[i]["logo"] + "'/>"
                                + "</div>"

//+ '  <div class="btn-group">'
//+ '     <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
//+ '         --'
//+ '         <span class="glyphicon glyphicon-chevron-down"></span>'
//+ '   </button>'   
//+ '  <ul class="dropdown-menu">' + linkCategories + '</ul>'
//+ '  </div>'

                                + " <input name='link_url[]' id='link_url" + data[i]["link_id"] + "' type='text' class='large' value='" + data[i]["link"] + "'/>"

                                + " <a href='#' class='btn btn-danger btn-sm'  onclick='hideUsefulLink(" + idDSA + "," + data[i]["link_id"] + ")'>"
                                + " <span class='glyphicon glyphicon-remove' title='remove this archive'/></a>"

                                + " <a href='#' class='btn btn-success btn-sm' onclick='saveUsefulLink(" + idProvider + "," + idDSA + "," + data[i]["link_id"] + ")'>"
                                + " <span class='glyphicon glyphicon-save' title='save this useful link'/></a>"
                                );
                        $("#useful-links" + idDSA + " ol").append(listItem);
                        //$("#useful-links" + idDSA + " ol .selectpicker").selectpicker();

                    }
                }

                $("#useful-links" + idDSA).append("<input type='hidden' name='ds_current_id' value='" + idDSA + "'/>");
                $("#useful-links" + idDSA).append("<a href='#useful-links' class='btn btn-info btn-md' onclick='addUsefulLink(" + idDSA + "," + idProvider + ")'><span class='glyphicon glyphicon-plus-sign' /> add a useful link</a>");
                $("#useful-links" + idDSA + " > ol").sortable({
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
 * @param {int} idProvider
 * @returns {boolean} false
 */
function getAllMetadata(idProvider) {
	console.log("getAllMetadata()");
	
    $.ajax({
        type: "GET",
        url: "../admin/getProviderMainData.php?key=" + idProvider,
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

                    $('#pr_name_edit').val(data[0]["name"]);
                    $('#pr_shortname_edit').val(data[0]["shortname"]);
                    $('#pr_url_edit').val(data[0]["providerUrl"]);
                    $('#pr_pywrapper').val(data[0]["pywrapper"]);

                    $("#maindata .save").html(
                            " <a href='#' class='btn btn-success btn-lg' onclick='saveMainMetadata()'>"
                            + "<span class='glyphicon glyphicon-save' title='save Main Metadata' /></a>");

                    $("#DSAGroupDynamic ul").html("");


                    for (i = 0; i < data.length; i++) {
                        if (i > 0 && data[i]["id"] == data[i - 1]["id"])
                            continue;


                        // display current DSA
                        //var displayedTitle = data[i]["title_slug"].substring(0, 11);
                  		 var displayedTitle = data[i]["title_slug"].substring(0, 16) + "/" + data[i]["title"].substring(0, 10);
                        var listItem = $("<li/>");
						$(listItem).on("click", { dat: data[i] }, function(event) { getMetadataForm(idProvider, event.data.dat) });
                        $(listItem).append("<a href='#dsa" + data[i]["id"] + "' "
                                + "title='" + + data[i]["id"] + ": " + data[i]["title_slug"] + " - " + data[i]["title"] + "'>"
                                + "<span>" + displayedTitle + "</span></a>");
                    //@todo embed into a DIV and use class inactive
                        $(listItem).css('background',(data[i]["active"] == 1 ? "white" : "lightgray")); 

                        $("#DSAGroupDynamic ul").append(listItem);

			// load first datasource
			if(i ===0)
				getMetadataForm(idProvider, data[0]);

                    }
                }

                $("#DSAGroupDynamic ul").append("<a href='#DSAGroupDynamic' class='btn btn-md btn-info' onclick='addDSA(" + idProvider + ")'><span class='glyphicon glyphicon-plus-sign' title='add a Data Source'/></a>");
                $("#DSAGroupDynamic").tabs();
                $("#DSAGroupDynamic").tabs("refresh");


            }
            );
    return false;
}


function getMetadataForm(idProvider, data) {

console.log("getMetadataForm data:");
console.log(data);

			//exit if it alread exists
			if($("#dsa"+data["id"]).length)
				return;

                        // ID
                        var myId = parseInt(data["id"]);

                        var collection = "<div id='dsa" + data["id"] + "'" + " data-id='" + data["id"] + "' " + (data["active"] == 0 ? "class='inactive'" : "") + ">";
                        collection += "<input name='ds[" + myId + "][id]' type='hidden' value='" + myId + "'/>";
                        collection += "<table class='table table-condensed'>";

                        // Last Modified
                        collection += "<tr><td><label for='ds_lastaccess'>last edit: </label>";
                        collection += "<td class='small-info'>" + data["timestamp"];

                        // Status
                        collection += "<tr><td><label for='ds_active'>Status: </label>";

                        collection += "<td><select id='ds_active" + myId + "' name='ds[" + myId + "][active]' >";
                        collection += "<option value='0' " + (data["active"] == 0 ? "selected='selected'" : "") + ">inactive</option>";
                        collection += "<option value='1' " + (data["active"] == 1 ? "selected='selected'" : "") + ">active</option>";
                        collection += "</select>";

                        // alternative pywrapper on dataset level

                        if (userRights & 8 == 8) {
                            collection += "<tr><td><label for='ds_pywrapper'>alternative BioCASe URL: </label>";
                            collection += "<td><input id='ds_pywrapper" + myId + "' name='ds[" + myId + "][alt_pywrapper]' type='text'/>";
                        }

                        // DataSource
                        collection += "<tr><td><label for='ds_accesspoint'>Data-Source: </label>";
                        collection += "<td><select id='ds_accesspoint" + myId + "' name='ds[" + myId + "][accesspoint]'/>";

                        // DataSource Full Title
                        collection += "<tr><td><label for='ds_title'>Title: </label>";
                        collection += "<td><input id='ds_title" + myId + "'" + " name='ds[" + myId + "][title]' type='text' value='" + data["title"] + "' required='required'/>";

                        // URL
                        collection += "<tr><td><label for='ds_url'>URL:</label>";
                        collection += "<td><input id='ds_url" + myId + "'" + " name='ds[" + myId + "][url]' type='text' readonly='readonly'/>";

                        // DataSet
                        collection += "<tr><td><label for='ds_title_list'>Data Set: </label>";
                        collection += "<td><div id='ds_title_list" + myId + "'>" + spinner + "</div>";
                        //collection += "<td><div id='ds_title_list" + data["id"] + "'>" +  data["dataset"] + "</div>";

                        // Schema
                        collection += "<tr><td><label for='ds_schema'>Schema: </label>";
                        collection += "<td><select id='ds_schema" + myId + "' name='ds[" + myId + "][ds_schema]'/>";

                        // landingpage
                        var preferred_0 = (data["preferred_landingpage"] == 0 ? "checked='checked'" : "");
                        var preferred_1 = (data["preferred_landingpage"] == 1 ? "checked='checked'" : "");

                        collection += "<tr><td colspan='1'>";
                        collection += "<label>Landing-Page:</label></td>";

                        collection += "<td>";
                        collection += "<input id='ds_preferred_landingpage" + myId + "'"
                                + " name='ds[" + myId + "][preferred_landingpage]' type='radio' value='0'" + preferred_0 + "/>";
                        collection += "<label> automatic</label>   ";

                        collection += "<input id='ds_preferred_landingpage" + myId + "'"
                                + " name='ds[" + myId + "][preferred_landingpage]' type='radio' value='1'" + preferred_1 + "/>";
                        collection += "<label for='ds_landingpage_url" + myId + "'> user-defined:</label></tr>";

                        collection += "<tr>";
                        collection += "<td></td>";
                        collection += "<td><input id='ds_landingpage_url" + myId + "'" + " name='ds[" + myId + "][landingpage_url]' value='" + data["landingpage_url"] + "' type='text' />";

                        // filter (advanced)
                        collection += "<tr><td colspan='2'>";
                        collection += "<div class='grouped no-border'>";
                        collection += "<h6><a data-toggle='collapse' href='#query-builder" + myId + "'>";
                        collection += "<span class='glyphicon glyphicon-triangle-bottom'></span>";
                        collection += "<span class='glyphicon glyphicon-triangle-right'></span>";
                        collection += "&nbsp;&nbsp;advanced</a></h6>";
                        // @todo: UI for query builder
                        collection += "<div id='query-builder" + myId + "' class='query-builder collapse' style='min-height:120px;'>";
                        // list of capabilities for constructing the filter obtained via ajax
                        collection += "<label>final filter:</label> <span class='short-hint'>" + message.filterSyntax + "</span>";
                        collection += "<div id='ds-filter" + myId + "' style='display:none'></div>";
                        collection += "<textarea id='ds_final_filter" + myId + "'" + " name='ds[" + myId + "][final_filter]'>"+ data["filter"] +"</textarea>";
				console.log("Filter on load: " + data["filter"] ); 
                        collection += "</div></div>";
                        collection += "</td></tr>";

                        // archives
                        collection += "<tr><td colspan='2'>";
                        collection += "<div class='grouped'>";
                        collection += "<h4><a data-toggle='collapse' href='#archives" + myId + "'>";
                        collection += "<span class='glyphicon glyphicon-triangle-bottom'></span>";
                        collection += "<span class='glyphicon glyphicon-triangle-right'></span>";
                        collection += "&nbsp;&nbsp;Archives</a></h4>";
                        collection += "<div id='archives" + myId + "' class='archives collapse'>";
                        collection += "<ol></ol>";
                        collection += "</div></div>";
                        collection += "</td></tr>";

                        // useful links
                        collection += "<tr><td colspan='2'>";
                        collection += "<div class='grouped'>";
                        collection += "<h4><a data-toggle='collapse' href='#useful-links" + myId + "'>";
                        collection += "<span class='glyphicon glyphicon-triangle-bottom'></span>";
                        collection += "<span class='glyphicon glyphicon-triangle-right'></span>";
                        collection += "&nbsp;&nbsp;Useful Links</a></h4>";
                        collection += "<div id='useful-links" + myId + "' class='useful-links collapse'>";
                        collection += "<ol></ol>";
                        collection += "</div></div>";
                        collection += "</td></tr>";

                        collection += "</table>";

                        // title and buttons on a single line
                        collection += "<div class='active-dsa'>";
                        // full title
                        collection += "<span class='active-dsa-title'>" + data["title"] + "</span></div>";
                        collection += "<div style='float:right;'>";
                        // remove
                        collection += " <a href='#' class='btn btn-danger btn-lg' onclick='hideDSA(" + myId + ")'>"
                                + "<span class='glyphicon glyphicon-remove' title='Remove Data Source " + data["title"] + "'/></a>";
                        // save
                        collection += " <a href='#' class='btn btn-success btn-lg' id='saveDSA" + myId + "'>"
                                + "<span class='glyphicon glyphicon-save' title='Save Data Source " + data["title"] + "' /></a>";
                        collection += "</div>";

                        $("#DSAGroupDynamic").append(collection);

                        // fill in DSA Point
                        var myPywrapper = (data["alt_pywrapper"] ? data["alt_pywrapper"] : data["pywrapper"]);
                        getDataSourceAccessPoints(idProvider, myPywrapper, data["id"], data["title_slug"], data["dataset"]);

                        // trigger changes to the DSA Point
                        $('#ds_accesspoint' + data["id"]).on("change", {datasource: data}, function (event) {

                            var myData = event.data.datasource;
					
                            console.log(myData);
							console.log("new Data:");
							console.log(this.value); 
                            console.log("existing dataset was: " + myData.dataset);
                            console.log("==> begin trigger value change of ds_accesspoint" + myData.id);

							var newURL = (data["alt_pywrapper"] ? data["alt_pywrapper"] : data["pywrapper"]) + "/pywrapper.cgi?dsa=" + this.value;
                            // compute the list of all possible datasets
                            console.log("triggering... computing datasets for: " + myData.dataset + " url=" + newURL);
                            //getDataSetTitles(myData.id, $('#ds_url' + myData.id).val(), myData.dataset);
                            getDataSetTitles(myData.id, newURL, myData.dataset);

                            // set DSA point as default full title
                            if ($('#ds_title' + myData.id).val() === "") {
                                $('#ds_title' + myData.id).val(myData.title);
                            }
                            $('#ds' + myData.id + ' div.active-dsa-title').text($('#ds_title' + myData.id).val());
                            console.log("set title to " + $('#ds_title' + myData.id).val());
                            console.log("<== end trigger DSA Point " + myData.id);
                        });
                        // trigger value of accesspoint, NOT  NESCESSARY
                        //$('#ds_accesspoint' + data["id"]).trigger("change", {datasource: data});

                        // trigger changes of alternative pywrapper
                        $('#ds_pywrapper' + data["id"]).on("change", {datasource: data}, function (event) {

                            var myData = event.data.datasource;
                            console.log("==> begin trigger value change of DS_PYWRAPPER" + myData.id);

                            console.log("general pywrapper: " + $('#ds_url' + myData.id).val());
                            console.log("alternative pywrapper: " + $(this).val( ));

                            if ($(this).val().trim().length > 0) {
                                console.log("set url to alternative pywrapper: " + $(this).val() + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + myData.id).val());
                                $('#ds_url' + myData.id).val($(this).val() + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + myData.id).val());
                            } else {
                                console.log("rollback to general pywrapper: " + $('#pr_pywrapper').val());
                                $('#ds_url' + myData.id).val($('#pr_pywrapper').val() + "/pywrapper.cgi?dsa=" + $('#ds_accesspoint' + myData.id).val());
                            }

                            // recompute the list of all possible datasets
                            console.log("recomputing datasets for: " + myData.dataset + " url=" + $('#ds_url' + myData.id).val());
                            getDataSetTitles(myData.id, $('#ds_url' + myData.id).val(), myData.dataset);

                            console.log("computing schemas for: " + myData.id);
                            console.log("idProvider=" + idProvider + " idDSA=" + myData.id);
                            getSchemas(idProvider, myData.id, $('#ds_accesspoint' + myData.id).val(), myData.schema);

                            console.log("<== end trigger pywrapper " + myData.id);
                        });
                        // trigger value of alternative pywrapper
                        $('#ds_pywrapper' + data["id"]).trigger("change", {datasource: data});

                        // trigger SAVE button
                        $("#saveDSA" + data["id"]).on("click", function () {

                            var myId = this.id.split("saveDSA")[1];
                            console.log("triggering saveDSA button for record " + myId);

                            var myDSA = new Object();
                            myDSA.id = myId;
                            myDSA.url = $("#ds_url" + myId).val();
                            myDSA.title = $("#ds_title" + myId).val();
                            myDSA.title_slug = $("#ds_accesspoint" + myId).val();
                            myDSA.dataset = $("#ds_title_list" + myId + " select").val();
                            myDSA.pywrapper = $("#ds_pywrapper" + myId).val();
                            myDSA.filter = $("#ds_final_filter" + myId).val();
							console.log("Filter: "); console.log(myDSA.filter);
                            myDSA.preferred_landingpage = $("#ds_preferred_landingpage" + myId + ":checked").val();
                            myDSA.landingpage_url = $("#ds_landingpage_url" + myId).val();
                            myDSA.active = $("#ds_active" + myId).val();
                            myDSA.schema = $("#ds_schema" + myId).val();

                            saveDSAPoint(myDSA, false);
                        });

                        $('#ds_pywrapper' + data["id"]).val(data["alt_pywrapper"]);

                        // preparing filter
                        getCapabilities(idProvider, data["id"], data["title_slug"]);

                        // get all archives
                        getArchives(idProvider, data["id"]);

                        // get all useful links
                        getUsefulLinks(idProvider, data["id"]);

                        $("#collapsible_" + data["id"]).accordion({collapsible: true, active: false});
}


$(document).ready(function () {

    $("#footer-control a").on("click", function () {
        $("#footer").toggle("slow");
    });

    // get system message list
    getMessages("/");

    // get providers
    getProviders();

    // get Link Category list (gbif, etc.)
    getLinkCategories();


    // trigger provider change
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

    var progress = setInterval(function () {

        var totalWidth = $(".progress").width();
        var currentWidth = Math.round(($.active * totalWidth) / 100);

        //update the progress-bar
        $(".progress-bar").css('width', currentWidth).attr('aria-valuenow', $.active);
        $(".progress-bar ").html($.active + message.concurrentRequests);

        //clear timer when zero is reached
        if ($.active === 0) {
            clearInterval(progress);
            $(".progress-bar").removeClass("active");
            $(".progress-bar").removeClass("progress-bar-striped");
            $(".progress-bar").removeClass("progress-bar-info");
            $(".progress-bar").removeClass("progress-bar-warning");
            $(".progress-bar").removeClass("progress-bar-danger");
            $(".progress-bar").addClass("progress-bar-success");
            $(".progress-bar").css('width', '100%');
            $(".progress-bar ").html('all done.');
        } else if ($.active < 30) {
            $(".progress-bar").addClass("progress-bar-info");
            $(".progress-bar").removeClass("progress-bar-warning");
            $(".progress-bar").removeClass("progress-bar-danger");
        } else if ($.active < 60) {
            $(".progress-bar").removeClass("progress-bar-info");
            $(".progress-bar").addClass("progress-bar-warning");
            $(".progress-bar").removeClass("progress-bar-danger");
        } else {
            $(".progress-bar").removeClass("progress-bar-info");
            $(".progress-bar").removeClass("progress-bar-warning");
            $(".progress-bar").addClass("progress-bar-danger");
        }
    }, 500);



}
);
