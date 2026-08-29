<?php
// IndexController
class Editor_DivisionController extends Common_EditorController {
	//初期化メソッドの定義
	private $_db;
	private $_table;
	private $_user;
	private $_pointsetting;
	private $_setting;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
		}
		$this->_db = new Model_Editordivision();
		$this->_setting = $this->_db->setting($this->_user->parent);
		$this->setting = $this->_setting;
		$this->view->setting = $this->setting;

	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　部門管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/division"　class="btn disabled"><i class="fa fa-cog"></i>　部門管理</a> <span class="divider">/</span>
													</li>';
		//GETパラメータを取得
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		//部門リストの読み込み
		/*
		*/
		$kaijou = $this->_db->getDivision($keyword,$p,$limit,$this->_user->parent);
		foreach($kaijou[0] as $k=>$v){
			foreach($this->pref as $vv){
				if($v['pref'] == $vv['id']){
					$kaijou[0][$k]['prefName'] = $vv['name'];
				}
			}
		}
		$this->view->users = $kaijou[0];
		//ページャーを生成
		$this->userpager($keyword,$kaijou[1],$limit,$p,"/editor/division/");
	}
	//詳細
	public function detailAction() {
		$postArr = $this->_db->getArray();
		$this->view->detail = $this->_db->getDivisionDetail($postArr['id'],$this->_user->parent);
		//スキルを取得
		$this->skil = $this->_db->fetchAll(
			$this->_db->select()
			->from("skil")
			->where("division=?",$postArr['id'])
		);
		$this->view->skil = $this->skil;
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　'.$this->view->detail['name'].'の部門情報';
		$this->view->bread = '<li><a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
								<a href="'.BASEURL.'/editor/division"><i class="fa fa-cog"></i>　部門管理</a> <span class="divider">/</span>
								'.$this->view->title.'
							  </li>';
	}
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　部門登録';
		$this->view->bread = '<li><a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
								<a href="'.BASEURL.'/editor/division"><i class="fa fa-cog"></i>　部門管理</a> <span class="divider">/</span>
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
			//スキルの設定
			$skil = json_decode($postArr['skil'],true);
			//不要データの削除
			unset($postArr['skil']);
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId("division",$postArr)){
				//スキルを登録
				if(!empty($skil)){
					foreach($skil as $v){
						if(!empty($v)){
							$arr = array(
								"name"=>$v,
								"division"=>$lastId
							);
							$this->_db->insert("skil",$arr);
						}
					}
				}
				//2重登録防止の為リダイレクト
				header("location:".BASEURL."/editor/division/update/?id=".$lastId);
			}
		}
	}
	//編集
	public function updateAction() {
		$postArr = $this->_db->getArray();
		$this->view->detail = $this->_db->getDivisionDetail($postArr['id'],$this->_user->parent);
		//スキルを取得
		$this->skil = $this->_db->fetchAll(
			$this->_db->select()
			->from("skil")
			->where("division=?",$postArr['id'])
		);
		$this->view->skil = $this->skil;
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　'.$this->view->detail['name'];
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/division"><i class="fa fa-cog"></i>　部門管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function updatefinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//データの整理
			//スキルの設定
			$skil = json_decode($postArr['skil'],true);
			$skilId = json_decode($postArr['skilId'],true);
			//不要データの削除
			unset($postArr['skil']);
			unset($postArr['skilId']);
			$new = array();
			$old = array();
			foreach($skil as $k=>$v){
				if(array_key_exists($k,$skilId)){
					$old[] = array("id"=>$skilId[$k],"name"=>$v);
				}else{
					if(!empty($v)){
						$new[] = $v;
					}
				}
			}
			$arr = array();
			foreach($postArr as $k=>$v){
				//idを除外
				if($k == "id"){
					$id = $v;
				}else{
					$arr[$k] = $v;
				}
			}
			//DBに登録
			$this->_db->update("division",$arr,"`id`=".$id);
			//スキル更新
			foreach($old as $v){
				if(empty($v['name'])){
					//スキル名が削除された場合は、DBから削除
					$this->_db->delete("skil",$this->_db->quoteInto("`id`=?",$v['id']));
				}else{
					//スキル名が存在する場合は更新
					$this->_db->update("skil",$v,$this->_db->quoteInto("`id`=?",$v['id']));
				}
			}
			foreach($new as $v){
				//新しいものは、スキルを追加
				$this->_db->insert("skil",array("name"=>$v,"division"=>$id));
			}
			header("location:".BASEURL."/editor/division/update?finish=1&id=".$id);
		}
		//タイトルの定義
		$this->view->title = "部門編集完了";
		$this->view->title = '<i class="fa fa-user"></i>　部門編集完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/division"><i class="fa fa-cog"></i>　部門管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/division/detail/?id='.$postArr['id'].'"><i class="fa fa-cog"></i>'.$this->view->data['name'].'の部門登録情報</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function itemAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$this->_db->delete("kaijou_default_item","`kaijou`=".$postArr['id']);
			$items = json_decode(htmlspecialchars_decode($postArr['item']),true);
			var_dump($items);
			/*
			*/

			foreach($items as $v){
				$this->_db->insert(
					"kaijou_default_item",
					array(
						"kaijou"=>$postArr['id'],
						"item"=>$v['data']['id'],
						"name"=>$v['data']['name'],
						"image"=>$v['data']['thumb'],
						"quantity"=>$v['q'],
						"price"=>$v['data']['price'],
						"category"=>$v['data']['category'],
						"parent"=>$this->_user->parent
					)
				);
			}
		}
		header("location:".BASEURL."/editor/division/detail?i=1&id=".$postArr['id']);
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
