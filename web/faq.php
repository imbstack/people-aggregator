<? 
$login_required = FALSE;
include_once("web/includes/page.php");
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns:st1="urn:schemas-microsoft-com:office:smarttags"
xmlns="http://www.w3.org/TR/REC-html40">
<link href="<?php echo PA::$theme_url;?>/pa_utility_template.css" rel="stylesheet" type="text/css" />
<head>
</head>
<body>
<div class="wrapper">
<div class="head">
<div id="logo01"></div>
</div>

<div class="maincontent">
<h1><span>PeopleAggregator</span> FAQ</h1>

<h2>Categories</h2>

<ul>
 <li><a href="#overview"><span
     class=SpellE>PeopleAggregator</span> Overview</a></li>
 <li><a href="#architecture"><span
     class=SpellE>PeopleAggregator</span> Architecture/Structure</a></li>
 <li><a href="#profile">Profile
     &amp; Admin</a></li>
 <li><a href="#import">Import and Export</a></li>
 <li><a href="#networks">Networks</a></li>
 <li><a href="#groups">Groups</a></li>
 <li><a href="#media">Media
     Gallery</a></li>
 <li><a href="#blogs"><span
     class=SpellE>Blogs</span></a></li>
 <li><a href="#microcontent"><span
     class=SpellE>Microcontent</span>: Events &amp; Reviews</a></li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l3 level1 lfo1;tab-stops:list .5in'><a href="#messaging">Messaging</a></li>
</ul>

<p>&nbsp;</p>

<h2><a name=overview></a><span class=SpellE>PeopleAggregator</span> Overview</h2>

<p><b>q. How do I become a member of the <span class=SpellE>PeopleAggregator</span>
network?</b></p>

<p>a. Click on the Register button - on either the splash page or at the top
right corner of any <span class=SpellE>PeopleAggregator</span> page.</p>

<p>a2. Enter your user name, first and last real name, your password (twice)
and your email address.</p>

<p>a3. Select a file to upload to be your photo.</p>

<p>a4. Confirm that you have read the EULA - the end user terms of service
(license) agreement.</p>

<p>a5. Click on the JOIN NOW button.</p>

<p>&nbsp;</p>

<p><b>q. Someone is bugging or harassing me. What can I do about it?</b></p>

<p>If harassment occurs on the <span class=SpellE>PeopleAggregator</span> home
network, please <a href="mailto:operator@broadbandmechanics.com">contact
Broadband Mechanics, Inc</a>. If harassment occurs on another network, please
contact the network operator.</p>

<p>&nbsp;</p>

<p><b>q. Can I modify the UI of the hosted network?</b></p>

<p>a. Yes, you can customize the header and in the future we will provide CSS
customization options.</p>

<p>&nbsp;</p>

<p><b>q. Can I build my own UI on top of your network?</b></p>

<p>a. Absolutely, this is why we released our source code for anyone to
download. There are full details on the <a
href="http://wiki.peopleaggregator.org"><span class=SpellE>PeopleAggregator</span>
<span class=SpellE>Wiki</span></a>.</p>

<p>&nbsp;</p>

<p><span class=GramE><b>q</b></span><b>: What is the license of the <span
class=SpellE>PeopleAggregator</span> code?</b></p>

<p><span class=GramE>a</span>: Contact <a
href="http://www.broadbandmechanics.com/who.html">Broadband Mechanics</a> for a
commercial license. Weve created our license as a 'pay as you go' model with
source code available.</p>

<p>Source available means that anyone can download and use the code for
noncommercial purposes. Modification of the code (and distribution of modified
versions) is allowed under the same terms - i.e. you can make your own <span
class=SpellE>PeopleAggregator</span> distribution, which noncommercial users
can use for free and commercial users can use <i>if</i> they have a license
from BBM.</p>

<p>&nbsp;</p>

<p><b>q. Can I use the download source code and use it in a college project of
mine?</b></p>

<p>a. See above. Anyone can download and use the code for noncommercial
purposes as long as you give us attribution. Contact <a
href="http://www.broadbandmechanics.com/who.html">Broadband Mechanics</a> for a
commercial license.</p>

<p>&nbsp;</p>

<p><b>q. Can my non-profit use the source code?</b></p>

<p>a. Yes, for free as long as you give attribution. Anyone can download and
use the code for noncommercial purposes. We only charge a license fee for commercial
use.</p>

<p>&nbsp;</p>

<p><b>q. What do you charge for commercial usage of the source code?</b></p>

<p>PAGYL (Pay As You Go License) fees have a lifetime cap of $20,000 for the
entire duration of the Agreement. The assessment of Fees for the license is
based on the highest Aggregate Network Members reported:</p>

<ul type=disc>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l1 level1 lfo2;tab-stops:list .5in'>Up to 250 Network Members, US
     $2,500.00</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l1 level1 lfo2;tab-stops:list .5in'>Over 250 up to 1,000 Network
     Members, US $5,000.00</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l1 level1 lfo2;tab-stops:list .5in'>Over 1,000 up to 10,000
     Network Members, US $10,000.00</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l1 level1 lfo2;tab-stops:list .5in'>Over 10,000 up to 50,000
     Network Members, US $15,000.00</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l1 level1 lfo2;tab-stops:list .5in'>Over 50,000 Network Members,
     US $20,000.</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l1 level1 lfo2;tab-stops:list .5in'>In no event shall the fees
     for each PAYGL exceed US $20,000.00 in cumulative fee payment.</li>
</ul>

<p>&nbsp;</p>

<p><b>q. Will you be monitoring our network?</b></p>

<p>We have ping services which will update the <span class=SpellE>PeopleAggregator</span>
network every time there are changes to your network. You can turn these pings
off at any stage, if you wish.</p>

<p>&nbsp;</p>

<p><b>q. What is Structured <span class=SpellE>Blogging</span> and will <span
class=SpellE>PeopleAggregator</span> have this feature</b></p>

