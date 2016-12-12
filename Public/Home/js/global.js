var page = 1;   //上拉加载更多全局页数
var pc_speed = 1000;//pc动画速度
var mobile_speed = 200;//移动端动画速度
$(function() {

    //去除移动端click延迟300ms插件fastclick初始化
    FastClick.attach(document.body);

    // 点击按钮弹出点赞或评论选项，点击周围则隐藏
    $(".info-flow-right-button .button-img").each(function () {
        divPop($(this));
    });

    // 点击点赞 执行函数
    $( document ).on( "click", '.like-png', function () {
        addLike($(this).parent().parent().parent().attr("id"),$(this).parent().parent().siblings(".info-flow-right-user-name").text());
    });

    // 点击按钮弹出评论框或者隐藏评论框，评论框失去焦点则隐藏
    $( document ).on( "click", '.comment-png', function(){
        $(this).parent().parent().siblings(".info-flow-right-input").show();
        $(this).parent().parent().siblings(".info-flow-right-input").children("input")
        .attr("placeholder","Comment")
        .attr("id",$("#avatar").attr("name"))
        .focus();//输入框聚焦
    });
    $( document ).on("blur", '.info-flow-right-input input', function(){  //评论框失去焦点则隐藏
        $(this).parent().hide();
    });

    // 绑定删除朋友圈事件//不知为何这里如果用document会造成删除动画失效，所以暂时用bind解决
    $(".delete-moment").bind("click", function () {
        deleteMoment($(this).parent());
    });

    // 绑定点击评论弹出回复框事件
    $(document).on("click",".one-comment",function(){ 
        $(this).parent().siblings(".info-flow-right-input").show();
        $(this).parent().siblings(".info-flow-right-input").children("input")
        .attr("placeholder","@".concat($(this).children(".comment-user-name").first().text()))//改变placeholder值
        .attr("id",$(this).children(".comment-user-name").first().text())
        .focus();//输入框聚焦
    });
    
    // 绑定消息点击事件
    $(document).on("click",".message-flow",function() {
        getOneMoment($(this).attr("name"));//传送moment_id查看具体该条moment
        $("#back").click();
        $("#current_location").text("Details"); 
        $("#back").text("SixChat"); 
    });

    // 响应好友请求
    $(document).on("click",".request-agree",function() {
        //传送请求id和请求人名
        agreeRequest($(this).parent().parent(".request-flow").attr("name"),$(this).siblings(".line1").children(".request-flow-right-user-name").children("span").text());
        $(this).parent().parent(".request-flow").slideUp(mobile_speed);
    });

    // 点击修改资料按钮事件
    $(document).on("click","#modify_profile_button",function() {
        $(this).text("confirm").attr("id","confirm_modify");
        var input_2_val = $("#profile_name_val").text();
        var input_3_val = $("#profile_sex_val").text();
        var input_4_val = $("#profile_region_val").text();
        var input_5_val = $("#profile_whatsup_val").text(); 
        var input_1 = "<div id='new_avatar_btn'><span>New Avatar Image</span><input type='file' name='profile_upfile' id='profile_photo'></div>";
        var input_2 = "<input id='profile_name_box' name='profile_name_box' type='text' placeholder='Name' value='' maxlength=140>";
        var input_3 = "<input id='profile_sex_box' name='profile_sex_box' type='text' placeholder='Gender' value='' maxlength=140>";
        var input_4 = "<input id='profile_region_box' name='profile_region_box' type='text' placeholder='Region' value='' maxlength=140>";
        var input_5 = "<input id='profile_whatsup_box' name='profile_whatsup_box' type='text' placeholder='WhatsUp' value='' maxlength=140>";
        
        $("#profile_avatar").empty().append(input_1);
        $("#profile_name").empty().append(input_2);
        $("#profile_sex").empty().append(input_3);
        $("#profile_region").empty().append(input_4);
        $("#profile_whatsup").empty().append(input_5);

        $("#profile_name_box").attr("value",input_2_val);
        $("#profile_sex_box").attr("value",input_3_val);
        $("#profile_region_box").attr("value",input_4_val);
        $("#profile_whatsup_box").attr("value",input_5_val);
    });
    $(document).on("click","#confirm_modify",function() {
        if( $.trim($("#profile_name_box").val()) && $.trim($("#profile_sex_box").val()) && $.trim($("#profile_region_box").val()) && $.trim($("#profile_whatsup_box").val()) ){   
            modifyProfile();
        } 
    });

    //若选择了图片则显示图片名 否则显示New Avatar Image
    $(document).on( 'change', "#profile_photo" , function(e){  
        try{
            var name = e.currentTarget.files[0].name;
            $("#new_avatar_btn span").text(name);
        }
        catch(err){
            $("#new_avatar_btn span").text("New Avatar Image");
        }                           
    });

    //双击或长按顶部中间栏刷新
    if(isPC()==0){   //移动端
        $("#current_location").longPress(function() {
            self.location.href="";
        });
    }
    else{
        $(document).on("dblclick","#current_location",function() {    
            self.location.href="";
        }); 
    };

    // 上拉到底加载更多
    $(window).scroll(function() {
        //$(document).scrollTop() 获取垂直滚动的距离
        //$(document).scrollLeft() 这是获取水平滚动条的距离
        // if ($(document).scrollTop() <= 0) {
        //     alert("滚动条已经到达顶部为0");
        // }
        if ( ( $(document).scrollTop() >= $(document).height() - $(window).height() ) && $(document).scrollTop() ) {
            //alert("滚动条已经到达底部为" + $(document).scrollTop());
            //alert(page);     
            loadNextPage(page);
            page++;
        }
    });

    // 点击主页头像
    $("#avatar").bind("click", function () {
        searchUser($("#camera").attr("name"));
    });

    $(document).on("click","#logout",function() {
        location.href="./logout";
    });

    // 替换文本内容
    $(".info-flow-right-text").each(function(){
        var str = $(this).text();
        $(this).html(replace_str(str));
    });

});

