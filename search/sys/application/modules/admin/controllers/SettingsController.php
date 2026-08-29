<?php
define("IMG_DIR","img/");
class Admin_SettingsController extends Common_AdminController {
	//初期化メソッドの定義
	private $_db;
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
		$this->_db = new Model_Adminsettings();
	}
	//各種設定一覧
	public function indexAction() {
		$this->view->title = '<i class="fa fa-cog"></i>　各種設定';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"　class="btn disabled"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
													</li>';

	}
	//決済方法設定
	public function paymentAction() {
		$this->view->title = '<i class="fa fa-credit-card-alt"></i>　決済方法設定';
		$table = "payment_method";
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		
		//編集ボタンが押された場合
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//新規登録
			if($postArr['mode'] == "insert"){
				unset($postArr['mode']);
				foreach($postArr as $k=>$v){
					if($v == ""){
						unset($postArr[$k]);
					}
				}
				$postArr['parent'] = $this->_user->id;
				if($this->_db->insert($table,$postArr)){
					$this->view->Msg = "決済方法の登録を行いました。";
				}
			}else{
				$id = $postArr['id'];
				unset($postArr['id']);
				unset($postArr['mode']);
				foreach($postArr as $k=>$v){
					if($v == ""){
						unset($postArr[$k]);
					}
				}
				if($this->_db->update($table,$postArr,$this->_db->quoteInto("`id`=?",$id))){
					$this->view->Msg2 = $postArr['name']."の編集を行いました。";
				}
			}
		}
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
				->from($table)
				->where("parent=?",$this->_user->id)
		);
		$this->view->data = $this->data;
	}
	//プラン設定
	public function planAction() {
		$this->view->title = '<i class="fa fa-credit-card-alt"></i>　プラン設定';
		$table = "plan";
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		
		//編集ボタンが押された場合
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//新規登録
			if($postArr['mode'] == "insert"){
				unset($postArr['mode']);
				foreach($postArr as $k=>$v){
					if($v == ""){
						unset($postArr[$k]);
					}
				}
				if($this->_db->insert($table,$postArr)){
					$this->view->Msg = "プランの登録を行いました。";
				}
			}else{
				$id = $postArr['id'];
				unset($postArr['id']);
				unset($postArr['mode']);
				foreach($postArr as $k=>$v){
					if($v == ""){
						unset($postArr[$k]);
					}
				}
				if($this->_db->update($table,$postArr,$this->_db->quoteInto("`id`=?",$id))){
					$this->view->Msg2 = $postArr['name']."の編集を行いました。";
				}
			}
		}
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
				->from($table)
		);
		$this->view->data = $this->data;
	}
	//会社設定
	public function companyAction() {
		$this->view->title = '<i class="fa fa-building"></i>　会社設定';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->settings("company",$this->view->title);
		$this->view->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
	}
	//サイト設定
	public function globalAction() {
		$this->view->title = '<i class="fa fa-desktop"></i>　サイト設定';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->settings("global",$this->view->title);
	}
	//ポイント設定
	public function pointAction() {
		$this->view->title = '<i class="fa fa-star"></i>　ポイント設定';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->settings("point",$this->view->title);
	}
	//配達日時設定
	public function deliveryAction() {
		$this->view->title = '<i class="fa fa-truck"></i>　配達日時設定';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->settings("delivery",$this->view->title);
	}
	//割引・特別料金設定
	public function discountAction() {
		$this->view->title = '<i class="fa fa-money"></i>　割引・特別料金設定';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->settings("discount",$this->view->title);
	}
	//消費税設定
	public function taxAction() {
		$this->view->title = '<i class="fa fa-flag"></i>　消費税設定';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->settings("tax",$this->view->title);
	}
	//送料設定
	public function shippingAction() {
		$this->view->pref =$this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
		$this->view->title = '<i class="fa fa-paper-plane"></i>　送料設定';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		
		$table = "shipping";
		//編集ボタンが押された場合
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//新規登録
			if($postArr['mode'] == "insert"){
				unset($postArr['mode']);
				foreach($postArr as $k=>$v){
					if($v == ""){
						unset($postArr[$k]);
					}
				}
				$postArr['parent'] = $this->_user->id;
				if($this->_db->insert($table,$postArr)){
					$this->view->Msg = "決済方法の登録を行いました。";
				}
			}else{
				$id = $postArr['id'];
				unset($postArr['id']);
				unset($postArr['mode']);
				foreach($postArr as $k=>$v){
					if($v == ""){
						unset($postArr[$k]);
					}
				}
				if($this->_db->update($table,$postArr,$this->_db->quoteInto("`id`=?",$id))){
					$this->view->Msg2 = $postArr['name']."の編集を行いました。";
				}
			}
		}
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
				->from($table)
				->where("parent=?",$this->_user->id)
		);
		$this->view->data = $this->data;
	}
	//各種メール設定
	public function mailAction() {
		$this->view->title = '<i class="fa fa-envelope-o"></i>　各種メール設定';
		$table = "mail_template";
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/settings"><i class="fa fa-cog"></i>　各種設定</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$postArr['parent'] = $this->_user->id;
			
			//データが登録されているか確認
			$this->data = $this->_db->fetchAll(
				$this->_db->select()
					->from($table,"count(*)")
					->where("parent=?",$this->_user->id)
					->where("name=?",$postArr['name'] )
			);
			$edit = false;
			if($this->data[0]["count(*)"] > 0){
				$where = array();
				$where[] = $this->_db->quoteInto("parent=?",$this->_user->id);
				$where[] = $this->_db->quoteInto("name=?",$postArr['name']);
				$str = implode(" AND ",$where);
				$this->_db->update($table,$postArr,$str);
				$edit = true;
			}else{
				$this->_db->insert($table,$postArr);
				$edit = true;
			}
			if($edit == true){
				$this->view->Msg = "メールテンプレートを編集しました。";
			}
		}
		//メールデータの取得
		$result = $this->_db->fetchAll(
			$this->_db->select()
			->from($table)
			->where("parent=?",$this->_user->id)
		);
		$mail = array();
		foreach($result as $v){
			$mail[$v['name']] =$v; 
		}
		$this->view->mail = $mail;
	}
	public function uploadAction() {
		if(is_uploaded_file($path = $_FILES['userfile']['tmp_name'])){
			// 調べたい画像のパス
			//getimagesize関数で画像情報を取得する
			list($img_width, $img_height, $mime_type, $attr) = getimagesize($path);
			//list関数の第3引数にはgetimagesize関数で取得した画像のMIMEタイプが格納されているので条件分岐で拡張子を決定する
			switch($mime_type){
				//jpegの場合
				case IMAGETYPE_JPEG:
					//拡張子の設定
					$img_extension = "jpg";
				break;
				//pngの場合
				case IMAGETYPE_PNG:
					//拡張子の設定
					$img_extension = "png";
				break;
				//gifの場合
				case IMAGETYPE_GIF:
					//拡張子の設定
					$img_extension = "gif";
				break;
			}
			$this->view->type = $img_extension;
			if($img_extension == "gif"){
				$in = imagecreatefromgif($path); // 元画像ファイル読み込み
				$filename = $this->getRequest()->getPost('id')."item_".date("YmdHis").".gif";
			}elseif($img_extension == "jpg"){
				$exif_data = exif_read_data($path);
				$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
				$filename = $this->getRequest()->getPost('id')."item_".date("YmdHis").".jpg";
			}elseif($img_extension == "png"){
				$in = imagecreatefrompng($path); // 元画像ファイル読み込み
				$filename = $this->getRequest()->getPost('id')."item_".date("YmdHis").".png";
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
			if($img_extension == "gif"){
				if(imagegif($out,IMG_DIR.$filename) ==false){
					exit();
				}
			}elseif($img_extension == "jpg"){
				if(imagejpeg($out,IMG_DIR.$filename) ==false){
					exit();
				}
			}elseif($img_extension == "png"){
				if(imagepng($out,IMG_DIR.$filename) ==false){
					exit();
				}
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
				$filename = $this->getRequest()->getPost('id')."_".date("YmdHis").".gif";
			}elseif($mime == "image/jpeg"){
				$exif_data = exif_read_data($path);
				$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
				$filename = $this->getRequest()->getPost('id')."_".date("YmdHis").".jpg";
			}elseif($mime == "image/png"){
				$in = imagecreatefrompng($path); // 元画像ファイル読み込み
				$filename = $this->getRequest()->getPost('id')."_".date("YmdHis").".png";
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
	
	
	//// 設定メニュー共通関数
	private function settings($table,$ja){
		//編集ボタンが押された場合
		if($this->_request->isPost()){
			//データが登録されているか確認
			$this->data = $this->_db->fetchAll(
				$this->_db->select()
					->from($table,"count(*)")
					->where("parent=?",$this->_user->id)
			);
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			
			
			if($this->_db->insertUpdate($table,$postArr,$this->_user->id,$this->data[0]["count(*)"])){
				$this->view->Msg = $ja."の更新を行いました。";
			}
		}
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
				->from($table)
				->where("parent=?",$this->_user->id)
		);
		$this->view->data = $this->data[0];
	}
}
?>