<p>Structured <span class=SpellE>Blogging</span> is a non-profit initiative to
give <span class=SpellE>bloggers</span> the tools to create and syndicate
structured information, such as reviews and events. It is integrated into the <span
class=SpellE>PeopleAggregator</span> content posting section.</p>

<p>Currently the <a href="http://structuredblogging.org/">Structured <span
class=SpellE>Blogging</span> website</a> offers <span class=SpellE>plugins</span>
for two of the most popular <span class=SpellE>blogging</span> platforms, <span
class=SpellE>Wordpress</span> and Movable Type. Using these <span class=SpellE>plugins</span>
makes it easy to create, edit, and maintain different kinds of posts.</p>

<p>&nbsp;</p>

<h2><a name=architecture></a><span class=SpellE>PeopleAggregator</span>
Architecture/Structure</h2>

<p><span class=GramE><b>q. How is the PeopleAggregator.net system</b></span><b>
organized - what is the structure of the system?</b></p>

<p>a. PeopleAggregator.net is a meta-network, hosting hundreds of networks(currently <?php 
$total_network = Network::get_total_networks();
echo $total_network;
?> numbers of networks)</p>

<p>a2. Each member of a network has their own personal pages (both public and
private).</p>

<p>a3. Each member also has their own <span class=SpellE>blog</span>, media
gallery (audio, video and images) and set of links. </p>

<p>a4. The media gallery can be viewed, uploaded into or shared with friends.</p>

<p>a5. Each member can control what modules are displayed on their personal
pages - and where each module is placed on those pages.</p>

<p>a6. Each member can send private messages to any other member.</p>

<p>a7. Groups can be created by any network member. Groups created in a
particular network are limited to just <i>that</i> network.</p>

<p>a8. Each Group can have its own Group <span class=SpellE>blog</span>, Group
gallery, Group Message board and display of group members. The creator of the
Group is called the Group Moderator.</p>

<p>a9. Network members can create their own networks. Each network has its own
unique membership, set of groups, media, and <span class=SpellE>blog</span>
content.</p>

<p>a10. Each network member has one account, but a unique set of media and
content (<span class=SpellE>blog</span> posts) for every network that theyre a
member of. There will be added search capabilities soon.</p>

<p>a11. Along the top of the screen is an orange menu bar  which allows access
to the Network Directory and gives members the ability to Create your own
Network or Return to the home network.</p>

<p>a12. Members can navigate between Networks by clicking on the 'Network' icon
in the Network directory, or in the My Networks module. Users at all times
return to the Home network by clicking on the 'Home network' button in the
orange menu bar.</p>

<p>&nbsp;</p>

<p><b>q. Do I have to be a member to view <span class=SpellE>PeopleAggregator</span>
Network content, People or Groups?</b></p>

<p>a. No  most content and all People profiles are available to anyone to view
(anonymously).</p>

<p>a2. However some content may be set by members to be ONLY viewed by friends
or by Group members.</p>

<p>a3. Members can control view access to all profile content on their public
page  for example, so that only friends can view it.</p>

<p>&nbsp;</p>

<p><b>q. How do I invite people into <span class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Click on the Invite a Friend button next to your Face and name, on the
top right hand side of the screen.</p>

<p>a2. Fill in your friends name (first, last) and email address.</p>

<p>a3. Then you may add a personalized portion to the invite message that will
be emailed to your friend.</p>

<p>a4. Click on the Send Invitation button.</p>

<p>&nbsp;</p>

<p><b>q. What are all those ID systems at register and log-in?</b></p>

<p>a. <span class=SpellE>PeopleAggregator</span> allows you to use other ID
systems.</p>

<p>a2. <span class=SpellE>LiveJournal</span>: you can log on at <span
class=SpellE>LiveJournal</span> and use your <span class=SpellE>LiveJournal</span>
<span class=SpellE>OpenID</span> account to log into <span class=SpellE>PeopleAggregator</span>.
But you need to be registered first at <span class=SpellE>PeopleAggregator</span>.</p>

<p>a3. <span class=SpellE>Sxip</span>: you can use your <span class=SpellE>Sxip</span>
ID username. Simply go to Sxore.com and login into <span class=SpellE>PeopleAggregator</span>
from there.</p>

<p>a4. <span class=SpellE>Flickr</span>: you can use your <span class=SpellE>Flickr</span>
ID as a username and password with <span class=SpellE>PeopleAggregator</span>.
Provided you have registered already at <span class=SpellE>PeopleAggregator</span>,
if you are logged on at <span class=SpellE>Flickr</span> then you will be
logged in automatically at <span class=SpellE>PeopleAggregator</span> too.</p>

<p>a5. More 'open identity' standards and updates will be available soon. <span
class=SpellE>PeopleAggregator</span> will support as many open ID standards as
possible.</p>

<p>&nbsp;</p>

<p><b>q. Whats the difference between <span class=SpellE>MyPage</span> and <span
class=SpellE>MyPublic</span> Page?</b></p>

<p>a. '<span class=SpellE>MyPage</span>' is a private page that only YOU see,
while '<span class=SpellE>MyPublic</span> Page' is the page that everyone else
sees - for example when someone clicks on your face or your name anywhere in <span
class=SpellE>PeopleAggregator</span>.</p>

<p>a2. <span class=SpellE>MyPublic</span> Page has a header graphic, called the
desktop image - this is only viewable on the <span class=SpellE>MyPublic</span>
Page.</p>

<p>a3. <span class=SpellE>MyPage</span> has a module called Enable Module -
this is not available on <span class=SpellE>MyPublic</span> Page. Also this
module is not <span class=SpellE>draggable</span>.</p>

<p>a4. The background color and module skins are different for <span
class=SpellE>MyPage</span> (light grey) and <span class=SpellE>MyPublic</span>
Page (dark blue).</p>

<p>&nbsp;</p>

<p><b>q. How are <span class=SpellE>MyPage</span> and <span class=SpellE>MyPublic</span>
Page similar?</b></p>

