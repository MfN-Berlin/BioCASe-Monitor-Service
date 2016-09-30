<?php
/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/manageProviderForm.php
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
<div id="main">
    <h1 class="hint">
        Registration Manager of a BioCASe provider in the Metadata Catalogue
    </h1>

    <form id="updateProvider" name="updateProvider" method="POST">
        <input type="hidden" name="frm_sent" id="frm_sent" value="yes"/>
        <select id="pr_name" name="pr_name"></select>

        <div class="save save-all" style="display:none">
            <input DISABLED type="image" alt="save" title="save all" src="../images/glyphicons/glyphicons-415-disk-save.png" />
        </div>

        <section id="main-metadata">
            <h3>Main Metadata
                <div class='save'></div>
            </h3>

            <table>
                <tr>
                    <td>*</td>
                    <td><label for="pr_name_edit">Name:</label></td>
                    <td><input type="text"  id="pr_name_edit" name="pr_name_edit" required="required" /></td>
                </tr>
                <tr>
                    <td>*</td>
                    <td><label for="pr_shortname_edit">Short Name:</label></td>
                    <td><input type="text"  id="pr_shortname_edit" name="pr_shortname_edit" required="required" /></td>
                </tr>
                <tr>
                    <td>*</td>
                    <td><label for="pr_url_edit">Institution URL:</label></td>
                    <td><input type="text"  id="pr_url_edit" name="pr_url_edit" required="required" /></td>
                </tr>
                <tr>
                    <td>*</td>
                    <td><label for="pr_url_edit">BioCASe URL:</label></td>
                    <td><input type="text"  id="pr_pywrapper" name="pr_pywrapper" placeholder="URL of the BioCASe pywrapper" required="required" /></td>
                </tr>
                <!--
                <tr>
                    <td>*</td>
                    <td><label for="pr_url_edit">querytool:</label></td>
                    <td><input type="text"  id="pr_querytool" name="pr_querytool" placeholder="URL of the BioCASe Query Tool" required="required" /></td>
                </tr>
                -->
            </table>
        </section>

        <section id="count-concepts">
            <h3 title='sortable by dragging the items'>Count Concepts </h3>
            <ul id="count-concepts-list"></ul>
        </section>

        <section id="DSAGroupDynamic" style="border: 1px solid #CCC !important; border-radius:0;">
            <h3>Data Source Access Points</h3>
            <ul id="dsa-list"></ul>
        </section>

        <div id="system-message"></div>

    </form>

</div>
