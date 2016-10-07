<?php
namespace Home\Controller;
use Think\Controller;
class MomentsController extends CommonController {
   
	/*显示朋友圈信息流*/
    public function index(){
    	$obj=new SixChatApi2016Controller();
		session_start();
		$user_name=$_SESSION["name"];
		
		//获取自己头像
		$map['user_name']=$user_name;
		$avatar=M("User")->where($map)->getField('avatar');

		//获取朋友圈信息流
		$list = D("Moment")->getMoments();
		for($i=0;$i<count($list);$i++){
			$list[$i]['user_name'] = htmlspecialchars($list[$i]['user_name']);
			$list[$i]['time']=$obj->tranTime(strtotime($list[$i]['time']));
			$list[$i]['info'] = htmlspecialchars($list[$i]['info']);
		}	

		$this->assign('list',$list);
      	$this->assign('avatar',$avatar);
      	$this->assign('my_name',$user_name);
        $this->display();
    }

  /*jquery异步加载每条朋友圈下面的赞用到的后台获取赞函数*/
    public function getLikes(){
	    if(isset($_REQUEST['id'])){  
		    $id = htmlspecialchars($_REQUEST['id']);  //moment_id
		    $moment_user_name = htmlspecialchars($_REQUEST['moment_user_name']);	//获取该条朋友圈的用户名
		    $user_name = $_SESSION["name"];	//当前用户
		    if(is_numeric($id) ){//是数字  
		    	$id = intval($id);  
		        $sql = "";  

		        /************************************************************权限版本
		    	if(!strcmp($user_name,$moment_user_name)){	
		    	//相等，浏览自己的帖子可以看到所有赞包括好友与非好友
				    $sql = "SELECT u.user_name as like_name FROM think_like l,think_user u where l.like_user_id = u.user_id and l.moment_id=".$id." and l.state=1 order by l.like_id asc";
		    	}
	         	else {//浏览他人的帖子时只能看到互为好友的赞或者 自己与该用户的赞
					$obj=new SixChatApi2016Controller();
		 			foreach ($obj->getUserId($user_name, $moment_user_name) as $k=>$val){
						$user_id = $val["reply_id"];
						$moment_user_id = $val["replyed_id"];
	         			$sql = "SELECT distinct(u.user_name) as like_name FROM think_like l,think_friend f,think_user u where l.like_user_id = u.user_id and l.moment_id=".$id." and l.state=1 and ((f.user_id=".$user_id." and f.friend_id=l.like_user_id) OR (l.like_user_id=".$moment_user_id.")) order by l.like_id asc";//？？？？？查询结果三个重复待排查
					}
	         	}
	         	***********************************************************************/

	         	//获取likes
	         	$list = D("Comment")->getLikes($id);
		        for($i=0;$i<count($list);$i++){  
		            $list[$i]=array(
		            	"reply_name"=>htmlspecialchars($list[$i]['reply_name']));
		        }  

		        echo json_encode($list);  
		    }  
		}  
    }

    /*jquery异步加载每条朋友圈下面的评论用到的后台获取评论函数*/
    public function getComments(){
	    if(isset($_REQUEST['id'])){  
		    $id = htmlspecialchars($_REQUEST['id']);  
		    $moment_user_name = htmlspecialchars($_REQUEST['moment_user_name']);	//获取该条朋友圈的用户名
		    $user_name = $_SESSION["name"];	//当前用户
		    
		    if(is_numeric($id) ){//是数字  
		    	$id = intval($id);  
		        $list = '';
		    	if(!strcmp($user_name,$moment_user_name)){	//相等，浏览自己的帖子可以看到所有评论包括好友与非好友
				    $list = D("Comment")->getComments1($id); 
		    	}
	         	else {//浏览他人的帖子时只能看到互为好友的评论或者 自己与该用户的对话
					$obj=new SixChatApi2016Controller();
		 			foreach ($obj->getUserId($user_name, $moment_user_name) as $k=>$val){
						$user_id = $val["reply_id"];
						$moment_user_id = $val["replyed_id"];
	         			$list = D("Comment")->getComments2($id,$user_id,$moment_user_id); 
					}
	         	}
		        for($i=0;$i<count($list);$i++){  
		            $list[$i]=array(
		            	"reply_name"=>htmlspecialchars($list[$i]['reply_name']),
		            	"replyed_name"=>htmlspecialchars($list[$i]['replyed_name']),
		            	"comment_id"=>$list[$i]['comment_id'],
		            	"comment"=>htmlspecialchars($list[$i]['comment']),
		            	"time"=>$list[$i]['time']);
		        }  
		        
		        echo json_encode($list);  
		    }  
		}  
    }

