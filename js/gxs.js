/**
 * Created by gxs on 2017/6/22.
 */

/**
 * 登录
 */
function login() {
    $(function() {
        $(".btn_login").click(function(){
            var username = $(".username").val();
            var password = $(".userPwd").val();
            var btn = $(this);
            if(username.replace(/(^\s*)|(\s*$)/g, "")==""||password==""){
                $('body').dialog({type:'danger',discription:"您输入的信息不完整！"});
                return false;
            }else{
                //接口
                var $form = $('#login-form');
                var url = '/login.html';
                var data = $form.serializeArray();

                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.href = '/index.html';
                    } else {
                        $('body').dialog({type:'danger',discription:result.errmsg ? result.errmsg : '请求失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
                });
                return false;
            }
        });
    });
}

/**
 * 用户管理
 */
function user(){
    $(function() {
        //新增
        $(".btn_add").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"block"});
            $("#form-group-status").addClass('hidden');
            //清空
            $('#id').val('');
            $('#mobile').val('');
            $('#truename').val('');
            $('#idno').val('');
            $('#balance').val('');
            $('#integral').val('');
            $('#status').val(0);
            $("input[type=radio][name=verified][value='0']").prop("checked",'checked');
            oCHCK();
        });

        //修改
        $(".tr_msg").on('click', ".btn-user-edit", function() {
            $(".container_tan,.tan_bgs").css({"display":"none"});
            $("#form-group-status").removeClass('hidden');
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
                        $('#status').val(result.data.status);
                        if (parseInt(result.data.verified) === 1) {
                            $("input[type=radio][name=verified][value='1']").prop("checked",'checked');
                        } else {
                            $("input[type=radio][name=verified][value='0']").prop("checked",'checked');
                        }
                        oCHCK();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
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
        var list = function () {
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
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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
        $(".btn-post").click(function(){
            var btn = $(this);
            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#post-form');
                var url = '/user/add.html';
                var data = $form.serializeArray();

                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
                });
                return false;
            }
        });

        //删除
        $(".tr_msg").on('click', ".btn-user-delete", function() {
            var id = $(this).parent().data('uid'); //不为空
            if (id == "") {
                return false;
            };
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
        list();

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
}

/**
 * 资金变动
 */
function balance()
{
    $(function() {
        //新增弹层
        $(".btn_add").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"block"});
            $('#form-group-status').attr('class', 'form-group hidden');
            //清空数据
            $('#id').val('');
            $('#client_id').val('');
            $('#current').val('');
            $('#note').val('');
            $("#status").val(0);
            oCHCK();
        });

        //修改
        $(".tr_msg").on('click', ".btn-balance-edit", function() {
            $(".container_tan,.tan_bgs").css({"display":"none"});
            $('#form-group-status').attr('class', 'form-group');
            var url = "/balance/info.json";
            var id = $(this).parent().data('id');
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
                        $('#client_id').val(result.data.client_id);
                        $('#current').val(result.data.current);
                        $('#note').val(result.data.note);
                        $("#status").val(result.data.status);
                        oCHCK();
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
        })

        //新增 && 修改 提交表单
        $(".btn-balance-post").click(function(){
            var id = $(".id").val(); //不存在id新增 否则修改
            var btn = $(this);
            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#balance-post-form');
                var url = '/balance/add.html';
                var data = $form.serializeArray();

                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
                });
                return false;
            }
        });

        //查看 && 分页 && 搜索
        var list = function () {
            var form = $('#balance_search_form');
            var url = "/balance/list.json";
            var formData = form.serializeArray();
            var statusList = ['正常', '删除', '禁用'];
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
                        var str = '';
                        if (data != null) {
                            for (var j = 0; j < data.length; j++) {
                                str += "<tr><td>" + data[j].id + "</td>"+
//                                    "<td>" + data[j].client_id + "</td>" +
                                    "<td>" + data[j].client_name + "</td>" +
                                    "<td>" + data[j].payment_id + "</td>" +
                                    "<td>" + data[j].note + "</td>" +
                                    "<td>" + data[j].current + "</td>" +
                                    "<td>" + statusList[data[j].status] + "</td>" +
                                    "<td>" + time(data[j].ctime) + "</td>" +
                                    "<td data-id=" + data[j].id + "><a  class='btn btn-xs  btn-primary btn-balance-edit'><i class='fa fa-fw fa-edit'></i>编辑</a><a  class='btn btn-xs  btn-primary btn-user-delete disabled-"+data[j].status+"'></i>删除</a></td></tr>"
                            }
                        }
                        $(".tr_msg").html(str);

                        //分页
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:result.current,
                            backFn:function(p){
                                next(p);
                            }
                        });
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
        };

        //删除
        $(".tr_msg").on('click', ".btn-user-delete", function() {
            var id = $(this).parent().data('id'); //不为空
            if (id == "") {
                return false;
            }
            $('body').dialog({type:'danger',discription:'是否删除该条目？'});
            $(".btn_Yes").click(function(){
                var url = "/balance/del.html";
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
            });
        });

        //方法调用
        //url是否有id
        var Request = new Object();
        Request = GetRequest();
        var params_id = Request['client_id'];
        if (params_id) {
            $('#filter_type').val('client_id');
            $('#filter_text').attr('name', 'client_id');
            $('#filter_text').val(params_id);
            list()
        } else {
            list();
        }

        //搜索
        $(".btn-balance-search").click(function () {
            list();
        });

        //排序
        $(".tr_order").on('click', ".fa-sort-amount-asc", function() {
            $(this).attr('class', 'fa fa-fw fa-sort-amount-desc');
            var order = $(this).data('order')+'  asc';
            $('#order').val(order);
            list();
        });

        $(".tr_order").on('click', ".fa-sort-amount-desc", function() {
            $(this).attr('class', 'fa fa-fw fa-sort-amount-asc');
            var order = $(this).data('order')+'  desc';
            $('#order').val(order);
            list();
        });

        //分页获取某一页信息
        function next(p){
            $('#page').val(p);
            list();
        };

        //格式化时间
        function time(times) {
            return new Date(parseInt(times) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
        }
    });
}

