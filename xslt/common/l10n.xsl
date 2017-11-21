<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:exsl="http://exslt.org/common" xmlns:dyn="http://exslt.org/dynamic" xmlns:func="http://exslt.org/functions">

    <xsl:template name="l10n">
        <xsl:param name="node"/>
        <xsl:param name="data" select="''"/>

        <xsl:variable name="values" select="exsl:node-set($data)/*[local-name()='values']"/>

        <xsl:variable name="values2">
            <xsl:for-each select="$values/@*|$values/*">
                <xsl:variable name="name1" select="local-name()"/>
                <xsl:variable name="name2" select="substring-after($name1, 'plural-')"/>
                <xsl:variable name="name3" select="substring-after($name1, 'filter-')"/>
                <xsl:variable name="name" select="substring-before(concat(normalize-space(concat($name2, ' ', $name3, ' ', $name1)), ' '), ' ')"/>

                <xsl:element name="{$name}">
                    <xsl:if test="not($name2='')">
                        <xsl:attribute name="plural">
                            <xsl:value-of select="1"/>
                        </xsl:attribute>
                    </xsl:if>
                    <xsl:if test="not($name3='')">
                        <xsl:attribute name="filter">
                            <xsl:value-of select="1"/>
                        </xsl:attribute>
                    </xsl:if>

                    <xsl:choose>
                        <xsl:when test="self::*">
                            <!--node-->
                            <xsl:copy-of select="node()"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <!--attribute-->
                            <xsl:value-of select="."/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:element>
            </xsl:for-each>
        </xsl:variable>

        <xsl:variable name="where">
            <xsl:for-each select="exsl:node-set($values2)/*[@filter]">
                <xsl:text> and @</xsl:text>
                <xsl:value-of select="local-name()"/>
                <xsl:text>="</xsl:text>
                <xsl:value-of select="text()"/>
                <xsl:text>"</xsl:text>
            </xsl:for-each>

            <xsl:variable name="lang" select="/root/@lang"/>

            <xsl:for-each select="exsl:node-set($values2)/*[@plural]">
                <xsl:text> and @plural-</xsl:text>
                <xsl:value-of select="local-name()"/>
                <xsl:text>="</xsl:text>
                <xsl:call-template name="l10n-plural-n">
                    <xsl:with-param name="lang" select="$lang"/>
                    <xsl:with-param name="n" select="text()"/>
                </xsl:call-template>
                <xsl:text>"</xsl:text>
            </xsl:for-each>
        </xsl:variable>

        <xsl:variable name="filtered" select="dyn:evaluate(concat('exsl:node-set($node)[true() ', $where, ']'))"/>

        <xsl:apply-templates select="$filtered[last()]/node()" mode="l10n">
            <xsl:with-param name="values" select="$values2"/>
        </xsl:apply-templates>
    </xsl:template>

    <xsl:template match="@*|node()" mode="l10n">
        <xsl:param name="values"/>
        <xsl:copy>
            <xsl:apply-templates select="@*|node()" mode="l10n">
                <xsl:with-param name="values" select="$values"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="*[starts-with(local-name(), 'v-')]" mode="l10n">
        <xsl:param name="values"/>
        <xsl:variable name="key" select="substring(local-name(), 3)"/>
        <xsl:copy-of select="exsl:node-set($values)/*[local-name()=$key]/node()"/>
    </xsl:template>

    <xsl:template name="l10n-plural-n">
        <xsl:param name="lang"/>
        <xsl:param name="n"/>

        <xsl:variable name="number" select="number($n)"/>

        <xsl:choose>
            <xsl:when test="$lang='ru_RU'">
                <xsl:choose>
                    <xsl:when test="$number > 10 and floor(($number mod 100) div 10) = 1">
                        <xsl:text>5</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:choose>
                            <xsl:when test="$number mod 10 = 1">
                                <xsl:text>1</xsl:text>
                            </xsl:when>
                            <xsl:when test="4 >= $number mod 10 and $number mod 10 >= 2">
                                <xsl:text>2</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>5</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test="$number=1">
                        <xsl:text>1</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>2</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
