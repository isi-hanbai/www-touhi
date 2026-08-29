<?php
// IndexController
class Admin_UserController extends Common_AdminController {
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
		//ユーザー種別リストの読み込み
		$userKind = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUserKind")
		);
		$this->view->userKind = $userKind;
		$this->_table ="ImageUser";

		//都道府県リストを表示
		$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
		$this->view->pref  = $this->pref ;
		//プランを表示
		$this->plan = $this->_db->fetchAll(
			$this->_db->select()
			->from("plan")
			->where("kind=1")
		);
		$this->view->plan  = $this->plan ;
		//消費税設定
		$tax = $this->_db->fetchAll(
			$this->_db->select()
			->from("tax")
			->where("parent=?",$this->_user->id)
		);
		$this->view->tax = $tax;



	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　ユーザー管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"　class="btn disabled"><i class="fa fa-cog"></i>　ユーザー管理</a> <span class="divider">/</span>
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
		//ユーザーリストの読み込み
		$user = $this->_db->getUsers($kind,$keyword,$p,$limit,$this->_user->id);
		foreach($user[0] as $k =>$v){
			$size = $this->dir_size(HOMEDIR."/image/".$v['id']);
			$user[0][$k]['size']=round($size/1000000000*100);
			$user[0][$k]['realsize']=$size;
			//オプションで追加容量がある場合は、最大サイズに追加
			$opt = $this->_db->fetchAll(
			$this->_db->quoteInto(
				"SELECT DISTINCT p.*,
				(
					SELECT sum(oa.score)
					FROM option_acount oa
					WHERE oa.opt = p.id
					AND oa.user = ?
					GROUP BY oa.opt , oa.user
				) AS user
				FROM plan p
				WHERE p.kind=0 AND activate=1",$v['id'])
			);
			foreach($opt as $vv){
				if($vv['user'] !=NULL){
					$user[0][$k]['max_size']+=$vv['size']*1;
				}
			}
		}
		$this->view->users = $user[0];
		//ページャーを生成
		$this->userpager($keyword,$user[1],$limit,$p,"/admin/user/");
		//common::userpager($keyword,$user[1],$limit,$p,"/admin/user/");
	}
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　ユーザー登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"><i class="fa fa-cog"></i>　ユーザー管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//パスワードを暗号化
			if(!empty($postArr['pw'])){
				$postArr['pw'] = $this->_db->pwHash($postArr['pw']);
			}
			//会社を登録
			$postArr['created'] = date("Y-m-d H:i:s");
			$postArr['kind'] = 4;
			$postArr['parent'] = $this->_user->id;
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId($this->_table,$postArr)){
				//ログイン用URL用IDを生成
				if($this->_db->update($this->_table,array("space"=>$this->_db->pwHash($lastId)),$this->_db->quoteInto("id=?",$lastId))){
					//ファイル保存用のディレクトリを追加
					mkdir(HOMEDIR."/image/".$lastId);
					//特権管理者を生成する
					$postArr['parent'] = $lastId;
					$postArr['kind'] = 2;
					$postArr['Authority'] = 1;
					if($Id = $this->_db->insertAndGetLastId($this->_table,$postArr)){
						//会社情報を登録
						$arr = array(
							"company"=>$postArr['company'],
							"tel"=>$postArr['tel']."-".$postArr['tel2']."-".$postArr['tel3'],
							"mail"=>$postArr['mail'],
							"zip"=>$postArr['zip']."-".$postArr['zip2'],
							"pref"=>$postArr['pref'],
							"addr"=>$postArr['addr'],
							"addr2"=>$postArr['addr2'],
							"parent"=>$lastId
						);
						//アクティビティログに有効化データを挿入
						$this->_db->insert(
							"outsourceActivateLog",
							array(
								"merchant"=>$lastId,
								"user"=>$Id,
								"date"=>date("Y-m-d H:i:s"),
								"outsource"=>0,
								"score"=>1
							)
						);
						if($Id = $this->_db->insertAndGetLastId("company",$arr)){
							header("location:".BASEURL."/admin/user/update/?id=".$lastId."&register=1");
						}
					}
				}
			}
		}
	}
	//編集
	public function updateAction() {
		$postArr = $this->_db->getArray();
		$detail= $this->_db->getUserDetail($postArr['id']);
		$size = $this->dir_size(HOMEDIR."/image/".$detail['id']);
		$detail['size']=round($size/1000000000*100);
		$detail['realsize']=$size;
		$opt = $this->_db->fetchAll(
		$this->_db->quoteInto(
			"SELECT DISTINCT p.*,
			(
				SELECT sum(oa.score)
				FROM option_acount oa
				WHERE oa.opt = p.id
				AND oa.user = ?
				GROUP BY oa.opt , oa.user
			) AS user
			FROM plan p
			WHERE p.kind=0 AND activate=1",$detail['id'])
		);
		foreach($opt as $vv){
			if($vv['user'] !=NULL){
				$detail['max_size']+=$vv['size']*1;
			}
		}
		$this->view->detail = $detail;
		$this->view->opt = $opt;


		if($postArr['update'] ==1){
			$this->view->msg = "更新しました";
		}elseif($postArr['register'] ==1){
			$this->view->msg = "登録しました";
		}
		//特権管理者のIDを取得
		$special = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUser")
			->where("parent=?",$this->view->detail['id'])
			->where("Authority=1")
		);
		$this->view->special = $special[0];

		//請求情報を取得
		$order = $this->_db->fetchAll(
			$this->_db->select()
			->from("order")
			->where("member_no=?",$postArr['id'])
			->order("CONCAT(`year`,`month`) DESC")
		);
		$this->view->order = $order;

		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"><i class="fa fa-cog"></i>　ユーザー管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user/detail/?id='.$postArr['id'].'"><i class="fa fa-cog"></i>詳細</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function updatefinishAction() {
		//タイトルの定義
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
				}elseif($k == "parent"){
					$parent = $v;
				}elseif($k == "pwactive"){
				}else{
					$arr[$k] = $v;
				}
			}
			$arr['space'] = $this->_db->pwHash($id);
			$this->_db->update("ImageUser",$arr,$this->_db->quoteInto("`id`=?",$id));
			if($parent){
				$id = $parent;
			}
			header("location:".BASEURL."/admin/user/update?id=".$id."&update=1");
		}
	}
	//CSVインポート
	public function importAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　一括登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"><i class="fa fa-cog"></i>　ユーザー管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user/import"　class="btn disabled">'.$this->view->title.'</a>
													</li>';

	}
	//CSVインポート完了
	public function importfinishAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　一括登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"><i class="fa fa-cog"></i>　ユーザー管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user/import">ユーザーCSV一括登録</a>
														<a href="'.BASEURL.'/admin/user/importfinish"　class="btn disabled">'.$this->view->title.'</a>
													</li>';
			//アップロードされたファイルを読み込み
			if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
				if (move_uploaded_file($_FILES["file"]["tmp_name"], APPLICATION_PATH."/files/" . $_FILES["file"]["name"])) {
					chmod(APPLICATION_PATH."/files/" . $_FILES["file"]["name"], 0644);
					echo $_FILES["file"]["name"] . "をアップロードしました。";
					$file_name = APPLICATION_PATH."/files/" . $_FILES["file"]["name"];
					$csv = file($file_name);
					//データ抽出
					$data = array();
					for($i=0;$i<count($csv);$i++){
						$row = explode(",",mb_convert_encoding(rtrim($csv[$i]),"UTF-8","SHIFT_JIS"));
						$data[] = array(
							"company"=>$row[0],
							"division"=>$row[1],
							"name"=>$row[2],
							"kana"=>$row[3],
							"mail"=>$row[4],
							"pw"=>$this->_db->pwHash($row[5]),
							"tel"=>$row[6],
							"fax"=>$row[7],
							"zip"=>$row[8],
							"pref"=>$row[9],
							"addr"=>$row[10],
							"addr2"=>$row[11],
							"kind"=>3,
							"active"=>1,
							"parent"=>$this->_user->id
						);
					}
					$this->view->Msg ="";
					//DBに登録
					foreach($data as $v){
						if($this->_db->insert($this->_table,$v)){
							$this->view->Msg.= $v['name']."を登録しました<br>";
						}
					}
					$this->view->Msg ="<a href=\"".BASEURL."/admin/user/\">ユーザー管理画面へ進む</a>";
				} else {
					$this->view->Msg = "ファイルをアップロードできません。";
				}
			} else {
				$this->view->Msg = "ファイルが選択されていません。";
			}
	}
	public function pointaddAction(){
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　ポイント追加';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"><i class="fa fa-cog"></i>　ユーザー管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user/pointadd"　class="btn disabled">'.$this->view->title.'</a>
													</li>';

		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			if($this->_db->insert(
				"cutomerPoint",
				array(
					"customerId"=>$postArr['id'],
					"created"=>date("Y-m-d H:i:s"),
					"pin"=>$postArr['point'],
					"parent"=>$this->_user->id,
					"limit_date"=>date("Y-m-d H:i:s",time()+60*60*24*$this->_pointsetting[0]['pointlimit'])
				)
			)){
				$this->view->Msg = "ポイントの追加を行いました。";
			}
			$this->view->data = $this->_db->getUserDetail($postArr['id']);
		}
	}
	public function pointdeleteAction(){
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　ポイント履歴削除';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user"><i class="fa fa-cog"></i>　ユーザー管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/user/pointdelete"　class="btn disabled">'.$this->view->title.'</a>
													</li>';

		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			if($this->_db->delete(
				"cutomerPoint",
				$this->_db->quoteInto("`id`=?",$postArr['id'])
			)){
				$this->view->Msg = "履歴の削除を行いました。";
			}
			$this->view->data = $this->_db->getUserDetail($postArr['userId']);
		}
	}
	public function postcardAction(){
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$postArr['ids'] = json_decode(htmlspecialchars_decode($postArr['ids']),true);
			$sql = "SELECT * FROM ImageUser";
			$whereArr  =array();
			foreach($postArr['ids'] as $v){
				$whereArr[] = $this->_db->quoteInto("`id`=?",$v);
			}
			$sql.= " WHERE ".implode(" OR ",$whereArr);
			//$sql.=$this->_db->quoteInto(" AND parent=?",$this->_user->id);
			$result = $this->_db->fetchAll($sql);

			$arr = array();
			foreach($result as $v){
				$zipArr = str_split(str_replace("-","",str_replace("ー","-",$v['zip'])));
				foreach($this->pref as $vg){
					if($vg['id'] == $v['pref']){
						$pref = $vg['name'];
					}
				}
				$arr[] = array(
					"z1"=>$zipArr[0],
					"z2"=>$zipArr[1],
					"z3"=>$zipArr[2],
					"z4"=>$zipArr[3],
					"z5"=>$zipArr[4],
					"z6"=>$zipArr[5],
					"z7"=>$zipArr[6],
					"div"=>$v['div'],
					"company"=>$v['company'],
					"name"=>$v['name'],
					"addr"=>$pref.str_replace("-","ー",$v['addr']),
					"addr2"=>$v['addr2']
				);
			}
			$this->view->data = $arr;
		 	//var_dump($result);
		}
	}

	/*
	*/
	//墓情報
	//登録完了
	public function hakaregistrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId("haka",$postArr)){
				//インサート処理後に行う処理を記載
				header("location:".BASEURL."/admin/user/detail/?id=".$postArr['mem_id']."&insert");
			}
		}
	}
	//編集完了
	public function hakaupdatefinishAction() {
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
			$this->_db->update("haka",$arr,"`id`=".$id);
			header("location:".BASEURL."/admin/user/detail/?id=".$postArr['mem_id']."&update");
		}
	}
	private function dir_size($dir){
		$handle = opendir($dir);
		while ($file = readdir($handle)) {
			if ($file != '..' && $file != '.' && !is_dir($dir.'/'.$file)) {
				$mas += filesize($dir.'/'.$file);
			} else if (is_dir($dir.'/'.$file) && $file != '..' && $file != '.') {
				$mas += $this->dir_size($dir.'/'.$file);
			}
		}
		return $mas;
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