<p><span class=GramE>a</span>. <span class=SpellE>MyPublic</span> Page has all
the same modules and theyre in the same position and state (open or closed) as
those modules on <span class=SpellE>MyPage</span>.</p>

<p>&nbsp;</p>

<p><b>q. What is the <span class=SpellE>PeopleAggregator</span> Home page?</b></p>

<p>a. This is the Home page for the main network of the system, aka the Meta
Network. Every member of the <span class=SpellE>PeopleAggregator</span> system
is a member of this network by default.</p>

<p>a2. Every Network also has its own unique Home page.</p>

<p>a3. Every Network has its own unique Network community <span class=SpellE>blog</span>
 which resides in the middle column of the Home page.</p>

<p>a4. Each Home page has update/ping modules for People and Groups. These
modules display (via a selectable drop-down menu) the latest, the largest or
the most recently created People or Groups.</p>

<p>a5. You can get to the Home page at all times by clicking on the 'Home'
button on the main menu bar.</p>

<p>&nbsp;</p>

<p><b>q. How do I search for something?</b></p>

<p>a. You can search for people by clicking 'People' in the top menu and then
filling in the 'Search Users' box. You can also search for groups and networks.
However at this point there is no general content search option.</p>

<p>&nbsp;</p>

<p><b>q. How do I find people?</b></p>

<p>Click 'People' in the top menu and then fill in the 'Search Users' box.
Enter as much information as you can in the text fields in order to refine your
search.</p>

<p>&nbsp;</p>

<p><b>q. What can I do in the 'Create Content' section?</b></p>

<p>a. You can publish content such as: <span class=SpellE>blogs</span>, audio,
images, video, events, reviews, information on people and information about a
group to My Page, your Group or to the Home Page.</p>

<p>&nbsp;</p>

<h2><a name=profile></a>Profile &amp; Admin</h2>

<p><b>q. How do I add or change information in my personal profile display?</b></p>

<p>a. Click on Me (in the secondary menu bar).</p>

<p>a2. <span class=GramE>Click on the Settings button in the secondary menu
bar profile.</span></p>

<p>a2. Fill in the fields under the Basic, General, Personal or Professional
tab screens</p>

<p>a3. Select access control (everybody, nobody, just friends)  to control who
may view each profile field.</p>

<p>a4. Click the Apply changes button.</p>

<p>&nbsp;</p>

<p><b>q. How do I change my header desktop image?</b></p>

<p>a. Click on Me &gt; Settings in the secondary menu bar.</p>

<p>a2. <span class=GramE>Click on the General tab.</span></p>

<p>a3. Select a file to upload as the desktop image. Note the three header
states (radio buttons): stretch, crop or tile.</p>

<p>a4. Select a display state using one of those <span class=GramE>radio</span>
buttons - and click apply changes.</p>

<p>&nbsp;</p>

<p><b>q. How do I prevent people from seeing some or all of my profile
settings?</b></p>

<p>a. Use the access controls next to each personal profile field.</p>

<p>a2. Select whether or not everyone, no one, or just your set of friends -
can view a particular field.</p>

<p>&nbsp;</p>

<p><b>q. How do I delete my account?</b></p>

<p>a. You cannot do that at this particular time, however this feature will be
available soon.</p>

<p>&nbsp;</p>

<p><b>q. I forgot my password, what do I do?</b></p>

<p>a. Click on the Forgot Password next to the log-in button.</p>

<p>&nbsp;</p>

<p><b>q. How do I change my photo?</b></p>

<p>a. Click on 'My Page' in the main menu, <span class=GramE>then</span> click
'Edit Profile'.&nbsp;</p>

<p>a2. Click the browser button to find a new photo on your computer, <span
class=GramE>then</span> click 'Upload Photo'.&nbsp;</p>

<p>a3. Click 'Apply Changes' to confirm the change.</p>

<p>&nbsp;</p>

<p><b>q. How do I change my email address?</b></p>

<p>a. Click on 'My Page' in the main menu, <span class=GramE>then</span> click
'Edit Profile'.&nbsp;</p>

<p>a2. Update the 'Email' text box.</p>

<p>a3. Click 'Apply Changes' to confirm the change.</p>

<p>&nbsp;</p>

<p><b>q. How do I change my screen name, password or URL?</b></p>

<p>a. You can't change your screen name or URL. However you may change your
password by clicking on 'My Page' in the main menu, then click 'Edit
Profile'.&nbsp;</p>

<p>a2. Update the 'Password' and 'Confirm Password' text boxes.</p>

<p>a3. Click 'Apply Changes' to confirm the change.</p>

<p>&nbsp;</p>

<p><b>q. How am I billed?</b></p>

<p>Individuals are not charged from within <span class=SpellE>PeopleAggregator</span>.
Network Operators will soon have an option to isolate their networks from
advertising, for a fee.</p>

<p>&nbsp;</p>

<p><b>q. Can I change the order relations appear on My Page?</b></p>

<p>No, but this will be available in the near future.</p>

<p>&nbsp;</p>

<p><b>q. Can I delete friends from my Relations?</b></p>

<p>a. Yes, go to 'My Page' and click on the face of the person in the
'Relations' section (top-left column).&nbsp;</p>

<p>a2. You will be taken to that person's Public Page, where there will be
three links at the top of the page. <span class=GramE>Click on 'Delete
Relationship'.</span></p>

<p>&nbsp;</p>

<h2><a name=import></a>Import and Export</h2>

<p><b>q. What can I import and export into and out of <span class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Profiles: <span class=SpellE>Facebook</span>, <span class=SpellE>Flickr</span>
and <span class=SpellE>Basecamp</span> profile formats are currently supported.
<a href="http://gmpg.org/xfn/">XFN</a> (XHTML Friends Network) export is also
supported.</p>

<p>a2. <a href="http://www.foaf-project.org/">FOAF</a> (Friend of a Friend)
export is coming soon. Also you will be able to import FOAF and XFN as well.</p>

