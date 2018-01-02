<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:pangaea.de:dataportals">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<oai:OAI-PMH xmlns:oai="http://www.openarchives.org/OAI/2.0/"
			  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd
				  				urn:pangaea.de:dataportals http://ws.pangaea.de/schemas/pansimple/pansimple.xsd
				  				http://purl.org/dc/elements/1.1/ http://dublincore.org/schemas/xmls/qdc/dc.xsd">
		
	<oai:responseDate>2002-02-08T08:55:46Z</oai:responseDate>
	<oai:request verb="GetRecord" identifier="oai:arXiv.org:cs/0112017" metadataPrefix="oai_dc">http://arXiv.org/oai2</oai:request>
	<oai:ListRecords>
		<xsl:for-each select="abcd:DataSets/abcd:DataSet/abcd:Units/abcd:Unit">
			<oai:record>
				<oai:header>
					<oai:identifier>urn:gfbio.org:abcd:<xsl:value-of select="translate(normalize-space(abcd:UnitID),' ','')"/>
					</oai:identifier>
					<oai:datestamp>
						<xsl:choose>
							<xsl:when test="matches(../../abcd:Metadata/abcd:RevisionData/abcd:DateModified,'\d{4}-\d{2}-\d{2}')">
								<xsl:value-of select="substring(../../abcd:Metadata/abcd:RevisionData/abcd:DateModified/text(),0,11)"/>
							</xsl:when>
							<xsl:when test="matches(../../abcd:Metadata/abcd:RevisionData/abcd:DateModified,'\d{4}')">
								<xsl:value-of select="substring(../../abcd:Metadata/abcd:RevisionData/abcd:DateModified/text(),0,5)"/>
							</xsl:when>
						</xsl:choose>
					</oai:datestamp><!-- -->
				</oai:header>
				<oai:metadata>
					<dataset>
						<dc:title>
							<xsl:choose>
								<xsl:when test="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString">
									<xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="abcd:RecordBasis"/>
								</xsl:otherwise>
							</xsl:choose> [<xsl:value-of select="abcd:UnitID"/>]</dc:title>
						<xsl:for-each select="../../abcd:ContentContacts">
							<dc:contributor>
								<xsl:value-of select="abcd:ContentContact/abcd:Name"/>
							</dc:contributor>
						</xsl:for-each>
						<xsl:for-each select="abcd:Gathering/abcd:Agents">
							<dc:contributor>
								<xsl:choose>
									<xsl:when test="abcd:GatheringAgent/abcd:Person/abcd:FullName!=''">
										<xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:FullName"/>
									</xsl:when>
									<xsl:when test="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:InheritedName!=''">
										<xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:InheritedName"/>
										<xsl:if test="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:GivenNames!=''">, <xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:GivenNames"/>
										</xsl:if>
									</xsl:when>
									<xsl:when test="abcd:GatheringAgent/abcd:AgentText!=''">
										<xsl:value-of select="abcd:GatheringAgent/abcd:AgentText"/>
									</xsl:when>
									<xsl:when test="abcd:GatheringAgentsText!=''">
										<xsl:value-of select="abcd:GatheringAgentsText"/>
									</xsl:when>
								</xsl:choose>
							</dc:contributor>
						</xsl:for-each>
						<!---
						<dc:date>
							<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin"/>
						</dc:date>-->
						<dc:publisher>GFBio <xsl:choose>
								<xsl:when test="../../abcd:Metadata/abcd:Owners/abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text">
									<xsl:value-of select="../../abcd:Metadata/abcd:Owners/abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../../abcd:Metadata/abcd:Description/abcd:Representation/abcd:Title"/>
								</xsl:otherwise>
							</xsl:choose>
						</dc:publisher>
						<dataCenter>GFBio <xsl:choose>
								<xsl:when test="../../abcd:Metadata/abcd:Owners/abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text">
									<xsl:value-of select="../../abcd:Metadata/abcd:Owners/abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../../abcd:Metadata/abcd:Description/abcd:Representation/abcd:Title"/>
								</xsl:otherwise>
							</xsl:choose>
						</dataCenter>
						<dc:type>
							<xsl:choose>
								<xsl:when test="contains(abcd:RecordBasis,'Specimen') or contains(abcd:KindOfUnit,'Specimen')">PhysicalObject</xsl:when>
								<xsl:when test="contains(abcd:RecordBasis,'Observation') or contains(abcd:KindOfUnit,'Observation')">Dataset</xsl:when>
								<xsl:when test="contains(abcd:RecordBasis,'Photograph') or contains(abcd:KindOfUnit,'Photograph') or abcd:RecordBasis='MultimediaObject' or abcd:KindOfUnit=MultimediaObject">Image</xsl:when>
							</xsl:choose>
						</dc:type>
						<dc:format>text/html</dc:format>
						<linkage type="metadata">
							<xsl:value-of select="abcd:RecordURI"/>
							<xsl:choose>
								<xsl:when test="abcd:RecordURI">
									<xsl:value-of select="abcd:RecordURI"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../../abcd:Metadata/abcd:Description/abcd:Representation/abcd:URI"/>
								</xsl:otherwise>
							</xsl:choose>
						</linkage>
						<dc:identifier>
							<xsl:value-of select="abcd:UnitID"/>
						</dc:identifier>
						<!-- 
						<dc:identifier>
							<xsl:value-of select="abcd:SourceInstitutionID"/>:<xsl:value-of select="abcd:SourceID"/>:<xsl:value-of select="abcd:UnitID"/>
						</dc:identifier>
						 -->
						<dc:coverage>
							<xsl:if test="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal!=''">
								<northBoundLatitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal"/>
								</northBoundLatitude>
								<westBoundLongitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LongitudeDecimal"/>
								</westBoundLongitude>
								<southBoundLatitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal"/>
								</southBoundLatitude>
								<eastBoundLongitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LongitudeDecimal"/>
								</eastBoundLongitude>								
							</xsl:if>
							<xsl:choose>
								<xsl:when test="abcd:Gathering/abcd:Country/abcd:Name">
									<location>
										<xsl:value-of select="abcd:Gathering/abcd:Country/abcd:Name"/>
									</location>
								</xsl:when>
								<xsl:when test="abcd:Gathering/abcd:Country/abcd:ISO3166Code!=''">
									<location>
										<xsl:value-of select="abcd:Gathering/abcd:Country/abcd:ISO3166Code"/>
									</location>
								</xsl:when>
							</xsl:choose>
							<xsl:if test="abcd:Gathering/abcd:LocalityText!=''">
								<location>
									<xsl:value-of select="abcd:Gathering/abcd:LocalityText"/>
								</location>
							</xsl:if>
							<xsl:for-each select="abcd:Gathering/abcd:NamedAreas/abcd:NamedArea">
								<xsl:if test="../../abcd:LocalityText!=abcd:AreaName and ../../abcd:Country/abcd:Name!=abcd:AreaName">
									<location>
										<xsl:value-of select="abcd:AreaName"/>
									</location>
								</xsl:if>
							</xsl:for-each>
							<xsl:choose>
								<xsl:when test="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin!=''">
									<startDate>
										<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin"/>
									</startDate>
								</xsl:when>
								<xsl:when test="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeEnd!=''">
									<endDate>
										<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeEnd"/>
									</endDate>
								</xsl:when>
								<xsl:when test="abcd:Gathering/abcd:DateTime/abcd:DateText!=''">
									<startDate>
										<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:DateText[1]"/>
									</startDate>
									<endDate>
										<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:DateText"/>
									</endDate>
								</xsl:when>
							</xsl:choose>
						</dc:coverage>
						<xsl:for-each select="abcd:Identifications/abcd:Identification">
							<dc:subject type="taxonomy">
								<xsl:value-of select="abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString"/>
							</dc:subject>
							<xsl:for-each select="abcd:Result/abcd:TaxonIdentified/abcd:HigherTaxa/abcd:HigherTaxon">
								<dc:subject type="taxonomy">
									<xsl:value-of select="abcd:HigherTaxonName"/>
								</dc:subject>
							</xsl:for-each>
						</xsl:for-each>
						<xsl:if test="../../abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Text">
							<dc:rights>
								<xsl:value-of select="../../abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Text"/>
								<xsl:if test="../../abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Details">, <xsl:value-of select="../../abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Details"/>
								</xsl:if>
								<xsl:if test="../../abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:URI"> <xsl:value-of select="../../abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:URI"/>
								</xsl:if>
							</dc:rights>
						</xsl:if>
						<xsl:if test="../../abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:Text">
							<dc:rights>
								<xsl:value-of select="../../abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:Text"/>
								<xsl:if test="../../abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:Details">, <xsl:value-of select="../../abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:Details"/>
								</xsl:if>
								<xsl:if test="../../abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:URI"> <xsl:value-of select="../../abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:URI"/>
								</xsl:if>
							</dc:rights>
						</xsl:if>
					</dataset>
				</oai:metadata>
			</oai:record>
			</xsl:for-each>
		</oai:ListRecords>
		</oai:OAI-PMH>
	</xsl:template>
</xsl:stylesheet>
