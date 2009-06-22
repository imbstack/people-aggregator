<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='xml' version='1.0' encoding='utf-8' indent='yes'/>
<xsl:template match="//person">
<profile>
    <field name="nsid" section="flickr" value="{@nsid}" />
<xsl:for-each select="*[string-length() &gt; 1]">
  <!-- Also retain all fields as Flickr -->
     <xsl:call-template name="paField">
      <xsl:with-param name="name">
        <xsl:value-of select="name()" />
      </xsl:with-param> 
    </xsl:call-template>
</xsl:for-each>
</profile>
</xsl:template>

<xsl:template name="paField">
<xsl:param name="name">TEST</xsl:param>
<xsl:param name="section">flickr</xsl:param>
<xsl:if test="count(*) &gt; 0">
<field 
  name="{$name}" 
  section="{$section}">
  <xsl:copy-of select="*" />
  </field><xsl:text>
  </xsl:text>
</xsl:if>
<xsl:if test="count(*) = 0">
<field 
  name="{$name}" 
  value="{.}" 
  section="{$section}" /><xsl:text>
  </xsl:text>
</xsl:if>
</xsl:template>
</xsl:stylesheet>