<p>a3. More advanced control and <span class=GramE>connections between social
networks (beyond Import/Export) is</span> coming too.</p>

<p>&nbsp;</p>

<p><b>q. How do I import <span class=SpellE>Facebook</span>, <span
class=SpellE>Flickr</span> or <span class=SpellE>Basecamp</span> profile info
into <span class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Go to Me &gt; Settings in the menu.</p>

<p>a2. Select the 'Import' tab and click on the system you wish to import from.</p>

<p>a4. All of your data that can be imported will be displayed. Checkboxes next
to each data field will allow you to filter what gets imported.</p>

<p>a5. Friends from external systems will be placed into a special module
called <span class=SpellE>MyFriends</span> (until further industry standards
and social contracts can be established to facilitate auto-registering people
into new systems).</p>

<p>&nbsp;</p>

<h2><a name=networks></a>Networks</h2>

<p><b>q. How do I join another network on the <span class=SpellE>PeopleAggregator</span>
meta-network?</b></p>

<p>a. Click on the Network Directory button in the top orange menu bar.</p>

<p>a2. Discover hundreds of different networks running on the same
meta-network. They can be sorted in several ways, including by 'category'.</p>

<p>a3. By clicking on the icon or name of the network, you are sent to that
Network's homepage.</p>

<p>a4. The orange menu bar will now display the name of the network you are
currently in.</p>

<p>a5. This is a completely stand alone, autonomous network - with its own
membership, set of groups and unique content.</p>

<p>a8. All of the content (<span class=SpellE>blog</span> posts, comments) you
create in this new network will stay in that network. All of the media you
upload will also stay in this network.</p>

<p>a7. The Network operator can set the header graphic for the network.</p>

<p>&nbsp;</p>

<p><b>q. How do I create a Network?</b></p>

<p>a. Click on Create a Network along the top orange menu bar.</p>

<p>a2. Fill in name, network URL, Alt ID text, tagline, and a description of
the Network.</p>

<p>a3. Select a file to upload for the Network image and small network image.</p>

<p>a4. Click the Network <span class=GramE>create</span> button.</p>

<p>a5. Select whether email should be sent to you when somebody accepts your
invitation.</p>

<p>a7. Select a Network Category.</p>

<p>a8. Enter Tags for this Network.</p>

<p>a9. Select whether or not you should stop accepting members in this network
beyond 128.</p>

<p>a10. Click on the Save Network Settings</p>

<p>NOTE: as of this version  you cannot edit any of your settings, moderate or
delete a Network. But this functionality is coming.</p>

<p>&nbsp;</p>

<h2><a name=groups></a>Groups</h2>

<p><b>q. How do I create a Group?</b></p>

<p>a. Click on the Groups menu item.</p>

<p>a2. Click on Create a Group.</p>

<p>a3. Fill in name, tags, description of the Group.</p>

<p>a4. Select an activity category and a file to upload for Group icon.</p>

<p>a5. Set whether the Group is accessible by anyone or just group members;
whether its an open, moderated or private group; and whether or not the
Groups content is moderated (by the Groups creator/moderator) or not.</p>

<p>&nbsp;</p>

<p><b>q. How do I join a <span class=GramE>Group</span></b></p>

<p>a. When you visit a Group, on the left of the screen is a Join this Group
button (under the Groups icon). There is also a Join this Group button in
the secondary menu bar.</p>

<p>a2. Clicking on either of these buttons enables you to join the Group.</p>

<p>a3. The Groups icon and listing will then be displayed in your My Groups
module. When you go to the Group Homepage, you will notice your list of Groups
at the top of the page.</p>

<p>&nbsp;</p>

<p><b>q. How do I edit or change a Groups settings after it has already been
created?</b></p>

<p>a. This can only be done by the Groups creator/moderator.</p>

<p>a2. If you are a moderator, go to the Group that you wish to edit.</p>

<p>a3. Click on Edit in the secondary menu bar.</p>

<p>a4. Change settings, text or selections and then click Submit.</p>

<p>a5. SHORTCUT: Click on miniature pencil displayed next to the Group name and
icon in the Group Directory. This will take you directly into edit mode for
that Group.</p>

<p>&nbsp;</p>

<p><b>q. How do I invite people into a Group?</b></p>

<p>a. Click on the Group menu item.</p>

<p>a2. Click on the Invite button in the secondary menu.</p>

<p>a3. Fill in your friends Name (first, last) and email address.</p>

<p>a4. Then you may add a personalized portion to the invite message that will
be emailed to your friend.</p>

<p>a5. ADVANCED FEATURE: You can send up to 5 invitations simultaneously by
clicking on the ADD Line button. This will give you another row to fill in a
friends name and email address. This can be repeated four times up to a total
of five invitations simultaneously. If you wish to do more, send out the first
five and then start again.</p>

<p>a6. Click on the Send Invitation button.</p>

<p>&nbsp;</p>

<p><b>q. How to post a Group <span class=SpellE>blog</span> post into <span
class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Click on the name of your Group to go to its homepage.</p>

<p>a2. Under 'Group <span class=SpellE>Blog</span>' click 'Add <span
class=SpellE>Blogpost</span>'. You will be taken to the 'Create Content &gt; <span
class=SpellE>Blog</span>' page.</p>

<p>a3. Enter a <span class=SpellE>blog</span> post title and the post details
under 'Description'.</p>

<p>a4. Enter tags to describe the post (optional).</p>

<p>a5. Tick the 'Route post to <span class=SpellE>PeopleAggregator</span>
Homepage' option if you want the post to appear on the main <span class=SpellE>PeopleAggregator</span>
page.</p>

<p>a6. If you have external <span class=SpellE>blogs</span> set up, you can use
OutputThis.org to route your post there.</p>

<p>a7. You may also publish the post to any Public group that you belong to, by
selecting the appropriate Group <span class=SpellE>Blogs</span> (<span
class=SpellE>nb</span>: only applies to Public Groups, not Private ones).</p>

<p>a8. Click 'Publish Post' to complete the post.&nbsp;</p>

