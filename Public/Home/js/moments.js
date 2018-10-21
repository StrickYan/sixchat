/***************************************************************************
 *
 * Copyright (c) 2017 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Public/Home/js/moment.js
 * @author 1275330626(com@qq.com)
 * @date 2017/08/06 03:22:39
 * @brief
 *
 **/

var swiper = new Swiper('.swiper-container', {
    // 定义滚动墙参数
    pagination: '.swiper-pagination',
    observer: true, // 修改swiper自己或子元素时，自动初始化swiper
    observeParents: true, // 修改swiper的父元素时，自动初始化swiper
    paginationClickable: true,
    spaceBetween: 0,
    centeredSlides: true,
    autoplay: 10000,
    autoplayDisableOnInteraction: false,
});

var page = 1; // 上拉加载更多全局页数
var pc_speed = 1000; // pc动画速度
var mobile_speed = 200; // 移动端动画速度

$(function () {
    FastClick.attach(document.body); // 去除移动端click延迟300ms插件fastclick初始化
    getSessionUser(); // 获取登录用户信息
    getRollingWall(); // 异步加载随机滚动3图url
    loadNextPageViaHtml(0);
    initCommentEvent();
    let isCommitted = false;// 表单是否已经提交标识，默认为false
    $(document).on("click", "#share", function () {
        if (isCommitted === false && ($.trim($("#text_box").val()) || $("#photo").val())) {
            document.body.scrollTop = document.documentElement.scrollTop = 0; // 跳转顶部
            isCommitted = true;
            addMoment();
            refresh();
            isCommitted = false;
        }
        else if (!$.trim($("#text_box").val()) && !$("#photo").val()) {
            alert("Please add a photo or some text :D");
        }
    });
    // 绑定消息点击事件
    $(document).on("click", ".message-flow", function () {
        location.href = "./details/id/" + $(this).attr("name");
    });
    // 点击修改资料按钮事件
    $(document).on("click", "#modify_profile_button", function () {
        $(this).text("confirm").attr("id", "confirm_modify");
        let input_2_val = $("#profile_name_val").text();
        let input_3_val = $("#profile_sex_val").text();
        let input_4_val = $("#profile_region_val").text();
        let input_5_val = $("#profile_whatsup_val").text();
        let input_1 = "<div id='new_avatar_btn'><span>+</span><input type='file' name='profile_upfile' id='profile_photo'></div>";
        let input_2 = "<input id='profile_name_box' name='profile_name_box' type='text' placeholder='Name' value='' maxlength=140>";
        let input_3 = "<input id='profile_sex_box' name='profile_sex_box' type='text' placeholder='Gender' value='' maxlength=140>";
        let input_4 = "<input id='profile_region_box' name='profile_region_box' type='text' placeholder='Region' value='' maxlength=140>";
        let input_5 = "<input id='profile_whatsup_box' name='profile_whatsup_box' type='text' placeholder='WhatsUp' value='' maxlength=140>";
        $("#profile_avatar").empty().append(input_1);
        $("#profile_name").empty().append(input_2);
        $("#profile_sex").empty().append(input_3);
        $("#profile_region").empty().append(input_4);
        $("#profile_whatsup").empty().append(input_5);
        $("#profile_name_box").attr("value", input_2_val);
        $("#profile_sex_box").attr("value", input_3_val);
        $("#profile_region_box").attr("value", input_4_val);
        $("#profile_whatsup_box").attr("value", input_5_val);
    });
    $(document).on("click", "#confirm_modify", function () {
        if ($.trim($("#profile_name_box").val()) && $.trim($("#profile_sex_box").val()) && $.trim($("#profile_region_box").val()) && $.trim($("#profile_whatsup_box").val())) {
            modifyProfile();
        }
    });
    // 若选择了图片则显示图片名 否则显示New Avatar Image
    $(document).on('change', "#profile_photo", function (e) {
        try {
            let name = e.currentTarget.files[0].name;
            $("#new_avatar_btn span").text('-');
        } catch (err) {
            $("#new_avatar_btn span").text("+");
        }
    });
    // 双击或长按顶部中间栏刷新
    if (isPC() === false) {
        // 移动端
        $("#current_location").longPress(function () {
            self.location.href = "";
        });
    } else {
        $(document).on("dblclick", "#current_location", function () {
            self.location.href = "";
        });
    }
    // 上拉到底加载更多
    // $(window).scroll(function() {
    //     //$(document).scrollTop() 获取垂直滚动的距离
    //     //$(document).scrollLeft() 这是获取水平滚动条的距离
    //     // if ($(document).scrollTop() <= 0) {
    //     //     alert("滚动条已经到达顶部为0");
    //     // }
    //     if (($(document).scrollTop() >= $(document).height() - $(window).height()) && $(document).scrollTop()) {
    //         //alert("滚动条已经到达底部为" + $(document).scrollTop());
    //         //alert(page);
    //         loadNextPage(page);
    //         page++;
    //     }
    // });
    // 点击主页头像
    $("#avatar").bind("click", function () {
        $("#camera").hide();
        searchUser(GLOBAL_USER_NAME);
        document.body.scrollTop = document.documentElement.scrollTop = 0; //跳转顶部
    });
    $(document).on("click", "#logout", function () {
        location.href = "../auth/logout";
    });
    // 替换文本内容
    $(".info-flow-right-text").each(function () {
        let str = $(this).text();
        $(this).html(replace_str(str));
    });
    //图片延迟加载
    $("img.lazy").lazyload({
        effect: "fadeIn",
        threshold: mobile_speed
    });
    //点击左上角导航返回
    $("#return").bind("click", function () {
        // 不处于动画状态则响应
        if (!$("#slidebar").is(":animated")) {
            clickToBack();
        }
    });
    //点击camera图标触发发送编辑页面
    $("#camera").on("click", function () {
        // 不处于动画状态则响应
        if (!$("#edit_box").is(":animated")) {
            clickCamera();
        }
    });
    $('#loading').on('click', function () {
        // loadNextPage(page);
        loadNextPageViaHtml(page);
        page++;
    });

    loadNews(); //加载未读提示
    // setInterval("loadNews()", 1000 * 60);
    Publish.newMsgNum();

});

