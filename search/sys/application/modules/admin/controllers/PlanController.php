<?php
// IndexController
class Admin_PlanController extends Common_AdminController {
	//初期化メソッドの定義
	private $_db;
	private $_table;
	private $_user;
	private $_pointsetting;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
		}
		$this->_db = new Model_Adminimageuser();

	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　プラン管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"　class="btn disabled"><i class="fa fa-cog"></i>　プラン管理</a> <span class="divider">/</span>
													</li>';
		//GETパラメータを取得
		$kind = $this->_request->getParam("kind");
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = "";
		//SQLコマンドを作成
		$sql = "SELECT DISTINCT u.*
					FROM plan u";
		//検索クエリを作成
		$whereArr = array();
		//検索キーワードが指定された場合
		if(!empty($keyword)){
			$key = mb_convert_kana($keyword,"s","UTF-8");
			$keyArr = split(" ",$key);
			foreach($keyArr as $v){
				$whereArr[] = "concat(u.name, u.description) LIKE '%".$v."%'";
			}
		}
		//WHERE句を生成
		if(!empty($whereArr)){
			$sql.= " WHERE ".implode(" and ",$whereArr);
			$where = implode(" and ",$whereArr);
		}
		//LIMIT句を生成
		$start = $p*$limit;
		$sql.= " ORDER BY `id` DESC";
		$sql.= " LIMIT {$start} , {$limit}";
		//DBから取得
		$sql2 = "SELECT COUNT(*) AS n FROM plan u ".$where;
		//ユーザーリストの読み込み
		$result = $this->_db->fetchAll($sql);
		$result2 = $this->_db->fetchAll($sql2);
		$user =  array($result,$result2[0]['n']);
		$this->view->users = $user[0];
		//ページャーを生成
		$this->userpager($keyword,$user[1],$limit,$p,"/admin/plan/");
	}
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　プラン登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"><i class="fa fa-cog"></i>　プラン管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId("plan",$postArr)){
				//インサート処理後に行う処理を記載
				header("location:".BASEURL."/admin/plan/update/?id=".$lastId."&register=1");
			}
		}
	}
	//編集
	public function updateAction() {
		$postArr = $this->_db->getArray();
		$detail = $this->_db->fetchAll(
			$this->_db->select()
			->from("plan")
			->where("id=?",$postArr['id'])
		);
		//var_dump($detail);
		$this->view->detail = $detail[0];
		if($postArr['update'] ==1){
			$this->view->msg = "更新しました";
		}elseif($postArr['register'] ==1){
			$this->view->msg = "登録しました";
		}
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/plan"><i class="fa fa-cog"></i>　プラン管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/plan/detail/?id='.$postArr['id'].'"><i class="fa fa-cog"></i>詳細</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function updatefinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//DBに登録
			$arr = array();
			foreach($postArr as $k=>$v){
				//idとpwactiveを除外
				if($k == "id"){
					$id = $v;
				}else{
					$arr[$k] = $v;
				}
			}
			$this->_db->update("plan",$arr,$this->_db->quoteInto("`id`=?",$id));
			header("location:".BASEURL."/admin/plan/update?id=".$id."&update=1");
		}
	}

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
