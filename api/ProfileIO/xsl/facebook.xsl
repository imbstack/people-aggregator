<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='xml' version='1.0' encoding='utf-8' indent='yes'/>
<xsl:template match="//result_elt">
<profile>
<xsl:for-each select="*[string-length() &gt; 1]">
<!-- 
select="*[string-length() &gt; 1]" 
ensures we only handle elemetns that HAVE content 
-->
<!-- 
* we no longer actually overwrite internal PA fields here 
* so the following code is commented out
-->

<!--
<xsl:choose>
  <xsl:when test="name()='first_name'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">first_name</xsl:with-param> 
      <xsl:with-param name="section">core</xsl:with-param> 
    </xsl:call-template>
    <xsl:call-template name="paField">
      <xsl:with-param name="name">first_name</xsl:with-param> 
      <xsl:with-param name="section">basic</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='last_name'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">last_name</xsl:with-param> 
      <xsl:with-param name="section">core</xsl:with-param> 
    </xsl:call-template>
    <xsl:call-template name="paField">
      <xsl:with-param name="name">last_name</xsl:with-param> 
      <xsl:with-param name="section">basic</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='gender'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">sex</xsl:with-param> 
      <xsl:with-param name="section">general</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='clubs'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">activities</xsl:with-param> 
      <xsl:with-param name="section">personal</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='tv'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">tv_shows</xsl:with-param> 
      <xsl:with-param name="section">personal</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='movies'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">movies</xsl:with-param>
      <xsl:with-param name="section">personal</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='books'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">books</xsl:with-param>
      <xsl:with-param name="section">personal</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='interests'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">passions</xsl:with-param>
      <xsl:with-param name="section">personal</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='political'">
    <xsl:call-template name="paField">
      <xsl:with-param name="name">political_view</xsl:with-param>
      <xsl:with-param name="section">personal</xsl:with-param> 
    </xsl:call-template>
  </xsl:when>

  <xsl:when test="name()='current_location'">
    <xsl:apply-templates select="*" />
  </xsl:when>

</xsl:choose>
-->

  <!-- Also retain all fields as Facebook -->
     <xsl:call-template name="paField">
      <xsl:with-param name="name">
        <xsl:value-of select="name()" />
      </xsl:with-param> 
      <xsl:with-param name="section">
        <xsl:value-of select="$nameSection" />
      </xsl:with-param>
    </xsl:call-template>
</xsl:for-each>
</profile>
</xsl:template>

<xsl:template match="//current_location/city">
  <xsl:call-template name="paField">
    <xsl:with-param name="name">city</xsl:with-param> 
    <xsl:with-param name="section">general</xsl:with-param>
  </xsl:call-template>
</xsl:template>
<xsl:template match="//current_location/state_or_region">
  <xsl:call-template name="paField">
    <xsl:with-param name="name">state</xsl:with-param> 
    <xsl:with-param name="section">general</xsl:with-param>
  </xsl:call-template>
</xsl:template>
<xsl:template match="//current_location/country">
  <xsl:call-template name="paField">
    <xsl:with-param name="name">country</xsl:with-param> 
    <xsl:with-param name="section">general</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="paField">
<xsl:param name="name">TEST</xsl:param>
<xsl:param name="section">TEST</xsl:param>
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