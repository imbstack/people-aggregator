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

header("Content-Type: text/html; charset=UTF-8");

?><h1>PA API - character encoding considerations</h1>

<h2>Input</h2>

<p>All input, whether specified in XML (for XML-RPC requests) a URL (for GET-type REST requests) or as POSTDATA (for POST-type REST requests), should be encoded as UTF-8.</p>

<p>ISO-8859-1 input will also be accepted, but UTF-8 is preferred.</p>

<h2>Output</h2>

<p>Non-ASCII characters in strings in JSON output are encoded in the <a href="http://www.json.org/">standard <code>\uXXXX</code> form</a>.

<p>All XML output (in XML-RPC and REST responses) is encoded with UTF-8.</p>

<h2>Examples</h2>

<p>GET example returning XML: <a href="../xml/peopleaggregator/echo?echoText=I%C3%B1t%C3%ABrn%C3%A2ti%C3%B4n%C3%A0liz%C3%A6ti%C3%B8n">peopleaggregator.echo (UTF-8)</a>, <a href="../xml/peopleaggregator/echo?echoText=I%F1t%EBrn%E2ti%F4n%E0liz%E6ti%F8n">peopleaggregator.echo (ISO-8859-1)</a>.</p>

<p><form method="POST" action="../xml/peopleaggregator/echoPost">POST example returning XML: <input type="text" name="echoText" value="I&#241;t&#235;rn&#226;ti&#244;n&#224;liz&#230;ti&#248;n"><input type="submit" value="peopleaggregator.echoPost"></form></p>

<p>GET example returning JSON: <a href="../json/peopleaggregator/echo?echoText=I%C3%B1t%C3%ABrn%C3%A2ti%C3%B4n%C3%A0liz%C3%A6ti%C3%B8n">peopleaggregator.echo (UTF-8)</a>, <a href="../json/peopleaggregator/echo?echoText=I%F1t%EBrn%E2ti%F4n%E0liz%E6ti%F8n">peopleaggregator.echo (ISO-8859-1)</a>.</p>

<p><form method="POST" action="../json/peopleaggregator/echoPost">POST example returning JSON: <input type="text" name="echoText" value="I&#241;t&#235;rn&#226;ti&#244;n&#224;liz&#230;ti&#248;n"><input type="submit" value="peopleaggregator.echoPost"></form></p>