<p>&nbsp;</p>

<p><b>q. Ive decided to Leave the Group, does that mean I am no longer a member
of the system?</b></p>

<p>No, it just means you are no longer a member of that particular group.</p>

<p>&nbsp;</p>

<h2><a name=media></a>Media Gallery</h2>

<p><b>q. What is the Gallery?</b></p>

<p>a. The Gallery is a Shared Media Gallery which holds video, audio or images.
You can view any of your <span class=GramE>friends</span> galleries and any of
your Groups galleries.</p>

<p>a2. Every Member and Group in the system has their own Gallery. Networks
will too, in the near future.</p>

<p>a3. The Gallery allows you to view thumbnails of your media, as well as list
out your media or upload additional items into the Gallery.</p>

<p>a4. The Upload controls allow you to select a file to upload and enter the
media item name, tag(s) and description. External URLs are also supported.</p>

<p>a5. The Upload controls also allow you to upload the media item into a
particular album (or create a new album) - as well as give you access control
over who can view the media item.</p>

<p>&nbsp;</p>

<p><b>q. How do I delete a media item from my <span class=GramE>gallery</span></b></p>

<p>a. Select the item you wish to delete, by clicking on the checkbox next to
the item.</p>

<p>a2. Select the delete action from the drop-down menu.</p>

<p>&nbsp;</p>

<p><b>q. How do I edit any of the information about a media item in my Gallery?</b></p>

<p>a. Select the item you wish to edit, by clicking on the checkbox next to the
item.</p>

<p>a2. Select the edit action from the drop-down menu.</p>

<p>a3. Now edit the information (name, tag, description, access control) and
click the apply changes button.</p>

<p>&nbsp;</p>

<p><b>q. Is there a limit to the number of images, audio clips and video I can
post?</b></p>

<p>a. There is no limit to the number of media items that you can upload,
however there is a size limit on each media items - maximum size 500KB for
images, 3MB for audio and video items. We will offer a turnkey video service in
the near future.</p>

<p>&nbsp;</p>

<p><b>q. How do I view the media items in one of my <span class=GramE>Groups</span></b></p>

<p>a. Click on 'Gallery' on the main menu.</p>

<p>a2. Click on 'Group's Media' on the secondary menu bar.</p>

<p>a3. In the 'Select Group' drop-down list, select the group you want to view.</p>

<p>&nbsp;</p>

<p><b>q. I uploaded some images and now I cant see them?</b></p>

<p>a. Go to your Public Page.</p>

<p>a2. Scroll down to the Gallery section and click on the image you're looking
for (or click 'View All' if you don't see it).</p>

<p>a3. Each person has their own set of items in their own network. So for
example if you go to another network, then those items will not be displayed in
the new network.</p>

<p>&nbsp;</p>

<p><b>q. I uploaded a video and now I cant play it. How do I fix this?</b></p>

<p>a. Check whether you have the right video player installed.</p>

<p>&nbsp;</p>

<p><b>q. How can I upload media (audio, vide or images) into one of my Groups</b></p>

<p>a. Go to the Group's homepage (e.g. by clicking 'Groups' in the main menu
and then clicking the name of one of your Groups).</p>

<p>a2. Scroll down the page, where you will see 3 sections for Image, Audio and
Video.&nbsp;</p>

<p>a3. There is an 'Upload' tab on each of those - click this tab to add a
media item.</p>

<p>a4. Select the 'Browse' button and select the media item from your
computer.&nbsp;</p>

<p>a5. Add a title and description.</p>

<p>a6. Enter tags (optional).</p>

<p>a7. Click 'Add another...' to add more than one media item.</p>

<p>a8. To finish, click 'Apply changes and Send to Gallery'.</p>

<p>&nbsp;</p>

<h2><a name=blogs></a><span class=SpellE>Blogs</span></h2>

<p><b>q. How to post a <span class=SpellE>Blog</span> post into <span
class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Click on '<span class=SpellE>Blogs</span>' on the main menu to go to the <span
class=SpellE>blogs</span> <span class=SpellE>frontpage</span>.</p>

<p>a2. Click 'Create Content' on the secondary menu.</p>

<p>a3. Enter a <span class=SpellE>blog</span> post title and the post details
under 'Description'.</p>

<p>a4. Enter tags to describe the post (optional).</p>

<p>a5. Tick the 'Route post to <span class=SpellE>Peopleaggregator</span>
Homepage' option if you want the post to appear on the main <span class=SpellE>PeopleAggregator</span>
page.</p>

<p>a6. If you have external <span class=SpellE>blogs</span> set up, you can use
OutputThis.org to route your post there.</p>

<p>a7. You may also publish the post to any Public group that you belong to, by
selecting the appropriate Group <span class=SpellE>Blogs</span> (<span
class=SpellE>nb</span>: only applies to Public Groups, not Private ones).</p>

<p>a8. Click 'Publish Post' to complete the post.&nbsp;</p>

<p>&nbsp;</p>

<p><b>q. How to create a <span class=SpellE>Podcast</span> (audio <span
class=SpellE>blog</span> post)?</b></p>

<p>a. Click on '<span class=SpellE>Blogs</span>' on the main menu to go to the <span
class=SpellE>blogs</span> <span class=SpellE>frontpage</span>.</p>

<p>a2. Click 'Create Content' on the secondary menu.</p>

<p>a3. There are 8 post type options, illustrated by images. Select the 'Audio'
type (the second one listed).</p>

<p>a4. Enter a title for your post under 'Audio Title'.</p>

<p>a5. Under 'Audio', select the 'Browse' button and select the media item from
your computer. Alternatively enter a URL if your audio file already exists on
the Web.</p>

<p>a6. Enter an accompanying image for your audio file. Under 'Image', select
the 'Browse' button and select the media item from your computer. Alternatively
enter a URL if your image file already exists on the Web.</p>

<p>a7. Enter other metadata associated with the audio file (artist name,
creation date, etc). These fields are optional.</p>