// 替换str
function replace_str(str){
    str = str.replace(/\</g,'&lt;');
    str = str.replace(/\>/g,'&gt;');
    str = str.replace(/\n/g,'<br />');
    //str = str.replace(/\[em_([0-9]*)\]/g,'<img src="face/$1.gif" border="0" />');
    
    //文本中url替换成可点击的链接 target='_blank'指明打开新窗口
    var regexp = /((http|ftp|https|file):[^'"\s]+)/ig;
    str = str.replace(regexp,"<a target='_blank' href='$1'>$1</a>");
    
    return str;
}

// 加载更多moments
function loadNextPage(page) {
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{"page":page},  
        dataType:"json",  
        url:"./loadNextPage",  

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            alert("加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){  
            var result = '';
            for(var i=0;i<data.length;i++){              
                result += "<div class='info-flow' >";
                result += "<div class='info-flow-left'>";
                result += '<img src='+'../../avatar_img/'+ data[i]['avatar'] +'>';
                result += "</div>";
                result += "<div class='info-flow-right' id="+data[i]['moment_id']+">";
                result += "<div class='info-flow-right-user-name'>"+ data[i]['user_name'] +"</div>";
                if(data[i]['info']){
                    result += "<div class='info-flow-right-text'>"+ data[i]['info'] +"</div>";
                }          
                if(data[i]['img_url']){
                    result += "<div class='info-flow-right-img'>";
                    result += "<a href=../../moment_img/"+ data[i]['img_url']+" data-lightbox="+data[i]['moment_id']+">";
                    result += '<img src=' +'../../moment_img/'+ data[i]['img_url']  + " onload='formatImg(this)'>";
                    result += "</a></div>";
                }
                result += "<div class='info-flow-right-time'>"+ data[i]['time'] +"</div>";
                if($("#camera").attr("name")==data[i]['user_name']){
                    result += "<div class='delete-moment'>Delete</div>";
                }        
                result += "<div class='info-flow-right-button'>";
                result += "<img name='button' class='button-img' src='../../Public/Home/img/default/feed_comment.png' />";
                result += "<div class='divPop'>";
                result += "<img class='like-png' src='../../Public/Home/img/default/logout_like.png' />";
                result += "<img class='comment-png' src='../../Public/Home/img/default/logout_comment.png' />";
                result += "</div>";
                result += "</div>";
                result += "<div class='info-flow-right-like'></div>";
                result += "<div class='info-flow-right-comment' ></div>";
                result += "<div class='info-flow-right-input' name='div_comment'>";
                result += "<input type='text' class='comment-box' placeholder='Comment' maxlength=140 required/>";
                result += "</div>";
                result += "</div>";
                result += "</div>";         
            }   
            $('#footer').before(result).fadeOut().fadeIn(pc_speed);
            $(".info-flow-right-button .button-img").each(function(){
                divPop($(this));
            });
            $(".delete-moment").unbind().bind("click",function() {
                deleteMoment($(this).parent());
            });
            refresh();
        } 
    });  
}

