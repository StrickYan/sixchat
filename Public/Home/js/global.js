function initCommentEvent() {
    // 点击按钮弹出点赞或评论选项，点击周围则隐藏
    $(".info-flow-right-button .button-img").each(function() {
        divPop($(this));
    });
    // 点击点赞 执行函数
    $(document).on("click", '.like-png', function() {
        addLike($(this).parent().parent().parent().attr("id"), $(this).parent().parent().siblings(".info-flow-right-user-name").text());
    });
    // 点击按钮弹出评论框或者隐藏评论框，评论框失去焦点则隐藏
    $(document).on("click", '.comment-png', function() {
        $(this).parent().parent().siblings(".info-flow-right-input").show();
        $(this).parent().parent().siblings(".info-flow-right-input").children("input").attr("placeholder", "Comment").attr("id", $("#top").attr("name")).focus(); //输入框聚焦
    });
    $(document).on("blur", '.info-flow-right-input input', function() { //评论框失去焦点则隐藏
        $(this).parent().hide();
    });
    // 绑定删除朋友圈事件//不知为何这里如果用document会造成删除动画失效，所以暂时用bind解决
    $(".delete-moment").bind("click", function() {
        deleteMoment($(this).parent());
    });
    // 绑定点击评论弹出回复框事件
    $(document).on("click", ".one-comment", function() {
        $(this).parent().siblings(".info-flow-right-input").show();
        $(this).parent().siblings(".info-flow-right-input").children("input").attr("placeholder", "@".concat($(this).children(".comment-user-name").first().text())) //改变placeholder值
            .attr("id", $(this).children(".comment-user-name").first().text()).focus(); //输入框聚焦
    });
}
// 点击弹出赞与评论选项
function divPop(obj) {
    obj.click(function(event) {
        //$(".divPop").hide(500);
        //取消事件冒泡
        event.stopPropagation();
        //设置弹出层位置
        //var offset = obj.offset();
        if (obj.siblings(".divPop").css("display") == "block") {
            obj.siblings(".divPop").hide(mobile_speed);
        }
        else{
            obj.siblings(".divPop").css({
                top: -10,
                right: 30
            });
            //动画显示
            obj.siblings(".divPop").show(mobile_speed);
        }
    });
    //单击空白区域隐藏弹出层
    $(".info-flow").click(function() {
        obj.siblings(".divPop").hide(mobile_speed);
    });
    //单击弹出层则自身隐藏
    $(".divPop").click(function() {
        obj.siblings(".divPop").hide(mobile_speed);
    });
}
// 替换str
function replace_str(str) {
    str = str.replace(/\</g, '&lt;');
    str = str.replace(/\>/g, '&gt;');
    // str = str.replace(/\n/g, '<br />');
    //str = str.replace(/\[em_([0-9]*)\]/g,'<img src="face/$1.gif" border="0" />');
    //文本中url替换成可点击的链接 target='_blank'指明打开新窗口
    var regexp = /((http|ftp|https|file):[^'"\s]+)/ig;
    str = str.replace(regexp, "<a target='_blank' href='$1'>$1</a>");
    return str;
}
// 刷新赞与评论
function refresh() {
    getAllLikes();
    getAllComments();
}
// 刷新赞与评论在moment详情页
function refreshAtDetails() {
    /*异步加载moment的赞与评论*/
    $(".info-flow-right").each(function() {
        getLikesForAjax($(this).attr("id"), $(this).find(".info-flow-right-user-name").text());
        getCommentsForAjax($(this).attr("id"), $(this).find(".info-flow-right-user-name").text());
    });
    /*绑定删除评论事件*/
    $(".one-comment").each(function() {
        deleteComment($(this));
    });
}
//回车发送评论或朋友圈
document.onkeypress = function EnterPress(e) {
    var e = e || window.event;
    //满足 回车键&&输入框聚焦&&内容不为空
    if (e.keyCode == 13 && $(".comment-box:focus").length && $.trim($(".comment-box:focus").val())) {
        addComment();
        refresh();
    } 
    // else if (e.keyCode == 13 && $("#text_box:focus").length && ($.trim($("#text_box:focus").val()) || $("#photo").val())) {
    //     addMoment();
    //     refresh();
    // }
    //输入名字查找用户资料
    else if (e.keyCode == 13 && $("#search_box:focus").length && $.trim($("#search_box:focus").val())) {
        $("#back").click();
        searchUser($("#search_box:focus").val());
    }
    //好友请求 发送加好友备注信息
    else if (e.keyCode == 13 && $("#add_friend_div input:focus").length) {
        friendRuquest($("#add_friend_div input:focus").val(), $("#profile_name").children().last().text());
    }
};
// 自定义判定设备类型函数
function isPC() {
    var userAgentInfo = navigator.userAgent;
    var Agents = ["Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod"];
    var flag = true;
    for (var v = 0; v < Agents.length; v++) {
        if (userAgentInfo.indexOf(Agents[v]) > 0) {
            flag = false;
            break;
        }
    }
    return flag;
}
// 自定义移动端长按函数
$.fn.longPress = function(fn) {
    var timeout = undefined;
    var $this = this;
    for (var i = 0; i < $this.length; i++) {
        $this[i].addEventListener('touchstart', function(event) {
            timeout = setTimeout(fn, 500);
        }, false);
        $this[i].addEventListener('touchend', function(event) {
            clearTimeout(timeout);
        }, false);
    }
}

function sleep(n) {
    var start=new Date().getTime();
    while(true) if(new Date().getTime()-start>n) break;
}