  /*jquery异步加载增加点赞函数*/
    public function addLike(){
	    if( isset($_REQUEST['moment_id'])  && isset($_REQUEST['moment_user_name']) ){ 
	    	$moment_id = htmlspecialchars($_REQUEST['moment_id']);
		    $reply_name = $_SESSION["name"];  
		    $replyed_name = htmlspecialchars($_REQUEST['moment_user_name']);  

			$obj=new SixChatApi2016Controller();
 			foreach ($obj->getUserId($reply_name, $replyed_name) as $k=>$val){
				$reply_id = $val["reply_id"];
				$replyed_id = $val["replyed_id"];

				$condition['moment_id'] = $moment_id;
				$condition['reply_id'] = $reply_id;
				$condition['replyed_id'] = $replyed_id;
				$condition['state'] = 1;
				$condition['type'] = 1;
				$result = M("Comment")->where($condition)->getField('comment_id');

				if($result){	//已点赞 则删除赞记录
					M("Comment")->where("comment_id=$result and type=1")->setField('state',0);
				}
				else {	//没有点赞记录 则增加点赞
					//插入赞
					$Comment=M("Comment");
			        $data['moment_id']=$moment_id;
			        $data['reply_id']=$reply_id;
			        $data['replyed_id']=$replyed_id;
			        $data['time']=date("Y-m-d H:i:s");
			        $data['type']=1;
			        $data['comment']="赞了你";
			        $Comment->data($data)->add();				
				}
			}	
			$list[0]="点赞成功";
			echo json_encode($list);   
		}  
    }


   /*jquery异步加载增加评论函数*/
    public function addComment(){
	    if( isset($_REQUEST['moment_id'])  && isset($_REQUEST['replyed_name']) && isset($_REQUEST['comment_val']) ){ 
	    	$moment_id = htmlspecialchars($_REQUEST['moment_id']);
		    $reply_name = $_SESSION["name"];  
		    $replyed_name = htmlspecialchars($_REQUEST['replyed_name']);  
		    $comment_val = htmlspecialchars(trim($_REQUEST['comment_val'])); 

			$obj=new SixChatApi2016Controller();
 			foreach ($obj->getUserId($reply_name, $replyed_name) as $k=>$val){
				$reply_id = $val["reply_id"];
				$replyed_id = $val["replyed_id"];

				//插入评论
				$Comment=M("Comment");
		        $data['moment_id']=$moment_id;
		        $data['reply_id']=$reply_id;
		        $data['replyed_id']=$replyed_id;
		        $data['comment']=$comment_val;
		        $data['time']=date("Y-m-d H:i:s");
		        $data['type']=2;
		        $Comment->data($data)->add();
			}	
			
			//获取新增评论的comment_id
			$comment_id=M("Comment")->max('Comment_id');

			//返回json数据
            $list[]=array(
            	"comment_id"=>$comment_id,
            	"moment_id"=>$moment_id,
            	"reply_name"=>$reply_name,
            	"replyed_name"=> $replyed_name,
            	"comment_val"=>$comment_val); 
	        echo json_encode($list);     
		}  
    }


