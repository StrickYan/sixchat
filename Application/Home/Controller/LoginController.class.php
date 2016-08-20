<?php 	//登录控制器
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
	public function index(){
		$id=null;
		$temp_id=null;	//存放cookie获取的id
		$temp_password=null;	//存放cookie获取的password
		$password=null;
		$text_placeholder="Name";	
		$pw_placeholder="Password";	
		$head_image="default_head.jpg";	//默认头像
		if (isset($_COOKIE["user"])){	//获取cookie
			$temp_id=$_COOKIE["user"];
		}
		if (isset($_COOKIE["password"])){	//获取cookie
			$temp_password=$_COOKIE["password"];
		}
		$id=trim($_POST['id']);
		if($id==null){		//为空则读入cookie值
			$id=$temp_id;
		}
		$password=trim($_POST['password']);
		if($password==null){	//为空则读入cookie值
			$password=$temp_password;
		}
		if($id!=null && $password!=null){
			$obj=new SixChatApi2016Controller();
			$result=$obj->login($id,$password);
			if($result==-1){	//用户名不存在
				$id=null;
				$password=null;
				$text_placeholder="该用户不存在";
			}
			else if($result==-2){	//密码错误
				$password=null;
				$pw_placeholder="密码错误";
			}
			else if(!$result){	//登录成功
			 	$this->redirect('Moments/index');
				return;
			}
		}
		if($id!=null){	//加载用户头像
			$condition2['user_name'] = $id;
			$avatar = M('User')->where($condition2)->getfield('avatar');
			if($avatar){
				$head_image=$avatar;
			}	
		}

        $array['head_image']=$head_image;
        $array['text_placeholder']=$text_placeholder;
        $array['id']=$id;
        $array['pw_placeholder']=$pw_placeholder;
        $array['password']=$password;
        $this->assign($array);	//模板赋值
        $this->display();	//模板渲染
    }
}