/**
 * 积分记录
 */
function integral()
{
    $(function() {
        //新增弹层
        $(".btn_add").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"block"});
            $('#form-group-status').attr('class', 'form-group hidden');
            //清空数据
            $('#id').val('');
            $('#client_id').val('');
            $('#current').val('');
            $('#changed').val('');
            $('#note').val('');
            $('#status').val(0);
            oCHCK();
        });

        //修改
        $(".tr_msg").on('click', ".btn-balance-edit", function() {
            $(".container_tan,.tan_bgs").css({"display":"none"});
            $('#form-group-status').attr('class', 'form-group');
            var url = "/integral/info.json";
            var id = $(this).parent().data('id');
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
                        $('#client_id').val(result.data.client_id);
                        $('#current').val(result.data.current);
                        $('#changed').val(result.data.changed);
                        $('#note').val(result.data.note);
                        $('#status').val(result.data.status);
                        oCHCK();
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
        })

        //新增 && 修改 提交表单
        $(".btn-integral-post").click(function(){
            var id = $(".id").val(); //不存在id新增 否则修改
            var btn = $(this);
            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#integral-post-form');
                var url = '/integral/add.html';
                var data = $form.serializeArray();

                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
                });
                return false;
            }
        });

        //查看 && 分页 && 搜索
        var list = function () {
            var form = $('#integral_search_form');
            var url = "/integral/list.json";
            var formData = form.serializeArray();
            var statusList = ['正常', '删除', '禁用'];
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
                        var str = '';
                        if (data != null) {
                            for (var j = 0; j < data.length; j++) {
                                str += "<tr><td>" + data[j].id + "</td>" +
//                                    "<td>" + data[j].client_id + "</td>" +
                                    "<td>" + data[j].client_name + "</td>" +
                                    "<td>" + data[j].current + "</td>" +
                                    "<td>" + data[j].note + "</td>" +
                                    "<td>" + statusList[data[j].status] + "</td>" +
                                    "<td>" + time(data[j].ctime) + "</td>" +
                                    "<td data-id=" + data[j].id + "><a  class='btn btn-xs  btn-primary btn-balance-edit'><i class='fa fa-fw fa-edit'></i>编辑</a><a  class='btn btn-xs  btn-primary btn-user-delete disabled-"+data[j].status+"'></i>删除</a></td></tr>"
                            }
                        }
                        $(".tr_msg").html(str);

                        //分页
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:result.current,
                            backFn:function(p){
                                next(p);
                            }
                        });
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
        };

        //删除
        $(".tr_msg").on('click', ".btn-user-delete", function() {
            var id = $(this).parent().data('id'); //不为空
            if (id == "") {
                return false;
            }
            $('body').dialog({type:'danger',discription:'是否删除该条目？'});
            $(".btn_Yes").click(function(){
                var url = "/integral/del.html";
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
            });
        });

        //方法调用
        //url是否有id
        var Request = new Object();
        Request = GetRequest();
        var params_id = Request['client_id'];
        if (params_id) {
            $('#filter_type').val('client_id');
            $('#filter_text').attr('name', 'client_id');
            $('#filter_text').val(params_id);
            list()
        } else {
            list();
        }

        //搜索
        $(".btn-integral-search").click(function () {
            list();
        });

        //排序
        $(".tr_order").on('click', ".fa-sort-amount-asc", function() {
            $(this).attr('class', 'fa fa-fw fa-sort-amount-desc');
            var order = $(this).data('order')+' asc';
            $('#order').val(order);
            list();
        });

        $(".tr_order").on('click', ".fa-sort-amount-desc", function() {
            $(this).attr('class', 'fa fa-fw fa-sort-amount-asc');
            var order = $(this).data('order')+' desc';
            $('#order').val(order);
            list();
        });

        //分页获取某一页信息
        function next(p){
            $('#page').val(p);
            list();
        };

        //时间格式化
        function time(times) {
            return new Date(parseInt(times) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
        }
    });

}

