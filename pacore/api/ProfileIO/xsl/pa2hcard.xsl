<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method='xml' encoding='utf-8' indent='yes' />
<!--
<xsl:output method='html' encoding='utf-8' indent='yes' />
-->
<xsl:param name="paUrl">http://www.peopleaggregator.net</xsl:param>
<xsl:param name="paUid">0</xsl:param>

<xsl:param name="paType">0</xsl:param>         <!-- Profile Type: 'Personal'=0, 'Professional'=1) -->
<xsl:param name="paCompanyType">-1</xsl:param> <!-- Company Type:'Current'=0, 'Prior'='1', 'No company'=-1) -->

<xsl:param name="exposeCore"></xsl:param>
<xsl:param name="exposeEmail"></xsl:param>
<xsl:param name="exposeAddress"></xsl:param>
<xsl:param name="exposePersonal"></xsl:param>
<xsl:param name="exposeSummary"></xsl:param>
<xsl:param name="exposeEducation"></xsl:param>

<!--================= this is removed now =======================
<xsl:param name="exposeCompany"></xsl:param>
<xsl:param name="exposePriorCompany"></xsl:param>
===============================================================-->

<xsl:param name="exposeInternalFriends"></xsl:param>
<xsl:param name="exposeFlickrFriends"></xsl:param>
<xsl:param name="exposeFacebookFriends"></xsl:param>
<xsl:param name="exposeOtherFriends"></xsl:param>


<!-- count internal, Flickr, Facebook and other relations -->
<xsl:variable name="n_internal" select="count(//relation[@network='internal'])"/>  
<xsl:variable name="n_flickr" select="count(//relation[@network='flickr'])"/>
<xsl:variable name="n_facebook" select="count(//relation[@network='facebook'])"/>
<xsl:variable name="n_others" select="count(//relation[@network != 'internal' and @network != 'flickr' and @network != 'facebook'])"/>

<xsl:template match="/">
<style><xsl:text>
    div.vcard {
      position: relative;
    }

    .vcard p {
      position: relative;
      margin-bottom: 10px;
    }

    .vcard ul {
      list-style: none;
      padding: 0; margin: 0;
    }
    
    .xfn .vcard {
      list-style: none;
    }
    
    .xfn .vcard img {
      width: auto;
      height: 35px;
    }
</xsl:text></style>

<div>
<xsl:attribute name="class">      <!-- we will check for the Profile Type here -->
  <xsl:choose>
    <xsl:when test="$paType=0">vcard personal</xsl:when>
    <xsl:otherwise>vcard professional</xsl:otherwise>
  </xsl:choose>
</xsl:attribute>

<xsl:if test="$exposeCore">

<xsl:variable name="full_name">
    <xsl:value-of select="//field[@name='first_name'][@section='core']/@value" />
    <xsl:text> </xsl:text>
    <xsl:value-of select="//field[@name='last_name'][@section='core']/@value" />
</xsl:variable>    

<!-- personal user data -->
  <p class="personal">
    <a class="url fn" rel="me" href="{$paUrl}/user.php?uid={$paUid}">
      <img class="photo" src="{$userPic}" alt="{$full_name}"/><br/><xsl:value-of select="$full_name"/></a>
    <br/>
    <xsl:if test="$exposeEmail">
      <a class="email">
        <xsl:attribute name="href">mailto:<xsl:value-of select="//field[@name='email'][@section='core']/@value" /></xsl:attribute>
        <xsl:value-of select="//field[@name='email'][@section='core']/@value" />
      </a><br/>
    </xsl:if>
    <b>Birthday</b>: <abbr class="bday"><xsl:value-of select="//field[@name='dob'][@section='general']/@value" /></abbr>
  </p>

</xsl:if>

