var swiper = new Swiper('.swiper-container', {	//定义滚动墙参数
    pagination: '.swiper-pagination',
    paginationClickable: true,
    spaceBetween: 0,
    centeredSlides: true,
    autoplay: 5000,
    autoplayDisableOnInteraction: false
});

var pc_speed = 1000;//pc动画速度
var mobile_speed = 200;//移动端动画速度

$(function() {

	getRollingWall();//异步加载随机滚动3图url

	refresh();	//初始化：刷新评论 给朋友圈元素绑定事件

	loadNews();
	setInterval("loadNews()",1000*60);
	
	$("img.lazy").lazyload({effect: "fadeIn",threshold: mobile_speed});//图片延迟加载

	$("#back").bind("click",function() {//定义点击左上角导航返回事件
        if($("#current_location").text()=="SixChat"){	//打开消息侧边栏
        	loadMessages();//异步加载消息
        	loadFriendRequest();
            $("#current_location").text("Messages"); 
            $("#back").text("SixChat"); 
            if(isPC()){//PC
            	$("#slidebar,#message_top").fadeIn(pc_speed);
            }
            else{
            	$("#slidebar,#message_top").animate({left:0},mobile_speed);
            }
            location.hash="#location";//跳到消息界面位置
            $("#slidebar~div").animate({opacity: 0},mobile_speed);
        }
        else if($("#current_location").text()=="Messages") {//关闭消息侧边栏
            $("#current_location").text("SixChat");
            $("#back").text("News"); 
            if(isPC()){//PC
				$("#slidebar,#message_top").fadeOut(pc_speed);
            }
            else{
            	$("#slidebar,#message_top").animate({left:"-100%"},mobile_speed);
            }
            location.hash="";
           	$("#slidebar~div").animate({opacity: 1},mobile_speed);

           	//不知为何，关闭侧边栏后会显示lightbox的隐藏区域，所以暂时想到的解决方法是加下面一句fix bug
           	$("#lightboxOverlay,#lightbox").hide();
        }
        else if($("#current_location").text()=="Details" || $("#current_location").text()=="Profile"){//返回主页面
        	self.location.href="";
        }
	});

	//点击camera图标触发发送编辑页面
	$("#camera").bind("click",function() {
		if($("#edit_box").length){
			$("#edit_box").slideUp(mobile_speed,function() {
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
			$("#edit_box").hide().slideDown(mobile_speed ,function(){
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

	$("body").animate({opacity: 1},pc_speed/4);

});




