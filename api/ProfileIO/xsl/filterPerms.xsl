<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='xml' encoding='utf-8' indent='yes' />

<xsl:param name="perm0"></xsl:param>
<xsl:param name="perm1"></xsl:param>
<xsl:param name="perm2"></xsl:param>

<xsl:template match="/">
<profile>
<xsl:if test="$perm0">
  <xsl:copy-of select="//field[@perm=0]" />
</xsl:if>
<xsl:if test="$perm1">
  <xsl:copy-of select="//field[@perm=1]" />
</xsl:if>
<xsl:if test="$perm2">
  <xsl:copy-of select="//field[@perm=2]" />
</xsl:if>

<xsl:copy-of select="//relation" />

</profile>
</xsl:template>
</xsl:stylesheet>