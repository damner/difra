<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
	<xsl:template match="sitemap:sitemapindex" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
		<html>
			<head>
				<title>Sitemap Index</title>
			</head>
			<body>
				<xsl:apply-templates/>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="sitemap:sitemap/sitemap:loc" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
		<xsl:variable name="link" select="substring-before(.,'.xml')"/>
		<a href="{$link}.html">
			<xsl:text>Sitemap page </xsl:text>
			<xsl:value-of select="substring-after($link,'/sitemap-')"/>
		</a>
		<br/>
	</xsl:template>

	<xsl:template match="sitemap:sitemap/*" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"/>
</xsl:stylesheet>