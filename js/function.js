/**
 * Created by admin on 2017/6/24.
 */

//url参数处理
function GetRequest() {
    var url = location.search; //获取url中"?"符后的字串
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for(var i = 0; i < strs.length; i ++) {
            theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
        }
    }
    return theRequest;
}

//时间处理
function time(times) {
    return new Date(parseInt(times) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
}

$('.date-range').daterangepicker({
    locale:{
        format: 'YYYY-MM-DD',
        isAutoVal:false,
    }
});
$("#filter_type").change(function() {
    $("#filter_text").attr("name", $(this).val());
});