// 刷新赞与评论 
function refresh() {	
	/*异步加载每条朋友圈的赞与评论*/
	$(".info-flow-right").each(function(){
        getLikesForAjax($(this).attr("id"),$(this).find(".info-flow-right-user-name").text());
		getCommentsForAjax($(this).attr("id"),$(this).find(".info-flow-right-user-name").text());
	});

    /*绑定删除评论事件*/
    $(".one-comment").each(function() { 
        deleteComment($(this));
    });

}

// jquery $.ajax() 异步加载每条朋友圈下面的所有赞
function getLikesForAjax(moment_id,moment_user_name){  
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{"id": moment_id,"moment_user_name": moment_user_name},   
        dataType:"json",  
        url:"./getLikes", 

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){ 
            var html="";  
            var i=0;
            if (data.length){
                html+="<img class='like-img' src='../../Public/Home/img/default/like.png'/>";              
            }
            for(i=0;i<data.length-1;i++){  
                html+="<span class='like-user-name'>"+data[i].reply_name+"</span>";//点赞人名字
                html+="<span>,</span>";
                //html+="</div>";  
            }
            if(i==data.length-1){  
                html+="<span class='like-user-name'>"+data[i].reply_name+"</span>";//点赞人名字
                //html+="</div>";                
            }  
            $("#"+moment_id).children(".info-flow-right-like").empty();
            $("#"+moment_id).children(".info-flow-right-like").append(html);   
        }  
    });  
}  


// jquery $.ajax() 异步加载每条朋友圈下面的所有评论
function getCommentsForAjax(moment_id,moment_user_name){  
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{"id": moment_id,"moment_user_name": moment_user_name},   
        dataType:"json",  
        url:"./getComments", 

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){  
            var html="";  
            for(var i=0;i<data.length;i++){  
                html+="<div class='one-comment' id="+data[i].comment_id+" ontouchstart='return false'>";  
                html+="<span class='comment-user-name'>"+data[i].reply_name+"</span>";//回复人名字
                if(data[i].reply_name!=data[i].replyed_name){
                    html+="<span> @ </span>";
               		html+="<span class='comment-user-name'>"+data[i].replyed_name+"</span>";//被回复人名	
                }
                html+="<span>：</span>"
                html+="<span>"+data[i].comment+"</span>";//评论
                html+="</div>";  
            }  
            $("#"+moment_id).children(".info-flow-right-comment").children().remove();
            $("#"+moment_id).children(".info-flow-right-comment").append(html);     
        }  
    });  
}  

//回车发送评论或朋友圈
document.onkeypress=function EnterPress(e) {
	var e = e || window.event;   
	//满足 回车键&&输入框聚焦&&内容不为空
	if(e.keyCode == 13 && $(".comment-box:focus").length && $.trim($(".comment-box:focus").val())){   
		addComment();
		refresh();
	}   
	else if( e.keyCode == 13 
		&& $("#text_box:focus").length 
		&& ( $.trim($("#text_box:focus").val()) || $("#photo").val()) ){   
		addMoment();
		refresh();
	} 
    //输入名字查找用户资料
    else if( e.keyCode == 13 
        && $("#search_box:focus").length 
        &&  $.trim($("#search_box:focus").val()) ){
        $("#back").click();   
        searchUser($("#search_box:focus").val());
    } 
    //好友请求 发送加好友备注信息
    else if( e.keyCode == 13 && $("#add_friend_div input:focus").length ){   
        friendRuquest($("#add_friend_div input:focus").val(),$("#profile_name").children().last().text());
    }

};

