<?php
define("IMG_DIR","img/");
class Admin_HelpController extends Common_AdminController {
	//初期化メソッドの定義
	private $_db;
	private $_table;
	private $_user;
	private $_stockStatus;
	private $_category;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
		}
		$this->_db = new Model_Adminhelp();
		//ユーザー種別リストの読み込み
		$this->_table ="help";
		$this->_category = $this->_db->fetchAll(
			$this->_db->select()
			->from("help_category")
		);
		$this->view->category = $this->_category;
	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　ヘルプ管理';
		$this->view->bread = '<li>
									<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
									'.$this->view->title.'
								</li>';
		
		
		//GETパラメータを取得
		$postArr = $this->_db->getArray();
		$category = $postArr["category"];
		$keyword = $postArr["keyword"];
		$p = $postArr["p"];
		$limit = $postArr["limit"];
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = "";
		//ユーザーリストの読み込み
		/*
		*/
		$user = $this->_db->getHelp($keyword,$p,$limit,$this->_user->id);
		$this->view->help = $user[0];
		//ページャーを生成
		common::pager($keyword,$user[1],$limit,$p,"/admin/help/");
	}	
	//詳細
	public function detailAction() {
		$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getHelpDetail($postArr['id']);
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'].'の詳細';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　商品管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　ヘルプ登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　ヘルプ管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//会員ラベルの付与と親をログインユーザーにする
			$postArr['parent'] = $this->_user->id;
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId($this->_table,$postArr)){
				//インサート処理後に行う処理を記載
				//$this->view->data = $postArr;
				header("location:".BASEURL."/admin/help/register2/?id=".$lastId);
			}
			/*
			var_dump($postArr);
			*/
		}
	}
	public function register2Action() {
		$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getHelpDetail($postArr['id']);
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>'.$this->view->data['name'].'　ヘルプ登録完了';
		$this->view->bread = '<li>
									<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
									<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　商品管理</a> <span class="divider">/</span>
									<a href="'.BASEURL.'/admin/help/registrer"><i class="fa fa-gift"></i>　商品登録</a> <span class="divider">/</span>
									　'.$this->view->title.'
								</li>';
	}
	//編集
	public function updateAction() {
		$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getHelpDetail($postArr['id']);
		
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'] .'のヘルプ情報編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　ヘルプ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/detail/?id='.$postArr['id'].'"><i class="fa fa-gift"></i>　'.$this->view->data['name'] .'のヘルプ詳細</a> <span class="divider">/</span>
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
			//アップデート処理
			$this->_db->update("help",$arr,$this->_db->quoteInto("`id`=?",$id));
			//詳細を表示
			$this->view->data = $this->_db->getHelpDetail($id);
		}
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'].'ヘルプ情報編集完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　ヘルプ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/detail/?id='.$id.'"><i class="fa fa-gift"></i>　'.$this->view->data['name'] .'のヘルプ詳細</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/update/?id='.$id.'"><i class="fa fa-gift"></i>　'.$this->view->data['name'].'ヘルプ情報編集</a> <span class="divider">/</span>
														　'.$this->view->title.'
													</li>';
	}
	//CSVインポート
	public function importAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　商品CSV一括登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/content"><i class="fa fa-gift"></i>　商品管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		
	}
	//CSVインポート完了
	public function importfinishAction() {
		//タイトルの定義
		$this->view->title = "商品CSV一括登録完了";
		$this->view->title = '<i class="fa fa-gift"></i>　商品CSV一括登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/content"><i class="fa fa-gift"></i>　商品管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/content/import"><i class="fa fa-gift"></i>　商品CSV一括登録</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
			//アップロードされたファイルを読み込み
			if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
				if (move_uploaded_file($_FILES["file"]["tmp_name"], APPLICATION_PATH."/files/" . $_FILES["file"]["name"])) {
					chmod(APPLICATION_PATH."/files/" . $_FILES["file"]["name"], 0644);
					$file_name = APPLICATION_PATH."/files/" . $_FILES["file"]["name"];
					$csv = file($file_name);
					//データ抽出
					$data = array();
					for($i=1;$i<count($csv);$i++){
						$row = explode(",",mb_convert_encoding(rtrim($csv[$i]),"UTF-8","SHIFT_JIS"));
						$data[] = array(
							"name"=>$row[0],
							"price"=>$row[1],
							"cost"=>$row[2],
							"category"=>$row[3],
							"active"=>$row[4],
							"size"=>$row[5],
							"description"=>$row[6],
							"tag"=>$row[7],
							"display"=>$row[8],
							"season"=>str_replace(":",",",$row[9]),
							"stockFlug"=>$row[10],
							"minOfSale"=>$row[11],
							"maxOfSale"=>$row[12],
							"realtimeFlug"=>$row[13],
							"orderToDeli"=>$row[14],
							"endOfSale"=>$row[15],
							"unit"=>$row[16],
							"number"=>$row[17],
							"parent"=>$this->_user->id
						);
					}
					/*
					var_dump($data);
					*/
					$this->view->Msg ="";
					$this->view->Msg.= "<p>".$_FILES["file"]["name"] . "をアップロードしました。</p>";
					$this->view->Msg.= "<ul>";
					//DBに登録
					foreach($data as $v){
						if($this->_db->insert($this->_table,$v)){
							$this->view->Msg.= "<li>".$v['name']."を登録しました</li>";
						}
					}
					$this->view->Msg.= "</ul>";
					$this->view->Msg.="<p><a href=\"".BASEURL."/admin/content/\">商品管理画面へ進む</a></p>";
				} else {
					$this->view->Msg= "ファイルをアップロードできません。";
				}
			} else {
				$this->view->Msg= "ファイルが選択されていません。";
			}
	}
	
	public function uploadAction() {
		if(is_uploaded_file($path = $_FILES['userfile']['tmp_name'])){
			// 調べたい画像のパス
			$mime = shell_exec('file -bi '.escapeshellcmd($path));
			$mime = trim($mime);
			$mime = preg_replace("/ [^ ]*/", "", $mime);
			if($mime == "image/gif"){
				$in = imagecreatefromgif($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".gif";
			}elseif($mime == "image/jpeg"){
				$exif_data = exif_read_data($path);
				$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".jpg";
			}elseif($mime == "image/png"){
				$in = imagecreatefrompng($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".png";
			}else{
				//jpg,gif,png以外のファイルは受付しない
				$this->view->error = "FileTypeError";
			}
			$max = 1920;
			//$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
			$width = imagesx($in); // 画像の幅を取得
			$height = imagesy($in); // 画像の高さを取得
			$min_width = $max; // 幅の最低サイズ
			$min_height = $max; // 高さの最低サイズ
			if($width >$min_width || $height == $min_height){
				if($width == $height) {
					$new_width = $min_width;
					$new_height = $min_height;
				} else if($width > $height) {//横長の場合
					$new_width = $min_width;
					$new_height = $height*($min_width/$width);
				} else if($width < $height) {//縦長の場合
					$new_width = $width*($min_height/$height);
					$new_height = $min_height;
				}
			}else{
				$new_width = $width;
				$new_height = $height;
			}
			//　画像生成
			$out = imagecreatetruecolor($new_width , $new_height);
			if($exif_data["Orientation"] == 6){
				$out = imagerotate($out,90, 0);
			}
			//プレースホルダを作成した画像にコピーして
			imagecopyresampled($out, $in,0,0,0,0, $new_width, $new_height, $width, $height);
			if($mime == "image/gif"){
				imagegif($out,IMG_DIR.$filename);
			}elseif($mime == "image/jpeg"){
				imagejpeg($out,IMG_DIR.$filename);
			}elseif($mime == "image/png"){
				imagepng($out,IMG_DIR.$filename);
			}
			//テンポラリ内の不要になったキャッシュを削除
			unlink($path);
			//登録した画像をDBに保存
			$upload_dir2 = '/img/';
			$this->view->filename = $upload_dir2.$filename;
			$this->view->success = true;
		}else{
			$this->view->error = "aaaaa";
		}
	}
	public function upload2Action() {
		if(is_uploaded_file($path = $_FILES['userfile2']['tmp_name'])){
			// 調べたい画像のパス
			$mime = shell_exec('file -bi '.escapeshellcmd($path));
			$mime = trim($mime);
			$mime = preg_replace("/ [^ ]*/", "", $mime);
			if($mime == "image/gif"){
				$in = imagecreatefromgif($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".gif";
			}elseif($mime == "image/jpeg"){
				$exif_data = exif_read_data($path);
				$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".jpg";
			}elseif($mime == "image/png"){
				$in = imagecreatefrompng($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".png";
			}else{
				//jpg,gif,png以外のファイルは受付しない
				$this->view->error = "FileTypeError";
			}
			$max = 1920;
			//$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
			$width = imagesx($in); // 画像の幅を取得
			$height = imagesy($in); // 画像の高さを取得
			$min_width = $max; // 幅の最低サイズ
			$min_height = $max; // 高さの最低サイズ
			if($width >$min_width || $height == $min_height){
				if($width == $height) {
					$new_width = $min_width;
					$new_height = $min_height;
				} else if($width > $height) {//横長の場合
					$new_width = $min_width;
					$new_height = $height*($min_width/$width);
				} else if($width < $height) {//縦長の場合
					$new_width = $width*($min_height/$height);
					$new_height = $min_height;
				}
			}else{
				$new_width = $width;
				$new_height = $height;
			}
			//　画像生成
			$out = imagecreatetruecolor($new_width , $new_height);
			if($exif_data["Orientation"] == 6){
				$out = imagerotate($out,90, 0);
			}
			//プレースホルダを作成した画像にコピーして
			imagecopyresampled($out, $in,0,0,0,0, $new_width, $new_height, $width, $height);
			if($mime == "image/gif"){
				imagegif($out,IMG_DIR.$filename);
			}elseif($mime == "image/jpeg"){
				imagejpeg($out,IMG_DIR.$filename);
			}elseif($mime == "image/png"){
				imagepng($out,IMG_DIR.$filename);
			}
			//テンポラリ内の不要になったキャッシュを削除
			unlink($path);
			//登録した画像をDBに保存
			$upload_dir2 = '/img/';
			$this->view->filename = $upload_dir2.$filename;
			$this->view->success = true;
		}else{
			$this->view->error = "aaaaa";
		}
	}
	public function deleteimageAction() {
		//画像を削除
		$image = $this->getRequest()->getPost('url');
		$image2 = preg_replace("/(https?|ftp)(:\/\/nanpuku.co.jp\/)/","/home/nanpuku/nanpuku.co.jp/public_html/",$image);
		unlink($image2);
		//データベースから削除
		$id = $this->getRequest()->getPost('id');
		$table = "image";
		$where = "`id`={$id}";
		$this->_db->delete($table,$where);
		$this->view->id = $id;
	}
	public function updatehtmlAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$where= $this->_db->quoteInto('id = ?' , $postArr['id']);
			if($this->_db->update("content",array("addHtml"=>$postArr['addHtml']),$where)){
				$this->view->success = "登録が完了しました。";
			}else{
				$this->view->error = "登録が完了できませんでした。";
			}
			$this->view->data = $this->_db->getcontentDetail($postArr['id']);
		}
	}
	
	
	/////////////////////////商品カテゴリの設定
	//一覧
	public function categoryAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　ヘルプページ一覧';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　ヘルプ管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		
		//GETパラメータを取得
		$postArr = $this->_db->getArray();
		$category = $postArr["category"];
		$keyword = $postArr["keyword"];
		$p = $postArr["p"];
		$limit = $postArr["limit"];
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = "";
		//ユーザーリストの読み込み
		$user = $this->_db->getContentCategory2($keyword,$p,$limit,$this->_user->id);
		
		$this->view->users = $user[0];
		//ページャーを生成
		common::categorypager($keyword,$user[1],$limit,$p,"/admin/help/category");
		/*
		*/
	}
	//詳細
	public function categorydetailAction() {
		$postArr = $this->_db->getArray();
		//$this->view->data = $this->_db->getcontentDetail($postArr['id']);
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
			->from("help_category")
			->where("parent=?",$this->_user->id)
			->where("`id`=?",$postArr['id'])
		);
		$this->view->data =$this->data[0];
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'].'';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　ヘルプ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/category"><i class="fa fa-gift"></i>　ヘルプページ一覧</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集
	public function categoryupdateAction() {
		$postArr = $this->_db->getArray();
		//$this->view->data = $this->_db->getcontentDetail($postArr['id']);
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
			->from("help_category")
			->where("parent=?",$this->_user->id)
			->where("`id`=?",$postArr['id'])
		);
		$this->view->data =$this->data[0];
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'] .'編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　ヘルプ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/category"><i class="fa fa-gift"></i>　ヘルプページ一覧</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/categorydetail/?id='.$postArr['id'].'"><i class="fa fa-gift"></i>　'.$this->view->data['name'] .'</a> <span class="divider">/</span>
														　'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function categoryupdatefinishAction() {
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
			//アップデート処理
			$this->_db->update("help_category",$arr,"`id`=".$id);
			//詳細を表示
			$this->data = $this->_db->fetchAll(
				$this->_db->select()
				->from("help_category")
				->where("parent=?",$this->_user->id)
				->where("`id`=?",$postArr['id'])
			);
			$this->view->data =$this->data[0];
			//$this->view->categorys = $this->_db->getContentCategory();
		}
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'].'編集完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　ヘルプ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/category"><i class="fa fa-gift"></i>　ヘルプページ一覧</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/categorydetail/?id='.$id.'"><i class="fa fa-gift"></i>　'.$this->view->data['name'] .'</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/categoryupdate/?id='.$id.'"><i class="fa fa-gift"></i>　'.$this->view->data['name'].'編集</a> <span class="divider">/</span>
														　'.$this->view->title.'
													</li>';
	}
	//登録
	public function categoryregistrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　ヘルプページ登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>ヘルプ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/category"><i class="fa fa-gift"></i>　ヘルプページ一覧</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
		//$this->view->categorys = $this->_db->getContentCategory();
	}
	//登録完了
	public function categoryregistrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//会員ラベルの付与と親をログインユーザーにする
			$postArr['parent'] = $this->_user->id;
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId("help_category",$postArr)){
				//インサート処理後に行う処理を記載
				header("location:".BASEURL."/admin/help/categoryregister2/?id=".$lastId);
			}
		}
	}
	public function categoryregister2Action() {
		$postArr = $this->_db->getArray();
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
			->from("help_category")
			->where("parent=?",$this->_user->id)
			->where("`id`=?",$postArr['id'])
		);
		$this->view->data =$this->data[0];
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>'.$this->view->data['name'].'登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help"><i class="fa fa-gift"></i>　ヘルプ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/help/registrer"><i class="fa fa-gift"></i>　ヘルプページ登録</a> <span class="divider">/</span>
														　'.$this->view->title.'
													</li>';
	}
}
?>
