<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Doc as DocModel;

class Doc extends Base{

	private $bread = '文档属性';

	public function lis(){
		$db = new DocModel();
		$list = $db->order('sorting ASC')->paginate(10);
		$this->assign('list',$list);
		$count = $db->count();
		$this->assign('count',$count);

		// 面包屑
		$this->assign('bread',breadcrumb([$this->bread,'属性列表']));
		return $this->fetch();
	}

	// 添加属性
	public function add(Request $request){
		if($request->isPost()){
			$db = new DocModel();
			$data = $request->post();
        	// 数据验证
            $result = $this->validate($data,'Doc');
            if(true !== $result){
                $this->redirect('doc/add','',302,['code' => 'error','msg' => $result,'data' => $data]);
            }else{
				if($db->allowField(true)->save($data)){
					system_logs('文档属性添加',session('uname'),1);
					$this->redirect('doc/lis','',302,['code' => 'success','msg' => '文档属性添加成功！']);
				}else{
					system_logs('文档属性添加',session('uname'),0);
					$this->redirect('doc/lis','',302,['code' => 'error','msg' => '文档属性添加失败！']);
				}
            }
            return;
		}
		$this->assign('bread',breadcrumb([$this->bread,'添加属性']));
		return $this->fetch();
	}

	// 编辑属性
	public function edit(Request $request){
		$id = $request->param('id/d');
		if(empty($id)){
            $this->redirect('doc/lis','',302,['code' => 'error','msg' => '缺少参数！']);
            return;
		}
		if($request->isPost()){
			$db = new DocModel();
			$data = $request->post();
        	// 数据验证
            $result = $this->validate($data,'Doc');
            if(true !== $result){
                $this->redirect('doc/edit',['id'=>$id],302,['code' => 'error','msg' => $result]);
            }else{
				if($db->save($data,['id' => $data['id']])){
					system_logs('文档属性编辑',session('uname'),1);
					$this->redirect('doc/lis','',302,['code' => 'success','msg' => '文档属性编辑成功！']);
				}else{
					system_logs('文档属性编辑',session('uname'),0);
					$this->redirect('doc/lis','',302,['code' => 'error','msg' => '文档属性编辑失败！']);
				}
            }
            return;
		}
		// 获取全部原始数据
		$det_rs = DocModel::get($id)->getData();
		$this->assign('rs',$det_rs);
		$this->assign('bread',breadcrumb([$this->bread,'编辑属性']));
		return $this->fetch();
	}

	// 删除属性
	public function del(Request $request){
		if($request->isGet()){
			$id = $request->param('id/d');
			if(!empty($id)){
				$db = DocModel::get($id);
				if($db){
					if($db->delete()){
						system_logs('文档属性删除',session('uname'),1);
						$this->redirect('doc/lis','',302,['code' => 'success','msg' => '文档属性删除成功！']);
					}else{
						system_logs('文档属性删除',session('uname'),0);
						$this->redirect('doc/lis','',302,['code' => 'error','msg' => '文档属性删除失败！']);
					}
				}else{
					$this->redirect('doc/lis','',302,['code' => 'error','msg' => '数据不存在！']);
				}

			}else{
				$this->redirect('doc/lis','',302,['code' => 'error','msg' => '参数错误！']);
			}
		}else{
			$this->redirect('doc/lis','',302,['code' => 'error','msg' => '请您正常操作！']);
		}
	}

	/**
	 * 文档属性状态
	 * @Author   Hui
	 * @DateTime 2017-07-02T23:12:19+0800
	 * @param    Request $request
	 * @return   
	 */
	public function docStatus(Request $request){
		if($request->isGet()){
			$id = $request->param('id/d');
			$status = $request->param('status/d');
			if(!isset($id) || !isset($status)){
				$this->redirect('doc/lis','',302,['code' => 'error','msg' => '参数错误！']);
			}else{
				$doc = DocModel::get($id);
				if($doc){
					$data['status'] = $status == 0 ? 1:0;
					$msg = $status == 0 ? '启用':'禁用';
					if($doc->save($data)) {
						system_logs('文档属性状态设置'.$msg,session('uname'),1);
						$this->redirect('doc/lis','',302,['code' => 'success','msg' => "文档属性{$msg}成功！"]);
					}else{
						system_logs('文档属性状态设置'.$msg,session('uname'),0);
						$this->redirect('doc/lis','',302,['code' => 'error','msg' => "文档属性{$msg}失败！"]);
					}
				}else{
					$this->redirect('doc/lis','',302,['code' => 'error','msg' => '数据不存在！']);
				}
			}
		}
	}

}
