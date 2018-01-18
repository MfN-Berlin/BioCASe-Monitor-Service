
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @namespace Bms
 * @file biocasemonitor/js/landingpage.js
 * @brief javascript statements for the landingpage 
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

/* global originalURL, dsa, unitUrl, querytoolUrl */


$(document).ready(function () {

    $("#xml-source").html(originalURL);
    $("#xml-source").attr("href", originalURL);
    $("#localQueryToolUrl").html('<a target="localQueryTool" href="' + querytoolUrl +  '">' + 'Local Query Tool' + '</a>');

    $.each($(".scroll-box ul li"), function (key, value) {
        var unitID = $(this).find(".landingpage-unit");
        unitID.attr("href", unitUrl + "&amp;" + unitID.html().trim());
        unitID.html("DataUnit Landingpage");
     });
    
    // first record as an example
    var attr = $(".scroll-box ul li:first-child .landingpage-unit").attr("href");
    if (attr !== undefined) {
        dataUnitPage = $(".scroll-box ul li:first-child .landingpage-unit").attr("href");
        wrapperURL = dataUnitPage.split("wrapper_url=")[1];
        $("#dataUnitLandingpage").html(wrapperURL);       
    }

});                   