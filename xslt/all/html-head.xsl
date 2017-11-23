<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="html-head">
		<head>
			<title>
				<xsl:call-template name="html-head-title"/>
			</title>
			<meta http-equiv="X-UA-Compatible" content="IE=edge"/>

			<xsl:variable name="instance">
				<xsl:choose>
					<xsl:when test="/root/@instance">
						<xsl:value-of select="/root/@instance"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>main</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>

			<!-- keywords -->
			<xsl:choose>
				<xsl:when test="/root/content/@keywords and not(/root/content/@keywords='')">
					<meta name="keywords" content="{/root/content/@keywords}"/>
				</xsl:when>
				<xsl:when test="$locale/default/keywords">
					<meta name="keywords" content="{$locale/default/keywords}"/>
				</xsl:when>
			</xsl:choose>
			<!-- description -->
			<xsl:choose>
				<xsl:when test="/root/content/@description and not(/root/content/@description='')">
					<meta name="description" content="{/root/content/@description}"/>
				</xsl:when>
				<xsl:when test="$locale/default/description">
					<meta name="description" content="{$locale/default/description}"/>
				</xsl:when>
			</xsl:choose>

			<link type="text/css" href="{/root/@urlprefix}/css/{$instance}.css?{/root/@build}" rel="stylesheet"/>
			<script type="text/javascript" src="{/root/@urlprefix}/js/{$instance}.js?{/root/@build}"/>

			<xsl:if test="/root/@debugConsole>0">
				<link type="text/css" href="{/root/@urlprefix}/css/console.css?{/root/@build}" rel="stylesheet"/>
				<script type="text/javascript" src="{/root/@urlprefix}/js/console.js?{/root/@build}"/>
			</xsl:if>


			<script type="text/javascript">
				<!--suppress CheckValidXmlInScriptTagBody -->
				<xsl:text>var config={};</xsl:text>
				<!--suppress CheckValidXmlInScriptTagBody -->
				<xsl:value-of select="/root/@jsConfig"/>
			</script>

			<xsl:call-template name="html-head-additional"/>
		</head>
	</xsl:template>

	<xsl:template name="html-head-additional">
	</xsl:template>

	<xsl:template name="html-head-title">
		<xsl:choose>
			<xsl:when test="/root/content/@title and not(/root/content/@title='')">
				<xsl:value-of select="/root/content/@title"/>
				<xsl:if test="/root/content/@pageTitle and not(/root/content/@pageTitle='')">
					<xsl:text>&#160;—&#160;</xsl:text>
					<xsl:value-of select="/root/content/@pageTitle"/>
				</xsl:if>
			</xsl:when>
			<xsl:when test="$locale/default/title and not($locale/default/title='')">
				<xsl:if test="/root/content/@pageTitle and not(/root/content/@pageTitle='')">
					<xsl:value-of select="/root/content/@pageTitle"/>
					<xsl:text>&#160;—&#160;</xsl:text>
				</xsl:if>
				<xsl:value-of select="$locale/default/title"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="/root/content/@pageTitle and not(/root/content/@pageTitle='')">
					<xsl:value-of select="/root/content/@pageTitle"/>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>