    //发送朋友圈功能函数
    public function addMoment() {
		$text_box = trim(isset($_POST['text_box']))? htmlspecialchars(trim($_POST['text_box'])) : '';	//获取朋友圈文本内容
		$image_name='';
		$response = array();
		$obj=new SixChatApi2016Controller();
		if(!$text_box && empty($_FILES['upfile']['tmp_name'])){
			echo $_FILES['upfile']['error'];
			echo "没有内容";
			exit;
		}
		$destination_folder="moment_img/"; //上传文件路径   
		$input_file_name = "upfile";
		$maxwidth = 640;
		$maxheight = 1136;
		$upload_result = $obj->uploadImg($destination_folder,$input_file_name,$maxwidth,$maxheight);//调用上传函数
		if($upload_result){	//有图片上传且上传成功返回图片名
			$image_name = $upload_result;
		}
		$user_name=$_SESSION["name"];				
		foreach ($obj->getUserId($user_name, $user_name) as $k=>$val){
			$user_id = $val["reply_id"];

			//插入朋友圈
			$Moment=M("Moment");
	        $data['user_id']=$user_id;
	        $data['info']=$text_box;
	        $data['img_url']=$image_name;
	        $data['time']=date("Y-m-d H:i:s");
	        $Moment->data($data)->add();
		}	

		//获取自己头像
		$map['user_name']=$user_name;
		$avatar=M("User")->where($map)->getField('avatar');

		//获取新增朋友圈的moment_id
		$moment_id=M("Moment")->max('moment_id');

	    $response['isSuccess'] = true;
	    $response['moment_id'] = $moment_id;
	    $response['user_name'] = $user_name;
	    $response['avatar'] = $avatar;
	    $response['text_box'] = $text_box;
	    $response['photo'] = $image_name;
	    $response['time'] = $obj->tranTime(strtotime(date("Y-m-d H:i:s")));
	    echo json_encode($response);
    }

	//选取随机三图做滚动墙纸
    public function getRollingWall(){	
    	$Model = M();
		$sql="select img_url,moment_id from think_moment where img_url <>'' and state=1 order by rand() limit 3";//显示朋友圈信息流
		$list = $Model->query($sql);

		//返回json数据
        $response[]=array(
        	"img_url_1"=>$list[0]['img_url'],
        	"moment_id_1"=>$list[0]['moment_id'],
        	"img_url_2"=>$list[1]['img_url'],
        	"moment_id_2"=>$list[1]['moment_id'],
        	"img_url_3"=>$list[2]['img_url'],
        	"moment_id_3"=>$list[2]['moment_id'],
        ); 
        echo json_encode($response);   
    }

    public function deleteMoment(){
    	$moment_id = htmlspecialchars($_REQUEST['moment_id']);
    	M("Moment")->where("moment_id=$moment_id")->setField('state',0);
    	M("Comment")->where("moment_id=$moment_id")->setField('state',0);//删除moment的时候连带删除其下所有评论
    	$list[0]="Delete moment is success.";
		echo json_encode($list);
    }

    public function deleteComment(){
    	$comment_id = htmlspecialchars($_REQUEST['comment_id']);
    	M("Comment")->where("Comment_id=$comment_id")->setField('state',0);
    	$list[0]="Delete comment is success.";
		echo json_encode($list);
    }

	/*显示赞与评论*/
    public function loadMessages(){
     	$obj=new SixChatApi2016Controller();
		$user_name=$_SESSION["name"];

		$map['user_name']=$user_name;
		$user_id=M("User")->where($map)->getField('user_id');

		// $sql = "SELECT user_name as reply_name,avatar,moment_id,comment,time FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and ((reply_id<>".$user_id." and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=".$user_id." and state=1)) or (replyed_id=".$user_id." and reply_id<>replyed_id)) order by comment_id desc limit 0,20";

		$sql = "SELECT user_name as reply_name,avatar,moment_id,comment,time FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and ((reply_id<>".$user_id." and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=".$user_id.")) or (replyed_id=".$user_id." and reply_id<>replyed_id)) order by comment_id desc limit 0,100";
		$list = M()->query($sql);
		for($i=0;$i<count($list);$i++){
			$list[$i]['time']=$obj->tranTime(strtotime($list[$i]['time']));
		}	

		$sql1 = "UPDATE think_comment SET news=0 WHERE state=1 and news=1 and ((reply_id<>".$user_id." and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=".$user_id.")) or (replyed_id=".$user_id." and reply_id<>replyed_id)) ";
		M()->execute($sql1);

		echo json_encode($list);
    }