<xsl:choose>
  <xsl:when test="$paCompanyType = 0">
  <!-- organization data - companyType='current' -->
    <p class="company">
      <b>Current Company</b>:
       <span class="org">
         <abbr class="type" title="current">
           <xsl:text> </xsl:text>
         </abbr>
         <a class="url">
          <xsl:attribute name="href">
            <xsl:value-of select="//field[@name='website'][@section='professional']/@value" />
          </xsl:attribute>
          <span class="organization-name">
            <xsl:value-of select="//field[@name='company'][@section='professional']/@value" /> 
          </span>
         </a>
       </span>  
      <br/>
      <xsl:if test="$exposeSummary"> <!-- we use the summary filed here to give more details about company! -->
        <p class="note">
          <b>Headline: </b> <span class="headline"><xsl:value-of select="//field[@name='headline'][@section='professional']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
          <b>Industry: </b> <span class="industry"><xsl:value-of select="//field[@name='industry'][@section='professional']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
          <b>Skills: </b> <span class="skills"><xsl:value-of select="//field[@name='career_skill'][@section='professional']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
          <b>Summary: </b> <span class="summary"><xsl:value-of select="//field[@name='summary'][@section='professional']/@value" />;<xsl:text>&#10;</xsl:text></span><br />
        </p>
      </xsl:if>  
      <b>Title</b>:
      <span class="title"><xsl:value-of select="//field[@name='title'][@section='professional']/@value" /></span><br/>
    </p>
  </xsl:when>
  <xsl:when test="$paCompanyType = 1">
  <!-- organization data - companyType='prior' -->
    <p class="company">
      <b>Prior Company</b>:
      <span class="org">
        <abbr class="type" title="prior">
          <xsl:text> </xsl:text>
        </abbr>
        <span class="organization-name">
          <xsl:value-of select="//field[@name='prior_company'][@section='professional']/@value" />
        </span>
      </span><br/>
      <b>Prior Title</b>:
      <span class="title"><xsl:value-of select="//field[@name='prior_company_title'][@section='professional']/@value" /></span><br/>
    </p>
  </xsl:when>
</xsl:choose>

<xsl:if test="$exposeEducation"> <!-- in this case we will display School and education details as notes  -->
  <p class="note">
    <b>College Name: </b><span class="college"><xsl:value-of select="//field[@name='college_name'][@section='professional']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Degree: </b><span class="degree"><xsl:value-of select="//field[@name='degree'][@section='professional']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Languages: </b><span class="languages"><xsl:value-of select="//field[@name='languages'][@section='professional']/@value" />;<xsl:text>&#10;</xsl:text></span><br />
    <b>Honors &amp; Awards: </b> <span class="awards"><xsl:value-of select="//field[@name='awards'][@section='professional']/@value" />;<xsl:text>&#10;</xsl:text></span><br />
  </p>
</xsl:if>

<xsl:if test="$exposeAddress">
  <!-- home address, at this time we have only the home address! -->
  <p class="adr">
    <abbr class="type">
      <xsl:attribute name="title">      <!-- we will check for the Profile Type here -->
        <xsl:choose>
          <xsl:when test="$paType=0">home</xsl:when>
          <xsl:otherwise>work</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:text> </xsl:text>
    </abbr>
    <xsl:choose>
      <xsl:when test="$paType=0">               <!-- profileType='Personal', we will use the user home address -->
        <b>City</b>: <span class="locality"><xsl:value-of select="//field[@name='city'][@section='general']/@value" /></span><br/>
        <b>State/Region</b>: <span class="region"><xsl:value-of select="//field[@name='state'][@section='general']/@value" /></span><br/>
        <b>Zip/Postal Code</b>: <span class="postal-code"><xsl:value-of select="//field[@name='postal_code'][@section='general']/@value" /></span><br/>
        <b>Country</b>: <span class="country-name"><xsl:value-of select="//field[@name='country'][@section='general']/@value" /></span>
      </xsl:when>
      <xsl:otherwise>                           <!-- profileTxpe='Professional', we must use company data for address -->
        <xsl:choose>
          <xsl:when test="$paCompanyType=0">    
            <!-- 
             //  companyType='current', but we don't have any company address data at this time!
             //  we will use the user home address temporrary at this time because this section
             //  will be expanded in future
            -->
            <b>City</b>: <span class="locality"><xsl:value-of select="//field[@name='city'][@section='general']/@value" /></span><br/>
            <b>State/Region</b>: <span class="region"><xsl:value-of select="//field[@name='state'][@section='general']/@value" /></span><br/>
            <b>Zip/Postal Code</b>: <span class="postal-code"><xsl:value-of select="//field[@name='postal_code'][@section='general']/@value" /></span><br/>
            <b>Country</b>: <span class="country-name"><xsl:value-of select="//field[@name='country'][@section='general']/@value" /></span>
          </xsl:when>
          <xsl:when test="$paCompanyType=1">
            <!-- 
             //  companyType='prior', we have only the location info for the prior company at this time!
             //  user can store full address into the location field for this time or
             //  we will expand this section in future ?
            -->
            <b>Locality</b>: <span class="locality"><xsl:value-of select="//field[@name='prior_company_city'][@section='professional']/@value" /></span><br/>
          </xsl:when>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </p>