// 获取登录用户信息
function getSessionUser() {
    $.ajax({
        type: "POST",
        url: reFormatUrl("User/getSessionUser"),
        dataType: "json",
        data: {},
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            let retData = ret[PARAM_RET_DATA];
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                alert(ret[PARAM_RET_MSG]);
                return;
            }
            $("#avatar img").attr("src", reFormatUrl("avatar_img/") + retData.avatar);
        }
    });
}

// 加载更多moments 返回一整块html直接渲染页面
function loadNextPageViaHtml(page) {
    let start_time = new Date().getTime();
    $.ajax({
        type: "POST",
        url: reFormatUrl("Moments/loadNextPageViaHtml"),
        dataType: "html",
        data: {
            "page": page
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (data) {
            $('#loading').before(data);
            $("div[data-page='" + page + "'] img.lazy").lazyload({
                placeholder: "../../Public/Home/img/default/white.png", //用图片提前占位
                // placeholder,值为某一图片路径.此图片用来占据将要加载的图片的位置,待图片加载时,占位图则会隐藏
                effect: "fadeIn", // 载入使用何种效果
                // effect(特效),值有show(直接显示),fadeIn(淡入),slideDown(下拉)等,常用fadeIn
                threshold: 200, // 提前开始加载
                // threshold,值为数字,代表页面高度.如设置为200,表示滚动条在离目标位置还有200的高度时就开始加载图片,可以做到不让用户察觉
                // event: 'click',  // 事件触发时才加载
                // event,值有click(点击),mouseover(鼠标划过),sporty(运动的),foobar(…).可以实现鼠标莫过或点击图片才开始加载,后两个值未测试…
                // container: $("#container"),  // 对某容器中的图片实现效果
                // container,值为某容器.lazyload默认在拉动浏览器滚动条时生效,这个参数可以让你在拉动某DIV的滚动条时依次加载其中的图片
                failurelimit: 3 // 图片排序混乱时
                // failurelimit,值为数字.lazyload默认在找到第一张不在可见区域里的图片时则不再继续加载,但当HTML容器混乱的时候可能出现可见区域内图片并没加载出来的情况,failurelimit意在加载N张可见区域外的图片,以避免出现这个问题.
            });
            $("div[data-page='" + page + "'] .info-flow-right-button .button-img").each(function () {
                divPop($(this));
            });
            $("div[data-page='" + page + "'] .delete-moment").bind("click", function () {
                deleteMoment($(this).parent());
            });
            refresh();

            let end_time = new Date().getTime();
            let run_time = end_time - start_time;
            if (page === 0 && run_time < 1200) {
                sleep(1200 - run_time);
            }
            $("#fake_div").css('display', '');
            $("#fakeloader").fadeOut('slow');
        }
    });
}

// 加载某条朋友圈下面的所有赞
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
                html += "<img class='like-img' src='../Public/Home/img/default/like.png'/>";
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

// 加载某条朋友圈下面的所有评论
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
                if (data[i].reply_name != data[i].replyed_name) {
                    html += "<span> @ </span>";
                    html += "<span class='comment-user-name'>" + data[i].replyed_name + "</span>"; //被回复人名
                }
                html += "<span>: </span>";
                html += "<span>" + data[i].comment + "</span>"; //评论
                html += "</div>";
            }
            $("div.info-flow-right[id=" + moment_id + "]").children(".info-flow-right-comment").children().remove();
            $("div.info-flow-right[id=" + moment_id + "]").children(".info-flow-right-comment").append(html);
        }
    });
}

