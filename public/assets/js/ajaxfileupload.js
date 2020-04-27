// (function ($) {
//     'use strict';
    var FU =  ({
    createUploadIframe: function (id, uri) {
        'use strict';
        //create frame
        var frameId = 'jUploadFrame' + id;
        var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
        if (window.ActiveXObject) {
            if (typeof uri === 'boolean') {
                iframeHtml += ' src="' + 'javascript:false' + '"';
            }
            else if (typeof uri === 'string') {
                iframeHtml += ' src="' + uri + '"';
            }
        }
        iframeHtml += ' />';
        window.jQuery(iframeHtml).appendTo(document.body);

        return window.jQuery('#' + frameId).get(0);
    },
    createUploadForm: function (id, fileElementId, data) {
        'use strict';
        //create form
        var formId = 'jUploadForm' + id;
        var fileId = 'jUploadFile' + id;
        var form = window.jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');
        if (data) {
            for (var i in data) {
                window.jQuery('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
            }
        }
        var oldElement = window.jQuery('#' + fileElementId);
        var newElement = window.jQuery(oldElement).clone();
        window.jQuery(oldElement).attr('id', fileId);
        window.jQuery(oldElement).before(newElement);
        window.jQuery(oldElement).appendTo(form);


        //set attributes
        window.jQuery(form).css('position', 'absolute');
        window.jQuery(form).css('top', '-1200px');
        window.jQuery(form).css('left', '-1200px');
        window.jQuery(form).appendTo('body');
        return form;
    },

    ajaxFileUpload: function (s) {
        'use strict';
        s = jQuery.extend({}, window.jQuery.ajaxSettings, s);
        var id = new Date().getTime();
        var form = FU.createUploadForm(id, s.fileElementId, (typeof(s.data) === 'undefined' ? false : s.data));
        var io = FU.createUploadIframe(id, s.secureuri);
        var frameId = 'jUploadFrame' + id;
        var formId = 'jUploadForm' + id;
        // Watch for a new set of requests
        if (s.global && !(window.jQuery.active++)) {
            window.jQuery.event.trigger("ajaxStart");
        }
        var requestDone = false;
        // Create the request object
        var xml = {};
        if (s.global) {
            window.jQuery.event.trigger("ajaxSend", [xml, s]);
        }
        // Wait for a response to come back
        var uploadCallback = function (isTimeout) {
            var io = document.getElementById(frameId);
            try {
                if (io.contentWindow) {
                    xml.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
                    xml.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;

                } else if (io.contentDocument) {
                    xml.responseText = io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
                    xml.responseXML = io.contentDocument.document.XMLDocument ? io.contentDocument.document.XMLDocument : io.contentDocument.document;
                }
            } catch (e) {
                window.jQuery.error(e);
            }
            if (xml || isTimeout === "timeout") {
                requestDone = true;
                var status;
                try {
                    status = isTimeout !== "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified
                    if (status !== "error") {
                        // process the data (runs the xml through httpData regardless of callback)
                        var data = FU.uploadHttpData(xml, s.dataType);
                        // If a local callback was specified, fire it and pass it the data

                        if (s.success) {
                            s.success(data, status);
                        }

                        // Fire the global callback
                        if (s.global) {
                            window.jQuery.event.trigger("ajaxSuccess", [xml, s]);
                        }
                    } else{
                        window.jQuery.error(s + xml + status);
                    }
                } catch (e) {
                    status = "error";
                    window.jQuery.error(e);
                }

                // The request was completed
                if (s.global){
                    window.jQuery.event.trigger("ajaxComplete", [xml, s]);
                }

                // Handle the global AJAX counter
                if (s.global && !--window.jQuery.active){
                    window.jQuery.event.trigger("ajaxStop");
                }

                // Process result
                if (s.complete) {
                    s.complete(xml, status);
                }

                window.jQuery(io).unbind();

                setTimeout(function () {
                    try {
                        window.jQuery(io).remove();
                        window.jQuery(form).remove();

                    } catch (e) {
                        window.jQuery.error(e);
                    }

                }, 10);

                xml = null;

            }

        };
        // Timeout checker
        if (s.timeout > 0) {
            setTimeout(function () {
                // Check to see if the request is still happening
                if (!requestDone) {
                    uploadCallback("timeout");
                }
            }, s.timeout);
        }
        try {
            var formNew = window.jQuery('#' + formId);
            window.jQuery(formNew).attr('action', s.url);
            window.jQuery(formNew).attr('method', 'POST');
            window.jQuery(formNew).attr('target', frameId);
            if (formNew.encoding) {
                window.jQuery(formNew).attr('encoding', 'multipart/form-data');
            }
            else {
                window.jQuery(formNew).attr('enctype', 'multipart/form-data');
            }
            window.jQuery(formNew).submit(); //Submit the form

        } catch (e) {
            window.jQuery.error(e);
        }

        window.jQuery('#' + frameId).load(uploadCallback);
        return {
            abort: function () {
            }
        };

    },

    uploadHttpData: function (r, type) {
        'use strict';
        var data = !type;
        data = type === "xml" || data ? r.responseXML : r.responseText;
        // If the type is "script", eval it in global context
        if (type === "script") {
            window.jQuery.globalEval(data);
        }
        // Get the JavaScript object, if JSON is used.
        if (type === "json") {
            //chrome含style,firefox不含
            data = data.replace('<pre style="word-wrap: break-word; white-space: pre-wrap;">', '').replace('<pre style="word-wrap: break-word; white-space: pre-wrap">', '').replace('<pre>', '').replace('</pre>', '').replace(/\\\\/g, '/');

            eval("data = " + data);
        }
        // evaluate scripts within html
        if (type === "html"){
            window.jQuery("<div>").html(data).evalScripts();
        }

        return data;
    }
});
// })(jQuery);