<p>a8. Enter the post details under 'Description'.</p>

<p>a9. Enter tags to describe the post (optional).</p>

<p>a10. Tick the 'Route post to <span class=SpellE>Peopleaggregator</span>
Homepage' option if you want the post to appear on the main <span class=SpellE>PeopleAggregator</span>
page.</p>

<p>a11. If you have external <span class=SpellE>blogs</span> set up, you can
use OutputThis.org to route your post there.</p>

<p>a12. You may publish the post to any Public group that you belong to, by
selecting the appropriate Group <span class=SpellE>Blogs</span> (<span
class=SpellE>nb</span>: only applies to Public Groups, not Private ones).</p>

<p>a13. You can also add the audio file to one of your albums by selecting from
the 'My Albums' drop-down list.</p>

<p>a14. Click 'Publish Post' to complete the post.</p>

<p>&nbsp;</p>

<p><b>q. How to <span class=SpellE>Vlog</span> (video <span class=SpellE>blog</span>)?</b></p>

<p>a. Click on '<span class=SpellE>Blogs</span>' on the main menu to go to the <span
class=SpellE>blogs</span> <span class=SpellE>frontpage</span>.</p>

<p>a2. Click 'Create Content' on the secondary menu.</p>

<p>a3. There are 8 post type options, illustrated by images. Select the 'Video'
type (the fourth one listed).</p>

<p>a4. Enter a title for your post under 'Video Title'.</p>

<p>a5. Under 'Video', select the 'Browse' button and select the media item from
your computer. Alternatively enter a URL if your video file already exists on
the Web.</p>

<p>a6. Enter an accompanying image for your video file. Under 'Image', select
the 'Browse' button and select the media item from your computer. Alternatively
enter a URL if your image file already exists on the Web.</p>

<p>a7. Enter other metadata associated with the video file (artist name,
creation date, etc). These fields are optional.</p>

<p>a8. Enter the post details under 'Description'.</p>

<p>a9. Enter tags to describe the post (optional).</p>

<p>a10. Tick the 'Route post to <span class=SpellE>Peopleaggregator</span>
Homepage' option if you want the post to appear on the main <span class=SpellE>PeopleAggregator</span>
page.</p>

<p>a11. If you have external <span class=SpellE>blogs</span> set up, you can
use OutputThis.org to route your post there.</p>

<p>a12. You may publish the post to any Public group that you belong to, by
selecting the appropriate Group <span class=SpellE>Blogs</span> (<span
class=SpellE>nb</span>: only applies to Public Groups, not Private ones).</p>

<p>a13. You can also add the video file to one of your albums by selecting from
the 'My Albums' drop-down list.</p>

<p>a14. Click 'Publish Post' to complete the post.</p>

<p>&nbsp;</p>

<p><b>q. How to post a People post into <span class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Click on '<span class=SpellE>Blogs</span>' on the main menu to go to the <span
class=SpellE>blogs</span> <span class=SpellE>frontpage</span>.</p>

<p>a2. Click 'Create Content' on the secondary menu.</p>

<p>a3. There are 8 post type options, illustrated by images. Select the
'People' type (the seventh one listed).</p>

<p>a4. Enter the details of the person (name, city, etc) in the given text fields.</p>

<p>a5. Under 'Picture for this person', select the 'Browse' button and select
the image from your computer. Alternatively enter a URL if your image file
already exists on the Web.</p>

<p>a6. Under '<span class=SpellE>Blog</span> Links' enter <span class=SpellE>blog</span>
title and link for each <span class=SpellE>blog</span> that the person owns.
Click the 'Add another <span class=SpellE>blog</span> link' for each successive
<span class=SpellE>blog</span>.</p>

<p>a7. Enter other metadata associated with the person (profile, quote,
Favorite movies, etc). These fields are optional.</p>

<p>a8. Enter tags to describe the person (optional).</p>

<p>a9. Tick the 'Route post to <span class=SpellE>Peopleaggregator</span>
Homepage' option if you want the post to appear on the main <span class=SpellE>PeopleAggregator</span>
page.</p>

<p>a10. If you have external <span class=SpellE>blogs</span> set up, you can
use OutputThis.org to route your post there.</p>

<p>a11. You may publish the post to any Public group that you belong to, by
selecting the appropriate Group <span class=SpellE>Blogs</span> (<span
class=SpellE>nb</span>: only applies to Public Groups, not Private ones).</p>

<p>a12. Click 'Publish Post' to complete the post.</p>

<p>&nbsp;</p>

<p><b>q. How do I edit my posts?</b></p>

<p>a. Click on '<span class=SpellE>Blogs</span>' on the main menu to go to the <span
class=SpellE>blogs</span> <span class=SpellE>frontpage</span>.</p>

<p>a2. Click 'Manage Content' on the secondary menu.</p>

<p>a3. The default <span class=GramE>list is 'All Content', but select</span> a
specific content type to refine the list.</p>

<p>a4. Click the pencil icon to edit a post.</p>

<p>a5. Modify the post.</p>

<p>a6. Click 'Publish Post' to complete the edit.&nbsp;</p>

<p>&nbsp;</p>

<p><span class=GramE><b>q</b></span><b>. how do I delete a post?</b></p>

<p>a. Click on '<span class=SpellE>Blogs</span>' on the main menu to go to the <span
class=SpellE>blogs</span> <span class=SpellE>frontpage</span>.</p>

<p>a2. Click 'Manage Content' on the secondary menu.</p>

<p>a3. The default <span class=GramE>list is 'All Content', but select</span> a
specific content type to refine the list.</p>

<p>a4. Tick the checkbox next to the post and click the 'Delete' button.</p>

<p>&nbsp;</p>

<p><b>q. What is <span class=SpellE>OutputThis</span> and how do I use it?</b></p>

