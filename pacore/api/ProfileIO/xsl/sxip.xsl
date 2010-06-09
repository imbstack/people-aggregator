<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method='xml' version='1.0' encoding='utf-8' indent='yes'/>
<xsl:template match="/">
<profile>
  <xsl:for-each select="//property[@value]">
  <xsl:choose>
<xsl:when test="@name='dix://sxip.net/namePerson/first'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">first_name</xsl:with-param> 
      <xsl:with-param name="section">core</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/namePerson/last'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">last_name</xsl:with-param> 
      <xsl:with-param name="section">core</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/contact/internet/email'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">email</xsl:with-param> 
      <xsl:with-param name="section">core</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/media/image/small'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">picture</xsl:with-param> 
      <xsl:with-param name="section">core</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/internet/web/default'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">homepage</xsl:with-param> 
      <xsl:with-param name="section">general</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/contact/web/Flickr'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">flickr</xsl:with-param> 
      <xsl:with-param name="section">general</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/contact/web/Delicious'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">delicious</xsl:with-param> 
      <xsl:with-param name="section">general</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/company/name'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">company</xsl:with-param> 
      <xsl:with-param name="section">professional</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/company/title'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">title</xsl:with-param> 
      <xsl:with-param name="section">professional</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
    
  <xsl:when test="@name='dix://sxip.net/media/spokenname'">
    <xsl:apply-templates select=".">
      <xsl:with-param name="name">caption</xsl:with-param> 
      <xsl:with-param name="section">general</xsl:with-param> 
    </xsl:apply-templates>
  </xsl:when>
  </xsl:choose>
          
  <!-- Also retaun all fields as SXIP -->
     <xsl:apply-templates select=".">
      <xsl:with-param name="name">
        <xsl:value-of select="@name" />
      </xsl:with-param> 
      <xsl:with-param name="section">
        <xsl:value-of select="$nameSection" />
      </xsl:with-param>
    </xsl:apply-templates>

  </xsl:for-each>
</profile>
</xsl:template>

<xsl:template match="property">
<xsl:param name="name">TEST</xsl:param>
<xsl:param name="section">TEST</xsl:param>
<field 
  name="{$name}" 
  value="{@value}" 
  section="{$section}" /><xsl:text>
  </xsl:text>
</xsl:template>
</xsl:stylesheet>