// 加载所有like
function getAllLikes() {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Comment/getAllLikes"),
        dataType: "json",
        data: {},
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            $(".info-flow-right-like").empty();
            for (let i = 0; i < data.length; i++) {
                let html = "";
                html += "<img class='like-img' src='../Public/Home/img/default/like.png'/>";
                html += "<span class='like-user-name'>" + data[i].reply_names + "</span>"; //点赞人名字
                $("div.info-flow-right[id=" + data[i].moment_id + "]").children(".info-flow-right-like").append(html);
            }
        }
    });
}

// 加载所有评论
function getAllComments() {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Comment/getAllComments"),
        dataType: "json",
        data: {},
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            $(".info-flow-right-comment").empty();
            for (let i = 0; i < data.length; i++) {
                let html = "";
                html += "<div class='one-comment' id=" + data[i].comment_id + " ontouchstart='return false'>";
                html += "<span class='comment-user-name'>" + data[i].reply_name + "</span>"; //回复人名字

                if (data[i].comment_level == 1 || data[i].reply_name == data[i].replyed_name) {
                    html += "<span>: </span>";
                    html += "<span>" + data[i].comment + "</span>"; //评论
                    html += "</div>";
                } else {
                    html += "<span> @ </span>";
                    html += "<span class='comment-user-name'>" + data[i].replyed_name + "</span>"; //被回复人名

                    html += "<span>: </span>";
                    html += "<span>" + data[i].comment + "</span>"; //评论
                    html += "</div>";
                }

                $("div.info-flow-right[id=" + data[i].moment_id + "]").children(".info-flow-right-comment").append(html);
                //$(".info-flow-right-comment").hide().slideDown('slow');
                deleteComment($("div.info-flow-right[id=" + data[i].moment_id + "]").children(".info-flow-right-comment").children('#' + data[i].comment_id));
            }
        }
    });
}

