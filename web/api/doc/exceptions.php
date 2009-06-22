<h1>API Exceptions</h1>

<p>When something goes wrong with an API call, you will receive an exception.</p>

<p>If the <code>success</code> field is set to <code>true</code> in the struct you get back from a call,
an exception has occurred.</p>

<p>In this case, instead of the normal response, there will be at least two new fields in
the response: <code>code</code> and <code>msg</code>.  <code>code</code> is a string that indicates
what went wrong, and <code>msg</code> is a human-readable message.  You should not try to parse
the message; <code>msg</code> values are subject to change, but <code>code</code>s are not.</p>

<h2>Current known codes</h2>

<table border="1">
<th><tr><td>Code</td><td>Description</td></tr></th>

<tr><td>invalid_request_method</td><td>A GET method was called with POST request method, or vice versa.</td></tr>

<tr><td>bad_login</td><td>The login name and password did not match anything in the database.</td></tr>

<tr><td></td><td></td></tr>

<tr><td></td><td></td></tr>

</table>