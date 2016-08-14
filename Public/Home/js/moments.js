var swiper = new Swiper('.swiper-container', {	//定义滚动墙参数
    pagination: '.swiper-pagination',
    paginationClickable: true,
    spaceBetween: 0,
    centeredSlides: true,
    autoplay: 5000,
    autoplayDisableOnInteraction: false
});



$(function() {

	getRollingWall();//异步加载随机滚动3图url

	refresh();	//初始化：刷新评论 给朋友圈元素绑定事件

	//loadMessages();//异步加载消息

	//loadFriendRequest();//加载好友请求

	loadNews();
	setInterval("loadNews()",1000*60);
	
	$("img.lazy").lazyload({effect: "fadeIn",threshold: 200});//图片延迟加载

	$("#back").bind("click",function() {//定义点击左上角导航返回事件
        if($("#current_location").text()=="SixChat"){	//打开消息侧边栏
        	loadMessages();//异步加载消息
        	loadFriendRequest();
            $("#current_location").text("Messages"); 
            $("#back").text("SixChat"); 
            $("#slidebar,#message_top").animate({left:0},300);
            location.hash="#location";//跳到消息界面位置
            //$("#slidebar~div").fadeOut(300);
            $("#slidebar~div").animate({opacity: 0},300);
        }
        else if($("#current_location").text()=="Messages") {//关闭消息侧边栏
            $("#current_location").text("SixChat");
            $("#back").text("M"); 
            $("#slidebar,#message_top").animate({left:"-100%"},300);
            location.hash="";
           	//$("#slidebar~div").fadeIn(300);
           	$("#slidebar~div").animate({opacity: 1},300);

           	//不知为何，关闭侧边栏后会显示lightbox的隐藏区域，所以暂时想到的解决方法是加下面一句fix bug
           	$("#lightboxOverlay,#lightbox").hide();
        }
        else if($("#current_location").text()=="Details" || $("#current_location").text()=="Profile"){//返回主页面
        	self.location.href="";
        }
	});


    $(document).on("click",".message_flow",function() {
        getOneMoment($(this).attr("name"));//传送moment_id查看具体该条moment
        $("#back").click();
        $("#current_location").text("Details"); 
        $("#back").text("SixChat"); 
    });


	//点击camera图标触发发送编辑页面
	$("#camera").bind("click",function() {
		if($("#edit_box").length){
			$("#edit_box").slideUp(300,function() {
				$("#edit_box").remove();
			});
		}
		else {
			var html="";
			html+="<div id='edit_box'>";
			html+="<form name='form_moment' id='form_moment'>";
			html+="<textarea id='text_box' name='text_box' placeholder='Please press Enter to send the moment :D' maxlength=280></textarea>";
			html+="<div id='btn'><span>Add Image</span>"
			html+="<input type='file' name='upfile' id='photo'>";
			html+="</div>";
			html+="</form>";
			html+="</div>";
			$("#top").after(html);
			$("#edit_box").hide().slideDown('slow' ,function(){
				$("#text_box").focus();
			});		

			$("#photo").on( 'change', function(e){	//若选择了图片则显示图片名 否则显示Add Image
					try{
						var name = e.currentTarget.files[0].name;
						$("#btn span").text(name);
					}
					catch(err){
						$("#btn span").text("Add Image");
					}							
			});
		}
	});


	$("body").animate({opacity: 1},300);

});




