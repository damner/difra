<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="declension">
		<!-- Число -->
		<xsl:param name="number" select="number"/>
		<!-- Имя ноды из lang файла -->
		<xsl:param name="dec_node_name" select="dec_node_name"/>
		<!-- Выводить на экран значение number -->
		<xsl:param name="view_number" select="1"/>

		<!-- Именительный падеж (пользователь) -->
		<xsl:param name="nominative" select="$locale/declension/*[name()=$dec_node_name]/nominative"/>

		<!-- Родительный падеж, единственное число (пользователя) -->
		<xsl:param name="genitive_singular"
				   select="$locale/declension/*[name()=$dec_node_name]/genitive_singular"/>

		<!-- Родительный падеж, множественное число (пользователей) -->
		<xsl:param name="genitive_plural" select="$locale/declension/*[name()=$dec_node_name]/genitive_plural"/>

		<xsl:variable name="last_digit">
			<xsl:choose>
				<xsl:when test="$number>0">
					<xsl:value-of select="$number mod 10"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="-number($number) mod 10"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="last_two_digits">
			<xsl:choose>
				<xsl:when test="$number>0">
					<xsl:value-of select="$number mod 100"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="-number($number) mod 100"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:choose>
			<xsl:when test="$last_digit = 1 and $last_two_digits != 11">
				<xsl:if test="$view_number=1">
					<xsl:value-of select="$number"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="$nominative"/>
			</xsl:when>
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12 or $last_digit = 3 and $last_two_digits != 13
                              or $last_digit = 4 and $last_two_digits != 14">
				<xsl:if test="$view_number=1">
					<xsl:value-of select="$number"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="$view_number=1">
					<xsl:value-of select="$number"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>