<p>a. <span class=SpellE>OutputThis</span> is a service that lets you post to
multiple locations at once. For example, you could make a post on <span
class=SpellE>PeopleAggregator</span> and also send it to your <span
class=SpellE>WordPress</span> <span class=SpellE>blog</span>, your <span
class=SpellE>TypePad</span> <span class=SpellE>blog</span>, your <span
class=SpellE>Blogger</span> <span class=SpellE>blog</span>, and any other
service that supports the <span class=SpellE>metaWeblog</span> API.</p>

<p>a2. First, you need an account <a href="http://outputthis.org/">on
outputthis.org</a>. After registering, go to the 'add a new target' page. Enter
the following information:</p>

<ol start=1 type=1>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l2 level1 lfo3;tab-stops:list .5in'>The name of your <span
     class=SpellE>weblog</span> (this is so you can identify it later when
     posting).</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l2 level1 lfo3;tab-stops:list .5in'>- The XML-RPC URL. For <span
     class=SpellE>Wordpress</span>, this is the URL of your <span class=SpellE>blog</span>
     plus <span class=SpellE>xmlrpc.php</span>, e.g. if your <span
     class=SpellE>blog</span> is at http://foobar.wordpress.com/, your XML-RPC
     URL is http://foobar.wordpress.com/xmlrpc.php.</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l2 level1 lfo3;tab-stops:list .5in'>- The API supported. Most
     modern <span class=SpellE>blogging</span> tools support the <span
     class=SpellE>MetaWeblog</span> API.</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l2 level1 lfo3;tab-stops:list .5in'>- Your login name and API
     password. This is usually the login name and password you use to log in to
     your <span class=SpellE>blogging</span> tool.</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l2 level1 lfo3;tab-stops:list .5in'>- Your <span class=SpellE>weblog</span>
     ID. For <span class=SpellE>Wordpress</span>, this is always 1. For Movable
     Type, each <span class=SpellE>blog</span> will have a different ID and you
     will have to check on your site.</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l2 level1 lfo3;tab-stops:list .5in'>Now click 'add target'.</li>
</ol>

<p>a3. Next you need to configure your account in <span class=SpellE>PeopleAggregator</span>.&nbsp;</p>

<ol start=1 type=1>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l0 level1 lfo4;tab-stops:list .5in'>First, go to your private
     page.</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l0 level1 lfo4;tab-stops:list .5in'>Now click &quot;edit your
     profile&quot; and select the &quot;General Info&quot; tab.&nbsp;</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l0 level1 lfo4;tab-stops:list .5in'>About halfway down there are
     spaces for your <span class=SpellE>OutputThis</span> username and
     password. Enter the username and password you used to sign up to <span
     class=SpellE>OutputThis</span>, <span class=GramE>then</span> click 'Apply
     Changes'.</li>
 <li class=MsoNormal style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;
     mso-list:l0 level1 lfo4;tab-stops:list .5in'>Now you are ready to use <span
     class=SpellE>OutputThis</span> to send posts from <span class=SpellE>PeopleAggregator</span>
     to your <span class=SpellE>blogs</span>!</li>
</ol>

<p>a4. In the 'create post' page, you will now see a box labeled &quot;External
<span class=SpellE>Blog</span>&quot;. To post via <span class=SpellE>OutputThis</span>
to your external <span class=SpellE>blogs</span>, select the ones you want to
post to and write your post as usual. Once you publish it, you should see it
appear on all the locations selected as well as on <span class=SpellE>PeopleAggregator</span>.</p>

<p>&nbsp;</p>

<p><b>q. What about the <span class=SpellE>PeopleAggregator</span> <span
class=SpellE>blog</span> object  some <span class=SpellE>Javascript</span> I
can insert into my <span class=SpellE>blog</span> or other web page?</b></p>

<p>This feature is coming in version 1.2.</p>

<p>&nbsp;</p>

<p><b>q. How do I let people know what my <span class=SpellE>blog</span> is?</b></p>

<p>Your <span class=SpellE>blog</span> is located at 'My Public Page'. People
can subscribe to it by clicking on the small orange icon in the top-right
corner of your <span class=SpellE>blog</span> (its RSS feed).</p>

<p>&nbsp;</p>

<p><b>q. How do I move a <span class=SpellE>blog</span> post to somewhere else
(community, group, etc.)</b></p>

<p>We will be adding this to the 'Manage Content' screen in version 1.2.</p>

<p>&nbsp;</p>

<p><b>q. Whats the difference between <span class=SpellE>Permalink</span> and <span
class=SpellE>Trackback</span>?</b></p>

<p>a. A <span class=SpellE>permalink</span> is the permanent link to your post,
which is the link you use to point people to your post.</p>

<p>a2. A <span class=SpellE>trackback</span> allows you to see who has seen the
original post and has written another post concerning it. The system works by
sending a 'ping' between the <span class=SpellE>blogs</span>, and therefore
providing the alert [adapted from <a
href="http://en.wikipedia.org/wiki/TrackBack"><span class=SpellE>Wikipedia</span>
definition</a>].</p>

<p>&nbsp;</p>

<p><b>q. What is RSS and how can I use it?</b></p>

<p>a. RSS stands for Really Simple Syndication and allows other people to
subscribe to your <span class=SpellE>blog</span>. In <span class=SpellE>PeopleAggregator</span>
we provide <span class=GramE>full-text feeds, which means</span> that other
people may read your <span class=SpellE>blog</span> posts in their RSS
Readers.&nbsp;</p>

<p>a2. To subscribe to an RSS feed, copy and paste the URL of the RSS feed
(identifiable by a small orange icon) into a news reader such as <span
class=SpellE>MyYahoo</span>, <span class=SpellE>Newsgator</span> or <span
class=SpellE>Bloglines</span>.</p>

<p>&nbsp;</p>

<p><b>q. How do I route my external <span class=SpellE>blog</span> posts INTO <span
class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Use <span class=SpellE>OutputThis</span> to write <span class=SpellE>blog</span>
posts in another system and automatically route it to <span class=SpellE>PeopleAggregator</span>.
See <span class=SpellE>OutPutThis</span> instructions in the FAQ above.</p>

