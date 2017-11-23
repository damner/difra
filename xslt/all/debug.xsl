<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/root/debug">
		<div id="debug">
			<xsl:choose>
				<xsl:when test="/root/@standalone">
					<xsl:attribute name="class">switcher standalone</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">
						<xsl:text>switcher panel</xsl:text>
						<xsl:if test="*/@class='errors'">
							<xsl:text> max</xsl:text>
						</xsl:if>
					</xsl:attribute>
					<div id="debug_toggle" onclick="debug.toggle()"/>
				</xsl:otherwise>
			</xsl:choose>
			<div id="debug_toggles">
				<input type="checkbox" name="debug" onclick="debug.toggleDebugger()">
					<xsl:if test="/root/@debug=1">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</input>
				<xsl:text> debug mode </xsl:text>
				<xsl:if test="/root/@debug=1">
					<input type="checkbox" name="debugConsole" onclick="debug.toggleConsole()">
						<xsl:if test="/root/@debugConsole=2">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:text> debug console </xsl:text>
				</xsl:if>
				<input type="checkbox" name="caches" onclick="debug.toggleCache()">
					<xsl:if test="/root/@caches=1">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</input>
				<xsl:text> caches </xsl:text>
			</div>
			<xsl:if test="/root/@debugConsole=2">
				<div id="debug_body">
					<ul>
						<li>
							<xsl:if test="not(*[@class='errors'])">
								<xsl:attribute name="class">selected</xsl:attribute>
							</xsl:if>
							<span class="tab-title">Все</span>
							<div class="tab-content">
								<table>
									<xsl:apply-templates select="*" mode="debugLine"/>
								</table>
							</div>
						</li>
						<li>
							<span class="tab-title">Сообщения</span>
							<div class="tab-content">
								<table>
									<xsl:apply-templates select="*[@class='messages']" mode="debugLine"/>
								</table>
							</div>
						</li>
						<li>
							<xsl:if test="*[@class='errors']">
								<xsl:attribute name="class">selected</xsl:attribute>
							</xsl:if>
							<span class="tab-title">Ошибки</span>
							<div class="tab-content">
								<table>
									<xsl:apply-templates select="*[@class='errors']" mode="debugLine"/>
								</table>
							</div>
						</li>
						<li>
							<span class="tab-title">События</span>
							<div class="tab-content">
								<table>
									<xsl:apply-templates select="*[@class='events']" mode="debugLine"/>
								</table>
							</div>
						</li>
						<li>
							<span class="tab-title">База данных</span>
							<div class="tab-content">
								<table>
									<xsl:apply-templates select="*[@class='db']" mode="debugLine"/>
								</table>
							</div>
						</li>
						<li>
							<span class="tab-title">Запросы</span>
							<div class="tab-content">
								<table id="debug-requests"/>
							</div>
						</li>
					</ul>
				</div>
			</xsl:if>
		</div>
	</xsl:template>

	<xsl:template match="*" mode="debugLine">
		<tr class="cl_{@class}">
			<td>
				<xsl:value-of select="position()"/>
			</td>
			<td>
				<xsl:value-of select="@timer"/>
				<xsl:text> </xsl:text>
			</td>
			<td>
				<xsl:value-of select="concat(translate(substring(@class, 1, 1), 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'), substring(@class, 2))"/>
			</td>
			<xsl:choose>
				<xsl:when test="@class='errors'">
					<td>
						<strong>
							<xsl:value-of select="@error"/>
						</strong>
						<xsl:text> in </xsl:text>
						<strong>
							<xsl:value-of select="@file"/>
						</strong>
						<xsl:text>:</xsl:text>
						<strong>
							<xsl:value-of select="@line"/>
						</strong>
						<br/>
						<strong>
							<xsl:value-of select="@message"/>
						</strong>
						<table>
							<xsl:for-each select="traceback/*">
								<tr>
									<td>
										<xsl:if test="@file or @line">
											<xsl:value-of select="@file"/>
											<xsl:text>:</xsl:text>
											<xsl:value-of select="@line"/>
											<xsl:text> </xsl:text>
										</xsl:if>
										<xsl:value-of select="@class"/>
										<xsl:value-of select="@type"/>
										<xsl:value-of select="@function"/>
										<xsl:for-each select="args">
											<xsl:text>(</xsl:text>
											<xsl:call-template name="debug_args"/>
											<xsl:text>)</xsl:text>
										</xsl:for-each>
									</td>
								</tr>
							</xsl:for-each>
						</table>
					</td>
				</xsl:when>
				<xsl:otherwise>
					<td>
						<xsl:value-of select="@message"/>
					</td>
				</xsl:otherwise>
			</xsl:choose>
		</tr>
	</xsl:template>

	<xsl:template name="debug_args">
		<xsl:choose>
			<xsl:when test="count(@*)&lt;2">
				<xsl:value-of select="@*"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>array(</xsl:text>
				<xsl:for-each select="@*">
					<xsl:value-of select="."/>
					<xsl:if test="not(position()=last())">
						<xsl:text>,</xsl:text>
					</xsl:if>
				</xsl:for-each>
				<xsl:text>)</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="not(position()=last())">,</xsl:if>
	</xsl:template>

</xsl:stylesheet>
