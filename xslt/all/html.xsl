<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/">
		<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html&gt;&#x0A;</xsl:text>
		<xsl:choose>
			<xsl:when test="/root/@ajax=1 or /root/@switcher=1">
				<html>
					<head>
						<title>
							<xsl:call-template name="html-head-title"/>
						</title>
					</head>
					<xsl:call-template name="html-body-switcher"/>
				</html>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="html"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="html">
		<html lang="{/root/@langShort}">
			<xsl:attribute name="class">
				<xsl:value-of select="/root/@uaClass"/>
			</xsl:attribute>
			<xsl:call-template name="html-head"/>
			<xsl:call-template name="html-body"/>
		</html>
	</xsl:template>

	<xsl:template name="html-body">
		<body>
			<xsl:call-template name="content-wrapper"/>
		</body>
	</xsl:template>

	<xsl:template name="html-body-switcher">
		<body>
			<xsl:call-template name="content-wrapper"/>
		</body>
	</xsl:template>

	<xsl:template name="content-wrapper">
		<div id="content" class="switcher">
			<xsl:call-template name="content"/>
		</div>
		<xsl:apply-templates select="/root/debug"/>
	</xsl:template>

	<xsl:template name="content">
		<xsl:apply-templates select="/root/content/*"/>
	</xsl:template>

</xsl:stylesheet>
