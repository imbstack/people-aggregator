<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<? 
$login_required = FALSE;
include_once("web/includes/page.php");
?>

<!-- Derived from Phil's update page. Minimal changes to affect the People Aggregator utility format CSS. -->
<!-- Changes include, linking to external style sheet, bringing footer linke to the head of the document and including in a greater head <div>, consolidating the red warning <div> styles into a single style in the sheet called warning. -->

<link href="<?php echo PA::$theme_url;?>/pa_utility_template.css" rel="stylesheet" type="text/css" />
<body>
<head>
<title>PeopleAggregator</title>
</head>
<div class="wrapper">
<div class="head">
 <div id="logo01"></div>
 <!-- Login taken out below -->
 <!-- <div id="login">You're not logged in <a href="#"> Login</a></div> -->
 
 <!-- Main nav taken out below -->
 <!-- <div id="mainnav">
    <ul>
      <li><a href="http://www.peopleaggregator.com/">home</a></li>
      <li><a href="http://update.peopleaggregator.org/">update</a></li>
      <li><a href="http://wiki.peopleaggregator.org/Installation_guide">wiki</a></li>
      <li><a href="http://www.myelin.co.nz/phil/email.php?subject=peopleaggregator+update+site+query">link</a></li> 
      <li><a href="/admin">administration</a></li> 
    </ul>
 </div> -->
 
</div>


<div class="maincontent">

<!-- Warning taken out below -->
<!-- <div class="warning">

<p>This is pre-release code.  Download and use at your own risk!<br>

Official announcement: <a href="http://www.gnomedex.com/">Gnomedex 6.0 (June 2006)</a>.<br>

First code release: July/August 2006.</p>

</div> -->

<h1>PeopleAggregator: Features and Modules</h1>



<h2>What is PeopleAggregator?</h2>


<p>PeopleAggregator has a number of different purposes:</p>


<ol>
 <li>It is a <b>social networking
     application</b>that runs at <a
     href="http://www.peopleaggregator.net/">http://www.peopleaggregator.net</a>.</li>
 <li>It is also a <b>do-it-yourself
     social networking system</b>. You can
     create your own community at the click of a button - no coding required.</li>
 <li>It is a <b>development
     platform</b> - the entire source code is
     available for download (free for charities and non-profits). You can also
     modify the code, for example to extend the functionality or create a new
     user interface.</li>

 <li>PeopleAggregator exposes <b>open
     APIs</b> and supports <b>open
     standards</b>. So all web applications
     that support these APIs can seamlessly share data between themselves. We
     also support open standards like microformats and OpenID.</li>
</ol>


<h2>How can you use PeopleAggregator?</h2>


<p>Depending on what role you are filling, PeopleAggregator
meets many needs:</p>


<ol>
 <li><b>For Developers</b>: </li>
 <ul>
  <li>PeopleAggregator is a
      sophisticated, yet easy to program to, application development platform.
      It utilizes two kinds of API calls; traditional mashup calls, which are
      REST and XML-RPC APIs (via http) and weve developed faster, more
      optimized internal API calls, which talk directly to our php5 code. 
      These calls are more for commercial grade quality results.</li>

  <li>It is built using php5 and
      MySQL, so it is easy and inexpensive to deploy. </li>
  <li>It supports easy UI
      customizations. The entire user interface you see at PeopleAggregator.net
      has been built utilizing our internal php5 APIs.  One simply needs to
      modify the user interface themes, and not touch any of the core
      engine.</li>
  <li>Built-in automatic upgrades
      are available! </li>
 </ul>
 <li><b>For Web Service and Site
     developers</b>: </li>
 <ul>

  <li>PeopleAggregator exposes all
      of the data and functionality of a social network through open APIs,
      which means that you can build compelling mashups that mesh with your
      existing application. For example, a photo-sharing site can connect its
      member base to member accounts in the PeopleAggregator, using the
      granular levels of relationships between members to control public access
      over their photos. </li>
  <li>PeopleAggregator can extend
      the functionality of your site, without having to build your own social
      network.  Groups are now available to any member based site, providing
      group blogs, message boards, media galleries and any other kind of
      grouping feature.</li>
  <li>If your software also
      exposes APIs, then that allows us to integrate with it. That provides a
      better experience to users, plus it drives traffic and visibility both
      ways.</li>
 </ul>
 <li><b>For End-Users</b>:</li>
 <ul>

  <li>PeopleAggregator is a full
      fledged social network, with a wide range of features and customization
      controls.</li>
  <li>PeopleAggregator is a Digital
      Identity Hub - for example, you could use your flickr ID to sign up or
      participate in a single sign-on experience with other OpenID
      applications and services.</li>
  <li>Import/Export  you can
      export your profile data and content, and move it to another application.
      So you are not locked down and you dont lose your data. </li>
  <li>Exposing APIs - we allow you
      to use your data outside of PeopleAggregator as well. Note that this
      requires other applications to allow consumption of that data.</li>
 </ul>