<p>&nbsp;</p>

<h2><a name=microcontent></a><span class=SpellE>Microcontent</span>: Events
&amp; Reviews</h2>

<p><b>q. How to post an Event post into <span class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Click on '<span class=SpellE>Blogs</span>' on the main menu to go to the <span
class=SpellE>blogs</span> <span class=SpellE>frontpage</span>.</p>

<p>a2. Click 'Create Content' on the secondary menu.</p>

<p>a3. There are 8 post type options, illustrated by images. Select the 'Event'
type (the fifth one listed).</p>

<p>a4. There are 3 sub-types for Events: Generic (the default), Conference and
Concert. The rest of these instructions are for 'Generic', but the requirements
for the others are basically the same.</p>

<p>a5. Enter a title for your post under 'Event Name'.</p>

<p>a6. Enter an accompanying image for your audio file. Under 'Image', select
the 'Browse' button and select the media item from your computer. Alternatively
enter a URL if your image file already exists on the Web.</p>

<p>a7. Enter other metadata associated with the audio file (entry fee, start
and end date, location, etc). These fields are optional.</p>

<p>a8. Enter the post details under 'Event Description'.</p>

<p>a9. Under 'Links to this event' enter Link title and URL for each site that
has relevant information about the event. Click the 'Add another link' for each
successive link.</p>

<p>a10. Enter tags to describe the post (optional).</p>

<p>a11. Tick the 'Route post to <span class=SpellE>Peopleaggregator</span>
Homepage' option if you want the post to appear on the main <span class=SpellE>PeopleAggregator</span>
page.</p>

<p>a12. If you have external <span class=SpellE>blogs</span> set up, you can
use OutputThis.org to route your post there.</p>

<p>a13. You may publish the post to any Public group that you belong to, by
selecting the appropriate Group <span class=SpellE>Blogs</span> (<span
class=SpellE>nb</span>: only applies to Public Groups, not Private ones).</p>

<p>a14. Click 'Publish Post' to complete the post.</p>

<p>&nbsp;</p>

<p><b>q. How to post a Review post into <span class=SpellE>PeopleAggregator</span>?</b></p>

<p>a. Click on '<span class=SpellE>Blogs</span>' on the main menu to go to the <span
class=SpellE>blogs</span> <span class=SpellE>frontpage</span>.</p>

<p>a2. Click 'Create Content' on the secondary menu.</p>

<p>a3. There are 8 post type options, illustrated by images. Select the
'Review' type (the sixth one listed).</p>

<p>a4. There are currently 13 sub-types for Reviews: Local Service, Movie,
Song, etc. The rest of these instructions are for 'Movie', but the requirements
for the others are basically the same.</p>

<p>a5. Enter a title for your post under 'Movie/TV show name'.</p>

<p>a6. Enter an accompanying image for your audio file. Under 'Image', select
the 'Browse' button and select the media item from your computer. Alternatively
enter a URL if your image file already exists on the Web.</p>

<p>a7. Enter other metadata associated with the audio file (Homepage, IMDB
link, Director, etc). These fields are optional.</p>

<p>a8. Enter the post details under 'Review'.</p>

<p>a9. Enter tags to describe the post (optional).</p>

<p>a10. Tick the 'Route post to <span class=SpellE>Peopleaggregator</span>
Homepage' option if you want the post to appear on the main <span class=SpellE>PeopleAggregator</span>
page.</p>

<p>a11. If you have external <span class=SpellE>blogs</span> set up, you can
use OutputThis.org to route your post there.</p>

<p>a12. You may publish the post to any Public group that you belong to, by
selecting the appropriate Group <span class=SpellE>Blogs</span> (<span
class=SpellE>nb</span>: only applies to Public Groups, not Private ones).</p>

<p>a13. Click 'Publish Post' to complete the post.</p>

<p>&nbsp;</p>

<p><b>q. How do I set what my <span class=SpellE>Flickr</span> account is?</b></p>

<p>a. Click on 'My Page'.</p>

<p>a2. Click 'Edit Profile'.</p>

<p>a3. <span class=GramE>Click on 'General Info'.</span></p>

<p>a4. Scroll down until you see the '<span class=SpellE>Flickr</span> Email
Id' text field. Enter your <span class=SpellE>Flickr</span> username into this
field.</p>

<p>a5. Click 'Apply Changes'.</p>

<p>&nbsp;</p>

<p><b>q. How do I set what my delicious account is?</b></p>

<p>a. Click on 'My Page'.</p>

<p>a2. Click 'Edit Profile'.</p>

<p>a3. <span class=GramE>Click on 'General Info'.</span></p>

<p>a4. Scroll down until you see the 'Delicious Id' text field. Enter your
Delicious username into this field.</p>

<p>a5. Click 'Apply Changes'.</p>

<p>&nbsp;</p>

<p><b>q. Where are events?</b></p>

<p>a. Events are located in the <span class=SpellE>Blogs</span> section. They
are created in <span class=SpellE>Blogs</span> &gt; Create Content &gt; Event.</p>

<p>&nbsp;</p>

<h2><a name=messaging></a>Messaging</h2>

<p><b>q. How do I send a Message?</b></p>

<p>a. Click 'My Page' on the main menu.</p>

<p>a2. Click '<st1:place w:st="on"><st1:PlaceName w:st="on">Message</st1:PlaceName>
 <st1:PlaceType w:st="on">Center</st1:PlaceType></st1:place>' on the secondary
menu bar. There you will see your Mail Box.</p>

<p>a3. Click the 'Compose' button.&nbsp;</p>

<p>a4. Complete your email as you would in your usual email inbox. Click the
'Send' button to send your message.</p>

<p>&nbsp;</p>

<p><b>q. Are their IM capabilities?</b></p>

<p>a. Future releases will have this feature.</p>

</div>
<div class="footer"><p>Copyright &copy; 2006 Broadband Mechanics Inc. All rights reserved.</p></div>
</div>
</body>
</html>

