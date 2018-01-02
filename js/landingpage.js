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