/**
 * 角色管理
 */
function role()
{
    $(function() {
        //新增
        $(".btn_add").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"block"});
            $('#form-group-status').attr('class', 'form-group hidden');
            //清空
            $('#id').val('');
            $("#role-name").val('');
            $("#grade").val('');
            $("#brief").val('');
            $("#status").val(0);
            oCHCK();
        });

        //修改
        $(".tr_msg").on('click', ".btn-user-edit", function() {
            $(".container_tan,.tan_bgs").css({"display":"none"});
            $('#form-group-status').attr('class', 'form-group');
            //填充id 和数据
            var url = "/role/row.json";
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
                        $("#role-name").val(result.data.name);
                        $("#grade").val(result.data.grade);
                        $("#brief").val(result.data.brief);
                        $("#status").val(result.data.status);
                        oCHCK();
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
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#role_search_form');
            var url = "/role/list.json";
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
                                str += "<tr class='table-"+data[j].status+"'><td>"+data[j].id+"</td>"+
                                    "<td>"+data[j].name+"</td>"+
                                    "<td>"+data[j].grade+"</td>"+
                                    "<td>"+data[j].brief+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"+
                                    "<td data-uid="+data[j].id+"><a  class='btn btn-xs  btn-primary btn-user-edit'><i class='fa fa-fw fa-edit'></i>编辑</a><a  class='btn btn-xs  btn-primary btn-user-delete disabled-"+data[j].status+"'><i class='fa fa-fw fa-close'></i>删除</a>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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
            var btn = $(this);
            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#user-post-form');
                var url = '/role/add.html';
                var data = $form.serializeArray();
                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
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
                var url = "/role/del.html";
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
            });
        });

        //方法调用
        list();

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
    });
}

/**
 * 权限管理
 */
