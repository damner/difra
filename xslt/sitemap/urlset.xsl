<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">

	<xsl:template match="sitemap:urlset" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
		<html>
			<head>
				<title>Sitemap</title>
			</head>
			<body>
				<xsl:apply-templates/>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="sitemap:url/sitemap:loc" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
		<xsl:variable name="link" select="."/>
		<a href="{$link}">
			<xsl:value-of select="$link"/>
		</a>
		<br/>
	</xsl:template>

	<xsl:template match="sitemap:url/*" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"/>

</xsl:stylesheet>