// 查看用户资料
function searchUser(search_name) {
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{"search_name":search_name},  
        dataType:"json",  
        url:"./searchUser",  

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("查找用户失败，失败原因：\n"+errorThrown);  
        },  
        success:function(data){ 
            if(data.user_name==undefined){
                alert("This user does not exist, please try again");
                return;
            }
            var html='';
            html+="<form name='form_profile' id='form_profile'>";
            html+="<div id=profile_avatar><img src="+"../../avatar_img/"+data.avatar+"></div>";
            html+="<div id='profile_name' ><span class='profile-span'>Name：</span><span id='profile_name_val'>"+data.user_name+"</span></div><hr>";
            html+="<div id='profile_sex' ><span class='profile-span'>Gender：</span><span id='profile_sex_val'>"+data.sex+"</span></div><hr>";
            html+="<div id='profile_region' ><span class='profile-span'>Region：</span><span id='profile_region_val'>"+data.region+"</span></div><hr>";
            html+="<div id='profile_whatsup' ><span class='profile-span'>What's Up：</span><span id='profile_whatsup_val'>"+data.whatsup+"</span></div><hr>";
            html+="</form>";
            if(data.is_friend==0){//还不是好友关系则显示
                html+="<div id='add_friend_div' ><input type='text' placeholder='write some remark here to your new friend' maxlength=140 /></div>";
            }
            if(data.user_name==$("#camera").attr("name")){//自己的资料可以修改
                html+="<div id='modify_profile_button'>modify</div>";
            }
            if(data.user_name==$("#camera").attr("name")){
                html+="<div id='logout'>Log Out</div>";
            }
            $("#slidebar_profile").empty().append(html);
            if(isPC()){//PC
                $("#slidebar_profile").fadeIn(pc_speed);
            }
            else{
                $("#slidebar_profile").animate({right:0},mobile_speed);
            }
            $("#slidebar_profile~div").animate({opacity:0},100);
            $("#back").text("SixChat");
            $("#current_location").text("Profile");
        } 
    });   
};

// 好友请求
function friendRuquest(remark,requested_name) {
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{"remark":remark,"requested_name":requested_name},  
        dataType:"json",  
        url:"./friendRuquest",  

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("好友请求发送失败，失败原因：\n"+errorThrown);  
        },  
        success:function(data){ 
            alert("好友请求已发送，请等待对方响应");
            $("#add_friend_div input").val("").blur();
        } 
    });    
};

// 点赞
function addLike(moment_id,moment_user_name) {
   $.ajax({  
        type:"POST",  
        async:false,  
        data:{"moment_id":moment_id ,"moment_user_name":moment_user_name},  
        dataType:"json",  
        url:"./addLike",  

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){  //当addLike后执行reFresh(),重新加载所有赞,所以下面单条添加可以省略
            refresh();
        } 
    });   
}

// 评论
function addComment(){  
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{"moment_id":$(".comment-box:focus").parent().parent().attr("id") ,"replyed_name":$(".comment-box:focus").attr("id") ,"comment_val":$(".comment-box:focus").val()},  
        dataType:"json",  
        url:"./addComment",  

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){  //当addComment后执行reFresh(),重新加载所有评论,所以下面单条添加可以省略
            // var html="";  
            // for(var i=0;i<data.length;i++){  
            //     html+="<div class='one-comment' id="+data[i].comment_id+">";  
            //     html+="<span class='comment-user-name'>"+data[i].reply_name+"</span>";//回复人名字
            //     if(data[i].reply_name!=data[i].replyed_name){
            //         html+=" @ ";
            //     	html+="<span class='comment-user-name'>"+data[i].replyed_name+"</span>";//被回复人名           	
            //     }
            //     html+="："
            //     html+="<span>"+data[i].comment_val+"</span>";//评论
            //     html+="</div>";  
            // }   
            // $(".comment-box:focus").parent().siblings(".info-flow-right-comment").append(html); 
            $(".comment-box:focus").val("");
            $(".comment-box:focus").parent().hide();  
        } 
    });  
} 

