using System;
using System.Collections;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.Text;
using System.Web;
using LitJson;
using System.Web.UI;

namespace BBM.RemoteWidget
{
    /* 
     * The RemoteWidgetController class encapsulates a connection to a
     * PeopleAggregator install, running in widget server mode.
     * 
     *  Copyright (C) 2007 Broadband Mechanics, Inc.
     *  Phillip Pearson <phil@broadbandmechanics.com>
     *
     * Usage:
     * 
     * 1. Inside your Page_Load method:
     * 
     *  - Instantiate a copy of this class, passing the Request object, the URL of the remote widget server,
     *    and the prefix to add to form/query string parameters pertaining to the widget system as a whole.
     * 
     *  - Call SetUser to set user information to be sent through with the widget request.
     * 
     *  - Instantiate a RemoteWidget object for every widget on the page, and add it to the controller with Add().
     *    (See RemoteWidget.cs for instantiation information.)
     * 
     *  - Call SetupComplete(), which will either make the HTTP request straight away if required, or leave it to later.
     * 
     * 2. Inside your page:
     * 
     *  - After writing out the HTML <head> element (including all links to JavaScript and CSS), call LoadWidgets()
     *    to make the HTTP request to the remote server.
     * 
     *  - Wherever you want to insert a widget into your HTML, print out the widget's HTML property.
     */
    public class RemoteWidgetController
    {
        Page m_page; // current HTTP request
        string m_remote_url, // url of widget server
            m_param_prefix; // prefix for global widget parameters (usually "w_")
        Hashtable m_widgets;
        int next_id = 1;
        bool m_setup_complete = false; // flag to say if SetupComplete() has been called
        bool m_widgets_loaded = false; // flag to say if LoadWidgets() has been called
        string m_post_target = null, // ParamPrefix for module this request is POSTed to
            m_ajax_target = null; // ParamPrefix for module this request is for (AJAX mode)
        RemoteWidget target = null; // RemoteWidget object we are posting/ajaxing to
        string m_postback_url_prefix;

        // info about the current user
        string m_user_namespace = null,
            m_user_id = null,
            m_user_email = null,
            m_user_first_name = null,
            m_user_last_name = null;

        public RemoteWidgetController(Page page, string remote_url, string param_prefix)
        {
            m_page = page;
            m_remote_url = remote_url;
            m_param_prefix = param_prefix;
            m_widgets = new Hashtable();

            // see if we have a POST or an AJAX request for a widget
            m_post_target = m_page.Request.QueryString[m_param_prefix + "post"];
            m_ajax_target = m_page.Request.QueryString[m_param_prefix + "ajax"];
            if (m_post_target != null && m_ajax_target != null) throw new Exception("You may only POST or AJAX-POST to one widget per request");

            // strip extra crap out of the url so we can safely build urls
            string url = m_page.Request.Url.OriginalString.Split(new char[] { '?' }, 2, StringSplitOptions.RemoveEmptyEntries)[0] + "?";
            foreach (string k in m_page.Request.QueryString) {
                // copy safe-looking params across - stripping out anything starting with our widget prefix
                string v = m_page.Request.QueryString[k];
                if (k.StartsWith(m_param_prefix)) continue;
                url += m_page.Server.UrlEncode(k) + "=" + m_page.Server.UrlEncode(v);
            }
            m_postback_url_prefix = url;
        }

        public void SetUser(string ns, string id, string email, string first_name, string last_name)
        {
            m_user_namespace = ns;
            m_user_id = id;
            m_user_email = email;
            m_user_first_name = first_name;
            m_user_last_name = last_name;
        }

        // add a widget to the list
        public void Add(RemoteWidget w)
        {
            // read parameters from Request
            w.CollectParams(m_page.Request);

            // assign an ID for the module
            string w_id = next_id.ToString();
            ++next_id;

            w.ID = w_id;

            // work out URLs that will flag a post to this module
            string url = m_postback_url_prefix + m_param_prefix;

            w.AjaxURL = url + "ajax=" + w.ParamPrefix;
            w.PostURL = url + "post=" + w.ParamPrefix;

            // see if the current request is a POST or AJAX call to this module
            if (m_post_target == w.ParamPrefix)
            {
                w.Method = "post";
                target = w;
            }
            else if (m_ajax_target == w.ParamPrefix)
            {
                w.Method = "ajax";
                target = w;
            }

            // and finally keep track of it so we can request it later from the srver
            m_widgets[w_id] = w;
        }

