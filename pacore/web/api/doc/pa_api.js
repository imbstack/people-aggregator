/*

	PeopleAggregator API access class.

	Usage:

	<script src="peopleaggregator_api_desc.js" type="text/javascript" language="javascript"></script>
	<script src="pa_api.js" type="text/javascript" language="javascript"></script>
	<script language="javascript">

		var api = new PA_API("http://mysite/api/json");

		api.call({
			method: 'getUserList',
			args: {
				'resultsPerPage': 20,
				'page': 1
			},
			onSuccess: function(resp) {
				alert("successful call; retrieved page "+resp.page+" of the user list");
			},
			onError: function(code, msg, resp) {
				alert("an error occurred; code "+code+", msg "+msg);
			}
		});

	</script>

*/

var PA_API = Class.create();
PA_API.prototype = {
    initialize: function(json_url) {
	if (!window.peopleaggregator_api_desc) {
	    alert("Cannot find peopleaggregator_api_desc variable; have you included peopleaggregator_api_desc.js?");
	    return;
        }
	this.desc = peopleaggregator_api_desc;
        this.json_url = json_url;
    },
    format_args: function(args) {
        var ret = [];
        for (var k in args) {
            ret.push(k+"="+escape(args[k]));
        }
        return ret.join("&");
    },
    call: function(cs) {
	var fd = this.desc.methods[cs.method];
	if (!fd) {
	    cs.onError("invalid_method", "Attempt to call unknown method "+cs.method);
	    return;
        }

        var url = this.json_url + "/" + cs.method.replace(/\./, '/');
        var qs = this.format_args(cs.args);
        new Ajax.Request(
            url, {
            method: fd.type,
            parameters: qs,
            onComplete: function(r) {
                // check for transport error
                if (r.status != 200) {
                    cs.onError("transport_error", "HTTP error "+r.status);
                    return;
                }
                // evaluate JSON response
                try {
                    var resp = eval("("+r.responseText+")");
                } catch (e) {
                    cs.onError("invalid_json", "Unable to evaluate JSON response");
                    return;
                }
                // check for error message inside JSON response
                if (!resp.success) {
                    cs.onError(resp.code, resp.msg, resp);
                    return;
                }
                // looks like it succeeded - pass control back to caller
                cs.onSuccess(resp);
            }});
    }
};