// 发送moment
function addMoment() {
    var data = new FormData($('#form_moment')[0]);
    $.ajax({
        url: './addMoment',
        type: 'POST',
        data: data,
        dataType: 'JSON',
        cache: false,
        processData: false,
        contentType: false
    }).done(function(ret){
        if(ret['isSuccess']){
            var result = '';
            result += "<div class='info-flow' >";
            result += "<div class='info-flow-left'>";
            result += '<img src='+'../../avatar_img/'+ ret['avatar'] +'>';
            result += "</div>";
            result += "<div class='info-flow-right' id="+ret['moment_id']+">";
            result += "<div class='info-flow-right-user-name'>"+ ret['user_name'] +"</div>";
            if(ret['text_box']){
            	result += "<div class='info-flow-right-text'>"+ ret['text_box'] +"</div>";
            }          
            if(ret['photo']){
            	result += "<div class='info-flow-right-img'>";
                result += "<a href=../../moment_img/"+ ret['photo']+" data-lightbox="+ret['moment_id']+">";
           		result += '<img src=' +'../../moment_img/'+ ret['photo']  + " onload='formatImg(this)'>";
            	result += "</a></div>";
            }
            result += "<div class='info-flow-right-time'>"+ ret['time'] +"</div>";
            result += "<div class='delete-moment'>Delete</div>";
            result += "<div class='info-flow-right-button'>";
            result += "<img name='button' class='button-img' src='../../Public/Home/img/default/feed_comment.png' />";
            result += "<div class='divPop'>";
            result += "<img class='like-png' src='../../Public/Home/img/default/logout_like.png' />";
            result += "<img class='comment-png' src='../../Public/Home/img/default/logout_comment.png' />";
            result += "</div>";
            result += "</div>";
            result += "<div class='info-flow-right-like'></div>";
            result += "<div class='info-flow-right-comment' ></div>";
            result += "<div class='info-flow-right-input' name='div_comment'>";
            result += "<input type='text' class='comment-box' placeholder='Comment' maxlength=140 required/>";
            result += "</div>";
            result += "</div>";
            result += "</div>";
            
            $("#camera").click();
			$('.info-flow').first().before(result).hide().slideDown(mobile_speed);
            divPop($(".info-flow-right-button .button-img").first());//给新载入的按钮元素绑定事件
            $(".delete-moment").first().bind("click",function() {   //给新载入的删除朋友圈元素绑定事件
                deleteMoment($(this).parent());
            });
        }else{
            alert('发送失敗');
        }
    });
}

// jquery $.ajax() 异步加载随机3图url
function getRollingWall(){  
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{},  
        dataType:"json",  
        url:"./getRollingWall",  

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){    
            var html_1="<img name="+data[0].moment_id_1+" src=../../moment_img/"+data[0].img_url_1+">";  
            var html_2="<img name="+data[0].moment_id_2+" src=../../moment_img/"+data[0].img_url_2+">";
            var html_3="<img name="+data[0].moment_id_3+" src=../../moment_img/"+data[0].img_url_3+">";

            $(".swiper-slide").first().append(html_1);
            $(".swiper-slide").first().next().append(html_2);
            $(".swiper-slide").last().append(html_3);
            $(".swiper-slide img").bind("click",function() {//跳转该图片所属moment详情
                getOneMoment($(this).attr("name"));
                $("#current_location").text("Details"); 
                $("#back").text("SixChat"); 
                $("#top~div").hide().show(mobile_speed);
            });
                
        }  
    });  
}

// 点击弹出赞与评论选项
function divPop(obj) {
    obj.click(function(event){
        //$(".divPop").hide(500);
        //取消事件冒泡  
        event.stopPropagation();  
        //设置弹出层位置  
        var offset = obj.offset();  
        obj.siblings(".divPop").css({ top: -10, right:  30 });  
        //动画显示  
        obj.siblings(".divPop").show(mobile_speed);  
    });
    //单击空白区域隐藏弹出层  
    $(".info-flow").click(function() { obj.siblings(".divPop").hide(mobile_speed);  });  
    //单击弹出层则自身隐藏  
    $(".divPop").click(function() { obj.siblings(".divPop").hide(mobile_speed); });  
}

// 删除moment
function deleteMoment(obj) {
    var data = confirm("Confirm deletion?");
    if(data){
        $.ajax({
            type:"POST",
            async:false,  
            data:{"moment_id":obj.attr("id")},  
            dataType:"json",  
            url:"./deleteMoment",  

            error:function(XMLHttpRequest, textStatus, errorThrown) {  
                //alert("加载错误，错误原因：\n"+errorThrown);  
            },  
            success:function(data){ 
                obj.parent().slideUp(500,function() {
                    obj.parent().remove(); 
                }) 
            }                 
        });
    }
}