// 查看用户资料
function searchUser(search_name) {
    $.ajax({
        type: "POST",
        url: reFormatUrl("User/searchUser"),
        dataType: "json",
        data: {
            "search_name": search_name
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

            if (isEmpty(data)) {
                alert("This user does not exist, please try again");
                return;
            }

            let html = '';
            html += "<form name='form_profile' id='form_profile'>";
            html += "<div id=profile_avatar><img src=" + "../avatar_img/" + data.avatar + "></div>";
            html += "<div id='profile_name' ><span class='profile-span'>Name: </span><span id='profile_name_val' class='profile-val'>" + data.user_name + "</span></div><hr>";
            html += "<div id='profile_sex' ><span class='profile-span'>Gender: </span><span id='profile_sex_val' class='profile-val'>" + data.sex + "</span></div><hr>";
            html += "<div id='profile_region' ><span class='profile-span'>Region: </span><span id='profile_region_val' class='profile-val'>" + data.region + "</span></div><hr>";
            html += "<div id='profile_whatsup' ><span class='profile-span'>What's Up: </span><span id='profile_whatsup_val' class='profile-val'>" + data.whatsup + "</span></div><hr>";
            html += "</form>";
            if (data.user_name === GLOBAL_USER_NAME) { //自己的资料可以修改
                html += "<div id='modify_profile_button'>modify</div>";
                html += "<div id='logout'>Log Out</div>";
            }
            else if (data.is_follow === 0) { //未关注
                html += "<div id='follow_button'>Follow</div>";
            }
            else if (data.is_follow === 1) { //已关注
                html += "<div id='follow_button'>Following</div>";
            }

            $("#back").text("SixChat");
            $("#current_location").text("Profile");
            $("#slidebar").remove();
            $("#slidebar_profile~div").remove();

            $("#slidebar_profile").html(html);
            if (isPC()) { //PC
                $("#slidebar_profile").fadeIn(pc_speed);
            } else {
                $("#slidebar_profile").animate({
                    left: 0
                }, mobile_speed);
            }

            // 绑定关注按钮点击事件
            let operation_follow = data.is_follow;
            $("#follow_button").bind("click", function () {
                operation_follow = 1 - operation_follow;
                follow(data.follow_id, data.followed_id, operation_follow);
            });
        }
    });
}

/**
 * @brief 关注或者取消关注
 * @param follow_id 关注人
 * @param followed_id 被关注人
 * @param operation_follow 关注操作：1：关注 0：取消关注
 * @return
 * */
function follow(follow_id, followed_id, operation_follow) {
    $.ajax({
        type: "POST",
        url: reFormatUrl("User/follow"),
        dataType: "json",
        data: {
            "follow_id": follow_id,
            "followed_id": followed_id,
            "operation_follow": operation_follow
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                alert(ret[PARAM_RET_MSG]);
                return;
            }
            $("#follow_button").text(operation_follow ? "Following" : "Follow");
        }
    });
}

// 好友请求
function friendRequest(remark, requested_name) {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Moments/friendRequest"),
        dataType: "json",
        data: {
            "remark": remark,
            "requested_name": requested_name
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                alert(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            alert("好友请求已发送，请等待对方响应");
            $("#add_friend_div input").val("").blur();
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
            // 当addLike后执行reFresh(),重新加载所有赞,所以下面单条添加可以省略
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            refresh();
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
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            // 当addComment后执行reFresh(), 重新加载所有评论
            $(".comment-box:focus").val("");
            $(".comment-box:focus").parent().hide();
            refresh();
        }
    });
}

// 发送moment
function addMoment() {
    let data = new FormData($('#form_moment')[0]);
    $("#camera").click();
    $.ajax({
        type: 'POST',
        url: reFormatUrl("Moments/addMoment"),
        dataType: 'JSON',
        data: data,
        cache: false,
        processData: false,
        contentType: false
    }).done(function (ret) {
        if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
            console.log(ret[PARAM_RET_MSG]);
            alert(ret[PARAM_RET_MSG]);
            return;
        }
        ret = ret[PARAM_RET_DATA];

        if (ret['isSuccess']) {
            let result = '';
            result += "<div class='info-flow' >";
            result += "<div class='info-flow-right' id=" + ret['moment_id'] + ">";
            result += "<div class='info-flow-right-avatar'>";
            result += '<img src=' + '../avatar_img/' + ret['avatar'] + '>';
            result += "</div>";
            result += "<div class='info-flow-right-user-name'>" + ret['user_name'] + "</div>";
            if (ret['photo']) {
                result += "<div class='info-flow-right-img'>";
                result += "<a href=../moment_img/" + ret['photo'] + " data-lightbox=" + ret['moment_id'] + ">";
                result += '<img src=' + '../moment_img/' + ret['photo'] + " >";
                result += "</a></div>";
            }
            //else {
            //    result += "<div class='info-flow-right-text only-text'>" + replace_str(ret['text_box']) + "</div>";
            //}
            if (ret['text_box']) {
                result += "<div class='info-flow-right-text'>" + replace_str(ret['text_box']) + "</div>";
            }
            result += "<div class='info-flow-right-time'>" + ret['time'] + "</div>";
            result += "<div class='delete-moment'>Delete</div>";
            result += "<div class='info-flow-right-button'>";
            result += "<img name='button' class='button-img' src='../Public/Home/img/default/feed_comment.png' />";
            result += "<div class='divPop'>";
            result += "<img class='like-png' src='../Public/Home/img/default/logout_like.png' />";
            result += "<img class='comment-png' src='../Public/Home/img/default/logout_comment.png' />";
            result += "</div>";
            result += "</div>";
            result += "<div class='info-flow-right-like'></div>";
            //if (ret['photo'] && ret['text_box']) {
            //    result += "<div class='info-flow-right-text'>About : " + replace_str(ret['text_box']) + "</div>";
            //}
            result += "<div class='info-flow-right-comment' ></div>";
            result += "<div class='info-flow-right-input' name='div_comment'>";
            result += "<input type='text' class='comment-box' placeholder='Comment' maxlength=140 required/>";
            result += "</div>";
            result += "</div>";
            result += "</div>";
            $("#free").after(result); // 插入新发布的 moment
            $('.info-flow').first().hide().slideDown(mobile_speed);
            divPop($(".info-flow-right-button .button-img").first()); // 给新载入的按钮元素绑定事件
            $(".delete-moment").first().bind("click", function () {
                // 给新载入的删除朋友圈元素绑定事件
                deleteMoment($(this).parent());
            });
        } else {
            alert('Sorry, send failed.');
        }
    });
}

