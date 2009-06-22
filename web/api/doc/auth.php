<h1>User authentication</h1>

<p>Some methods require authentication.  To authenticate, first call the <code><a href="peopleaggregator_login.html">login()</a></code> method, then pass 
the authentication token returned (in the <code>authToken</code> field of the return struct) as a parameter
called <code>authToken</code> to the method you wish to call.</p>

<p>Undoubtably there will be more methods of getting auth tokens in future; we plan to implement something
like the <a href="http://www.flickr.com/services/api/auth.spec.html">flickr authentication API</a> at some
point (although most likely without the mandatory registration).</p>

<!--

 <h2>Authentication for REST requests</h2>

  <p>REST requests should use basic HTTP authentication.  If an invalid 
  login/password combination is given, an HTTP <code>401 Authentication 
  Required</code> response code will be returned.</p>

 <h2>Authentication for XML-RPC calls</h2>

  <p>XML-RPC calls should pass authentication information in a struct called 
  <code>auth</code>, containing the following members:</p>
  
  <ul>
  
   <li><code>login</code>: A valid login name.</li>
   
   <li><code>password</code>: A valid password.</li>
  
  </ul>
  
  <p>If an invalid login/password combination is given, an XML-RPC fault with 
  code 4 will be raised.</p>

-->