function power()
{
    $(function() {
        //新增
        $(".btn_add").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"block"});
            //清空
            $('#id').val('');
            $('#role_id').val('');
            $("#power-name").val('');
            $("#status").val(0);
            $("#router").val('');
            oCHCK();
        });

        //修改
        $(".tr_msg").on('click', ".btn-user-edit", function() {
            $(".container_tan,.tan_bgs").css({"display":"none"});
            //填充id 和数据
            var url = "/power/row.json";
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
                        $('#role_id').val(result.data.role_id);
                        $("#power-name").val(result.data.name);
                        $("#status").val(result.data.status);
                        $("#router").val(result.data.router);
                        oCHCK();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
            $(".container_tan,.tan_bgs").css({"display":"block"});
        });

        $(".btn_exit").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"none"});
        });

        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#power_search_form');
            var url = "/power/list.json";
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
                                    "<td>"+data[j].role_id+"</td>"+
                                    "<td>"+data[j].name+"</td>"+
                                    "<td>"+data[j].router+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"+
                                    "<td data-uid="+data[j].id+"><a  class='btn btn-xs  btn-primary btn-user-edit'><i class='fa fa-fw fa-edit'></i>编辑</a><a  class='btn btn-xs  btn-primary btn-user-delete disabled-"+data[j].status+"'><i class='fa fa-fw fa-close'></i>删除</a>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
                                next(current);
                            }
                        });

                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
        };

        //新增 && 修改
        $(".btn-user-post").click(function(){
            var id = $(".id").val(); //不存在id新增 否则修改
            var btn = $(this);
            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#user-post-form');
                var url = '/power/add.html';
                var data = $form.serializeArray();
                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
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
                var url = "/power/del.html";
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
            });
        });

        //方法调用
        list();

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
    });
}

/**
 * 系统日志
 */
function syslog()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
                var statusList = ['正常', '删除', '禁用'];
                var form = $('#user_search_form');
                var url = "/syslog/list.json";
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
                                        "<td>"+data[j].client_id+"</td>"+
                                        "<td>"+data[j].brief+"</td>"+
                                        "<td>"+statusList[data[j].status]+"</td>"+
                                        "<td>"+time(data[j].ctime)+"</td>"
                                };
                            }
                            $(".tr_msg").html(str);

                            //分页
                            var current = result.current;
                            $(".tcdPageCode").createPage({
                                pageCount:result.total,
                                current:current,
                                backFn:function(current){
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

        //方法调用
        list();

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
    });
}

/**
 * 支付日志
 */
function paylog()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
                var statusList = ['正常', '删除', '禁用'];
                var form = $('#user_search_form');
                var url = "/paylog/list.json";
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
//                                        "<td>"+data[j].client_id+"</td>"+
                                        "<td>"+data[j].client_name+"</td>"+
                                        "<td>"+data[j].amount+"</td>"+
                                        "<td>"+data[j].brief+"</td>"+
                                        "<td>"+statusList[data[j].status]+"</td>"+
                                        "<td>"+time(data[j].ctime)+"</td>"
                                };
                            }
                            $(".tr_msg").html(str);

                            //分页
                            var current = result.current;
                            $(".tcdPageCode").createPage({
                                pageCount:result.total,
                                current:current,
                                backFn:function(current){
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

        //方法调用
        list();

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
    });
}

/**
 * 反馈日志
 */
function feedback()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
                var statusList = ['正常', '删除', '禁用'];
                var form = $('#user_search_form');
                var url = "/feedback/list.json";
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
//                                        "<td>"+data[j].client_id+"</td>"+
                                        "<td>"+data[j].client_name+"</td>"+
                                        "<td>"+data[j].device_id+"</td>"+
                                        "<td>"+data[j].cases+"</td>"+
                                        "<td>"+data[j].content+"</td>"+
                                        "<td>"+statusList[data[j].status]+"</td>"+
                                        "<td>"+time(data[j].ctime)+"</td>"
                                };
                            }
                            $(".tr_msg").html(str);

                            //分页
                            var current = result.current;
                            $(".tcdPageCode").createPage({
                                pageCount:result.total,
                                current:current,
                                backFn:function(current){
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

        //方法调用
        list();

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
    });
}

/**
 * 押金财务记录
 */
function deposit()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#user_search_form');
            var url = "/deposit/list.json";
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
                                    "<td>"+data[j].client_id+"</td>"+
                                    "<td>"+data[j].amount+"</td>"+
                                    "<td>"+data[j].payment_id+"</td>"+
                                    "<td>"+data[j].paidtype+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

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
    });
}