// jquery $.ajax() 异步加载随机3图url
function getRollingWall() {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Moments/getRollingWall"),
        dataType: "json",
        data: {},
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            let html_1 = "<a href=./details/id/" + data[0].moment_id_1 + "><img name=" + data[0].moment_id_1 + " class='lazy' data-original=../moment_img/" + data[0].img_url_1 + "></a>";
            let html_2 = "<a href=./details/id/" + data[0].moment_id_2 + "><img name=" + data[0].moment_id_2 + " class='lazy' data-original=../moment_img/" + data[0].img_url_2 + "></a>";
            let html_3 = "<a href=./details/id/" + data[0].moment_id_3 + "><img name=" + data[0].moment_id_3 + " class='lazy' data-original=../moment_img/" + data[0].img_url_3 + "></a>";
            $(".swiper-slide").first().append(html_1);
            $(".swiper-slide").first().next().append(html_2);
            $(".swiper-slide").last().append(html_3);

            $("#slide_wall img.lazy").lazyload({
                placeholder: "../Public/Home/img/default/white.png", //用图片提前占位
                // placeholder,值为某一图片路径.此图片用来占据将要加载的图片的位置,待图片加载时,占位图则会隐藏
                effect: "fadeIn", // 载入使用何种效果
                // effect(特效),值有show(直接显示),fadeIn(淡入),slideDown(下拉)等,常用fadeIn
                threshold: 1000, // 提前开始加载
                // threshold,值为数字,代表页面高度.如设置为200,表示滚动条在离目标位置还有200的高度时就开始加载图片,可以做到不让用户察觉
                // event: 'click',  // 事件触发时才加载
                // event,值有click(点击),mouseover(鼠标划过),sporty(运动的),foobar(…).可以实现鼠标莫过或点击图片才开始加载,后两个值未测试…
                // container: $("#container"),  // 对某容器中的图片实现效果
                // container,值为某容器.lazyload默认在拉动浏览器滚动条时生效,这个参数可以让你在拉动某DIV的滚动条时依次加载其中的图片
                // failurelimit : 3 // 图片排序混乱时
                // failurelimit,值为数字.lazyload默认在找到第一张不在可见区域里的图片时则不再继续加载,但当HTML容器混乱的时候可能出现可见区域内图片并没加载出来的情况,failurelimit意在加载N张可见区域外的图片,以避免出现这个问题.
            });
        }
    });
}

