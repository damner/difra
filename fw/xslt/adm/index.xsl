<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="index">
		<h1>
			<xsl:value-of select="$locale/adm/stats/h2"/>
		</h1>

		<h2>Difra</h2>
		<table class="summary">
			<colgroup>
				<col style="width:250px"/>
				<col/>
			</colgroup>
			<tbody>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/summary/platform-version"/>
				</th>
				<td>
					<xsl:value-of select="stats/difra/@version"/>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/summary/loaded-plugins"/>
				</th>
				<td>
					<xsl:choose>
						<xsl:when test="stats/plugins/@loaded and not(stats/plugins/@loaded='')">
							<xsl:value-of select="stats/plugins/@loaded"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>—</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/summary/cache-type"/>
				</th>
				<td>
					<xsl:value-of select="stats/cache/@type"/>
				</td>
			</tr>
			</tbody>
		</table>
		<h2>
			<xsl:value-of select="$locale/adm/stats/server/title"/>
		</h2>
		<table class="summary">
			<colgroup>
				<col style="width:250px"/>
				<col/>
			</colgroup>
			<tbody>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/server/webserver"/>
				</th>
				<td>
					<xsl:value-of select="stats/system/webserver"/>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/server/phpversion"/>
				</th>
				<td>
					<xsl:value-of select="stats/system/phpversion"/>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/permissions"/>
				</th>
				<td>
					<xsl:choose>
						<xsl:when test="stats/permissions/@*">
							<xsl:for-each select="stats/permissions/@*">
								<div style="color:red">
									<xsl:value-of select="."/>
								</div>
							</xsl:for-each>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$locale/adm/stats/permissions-ok"/>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
			</tbody>
		</table>
		<h2>
			<xsl:value-of select="$locale/adm/stats/extensions/title"/>
		</h2>
		<table class="summary">
			<colgroup>
				<col style="width:250px"/>
				<col/>
			</colgroup>
			<tbody>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/extensions/required-extensions"/>
				</th>
				<td>
					<xsl:choose>
						<xsl:when test="not(stats/extensions/@ok='')">
							<xsl:value-of select="stats/extensions/@ok"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>—</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
			<xsl:if test="not(stats/extensions/@required='')">
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/extensions/missing-extensions"/>
					</th>
					<td style="color:red">
						<xsl:value-of select="stats/extensions/@required"/>
					</td>
				</tr>
			</xsl:if>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/extensions/extra-extensions"/>
				</th>
				<td>
					<xsl:choose>
						<xsl:when test="not(stats/extensions/@extra='')">
							<xsl:value-of select="stats/extensions/@extra"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>—</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
			</tbody>
		</table>
		<h2>
			<xsl:value-of select="$locale/adm/stats/database/title"/>
		</h2>
		<xsl:choose>
			<xsl:when test="stats/mysql/@error">
				<div class="error">
					<xsl:value-of select="stats/mysql/@error"/>
				</div>
			</xsl:when>
			<xsl:when test="count(stats/mysql/table[@diff=1])=0 and count(stats/mysql/table[@nodef=1])=0 and count(stats/mysql/table[@nogoal=1])=0">
				<div class="message">
					<xsl:value-of select="$locale/adm/stats/database/status-ok"/>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="stats/mysql/table" mode="diff"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="stats/mysql/table" mode="diff">
		<xsl:choose>
			<xsl:when test="@diff=1">
				<table>
					<colgroup>
						<col style="width:250px"/>
						<col/>
					</colgroup>
					<tbody>
					<tr>
						<td colspan="2">
							<xsl:text>Table </xsl:text>
							<strong>`<xsl:value-of select="@name"/>`
							</strong>
							<xsl:text> diff:</xsl:text>

						</td>
					</tr>
					<tr>
						<td style="width:50%">Current</td>
						<td>Described</td>
					</tr>
					<xsl:for-each select="diff">
						<xsl:choose>
							<xsl:when test="@sign='='">
								<tr class="small bg-green">
									<td>
										<xsl:value-of select="@value"/>
									</td>
									<td>
										<xsl:value-of select="@value"/>
									</td>
								</tr>
							</xsl:when>
							<xsl:when test="@sign='-'">
								<tr class="small bg-red">
									<td>
										<xsl:value-of select="@value"/>
									</td>
									<td>
									</td>
								</tr>
							</xsl:when>
							<xsl:when test="@sign='+'">
								<tr class="small bg-red">
									<td>
									</td>
									<td>
										<xsl:value-of select="@value"/>
									</td>
								</tr>
							</xsl:when>
						</xsl:choose>
					</xsl:for-each>
					</tbody>
				</table>
			</xsl:when>
			<xsl:when test="@nogoal=1">
				<div class="message error">
					<xsl:text>Table `</xsl:text>
					<xsl:value-of select="@name"/>
					<xsl:text>` is not described.</xsl:text>
				</div>
			</xsl:when>
			<xsl:when test="@nodef=1">
				<div class="message error">
					<xsl:text>Table `</xsl:text>
					<xsl:value-of select="@name"/>
					<xsl:text>` does not exist.</xsl:text>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<div class="message">
					<xsl:text>Table `</xsl:text>
					<xsl:value-of select="@name"/>
					<xsl:text>` is ok.</xsl:text>
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
