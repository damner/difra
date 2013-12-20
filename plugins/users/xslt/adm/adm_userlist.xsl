<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="userList">
		<h2>
			<xsl:value-of select="$locale/auth/adm/h2-title"/>
		</h2>
		<xsl:choose>
			<xsl:when test="not(item)">
				<xsl:value-of select="$locale/auth/adm/users-empty"/>
			</xsl:when>
			<xsl:otherwise>
				<table>
					<colgroup>
						<col style="width: 30px"/>
						<col/>
						<col style="width: 180px"/>
						<col style="width: 180px"/>
						<col style="width: 140px"/>
						<col style="width: 300px"/>
					</colgroup>
					<thead>
						<tr>
							<th>
								<xsl:value-of select="$locale/auth/adm/id"/>
							</th>
							<th>
								<xsl:value-of select="$locale/auth/adm/email"/>
							</th>
							<th>
								<xsl:value-of select="$locale/auth/adm/registered"/>
							</th>
							<th>
								<xsl:value-of select="$locale/auth/adm/logged"/>
							</th>
							<th>
								<xsl:value-of select="$locale/auth/adm/flags"/>
							</th>
							<th>

							</th>
						</tr>
					</thead>
					<tbody>
						<xsl:for-each select="item">
							<tr>
								<td>
									<xsl:value-of select="@id"/>
								</td>
								<td>
									<xsl:value-of select="@email"/>
								</td>
								<td>
									<xsl:value-of select="@registered"/>
								</td>
								<td>
									<xsl:choose>
										<xsl:when
											test="@logged='0000-00-00 00:00:00'">
											<xsl:text>—</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="@logged"/>
										</xsl:otherwise>
									</xsl:choose>
								</td>
								<td>
									<xsl:choose>
										<xsl:when
											test="@banned=1 and @active=0">
											<xsl:value-of
												select="$locale/auth/adm/inactive"/>
											<xsl:text>,&#160;</xsl:text>
											<xsl:value-of
												select="$locale/auth/adm/banned"/>
										</xsl:when>
										<xsl:when test="@banned=1">
											<xsl:value-of
												select="$locale/auth/adm/banned"/>
										</xsl:when>
										<xsl:when test="@active=0">
											<xsl:value-of
												select="$locale/auth/adm/inactive"/>
										</xsl:when>
										<xsl:when test="@moderator=1">
											<xsl:value-of
												select="$locale/auth/adm/moderator_flag"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text>—</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
								</td>
								<td class="actions">
									<xsl:if test="@active=0">
										<a href="/adm/users/list/activate/{@id}" class="button ajaxer">
											<xsl:value-of select="$locale/auth/adm/activate"/>
										</a>
									</xsl:if>

									<xsl:choose>
										<xsl:when test="@banned=1">
											<a href="/adm/users/list/unban/{@id}"
											   class="button ajaxer">
												<xsl:value-of
													select="$locale/auth/adm/unban"/>
											</a>
										</xsl:when>
										<xsl:otherwise>
											<a href="/adm/users/list/ban/{@id}"
											   class="button ajaxer">
												<xsl:value-of
													select="$locale/auth/adm/ban"/>
											</a>
										</xsl:otherwise>
									</xsl:choose>
									<xsl:choose>
										<xsl:when test="@moderator=1">
											<a href="/adm/users/list/unmoderator/{@id}"
											   class="button ajaxer">
												<xsl:value-of
													select="$locale/auth/adm/unModerator"/>
											</a>
										</xsl:when>
										<xsl:otherwise>
											<a href="/adm/users/list/moderator/{@id}"
											   class="button ajaxer">
												<xsl:value-of
													select="$locale/auth/adm/moderator"/>
											</a>
										</xsl:otherwise>
									</xsl:choose>
									<a href="/adm/users/list/edit/{@id}" class="action edit"/>
								</td>
							</tr>
						</xsl:for-each>
					</tbody>
				</table>
			</xsl:otherwise>
		</xsl:choose>

		<xsl:if test="/root/userList/@pages&gt;1">
			<div class="paginator">
				<xsl:call-template name="paginator">
					<xsl:with-param name="link">
						<xsl:value-of select="/root/userList/@link"/>
					</xsl:with-param>
					<xsl:with-param name="pages">
						<xsl:value-of select="/root/userList/@pages"/>
					</xsl:with-param>
					<xsl:with-param name="current">
						<xsl:value-of select="/root/userList/@current"/>
					</xsl:with-param>
				</xsl:call-template>
			</div>
		</xsl:if>

	</xsl:template>
</xsl:stylesheet>
