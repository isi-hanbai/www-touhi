<?php
define("IMG_DIR","img/");
class Admin_OrderController extends Common_AdminController {
	//初期化メソッドの定義
	private $_db;
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
		$this->_db = new Model_Adminorder();
		//ユーザー種別リストの読み込み

		$this->view->user = $this->_user;
		//各種設定を読み込み
		$this->_setting = $this->_db->setting($this->_user->id);
		$this->setting = $this->_setting;
		$this->view->setting = $this->setting;
		//都道府県一覧取得
		$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);

		$this->view->pref = $this->pref;

	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';


		//GETパラメータを取得
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		//性別
		if($this->_request->getParam("sex")){
			$sex = $this->_request->getParam("sex");
		}else{
			$sex = 0;
		}
		//年代
		if($this->_request->getParam("gene")){
			$gene = $this->_request->getParam("gene");
		}else{
			$gene = 0;
		}
		//生月
		if($this->_request->getParam("birthM")){
			$birthM = $this->_request->getParam("birthM");
		}else{
			$birthM = 0;
		}
		//会員
		if($this->_request->getParam("membership")){
			$membership = $this->_request->getParam("membership");
		}else{
			$membership = 0;
		}
		//商品
		if($this->_request->getParam("item")){
			$item = $this->_request->getParam("item");
		}else{
			$item = 0;
		}
		//ポイント有効期限
		if($this->_request->getParam("point")){
			$point = $this->_request->getParam("point");
		}else{
			$point = 0;
		}






		if($this->_request->getParam("resive")){
			$resive = $this->_request->getParam("resive");
		}else{
			$resive = 0;
		}
		if($this->_request->getParam("pay")){
			$pay = $this->_request->getParam("pay");
		}else{
			$pay = 0;
		}
		if($this->_request->getParam("shipping")){
			$shipping = $this->_request->getParam("shipping");
		}else{
			$shipping = 0;
		}
		if($this->_request->getParam("3dn")){
			$n3d = $this->_request->getParam("3dn");
		}else{
			$n3d = 0;
		}
		if($this->_request->getParam("1mn")){
			$n1m = $this->_request->getParam("1mn");
		}else{
			$n1m = 0;
		}
		if($this->_request->getParam("2mn")){
			$n2m = $this->_request->getParam("2mn");
		}else{
			$n2m = 0;
		}
		if($this->_request->getParam("3mn")){
			$n3m = $this->_request->getParam("3mn");
		}else{
			$n3m = 0;
		}
		if($this->_request->getParam("6mn")){
			$n6m = $this->_request->getParam("6mn");
		}else{
			$n6m = 0;
		}
		//var_dump($_GET);

		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = "";
		//受注リストの読み込み
		$user = $this->_db->getorders($keyword,$p,$limit,$this->_user->id,$resive,$pay,$shipping,$n3d,$n1m,$n2m,$n3m,$n6m,$sex,$birthM,$item,$membership,$point,$gene);

		//メールアドレスが重複する数を取得
		foreach($user[0] as $k=>$v){
			$c = $this->_db->fetchAll(
				$this->_db->select()
				->from("order",array("count(*) AS c"))
				->where("cus_mail=?",$v['cus_mail'])
			);
			if($c[0]["c"] >1){
				$user[0][$k]["c"] = $c[0]["c"];
			}
		}
		$this->view->users = $user[0];
		//ページャーを生成
		$this->orderpager($keyword,$user[1],$limit,$p,"/admin/order/",$sex,$birthM,$membership,$item,$point,$resive,$pay,$shipping,$n3d,$n1m,$n2m,$n3m,$n6m,$gene);

		//商品一覧
		$items = $this->_db->fetchAll(
			$this->_db->select()
			->from("item",array("id","name"))
		);
		array_unshift($items,array("id"=>"","name"=>"指定なし"));
		$this->view->items = $items;

	}
	//詳細
	public function detailAction() {
		//POSTデータのエスケープ処理
		$postArr = $this->_db->getArray();
		$this->data = $this->_db->getorderDetail($postArr['id'],$this->_user->id);

		$c = $this->_db->fetchAll(
			$this->_db->select()
			->from("order",array("count(*) AS c"))
			->where("cus_mail=?",$this->data['cus_mail'])
		);
		$this->data['c'] = $c[0]['c'];
		$this->view->data = $this->data;
		//受注された商品データ
		$this->item = $this->_db->getorderByItems($postArr['id'],$this->_user->id);
		$this->view->item = $this->item;
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['order_id'].'の受注詳細';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		//進捗データ更新時のアラート
		if($postArr['step']){
			$this->view->MsgStep = "進捗データの編集が完了しました。";
		}

		//メールデータの取得
		$result = $this->_db->fetchAll(
			$this->_db->select()
			->from("mail_template")
			->where("parent=?",$this->_user->id)
		);
		$mail = array();
		$detailStr = "ーーーーーーーーーーーー\n";;
		$detailStr.= "ご受注商品情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($this->item as $v){
			$taxRatio = 1/100*$this->setting['tax']['ratio'];
			$price = $v['price'];
			$detailStr.= $v['name']."　".number_format($price)."円×".$v['quantity'].$v['unit']."=".number_format($price*$v['quantity'])."円\n";
		}
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "小計：".number_format($this->data['item_total'])."円\n";
		$detailStr.= "内消費税：".number_format($this->data['tax_total'])."円\n";
		$detailStr.= "送料：".number_format($this->data['shipping_cost'])."円\n";
		if($this->setting['point']['pointuse'] ==0 && $this->data['use_point']){
			$detailStr.= "ポイント：".number_format($this->data['use_point'])."円\n";
		}
		if($this->setting['discount']['discountUse'] ==0 && $this->data['off_price']){
			$detailStr.= "割引：".number_format($this->data['off_price'])."円\n";
		}
		if($this->data['collect_cost']>0){
			$detailStr.= "代引き手数料：".number_format($this->data['collect_cost'])."円\n";
		}
		$detailStr.= "合計：".number_format($this->data['seikyu_cost'])."円\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "決済方法\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($this->setting['payment'] as $v ){
			if($v['id'] == $this->data['payment_method']){
				$detailStr.= "決済方法：".$v['name']."\n\n".$v['description']."\n";
			}
		}
		$detailStr.= "\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "ご受注主様情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お名前：".$this->data['cus_name']." ".$this->data['cus_name2']."\n";
		$detailStr.= "フリガナ：".$this->data['cus_kana']." ".$this->data['cus_kana2']."\n";
		$detailStr.= "電話番号：".$this->data['cus_tel']."-".$this->data['cus_tel2']."-".$this->data['cus_tel3']."\n";
		$detailStr.= "携帯電話番号：".$this->data['cus_mtel']."-".$this->data['cus_mtel2']."-".$this->data['cus_mtel3']."\n";
		//$detailStr.= "性別：".$this->data['cus_sex']."\n";
		$detailStr.= "生年月日：".$this->data['cus_birthY']."年".$this->data['cus_birthM']."月".$this->data['cus_birthD']."日\n";
		foreach($this->pref as $v){
			if($v['id'] == $this->data['cus_pref']){
				$cusPrefName = $v['name'];
			}
		}
		$detailStr.= "ご住所：〒".$this->data['cus_zip']."-".$this->data['cus_zip2']."\n".$cusPrefName.$this->data['cus_addr']."\n".$this->data['cus_addr2']."\n";
		$detailStr.= "メールアドレス：".$this->data['cus_mail']."\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お届け先様情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お名前：".$this->data['delivery_name']." ".$this->data['delivery_name2']."\n";
		$detailStr.= "フリガナ：".$this->data['delivery_kana']." ".$this->data['delivery_kana2']."\n";
		$detailStr.= "電話番号：".$this->data['delivery_tel']."-".$this->data['delivery_tel2']."-".$this->data['delivery_tel3']."\n";
		foreach($this->pref as $v){
			if($v['id'] == $this->data['delivery_pref']){
				$deliveryPrefName = $v['name'];
			}
		}
		$detailStr.= "ご住所：〒".$this->data['delivery_zip']."-".$this->data['delivery_zip2']."\n".$deliveryPrefName.$this->data['delivery_addr']."\n".$this->data['delivery_addr2']."\n";

		if($this->setting['delivery']['shippingdat'] ==0 || $this->setting['delivery']['shippingtime'] == 0){
			$detailStr.= "ーーーーーーーーーーーー\n";
			$detailStr.= "お届け日時の指定\n";
			$detailStr.= "ーーーーーーーーーーーー\n";
			if($this->setting['delivery']['shippingdat'] ==0 ){
				$detailStr.= "配達希望日：".$this->data['delivery_date']."\n";
			}
			if($this->setting['delivery']['shippingtime'] ==0 ){
				$detailStr.= "配達時間帯：".$this->data['delivery_time']."\n";
			}
			$detailStr.= "※天候や交通状況によりご希望の日時にお届けできない事がございます。予めご了承ください。\n";
		}
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "その他備考\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= $this->data['order_comment']."\n";
		$detailStr.= "\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($result as $v){
			foreach($v as $k=>$vv){
				$patterns[0] = '/%NAME%/';
				$patterns[1] = '/%MAIL%/';
				$patterns[2] = '/%ORDER%/';
				$patterns[3] = '/%SHOP%/';
				$patterns[5] = '/%DELIVERY_PAPER%/';
				$replacements[0] = $this->data['cus_name']." ".$this->data['cus_name2'];
				$replacements[1] = $this->data['cus_mail'];
				$replacements[2] = $detailStr;
				$replacements[3] = $this->_user->company;
				$replacements[5] = $this->data['delivery_paper_no'];
				$v[$k] = preg_replace($patterns, $replacements, $vv);
			}
			$mail[$v['name']] =$v;
		}
		$this->view->mail = $mail;
	}
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注登録';
		$this->view->bread = '<li>
													<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		//商品パターンを読み込み
		$kaijou = $this->_db->fetchAll(
			$this->_db->select()
			->from("kaijou")
		);
		array_unshift($kaijou, array("id"=>NULL,"name"=>"選択してください"));
		$this->view->kaijou  = $kaijou;

		if($this->_request->isGet()){
			$getArr = $this->_db->getArray();
			//projectIDが付与されていた場合
			$this->view->sougi = $getArr['s'];
			if($getArr["s"]){
				$sougi = $this->_db->fetchAll(
					$this->_db->select()
					->from("sougi")
					->where("id=?",$getArr['s'])
				);
				//var_dump($sougi);
				$user = $sougi[0];
				$data = array(
					"cus_name"=>$user['cus_name'],
					"cus_company"=>$user['cus_company'],
					"cus_div"=>$user['cus_div'],
					"cus_name2"=>$user['cus_name2'],
					"cus_kana"=>$user['cus_kana'],
					"cus_kana2"=>$user['cus_kana2'],
					"cus_tel"=>$user['cus_tel'],
					"cus_tel2"=>$user['cus_tel2'],
					"cus_tel3"=>$user['cus_tel3'],
					"cus_mtel"=>$user['cus_mtel'],
					"cus_mtel2"=>$user['cus_mtel2'],
					"cus_mtel3"=>$user['cus_mtel3'],
					"cus_mail"=>$user['cus_mail'],
					"cus_sex"=>$user['cus_sex'],
					"cus_birthY"=>$user['cus_birthY'],
					"cus_birthM"=>$user['cus_birthM'],
					"cus_birthD"=>$user['cus_birthD'],
					"cus_zip"=>$user['cus_zip'],
					"cus_zip2"=>$user['cus_zip2'],
					"cus_pref"=>$user['cus_pref'],
					"cus_addr"=>$user['cus_addr'],
					"cus_addr2"=>$user['cus_addr2'],
					"member_no"=>$user['cus_id']
				);
				$this->view->data = $data;
			}





		}
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			if($postArr["id"]){
				//受注IDが付与されていた場合
				$this->view->data = $this->_db->getorderDetail($postArr["id"]);
				//受注された商品データ
				$this->view->item = $this->_db->getorderByItems2($postArr["id"]);
			}
			if($postArr["member_no"]){
				$users = new Model_Adminimageuser();
				$user = $users->getUserDetail($postArr["member_no"]);
				$data = array(
					"cus_name"=>$user['name'],
					"cus_name2"=>$user['name2'],
					"cus_kana"=>$user['kana'],
					"cus_kana2"=>$user['kana2'],
					"cus_tel"=>$user['tel'],
					"cus_tel2"=>$user['tel2'],
					"cus_tel3"=>$user['tel3'],
					"cus_mtel"=>$user['mtel'],
					"cus_mtel2"=>$user['mtel2'],
					"cus_mtel3"=>$user['mtel3'],
					"cus_mail"=>$user['mail'],
					"cus_sex"=>$user['sex'],
					"cus_birthY"=>$user['birthY'],
					"cus_birthM"=>$user['birthM'],
					"cus_birthD"=>$user['birthD'],
					"cus_zip"=>$user['zip'],
					"cus_zip2"=>$user['zip2'],
					"cus_pref"=>$user['pref'],
					"cus_addr"=>$user['addr'],
					"cus_addr2"=>$user['addr2'],
					"member_no"=>$user['id']
				);
				$this->view->data = $data;
			}
		}
	}
	public function registrer3Action() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	public function registrer4Action() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		/**/
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			if($postArr["id"]){
				//受注IDが付与されていた場合
				$this->view->data = $this->_db->getorderDetail($postArr["id"]);
				//受注された商品データ
				$this->view->item = $this->_db->getorderByItems2($postArr["id"]);
			}
			if($postArr["member_no"]){
				$users = new Model_Adminimageuser();
				$user = $users->getUserDetail($postArr["member_no"]);
				$data = array(
					"cus_campany"=>$user['company'],
					"cus_name"=>$user['name'],
					"cus_kana"=>$user['kana'],
					"cus_div"=>$user['div'],
					"cus_tel"=>$user['tel'],
					"cus_fax"=>$user['fax'],
					"cus_zip"=>$user['zip'],
					"cus_pref"=>$user['pref'],
					"cus_addr"=>$user['addr'],
					"cus_addr2"=>$user['addr2'],
					"member_no"=>$user['id']
				);
				$this->view->data = $data;
			}
		}
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();

			//マーチャント番号を付与
			$postArr['parent'] = $this->_user->id;
			//購入商品は別の配列に入れて受注データから削除
			$itemArr = $postArr['item'];
			unset($postArr['item']);
			//不要なデータを受注データから削除
			unset($postArr['poinAmount']);

			//空白の数値を0とする
			if($postArr['off_price'] == ""){
				$postArr['off_price'] = 0;
			}
			if($postArr['use_point'] == ""){
				$postArr['use_point'] = 0;
			}

			//オーダー番号の生成
			$id = $this->_db->fetchAll(
				$this->_db->select()
				->from("orderNo")
				->where("parent=?",$this->_user->id)
				->limit(1)
			);
			$newId = $id[0]['no']+1;
			$this->_db->update("orderNo",array("no"=>$newId),$this->_db->quoteInto("parent=?",$this->_user->id));

			$order_id = $newId.date("msYidH");
			$postArr['order_id'] = $order_id ;
			$postArr['orderDatetime'] = date("Y-m-d H:i:s");
			$itemArr = json_decode(html_entity_decode($itemArr),true);
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId("order",$postArr)){
				//商品データをDBに挿入
				foreach($itemArr as $v){
					$items = array(
						"itemId"=>$v['data']['id'],
						"image"=>$v['data']['imagen'],
						"name"=>$v['data']['name'],
						"price"=>$v['data']['price'],
						"quantity"=>$v['q'],
						//"cost"=>$v['data']['cost'],
						"category"=>$v['data']['category'],
						"unit"=>$v['data']['unit'],
						"number"=>$v['data']['number'],
						"orderId"=>$lastId,
						//"tax"=>$v['data']['tax'],
						"parent"=>$this->_user->id
					);
					//var_dump($items);
					$this->_db->insert("orderItem",$items);
				}
				//ポイントの入出力
				if($this->setting['point']['pointuse'] ==0){
					if(!empty($postArr['member_no'])){
						$pin = ceil(($postArr['item_total']-$postArr['use_point'])*0.03);
						$customerPointArr = array(
							"customerId" =>$postArr['member_no'],
							"created" =>date("Y-m-d H:i:s"),
							"pin" =>$pin,
							"pout" =>$postArr['use_point'],
							"parent" =>$this->_user->id,
							"orderId" =>$lastId,
							"limit_date" =>date("Y-m-d H:i:s",time()+60*60*24*$this->setting['point']['pointlimit'])
						);
						$this->_db->insert("cutomerPoint",$customerPointArr);
					}
				}
				header("location:".BASEURL."/admin/order/registrer2/?id=".$lastId);
			}
			/*
			var_dump($postArr);
			*/
		}
	}
	public function registrer2Action() {
		$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getorderDetail($postArr["id"]);
		//受注された商品データ
		$this->view->item = $this->_db->getorderByItems($postArr["id"]);
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['order_id'].'の受注登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/registrer"><i class="fa fa-gift"></i>　受注登録</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集
	public function updateAction() {
		$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getorderDetail($postArr["id"]);
		//受注された商品データ
		$this->view->item = $this->_db->getorderByItems2($postArr["id"]);
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注情報編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/detail/?id='.$postArr["id"].'"><i class="fa fa-gift"></i>　'.$this->view->data['order_id'].'の受注詳細</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	public function update4Action() {
		$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getorderDetail($postArr["id"]);
		//受注された商品データ
		$this->view->item = $this->_db->getorderByItems3($postArr["id"]);
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注情報編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/detail/?id='.$postArr["id"].'"><i class="fa fa-gift"></i>　'.$this->view->data['order_id'].'の受注詳細</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function updatefinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();

			//マーチャント番号を付与
			$postArr['parent'] = $this->_user->id;
			//購入商品は別の配列に入れて受注データから削除
			$itemArr = $postArr['item'];
			unset($postArr['item']);
			//不要なデータを受注データから削除
			unset($postArr['poinAmount']);

			//空白の数値を0とする
			if($postArr['off_price'] == ""){
				$postArr['off_price'] = 0;
			}
			if($postArr['use_point'] == ""){
				$postArr['use_point'] = 0;
			}

			//DBに登録  ↓ここをupdateに変更及び、商品を
			//var_dump($postArr);
			$this->_db->update("order",$postArr,$this->_db->quoteInto("`id`=?",$postArr['id']));

			//商品データをDBに更新（一旦削除してinsert）
			$this->_db->delete("orderItem",$this->_db->quoteInto("`orderId`=?",$postArr['id']));
			$itemArr = json_decode($this->_request->getPost('item'),true);
			if($v['data']['imagen'] ==NULL){
				$v['data']['imagen'] = "";
			}
			foreach($itemArr as $v){
				$items = array(
					"itemId"=>$v['data']['id'],
					"image"=>$v['data']['imagen'],
					"name"=>$v['data']['name'],
					"price"=>$v['data']['price'],
					"quantity"=>$v['q'],
					"cost"=>$v['data']['cost'],
					"category"=>$v['data']['category'],
					"unit"=>$v['data']['unit'],
					"number"=>$v['data']['number'],
					"orderId"=>$postArr['id'],
					"tax"=>$v['data']['tax'],
					"parent"=>$this->_user->id
				);
				$this->_db->insert("orderItem",$items);
			}
			//ポイントの入出力
			if($this->setting['point']['pointuse'] ==0){
				if(!empty($postArr['member_no'])){
					$pin = ceil(($postArr['item_total']-$postArr['use_point'])*0.03);
					$customerPointArr = array(
						"customerId" =>$postArr['member_no'],
						"created" =>date("Y-m-d H:i:s"),
						"pin" =>$pin,
						"pout" =>$postArr['use_point'],
						"parent" =>$this->_user->id,
						"orderId" =>$postArr['id'],
						"limit_date" =>date("Y-m-d H:i:s",time()+60*60*24*$this->setting['point']['pointlimit'])
					);

					$this->_db->update("cutomerPoint",$customerPointArr,$this->_db->quoteInto("`orderId`=?",$postArr['id']));
				}
			}
			$this->view->Msg = "受注の編集が完了いたしました。";
		}
		$this->view->data = $this->_db->getorderDetail($postArr["id"]);
		//受注された商品データ
		$this->view->item = $this->_db->getorderByItems($postArr["id"]);
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注情報編集完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/detail/?id='.$postArr["id"].'"><i class="fa fa-gift"></i>　'.$this->view->data['order_id'].'の受注詳細</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	public function mailAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-envelope-o"></i>　メールの送付';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		//POSTデータのエスケープ処理
		$postArr = $this->_db->postArray();
		$this->data = $this->_db->getorderDetail($postArr ['id']);
		$this->item = $this->_db->getorderByItems($postArr ['id']);
		$this->view->item = $this->item;

		$this->view->data = $this->data;
		//メールデータの取得
		$result = $this->_db->fetchAll(
			$this->_db->select()
			->from("mail_template")
			->where("parent=?",$this->_user->id)
		);
		$mail = array();
		$detailStr = "ーーーーーーーーーーーー\n";;
		$detailStr.= "ご受注商品情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($this->item as $v){
			$taxRatio = 1/100*$this->setting['tax']['ratio'];
			$price = $v['price'];
			$detailStr.= $v['name']."　".number_format($price)."円×".$v['quantity'].$v['unit']."=".number_format($price*$v['quantity'])."円\n";
		}
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "小計：".number_format($this->data['item_total'])."円\n";
		$detailStr.= "内消費税：".number_format($this->data['tax_total'])."円\n";
		$detailStr.= "送料：".number_format($this->data['shipping_cost'])."円\n";
		if($this->setting['point']['pointuse'] ==0 && $this->data['use_point']){
			$detailStr.= "ポイント：".number_format($this->data['use_point'])."円\n";
		}
		if($this->setting['discount']['discountUse'] ==0 && $this->data['off_price']){
			$detailStr.= "割引：".number_format($this->data['off_price'])."円\n";
		}
		if($this->data['collect_cost']>0){
			$detailStr.= "代引き手数料：".number_format($this->data['collect_cost'])."円\n";
		}
		$detailStr.= "合計：".number_format($this->data['seikyu_cost'])."円\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "決済方法\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($this->setting['payment'] as $v ){
			if($v['id'] == $this->data['payment_method']){
				$detailStr.= "決済方法：".$v['name']."\n\n".$v['description']."\n";
			}
		}
		$detailStr.= "\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "ご受注主様情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お名前：".$this->data['cus_name']." ".$this->data['cus_name2']."\n";
		$detailStr.= "フリガナ：".$this->data['cus_kana']." ".$this->data['cus_kana2']."\n";
		$detailStr.= "電話番号：".$this->data['cus_tel']."-".$this->data['cus_tel2']."-".$this->data['cus_tel3']."\n";
		$detailStr.= "携帯電話番号：".$this->data['cus_mtel']."-".$this->data['cus_mtel2']."-".$this->data['cus_mtel3']."\n";
		//$detailStr.= "性別：".$this->data['cus_sex']."\n";
		$detailStr.= "生年月日：".$this->data['cus_birthY']."年".$this->data['cus_birthM']."月".$this->data['cus_birthD']."日\n";
		foreach($this->pref as $v){
			if($v['id'] == $this->data['cus_pref']){
				$cusPrefName = $v['name'];
			}
		}
		$detailStr.= "ご住所：〒".$this->data['cus_zip']."-".$this->data['cus_zip2']."\n".$cusPrefName.$this->data['cus_addr']."\n".$this->data['cus_addr2']."\n";
		$detailStr.= "メールアドレス：".$this->data['cus_mail']."\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お届け先様情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お名前：".$this->data['delivery_name']." ".$this->data['delivery_name2']."\n";
		$detailStr.= "フリガナ：".$this->data['delivery_kana']." ".$this->data['delivery_kana2']."\n";
		$detailStr.= "電話番号：".$this->data['delivery_tel']."-".$this->data['delivery_tel2']."-".$this->data['delivery_tel3']."\n";
		foreach($this->pref as $v){
			if($v['id'] == $this->data['delivery_pref']){
				$deliveryPrefName = $v['name'];
			}
		}
		$detailStr.= "ご住所：〒".$this->data['delivery_zip']."-".$this->data['delivery_zip2']."\n".$deliveryPrefName.$this->data['delivery_addr']."\n".$this->data['delivery_addr2']."\n";

		if($this->setting['delivery']['shippingdat'] ==0 || $this->setting['delivery']['shippingtime'] == 0){
			$detailStr.= "ーーーーーーーーーーーー\n";
			$detailStr.= "お届け日時の指定\n";
			$detailStr.= "ーーーーーーーーーーーー\n";
			if($this->setting['delivery']['shippingdat'] ==0 ){
				$detailStr.= "配達希望日：".$this->data['delivery_date']."\n";
			}
			if($this->setting['delivery']['shippingtime'] ==0 ){
				$detailStr.= "配達時間帯：".$this->data['delivery_time']."\n";
			}
			$detailStr.= "※天候や交通状況によりご希望の日時にお届けできない事がございます。予めご了承ください。\n";
		}
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "その他備考\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= $this->data['order_comment']."\n";
		$detailStr.= "\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($result as $v){
			foreach($v as $k=>$vv){
				$patterns[0] = '/%NAME%/';
				$patterns[1] = '/%MAIL%/';
				$patterns[2] = '/%ORDER%/';
				$patterns[3] = '/%SHOP%/';
				$patterns[5] = '/%DELIVERY_PAPER%/';
				$replacements[0] = $this->data['cus_name']." ".$this->data['cus_name2'];
				$replacements[1] = $this->data['cus_mail'];
				$replacements[2] = $detailStr;
				$replacements[3] = $this->_user->company;
				$replacements[5] = $this->data['delivery_paper_no'];
				$v[$k] = preg_replace($patterns, $replacements, $vv);
			}
			$mail[$v['name']] =$v;
		}
		$this->view->mail = $mail;
	}
	//進捗メール送信
	public function mailsendAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-envelope-o"></i>　メールの送付完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/mail"><i class="fa fa-envelope-o"></i>　メールの送付</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/mailsend/"　class="btn disabled">　'.$this->view->title.'</a>
													</li>';


		//POSTデータのエスケープ処理
		$postArr = $this->_db->postArray();
		//メールを送ると同時にチェックを入れる
		if($postArr['name'] == "checkOrder"){
			$col = "check_order";
		}elseif($postArr['name'] == "paymentEnd"){
			$col = "check_payment_end";
		}elseif($postArr['name'] == "delivery"){
			$col = "check_delivery";
		}elseif($postArr['name'] == "3d"){
			$col = "check_follow_mail";
		}elseif($postArr['name'] == "1m"){
			$col = "check_3w";
		}elseif($postArr['name'] == "2m"){
			$col = "check_2m";
		}elseif($postArr['name'] == "3m"){
			$col = "check_3m";
		}elseif($postArr['name'] == "6m"){
			$col = "check_6m";
		}
		//進捗状況の更新
		if($col){
			$res = $this->_db->update("order",array($col=>date("Y-m-d H:i:s")),$this->_db->quoteInto("`id`=?",$postArr ['id']));
		}

		$this->data = $this->_db->getorderDetail($postArr ['id']);
		$this->item = $this->_db->getorderByItems($postArr ['id']);
		$this->view->item = $this->item;
		$this->view->data = $this->data;

		//メールデータの取得
		$result = $this->_db->fetchAll(
			$this->_db->select()
			->from("mail_template")
			->where("parent=?",$this->_user->id)
		);
		$mail = array();
		$detailStr = "ーーーーーーーーーーーー\n";;
		$detailStr.= "ご受注商品情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($this->item as $v){
			$taxRatio = 1/100*$this->setting['tax']['ratio'];
			$price = $v['price'];
			$detailStr.= $v['name']."　".number_format($price)."円×".$v['q'].$v['unit']."=".number_format($price*$v['q'])."円\n";
		}
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "小計：".number_format($this->data['item_total'])."円\n";
		$detailStr.= "内消費税：".number_format($this->data['tax_total'])."円\n";
		$detailStr.= "送料：".number_format($this->data['shipping_cost'])."円\n";
		if($this->setting['point']['pointuse'] ==0 && $this->data['use_point']){
			$detailStr.= "ポイント：".number_format($this->data['use_point'])."円\n";
		}
		if($this->setting['discount']['discountUse'] ==0 && $this->data['off_price']){
			$detailStr.= "割引：".number_format($this->data['off_price'])."円\n";
		}
		if($this->data['collect_cost']>0){
			$detailStr.= "代引き手数料：".number_format($this->data['collect_cost'])."円\n";
		}
		$detailStr.= "合計：".number_format($this->data['seikyu_cost'])."円\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "決済方法\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($this->setting['payment'] as $v ){
			if($v['id'] == $this->data['payment_method']){
				$detailStr.= "決済方法：".$v['name']."\n\n".$v['description']."\n";
			}
		}
		$detailStr.= "\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "ご受注主様情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お名前：".$this->data['cus_name']." ".$this->data['cus_name2']."\n";
		$detailStr.= "フリガナ：".$this->data['cus_kana']." ".$this->data['cus_kana2']."\n";
		$detailStr.= "電話番号：".$this->data['cus_tel']."-".$this->data['cus_tel2']."-".$this->data['cus_tel3']."\n";
		$detailStr.= "携帯電話番号：".$this->data['cus_mtel']."-".$this->data['cus_mtel2']."-".$this->data['cus_mtel3']."\n";
		//$detailStr.= "性別：".$this->data['cus_sex']."\n";
		$detailStr.= "生年月日：".$this->data['cus_birthY']."年".$this->data['cus_birthM']."月".$this->data['cus_birthD']."日\n";
		foreach($this->pref as $v){
			if($v['id'] == $this->data['cus_pref']){
				$cusPrefName = $v['name'];
			}
		}
		$detailStr.= "ご住所：〒".$this->data['cus_zip']."-".$this->data['cus_zip2']."\n".$cusPrefName.$this->data['cus_addr']."\n".$this->data['cus_addr2']."\n";
		$detailStr.= "メールアドレス：".$this->data['cus_mail']."\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お届け先様情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お名前：".$this->data['delivery_name']." ".$this->data['delivery_name2']."\n";
		$detailStr.= "フリガナ：".$this->data['delivery_kana']." ".$this->data['delivery_kana2']."\n";
		$detailStr.= "電話番号：".$this->data['delivery_tel']."-".$this->data['delivery_tel2']."-".$this->data['delivery_tel3']."\n";
		foreach($this->pref as $v){
			if($v['id'] == $this->data['delivery_pref']){
				$deliveryPrefName = $v['name'];
			}
		}
		$detailStr.= "ご住所：〒".$this->data['delivery_zip']."-".$this->data['delivery_zip2']."\n".$deliveryPrefName.$this->data['delivery_addr']."\n".$this->data['delivery_addr2']."\n";
		if($this->setting['delivery']['shippingdat'] ==0 || $this->setting['delivery']['shippingtime'] == 0){
			$detailStr.= "ーーーーーーーーーーーー\n";
			$detailStr.= "お届け日時の指定\n";
			$detailStr.= "ーーーーーーーーーーーー\n";
			if($this->setting['delivery']['shippingdat'] ==0 ){
				$detailStr.= "配達希望日：".$this->data['delivery_date']."\n";
			}
			if($this->setting['delivery']['shippingtime'] ==0 ){
				$detailStr.= "配達時間帯：".$this->data['delivery_time']."\n";
			}
			$detailStr.= "※天候や交通状況によりご希望の日時にお届けできない事がございます。予めご了承ください。\n";
		}
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "その他備考\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= $this->data['order_comment']."\n";
		$detailStr.= "\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($result as $v){
			foreach($v as $k=>$vv){
				$patterns[0] = '/%NAME%/';
				$patterns[1] = '/%MAIL%/';
				$patterns[2] = '/%ORDER%/';
				$patterns[3] = '/%SHOP%/';
				$patterns[4] = '/%SHOPDETAIL%/';
				$patterns[5] = '/%DELIVERY_PAPER%/';
				$replacements[0] = $this->data['cus_campany'].$this->data['cus_name'];
				$replacements[1] = $this->data['cus_mail'];
				$replacements[2] = $detailStr;
				$replacements[3] = $this->_user->company;
				$replacements[4] = 'ショップ詳細';
				$replacements[5] = $this->data['delivery_paper_no'];
				$v[$k] = preg_replace($patterns, $replacements, $vv);
			}
			$mail[$v['name']] =$v;
		}
		$this->view->mail = $mail;

		$smtp = array();
		foreach($this->setting['global'] as $k=>$v){
			if($k == "SMTPhost"){
				$smtp[$k] = $v;
			}
			if($k == "SMTPuser"){
				$smtp[$k] = $v;
			}
			if($k == "SMTPpass"){
				$smtp[$k] = $v;
			}
			if($k == "SMTPport"){
				$smtp[$k] = $v;
			}
			if($k == "SMTPsecur"){
				$smtp[$k] = $v;
			}
			if($k == "SMTPcertificate"){
				$smtp[$k] = $v;
			}
		}
		$this->_db->sendmail(
			$postArr['autoRes'],
			$postArr['subject'],
			$postArr['body'],
			$this->setting['global']['infoMail'],
			$this->setting['global']['SiteName'],
			$this->data['cus_mail'],
			$this->data['cus_name'],
			$smtp
		);

		//受注詳細へリダイレクト
		header("location:".BASEURL."/admin/order/detail/?id=".$postArr['id']);
	}
	//伝票番号登録
	public function deliverynoAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注詳細';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			if($this->_db->update("order",array("delivery_paper_no"=>$postArr['delivery_paper_no']),$this->_db->quoteInto("`id`=?",$postArr['id']))){
				$this->view->Msg = "伝票番号の登録が完了いたしました。";
			}
			$this->view->data = $this->_db->getorderDetail($postArr['id']);
			//受注された商品データ
			$this->view->item = $this->_db->getorderByItems($postArr['id']);
		}

		//受注詳細へリダイレクト
		header("location:".BASEURL."/admin/order/detail/?id=".$postArr['id']);
	}

	//帳票の印刷
	public function paperAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$this->data = $this->_db->getorderDetail($postArr['id'],$this->_user->id);

			//2018-05-26　定期コース名を取得
			if($this->data['subscriptions'] !=0){
				$corse = $this->_db->fetchAll(
					"SELECT c.name AS course
					FROM Subscriptions o
					LEFT JOIN SubscriptionsCourse c ON c.id = o.courseid
					WHERE o.id = ". $this->data['subscriptions']."
					LIMIT 1"
				);
				$this->data['course'] = $corse[0]['course'];
			}

			//受注回数を取得
			$c = $this->_db->fetchAll(
				$this->_db->select()
				->from("order",array("count(*) AS c"))
				->where("cus_mail=?",$this->data['cus_mail'])
			);
			$this->data['c'] = $c[0]['c'];
			//受注された商品データ
			$this->item = $this->_db->getorderByItems($postArr['id'],$this->_user->id);
			$date = date("Y-m-d",strtotime($postArr['paperdate'])." 00:00:00");
			//都道府県名
			foreach($this->pref as $v){
				if($this->data['cus_pref'] == $v['id']){
					$this->data['cus_prefName'] = $v['name'];
				}
				if($this->data['delivery_pref'] == $v['id']){
					$this->data['delivery_prefName'] = $v['name'];
				}
			}
			//決済方法
			foreach($this->_setting['payment'] as $v){
				if($this->data['payment_method'] == $v['id']){
					$this->data['payment_methodName'] = $v['name'];
				}
			}

			//用途名
			foreach($this->_setting['youto'] as $v){
				if($this->data['youto'] == $v['id']){
					$this->data['youtoName'] = $v['name'];
				}
			}
			//名前を記載するかのチェック
			if($postArr['camp']){
				$this->data['camp'] = true;
			}
			echo 123;
			//$this->pdf = new Model_Adminpaper();
			//$this->printPdf($postArr['kind'],$date,$this->data,$this->item,$this->_user,$this->_setting['company']);









				if($date ==0){
					$date = date("Y-m-d");
				}
				//FPDFライブリの読み込み
				require_once 'PEAR/fpdf/japanese.php';
				mb_language("ja");
				mb_internal_encoding('SJIS');
				define(SC_CHAR, "UTF-8");
				if($postArr['kind'] == 4 || $postArr['kind'] == 5 || $postArr['kind'] == 6 || $postArr['kind'] == 7){
					//########## 納品書・請求書・見積書 ##########
					$pdf=new PDF_Japanese();
					// 自スクリプトの文字コード
					// インスタンス作成
					$pdf = new PDF_Japanese('P', 'mm', 'A4');
					// SJISフォント(MSPGothicを使用)
					$pdf->AddSJISFont2();
					// 書き込み開始
					$pdf->Open();
					// フォントのセット ※SJIS(MSPGothic)でフォントサイズ10
					// ページを追加(新規ページ)
					$pdf->AddPage();
					$pdf->SetFont('SJIS', 'BU', 14);
					//帳票タイトルの表示
					if($postArr['kind'] == 4){
						$headding = "お買上げ明細書";
					}elseif($postArr['kind'] == 5 || $postArr['kind'] == 6){
						$headding = "御請求書";
					}else{
						$headding = "御見積書";
					}
					$pdf->Text(170, 12, $this->sjis_conv($headding));//headding
					//日付の表示
					$pdf->SetFont('SJIS', '', 9);
					$p_date_arr = explode("-",$date);
					$yyyy = $p_date_arr[0];
					$mm = $p_date_arr[1];
					$dd = $p_date_arr[2];
					$jo_time = "発行日：".date("Y年m月d日",mktime(0,0,0, $mm,$dd,$yyyy));// 発行日
					$pdf->Text(130, 23.5, $this->sjis_conv($jo_time));
					$pdf->SetFont('SJIS', '', 12);
					$x = 173;
					$y = 27;
					//印鑑画像の表示
					if($kind != 6){
						if($this->_setting['company']['stamp']){
							$pdf->Image(BASEURL.$this->_setting['company']['stamp'], $x, $y, 25,25);
						}
					}
					//自社データの表示
					$pdf->Text(130, 30, $this->sjis_conv($this->_setting['company']['company']));// 会社名
					$pdf->SetFont('SJIS', '', 9);
					$pdf->Text(130, 35, $this->sjis_conv("〒".$this->_setting['company']['zip']));// 会社郵便番号
					$pdf->Text(130, 38.5, $this->sjis_conv($this->_setting['company']['addr']));// 会社住所
					$pdf->Text(130, 42, $this->sjis_conv($this->_setting['company']['addr2']));// 会社住所
					$pdf->Text(130, 45, $this->sjis_conv("電話：".$this->_setting['company']['tel']));// 会社電話
					$pdf->Text(130, 48, $this->sjis_conv("FAX ：".$this->_setting['company']['fax']));// 会社FAX
					//$pdf->Text(130, 48, $this->sjis_conv("Mail：".$user->mail));// 会社Mail
					//$pdf->Text(130, 53, $this->sjis_conv("担当：".$this->_setting['company']->name));// 会社Mail
					//注文主データ　
					$pdf->SetFont('SJIS', '',9);
					$oy_post = "〒".$this->data['cus_zip']."-".$this->data['cus_zip2'];
					$pdf->Text(28,22, $this->sjis_conv($oy_post));// 注文主郵便番号
					$pdf->SetXY(28, 24);
					$pdf->MultiCell(70, 4, $this->sjis_conv($this->data['cus_prefName'].$this->data['cus_addr']."\n".$this->data['cus_addr2']));// 注文主住所
					$pdf->SetFont('SJIS', '', 12);

					$c_name = $this->data['cus_campany']."様";
					//$c_name = $this->data['cus_name']." ".$this->data['cus_name2']."様";
					$pdf->SetXY(28, 35);
					$pdf->MultiCell(70, 4.5, $this->sjis_conv($c_name));
					//$pdf->Image($image_file_path,28,43,51.1,10,"PNG");//バーコードを出力

					$pdf->SetFont('SJIS', '',9);
					if($postArr['kind'] == 4){
					$pdf->Text(10,63, $this->sjis_conv("このたびはジョン&マリーでお買上げいただき、誠にありがとうございます。丁寧に作られたオーガニック製品の心地よさを"));
					$pdf->Text(10,68, $this->sjis_conv("存分にお楽しみください。"));
					}elseif($postArr['kind'] == 5 || $postArr['kind'] == 6){
					$pdf->Text(10,63, $this->sjis_conv("毎々、格別なるお引き立てに預かり、厚く御礼申し上げます。下記の通りご請求申し上げます。"));
					}else{
					$pdf->Text(10,63, $this->sjis_conv("毎々、格別なるお引き立てに預かり、厚く御礼申し上げます。下記の通りお見積り申し上げます。"));
					}

					$pdf->SetFont('SJIS', 'BU', 12);

					if($postArr['kind'] == 4 || $postArr['kind'] == 5|| $postArr['kind'] == 6){
						$pdf->Text(10,74, $this->sjis_conv("お買上げ金額：".number_format($this->data['seikyu_cost'])."円"));// 請求金額
					}else{
						$pdf->Text(10,74, $this->sjis_conv("お見積り金額：".number_format($this->data['seikyu_cost'])."円"));// 請求金額

					}

					//商品詳細を出力
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 10, 75, 190,7);
					$pdf->SetFont('SJIS', '',9);
					$w1 = 75;
					$w2 = 45;
					$w3 = 10;
					$w4 = 60;
					$w6 = 130;
					$pdf->SetY(75);
					$pdf->Cell($w1,7,$this->sjis_conv("[商品番号]商品名"),1,'L',"L");
					$pdf->Cell($w2,7,$this->sjis_conv("単価"),1,"",'L',0);
					$pdf->Cell($w3,7,$this->sjis_conv("数量"),1,"",'L',0);
					$pdf->Cell($w4,7,$this->sjis_conv("価格"),1,"",'L',0);
					$pdf->Ln();
					//価格を税抜きに変更
					foreach($this->item as $v){
						$price = $this->spacePadding($v['price'],7);
						$pdf->Cell($w1, 7, $this->sjis_conv($v['name']), 1, '',"RB");
						$pdf->Cell($w2, 7, $this->sjis_conv($price."円"), 1, "",'RB', 0);
						$pdf->Cell($w3, 7, $this->sjis_conv($v['quantity'] .$v['unit'] ), 1,"", 'RB', 0);
						$kakaku =$v['price'] *$v['quantity'] ;
						$kakaku = $this->spacePadding($kakaku,12);
						$pdf->Cell($w4, 7, $this->sjis_conv($kakaku."円"), 1, "",'RB', 0);
						$pdf->Ln();
					}
					//空白行を挿入
					$arr_size =count($this->item);

					if($postArr['kind'] == 5 || $postArr['kind'] == 6){
							$c = 10;
					}else{
						if($this->data['check_payment_end'] !=""){
							$c = 5;
						}else{
							$c = 10;
						}
					}
					if($arr_size <$c){
						$loop_size = $c-$arr_size;
					}
					for($i=0;$i<$loop_size;$i++){
						$pdf->Cell($w1, 7, $this->sjis_conv(' '), 1, 'L', 0);
						$pdf->Cell($w2, 7, $this->sjis_conv(' '), 1, 'L', 0);
						$pdf->Cell($w3, 7, $this->sjis_conv(' '), 1, 'L', 0);
						$pdf->Cell($w4, 7, $this->sjis_conv(' '), 1, 'L', 0);
						$pdf->Ln();
					}
					$pdf->Cell($w6,7,$this->sjis_conv("小計"),1,'L',0);
					$pdf->Cell($w4,7,$this->sjis_conv($this->spacePadding($this->data['item_total'],12)."円"),1,"",'RB',0);
					$pdf->Ln();
					$pdf->Cell($w6,7,$this->sjis_conv("消費税"),1,'L',0);
					$pdf->Cell($w4,7,$this->sjis_conv($this->spacePadding($this->data['tax_total'],12)."円"),1,"",'RB',0);
					if($this->data['shipping_cost']){
						$pdf->Ln();
						$pdf->Cell($w6,7,$this->sjis_conv("送料"),1,'L',0);
						$pdf->Cell($w4,7,$this->sjis_conv($this->spacePadding($this->data['shipping_cost'],12)."円"),1,"",'RB',0);
					}
					$pdf->Ln();
					if(!empty($this->data['collect_cost'])){
						$pdf->Cell($w6,7,$this->sjis_conv("代引手数料"),1,'L',0);
						$pdf->Cell($w4,7,$this->sjis_conv($this->spacePadding($this->data['collect_cost'],12)."円"),1,"",'RB',0);
						$pdf->Ln();
					}
					if(!empty($this->data['use_point'])){
						$this->data['use_point'] = "-".$this->data['use_point'];
						$pdf->Cell($w6,7,$this->sjis_conv("ポイント使用"),1,'L',0);
						$pdf->Cell($w4,7,$this->sjis_conv($this->spacePadding($this->data['use_point'],12)."円"),1,"",'RB',0);
						$pdf->Ln();
					}
					if(!empty($this->data['off_price'])){
						$this->data['off_price'] = "-".$this->data['off_price'];
						$pdf->Cell($w6,7,$this->sjis_conv("割引"),1,'L',0);
						$pdf->Cell($w4,7,$this->sjis_conv($this->spacePadding($this->data['off_price'],12)."円"),1,"",'RB',0);
						$pdf->Ln();
					}
					$pdf->Cell($w6,7,$this->sjis_conv("合計"),1,'L',0);
					$pdf->Cell($w4,7,$this->sjis_conv($this->spacePadding($this->data['seikyu_cost'],12)."円"),1,"",'RB',0);
					$pdf->Ln();
					$pdf->Ln();

					$y = $pdf->GetY();
					$pdf->SetXY(10, $y);
					//$otodoke = "お届け先様："
					//.$this->data['delivery_name']
					//."　".$this->data['delivery_name2']
					//."(".$this->data['delivery_kana'].$this->data['delivery_kana2'].")様\n〒"
					//.$this->data['delivery_zip']."-".$this->data['delivery_zip2']." "
					//.$this->data['delivery_prefName']
					//.$this->data['delivery_addr']
					//.$this->data['delivery_addr2']."\n";
					//if(!empty($this->data['delivery_date'])){
					//	$d_date_arr = explode("-",$d$this->dataata['delivery_date']);
					//	$d_date_str = date("Y年m月d日",mktime(0,0,0,$d_date_arr[1],$d_date_arr[2],$d_date_arr[0]));
					//}else{
					//	$d_date_str = "ご指定がございません";
					//}
					//$otodoke.= "お届け日時指定：".$d_date_str." ".$this->data['delivery_time'];
					if($this->data['order_comment']){
						$otodoke = "備考\n".$this->data['order_comment'];
						$pdf->MultiCell(190, 6, $this->sjis_conv("$otodoke"),1,"LT");
					}
					$pdf->Ln();
					if($postArr['kind'] == 4 && $this->_setting['company']['goodsFooter']){
						$y = $pdf->GetY();
						$pdf->MultiCell(190, 6, $this->sjis_conv($this->_setting['company']['goodsFooter']),1,"LT");
					}elseif($postArr['kind'] == 5 && $this->_setting['company']['demandFooter']){
						$y = $pdf->GetY();
						$pdf->MultiCell(190, 6, $this->sjis_conv($this->_setting['company']['demandFooter']),1,"LT");
					}elseif($postArr['kind'] == 6 && $this->_setting['company']['demandFooter']){
						$y = $pdf->GetY();
						$pdf->MultiCell(190, 6, $this->sjis_conv($this->_setting['company']['demandFooter']),1,"LT");
					}elseif($postArr['kind'] == 7 && $this->_setting['company']['esitmateFooter']){
						$y = $pdf->GetY();
						$pdf->MultiCell(190, 6, $this->sjis_conv($this->_setting['company']['esitmateFooter']),1,"LT");
					}
				}
				if($postArr['kind'] == 8){
					//########## 納品書・請求書・見積書 ##########
					$pdf=new PDF_Japanese();
					// 自スクリプトの文字コード
					// インスタンス作成
					$pdf = new PDF_Japanese('P', 'mm', 'A4');
					// SJISフォント(MSPGothicを使用)
					$pdf->AddSJISFont2();
					// 書き込み開始
					$pdf->Open();
					// フォントのセット ※SJIS(MSPGothic)でフォントサイズ10
					// ページを追加(新規ページ)
					$pdf->AddPage();
					$pdf->SetFont('SJIS', 'B', 12);

					//商品詳細を出力
					$pdf->Image(BASEURL.'/img/logo.jpg', 80, 20, 50,11.7);
					//帳票タイトルの表示
					$headding = "お買上げ明細書";
					$pdf->Text(91, 40, $this->sjis_conv($headding));//headding
					$pdf->SetFont('SJIS', '', 10);
					$c_name = $this->data['cus_name']." ".$this->data['cus_name2']."様";
					$pdf->SetXY(20, 45);
					$pdf->MultiCell(70, 4.5, $this->sjis_conv($c_name));
					$pdf->SetFont('SJIS', '', 8);
					$pdf->SetXY(20, 53);
					$str = "このたびはジョン&マリーでお買上げいただき、誠にありがとうございます。\n丁寧に作られたオーガニック製品の心地よさを存分にお楽しみください。";
					if($this->data['c']==1 && $this->data['member_no']){
						$pdf->Text(21, 70, $this->sjis_conv( "新規会員登録ありがとうございます。"));// 会社名
					}
					$pdf->MultiCell(105, 4.5, $this->sjis_conv($str));
					//自社データの表示
					$pdf->SetFont('SJIS', '', 12);
					$pdf->Text(130, 57, $this->sjis_conv($this->_setting['company']['company']));// 会社名
					$pdf->SetFont('SJIS', '', 9);
					$pdf->Text(130, 62, $this->sjis_conv("〒".$this->_setting['company']['zip']));// 会社郵便番号
					$pdf->Text(130, 65.5, $this->sjis_conv($this->_setting['company']['addr']));// 会社住所
					$pdf->Text(130, 69, $this->sjis_conv($this->_setting['company']['addr2']));// 会社住所
					$pdf->Text(130, 73, $this->sjis_conv("電話：".$this->_setting['company']['tel']));// 会社電話
					$pdf->Text(130, 77, $this->sjis_conv("FAX ：".$this->_setting['company']['fax']));// 会社FAX
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 20, 82, 170,5);
					$pdf->SetFont('SJIS', '', 10);
					$pdf->Text(25, 85.5, $this->sjis_conv("お買上げ明細"));


					$pdf->Ln();
					$pdf->Ln();
					$pdf->Ln();
					$pdf->Ln();
					$pdf->Ln();
					$pdf->Ln();
					//お届け先の表示
					$pdf->SetFont('SJIS', '', 8);
					$otodoke = "■お届け先様\n"
					."　　　".$this->data['delivery_name']
					."　".$this->data['delivery_name2']
					."(".$this->data['delivery_kana'].$this->data['delivery_kana2'].")様\n"
					."　　　〒".$this->data['delivery_zip']."-".$this->data['delivery_zip2']."\n"
					."　　　".$this->data['delivery_prefName']
					.$this->data['delivery_addr']
					.$this->data['delivery_addr2']."\n";
					$y = $pdf->GetY()-1;
					$pdf->SetXY(20, $y);
					$pdf->MultiCell(170, 4, $this->sjis_conv("$otodoke"),0,"LT");

					//お届け日時などの表示
					$pdf->SetFont('SJIS', '', 8);
					$otodoke2 = "■ご注文日：".date("Y年m月d日",strtotime($this->data['orderDatetime']))."\n";
					$otodoke2.= "■受注番号：".$this->data['order_id']."\n";
					$otodoke2.= "■決済方法：".$this->data['payment_methodName']."\n";
					if($this->data['member_no']){
						$otodoke2.= "■会員区分：会員　ポイント残高：".$this->data['point']."pt\n";
					}
					if($this->data['delivery_date'] !="0000-00-00"){
						$otodoke2.= "■お届け日：".date("Y年m月d日",strtotime($this->data['delivery_date']))."\n";
					}

					$pdf->SetXY(120, $y);
					$pdf->MultiCell(170, 4, $this->sjis_conv("$otodoke2"),0,"LT");



					//商品詳細を出力
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 20, 108, 170,6);
					$pdf->SetFont('SJIS', '',8);
					$w1 = 65;
					$w2 = 45;
					$w3 = 10;
					$w4 = 50;
					$w6 = 110;
					$pdf->SetXY(20,108);
					$pdf->Cell($w1,6,$this->sjis_conv("[商品番号]商品名"),1,"",'L',0);
					$pdf->Cell($w2,6,$this->sjis_conv("単価"),1,"",'L',0);
					$pdf->Cell($w3,6,$this->sjis_conv("数量"),1,"",'L',0);
					$pdf->Cell($w4,6,$this->sjis_conv("価格"),1,"",'L',0);
					$pdf->Ln();
					//価格を税抜きに変更
					foreach($this->item as $v){
						$pdf->SetX(20);
						$price = $this->spacePadding($v['price'],8);
						$pdf->Cell($w1, 6, $this->sjis_conv($v['name']), 1, '',"RB");
						$pdf->Cell($w2, 6, $this->sjis_conv($price."円"), 1, "",'RB', 0);
						$pdf->Cell($w3, 6, $this->sjis_conv($v['quantity'] .$v['unit'] ), 1,"", 'RB', 0);
						$kakaku =$v['price'] *$v['quantity'] ;
						$kakaku = $this->spacePadding($kakaku,11);
						$pdf->Cell($w4, 6, $this->sjis_conv($kakaku."円"), 1, "",'RB', 0);
						$pdf->Ln();
					}
					//空白行を挿入
					$arr_size =count($this->item);

					if($postArr['kind'] == 5 || $postArr['kind'] == 6){
							$c = 10;
					}else{
						if($this->data['check_payment_end'] !=""){
							$c = 5;
						}else{
							$c = 10;
						}
					}
					if($arr_size <$c){
						$loop_size = $c-$arr_size;
					}
					for($i=0;$i<$loop_size;$i++){
						$pdf->SetX(20);
						$pdf->Cell($w1, 6, $this->sjis_conv(' '), 1, 'L', 0);
						$pdf->Cell($w2, 6, $this->sjis_conv(' '), 1, 'L', 0);
						$pdf->Cell($w3, 6, $this->sjis_conv(' '), 1, 'L', 0);
						$pdf->Cell($w4, 6, $this->sjis_conv(' '), 1, 'L', 0);
						$pdf->Ln();
					}
					$y = $pdf->GetY();
					//注意書き
					$pdf->SetXY(20,$y+3);
		$setumei = "お届けした商品に配送事故による汚れ、キズが生じた場合や\n
		ご商品の納品等は、直ちに良品と交換させていただきます。\n
		詳しくは、オンラインショップ内の「お買い物ガイド」\n
		もしくは、弊社カスタマーサポートセンターまでお問い合わせください。\n
		\n
		\n※ポイント残高は、オンラインショップ内のマイページをご確認ください。\n
		（会員登録済みのお客様に限ります。）\n
		\n
		◎お問い合わせ（カスタマーサポートセンター）\n
		メール：info@john-mary.com\n
		※メールでの受付は、24時間365日受付致します。\n
		営業時間：10:00〜18:00（土日祝・年末年始を除く）";
					$pdf->MultiCell(105, 2, $this->sjis_conv($setumei),0,"LT");

					if($this->data['subscriptionsTurn'] >0){
						$pdf->Ln();
						$pdf->SetX(20);
						$pdf->SetFont('SJIS', '', 10);
						$pdf->SetTextColor(103, 72, 24);
						$pdf->SetDrawColor(103, 72, 24);
						$pdf->MultiCell(105, 8, $this->sjis_conv("　".$this->data['course'].":".$this->data['subscriptionsTurn']."回目"),1,"LT");
						$pdf->SetFont('SJIS', '', 8);
						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetDrawColor(0, 0, 0);
					}




					//送料・ポイント・決済手数料・消費税・合計
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y+3), 60,5);
					$pdf->SetXY(130,$y+3);
					$pdf->Cell(60, 5, $this->sjis_conv('小計'), 1,"", 'C', 0);
					$pdf->Ln();
					$pdf->SetX(130);
					$pdf->Cell(60, 5, $this->sjis_conv($this->data['item_total']."円"), 1,"", 'C', 0);
					$pdf->Ln();

					$y = $pdf->GetY();
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
					$pdf->SetXY(130,$y);
					$pdf->Cell(60, 5, $this->sjis_conv('消費税'), 1,"", 'C', 0);
					$pdf->Ln();
					$pdf->SetX(130);
					$pdf->Cell(60, 5, $this->sjis_conv($this->data['tax_total']."円"), 1,"", 'C', 0);
					$pdf->Ln();

					$y = $pdf->GetY();
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
					$pdf->SetXY(130,$y);
					$pdf->Cell(60, 5, $this->sjis_conv('送料'), 1,"", 'C', 0);
					$pdf->Ln();
					$pdf->SetX(130);
					$pdf->Cell(60, 5, $this->sjis_conv($this->data['shipping_cost']."円"), 1,"", 'C', 0);
					$pdf->Ln();

					if(!empty($this->data['collect_cost'])){
						$y = $pdf->GetY();
						$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
						$pdf->SetXY(130,$y);
						$pdf->Cell(60, 5, $this->sjis_conv('決済手数料'), 1,"", 'C', 0);
						$pdf->Ln();
						$pdf->SetX(130);
						$pdf->Cell(60, 5, $this->sjis_conv($this->data['collect_cost']."円"), 1,"", 'C', 0);
						$pdf->Ln();
					}

					if(!empty($this->data['use_point'])){
						$y = $pdf->GetY();
						$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
						$pdf->SetXY(130,$y);
						$pdf->Cell(60, 5, $this->sjis_conv('ポイント利用額'), 1,"", 'C', 0);
						$pdf->Ln();
						$pdf->SetX(130);
						$pdf->Cell(60, 5, $this->sjis_conv("-".$this->data['use_point']."円"), 1,"", 'C', 0);
						$pdf->Ln();
					}
					if(!empty($this->data['off_price'])){
						$y = $pdf->GetY();
						$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
						$pdf->SetXY(130,$y);
						$pdf->Cell(60, 5, $this->sjis_conv('割引'), 1,"", 'C', 0);
						$pdf->Ln();
						$pdf->SetX(130);
						$pdf->Cell(60, 5, $this->sjis_conv($this->data['off_price']."円"), 1,"", 'C', 0);
						$pdf->Ln();
					}
					$y = $pdf->GetY();
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y+7), 60,5);
					$pdf->SetXY(130,$y+7);
					$pdf->Cell(60, 5, $this->sjis_conv('お買上金額'), 1,"", 'C', 0);
					$pdf->Ln();
					$pdf->SetX(130);
					$pdf->SetFont('SJIS', 'B', 10);
					$pdf->Cell(60, 6, $this->sjis_conv($this->data['seikyu_cost']."円"), 1,"", 'C', 0);
					$pdf->Ln();

					$pdf->Ln();
				}
				if($postArr['kind'] == 5 || $postArr['kind'] == 6){
				}else{
					if($this->data['check_payment_end'] !=""){
						//領収書部
						$x = 173;
						$y = 27;
						$pdf->Ln();
						$y = $pdf->GetY();
						$pdf->Line(0,$y,210,$y);
						$pdf->Ln();
						$pdf->SetFont('SJIS', 'B', 14);
						$pdf->Cell(105, 7, $this->sjis_conv('領収書'), 0, '', 0);
						$pdf->Ln();

						$pdf->Ln();
						$pdf->SetFont('SJIS', 'B', 12);
						$pdf->Cell(30, 7, $this->sjis_conv($c_name), 0, '', 0);
						$pdf->SetFont('SJIS', 'B', 9);
						$pdf->Cell(150, 7, $this->sjis_conv(date("Y",strtotime($this->data['check_payment_end']))."年".date("m月d日",strtotime($this->data['check_payment_end']))), 0, '', 0);
						$pdf->Ln();
						$y = $pdf->GetY();
						$pdf->Line(10,$y,90,$y);
						$pdf->Ln();
						$y = $pdf->GetY();
						$pdf->SetFont('SJIS', 'B', 14);
						$pdf->Image(BASEURL.'/uploads/obi.jpg', 10, $y, 180,10);
						$pdf->Cell(180, 10, $this->sjis_conv('     ¥').number_format($this->data['seikyu_cost']), 1,20,'LTRB', 0);
						$pdf->SetFont('SJIS', 'B', 10);
						$y = $pdf->GetY();
						$pdf->Cell(30, 7, $this->sjis_conv('但：品代として'), 0, '', 0);
						$pdf->Ln();
						$y = $pdf->GetY();

						//印鑑画像の表示
						if($postArr['kind'] != 6){
							if($this->_setting['company']['stamp']){
								$pdf->Image(BASEURL.$this->_setting['company']['stamp'], $x, $y, 25,25);
							}
						}
						$pdf->Text(130, $y , $this->sjis_conv($this->_setting['company']['company']));// 会社名
						$pdf->SetFont('SJIS', '', 9);
						$y = $pdf->GetY();
						$pdf->Text(130, $y+5 , $this->sjis_conv("〒".$this->_setting['company']['zip']));// 会社郵便番号
						$pdf->Text(130, $y+10 , $this->sjis_conv($this->_setting['company']['addr']));// 会社住所
						$pdf->Text(130, $y+15 , $this->sjis_conv($this->_setting['company']['addr2']));// 会社住所
						$pdf->Text(130, $y+20 , $this->sjis_conv("電話：".$this->_setting['company']['tel']));// 会社電話
						$pdf->Text(130, $y+25 , $this->sjis_conv("FAX ：".$this->_setting['company']['fax']));// 会社FAX
					}
				}
				// PDFをブラウザに送信
				ob_end_clean();
				$pdf->Output();




		}else{
			echo "bad request!!!!";
		}
	}


	public function csvAction(){
		$postArr = $this->_db->postArray();

		//GETパラメータを取得
		$keyword = $postArr['keyword'];
		//性別
		if($postArr['sex']){
			$sex = $postArr['sex'];
		}else{
			$sex = 0;
		}
		//生月
		if($postArr['birthM']){
			$birthM = $postArr['birthM'];
		}else{
			$birthM = 0;
		}
		//会員
		if($postArr['membership']){
			$membership = $postArr['membership'];
		}else{
			$membership = 0;
		}
		//商品
		if($postArr['item']){
			$item = $postArr['item'];
		}else{
			$item = 0;
		}
		//ポイント有効期限
		if($postArr['point']){
			$point = $postArr['point'];
		}else{
			$point = 0;
		}

		if($postArr['resive']){
			$resive = $postArr['resive'];
		}else{
			$resive = 0;
		}
		if($postArr['pay']){
			$pay = $postArr['pay'];
		}else{
			$pay = 0;
		}
		if($postArr['shipping']){
			$shipping = $postArr['shipping'];
		}else{
			$shipping = 0;
		}
		if($postArr['3dn']){
			$n3d = $postArr['3dn'];
		}else{
			$n3d = 0;
		}
		if($postArr['1mn']){
			$n1m = $postArr['1mn'];
		}else{
			$n1m = 0;
		}
		if($postArr['2mn']){
			$n2m = $postArr['2mn'];
		}else{
			$n2m = 0;
		}
		if($postArr['3mn']){
			$n3m = $postArr['3mn'];
		}else{
			$n3m = 0;
		}
		if($postArr['6mn']){
			$n6m = $postArr['6mn'];
		}else{
			$n6m = 0;
		}
		$start = $limit*$p;
		$parent = "";
		//受注リストの読み込み
		$user = $this->_db->getorders2($keyword,$this->_user->id,$resive,$pay,$shipping,$n3d,$n1m,$n2m,$n3m,$n6m,$sex,$birthM,$item,$membership,$point);
		//var_dump($user);


		// タイトル設定
		$csv = "";
		// コンテンツ設定
		foreach ($user as $value) {
			$csv .= $this->sjisconv($value['cus_mail']).",";//メール
			$csv .= $this->sjisconv($value['cus_name'])." ".$this->sjisconv($value['cus_name2']).",";//名前
			$csv .= $this->sjisconv($value['cus_zip']."-".$value['cus_zip2']).",";//〒
			foreach($this->pref as $v){
				if($v['id'] == $value['cus_pref']){
					$cus_pref_name = $v['name'];
				}
			}
			$csv .= $this->sjisconv($cus_pref_name).$this->sjisconv($value['cus_addr'])." ".$this->sjisconv($value['cus_addr2'])."\n";
		}

		// CSVのHTTPヘッダ
		header("Content-Type: application/octet-stream; charset=sjis-win");
		header("Content-Disposition: attachment; filename=order.csv");
		echo $csv;
	}











	/*##############　これ以降は、今後の機能追加分として使用する可能性がある　###############*/
	//CSVインポート
	public function importAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　受注CSV一括登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/import"　class="btn disabled">　'.$this->view->title.'</a>
													</li>';

	}
	//CSVインポート完了
	public function importfinishAction() {
		//タイトルの定義
		$this->view->title = "受注CSV一括登録完了";
		$this->view->title = '<i class="fa fa-gift"></i>　受注CSV一括登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order"><i class="fa fa-gift"></i>　受注管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/import"><i class="fa fa-gift"></i>　受注CSV一括登録</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/order/import"　class="btn disabled">　'.$this->view->title.'</a>
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
					$this->view->Msg.="<p><a href=\"".BASEURL."/admin/order/\">受注管理画面へ進む</a></p>";
				} else {
					$this->view->Msg= "ファイルをアップロードできません。";
				}
			} else {
				$this->view->Msg= "ファイルが選択されていません。";
			}
	}

	public function uploadAction() {
		if(is_uploaded_file($path = $_FILES['upload_image']['tmp_name'])){
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
			$upload_dir2 = 'img/';
			$table = "image";
			$param = array(
				"url"=>$upload_dir2.$filename,
				"order"=>$this->getRequest()->getPost('id')
			);
			$this->_db->insert($table,$param);
			$id = $this->_db->lastInsertId($table);
			$this->view->id = $id;
			$this->view->filename = $filename;
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
			if($this->_db->update("order",array("addHtml"=>$postArr['addHtml']),$where)){
				$this->view->success = "登録が完了しました。";
			}else{
				$this->view->error = "登録が完了できませんでした。";
			}
			$this->view->data = $this->_db->getorderDetail($postArr['id']);
		}
	}
	//注文用ページャーの生成
	private function orderpager($keyword="",$n=0,$limit=10,$p=0,$page,$sex,$birthM,$membership,$item,$point,$resive,$pay,$shipping,$n3d,$n1m,$n2m,$n3m,$n6m,$gene){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$prev."&sex=".$sex."&birthM=".$birthM."&membership=".$membership."&item=".$item."&point=".$point."&resive=".$resive."&pay=".$pay."&shipping=".$shipping."&3dn=".$n3d."&1mn=".$n1m."&2mn=".$n2m."&3mn=".$n3m."&6mn=".$n6m."&gene=".$gene."\"><前へ</a></li>";
			}
			if($p > 5){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=0&sex=".$sex."&birthM=".$birthM."&membership=".$membership."&item=".$item."&point=".$point."&resive=".$resive."&pay=".$pay."&shipping=".$shipping."&3dn=".$n3d."&1mn=".$n1m."&2mn=".$n2m."&3mn=".$n3m."&6mn=".$n6m."&gene=".$gene."\"><<最初へ</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+5 || $i < $p-5){
				}else{
					if($i == $p){
						$avtive = " class=\"active\"";
					}else{
						$avtive = "";
					}
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$i."&sex=".$sex."&birthM=".$birthM."&membership=".$membership."&item=".$item."&point=".$point."&resive=".$resive."&pay=".$pay."&shipping=".$shipping."&3dn=".$n3d."&1mn=".$n1m."&2mn=".$n2m."&3mn=".$n3m."&6mn=".$n6m."&gene=".$gene."\">".$pn."</a></li>";
				}
			}
			if($p+5 < ceil($n/$limit)){
				$pager.= "<li class=\"active\" ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".ceil($n/$limit)."&sex=".$sex."&birthM=".$birthM."&membership=".$membership."&item=".$item."&point=".$point."&resive=".$resive."&pay=".$pay."&shipping=".$shipping."&3dn=".$n3d."&1mn=".$n1m."&2mn=".$n2m."&3mn=".$n3m."&6mn=".$n6m."&gene=".$gene."\">…</a></li>";
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."&sex=".$sex."&birthM=".$birthM."&membership=".$membership."&item=".$item."&point=".$point."&resive=".$resive."&pay=".$pay."&shipping=".$shipping."&3dn=".$n3d."&1mn=".$n1m."&2mn=".$n2m."&3mn=".$n3m."&6mn=".$n6m."&gene=".$gene."\">最後へ（".ceil($n/$limit)."）>></a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$next."&sex=".$sex."&birthM=".$birthM."&membership=".$membership."&item=".$item."&point=".$point."&resive=".$resive."&pay=".$pay."&shipping=".$shipping."&3dn=".$n3d."&1mn=".$n1m."&2mn=".$n2m."&3mn=".$n3m."&6mn=".$n6m."&gene=".$gene."\"><span aria-hidden=\"true\">次&raquo;</span></a></li>";
			}
			$pager.= "</ul></nav>";
			$this->view->pager = $pager;
		}
	}
	private function sjisconv($v){
		return mb_convert_encoding($v,"sjis-win","utf-8");
	}
	private function sjis_conv($conv_str) {
		return (mb_convert_kana(mb_convert_encoding($conv_str, "SJIS", SC_CHAR),"RNA") );
	}
	private function spacePadding($n,$c) {
		$cnt = $c+5-strlen($n);
		$sp="";
		for($i=0;$i<$cnt;$i++){
			$sp.=" ";
		}
		return $sp.$n;
	}
	private function printPdf($kind = 1,$date = 0,$data=array(),$item=array(),$user=array(),$company=array()){
		if($date ==0){
			$date = date("Y-m-d");
		}
		//FPDFライブリの読み込み
		require_once 'PEAR/fpdf/japanese.php';
		mb_language("ja");
		mb_internal_encoding('SJIS');
		define(SC_CHAR, "UTF-8");
		if($kind == 4 || $kind == 5 || $kind == 6 || $kind == 7){
			//########## 納品書・請求書・見積書 ##########
			$pdf=new PDF_Japanese();
			// 自スクリプトの文字コード
			// インスタンス作成
			$pdf = new PDF_Japanese('P', 'mm', 'A4');
			// SJISフォント(MSPGothicを使用)
			$pdf->AddSJISFont2();
			// 書き込み開始
			$pdf->Open();
			// フォントのセット ※SJIS(MSPGothic)でフォントサイズ10
			// ページを追加(新規ページ)
			$pdf->AddPage();
			$pdf->SetFont('SJIS', 'BU', 14);
			//帳票タイトルの表示
			if($kind == 4){
				$headding = "お買上げ明細書";
			}elseif($kind == 5 || $kind == 6){
				$headding = "御請求書";
			}else{
				$headding = "御見積書";
			}
			$pdf->Text(170, 12, sjis_conv($headding));//headding
			//日付の表示
			$pdf->SetFont('SJIS', '', 9);
			$p_date_arr = explode("-",$date);
			$yyyy = $p_date_arr[0];
			$mm = $p_date_arr[1];
			$dd = $p_date_arr[2];
			$jo_time = "発行日：".date("Y年m月d日",mktime(0,0,0, $mm,$dd,$yyyy));// 発行日
			$pdf->Text(130, 23.5, sjis_conv($jo_time));
			$pdf->SetFont('SJIS', '', 12);
			$x = 173;
			$y = 27;
			//印鑑画像の表示
			//if($kind != 6){
			//	if($company['stamp']){
			//		$pdf->Image(BASEURL.$company['stamp'], $x, $y, 25,25);
			//	}
			//}
			//自社データの表示
			$pdf->Text(130, 30, sjis_conv($company['company']));// 会社名
			$pdf->SetFont('SJIS', '', 9);
			$pdf->Text(130, 35, sjis_conv("〒".$company['zip']));// 会社郵便番号
			$pdf->Text(130, 38.5, sjis_conv($company['addr']));// 会社住所
			$pdf->Text(130, 42, sjis_conv($company['addr2']));// 会社住所
			$pdf->Text(130, 45, sjis_conv("電話：".$company['tel']));// 会社電話
			$pdf->Text(130, 48, sjis_conv("FAX ：".$company['fax']));// 会社FAX
			//$pdf->Text(130, 48, sjis_conv("Mail：".$user->mail));// 会社Mail
			//$pdf->Text(130, 53, sjis_conv("担当：".$company->name));// 会社Mail
			//注文主データ　
			$pdf->SetFont('SJIS', '',9);
			$oy_post = "〒".$data['cus_zip']."-".$data['cus_zip2'];
			$pdf->Text(28,22, sjis_conv($oy_post));// 注文主郵便番号
			$pdf->SetXY(28, 24);
			$pdf->MultiCell(70, 4, sjis_conv($data['cus_prefName'].$data['cus_addr']."\n".$data['cus_addr2']));// 注文主住所
			$pdf->SetFont('SJIS', '', 12);
			$c_name = $data['cus_name']." ".$data['cus_name2']."様";
			$pdf->SetXY(28, 35);
			$pdf->MultiCell(70, 4.5, sjis_conv($c_name));
			//$pdf->Image($image_file_path,28,43,51.1,10,"PNG");//バーコードを出力

			$pdf->SetFont('SJIS', '',9);
			if($kind == 4){
			$pdf->Text(10,63, sjis_conv("このたびはジョン&マリーでお買上げいただき、誠にありがとうございます。丁寧に作られたオーガニック製品の心地よさを"));
			$pdf->Text(10,68, sjis_conv("存分にお楽しみください。"));
			}elseif($kind == 5 || $kind == 6){
			$pdf->Text(10,63, sjis_conv("毎々、格別なるお引き立てに預かり、厚く御礼申し上げます。下記の通りご請求申し上げます。"));
			}else{
			$pdf->Text(10,63, sjis_conv("毎々、格別なるお引き立てに預かり、厚く御礼申し上げます。下記の通りご請求申し上げます。"));
			}

			$pdf->SetFont('SJIS', 'BU', 12);
			$pdf->Text(10,74, sjis_conv("お買上げ金額：".number_format($data['seikyu_cost'])."円"));// 請求金額


			//商品詳細を出力
			$pdf->Image(BASEURL.'/uploads/obi.jpg', 10, 75, 190,7);
			$pdf->SetFont('SJIS', '',9);
			$w1 = 75;
			$w2 = 45;
			$w3 = 10;
			$w4 = 60;
			$w6 = 130;
			$pdf->SetY(75);
			$pdf->Cell($w1,7,sjis_conv("[商品番号]商品名"),1,'L',"L");
			$pdf->Cell($w2,7,sjis_conv("単価"),1,"",'L',0);
			$pdf->Cell($w3,7,sjis_conv("数量"),1,"",'L',0);
			$pdf->Cell($w4,7,sjis_conv("価格"),1,"",'L',0);
			$pdf->Ln();
			//価格を税抜きに変更
			foreach($item as $v){
				$price = spacePadding($v['price'],7);
				$pdf->Cell($w1, 7, sjis_conv($v['name']), 1, '',"RB");
				$pdf->Cell($w2, 7, sjis_conv($price."円"), 1, "",'RB', 0);
				$pdf->Cell($w3, 7, sjis_conv($v['quantity'] .$v['unit'] ), 1,"", 'RB', 0);
				$kakaku =$v['price'] *$v['quantity'] ;
				$kakaku = spacePadding($kakaku,12);
				$pdf->Cell($w4, 7, sjis_conv($kakaku."円"), 1, "",'RB', 0);
				$pdf->Ln();
			}
			//空白行を挿入
			$arr_size =count($item);

			if($kind == 5 || $kind == 6){
					$c = 10;
			}else{
				if($data['check_payment_end'] !=""){
					$c = 5;
				}else{
					$c = 10;
				}
			}
			if($arr_size <$c){
				$loop_size = $c-$arr_size;
			}
			for($i=0;$i<$loop_size;$i++){
				$pdf->Cell($w1, 7, sjis_conv(' '), 1, 'L', 0);
				$pdf->Cell($w2, 7, sjis_conv(' '), 1, 'L', 0);
				$pdf->Cell($w3, 7, sjis_conv(' '), 1, 'L', 0);
				$pdf->Cell($w4, 7, sjis_conv(' '), 1, 'L', 0);
				$pdf->Ln();
			}
			$pdf->Cell($w6,7,sjis_conv("小計"),1,'L',0);
			$pdf->Cell($w4,7,sjis_conv(spacePadding($data['item_total'],12)."円"),1,"",'RB',0);
			$pdf->Ln();
			$pdf->Cell($w6,7,sjis_conv("消費税"),1,'L',0);
			$pdf->Cell($w4,7,sjis_conv(spacePadding($data['tax_total'],12)."円"),1,"",'RB',0);
			$pdf->Ln();
			$pdf->Cell($w6,7,sjis_conv("送料"),1,'L',0);
			$pdf->Cell($w4,7,sjis_conv(spacePadding($data['shipping_cost'],12)."円"),1,"",'RB',0);
			$pdf->Ln();
			if(!empty($data['collect_cost'])){
				$pdf->Cell($w6,7,sjis_conv("代引手数料"),1,'L',0);
				$pdf->Cell($w4,7,sjis_conv(spacePadding($data['collect_cost'],12)."円"),1,"",'RB',0);
				$pdf->Ln();
			}
			if(!empty($data['use_point'])){
				$data['use_point'] = "-".$data['use_point'];
				$pdf->Cell($w6,7,sjis_conv("ポイント使用"),1,'L',0);
				$pdf->Cell($w4,7,sjis_conv(spacePadding($data['use_point'],12)."円"),1,"",'RB',0);
				$pdf->Ln();
			}
			if(!empty($data['off_price'])){
				$data['off_price'] = "-".$data['off_price'];
				$pdf->Cell($w6,7,sjis_conv("割引"),1,'L',0);
				$pdf->Cell($w4,7,sjis_conv(spacePadding($data['off_price'],12)."円"),1,"",'RB',0);
				$pdf->Ln();
			}
			$pdf->Cell($w6,7,sjis_conv("合計"),1,'L',0);
			$pdf->Cell($w4,7,sjis_conv(spacePadding($data['seikyu_cost'],12)."円"),1,"",'RB',0);
			$pdf->Ln();
			$pdf->Ln();

			$y = $pdf->GetY();
			$pdf->SetXY(10, $y);
			$otodoke = "備考";
			$otodoke = "お届け先様："
			.$data['delivery_name']
			."　".$data['delivery_name2']
			."(".$data['delivery_kana'].$data['delivery_kana2'].")様\n〒"
			.$data['delivery_zip']."-".$data['delivery_zip2']." "
			.$data['delivery_prefName']
			.$data['delivery_addr']
			.$data['delivery_addr2']."\n";
			//if(!empty($data['delivery_date'])){
			//	$d_date_arr = explode("-",$data['delivery_date']);
			//	$d_date_str = date("Y年m月d日",mktime(0,0,0,$d_date_arr[1],$d_date_arr[2],$d_date_arr[0]));
			//}else{
			//	$d_date_str = "ご指定がございません";
			//}
			//$otodoke.= "お届け日時指定：".$d_date_str." ".$data['delivery_time'];
			$pdf->MultiCell(190, 6, sjis_conv("$otodoke"),1,"LT");
			$pdf->Ln();
			if($kind == 4){
				$y = $pdf->GetY();
				$pdf->MultiCell(190, 6, sjis_conv($company['goodsFooter']),1,"LT");
			}elseif($kind == 5 || $kind == 6){
				$y = $pdf->GetY();
				$pdf->MultiCell(190, 6, sjis_conv($company['demandFooter']),1,"LT");
			}elseif($kind == 7){
				$y = $pdf->GetY();
				$pdf->MultiCell(190, 6, sjis_conv($company['esitmateFooter']),1,"LT");
			}
		}
		if($kind == 8){
			//########## 納品書・請求書・見積書 ##########
			$pdf=new PDF_Japanese();
			// 自スクリプトの文字コード
			// インスタンス作成
			$pdf = new PDF_Japanese('P', 'mm', 'A4');
			// SJISフォント(MSPGothicを使用)
			$pdf->AddSJISFont2();
			// 書き込み開始
			$pdf->Open();
			// フォントのセット ※SJIS(MSPGothic)でフォントサイズ10
			// ページを追加(新規ページ)
			$pdf->AddPage();
			$pdf->SetFont('SJIS', 'B', 12);

			//商品詳細を出力
			$pdf->Image(BASEURL.'/img/logo.jpg', 80, 20, 50,11.7);
			//帳票タイトルの表示
			$headding = "お買上げ明細書";
			$pdf->Text(91, 40, sjis_conv($headding));//headding
			$pdf->SetFont('SJIS', '', 10);
			$c_name = $data['cus_name']." ".$data['cus_name2']."様";
			$pdf->SetXY(20, 45);
			$pdf->MultiCell(70, 4.5, sjis_conv($c_name));
			$pdf->SetFont('SJIS', '', 8);
			$pdf->SetXY(20, 53);
			$str = "このたびはジョン&マリーでお買上げいただき、誠にありがとうございます。\n丁寧に作られたオーガニック製品の心地よさを存分にお楽しみください。";
			if($data['c']==1 && $data['member_no']){
				$pdf->Text(21, 70, sjis_conv( "新規会員登録ありがとうございます。"));// 会社名
			}
			$pdf->MultiCell(105, 4.5, sjis_conv($str));
			//自社データの表示
			$pdf->SetFont('SJIS', '', 12);
			$pdf->Text(130, 57, sjis_conv($company['company']));// 会社名
			$pdf->SetFont('SJIS', '', 9);
			$pdf->Text(130, 62, sjis_conv("〒".$company['zip']));// 会社郵便番号
			$pdf->Text(130, 65.5, sjis_conv($company['addr']));// 会社住所
			$pdf->Text(130, 69, sjis_conv($company['addr2']));// 会社住所
			$pdf->Text(130, 73, sjis_conv("電話：".$company['tel']));// 会社電話
			$pdf->Text(130, 77, sjis_conv("FAX ：".$company['fax']));// 会社FAX
			$pdf->Image(BASEURL.'/uploads/obi.jpg', 20, 82, 170,5);
			$pdf->SetFont('SJIS', '', 10);
			$pdf->Text(25, 85.5, sjis_conv("お買上げ明細"));


			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			//お届け先の表示
			$pdf->SetFont('SJIS', '', 8);
			$otodoke = "■お届け先様\n"
			."　　　".$data['delivery_name']
			."　".$data['delivery_name2']
			."(".$data['delivery_kana'].$data['delivery_kana2'].")様\n"
			."　　　〒".$data['delivery_zip']."-".$data['delivery_zip2']."\n"
			."　　　".$data['delivery_prefName']
			.$data['delivery_addr']
			.$data['delivery_addr2']."\n";
			$y = $pdf->GetY()-1;
			$pdf->SetXY(20, $y);
			$pdf->MultiCell(170, 4, sjis_conv("$otodoke"),0,"LT");

			//お届け日時などの表示
			$pdf->SetFont('SJIS', '', 8);
			$otodoke2 = "■ご注文日：".date("Y年m月d日",strtotime($data['orderDatetime']))."\n";
			$otodoke2.= "■受注番号：".$data['order_id']."\n";
			$otodoke2.= "■決済方法：".$data['payment_methodName']."\n";
			if($data['member_no']){
				$otodoke2.= "■会員区分：会員　ポイント残高：".$data['point']."pt\n";
			}
			if($data['delivery_date'] !="0000-00-00"){
				$otodoke2.= "■お届け日：".date("Y年m月d日",strtotime($data['delivery_date']))."\n";
			}

			$pdf->SetXY(120, $y);
			$pdf->MultiCell(170, 4, sjis_conv("$otodoke2"),0,"LT");



			//商品詳細を出力
			$pdf->Image(BASEURL.'/uploads/obi.jpg', 20, 108, 170,6);
			$pdf->SetFont('SJIS', '',8);
			$w1 = 65;
			$w2 = 45;
			$w3 = 10;
			$w4 = 50;
			$w6 = 110;
			$pdf->SetXY(20,108);
			$pdf->Cell($w1,6,sjis_conv("[商品番号]商品名"),1,"",'L',0);
			$pdf->Cell($w2,6,sjis_conv("単価"),1,"",'L',0);
			$pdf->Cell($w3,6,sjis_conv("数量"),1,"",'L',0);
			$pdf->Cell($w4,6,sjis_conv("価格"),1,"",'L',0);
			$pdf->Ln();
			//価格を税抜きに変更
			foreach($item as $v){
				$pdf->SetX(20);
				$price = spacePadding($v['price'],8);
				$pdf->Cell($w1, 6, sjis_conv($v['name']), 1, '',"RB");
				$pdf->Cell($w2, 6, sjis_conv($price."円"), 1, "",'RB', 0);
				$pdf->Cell($w3, 6, sjis_conv($v['quantity'] .$v['unit'] ), 1,"", 'RB', 0);
				$kakaku =$v['price'] *$v['quantity'] ;
				$kakaku = spacePadding($kakaku,11);
				$pdf->Cell($w4, 6, sjis_conv($kakaku."円"), 1, "",'RB', 0);
				$pdf->Ln();
			}
			//空白行を挿入
			$arr_size =count($item);

			if($kind == 5 || $kind == 6){
					$c = 10;
			}else{
				if($data['check_payment_end'] !=""){
					$c = 5;
				}else{
					$c = 10;
				}
			}
			if($arr_size <$c){
				$loop_size = $c-$arr_size;
			}
			for($i=0;$i<$loop_size;$i++){
				$pdf->SetX(20);
				$pdf->Cell($w1, 6, sjis_conv(' '), 1, 'L', 0);
				$pdf->Cell($w2, 6, sjis_conv(' '), 1, 'L', 0);
				$pdf->Cell($w3, 6, sjis_conv(' '), 1, 'L', 0);
				$pdf->Cell($w4, 6, sjis_conv(' '), 1, 'L', 0);
				$pdf->Ln();
			}
			$y = $pdf->GetY();
			//注意書き
			$pdf->SetXY(20,$y+3);
$setumei = "お届けした商品に配送事故による汚れ、キズが生じた場合や\n
ご商品の納品等は、直ちに良品と交換させていただきます。\n
詳しくは、オンラインショップ内の「お買い物ガイド」\n
もしくは、弊社カスタマーサポートセンターまでお問い合わせください。\n
\n
\n※ポイント残高は、オンラインショップ内のマイページをご確認ください。\n
（会員登録済みのお客様に限ります。）\n
\n
◎お問い合わせ（カスタマーサポートセンター）\n
メール：info@john-mary.com\n
※メールでの受付は、24時間365日受付致します。\n
営業時間：10:00〜18:00（土日祝・年末年始を除く）";
			$pdf->MultiCell(105, 2, sjis_conv($setumei),0,"LT");

			if($data['subscriptionsTurn'] >0){
				$pdf->Ln();
				$pdf->SetX(20);
				$pdf->SetFont('SJIS', '', 10);
				$pdf->SetTextColor(103, 72, 24);
				$pdf->SetDrawColor(103, 72, 24);
				$pdf->MultiCell(105, 8, sjis_conv("　".$data['course'].":".$data['subscriptionsTurn']."回目"),1,"LT");
				$pdf->SetFont('SJIS', '', 8);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetDrawColor(0, 0, 0);
			}




			//送料・ポイント・決済手数料・消費税・合計
			$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y+3), 60,5);
			$pdf->SetXY(130,$y+3);
			$pdf->Cell(60, 5, sjis_conv('小計'), 1,"", 'C', 0);
			$pdf->Ln();
			$pdf->SetX(130);
			$pdf->Cell(60, 5, sjis_conv($data['item_total']."円"), 1,"", 'C', 0);
			$pdf->Ln();

			$y = $pdf->GetY();
			$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
			$pdf->SetXY(130,$y);
			$pdf->Cell(60, 5, sjis_conv('消費税'), 1,"", 'C', 0);
			$pdf->Ln();
			$pdf->SetX(130);
			$pdf->Cell(60, 5, sjis_conv($data['tax_total']."円"), 1,"", 'C', 0);
			$pdf->Ln();

			$y = $pdf->GetY();
			$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
			$pdf->SetXY(130,$y);
			$pdf->Cell(60, 5, sjis_conv('送料'), 1,"", 'C', 0);
			$pdf->Ln();
			$pdf->SetX(130);
			$pdf->Cell(60, 5, sjis_conv($data['shipping_cost']."円"), 1,"", 'C', 0);
			$pdf->Ln();

			if(!empty($data['collect_cost'])){
				$y = $pdf->GetY();
				$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
				$pdf->SetXY(130,$y);
				$pdf->Cell(60, 5, sjis_conv('決済手数料'), 1,"", 'C', 0);
				$pdf->Ln();
				$pdf->SetX(130);
				$pdf->Cell(60, 5, sjis_conv($data['collect_cost']."円"), 1,"", 'C', 0);
				$pdf->Ln();
			}

			if(!empty($data['use_point'])){
				$y = $pdf->GetY();
				$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
				$pdf->SetXY(130,$y);
				$pdf->Cell(60, 5, sjis_conv('ポイント利用額'), 1,"", 'C', 0);
				$pdf->Ln();
				$pdf->SetX(130);
				$pdf->Cell(60, 5, sjis_conv("-".$data['use_point']."円"), 1,"", 'C', 0);
				$pdf->Ln();
			}
			if(!empty($data['off_price'])){
				$y = $pdf->GetY();
				$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
				$pdf->SetXY(130,$y);
				$pdf->Cell(60, 5, sjis_conv('割引'), 1,"", 'C', 0);
				$pdf->Ln();
				$pdf->SetX(130);
				$pdf->Cell(60, 5, sjis_conv($data['off_price']."円"), 1,"", 'C', 0);
				$pdf->Ln();
			}
			$y = $pdf->GetY();
			$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y+7), 60,5);
			$pdf->SetXY(130,$y+7);
			$pdf->Cell(60, 5, sjis_conv('お買上金額'), 1,"", 'C', 0);
			$pdf->Ln();
			$pdf->SetX(130);
			$pdf->SetFont('SJIS', 'B', 10);
			$pdf->Cell(60, 6, sjis_conv($data['seikyu_cost']."円"), 1,"", 'C', 0);
			$pdf->Ln();

			$pdf->Ln();
		}
		if($kind == 5 || $kind == 6){
		}else{
			if($data['check_payment_end'] !=""){
				//領収書部
				$x = 173;
				$y = 27;
				$pdf->Ln();
				$y = $pdf->GetY();
				$pdf->Line(0,$y,210,$y);
				$pdf->Ln();
				$pdf->SetFont('SJIS', 'B', 14);
				$pdf->Cell(105, 7, sjis_conv('領収書'), 0, '', 0);
				$pdf->Ln();

				$pdf->Ln();
				$pdf->SetFont('SJIS', 'B', 12);
				$pdf->Cell(30, 7, sjis_conv($c_name), 0, '', 0);
				$pdf->SetFont('SJIS', 'B', 9);
				$pdf->Cell(150, 7, sjis_conv(date("Y",strtotime($data['check_payment_end']))."年".date("m月d日",strtotime($data['check_payment_end']))), 0, '', 0);
				$pdf->Ln();
				$y = $pdf->GetY();
				$pdf->Line(10,$y,90,$y);
				$pdf->Ln();
				$y = $pdf->GetY();
				$pdf->SetFont('SJIS', 'B', 14);
				$pdf->Image(BASEURL.'/uploads/obi.jpg', 10, $y, 180,10);
				$pdf->Cell(180, 10, sjis_conv('     ¥').number_format($data['seikyu_cost']), 1,20,'LTRB', 0);
				$pdf->SetFont('SJIS', 'B', 10);
				$y = $pdf->GetY();
				$pdf->Cell(30, 7, sjis_conv('但：品代として'), 0, '', 0);
				$pdf->Ln();
				$y = $pdf->GetY();

				//印鑑画像の表示
				if($kind != 6){
					if($company['stamp']){
						$pdf->Image(BASEURL.$company['stamp'], $x, $y, 25,25);
					}
				}
				$pdf->Text(130, $y , sjis_conv($company['company']));// 会社名
				$pdf->SetFont('SJIS', '', 9);
				$y = $pdf->GetY();
				$pdf->Text(130, $y+5 , sjis_conv("〒".$company['zip']));// 会社郵便番号
				$pdf->Text(130, $y+10 , sjis_conv($company['addr']));// 会社住所
				$pdf->Text(130, $y+15 , sjis_conv($company['addr2']));// 会社住所
				$pdf->Text(130, $y+20 , sjis_conv("電話：".$company['tel']));// 会社電話
				$pdf->Text(130, $y+25 , sjis_conv("FAX ：".$company['fax']));// 会社FAX
			}
		}
		// PDFをブラウザに送信
		ob_end_clean();
		$pdf->Output();
		/*
		echo 123;
		*/
	}
}
?>
