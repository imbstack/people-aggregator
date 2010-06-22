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
<?php
/***************************************************************************
 *   Copyright (C) 2009 by Zoran Hron   *
 *   zhron@broadbandmechanics.com   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Library General Public License as       *
 *   published by the Free Software Foundation; either version 2 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU Library General Public     *
 *   License along with this program; if not, write to the                 *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
require_once "web/includes/classes/InPlaceEdit.class.php";
printHeader();
//echo "<h1>Hello World!</h1>\n";
printFooter();

function printHeader() {
    echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<!--
-->
<script type="text/javascript" language="javascript" src="/Themes/Default/javascript/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" language="javascript" src="/Themes/Default/javascript/jquery.lite.js"></script>
<script type="text/javascript" language="javascript" src="/Themes/Default/javascript/inplaced.js"></script>
	<meta name="description" content="" />
	<meta name="author" content="Zoran Hron" />
	<meta name="keywords" content="" />
	<title>ZHF</title>
<style type="text/css">
 .inplace_active { background: #CFFCCF; cursor:pointer;cursor:hand}
</style>
</head>
<body>
    <h1>AJAX in-place edit example page</h1>
    <h3>Edit Table example</h3>
    <div class="divstyle inplace_edit" ajaxUrl="/ajax/edinplace_test.php" id="my_div" tinyMCE="rich" style="width:240px; height:80px">
       <table border="1" style="border: 1px solid silver; border-collapse: collapse; width:100%; height:100%">
       <thead>
       <tr>
        <th>Name</th>
        <th>Address</th>
       <tr>
       </thead>
       <tbody>
       <tr>
        <td>Zoran Hron</td>
        <td>II Anina 7</td>
       </tr>
       </tbody>
       </table>
    </div>
    <hr />
    <h3>Edit Table cell example</h3>
    <table border="1" style="border: 1px solid silver; border-collapse: collapse; width:240px; height:68px">
    <tr>
    <td>Click</td>
    <td id="user_name" class="my_class inplace_edit" ajaxUrl="/ajax/edinplace_test.php" tinyMCE="normal" >
      <b>here</b>
    </td>
    <td>to</td>
    <td>edit</td>
    </tr>
    </table>
    <hr />
    <h3>INPUT example</h3>
    <div>
    <INPUT id="input_name" class="my_class inplace_edit" ajaxUrl="/ajax/edinplace_test.php" tinyMCE="basic" value="ClickMe" style="border: 1px solid silver" />
    </div>
    <hr />
    <h3>TEXTAREA example</h3>
    <textarea id="tarea_name" class="my_class inplace_edit" ajaxUrl="/ajax/edinplace_test.php" tinyMCE="minimal" style="border: 1px solid silver; width: 240px; height: 120px" >Click here to edit</textarea>
    <hr/>
    <h3>Edit List element example</h3>
EOF;
    echo '<ul style="width: 180px; list-style-image: none; list-style-type: none">'.'<li>First element</li>'.InPlaceEdit::li('li_elem2', '/ajax/edinplace_test.php', 'Editable element', 'border-bottom: 1px solid silver').'<li>Last element</li>'.'</ul>';
}

function printFooter() {
    echo "</body>\n</html>";
}
?>
