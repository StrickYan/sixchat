var mobile_speed = 200; //移动端动画速度
$(function() {
    FastClick.attach(document.body); //去除移动端click延迟300ms插件fastclick初始化
    refresh(); //初始化：刷新评论 给朋友圈元素绑定事件
    initCommentEvent(); //初始化点赞和评论相关事件
    //双击或长按顶部中间栏刷新
    if (isPC() == 0) { //移动端
        $("#current_location").longPress(function() {
            self.location.href = "";
        });
    } else {
        $(document).on("dblclick", "#current_location", function() {
            self.location.href = "";
        });
    };
    // 替换文本内容
    $(".info-flow-right-text").each(function() {
        var str = $(this).text();
        $(this).html(replace_str(str));
    });
    $("img.lazy").lazyload({
        effect: "fadeIn",
        threshold: mobile_speed
    }); //图片延迟加载
    $("#return").bind("click", function() {
        location.href = "../../index.html";
    }); //点击左上角导航返回
});
// jquery $.ajax() 异步加载每条朋友圈下面的所有赞
function getLikesForAjax(moment_id, moment_user_name) {
    $.ajax({
        type: "POST",
        async: false,
        data: {
            "id": moment_id,
            "moment_user_name": moment_user_name
        },
        dataType: "json",
        url: "../../getLikes",
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert("加载错误，错误原因：\n"+errorThrown);
        },
        success: function(data) {
            var html = "";
            var i = 0;
            if (data.length) {
                html += "<img class='like-img' src='../../../../Public/Home/img/default/like.png'/>";
            }
            for (i = 0; i < data.length - 1; i++) {
                html += "<span class='like-user-name'>" + data[i].reply_name + "</span>"; //点赞人名字
                html += "<span>,</span>";
            }
            if (i == data.length - 1) {
                html += "<span class='like-user-name'>" + data[i].reply_name + "</span>"; //点赞人名字
            }
            $("#" + moment_id).children(".info-flow-right-like").empty();
            $("#" + moment_id).children(".info-flow-right-like").append(html);
        }
    });
}
// jquery $.ajax() 异步加载每条朋友圈下面的所有评论
function getCommentsForAjax(moment_id, moment_user_name) {
    $.ajax({
        type: "POST",
        async: false,
        data: {
            "id": moment_id,
            "moment_user_name": moment_user_name
        },
        dataType: "json",
        url: "../../getComments",
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert("加载错误，错误原因：\n"+errorThrown);
        },
        success: function(data) {
            var html = "";
            for (var i = 0; i < data.length; i++) {
                html += "<div class='one-comment' id=" + data[i].comment_id + " ontouchstart='return false'>";
                html += "<span class='comment-user-name'>" + data[i].reply_name + "</span>"; //回复人名字
                if (data[i].reply_name != data[i].replyed_name) {
                    html += "<span> @ </span>";
                    html += "<span class='comment-user-name'>" + data[i].replyed_name + "</span>"; //被回复人名
                }
                html += "<span>：</span>"
                html += "<span>" + data[i].comment + "</span>"; //评论
                html += "</div>";
            }
            $("#" + moment_id).children(".info-flow-right-comment").children().remove();
            $("#" + moment_id).children(".info-flow-right-comment").append(html);
        }
    });
}
// 点赞
function addLike(moment_id, moment_user_name) {
    $.ajax({
        type: "POST",
        async: false,
        data: {
            "moment_id": moment_id,
            "moment_user_name": moment_user_name
        },
        dataType: "json",
        url: "../../addLike",
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert("加载错误，错误原因：\n"+errorThrown);
        },
        success: function(data) { //当addLike后执行reFresh(),重新加载所有赞,所以下面单条添加可以省略
            refresh();
        }
    });
}
// 评论
function addComment() {
    $.ajax({
        type: "POST",
        async: false,
        data: {
            "moment_id": $(".comment-box:focus").parent().parent().attr("id"),
            "replyed_name": $(".comment-box:focus").attr("id"),
            "comment_val": $(".comment-box:focus").val()
        },
        dataType: "json",
        url: "../../addComment",
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert("加载错误，错误原因：\n"+errorThrown);
        },
        success: function(data) { //当addComment后执行reFresh(),重新加载所有评论,所以下面单条添加可以省略
            $(".comment-box:focus").val("");
            $(".comment-box:focus").parent().hide();
        }
    });
}
// 删除moment
function deleteMoment(obj) {
    var data = confirm("Confirm deletion?");
    if (data) {
        $.ajax({
            type: "POST",
            async: false,
            data: {
                "moment_id": obj.attr("id")
            },
            dataType: "json",
            url: "../../deleteMoment",
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert("加载错误，错误原因：\n"+errorThrown);
            },
            success: function(data) {
                obj.parent().slideUp(500, function() {
                    obj.parent().remove();
                })
            }
        });
    }
}
// 删除评论函数
function deleteComment(obj) {
    if (obj.children(".comment-user-name").first().text() == $("#top").attr("name")) { //自己的评论才有权限删除
        // alert("hh");
        if (isPC() == 0) { //移动端
            obj.longPress(function() {
                var data = confirm("Confirm deletion?");
                if (data) {
                    $.ajax({
                        type: "POST",
                        async: false,
                        data: {
                            "comment_id": obj.attr("id")
                        },
                        dataType: "json",
                        url: "../../deleteComment",
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            //alert("加载错误，错误原因：\n"+errorThrown);
                        },
                        success: function(data) {
                            obj.slideUp(500, function() {
                                obj.remove();
                            })
                        }
                    });
                }
            });
        } else { //PC端
            var timeout;
            obj.mousedown(function() {
                timeout = setTimeout(function() {
                    var data = confirm("Confirm deletion?");
                    if (data) {
                        $.ajax({
                            type: "POST",
                            async: false,
                            data: {
                                "comment_id": obj.attr("id")
                            },
                            dataType: "json",
                            url: "../../deleteComment",
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                //alert("加载错误，错误原因：\n"+errorThrown);
                            },
                            success: function(data) {
                                obj.slideUp(500, function() {
                                    obj.remove();
                                })
                            }
                        });
                    }
                }, 500);
            });
            obj.mouseup(function() {
                clearTimeout(timeout);
            });
        };
    }
}