// 删除moment
function deleteMoment(obj) {
    var data = confirm("Confirm deletion?");
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
        if (isPC() === false) { //移动端
            // obj.longPress(function() {
            //     var data = confirm("Confirm deletion?");
            //     if (data) {
            //         $.ajax({
            //             type: "POST",
            //             data: {
            //                 "comment_id": obj.attr("id")
            //             },
            //             dataType: "json",
            //             url: "./deleteComment",
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
                // console.log("you have done", ev.type);
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
        } else { //PC端
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

// 异步加载信息
function loadMessages() {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Comment/loadMessages"),
        dataType: "json",
        data: {},
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
            html += "<div id='search'>";
            html += "<input type='text' id='search_box' placeholder='Follow New Friends' maxlength=140 required/>";
            html += "</div>";
            for (let i = 0; i < data.length; i++) {
                html += "<div class='message-flow' name=" + data[i].moment_id + ">";
                html += "<div class='message-flow-left'>";
                html += "<img src=../avatar_img/" + data[i].avatar + " alt=''>";
                html += "</div>";
                html += "<div class='message-flow-right'>";
                html += "<div class='line1'>";
                html += "<div class='message-flow-right-user-name'>" + data[i].reply_name + "</div>";
                html += "<div class='message-flow-right-time'>" + data[i].time + "</div>";
                html += "</div>";
                html += "<div class='message-flow-right-text'>" + data[i].comment + "</div>";
                html += "</div></div>";
            }
            $("#slidebar").empty().append(html);
        }
    });
}

// 修改资料
function modifyProfile() {
    let data = new FormData($('#form_profile')[0]);
    $.ajax({
        type: 'POST',
        url: reFormatUrl("User/modifyProfile"),
        dataType: 'JSON',
        data: data,
        cache: false,
        processData: false,
        contentType: false
    }).done(function (ret) {
        if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
            console.log(ret[PARAM_RET_MSG]);
            return;
        }
        ret = ret[PARAM_RET_DATA];

        if (ret['isSuccess']) {
            alert('修改成功');
            self.location.href = "";
        } else {
            alert(ret['msg']);
        }
    });
}

// 加载未读消息数量
function loadNews() {
    $.ajax({
        type: "POST",
        url: reFormatUrl("Comment/loadNews"),
        dataType: "json",
        data: {},
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("XMLHttpRequest: " + XMLHttpRequest + "\n" + "textStatus: " + textStatus + "\n" + "errorThrown: " + errorThrown);
        },
        success: function (ret) {
            if (ret[PARAM_RET_CODE] !== ERROR_CODE_SUCCESS) {
                console.log(ret[PARAM_RET_MSG]);
                return;
            }
            let data = ret[PARAM_RET_DATA];

            let html = "<span id='news'> +" + data['number'] + "</span>";
            $("#news").remove();
            if (data['number'] !== 0) {
                $("#back").append(html);
            }
        }
    });
}

// edit a moment
function clickCamera() {
    if ($("#edit_box").length) {
        $("#edit_box").slideUp(mobile_speed, function () {
            $("#edit_box").remove();
        });
    } else {
        let html = "";
        html += "<div id='edit_box'>";
        html += "<form name='form_moment' id='form_moment'>";
        html += "<textarea id='text_box' name='text_box' placeholder='Say something ?' maxlength=280></textarea>";
        html += "<div id='btn'><span>+</span>";
        html += "<input type='file' name='upfile' id='photo'>";
        html += "</div>";
        html += "<div id='share'>Share</div>";
        html += "</form>";
        html += "</div>";
        $("#top").after(html);
        $("#edit_box").hide().slideDown(mobile_speed, function () {
            $("#text_box").focus();
        });
        $("#photo").on('change', function (e) {
            // 若选择了图片则显示图片名 否则显示+
            try {
                // var name = e.currentTarget.files[0].name;
                $("#btn span").text('-');
            } catch (err) {
                $("#btn span").text("+");
            }
        });
    }
}

// 处理各种页面返回
function clickToBack() {
    if ($("#current_location").text() === "SixChat") {
        //打开消息侧边栏
        $("#camera").hide();
        $("#current_location").text("Messages");
        $("#back").text("SixChat");
        if (isPC()) { //PC
            $("#slidebar,#message_top").fadeIn(pc_speed);
        } else {
            $("#slidebar,#message_top").animate({
                left: 0
            }, mobile_speed);
        }
        $("#slidebar_profile~div").remove();
        loadMessages(); //异步加载消息
        document.body.scrollTop = document.documentElement.scrollTop = 0; //跳转顶部
    } else if ($("#current_location").text() === "Messages") {
        //关闭消息侧边栏
        self.location.href = "";
        return;
    } else if ($("#current_location").text() === "Details" || $("#current_location").text() === "Profile") {
        //返回主页面
        self.location.href = "";
    }
}
