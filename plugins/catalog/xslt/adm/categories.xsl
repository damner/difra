<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="CatalogCategories">
		<h2>
			<xsl:value-of select="$locale/catalog/adm/title-categories"/>
		</h2>
		<a href="/adm/catalog/categories/add" class="action add">
		</a>
		<xsl:choose>
			<xsl:when test="category">
				<table>
					<colgroup>
						<col/>
						<col style="width: 170px"/>
					</colgroup>
					<tr>
						<th>
							<xsl:value-of select="$locale/catalog/adm/name"/>
						</th>
						<th>
						</th>
					</tr>
					<xsl:call-template name="CatalogSubcategory">
						<xsl:with-param name="maxdepth" select="@maxdepth"/>
					</xsl:call-template>
				</table>
			</xsl:when>
			<xsl:otherwise>
				<span class="message">
					<xsl:value-of select="$locale/catalog/adm/no-categories"/>
				</span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="CatalogSubcategory">
		<xsl:param name="parent" select="0"/>
		<xsl:param name="depth" select="0"/>
		<xsl:param name="node" select="."/>
		<xsl:param name="unique" select="sub_"/>
		<xsl:param name="maxdepth" select="0"/>

		<xsl:for-each select="$node/category[@parent=$parent]">
			<tr>
				<xsl:attribute name="id">
					<xsl:value-of select="$unique"/>
					<xsl:value-of select="$parent"/>
					<xsl:text>_</xsl:text>
					<xsl:value-of select="position()"/>
				</xsl:attribute>
				<td style="padding-left: {$depth*40+16}px">
					<a href="/adm/catalog/items/category/{@id}">
						<xsl:value-of select="@name"/>
					</a>
				</td>
				<td class="actions">
					<xsl:choose>
						<xsl:when test="$maxdepth>0 and $depth>=$maxdepth - 1">
							<a href="#" class="action add disabled">
								<xsl:value-of select="$locale/adm/actions/add"/>
							</a>
						</xsl:when>
						<xsl:otherwise>
							<a href="/adm/catalog/categories/add/to/{@id}" class="action add">
								<xsl:value-of select="$locale/adm/actions/add"/>
							</a>
						</xsl:otherwise>
					</xsl:choose>
					<a class="action edit" href="/adm/catalog/categories/edit/{@id}"/>
					<xsl:call-template name="actionUp">
						<xsl:with-param name="link">
							<xsl:text>/adm/catalog/categories/up/</xsl:text>
							<xsl:value-of select="@id"/>
						</xsl:with-param>
						<xsl:with-param name="idPrefix">
							<xsl:value-of select="$unique"/>
							<xsl:value-of select="$parent"/>
							<xsl:text>_</xsl:text>
						</xsl:with-param>
					</xsl:call-template>
					<xsl:call-template name="actionDown">
						<xsl:with-param name="link">
							<xsl:text>/adm/catalog/categories/down/</xsl:text>
							<xsl:value-of select="@id"/>
						</xsl:with-param>
						<xsl:with-param name="idPrefix">
							<xsl:value-of select="$unique"/>
							<xsl:value-of select="$parent"/>
							<xsl:text>_</xsl:text>
						</xsl:with-param>
					</xsl:call-template>
					<a class="action delete" href="/adm/catalog/categories/delete/{@id}"/>
				</td>
				<xsl:call-template name="CatalogSubcategory">
					<xsl:with-param name="depth" select="$depth+1"/>
					<xsl:with-param name="maxdepth" select="$maxdepth"/>
					<xsl:with-param name="parent" select="@id"/>
					<xsl:with-param name="node" select=".."/>
					<xsl:with-param name="unique">
						<xsl:value-of select="$unique"/>
						<xsl:value-of select="$parent"/>
						<xsl:text>_</xsl:text>
					</xsl:with-param>
				</xsl:call-template>
			</tr>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