    /*查看一条朋友圈*/
    public function getOneMoment(){
    	$moment_id = htmlspecialchars($_POST['moment_id']);
		$my_name = $_SESSION["name"]; 

		$sql="
		SELECT u.user_name,u.avatar,m.info,m.img_url,m.time
			from think_moment m,think_user u
				where m.moment_id=".$moment_id." and m.state=1 and m.user_id=u.user_id";//显示该条朋友圈内容
		$list = M()->query($sql);
		for($i=0;$i<count($list);$i++){
			$list[$i]['my_name'] = $my_name;
			$list[$i]['user_name'] = $list[$i]['user_name'];
			$list[$i]['avatar'] = $list[$i]['avatar'];
			$list[$i]['text_box'] = $list[$i]['info'];
			$list[$i]['photo'] = $list[$i]['img_url'];
			$list[$i]['moment_id'] = $moment_id;
			$list[$i]['time'] = date("M j, Y H:i",strtotime($list[$i]['time']));
			echo json_encode($list);
		}	
    }

    /*查找用户资料*/
    public function searchUser()
    {
    	$search_name=htmlspecialchars($_REQUEST['search_name']);
    	$user_name = $_SESSION['name'];
    	$map['user_name']=$search_name;
		$list=M("User")->where($map)->find();

		$list['is_friend'] = 0;//0代表不是好友关系
		$obj=new SixChatApi2016Controller();
		foreach ($obj->getUserId($user_name, $search_name) as $k=>$val){
			$user_id = $val["reply_id"];
			$friend_id = $val["replyed_id"];
			
			$map1['user_id']=$user_id;
			$map1['friend_id']=$friend_id;
			$result=M("Friend")->where($map1)->find();
			if($result){
				$list['is_friend'] = 1;//是好友关系
			}
		}	
		echo json_encode($list);
    }


    /*好友请求*/
    public function friendRuquest()
    {
    	$requested_name = htmlspecialchars($_REQUEST['requested_name']);
    	$request_name = $_SESSION['name'];
    	$remark = htmlspecialchars($_REQUEST['remark']);

		$obj=new SixChatApi2016Controller();
			foreach ($obj->getUserId($request_name, $requested_name) as $k=>$val){
			$request_id = $val["reply_id"];
			$requested_id = $val["replyed_id"];

			$map['request_id']=$request_id;
			$map['requested_id']=$requested_id;
			$map['state'] = 1;

			$map1['request_id']=$requested_id;
			$map1['requested_id']=$request_id;
			$map1['state'] = 1;

			$result_1=M("Friend_request")->where($map)->select();
			$result_2=M("Friend_request")->where($map1)->select();
			if($result_1 || $result_2){//已存在任意一方的请求则不进行操作
				
			}
			else {
				//插入好友请求
				$Friend_request=M("Friend_request");
		        $data['request_id']=$request_id;
		        $data['requested_id']=$requested_id;
		        $data['remark']=$remark;
		        $data['request_time']=date("Y-m-d H:i:s");
		        $Friend_request->data($data)->add();	
			}
		} 	
		echo json_encode(array("result"=>"ok"));
    }


    public function loadFriendRequest()
    {
     	$obj=new SixChatApi2016Controller();
		$user_name=$_SESSION["name"];
		$map['user_name']=$user_name;
		$user_id=M("User")->where($map)->getField('user_id');    	
    	$map1['requested_id']=$user_id;
    	$map1['state']=1;
		$result=M("Friend_request")->where($map1)->select();
		for($i=0;$i<count($result);$i++){
			$map2['user_id'] = $result[$i]['request_id'];
			$request_name=M("User")->where($map2)->getField('user_name');
			$avatar=M("User")->where($map2)->getField('avatar');
			$result[$i]['request_name'] = $request_name;
			$result[$i]['avatar'] = $avatar;
			$result[$i]['id'] = $result[$i]['id'];
			$result[$i]['remark'] = $result[$i]['remark'];
			$result[$i]['time']=$obj->tranTime(strtotime($result[$i]['request_time']));
		}
		echo json_encode($result);
    }

    /*处理好友请求*/
    public function agreeRequest()
    {
    	$id = htmlspecialchars($_REQUEST['id']);//该好友请求id
    	$request_name = htmlspecialchars($_REQUEST['request_name']);//请求人
    	$requested_name = $_SESSION['name'];//被请求人

    	$map['id'] = $id;
    	M("Friend_request")->where($map)->setField('state',0);

		$obj=new SixChatApi2016Controller();
		foreach ($obj->getUserId($request_name, $requested_name) as $k=>$val){
			$request_id = $val["reply_id"];
			$requested_id = $val["replyed_id"];

			$data['user_id'] = $request_id;
			$data['friend_id'] = $requested_id;
			$data['time'] = date("Y-m-d H:i:s");
			M("Friend")->data($data)->add();	//好友表添加记录
			
			$data1['user_id'] = $requested_id;
			$data1['friend_id'] = $request_id;
			$data1['time'] = date("Y-m-d H:i:s");
			M("Friend")->data($data1)->add();	//双向好友

		}
		echo json_encode(array("result"=>"ok"));
    }

