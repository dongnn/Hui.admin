<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\AuthRule;
use app\admin\model\AuthGroup as AuthGroupModel;

/**
 * 角色控制器
 * Class Group
 * @package app\admin\controller
 */
class Group extends Base{

	private $bread = '角色管理';

    /**
     * 角色列表
     * @param Request $request 请求信息
     * @return mixed
     */
    public function lis(Request $request){
		// 查询条件
		$where = [];
		$keywords = $request->post('keywords');
		$this->assign('keywords',$keywords);
		if(isset($keywords)){
			$where = [
				'title' => ['like', '%'.$keywords.'%'],
			];
		}
		$db = new AuthGroupModel();
		$list = $db->where($where)->order('id')->paginate(15);
		$this->assign('list',$list);
		$count = $db->where('status','=',1)->count();

		// 面包屑
		$this->assign('count',$count);
		$this->assign('bread',breadcrumb([$this->bread,'角色列表']));
		return $this->fetch();
	}

    /**
     * 添加角色
     * @param Request $request 请求信息
     * @return mixed|void
     */
	public function add(Request $request){
		if($request->isPost()){
			$db = new AuthGroupModel();
			$data = $request->post();
			// 处理数组数据
			foreach($data as $key => $value){
				if(is_array($value)){
					$data[$key] = implode(",",$value);
				}
			}
        	// 数据验证
            $result = $this->validate($data,'AuthGroup');
            if(true !== $result){
                $this->redirect('Group/add','',302,['code' => 'error','msg' => $result,'data' => $data]);
            }else{
				if($db->allowField(true)->save($data)){
					system_logs('添加角色',session('uname'),1);
					$this->redirect('Group/lis','',302,['code' => 'success','msg' => '角色添加成功！']);
				}else{
					system_logs('添加角色',session('uname'),0);
					$this->redirect('Group/lis','',302,['code' => 'error','msg' => '角色添加失败！']);
				} 
            }
            return;
		}
		// 权限
		$auth_rule = new AuthRule();
		//读取数据
		$list = $auth_rule->where('pid','=',0)->order('id')->select();
		$this->assign('auth_rule',$auth_rule);
		$this->assign('list',$list);
		// 面包屑
		$this->assign('bread',breadcrumb([$this->bread,'添加角色']));
		return $this->fetch();
	}

    /**
     * 编辑角色
     * @param Request $request 请求信息
     * @return mixed|void
     */
	public function edit(Request $request){
		$db = new AuthGroupModel();
		$id = $request->param('id/d');
		if(!empty($id)){
			if($request->isPost()){
				$data = $request->post();
				// 处理数组数据
				foreach($data as $key => $value){
					if(is_array($value)){
						$data[$key] = implode(",",$value);
					}
				}
	        	// 数据验证
	            $result = $this->validate($data,'AuthGroup');
	            if(true !== $result){
	                $this->redirect('Group/edit',['id' => $id],302,['code' => 'error','msg' => $result]);
	            }else{
					if($db->allowField(true)->save($data,['id' => $id])){
						system_logs('编辑角色',session('uname'),1);
						$this->redirect('Group/lis','',302,['code' => 'success','msg' => '角色编辑成功！']);
					}else{
						system_logs('编辑角色',session('uname'),0);
						$this->redirect('Group/edit',['id' => $id],302,['code' => 'error','msg' => '没有更新数据！']);
					} 
	            }
	            return;
			}
			// 权限
			$auth_rule = new AuthRule();
			//读取数据
			$list = $auth_rule->where('pid','=',0)->order('id')->select();
			$this->assign('auth_rule',$auth_rule);
			$this->assign('list',$list);
			
			// 获取全部原始数据
			$det_rs = AuthGroupModel::get($id)->getData();
			$this->assign('rs',$det_rs);
			$rules = explode(",",$det_rs['rules']);
			$this->assign('rules',$rules);
			
			// 面包屑
			$this->assign('bread',breadcrumb([$this->bread,'编辑角色']));
			return $this->fetch();
		}else{
			$this->redirect('Group/lis','',302,['code' => 'error','msg' => '参数错误！']);
		}
	}

    /**
     * 删除角色
     * @param Request $request
     */
	public function del(Request $request){
		if($request->isGet()){
			$id = $request->param('id/d');
			if(isset($id) && !empty($id)){
				$result = AuthGroupModel::get($id);
				if(!$result){
					$this->redirect('Group/lis','',302,['code' => 'error','msg' => '数据不存在！']);
				}else{
					if($result->delete()){
						system_logs('删除角色【'.$result->title.'】',session('uname'),1);
						$this->redirect('Group/lis','',302,['code' => 'success','msg' => '角色【'.$result->title.'】删除成功！']);
					}else{
						system_logs('删除角色【'.$result->title.'】',session('uname'),0);
						$this->redirect('Group/lis','',302,['code' => 'error','msg' => '角色删除失败！']);
					}				
				}
			}else{
				$this->redirect('Group/lis','',302,['code' => 'error','msg' => '参数错误！']);
			}
		}
	}

}
