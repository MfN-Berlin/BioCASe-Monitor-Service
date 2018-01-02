<?xml version="1.0" encoding="UTF-8"?>
<!--
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 *   landingpage xsl transformation for datasets without multimedia files
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
-->

<xsl:stylesheet version="2.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:fn="http://www.w3.org/2005/xpath-functions"
                xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06"
                xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3"
                xmlns="http://www.w3.org/1999/xhtml"
>
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>

    <xsl:template name="get-file-extension">
        <xsl:param name="path"/>
        <xsl:choose>
            <xsl:when test="contains($path, '/')">
                <xsl:call-template name="get-file-extension">
                    <xsl:with-param name="path" select="substring-after($path, '/')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="contains($path, '.')">
                <xsl:call-template name="get-file-extension">
                    <xsl:with-param name="path" select="substring-after($path, '.')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$path"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="/">
        <html>
            <head>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <title>ABCD Landingpage</title>
                <link rel="stylesheet" type="text/css" href="./css/frame.css"/>
                <link rel="stylesheet" type="text/css" href="./css/frontend.css"/>
                <link rel="stylesheet" type="text/css" href="./css/landingpage.css"/>
                <link rel="stylesheet" type="text/css" href="./css/custom.css"/>
                <script src="js/general.js"></script>
                <script src="js/landingpage.js"></script>
            </head>
            <body>

                <nav class="navbar  navbar-default">
                    <div class="container-fluid">
                        <div class="navbar-header">
                            <figure>
                                <a href="./">
                                    <img src="./images/biocase-logo.jpg" alt="logo" title="BioCASe Monitor Start Page"/>
                                </a>
                                <figcaption>Monitor</figcaption>
                            </figure>
                        </div>
                        <div class="navbar-header">
                            <xsl:processing-instruction name="php">
                                if ($custom_layout == "1") {
                                include "config/custom/institution_logo.html";
                                }
                                ?</xsl:processing-instruction>
                        </div>

                        <div class="navbar-header">
                            <h3>ABCD Landingpage</h3>
                        </div>

                        <xsl:for-each select="//abcd:DataSets/abcd:DataSet">
                            <div class="navbar-header">
                                <h3 style="border-left:1px solid grey;margin-left:10px;padding-left:10px;">
                                    <xsl:value-of select="abcd:Metadata/abcd:Description/abcd:Representation/abcd:Title"/>
                                </h3>
                            </div>
                        </xsl:for-each>
                      

                        <ul class="nav navbar-nav navbar-right">

                            <xsl:processing-instruction name="php">   

                                if ($_SESSION["authenticated"]) { 
                                echo '<li>
                                    <a href="admin/manageProvider.php"
                                       id="menuProvider"
                                       title="manage provider metadata"
                                       class="glyphicon glyphicon-cog"> Dashboard</a>
                                </li>';
                                }
                                echo '<li>
                                    <a href="admin/index.php" title="Administration" class="glyphicon glyphicon-log-in"> Login</a>
                                </li>';
                                } else {
                                echo '<li>
                                    <a href="admin/manageUser.php" title="Profile" class="glyphicon glyphicon-user"> ' . $_SESSION["fullname"] . '</a>
                                </li>';
                                echo '<li>
                                    <a href="index.php?log_out=1" title="log out" class="glyphicon glyphicon-log-out"> Logout</a>
                                </li>';
                                }
                                ?</xsl:processing-instruction>

                            <li>
                                <a href="./services/" title="API" class="glyphicon glyphicon-globe"> Webservices</a>
                            </li>

                            <li>
                                <a id="footer-control" href="#"
                                   title="Legal Infos"
                                   class="glyphicon glyphicon-info-sign"> Legal</a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <div id="footer">
                    <ul class="impressum">
                        <li class="menuItem">
                            <b>BioCASe Monitor</b>
                            <div style="font-size:0.9em">
                                v
                               <xsl:processing-instruction name="php">echo _VERSION; ?</xsl:processing-instruction>
                            </div>
                        </li>

                        <li class="menuItem" style="display:none" >
                            developed by
                            <br/>
                            <br/>
                            <img src="../images/OpenUp-Logo50x50.png" alt="OpenUp!" width="40"/>
                            and
                            <img src="../images/GBIF-D-Logo50x50.png" alt="GBIF-D" width="40"/>
                        </li>
                        <li class="menuItem">
                            hosted by
                            <br/>
                            <br/>
                            <a href="http://www.naturkundemuseum.berlin/" title="http://www.naturkundemuseum.berlin/"
                               target="_blank">
                                <img src="../images/mfnlogo_167_190.jpg" height="30" alt="Museum für Naturkunde, Berlin"/>
                            </a>
                        </li>

                        <li class="menuItem">
                            <a href="./index.php">Home</a>
                        </li>

                        <li class="menuItem">
                            <a href="http://biocasemonitor.biodiv.naturkundemuseum-berlin.de/index.php/Documentation" target="_blank">Documentation</a>
                            <!-- <a href="./admin/documentation.php" target="_blank">Documentation</a> -->
                        </li>
                        <li class="menuItem">
                            <a href="../admin/impressum.php">Imprint</a>
                        </li>
                    </ul>
                </div>


                <div class="container">

                    <div class="row">
                        <div class="col-md-12 alert alert-info">
                            This Dataset Landingpage is dynamically generated by the BioCASe Monitor Service and shows some example units.
                            
                            <button class="btn" data-toggle="collapse" data-target="#readmore">read more</button>
                            
                            <ol id="readmore" class="collapse">
                                <li>The
                                    <b><a href="./services/" target="_blank">Webservice "get Landingpages"</a></b>
                                    helps to build the URL for detailed landingpages.
                                </li>    
                                <li>
                                    The <b><span id="localQueryToolUrl" class="hyperlink"></span></b> 
                                    to filter out the desired single data units.                                                                       
                                </li>
                                <li>
                                    The <b>DataUnit Landingpages</b> can be easily generated by adding the UnitID
                                    <b><span class="hyperlink">&amp;cat=XXX</span></b>
                                    to the parameter string.
                                    <br/>For the first Example Unit this would be:
                                    <br/>
                                    <span id="dataUnitLandingpage" class="hyperlink"></span>
                                </li>
                            </ol>
                        </div>
                    </div>

                    <xsl:for-each select="//abcd:DataSets/abcd:DataSet">
                        <div class="row dataset">

                            <div class="well">
                                <h2>
                                    <xsl:value-of select="abcd:Metadata/abcd:Description/abcd:Representation/abcd:Title"/>
                                </h2>

                                <p>XML source:
                                    <a id="xml-source">
                                        <xsl:attribute name="href">original URL</xsl:attribute>
                                        <xsl:attribute name="target">_blank</xsl:attribute>
                                        <xsl:attribute name="title">get xml source</xsl:attribute>
                                        <!-- original url -->
                                    </a>
                                </p>

                                <p>
                                    <xsl:value-of select="abcd:Metadata/abcd:Description/abcd:Representation/abcd:Details"/>
                                </p>
                            </div>

                            <table class="table">
                                <xsl:if test="abcd:TechnicalContacts/abcd:TechnicalContact">
                                    <tr>
                                        <td>
                                            <b>Technical Contact<xsl:if test="count(abcd:TechnicalContacts/abcd:TechnicalContact) gt 1">s</xsl:if></b>
                                        </td>
                                        <td>
                                            <ul>
                                                <xsl:for-each select="abcd:TechnicalContacts/abcd:TechnicalContact">
                                                    <li>
                                                        <xsl:value-of select="abcd:Name"/>
                                                        <xsl:if test="abcd:Email"> (<a>
                                                                <xsl:attribute name="href">mailto:<xsl:value-of select="abcd:Email"/></xsl:attribute>
                                                                <xsl:value-of select="abcd:Email"/>
                                                            </a>)</xsl:if>
                                                    </li>
                                                </xsl:for-each>
                                            </ul>
                                        </td>
                                    </tr>
                                </xsl:if>

                                <xsl:if test="abcd:ContentContacts/abcd:ContentContact">
                                    <tr>
                                        <td>
                                            <b>Content Contact<xsl:if test="count(abcd:ContentContacts/abcd:ContentContact) gt 1">s</xsl:if></b>
                                        </td>
                                        <td>
                                            <ul>
                                                <xsl:for-each select="abcd:ContentContacts/abcd:ContentContact">
                                                    <li>
                                                        <xsl:value-of select="abcd:Name"/>
                                                        <xsl:if test="abcd:Email"> (<a>
                                                                <xsl:attribute name="href">mailto:<xsl:value-of select="abcd:Email"/></xsl:attribute>
                                                                <xsl:value-of select="abcd:Email"/>
                                                            </a>)</xsl:if>
                                                    </li>
                                                </xsl:for-each>
                                            </ul>
                                        </td>
                                    </tr>
                                </xsl:if>

                                <xsl:if test="abcd:Metadata/abcd:Owners/abcd:Owner">
                                    <tr>
                                        <td>
                                            <b>Owner<xsl:if test="count(abcd:Metadata/abcd:Owners/abcd:Owner) gt 1">s</xsl:if></b>
                                        </td>
                                        <td>
                                            <ul>
                                                <xsl:for-each select="abcd:Metadata/abcd:Owners/abcd:Owner[not(abcd:Person) and abcd:Organisation]">
                                                    <li class="org">
                                                        <xsl:choose>
                                                            <xsl:when test="abcd:URIs/abcd:URL">
                                                                <a>
                                                                    <xsl:attribute name="href">
                                                                        <xsl:value-of select="abcd:URIs/abcd:URL[1]"/>
                                                                    </xsl:attribute>
                                                                    <xsl:value-of select="abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
                                                                    <xsl:if test="abcd:Organisation/abcd:Name/abcd:Representation/abcd:Abbreviation"> (<xsl:value-of select="abcd:Organisation/abcd:Name/abcd:Representation/abcd:Abbreviation"/>)</xsl:if>
                                                                </a>
                                                            </xsl:when>
                                                            <xsl:otherwise>
                                                                <xsl:value-of select="abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
                                                                <xsl:if test="abcd:Organisation/abcd:Name/abcd:Representation/abcd:Abbreviation"> (<xsl:value-of select="abcd:Organisation/abcd:Name/abcd:Representation/abcd:Abbreviation"/>)</xsl:if>
                                                            </xsl:otherwise>
                                                        </xsl:choose>
                                                    </li>
                                                </xsl:for-each>
                                                <xsl:for-each select="abcd:Metadata/abcd:Owners/abcd:Owner[abcd:Person]">
                                                    <li class="pers">
                                                        <xsl:value-of select="abcd:Person/abcd:FullName"/>
                                                        <xsl:if test="abcd:Roles/abcd:Role">
                                                            (<xsl:value-of select="fn:replace(fn:string-join(abcd:Roles/abcd:Role,', '),', $','')"/>)
                                                            <xsl:if test="abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text">
                                                                <br/>
                                                                <xsl:value-of select="abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
                                                            </xsl:if>
                                                        </xsl:if>
                                                    </li>
                                                </xsl:for-each>
                                            </ul>
                                        </td>
                                    </tr>
                                </xsl:if>

                                <xsl:if test="abcd:Metadata/abcd:RevisionData/abcd:DateModified">
                                    <tr>
                                        <td>
                                            <b>Last Modified</b>
                                        </td>
                                        <td>
                                            <xsl:value-of select="abcd:Metadata/abcd:RevisionData/abcd:DateModified"/>
                                        </td>
                                    </tr>
                                </xsl:if>

                                <xsl:if test="abcd:Units/abcd:Unit/abcd:RecordBasis">
                                    <tr>
                                        <td>
                                            <b>Record Basis</b>
                                        </td>
                                        <td>
                                            <ul>
                                                <xsl:for-each select="distinct-values(abcd:Units/abcd:Unit/abcd:RecordBasis/text())">
                                                    <xsl:sort select="."/>
                                                    <li>
                                                        <xsl:value-of select="."/>
                                                    </li>
                                                </xsl:for-each>
                                            </ul>
                                        </td>
                                    </tr>
                                </xsl:if>

                                <tr>
                                    <td>
                                        <b>
                                            <xsl:value-of select="count(abcd:Units/abcd:Unit)"/> Example Units</b>
                                    </td>
                                    <td>
                                        <div class="scroll-box">
                                            <ul>
                                                <xsl:for-each select="abcd:Units/abcd:Unit">
                                                    <li>

                                                        <xsl:variable name="fileuri">
                                                            <xsl:value-of select="abcd:MultiMediaObjects/abcd:MultiMediaObject[1]/abcd:FileURI"/>
                                                        </xsl:variable>

                                                        <xsl:variable name="extension">
                                                            <xsl:call-template name="get-file-extension">
                                                                <xsl:with-param name="path" select="abcd:MultiMediaObjects/abcd:MultiMediaObject[1]/abcd:FileURI" />
                                                            </xsl:call-template>
                                                        </xsl:variable>

                                                        <div style='float:left;width:220px;'>

                                                            <xsl:if test="$extension = 'wav' or $extension = 'mp3'">
                                                                <audio>
                                                                    <xsl:attribute name="controls"></xsl:attribute>
                                                                    <source>
                                                                        <xsl:attribute name="src">
                                                                            <xsl:value-of select="abcd:MultiMediaObjects/abcd:MultiMediaObject[1]/abcd:FileURI"/>
                                                                        </xsl:attribute>
                                                                    </source>
                                                                </audio>
                                                            </xsl:if>

                                                            <xsl:if test="$extension = 'mp4' or $extension = 'ogv'">
                                                                <video>
                                                                    <xsl:attribute name="controls"></xsl:attribute>
                                                                    <source>
                                                                        <xsl:attribute name="src">
                                                                            <xsl:value-of select="abcd:MultiMediaObjects/abcd:MultiMediaObject[1]/abcd:FileURI"/>
                                                                        </xsl:attribute>
                                                                    </source>
                                                                </video>
                                                            </xsl:if>

                                                            <xsl:if test="$extension = 'jpg' or $extension = 'png' or $extension = 'gif'">
                                                                <img>
                                                                    <xsl:attribute name="src">
                                                                        <xsl:value-of select="abcd:MultiMediaObjects/abcd:MultiMediaObject[1]/abcd:FileURI"/>
                                                                    </xsl:attribute>
                                                                    <xsl:attribute name="width">
                                                                        200
                                                                    </xsl:attribute>
                                                                </img>
                                                            </xsl:if>

                                                        </div>

                                                        <div style='margin-left:220px;'>
                                                            <xsl:choose>
                                                                <xsl:when test="abcd:RecordURI">
                                                                    <a>
                                                                        <xsl:attribute name="href">
                                                                            <xsl:value-of select="abcd:RecordURI/text()"/>
                                                                        </xsl:attribute>
                                                                        <xsl:attribute name="target">
                                                                            _blank
                                                                        </xsl:attribute>
                                                                        <xsl:value-of select="abcd:UnitID"/> [<xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString"/>]
                                                                    </a>
                                                                </xsl:when>
                                                                <xsl:otherwise>
                                                                    <xsl:value-of select="abcd:UnitID"/>
                                                                    [<xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString"/>]
                                                                </xsl:otherwise>
                                                            </xsl:choose>

                                                            <br/>
                                                            <br/>
                                                            <i>Higher Taxon: </i>
                                                            <xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:HigherTaxa/abcd:HigherTaxon/abcd:HigherTaxonName"/>

                                                            <br/>
                                                            <br/>
                                                            <i>SourceInstitutionID: </i>
                                                            <xsl:value-of select="abcd:SourceInstitutionID"/>

                                                            <br/>
                                                            <i>SourceID: </i>
                                                            <xsl:value-of select="abcd:SourceID"/>

                                                            <br/>
                                                            <i>UnitID: </i>
                                                            <xsl:value-of select="abcd:UnitID"/>

                                                            <br/>
                                                            <br/>
                                                            <a class="landingpage-unit">
                                                                <xsl:attribute name="id">
                                                                    <xsl:value-of select="fn:replace(abcd:UnitID,' ','-')"/>
                                                                </xsl:attribute>
                                                                <xsl:attribute name="target">_blank</xsl:attribute>
                                                                <xsl:attribute name="href"/>
                                                                inst=<xsl:value-of select="abcd:SourceInstitutionID"/>&amp;col=<xsl:value-of select="abcd:SourceID"/>&amp;cat=<xsl:value-of select="fn:encode-for-uri(abcd:UnitID)"/>
                                                            </a>


                                                        </div>

                                                    </li>


                                                </xsl:for-each>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </xsl:for-each>

                </div>

            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
