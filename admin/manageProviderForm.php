<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/admin/manageProviderForm.php
 * @brief backend: manage Provider (html form)
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
if (false) {
    
}
?>
<div id="main" class="container">

    <form id="updateProvider" name="updateProvider" method="POST">

        <input type="hidden" name="frm_sent" id="frm_sent" value="yes"/>
        <select id="pr_name" name="pr_name"></select>

        <div class="progress" style="margin:0;width:100%">
            <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" width="100%"
                 aria-valuenow="1000" aria-valuemin="0" aria-valuemax="1000" ></div>
        </div>

        <section id="maindata">
            <h3>
                <a data-toggle="collapse" href="#maindata-content">Basic Metadata</a>
            </h3>
            <div id="maindata-content" class="collapse">
                <table class="table table-condensed">
                    <tr>
                        <td><label for="pr_name_edit">Name:</label></td>
                        <td><input type="text"  id="pr_name_edit" name="pr_name_edit" required="required" /></td>
                        <td rowspan="4"><div class='save'></div></td>
                    </tr>
                    <tr>
                        <td><label for="pr_shortname_edit">Short Name:</label></td>
                        <td><input type="text"  id="pr_shortname_edit" name="pr_shortname_edit" required="required" /></td>
                    </tr>
                    <tr>
                        <td><label for="pr_url_edit">Institution URL:</label></td>
                        <td><input type="text"  id="pr_url_edit" name="pr_url_edit" required="required" /></td>
                    </tr>
                    <tr>
                        <td><label for="pr_url_edit">BioCASe URL:</label></td>
                        <td><input type="text"  id="pr_pywrapper" name="pr_pywrapper" placeholder="URL of the BioCASe pywrapper" required="required" /></td>
                    </tr>
                </table>
            </div>
        </section>

    </form>



    <form id="dummyForm" name="updateProviderDetails" method="POST">

        <section id="count-concepts">
            <h3>
                <a data-toggle="collapse" href="#count-concepts-content">Count Concepts</a>
            </h3>
            <div id="count-concepts-content" class="collapse">
                <ul id="count-concepts-list"></ul>
            </div>
        </section>

        <section id="DSAGroupDynamic">
            <h3>
                <a href="#dsa-list-content">Data Source Access Points</a>
            </h3>
            <ul id="dsa-list" ></ul>
        </section>

    </form>



    <div id="system-message"></div>

    <div id="global-link-categories"></div>
</div>