</ol>

<p><b>See also:</b> <a href="http://wiki.peopleaggregator.org/User_Guide_For_Network_Operators">User
Guide For Network Operators</a></p>


<h2>Key Features</h2>


<p><b>Meta Network functionality</b><span style='font-weight:
normal'>: the ability to create social networks on-the-fly provides potential
social network operators a cheap and easy way to set up their own social
networks.</p>


<p><b>User profiles</b></p>

<ul>
 <li>Wide range of end-user profile
     data and attributes</li>
 <li>Customizable user profile
     fields and desktop image</li>
 <li>Access control (show my date
     of birth only to my immediate friends)</li>
 <li>Basic/Professional/Personal
     Info</li>

</ul>


<p><b>Privacy settings</b>: control
who has access over what based upon your relationship to that person.</p>


<p><b>Structured Blogging</b>:
a microcontent publishing platform which allows the user to create a wide range
of content and blog posts.</p>

<ul>
 <li><u>These blog posts types
     include:</u></li>

 <ul>
  <li>Blog</li>
  <li>Audio - podcasting</li>
  <li>Image</li>
  <li>Video - vlogging</li>
  <li>Event</li>

  <li>Review</li>
  <li>People</li>
  <li>Group</li>
 </ul>
 <li>All of these posts are
     encoded with microformats tags, so that new kinds of applications and
     services can leverage the rich structured data of these posts. </li>
</ul>

<p><b>Personal, Community and Group blogs</b>: any blog post can get sent to the overall
Community Network Home page blog or to any Group blog (in addition to your own
Personal blog.)  </p>

<ul>
 <li>Route these blog posts to your
     external blog</li>
 <li>Send a blog post into
     PeopleAggregator utilizing the Metaweblog or Atom APIs</li>
 <li>WYSIWYG blog editor</li>
</ul>

<p><b>Private Page</b>: where
you have access to all your system tools and settings, including control over
what modules are displayed and where theyre displayed.</p>

<ul>
 <li>Settings controls</li>
 <li>Wide range of modules</li>
 <li>Private view of your personal
     blog</li>
</ul>

<p><b>Public Page</b>: your customizable
personal homepage that everyone can see.</p>

<ul>
 <li>Public view of your personal
     blog</li>
 <li>Wide range of modules</li>
 <li>Desktop image</li>

</ul>

<p><b>Theme system</b></p>

<ul>
 <li>Template-based themeing system
     easily customizable via available source code</li>
 <li>End-user CSS customization
     coming!</li>
</ul>


<p><b>Networks</b></p>

<ul>
 <li>Join or Create your own completely
     autonomous social network</li>
 <li>End-users profile accounts
     work across any number of networks.  Ones media uploads and blog posts
     are kept separate on a network by network basis.</li>
 <li>A Network directory enables
     Network discoverability</li>
</ul>


