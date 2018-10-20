var mobile_speed = 200; //移动端动画速度
$(function () {
    FastClick.attach(document.body); //去除移动端click延迟300ms插件fastclick初始化
    refreshAtDetails(); //初始化：刷新评论 给朋友圈元素绑定事件
    initCommentEvent(); //初始化点赞和评论相关事件
    //双击或长按顶部中间栏刷新
    if (isPC() === false) { //移动端
        $("#current_location").longPress(function () {
            self.location.href = "";
        });
    } else {
        $(document).on("dblclick", "#current_location", function () {
            self.location.href = "";
        });
    }
    ;
    // 替换文本内容
    $(".info-flow-right-text").each(function () {
        var str = $(this).text();
        $(this).html(replace_str(str));
    });
    $("img.lazy").lazyload({
        effect: "fadeIn",
        threshold: 200
    }); //图片延迟加载
    $("#return").bind("click", function () {
        location.href = "../../index";
    }); //点击左上角导航返回
    $("body").fadeIn('slow');
});

//回车发送评论
document.onkeypress = function EnterPress(e) {
    var e = e || window.event;
    //满足 回车键&&输入框聚焦&&内容不为空
    if (e.keyCode === 13 && $(".comment-box:focus").length && $.trim($(".comment-box:focus").val())) {
        addComment();
        // refreshAtDetails();
    }
};

// jquery $.ajax() 异步加载每条朋友圈下面的所有赞
function getLikesForAjax(moment_id, moment_user_name) {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Comment/getLikes"),
        dataType: "json",
        data: {
            "id": moment_id,
            "moment_user_name": moment_user_name
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            let html = "";
            let i;
            if (data.length) {
                html += "<img class='like-img' src='../../../Public/Home/img/default/like.png'/>";
            }
            for (i = 0; i < data.length - 1; i++) {
                html += "<span class='like-user-name'>" + data[i].reply_name + "</span>"; //点赞人名字
                html += "<span>,</span>";
            }
            if (i === data.length - 1) {
                html += "<span class='like-user-name'>" + data[i].reply_name + "</span>"; //点赞人名字
            }
            $("div.info-flow-right[id=" + moment_id + "]").children(".info-flow-right-like").empty();
            $("div.info-flow-right[id=" + moment_id + "]").children(".info-flow-right-like").append(html);
        }
    });
}

// jquery $.ajax() 异步加载每条朋友圈下面的所有评论
function getCommentsForAjax(moment_id, moment_user_name) {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Comment/getComments"),
        dataType: "json",
        data: {
            "id": moment_id,
            "moment_user_name": moment_user_name
        },
        async: false,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            let html = "";
            for (let i = 0; i < data.length; i++) {
                html += "<div class='one-comment' id=" + data[i].comment_id + " ontouchstart='return false'>";
                html += "<span class='comment-user-name'>" + data[i].reply_name + "</span>"; //回复人名字

                if (data[i].comment_level == 1 || data[i].reply_name == data[i].replyed_name) {
                    html += "<span>: </span>"
                    html += "<span>" + data[i].comment + "</span>"; //评论
                    html += "</div>";
                } else {
                    html += "<span> @ </span>";
                    html += "<span class='comment-user-name'>" + data[i].replyed_name + "</span>"; //被回复人名

                    html += "<span>: </span>";
                    html += "<span>" + data[i].comment + "</span>"; //评论
                    html += "</div>";
                }
            }
            $("div.info-flow-right[id=" + moment_id + "]").children(".info-flow-right-comment").children().remove();
            $("div.info-flow-right[id=" + moment_id + "]").children(".info-flow-right-comment").append(html);
        }
    });
}

// 点赞
function addLike(moment_id, moment_user_name) {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Comment/addLike"),
        dataType: "json",
        data: {
            "moment_id": moment_id,
            "moment_user_name": moment_user_name
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            //当addLike后执行reFresh(),重新加载所有赞,所以下面单条添加可以省略
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            refreshAtDetails();
        }
    });
}

// 评论
function addComment() {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Comment/addComment"),
        dataType: "json",
        data: {
            "moment_id": $(".comment-box:focus").parent().parent().attr("id"),
            "replyed_name": $(".comment-box:focus").attr("id"),
            "comment_val": $(".comment-box:focus").val(),
            "comment_level": $(".comment-box:focus").attr("placeholder") === "Comment" ? 1 : 2, // 评论层级，直接评论为1层，点击他人评论进行回复则为2层
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) { //当addComment后执行reFresh(),重新加载所有评论,所以下面单条添加可以省略
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            $(".comment-box:focus").val("");
            $(".comment-box:focus").parent().hide();
            refreshAtDetails();
        }
    });
}

// 删除moment
function deleteMoment(obj) {
    let data = confirm("Confirm deletion?");
    if (data) {
        $.ajax({
            type: "POST",
            url: reFormatUrl("Moments/deleteMoment"),
            dataType: "json",
            data: {
                "moment_id": obj.attr("id")
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
            },
            success: function (ret) {
                if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                    console.log(ret[PARAM_RET_MSG]);
                    return;
                }
                let data = ret[PARAM_RET_DATA];

                obj.parent().slideUp(500, function () {
                    obj.parent().remove();
                })
            }
        });
    }
}

// 删除评论函数
function deleteComment(obj) {
    if (obj.children(".comment-user-name").first().text() === GLOBAL_USER_NAME) { //自己的评论才有权限删除
        if (isPC() === false) { // 移动端
            // obj.longPress(function() {
            //     var data = confirm("Confirm deletion?");
            //     if (data) {
            //         $.ajax({
            //             type: "POST",
            //             data: {
            //                 "comment_id": obj.attr("id")
            //             },
            //             dataType: "json",
            //             url: "../../deleteComment",
            //             error: function(XMLHttpRequest, textStatus, errorThrown) {
            //                 //alert("加载错误，错误原因：\n"+errorThrown);
            //             },
            //             success: function(data) {
            //                 obj.slideUp(500, function() {
            //                     obj.remove();
            //                 })
            //             }
            //         });
            //     }
            // });

            touch.on(obj, 'hold', function (ev) {
                //console.log("you have done", ev.type);
                let data = confirm("Confirm deletion?");
                if (data) {
                    $.ajax({
                        type: "POST",
                        url: reFormatUrl("Comment/deleteComment"),
                        dataType: "json",
                        data: {
                            "comment_id": obj.attr("id")
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
                        },
                        success: function (ret) {
                            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                                console.log(ret[PARAM_RET_MSG]);
                                return;
                            }
                            let data = ret[PARAM_RET_DATA];

                            obj.slideUp(500, function () {
                                obj.remove();
                            })
                        }
                    });
                }
            });
        } else { // PC端
            let timeout;
            obj.mousedown(function () {
                timeout = setTimeout(function () {
                    let data = confirm("Confirm deletion?");
                    if (data) {
                        $.ajax({
                            type: "POST",
                            url: reFormatUrl("Comment/deleteComment"),
                            dataType: "json",
                            data: {
                                "comment_id": obj.attr("id")
                            },
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
                            },
                            success: function (ret) {
                                if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                                    console.log(ret[PARAM_RET_MSG]);
                                    return;
                                }
                                let data = ret[PARAM_RET_DATA];

                                obj.slideUp(500, function () {
                                    obj.remove();
                                })
                            }
                        });
                    }
                }, 500);
            });
            obj.mouseup(function () {
                clearTimeout(timeout);
            });
        }
    }
}
