/**
 * Created by gxs on 2017/6/23.
 */
    $(function() {
    /**
     * 用户管理
     */
    var current,create = 0;
    //新增
    $(".btn_add").click(function(){
        $(".container_tan,.tan_bgs").css({"display":"block"});
        //清空
        $('#id').val('');
        $('#mobile').val('');
        $('#truename').val('');
        $('#idno').val('');
        $('#balance').val('');
        $('#integral').val('');
        $("input[type=radio][name=verified][value='0']").prop("checked",'checked');
    });

    //修改
    $(".tr_msg").on('click', ".btn-user-edit", function() {
       $(".container_tan,.tan_bgs").css({"display":"none"});
       //填充id 和数据
       var url = "/user/info.json";
       var id = $(this).parent().data('uid');
       $('#id').val(id);
       //获取单条信息
       $.ajax({
           type: 'GET',
           url: url,
           data: {
               id : id
           },
           cache: false,
           dataType: 'json',
           success:function(result){
               if (result.status) {
                   //放入数据
                   $('#id').val(id);
                   $('#mobile').val(result.data.mobile);
                   $('#truename').val(result.data.truename);
                   $('#idno').val(result.data.idno);
                   $('#balance').val(result.data.balance);
                   $('#integral').val(result.data.integral);
                   if (parseInt(result.data.verified) === 1) {
                       $("input[type=radio][name=verified][value='1']").prop("checked",'checked');
                   } else {
                       $("input[type=radio][name=verified][value='0']").prop("checked",'checked');
                   }
               } else {
                   $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
               }
           },
           error: function(error){
               console.log(error);
               $('body').dialog({type:'danger',discription:'请求失败!'});
           }
       });
       $(".container_tan,.tan_bgs").css({"display":"block"});
   });

    $(".btn_exit").click(function(){
        $(".container_tan,.tan_bgs").css({"display":"none"});
        console.log("取消录入")
    });

    //查看 && 分页 && 搜索
    var list = function (s) {
        var form = $('#user_search_form');
        var url = "/user/list.json";
        var formData = form.serializeArray();
        $.ajax({
            type: 'GET',
            url: url,
            data: formData,
            cache: false,
            dataType: 'json',
            success:function(result){
                if (result.status) {
                    //拼接数据
                    var data = result.data;
                    var str = "";
                    if (data != null) {
                        for(var j =0; j<data.length;j++){
                            str += "<tr><td>"+data[j].id+"</td>"+
                                "<td>"+data[j].truename+"</td>"+
                                "<td>"+data[j].mobile+"</td>"+
                                "<td>"+data[j].idno+"</td>"+
                                "<td>"+data[j].balance+"</td>"+
                                "<td>"+data[j].integral+"</td>"+
                                "<td>"+verified(data[j].verified)+"</td>"+
                                "<td>"+time(data[j].ctime)+"</td>"+
                                "<td>"+status(data[j].status)+"</td>"+
                                "<td data-uid="+data[j].id+"><a  class='btn btn-xs  btn-primary btn-user-edit'><i class='fa fa-fw fa-edit'></i>编辑</a><a  class='btn btn-xs  btn-primary btn-user-delete disabled-"+data[j].status+"'><i class='fa fa-fw fa-close'></i>删除</a>" +
                                "<a  class='btn btn-xs  btn-primary' href='/balance.html?client_id="+data[j].agent_id+"'><i class='fa fa-fw fa-dollar'></i>资金变动</a>" +
                                "<a  class='btn btn-xs  btn-primary' href='/integral.html?client_id="+data[j].agent_id+"'><i class='fa fa-fw fa-cc-paypal'></i>  积分记录</a></td></tr>"
                        };
                    }
                    $(".tr_msg").html(str);

                    //分页
                    current = result.current;
                    
                    $(".tcdPageCode").createPage({
                        pageCount:result.total,
                        current:current,
                        backFn:function(current){
                            $(".movieList").empty();
                            next(current);
                        }
                    });

                } else {
                    $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                }
            },
            error: function(error){
                console.log(error);
                $('body').dialog({type:'danger',discription:'请求失败!'});
            }
        });
    };

    //新增 && 修改
    $(".btn-user-post").click(function(){
        var id = $(".id").val(); //不存在id新增 否则修改
        var mobile = $("#mobile").val();
        var truename = $("#truename").val();
        var idno = $("#idno").val();
        if(mobile.replace(/(^\s*)|(\s*$)/g, "")=="" || truename.replace(/(^\s*)|(\s*$)/g, "")=="" || idno == ""){
            $('body').dialog({type:'danger',discription:"您输入的信息不完整！"});
            return false;
        }else{
            var $form = $('#user-post-form');
                var url = '/user/add.html';
                var data = $form.serializeArray();

                $form.find('.btn-user-post').prop('disabled', true);
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                    }
                    $form.find('.btn-user-post').prop('disabled', false);
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                });
                return false;
        }
    });

    //删除
    $(".tr_msg").on('click', ".btn-user-delete", function() {
        var id = $(this).parent().data('uid'); //不为空
        if (id == "") {
            return false;
        }
        $('body').dialog({type:'danger',discription:'是否删除该条目？'});
        $(".btn_Yes").click(function(){
            var url = "/user/del.html";
            var data = {
                id : id
            };
            $.post(url, data, function(result){
                if (result.status) {
                    window.location.reload();
                } else {
                    $('body').dialog({type:'danger',discription:result.message ? result.message : '删除失败!'});
                }
            }).error(function(xhr,errorText,errorType){
                $('body').dialog({type:'danger',discription:'请求失败!'});
            });
        })
    });

    //方法调用
    list(1);

    //搜索
    $(".btn-user-search").click(function () {
        list();
    });

    //排序
    $(".tr_order").on('click', ".fa-sort-amount-asc", function() {
        $(this).attr('class', 'fa fa-fw fa-sort-amount-desc');
        var order = $(this).data('order')+ ' asc';
        $('#order').val(order);
        list();
    });

    $(".tr_order").on('click', ".fa-sort-amount-desc", function() {
        $(this).attr('class', 'fa fa-fw fa-sort-amount-asc');
        var order = $(this).data('order')+ ' desc';
        $('#order').val(order);
        list();
    });

    //分页获取某一页信息
    function next(p){
        $('#page').val(p);
        list();
    };

    function time(times) {  
        return new Date(parseInt(times) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');  
   }
   function status(s){
       if(s==0){
           return "正常";
       }else if(s==1){
           return "删除";
       }else if(s==2){
           return "禁用";
       }else{
           return "未知";
       }
   };
   function verified(v){
       if(v==0){
           return "否"
       }else if (v==1){
           return "是"
       }else{
           return "未知"
       }
   }
});
