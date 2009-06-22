using System.Collections.Specialized;
using System.Web;
using LitJson;

namespace BBM.RemoteWidget
{
    /*
     * The RemoteWidget class represents a single widget.  See the instructions in RemoteWidgetController.cs for
     * detail on how to use this in your project.
     * 
     *  Copyright (C) 2007 Broadband Mechanics, Inc.
     *  Phillip Pearson <phil@broadbandmechanics.com>
     * 
     * Instantiation:
     * 
     * 1. Construct a RemoteWidget object, passing:
     * 
     *    - The name of the PeopleAggregator module to connect to, e.g. "ReviewModule"
     * 
     *    - The prefix you want to use for this module's query string / form parameters.
     *      Take care not to use anything that will show in other parameters.
     *      e.g. "rv_".
     * 
     *    - The skin and view to use.  In the case of PeopleAggregator, these variables
     *      select the display template to use for the module; "skin" normally refers
     *      to your application, and "view" specifies the display mode.  e.g. skin =
     *      "videoplay" and view = "item" for ReviewModule will use
     *      web/BetaBlockModules/ReviewModule/videoplay_item.tpl.
     * 
     * 2. Set any required parameters.  For example, ReviewModule requires subject_type
     *    and subject_id:
     * 
     *    string movie_id = Request.QueryString["id"];
     *    // (validate movie_id before passing to m_reviews)
     *    m_reviews.Set("subject_type", "movie");
     *    m_reviews.Set("subject_id", movie_id);
     */
    public class RemoteWidget
    {
        string m_module_name, // PA module name, e.g. "ReviewModule"
            m_module_id = null, // local module id (set by controller)
            m_param_prefix, // prefix for parameters, e.g. "rv_"
            m_method = "get", // "get", "post", "ajax"
            m_ajax_url = null, // URL to post to to action an AJAX request to this widget
            m_post_url = null, // URL to post to to action a POST to this widget
            m_html = null; // html received from server for display
        NameValueCollection m_args, // config args for this widget - view, skin, etc
            m_params; // parameters for this widget;

        public RemoteWidget(string module_name, string param_prefix, string skin, string view)
        {
            m_module_name = module_name;
            m_param_prefix = param_prefix;
            // set module configuration arguments
            m_args = new NameValueCollection();
            m_args["skin"] = skin;
            m_args["view"] = view;
            // make placeholder for user-supplied args (from Request.QueryString and Request.Form)
            m_params = new NameValueCollection();
        }

        // prefix for form/query string parameters (e.g. "rv_")
        public string ParamPrefix { get { return m_param_prefix; } }

        // properties set by widget controller
        public string ID { set { m_module_id = value; } }
        public string Method { get { return m_method; }  set { m_method = value; } }
        public string AjaxURL { set { m_ajax_url = value; } }
        public string PostURL { set { m_post_url = value; } }

        // HTML received from server
        public string HTML { set { m_html = value; } get { return m_html; } }

        // Pull relevant params (starting with m_param_prefix) from an HttpRequest.
        public void CollectParams(HttpRequest req)
        {
            CollectParamsFrom(req.QueryString);
            CollectParamsFrom(req.Form);
        }

        // Pull relevant params from a NameValueCollection, e.g. Request.QueryString
        // or Request.Form (stripping m_param_prefix from the start).
        void CollectParamsFrom(NameValueCollection nvc)
        {
            foreach (string k in nvc)
            {
                if (k.StartsWith(m_param_prefix))
                {
                    m_params[k.Substring(m_param_prefix.Length)] = nvc[k];
                }
            }
        }

        public void Set(string k, string v)
        {
            m_params[k] = v;
        }

        public void DumpJson(JsonWriter w)
        {
            w.WriteObjectStart();
            w.WritePropertyName("id");
            w.Write(m_module_id);
            w.WritePropertyName("name");
            w.Write(m_module_name);
            w.WritePropertyName("method");
            w.Write(m_method);
            w.WritePropertyName("ajax_url");
            w.Write(m_ajax_url);
            w.WritePropertyName("post_url");
            w.Write(m_post_url);
            w.WritePropertyName("param_prefix");
            w.Write(m_param_prefix);
            w.WritePropertyName("args");
            w.WriteNameValueCollection(m_args);
            w.WritePropertyName("params");
            w.WriteNameValueCollection(m_params);
            w.WriteObjectEnd();
        }

        public void LoadFromJson(JsonData data)
        {
            m_html = data["html"].ToString();
        }
    }
}