	/*修改资料*/
    public function modifyProfile()
    {
		$profile_name_box = isset($_POST['profile_name_box'])? htmlspecialchars($_POST['profile_name_box']) : '';	//获取文本内容
		$profile_sex_box = isset($_POST['profile_sex_box'])? htmlspecialchars($_POST['profile_sex_box']) : '';
		$profile_region_box = isset($_POST['profile_region_box'])? htmlspecialchars($_POST['profile_region_box']) : '';
		$profile_whatsup_box = isset($_POST['profile_whatsup_box'])? htmlspecialchars($_POST['profile_whatsup_box']) : '';
	
		$obj=new SixChatApi2016Controller();
		if(!$profile_name_box && !$profile_sex_box  && !$profile_region_box && !$profile_whatsup_box && empty($_FILES['profile_upfile']['tmp_name'])){
			echo "没有内容";
			exit;
		}
		$response = array();
		$image_name='';
		$destination_folder="avatar_img/"; //上传文件路径   
		$input_file_name = "profile_upfile";
		$maxwidth = 200;
		$maxheight = 200;
		$upload_result = $obj->uploadImg($destination_folder,$input_file_name,$maxwidth,$maxheight);//上传头像
		if($upload_result){	//有图片上传且上传成功返回图片名
			$image_name = $upload_result;
			//上传成功后进行修改数据库图片路径操作
			$map['user_name']=$_SESSION['name'];
			M("User")->where($map)->setField('avatar',$image_name);
		}
		$user_name=$_SESSION["name"];				
		foreach ($obj->getUserId($user_name, $user_name) as $k=>$val){
			$user_id = $val["reply_id"];
			$map['user_id'] = $user_id;
			$data=array('user_name'=>$profile_name_box,'sex'=>$profile_sex_box,'region'=>$profile_region_box,'whatsup'=>$profile_whatsup_box);
			M("User")->where($map)->setField($data);
		}	
		$_SESSION["name"] = $profile_name_box;
	    $response['isSuccess'] = true;
	    echo json_encode($response);
    }

    /*加载下一页moments*/
    public function loadNextPage()
    {
    	$page = htmlspecialchars($_REQUEST['page']);
    	$obj=new SixChatApi2016Controller();

		// $Model = M();
		// $sql="
		// select u.user_name,u.avatar,m.info,m.img_url,m.time,m.moment_id
		// 	from think_moment m,think_user u
		// 		where m.state=1 and m.user_id=u.user_id
		// 			order by m.time 
		// 				desc limit ".($page*20).",10";//显示朋友圈信息流
		// $list = $Model->query($sql);
		$list = D("Moment")->getNextPage($page);

		for($i=0;$i<count($list);$i++){
			$list[$i]['user_name'] = htmlspecialchars($list[$i]['user_name']);
			$list[$i]['time']=$obj->tranTime(strtotime($list[$i]['time']));
			$list[$i]['info'] = htmlspecialchars($list[$i]['info']);
		}
		echo json_encode($list);
    }

    /*加载未读消息数量*/
    public function loadNews()
    {
     	$obj=new SixChatApi2016Controller();
		$user_name=$_SESSION["name"];
		$map['user_name']=$user_name;
		$user_id=M("User")->where($map)->getField('user_id');
		$sql = "SELECT moment_id FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and news=1 and ((reply_id<>".$user_id." and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=".$user_id.")) or (replyed_id=".$user_id." and reply_id<>replyed_id)) order by comment_id desc limit 0,100";
		$list = M()->query($sql);	
    	$map1['requested_id']=$user_id;
    	$map1['state']=1;
		$result=M("Friend_request")->where($map1)->select();
		$num = count($list)+count($result);
		echo json_encode(array("number"=>$num));
    }

    //注销
    public function logout()
    {
    	$obj=new SixChatApi2016Controller();
    	$obj->logout();
    	header("Location:index");
    }

}