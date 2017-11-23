<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="paginator">
		<xsl:call-template name="paginator">
			<xsl:with-param name="current" select="@page"/>
			<xsl:with-param name="pages" select="@pages"/>
			<xsl:with-param name="link" select="@link"/>
			<xsl:with-param name="get" select="@get"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template name="paginator">
		<xsl:param name="i" select="1"/>
		<xsl:param name="pages"/>
		<xsl:param name="link"/>
		<xsl:param name="current"/>
		<xsl:param name="get"/>
		<div class="paginator">
			<xsl:call-template name="paginatorSub">
				<xsl:with-param name="i" select="$i"/>
				<xsl:with-param name="pages" select="$pages"/>
				<xsl:with-param name="link" select="$link"/>
				<xsl:with-param name="current" select="$current"/>
				<xsl:with-param name="get" select="$get"/>
			</xsl:call-template>
		</div>
	</xsl:template>

	<xsl:template name="paginatorSub">
		<xsl:param name="i" select="1"/>
		<xsl:param name="pages"/>
		<xsl:param name="link"/>
		<xsl:param name="current"/>
		<xsl:param name="get"/>

		<xsl:if test="$pages &gt; 1 or $current != 1">
			<!-- кнопка назад -->
			<xsl:if test="$i = 1 and $current &gt; 1">
				<div class="pagerItem pagerPrev">
					<xsl:variable name="prev" select="$current - 1"/>
					<xsl:choose>
						<xsl:when test="$prev = 1">
							<a href="{$link}">←</a>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="paginatorLink">
								<xsl:with-param name="get" select="$get"/>
								<xsl:with-param name="link" select="$link"/>
								<xsl:with-param name="page" select="$prev"/>
								<xsl:with-param name="text">←</xsl:with-param>
							</xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:if>

			<div class="pagerItem" id="pagerItem{$i}">
				<xsl:choose>
					<xsl:when test="$i = $current">
						<xsl:attribute name="class">pagerItem pagerSelected</xsl:attribute>
						<xsl:value-of select="$i"/>
					</xsl:when>
					<xsl:when test="$i = 1">
						<a href="{$link}">
							<xsl:value-of select="$i"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="paginatorLink">
							<xsl:with-param name="get" select="$get"/>
							<xsl:with-param name="link" select="$link"/>
							<xsl:with-param name="page" select="$i"/>
							<xsl:with-param name="text" select="$i"/>
						</xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</div>

			<xsl:choose>
				<!-- скипаем от первого в середину -->
				<xsl:when test="$i &lt; $current - 6">
					<div class="pagerItem pagerSkip">…</div>
					<xsl:call-template name="paginatorSub">
						<xsl:with-param name="pages" select="$pages"/>
						<xsl:with-param name="current" select="$current"/>
						<xsl:with-param name="link" select="$link"/>
						<xsl:with-param name="i" select="$current - 4"/>
						<xsl:with-param name="get" select="$get"/>
					</xsl:call-template>
				</xsl:when>
				<!-- скипаем от средних до последних -->
				<xsl:when test="$i &gt; $current + 3 and $i &lt; $pages">
					<div class="pagerItem pagerSkip">…</div>
					<xsl:call-template name="paginatorSub">
						<xsl:with-param name="pages" select="$pages"/>
						<xsl:with-param name="current" select="$current"/>
						<xsl:with-param name="link" select="$link"/>
						<xsl:with-param name="i" select="$pages"/>
						<xsl:with-param name="get" select="$get"/>
					</xsl:call-template>
				</xsl:when>
				<!-- показываемые страницы -->
				<xsl:when test="$i &lt; $pages">
					<xsl:call-template name="paginatorSub">
						<xsl:with-param name="pages" select="$pages"/>
						<xsl:with-param name="current" select="$current"/>
						<xsl:with-param name="link" select="$link"/>
						<xsl:with-param name="i" select="$i + 1"/>
						<xsl:with-param name="get" select="$get"/>
					</xsl:call-template>
				</xsl:when>
				<!-- кнопка вперёд -->
				<xsl:otherwise>
					<xsl:if test="$current &lt; $pages">
						<div class="pagerItem pagerNext">
							<xsl:call-template name="paginatorLink">
								<xsl:with-param name="get" select="$get"/>
								<xsl:with-param name="link" select="$link"/>
								<xsl:with-param name="page" select="$current + 1"/>
								<xsl:with-param name="text">→</xsl:with-param>
							</xsl:call-template>
						</div>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<xsl:template name="paginatorLink">
		<xsl:param name="link"/>
		<xsl:param name="page"/>
		<xsl:param name="get"/>
		<xsl:param name="text"/>

		<a>
			<xsl:attribute name="href">
				<xsl:value-of select="$link"/>
				<xsl:choose>
					<xsl:when test="not($get) or ($get='')">
						<xsl:text>/page/</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$get"/>
						<xsl:text>page=</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:value-of select="$page"/>
			</xsl:attribute>
			<xsl:value-of select="$text"/>
		</a>
	</xsl:template>

</xsl:stylesheet>