        // the page will call SetupComplete() once it's done 
        public void SetupComplete()
        {
            Debug.Assert(!m_setup_complete);
            m_setup_complete = true;

            // if we're handling a form post, we want to send the HTTP request right now,
            // otherwise we can wait until the page calls LoadWidgets().
            if (m_page.Request.RequestType == "POST")
            {
                if (target == null)
                {
                    // we couldn't find the target - so ignore this POST request
                }
                if (m_post_target != null)
                {
                    // It's a POST request, and we have a target, so it should be safe to intercept the request here.
                    // Not implemented yet... also doesn't play well with <form runat="server"> (you'll never get here
                    // as the form will post to the URL specified in the outer form rather than the widget-generated
                    // one).
                    throw new Exception("Directly POSTing to widgets not supported yet - use AJAX instead!");
                }
                else if (m_ajax_target != null)
                {
                    LoadWidgets(true);
                    m_page.Response.Write(target.HTML);
                    m_page.Response.End();
                }
                // if neither target is set, the POST wasn't for us - ignore it; the page should take care of it.
            }
        }

        // call this in the page, after outputting <script> and <link> tags referring to other things - so the client can
        // go off and do other stuff while we load the widgets.
        public void LoadWidgets()
        {
            LoadWidgets(false);
        }

        public void LoadWidgets(bool exclude_unless_post)
        {
            Debug.Assert(m_setup_complete, "You must call SetupComplete() before LoadWidgets()");
            if (m_widgets_loaded) return; // already loaded - presumably by SetupComplete()
            m_widgets_loaded = true;

            // make request to backend to get widget HTML
            JsonWriter w = new JsonWriter();

            w.WriteObjectStart();

            w.WritePropertyName("modules");
            w.WriteArrayStart();
            foreach (RemoteWidget wi in m_widgets.Values)
            {
                if (exclude_unless_post && wi.Method == "get") continue;
                wi.DumpJson(w);
            }
            w.WriteArrayEnd(); // modules

            w.WritePropertyName("global");
            w.WriteObjectStart();
            w.WritePropertyName("user");
            w.WriteObjectStart();
            w.WritePropertyName("namespace");
            w.Write(m_user_namespace);
            w.WritePropertyName("user_id");
            w.Write(m_user_id); // placeholder for now until we get shadow accounts working
            w.WritePropertyName("email");
            w.Write(m_user_email);
            w.WritePropertyName("first_name");
            w.Write(m_user_first_name);
            w.WritePropertyName("last_name");
            w.Write(m_user_last_name);
            w.WriteObjectEnd(); // user
            w.WriteObjectEnd(); // global

            w.WriteObjectEnd(); // outer

            string json_request = w.ToString();

            // now post the request to the backend server
            HttpWebRequest req = (HttpWebRequest)WebRequest.Create(m_remote_url);
            req.Method = "POST";
            req.ContentType = "application/x-javascript";
            byte[] post_data = Encoding.UTF8.GetBytes(json_request);
            req.ContentLength = post_data.Length;
            Stream post_stream = req.GetRequestStream();
            post_stream.Write(post_data, 0, post_data.Length);
            post_stream.Close();

            HttpWebResponse resp = (HttpWebResponse)req.GetResponse();
            StreamReader resp_stream = new StreamReader(resp.GetResponseStream());
            /*
            while (true)
            {
                string line = resp_stream.ReadLine();
                if (line == null) break;
                Debug.Print("line from response: " + line);
            }
            */
            string raw_data = resp_stream.ReadToEnd();
            resp.Close();
            string error = null;
            try
            {
                JsonData data = JsonMapper.ToObject(raw_data);

                // http request done - now handle the json response
                if (((IDictionary)data).Contains("error"))
                {
                    error = (string)data["error"];
                }
                else
                {
                    JsonData modules = data["modules"];
                    if (!modules.IsArray) error = "JSON server returned non-array for modules.";
                    else foreach (JsonData module in modules)
                    {
                        string module_id = module["id"].ToString();
                        RemoteWidget wi = (RemoteWidget)m_widgets[module_id];
                        wi.LoadFromJson(module);
                    }
                }
            }
            catch (JsonException)
            {
                error = "BAD JSON RESPONSE FROM WIDGET SERVER: " + raw_data;
            }
            if (error != null)
            {
                foreach (RemoteWidget wi in m_widgets.Values)
                {
                    wi.HTML = m_page.Server.HtmlEncode(error);
                }
            }
        }
    }
}
