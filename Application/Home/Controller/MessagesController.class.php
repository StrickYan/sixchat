<?php
namespace Home\Controller;
use Think\Controller;
class MessagesController extends CommonController {
    
	/*显示赞与评论*/
    public function index(){
     	$obj=new SixChatApi2016Controller();
		// session_start();
		 $user_name=$_SESSION["name"];

		$map['user_name']=$user_name;
		$user_id=M("User")->where($map)->getField('user_id');


		$sql = "SELECT user_name as reply_name,avatar,moment_id,comment,time FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and ((reply_id<>".$user_id." and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=".$user_id." and state=1)) or (replyed_id=".$user_id." and reply_id<>replyed_id)) order by comment_id desc limit 0,20";
		//$sql = "SELECT reply_id,moment_id,comment,think_comment.time FROM think_comment where state=1  order by comment_id desc limit 0,20";
		$list = M()->query($sql);
		for($i=0;$i<count($list);$i++){
			$list[$i]['time']=$obj->tranTime(strtotime($list[$i]['time']));
		}	
		$this->assign('list',$list);
       	$this->assign('my_name',$user_name);
        $this->display();
    }


 


}