/**
 * 代理商财务记录
 * 购买设备记录
 * 用户充值记录
 */
function finance()
{
    var re_id;
    $(function() {
        //获取登录代理商信息
        $.get('/account.json', {}, function(result){
            if (result.status) {
                //搜索表单
                $('#agent_id').val(result.data.id);
                //方法调用
                list();
            } else {
                $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                return false;
            }
        }).error(function(xhr,errorText,errorType){
            $('body').dialog({type:'danger',discription:'请求失败!'});
            return false;
        });

        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#user_search_form');
            var url = "/finance/list.json";
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
//                                    "<td>"+data[j].client_id+"</td>"+
                                    "<td>"+data[j].client_name+"</td>"+
                                    "<td>"+data[j].payment_id+"</td>"+
                                    "<td>"+data[j].note+"</td>"+
                                    "<td>"+data[j].current+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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
    });
}

/**
 * 文档信息
 */
function article()
{
    $(function() {
        $(".btn_exit").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"none"});
        });

        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/article/list.json";
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
                                    "<td>"+data[j].class_id+"</td>"+
                                    "<td>"+data[j].classname+"</td>"+
                                    "<td>"+data[j].title+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"+
                                    "<td data-uid="+data[j].id+"><a  class='btn btn-xs  btn-primary btn-user-edit' href='/article/add.html?id="+data[j].id+"'><i class='fa fa-fw fa-edit'></i>编辑</a><a  class='btn btn-xs  btn-primary btn-user-delete disabled-"+data[j].status+"'><i class='fa fa-fw fa-close'></i>删除</a>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
                                next(current);
                            }
                        });

                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
        };

        //删除
        $(".tr_msg").on('click', ".btn-user-delete", function() {
            var id = $(this).parent().data('uid'); //不为空
            if (id == "") {
                return false;
            }
            $('body').dialog({type:'danger',discription:'是否删除该条目？'});
            $(".btn_Yes").click(function(){
                var url = "/article/del.html";
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
            });
        });

        //方法调用
        list();

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
    });
}

function articleHtml()
{
    $(function() {
        //获取分类信息
        $.ajax({
            type: 'GET',
            url: '/article/catedrop.json',
            cache: false,
            dataType: 'json',
            success:function(result){
                if (result.status) {
                    //放入数据
                    $('#class_id').html(result.data);
                } else {
                    $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                }
            },
            error: function(error){
                $('body').dialog({type:'danger',discription:'请求失败!'});
            }
        });

        //方法调用
        //url是否有id
        var Request = new Object();
        Request = GetRequest();
        var params_id = Request['id'];
        //不存在params_id 新增  存在params_id 修改
        if (params_id) {
            //修改
            //获取单条信息
            $.ajax({
                type: 'GET',
                url: '/article/row.json',
                data: {
                    id : params_id
                },
                cache: false,
                dataType: 'json',
                success:function(result){
                    if (result.status) {
                        //放入数据
                        $('#id').val(params_id);
                        $('#title').val(result.data.title);
                        $('#class_id').val(result.data.class_id);
                        $('#content').val(result.data.content);
                        $('#status').val(result.data.status);
                        oCHCK();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
        } else {
            //新增 隐藏状态
            $('#form-group-status').addClass('hidden');
        }

        //新增 && 修改 提交
        $(".btn-post").click(function(){
            var id = $(".id").val(); //不存在id新增 否则修改
            var btn = $(this);
            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#post-form');
                var url = '/article/add.html';
                var data = $form.serializeArray();
                data.push({'name':'content','value': content});
                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        //跳转到列表页
                        window.location.href = '/article.html';
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
                });
                return false;
            }
        });
    })
}

/**
 * 文档分类
 */
function category()
{
    $(function() {
        //获取分类信息
        $.ajax({
            type: 'GET',
            url: '/article/catedrop.json',
            cache: false,
            dataType: 'json',
            success:function(result){
                if (result.status) {
                    //放入数据
                    $('#parent_id').html("<option value='0'>顶级分类</option>"+result.data);
                } else {
                    $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                }
            },
            error: function(error){
                $('body').dialog({type:'danger',discription:'请求失败!'});
            }
        });

        //新增
        $(".btn_add").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"block"});
            $("#form-group-status").addClass('hidden');
            //清空
            $('#id').val('');
            $('#parent_id').val(0);
            $("#name").val('');
            $("#status").val(0);
            oCHCK();
        });

        //修改
        $(".tr_msg").on('click', ".btn-user-edit", function() {
            $(".container_tan,.tan_bgs").css({"display":"none"});
            $("#form-group-status").removeClass('hidden');
            //填充id 和数据
            var url = "/category/row.json";
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
                        $('#parent_id').val(result.data.parent_id);
                        $("#name").val(result.data.name);
                        $("#status").val(result.data.status);
                        oCHCK();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
            $(".container_tan,.tan_bgs").css({"display":"block"});
        });

        $(".btn_exit").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"none"});
        });

        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/category/list.json";
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
                                    "<td>"+data[j].parent_id+"</td>"+
                                    "<td>"+data[j].name+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"+
                                    "<td data-uid="+data[j].id+"><a  class='btn btn-xs  btn-primary btn-user-edit'><i class='fa fa-fw fa-edit'></i>编辑</a><a  class='btn btn-xs  btn-primary btn-user-delete disabled-"+data[j].status+"'><i class='fa fa-fw fa-close'></i>删除</a>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
                                next(current);
                            }
                        });

                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
        };

        //新增 && 修改
        $(".btn-post").click(function(){
            var id = $(".id").val(); //不存在id新增 否则修改
            var btn = $(this);
            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#post-form');
                var url = '/category/add.html';
                var data = $form.serializeArray();
                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
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
                var url = "/category/del.html";
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
            });
        });

        //方法调用
        list();

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
    });
}