// 自定义判定设备类型函数
function isPC() {
    var userAgentInfo = navigator.userAgent;
    var Agents = ["Android", "iPhone",
                "SymbianOS", "Windows Phone",
                "iPad", "iPod"];
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
    for(var i = 0;i<$this.length;i++){
        $this[i].addEventListener('touchstart', function(event) {
            timeout = setTimeout(fn, 500);
            }, false);
        $this[i].addEventListener('touchend', function(event) {
            clearTimeout(timeout);
            }, false);
    }
}

// 删除评论函数
function deleteComment(obj) {
    if(obj.children(".comment-user-name").first().text()==$("#avatar").attr("name")){//自己的评论才有权限删除
       // alert("hh");
        if(isPC()==0){   //移动端
            obj.longPress(function(){
                var data = confirm("Confirm deletion?");
                if(data){
                    $.ajax({
                        type:"POST",
                        async:false,  
                        data:{"comment_id":obj.attr("id")},  
                        dataType:"json",  
                        url:"./deleteComment",  

                        error:function(XMLHttpRequest, textStatus, errorThrown) {  
                            //alert("加载错误，错误原因：\n"+errorThrown);  
                        },  
                        success:function(data){ 
                            obj.slideUp(500,function() {
                                obj.remove(); 
                            }) 
                        }                 
                    });
                }
            });
        }
        else{   //PC端
            var timeout ;    
            obj.mousedown(function() { 
                timeout = setTimeout(function() {  
                    var data = confirm("Confirm deletion?");
                    if(data){
                        $.ajax({
                            type:"POST",
                            async:false,  
                            data:{"comment_id":obj.attr("id")},  
                            dataType:"json",  
                            url:"./deleteComment",  

                            error:function(XMLHttpRequest, textStatus, errorThrown) {  
                                //alert("加载错误，错误原因：\n"+errorThrown);  
                            },  
                            success:function(data){ 
                                obj.slideUp(500,function() {
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

//停止默认事件
function preventDefault(event) {
    var e = event || window.event;
    if (e.preventDefault)
        e.preventDefault();
    e.returnValue = false;
};

// 异步加载信息
function loadMessages() {
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{},   
        dataType:"json",  
        url:"./loadMessages", 

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("信息加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){    
            var html="";
            html+="<div id='location' name='location'></div> <!-- 设定一个location.hash位置 -->";  
            html+="<div id='search'>"
            html+="<input type='text' id='search_box' placeholder='Search New Friends' maxlength=140 required/>"
            html+="</div>";
            for(var i=0;i<data.length;i++){  
                html+="<div class='message-flow' name="+data[i].moment_id+">";
                html+="<div class='message-flow-left'>";
                html+="<img src=../../avatar_img/"+data[i].avatar+" alt=''>";
                html+="</div>";
                html+="<div class='message-flow-right'>";
                html+="<div class='line1'>";
                html+="<div class='message-flow-right-user-name'>"+data[i].reply_name+"</div>";
                html+="<div class='message-flow-right-time'>"+data[i].time+"</div>";
                html+="</div>";
                html+="<div class='message-flow-right-text'>"+data[i].comment+"</div>";
                html+="</div></div>";
            }  
            $("#slidebar").empty().append(html);                 
        }  
    });  
};

// 查看一条moment
function getOneMoment(moment_id) {
    $.ajax({
        url: './getOneMoment',
        type: 'POST',
        data: {"moment_id": moment_id},
        dataType: 'JSON',
        async:false,  

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("信息加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){    
            var result = '';
            result += "<div class='info-flow' id='the_one_moment'>";
            result += "<div class='info-flow-left'>";
            result += '<img src='+'../../avatar_img/'+ data[0]['avatar'] +'>';
            result += "</div>";
            result += "<div class='info-flow-right' id="+data[0]['moment_id']+">";
            result += "<div class='info-flow-right-user-name'>"+ data[0]['user_name'] +"</div>";
            if(data[0]['text_box']){
                result += "<div class='info-flow-right-text'>"+ data[0]['text_box'] +"</div>";
            }          
            if(data[0]['photo']){
                result += "<div class='info-flow-right-img'>";
                result += "<a href=../../moment_img/"+ data[0]['photo'] + " data-lightbox="+data[0]['moment_id']+">";
                result += '<img src=' +'../../moment_img/'+ data[0]['photo']  + " onload='formatImg(this)'>";
                result += "</a></div>";
            }
            result += "<div class='info-flow-right-time'>"+ data[0]['time'] +"</div>";
            if(data[0]['user_name']==data[0]['my_name']){
                result += "<div class='delete-moment'>Delete</div>";
            }
            result += "<div class='info-flow-right-button'>";
            result += "<img name='button' class='button-img' src='../../Public/Home/img/default/feed_comment.png' />";
            result += "<div class='divPop'>";
            result += "<img class='like-png' src='../../Public/Home/img/default/logout_like.png' />";
            result += "<img class='comment-png' src='../../Public/Home/img/default/logout_comment.png' />";
            result += "</div>";
            result += "</div>";
            result += "<div class='info-flow-right-like'></div>";
            result += "<div class='info-flow-right-comment' ></div>";
            result += "<div class='info-flow-right-input' name='div_comment'>";
            result += "<input type='text' class='comment-box' placeholder='Comment' maxlength=140 required/>";
            result += "</div>";
            result += "</div>";
            result += "</div>";
            
            var my_name = $("#avatar").attr("name");//临时存储我的名字

            $("#slidebar~div").remove();    
            $("#slidebar").after(result);

            $(".comment-box").attr("id",my_name);//将我的名字赋值给输入框作为id属性
  
            divPop($(".info-flow-right-button .button-img").first());//给新载入的按钮元素绑定事件
            $(".delete-moment").first().bind("click",function() {   //给新载入的删除朋友圈元素绑定事件
                deleteMoment($(this).parent());
            });              

            refresh();
        }
    });  
};

// 加载好友添加请求
function loadFriendRequest() {
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{},   
        dataType:"json",  
        url:"./loadFriendRequest", 

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
            //alert("好友请求加载错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){    
            var html="";
            for(var i=0;i<data.length;i++){  
                html+="<div class='request-flow' name="+data[i].id+">";
                html+="<div class='request-flow-left'>";
                html+="<img src=../../avatar_img/"+data[i].avatar+" alt=''>";
                html+="</div>";
                html+="<div class='request-flow-right'>";
                html+="<div class='line1'>";
                html+="<div class='request-flow-right-user-name'><span>"+data[i].request_name+"</span> wants to add you.</div>";
                html+="<div class='request-flow-right-time'>"+data[i].time+"</div>";
                html+="</div>";
                html+="<div class='request-flow-right-text'>remark："+data[i].remark+"</div>";
                html+="<div class='request-agree'>agree</div>";
                html+="</div></div>";
            }  
            $("#search").after(html);                 
        }  
    });  
};

// 处理好友请求
function agreeRequest(id,request_name) {
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{"id":id,"request_name":request_name},   
        dataType:"json",  
        url:"./agreeRequest", 

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
           // alert("好友添加错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){    
            alert("已添加对方为好友");                 
        }  
    });  
};

// 修改资料
function modifyProfile() {
    var data = new FormData($('#form_profile')[0]);
    $.ajax({
        url: './modifyProfile',
        type: 'POST',
        data: data,
        dataType: 'JSON',
        cache: false,
        processData: false,
        contentType: false
    }).done(function(ret){
        if(ret['isSuccess']){
            alert('修改成功');
            self.location.href="";
        }else{
            alert('修改失敗');
        }
    });
}

// 加载未读消息数量
function loadNews() {
    $.ajax({  
        type:"POST",  
        async:false,  
        data:{},   
        dataType:"json",  
        url:"./loadNews", 

        error:function(XMLHttpRequest, textStatus, errorThrown) {  
           // alert("查询错误，错误原因：\n"+errorThrown);  
        },  
        success:function(data){    
            var html = "<span id='news'> +"+data['number']+"</span>";
            $("#news").remove();
            if(data['number']!=0){
                $("#back").append(html);
            }               
        }  
    });  
}
