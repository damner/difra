<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template name="input-datetime-format">
        <xsl:param name="datetime"/>
        <xsl:choose>
            <xsl:when test="substring-after($datetime, ' ')!=''">
                <xsl:value-of select="substring-before($datetime, ' ')"/>
                <xsl:text>T</xsl:text>
                <xsl:value-of select="substring-after($datetime, ' ')"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$datetime"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