</xsl:if>

<xsl:if test="$exposePersonal">
  <!-- Personal interests and beliefs -->
  <p class="note">
    <b>Ethnicity: </b><span class="ethnicity"><xsl:value-of select="//field[@name='ethnicity'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Religion: </b><span class="religion"><xsl:value-of select="//field[@name='religion'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Political View: </b><span class="political"><xsl:value-of select="//field[@name='political_view'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Passion: </b><span class="passion"><xsl:value-of select="//field[@name='passion'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Activities: </b><span class="activities"><xsl:value-of select="//field[@name='activities'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Books: </b><span class="books"><xsl:value-of select="//field[@name='books'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Movies: </b><span class="movies"><xsl:value-of select="//field[@name='movies'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Music: </b><span class="music"><xsl:value-of select="//field[@name='music'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>TV Shows: </b><span class="tv"><xsl:value-of select="//field[@name='tv_shows'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span><br/>
    <b>Food: </b><span class="food"><xsl:value-of select="//field[@name='cusines'][@section='personal']/@value" />;<xsl:text>&#10;</xsl:text></span>
  </p>

</xsl:if>
</div>

  <xsl:if test="$exposeInternalFriends">
   <p>
    <b>Friends on this network(<xsl:value-of select="$n_internal"/>):</b>
    <ul class="xfn internal"><xsl:apply-templates select="//relation[@network='internal']" /><xsl:text> </xsl:text></ul>
   </p>
  </xsl:if>

  <xsl:if test="$exposeFlickrFriends">
   <p>
    <b>Flickr.com Friends(<xsl:value-of select="$n_flickr"/>):</b>
    <ul class="xfn flickr"><xsl:apply-templates select="//relation[@network='flickr']" /><xsl:text> </xsl:text></ul>
   </p>
  </xsl:if>

  <xsl:if test="$exposeFacebookFriends">
   <p>
      <b>Facebook.com Friends(<xsl:value-of select="$n_facebook"/>):</b>
      <ul class="xfn facebook"><xsl:apply-templates select="//relation[@network='facebook']" /><xsl:text> </xsl:text></ul>
   </p>
  </xsl:if>

  <xsl:if test="$exposeOtherFriends">      <!-- match al other networks! -->
   <p>
      <b>Friends from other networks(<xsl:value-of select="$n_others"/>):</b>
      <ul class="xfn others"><xsl:apply-templates select="//relation[@network != 'internal' and @network != 'flickr' and @network != 'facebook']" /><xsl:text> </xsl:text></ul>
   </p>
  </xsl:if>

</xsl:template>

<xsl:template match="relation">
<xsl:variable name="isInternal" select="(@network = 'internal')"/>

   <li class="vcard {@network}">
    <xsl:choose>
      <xsl:when test="$isInternal">
        <a class="fn url" href="{$paUrl}/user.php?uid={@profile_url}" rel="friend" target="_blank">
        <xsl:choose>
          <xsl:when test='string-length(@thumbnail_url)=0'>
            <img class="photo" src="{$paUrl}/files/rsz/crop_35x35/images/default.png" alt="{@display_name}" /><br />
          </xsl:when>
          <xsl:otherwise>
            <img class="photo" src="{$paUrl}/files/rsz/crop_35x35/files/{@thumbnail_url}" alt="{@display_name}" /><br />
          </xsl:otherwise>
        </xsl:choose>
        <xsl:value-of select="@display_name" /></a>
      </xsl:when>
      <xsl:otherwise>
        <a class="fn url" href="{@profile_url}" rel="friend" target="_blank">
        <img class="photo" src="{@thumbnail_url}" alt="{@display_name}" /><br />
        <xsl:value-of select="@display_name" /></a>
      </xsl:otherwise>
    </xsl:choose>
   </li>
</xsl:template>
</xsl:stylesheet>