/**
 * 代理管理
 */
function agent()
{
    $(function() {
        /**
         * 所有代理商
         */
        $.ajax({
            type: 'GET',
            url: '/agent/catedrop.json',
            cache: false,
            dataType: 'json',
            success:function(result){
                if (result.status) {
                    //放入数据
                    $('#parent_id').html("<option value='0' selected='selected'>顶级加盟商</option>"+result.data);
                } else {
                    $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                }
            },
            error: function(error){
                $('body').dialog({type:'danger',discription:'请求失败!'});
            }
        });

        //新增
        $(".btn_add").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"block"});
            $("#form-group-status").addClass('hidden');
            //清空
            $('#id').val('');
            $('#account').val('');
            $("#name").val('');
            $("#mobile").val('');
            $("#email").val('');
            $("#parent_id").val(0);
            $("#balance").val('');
            $("#status").val(0);
            $("input[type=radio][name=verified][value='0']").prop("checked",'checked');
            oCHCK();
        });

        //修改
        $(".tr_msg").on('click', ".btn-user-edit", function() {
            $(".container_tan,.tan_bgs").css({"display":"none"});
            $("#form-group-status").removeClass('hidden');
            //填充id 和数据
            var url = "/agent/row.json";
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
                        $('#account').val(result.data.account);
                        $("#name").val(result.data.name);
                        $("#mobile").val(result.data.mobile);
                        $("#email").val(result.data.email);
                        $("#parent_id").val(result.data.parent_id);
                        $("#balance").val(result.data.balance);
                        $("#status").val(result.data.status);
                        if (parseInt(result.data.verified) === 1) {
                            $("input[type=radio][name=verified][value='1']").prop("checked",'checked');
                        } else {
                            $("input[type=radio][name=verified][value='0']").prop("checked",'checked');
                        }
                        oCHCK();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
            $(".container_tan,.tan_bgs").css({"display":"block"});
        });

        $(".btn_exit").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"none"});
        });

        //查看 && 分页 && 搜索
        var list = function () {
            var verifiedList = ['否', '是'];
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/agent/list.json";
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
                                    "<td>"+data[j].account+"</td>"+
                                    "<td>"+data[j].name+"</td>"+
                                    "<td>"+data[j].mobile+"</td>"+
                                    "<td>"+data[j].email+"</td>"+
                                    "<td>"+data[j].parent_id+"</td>"+
                                    "<td>"+verifiedList[data[j].verified]+"</td>"+
                                    "<td>"+data[j].balance+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"+
                                    "<td data-uid="+data[j].id+"><a  class='btn btn-xs  btn-primary btn-user-edit'><i class='fa fa-fw fa-edit'></i>编辑</a><a  class='btn btn-xs  btn-primary btn-user-delete disabled-"+data[j].status+"'><i class='fa fa-fw fa-close'></i>删除</a>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
                                next(current);
                            }
                        });

                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
        };

        //新增 && 修改
        $(".btn-post").click(function(){
            var id = $(".id").val(); //不存在id新增 否则修改
            var btn = $(this);

            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#post-form');
                var url = '/agent/add.html';
                var data = $form.serializeArray();
                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
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
                var url = "/agent/del.html";
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
            });
        });

        //方法调用
        list();

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
    });
}

