function getParameterByName(name)
{
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function browser_supports_cors()
{
    if('withCredentials' in new XMLHttpRequest())
    {
        return true;
    }
    else if(typeof XDomainRequest !== 'undefined')
    {
        return true;
    }
    return false;
}

function browser_supports_font_face_css2(rule)
{
    var result = false;
    var head = document.head || document.getElementsByTagName('head')[0] || docElement;
    var style = document.createElement("style");

    style.type = 'text/css';
    head.insertBefore(style, head.firstChild);
    var sheet = style.sheet || style.styleSheet;
    try
    {
        sheet.insertRule(rule, 0);
        result = !(/unknown/i).test(sheet.cssRules[0].cssText);
        sheet.deleteRule(sheet.cssRules.length - 1);
    } catch(e) {}
    return result;
}

function browser_supports_font_face_older(rule)
{
    var head = document.head || document.getElementsByTagName('head')[0] || docElement;
    var style = document.createElement("style");

    style.type = 'text/css';
    head.insertBefore(style, head.firstChild);
    var sheet = style.sheet || style.styleSheet;
    sheet.cssText = rule;

    return sheet.cssText.length !== 0 && !(/unknown/i).test(sheet.cssText) &&
           sheet.cssText.replace(/\r+|\n+/g, '').indexOf(rule.split(' ')[0]) === 0;
}

function browser_supports_font_face()
{
    var impl = document.implementation || { hasFeature: function() {return false;}};

    var rule = '@font-face { font-family: "font"; src: "font.ttf"; }';
    if(impl.hasFeature('CSS2', ''))
    {
        return browser_supports_font_face_css2(rule);
    }
    else
    {
        return browser_supports_font_face_older(rule);
    }
}

const NOTIFICATION_SUCCESS = "alert-success";
const NOTIFICATION_INFO    = "alert-info";
const NOTIFICATION_WARNING = "alert-warning";
const NOTIFICATION_FAILED  = "alert-danger";

function add_notification(container, message, severity, dismissible)
{
    if(severity == undefined)
    {
        severity = NOTIFICATION_INFO; 
    }
    if(dismissible == undefined)
    {
        dismissible = true;
    }
    var class_str = 'alert '+severity;
    if(dismissible)
    {
        class_str+=' alert-dismissible';
    }
    var alert_div = $('<div/>', {class: class_str, role: 'alert'});
    if(dismissible)
    {
        var button = $('<button/>', {type: 'button', class: 'close', 'data-dismiss': 'alert'});
        $('<span/>', {'aria-hidden': 'true'}).html('&times;').appendTo(button);
        $('<span/>', {class: 'sr-only'}).html('Close').appendTo(button);
        button.appendTo(alert_div);
    }
    var prefix = '';
    switch(severity)
    {
        case NOTIFICATION_INFO:
            prefix = '<strong>Notice:</strong> ';
            break;
        case NOTIFICATION_WARNING:
            prefix = '<strong>Warning!</strong> ';
            break;
        case NOTIFICATION_FAILED:
            prefix = '<strong>Warning!</strong> ';
            break;
    }
    alert_div.append(prefix+message);
    container.prepend(alert_div);
}

function browser_supported()
{
    if(!$.support.ajax)
    {
        window.location = '/badbrowser.php?no-ajax';
    }
}

$(broswer_supported);
