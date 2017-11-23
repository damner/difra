<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="html-body">
		<body>
			<div id="left">
				<xsl:apply-templates select="root/menu[@instance='adm']">
					<xsl:with-param name="auto" select="0"/>
				</xsl:apply-templates>
				<div id="signature">
					<a href="http://www.difra.org/">
						<span id="signature_difra">
							<xsl:value-of select="/root/@frameworkLong"/>
						</span>
					</a>
				</div>
			</div>
			<xsl:call-template name="content-wrapper"/>
			<!--
			<div id="right">
				Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam suscipit, tellus quis
				volutpat malesuada, urna arcu fringilla
				arcu, non
				bibendum tortor magna id tellus. Praesent luctus vestibulum iaculis. In condimentum diam
				eget felis faucibus a pretium tortor
				accumsan.
				Praesent pharetra hendrerit hendrerit. Vestibulum ante ipsum primis in faucibus orci
				luctus et ultrices posuere cubilia Curae;
				Pellentesque
				in nulla arcu, interdum bibendum ligula. Quisque ultricies, risus vel molestie cursus,
				leo ipsum consequat augue, ac gravida leo
				diam ac
				lacus. Praesent placerat bibendum diam, nec vehicula ante molestie ut. Mauris fermentum,
				lectus vel dapibus blandit, libero arcu
				iaculis
				dolor, quis tristique justo dui id sem. Mauris tincidunt scelerisque interdum. Nulla
				laoreet turpis non ante pharetra tincidunt.
				Nam
				condimentum, neque sit amet hendrerit malesuada, velit nisl varius eros, a adipiscing
				orci magna sit amet ante.
			</div>
			-->
		</body>
	</xsl:template>

	<xsl:template name="html-body-switcher">
		<body>
			<xsl:call-template name="content-wrapper"/>
			<xsl:apply-templates select="root/menu[@instance='adm']">
				<xsl:with-param name="auto" select="0"/>
			</xsl:apply-templates>
		</body>
	</xsl:template>

</xsl:stylesheet>