/**
 * 日收益
 */
function daily_earnings()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/revenue/day.json";
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
                                str += "<tr><td>"+data[j].profit+"</td>"+
                                    "<td>"+data[j].day+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

        //搜索
        $(".btn-search").click(function () {
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
    });
}

/**
 *代理月收益
 */
function monthly_income()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/revenue/month.json";
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
                                str += "<tr><td>"+data[j].profit+"</td>"+
                                    "<td>"+data[j].month+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

        //搜索
        $(".btn-search").click(function () {
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
    });
}

/**
 * 设备批次管理
 */
function entitysn()
{
    $(function() {
        //新增
        $(".btn_add").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"block"});
            $("#form-group-status").addClass('hidden');
            //清空
            $('#id').val('');
            $('#name').val('');
            $("#price").val('');
            $("#start").val('');
            $("#total").val('');
            $("#status").val(0);
            oCHCK();
        });

        //修改
        $(".tr_msg").on('click', ".btn-user-edit", function() {
            $(".container_tan,.tan_bgs").css({"display":"none"});
            $("#form-group-status").removeClass('hidden');
            //填充id 和数据
            var url = "/entitysn/row.json";
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
                        $('#name').val(result.data.name);
                        $("#price").val(result.data.price);
                        $("#start").val(result.data.start);
                        $("#total").val(result.data.total);
                        $("#status").val(result.data.status);
                        oCHCK();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
            $(".container_tan,.tan_bgs").css({"display":"block"});
        });

        $(".btn_exit").click(function(){
            $(".container_tan,.tan_bgs").css({"display":"none"});
        });

        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var generateList = ['否', '是'];
            var form = $('#search_form');
            var url = "/entitysn/list.json";
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
                                    "<td>"+data[j].name+"</td>"+
                                    "<td>"+data[j].price+"</td>"+
                                    "<td>"+data[j].batch+"</td>"+
                                    "<td>"+data[j].start+"</td>"+
                                    "<td>"+data[j].total+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+generateList[data[j].generate]+"</td>"+
                                    "<td data-uid="+data[j].id+"><a  class='btn btn-xs  btn-primary btn-user-edit'><i class='fa fa-fw fa-edit'></i>编辑</a>" +
                                    "<a class='btn btn-xs  btn-primary btn-delete disabled-"+data[j].status+"'><i class='fa fa-fw fa-close'></i>删除</a>" +
                                    "<a class='btn btn-xs btn-info btn-create' data-generate='"+data[j].generate+"'><i class='fa fa-fw'></i>生成实体</a>" +
                                    "<a class='btn btn-xs  btn-primary' href='/entsn/range.html?id="+data[j].id+"'><i class='fa fa-fw fa-edit'></i>运营范围</a>" +
                                    "</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
                                next(current);
                            }
                        });

                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '获取失败!'});
                    }
                },
                error: function(error){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                }
            });
        };

        //新增 && 修改
        $(".btn-post").click(function(){
            var id = $(".id").val(); //不存在id新增 否则修改
            var btn = $(this);
            //表单验证通过
            if(oCheckSbumit1()){
                var $form = $('#post-form');
                var url = '/entitysn/add.html';
                var data = $form.serializeArray();
                btn.addClass('disabled');
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '失败!'});
                        btn.removeClass('disabled');
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                    btn.removeClass('disabled');
                });
                return false;
            }
        });

        //删除
        $(".tr_msg").on('click', ".btn-delete", function() {
            var id = $(this).parent().data('uid'); //不为空
            if (id == "") {
                return false;
            }
            $('body').dialog({type:'danger',discription:'是否删除该条目？'});
            $(".btn_Yes").click(function(){
                var url = "/entitysn/del.html";
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
            });
        });

        //生成
        $(".tr_msg").on('click', ".btn-create", function() {
            var id = $(this).parent().data('uid'); //不为空
            if (id == "") {
                return false;
            }
            var generate = $(this).data('generate');
            if (generate == 1) {
                $('body').dialog({type:'info',discription:'已生成!'});
                return false;
            }
            $('body').dialog({type:'danger',discription:'是否生成该条目？'});
            $(".btn_Yes").click(function(){
                var url = "/entitysn/generate.html";
                var data = {
                    id : id
                };
                $.post(url, data, function(result){
                    if (result.status) {
                        window.location.reload();
                    } else {
                        $('body').dialog({type:'danger',discription:result.message ? result.message : '生成失败!'});
                    }
                }).error(function(xhr,errorText,errorType){
                    $('body').dialog({type:'danger',discription:'请求失败!'});
                });
            });
        });

        //方法调用
        list();

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
    });
}

