/**
 * Created by Pitrrs on 5.11.14.
 */
function changeClass(th, nclass) {
    if (nclass == "debugger_opened") {
        var paras = document.getElementsByClassName('debugger_opened');

        for (var i = 0; i < paras.length; i++) {
            if (paras[i] != th)
                paras[i].classList.remove("debugger_opened");
        }
    }
    if (nclass == "debugger_stack_opened") {
        var paras = th.parentNode.getElementsByClassName('debugger_stack_opened');
        for (var i = 0; i < paras.length; i++) {
            if (paras[i] != th)
                paras[i].classList.remove("debugger_stack_opened");
        }
    }
    th.classList.toggle(nclass);
}
var count = 0;
jQuery(document).ready(function ($) {
    $(document).bind("ajaxSend",function (data) {
    }).bind("ajaxComplete", function (event, xhr, options) {
        var $res = JSON.parse(xhr.responseText);
        $("#debugger_ajax").show();
        count++;
        var d = $("#debugger_ajax");
        var dt = new Date();
        var da = dt.getHours() + ":" + (dt.getMinutes() < 10 ? "0" : "") + dt.getMinutes() + ":" + (dt.getSeconds() < 10 ? "0" : "") + dt.getSeconds() + "." + dt.getMilliseconds();
        d.find("#debugger_ajax_count").html(count);
        d.find("#debugger_ajax_count2").html(count);
        d.find("#debugger_ajax_content>pre").append("<div class='debugger_stack'><div class='debugger_file'><div class='debugger_file_header'><h4 onclick='changeClass(this.parentNode.parentNode.parentNode,\"debugger_stack_opened\")'>" + da + " #" + count + ": " + options.type + " - " + options.url + " (WARNINGS: " + $res.STATISTIC.ERRORS.length + ", MYSQL: " + Object.keys($res.STATISTIC.MYSQL).length + ")" + "</h4></div><div class='debugger_file_content'>" + dump_to_table($res) + "</div></div></div>");

    });
});
function dump_to_table($data, level) {
    if (!level) level = 0;
    var x = 0;
    var $retval = '<table border="1" class="debug_table ' + (level > 2 ? "collapese" : '') + '"><thead><tr><th colspan="2" onclick="changeClass(this.parentNode.parentNode.parentNode,\'collapese\')">' + (typeof $data) + '</th></tr></thead><tbody>';
    if (typeof ($data) == "number") $retval += "<tr><td><strong>Number:</strong> </td><td>" + $data + "</td></tr>";
    else if (typeof ($data) == "string") $retval += "<tr><td><strong>String:</strong> </td><td>'" + $data + "'</td></tr>";
    else if (typeof ($data) == "undefined") $retval += "<tr><td></td><td><strong>UNDEFINED</strong></td></tr>";
    else if (typeof ($data) == null) $retval += "<tr><td></td><td><strong>NULL</strong></td></tr>";
    else if ($data === true) $retval += "<tr><td><strong>Bool: </strong></td><td>TRUE</td></tr>";
    else if ($data === false) $retval += "<tr><td><strong>Bool:</strong></td><td> FALSE</td></tr>";
    else if (typeof ($data) == "array") {

        for (x = 0; x < $data.length; x++) {
            $retval += "<tr><td>";
            $retval += "<strong>" + x + "</strong></td><td>";
            $retval += dump_to_table(data[x], level + 1);
            $retval += "</td></tr>";
        }

    } else if (typeof ($data) == "object") {

        var len = parseInt(new Object($data).length) > 0 ? parseInt(new Object($data).length) : 0;
        for (var key in $data) {
            $retval += "<tr><td>";
            $retval += "<strong>" + key + "</strong></td><td>";
            $retval += dump_to_table($data[key], level + 1);
            $retval += "</td></tr>";
        }

    }
    return $retval + "</tbody></table>";
}