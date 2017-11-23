<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="locales">
		<h2>
			<xsl:value-of select="$locale/adm/locales/title"/>
		</h2>
		<table class="top-align">
			<thead>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/locales/key"/>
					</th>
					<xsl:for-each select="locale">
						<th>
							<xsl:value-of select="@name"/>
						</th>
					</xsl:for-each>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="all/item">
					<xsl:sort select="@key"/>
					<xsl:variable name="key" select="@key"/>
					<tr>
						<td>
							<span style="white-space: nowrap;">
								<xsl:value-of select="@path"/>
							</span>
							<br/>
							<small>
								<xsl:value-of select="@attributes"/>
							</small>
						</td>
						<xsl:for-each select="../../locale">
							<td>
								<xsl:value-of select="item[@key=$key]/@value"/>
							</td>
						</xsl:for-each>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>

</xsl:stylesheet>