/**
 * 设备活动记录
 */
function activity()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/device/activitylist.json";
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
                                    "<td>"+data[j].device_id+"</td>"+
                                    "<td>"+data[j].client_id+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

        //搜索
        $(".btn-search").click(function () {
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
    });
}

/**
 * 设备收益记录
 */
function revenue()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/device/revenuelist.json";
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
                                    "<td>"+data[j].device_id+"</td>"+
                                    "<td>"+data[j].client_id+"</td>"+
                                    "<td>"+time(data[j].start)+"</td>"+
                                    "<td>"+time(data[j].over)+"</td>"+
                                    "<td>"+data[j].profit+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

        //搜索
        $(".btn-search").click(function () {
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
    });
}

/**
 * 设备状态跟踪
 */
function tracing()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/device/tracinglist.json";
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
                                    "<td>"+data[j].device_id+"</td>"+
                                    "<td>"+data[j].lat+"</td>"+
                                    "<td>"+data[j].lng+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

        //搜索
        $(".btn-search").click(function () {
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
    });
}

/**
 * 设备列表
 */
function device()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/device/list.json";
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
                                    "<td>"+data[j].imei+"</td>"+
                                    "<td>"+data[j].binding+"</td>"+
                                    "<td>"+data[j].cases+"</td>"+
                                    "<td>"+data[j].client_id+"</td>"+
                                    "<td>"+data[j].lat+"</td>"+
                                    "<td>"+data[j].lng+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

        //搜索
        $(".btn-search").click(function () {
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
    });
}

/**
 * 实体列表
 */
function device_entity()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/entity/list.json";
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
                                    "<td>"+data[j].entsn_id+"</td>"+
                                    "<td>"+data[j].sn+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

        //搜索
        $(".btn-search").click(function () {
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
    });
}

/**
 * 系统消息记录
 */
function message()
{
    $(function() {
        //查看 && 分页 && 搜索
        var list = function () {
            var statusList = ['正常', '删除', '禁用'];
            var form = $('#search_form');
            var url = "/message/list.json";
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
                                    "<td>"+data[j].title+"</td>"+
                                    "<td>"+data[j].target_id+"</td>"+
                                    "<td>"+statusList[data[j].status]+"</td>"+
                                    "<td>"+time(data[j].ctime)+"</td>"
                            };
                        }
                        $(".tr_msg").html(str);

                        //分页
                        var current = result.current;
                        $(".tcdPageCode").createPage({
                            pageCount:result.total,
                            current:current,
                            backFn:function(current){
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

        //方法调用
        list();

        //搜索
        $(".btn-search").click(function () {
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
    });
}