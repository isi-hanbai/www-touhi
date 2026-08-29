<?php
class Editor_UserclassController extends Common_EditorController {
	//初期化メソッドの定義
	private $_db;
	private $_table;
	private $_user;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
		}
		$this->_db = new Model_Editorimageuser();
		$this->_table ="userClassification";

		$this->view->Auth = $this->_user->Authority;
		$this->view->outsource = $this->_user->outsource;
	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　会員分類管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/userclass"　class="btn disabled"><i class="fa fa-cog"></i>　会員分類管理</a> <span class="divider">/</span>
													</li>';
		//GETパラメータを取得
		$kind = 3;
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = "";
		//会員リストの読み込み
		$user = $this->_db->getUsersClasses($keyword,$p,$limit,$this->_user->parent);
		$this->view->users = $user[0];
		//ページャーを生成
		$this->userpager($keyword,$user[1],$limit,$p,"/editor/userclass/");
		//common::userpager($keyword,$user[1],$limit,$p,"/merchant/user/");
	}
	//詳細
	public function detailAction() {
    $this->view->data = $this->_db->getUserDetail($this->_request->getParam("id"));
		//$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getUserDetail($this->_request->getParam("id"));
		if($postArr['reg']){
			$this->view->msg = "登録しました。";
		}elseif($postArr['update']){
			$this->view->msg = "更新しました。";
		}
		//タイトルの定義
		$this->view->reg = $postArr['reg'];
		$this->view->update = $postArr['update'];
		$this->view->title = '<i class="fa fa-user"></i>　'.$this->view->data['name'];
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/userclass"><i class="fa fa-cog"></i>　会員分類管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//登録
	public function registrerAction() {
		$this->view->comData = $this->_user->comData;
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　会員分類登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/userclass"><i class="fa fa-cog"></i>　会員分類管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$postArr['parent'] = $this->_user->parent;
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId($this->_table,$postArr)){
				//インサート処理後に行う処理を記載
				header("location:".BASEURL."/editor/userclass/update/?reg=ture&id=".$lastId);
			}
		}
	}
	//編集
	public function updateAction() {
    if($this->_request->getParam("reg")){
      $this->view->msg = "登録しました。";
      $this->view->reg = 1;
    }elseif($this->_request->getParam("update")){
      $this->view->msg = "更新しました。";
      $this->view->update = 1;
    }
		//$postArr = $this->_db->getArray();
		$this->view->detail = $this->_db->getUsersClassesDetail($this->_request->getParam("id"),$this->_user->parent);
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/userclass"><i class="fa fa-cog"></i>　会員分類管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/userclass/detail/?id='.$postArr['id'].'"><i class="fa fa-cog"></i>'.$this->view->detail['name'].'</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function updatefinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//パスワードを暗号化
			if($postArr['pwactive'] ){
				$postArr['pw'] = $this->_db->pwHash($postArr['pw']);
			}
			//DBに登録
			$arr = array();
			foreach($postArr as $k=>$v){
				//idとpwactiveを除外
				if($k == "id"){
					$id = $v;
				}elseif($k == "pwactive"){
				}else{
					$arr[$k] = $v;
				}
			}
			$this->_db->update($this->_table,$arr,"`id`=".$id);
			//$this->view->data = $this->_db->getUserDetail($this->_request->getPost("id"));

			header("location:".BASEURL."/editor/userclass/update/?update=ture&id=".$id);
		}
	}
	
	
	
	
	
	
	//会員用ページャーの生成
	private function userpager($keyword="",$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination pagination-sm\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\">
<span aria-hidden=\"true\">&laquo; 前へ</span>
</a>
</li>";
			}
			if($p > 2){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=0\">1</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+2 || $i < $p-2){
				}else{
					if($i == $p){
						$avtive = " class=\"active\"";
					}else{
						$avtive = "";
					}
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li>
<a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$next."\" aria-label=\"Next\">
<span aria-hidden=\"true\">次&raquo;</span>
</a>
</li>";
			}
			$pager.= "</ul></div>";
			$this->view->pager = $pager;
		}
	}
}
?>