<p><b>Groups</b>: traditional
social networking Groups can be created by any network member. These Groups are
usually based upon members common interests, but can also be based upon
events, places, entities, topics or memes.</p>

<ul>
 <li>Each group has the following
     features: <b>Group Blog, Media Gallery, Message Boards, Member list</b></li>
 <li>Directory of Groups</li>
</ul>

<ul>
 <li>Create, delete and moderate
     groups</li>

 <li>Public, moderated and private
     groups</li>
</ul>


<p><b>Home Page</b>: displays
the main networks content.</p>

<ul>
 <li>Home Page blog</li>
</ul>

<ul>
 <li>Announcements</li>
 <li>Wide range of modules,
     including presence ping modules displaying members and groups status</li>
</ul>

<p><b>Media Gallery</b>: a
repository for storage of images, audio or video.  For individuals and groups. 
</p>

<ul>
 <li>Albums can be created for
     organizing ones media</li>

</ul>

<ul>
 <li>Sharing between individuals
     and groups</li>
</ul>


<p><b>Manage Content:</b></p>

<ul>
 <li>Delete any blog post or media
     item</li>

</ul>

<p><b>Messaging</b></p>

<ul>
 <li>Private messages to other
     users</li>
 <li>Folders for organizing
     messages</li>
</ul>

<p><b>People (Relationships)</b></p>

<ul>
 <li>Establish relationships
     between network members</li>
 <li>List relationships that
     one has</li>
 <li>Display status of
     Members </li>
 <ul style='margin-top:0in' type=circle>
  <li>Newest Members</li>

  <li>Latest login</li>
 </ul>
 <li>Search Users</li>
</ul>


<p><b>Message boards</b></p>

<ul>
 <li>Hierarchical topic based
     message board</li>

 <li>Threaded discussions</li>
</ul>

<p><b>Invites</b></p>

<ul>
 <li>Mechanism for inviting users
     into system and groups using email invites</li>
 <li>Track status of invites</li>

</ul>


<p><b>Tags</b></p>

<ul>
 <li>Everything can be tagged</li>
</ul>

<ul>
 <ul>
  <li>Users</li>

  <li>Groups</li>
  <li>User generated content</li>
 </ul>
 <li>Browse and pivot on Tags</li>
</ul>


<p><b>Comments</b></p>

<ul>
 <li>Post comments to content</li>
</ul>

<p><b>Search</b></p>

<ul>
 <li>Text search </li>
</ul>


<p><b>Links</b>: </p>

<ul>
 <li>Manage Link Lists  via a
     simple Link editor</li>
 <li>Display these links on your
     Public and private pages</li>
</ul>

<p><b>Modules</b>: internal
and third party plug-in services (see list below)Modules</p>



<ul>
 <li><b>Recent Media: </b>display of the most recently uploaded media</li>
 <li><b>Recent Posts: </b>list of all recent blog posts throughout the
     network</li>
 <li><b>Most popular tags: </b>list of the most used tags in the system</li>

 <li><b>Relations</b>: list of people youre connected with in
     PeopleAggregator</li>
 <li><b>Gallery</b>: list of media items</li>
 <li><b>My Groups</b>: the groups you belong to</li>
 <li><b>My Links</b>: list of your links</li>
 <li><b>My Networks</b>: list of networks you belong to</li>

 <li><b>Photo</b>: your profile photo</li>
 <li><b>Added as a friend by</b>: list of members who added you as a friend</li>
 <li><b>Messages</b>: recent private email messages and any message
     pending</li>
 <li><b>My Recent Comments</b>: comments made on your blog (person or group)</li>
 <li><b>Flickr</b>: displays latest pictures from a specified
     Flickr account</li>

 <li><b>Delicious Links</b>: list of links from a specified del.icio.us
     account</li>
</ul>
</div>
<div class="footer"><p>Copyright &copy; 2006 Broadband Mechanics Inc. All rights reserved.</p></div>
</div>
</body>
