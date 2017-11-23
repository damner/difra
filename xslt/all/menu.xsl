<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="menu">
		<xsl:param name="auto" select="1"/>
		<xsl:if test="$auto=0">
			<div class="menu switcher">
				<xsl:if test="@instance">
					<xsl:attribute name="id">
						<xsl:text>menu_</xsl:text>
						<xsl:value-of select="@instance"/>
					</xsl:attribute>
				</xsl:if>
				<xsl:call-template name="common_menu"/>
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template name="common_menu">
		<xsl:if test="*">
			<xsl:variable name="instance" select="/root/menu/@instance"/>
			<ul>
				<xsl:for-each select="*[not(@hidden=1) and (not(@href='') or ./*)]">
					<xsl:sort select="@priority" order="descending" data-type="number"/>
					<xsl:variable name="selected">
						<xsl:choose>
							<xsl:when test="@pseudoHref=''">
								<xsl:text>0</xsl:text>
							</xsl:when>
							<xsl:when test="@pseudoHref=/root/@controllerUri">
								<text>2</text>
							</xsl:when>
							<xsl:when
								test="substring(/root/@controllerUri,1,string-length(@pseudoHref))=@pseudoHref">
								<text>1</text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>0</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<li id="{@id}">
						<xsl:copy-of select="@pseudoHref"/>
						<xsl:copy-of select="/root/@controllerUri"/>
						<xsl:attribute name="class">
							<xsl:if test="@sup=1">
								<xsl:text>sup</xsl:text>
								<xsl:if test="$selected>0">
									<xsl:text> </xsl:text>
								</xsl:if>
							</xsl:if>
							<xsl:choose>
								<xsl:when test="$selected=1">
									<xsl:text>selected</xsl:text>
								</xsl:when>
								<xsl:when test="$selected=2">
									<xsl:text>selected match</xsl:text>
								</xsl:when>
							</xsl:choose>
						</xsl:attribute>
						<!-- получаем название пункта меню -->
						<xsl:variable name="title">
							<xsl:choose>
								<xsl:when test="@title">
									<xsl:value-of select="@title"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:variable name="id" select="@id"/>
									<xsl:choose>
										<xsl:when test="$locale/menu/*[name()=$instance]/*[name()=$id]">
											<xsl:value-of select="$locale/menu/*[name()=$instance]/*[name()=$id]"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="name()"/>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:choose>
							<xsl:when test="@href=''">
								<span class="menu_nolink">
									<xsl:value-of select="$title"/>
								</span>
							</xsl:when>
							<xsl:when test="@href and not(@href=/root/@menuitem)">
								<a href="{@href}">
									<xsl:value-of select="$title"/>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<span class="menu_active">
									<xsl:value-of select="$title"/>
								</span>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:call-template name="common_menu"/>
					</li>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
