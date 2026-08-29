<?php
//コンポーネントのロード
class OrderController extends Common_IndexController {
	//初期化メソッドの定義
	private $_db;
	public $_user;
	public $_setting;
	public function init(){
		$this->_db = new Model_Indexitem();
		//設定の読み込み
		$setting = new Model_Indexsettings;
		$this->_setting = $setting->setting("88");
		$this->view->setting =$this->_setting;
		//都道府県一覧取得
		$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
		
		$this->view->pref = $this->pref;
		/**
		*/
		$auth = Zend_Auth::getInstance();
		$this->_user = $auth->getIdentity();
		$this->view->user =$this->_user;
	}
	public function indexAction() {
		$this->view->title = '<i class="icon-shopping-cart"></i> ショッピングカート';
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title=" '.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>トップ</a></li>
													<li>'.$this->view->title.'</li>';
		$myNamespace = new Zend_Session_Namespace('items');
		$data = array();
		
		//ログイン状態の場合は、登録情報を顧客情報に挿入
		if(!empty($this->_user)){
			$data['cus_campany'] = $this->_user->company;
			$data['cus_div'] = $this->_user->division;
			$data['cus_name'] = $this->_user->name;
			$data['cus_name2'] = $this->_user->name2;
			$data['cus_kana'] = $this->_user->kana;
			$data['cus_kana2'] = $this->_user->kana2;
			$data['cus_mail'] = $this->_user->mail;
			$data['cus_tel'] = $this->_user->tel;
			$data['cus_tel2'] = $this->_user->tel2;
			$data['cus_tel3'] = $this->_user->tel3;
			$data['cus_mtel'] = $this->_user->mtel;
			$data['cus_mtel2'] = $this->_user->mtel2;
			$data['cus_mtel3'] = $this->_user->mtel3;
			$data['cus_fax'] = $this->_user->fax;
			$data['cus_zip'] = $this->_user->zip;
			$data['cus_zip2'] = $this->_user->zip2;
			$data['cus_pref'] = $this->_user->pref;
			$data['cus_addr'] = $this->_user->addr;
			$data['cus_addr2'] = $this->_user->addr2;
			$data['cus_sex'] = $this->_user->sex;
			$data['cus_birthY'] = $this->_user->birthY;
			$data['cus_birthM'] = $this->_user->birthM;
			$data['cus_birthD'] = $this->_user->birthD;
		}
		if($this->_request->isGet()){
			//注文履歴からの注文
			if($this->_request->getQuery('order')){
				$getArr = $this->_db->getArray();
				$order = $this->_db->fetchAll(
					$this->_db->select()
					->from("order")
					->where('`id`=?',$getArr['order'])
					->limit(1)
				);
				foreach($order as $k=>$v){
					$order_item = $this->_db->fetchAll(
						$this->_db->select()
						->from("orderItem")
						->where("orderId=?",$v['id'])
					);
					$order[$k]['item'] = $order_item;
				}
				foreach($order[0]['item'] as $vv){
					//商品数量が１以上の場合は、セッション情報を書き換え
					if(is_null($myNamespace->items)){
						var_dump($data);
						//商品がセッションに保存されていない場合
						$myNamespace->items[] = $this->getItem($vv['number'],$vv['quantity']);
					}else{
						//var_dump($data);
						//商品がセッションに保存されてる場合
						$exists = false;
						foreach($myNamespace->items as $k =>$v){
							if($v['number'] ==$vv['number']){
								//同一商品がセッションに存在する場合
								$myNamespace->items[$k]['quantity'] = intval($vv['quantity']);
								$exists = true;
							}
						}
						if($exists == false){
							//var_dump($data);
							//同一商品がセッションに存在しない場合
							$myNamespace->items[] = $this->getItem($vv['number'],$vv['quantity']);
						}
					}
				}
				$data = $order[0];
			}
		}
		
		if($this->_request->isPost()){
			$postArr =$this->_db->postArray();
			if($postArr['edit']){
				//echo $postArr['edit'];
				$data['cus_campany'] = $postArr['cus_campany'];
				$data['cus_div'] = $postArr['cus_div'];
				$data['cus_name'] = $postArr['cus_name'];
				$data['cus_name2'] = $postArr['cus_name2'];
				$data['cus_kana'] = $postArr['cus_kana'];
				$data['cus_kana2'] = $postArr['cus_kana2'];
				$data['cus_mail'] = $postArr['cus_mail'];
				$data['cus_tel'] = $postArr['cus_tel'];
				$data['cus_tel2'] = $postArr['cus_tel2'];
				$data['cus_tel3'] = $postArr['cus_tel3'];
				$data['cus_mtel'] = $postArr['cus_mtel'];
				$data['cus_mtel2'] = $postArr['cus_mtel2'];
				$data['cus_mtel3'] = $postArr['cus_mtel3'];
				$data['cus_fax'] = $postArr['cus_fax'];
				$data['cus_zip'] = $postArr['cus_zip'];
				$data['cus_zip2'] = $postArr['cus_zip2'];
				$data['cus_pref'] = $postArr['cus_pref'];
				$data['cus_addr'] = $postArr['cus_addr'];
				$data['cus_addr2'] = $postArr['cus_addr2'];
				$data['cus_sex'] = $postArr['cus_sex'];
				$data['cus_birthY'] = $postArr['cus_birthY'];
				$data['cus_birthM'] = $postArr['cus_birthM'];
				$data['cus_birthD'] = $postArr['cus_birthD'];
				
				$data['delivery_campany'] = $postArr['delivery_campany'];
				$data['delivery_div'] = $postArr['delivery_div'];
				$data['delivery_name'] = $postArr['delivery_name'];
				$data['delivery_name2'] = $postArr['delivery_name2'];
				$data['delivery_kana'] = $postArr['delivery_kana'];
				$data['delivery_kana2'] = $postArr['delivery_kana2'];
				$data['delivery_tel'] = $postArr['delivery_tel'];
				$data['delivery_tel2'] = $postArr['delivery_tel2'];
				$data['delivery_tel3'] = $postArr['delivery_tel3'];
				$data['delivery_mtel'] = $postArr['delivery_mtel'];
				$data['delivery_mtel2'] = $postArr['delivery_mtel2'];
				$data['delivery_mtel3'] = $postArr['delivery_mtel3'];
				$data['delivery_fax'] = $postArr['delivery_fax'];
				$data['delivery_zip'] = $postArr['delivery_zip'];
				$data['delivery_zip2'] = $postArr['delivery_zip2'];
				$data['delivery_pref'] = $postArr['delivery_pref'];
				$data['delivery_addr'] = $postArr['delivery_addr'];
				$data['delivery_addr2'] = $postArr['delivery_addr2'];
				
				
				$data['delivery_date'] = $postArr['delivery_date'];
				$data['delivery_time'] = $postArr['delivery_time'];
				$data['payment_method'] = $postArr['payment_method'];
				$data['order_comment'] = $postArr['order_comment'];
			}
			if($postArr['quantity'] > 0){
				//商品数量が１以上の場合は、セッション情報を書き換え
				if(is_null($myNamespace->items)){
					//商品がセッションに保存されていない場合
					$myNamespace->items[] = $this->getItem($postArr['number'],$postArr['quantity']);
				}else{
					//商品がセッションに保存されてる場合
					$exists = false;
					foreach($myNamespace->items as $k =>$v){
						if($v['number'] ==$postArr['number']){
							//同一商品がセッションに存在する場合
							$myNamespace->items[$k]['q'] = intval($postArr['quantity']);
							$exists = true;
						}
					}
					if($exists == false){
						//同一商品がセッションに存在しない場合
						$myNamespace->items[] = $this->getItem($postArr['number'],$postArr['quantity']);
					}
				}
			}else{
				//商品数量が0以下の場合は、商品情報を削除
				foreach($myNamespace->items as $k =>$v){
					if($v['number'] ==$postArr['number']){
						//同一商品がセッションに存在する場合
						unset($myNamespace->items[$k]);
					}
				}
			}
		}
		//↓セッションを消したいときだけ有効化（エラーが出る）
		//Zend_Session::destroy();
		$this->view->item = $myNamespace->items;
		if(count($myNamespace->items)>0){
			$c =0;
			$t =0;
			foreach($myNamespace->items as $v){
				$c += $v['q'];
				$t += $v['price']*$v['q'];
			}
			$this->view->c = $c;
			$this->view->t = $t;
			if($this->_setting['tax']['rule'] ==0){
				$this->view->tax = round($t*(1+$this->_setting['tax']['ratio']/100)-$t);
			}elseif($this->setting['tax']['rule'] ==1){
				$this->view->tax = ceil($t*(1+$this->_setting['tax']['ratio']/100)-$t);
			}else{
				$this->view->tax = floor($t*(1+$this->_setting['tax']['ratio']/100)-$t);
			}
		}
		$this->view->detail = $data;
	}
	public function confurmAction() {
		$this->view->title = '<i class="icon-shopping-cart"></i> ショッピングカート内容の確認';
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title=" '.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>トップ</a></li>
													<li>'.$this->view->title.'</li>';
		
		if($this->_request->isPost()){
			$myNamespace = new Zend_Session_Namespace('items');
			$this->view->item = $myNamespace->items;
			$postArr = $this->_db->postArray();
			
			if(count($myNamespace->items)>0){
				$c =0;
				$t =0;
				foreach($myNamespace->items as $v){
					$c += $v['q'];
					$t += $v['price']*$v['q'];
				}
			}
			//送料計算
			foreach($myNamespace->items as $v){
				foreach($this->_setting['shipping'] as $vv){
					if($v['shipping'] == $vv['id']){
						$shippingCost = $vv['s'.$postArr['delivery_pref']];
					}
				}
			}
			//決済手数料
			$collect_cost = $this->_db->fetchAll(
				$this->_db->select()
				->from("payment_method","fee")
				->where("`id`=?",$postArr['payment_method'])
			);
			$collect_cost = $collect_cost[0]['fee']+$collect_cost[0]['fee']*$this->_setting['tax']['ratio']/100;
			//ポイントを引いた合計金額
			$s = $t;
			/*
			if($this->_setting['point']['pointuse'] ==0){
				$s = $t-$postArr['poinAmount'];
			}else{
				$s = $t;
			}
			*/
			//送料無料設定がある場合
			if($this->_setting['discount']['shippingDiscountUse'] == 0 && $this->_setting['discount']['shippingDiscountAmount'] <= $s){
				$shippingCost = 0;
				//$collect_cost =0; 2016.11.11 変更
			}
			$postArr['shipping'] = $shippingCost;
			$postArr['collect_cost'] = $collect_cost;
			$postArr['t'] = $t;
			$postArr['c'] = $c;
			//合計金額の算出
			if($this->_setting['point']['pointuse'] ==0){
				$postArr['total'] = $t+$postArr['tax']+$shippingCost+$collect_cost-$postArr['poinAmount'];
			}else{
				$postArr['total'] = $t+$postArr['tax']+$shippingCost+$collect_cost;
			}
			
			
			$this->view->detail = $postArr;
			
			//会員登録ができるか確認
			if(!$postArr['member_no']){
				$mem = $this->_db->fetchAll(
					$this->_db->select()
					->from("ImageUser","COUNT(*) as c")
					->where("mail=?",$postArr['cus_mail'])
					->limit(1)
				);
				$mem_exists = true;
				if($mem[0]['c'] == 0){
					$mem_exists = false;
				}
				$this->view->mem_exists = $mem_exists;
			}
		}
	}
	public function finishAction() {
		//タイトルとパンくずリストを設定
		$this->view->title = '<i class="icon-shopping-cart"></i> 注文完了';
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title=" '.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>トップ</a></li>
													<li>'.$this->view->title.'</li>';
		
		if($this->_request->isPost()){
			//セッションの商品情報を取得してビューへ送る
			$myNamespace = new Zend_Session_Namespace('items');
			$this->view->item = $myNamespace->items;
			$itemArr = $myNamespace->items;
			$postArr = $this->_db->postArray();
			
			$registration = true;//GMO取引登録チェック用フラグ
			$payment = true;//決済実行チェック用フラグ
			
			//GMO決済を使用するか判断
			foreach($this->_setting['payment'] as $v){
				if($v['id'] == $postArr['payment_method'] && $v['gmoFlug'] == 1){
					$credit = true;
					//ショップID・PWをDBから取得
					$ShopID = $v['gmoShopID'] ;
					$ShopPW = $v['gomShopPW'] ;
				}elseif($v['id'] == $postArr['payment_method'] && $v['gmoFlug'] == 2){
					$cvs = true;
					//ショップID・PWをDBから取得
					$ShopID = $v['gmoShopID'] ;
					$ShopPW = $v['gomShopPW'] ;
				}
			}
			//GMOクレジットカード決済を使用する場合
			if($credit == true){
				//GMOorder_idが存在しない場合は取引登録を行い、GMOorder_idをセットする
				if(!$postArr['GMOorder_id']){
					//決済用オーダーIDを作成
					$arrTime = explode('.',microtime(true));
					//$GMOorder_id = hash("sha512",date("iYsdHm").$arrTime[0]);
					$GMOorder_id = time().$arrTime[0];
					//パラメータを整形
					$data = array(
						"ShopID" => $ShopID,
						"ShopPass" => $ShopPW,
						"OrderID" => $GMOorder_id,
						"JobCd" => "CAPTURE",
						"Amount" => floor($postArr['total'])
					);
					$data = http_build_query($data, "", "&");
					//POST送信用ヘッダー
					$header = array(
						"Content-Type: application/x-www-form-urlencoded",
						"Content-Length: ".strlen($data)
					);
					// インスタンスを作成する 
					$context = array(
						"http" => array(
							"method"  => "POST",
							"header"  => implode("\r\n", $header),
							"content" => $data
						)
					);
					//取引登録を実行
					$url = "https://p01.mul-pay.jp/payment/EntryTran.idPass";
					$httpResponse = file_get_contents($url, false, stream_context_create($context));
					$httpResponse = explode("&",$httpResponse);
					$arrayParam = array();
					foreach($httpResponse as $v){
						$aa = explode("=",$v);
						//GMOペイメントでクレジットカード決済を行う場合
						$postArr[$aa[0]] = $aa[1];
					}
	
					if($postArr['ErrCode']){
						//取引登録のエラー発生時
						$registration = false;
						echo "取引登録ができなかった";
					}else{
						//取引登録が完了できた場合
						$registration = true;
						$postArr["GMOorder_id"] = $GMOorder_id;
					}
				}
				if($registration == false){
					//何らかの理由で取引登録ができなかった場合（クレジット、コンビニの場合のみ）
					$this->view->errMsgRegistrationGMO = "取引登録ができなかった";
					echo $this->view->errMsgRegistrationGMO;
				}else{
					//決済を実行
					//パラメータを整形
					$data2 = array(
						"AccessID" => $postArr["AccessID"],
						"AccessPass" => $postArr["AccessPass"],
						"OrderID" => $postArr["GMOorder_id"],
						"Method" => 1,
						"CardNo" => $postArr["CardNo"],
						"Expire" => $postArr["ExpireYY"].$postArr["ExpireMM"],
						"SecurityCode" => $postArr["SecurityCode"]
					);
					$data2 = http_build_query($data2, "", "&");
					//POST送信用ヘッダー
					$header2 = array(
						"Content-Type: application/x-www-form-urlencoded",
						"Content-Length: ".strlen($data2)
					);
					// インスタンスを作成する 
					$context2 = array(
						"http" => array(
							"method"  => "POST",
							"header"  => implode("\r\n", $header2),
							"content" => $data2
						)
					);
					//クレジットカード決済をを実行
					$url2 = "https://p01.mul-pay.jp/payment/ExecTran.idPass";
					$httpResponse2 = file_get_contents($url2, false, stream_context_create($context2));
					$httpResponse2 = explode("&",$httpResponse2);
					foreach($httpResponse2 as $v){
						$aa = explode("=",$v);
						//GMOペイメントでコンビニ決済を行う場合
						if($aa[0] == "ErrCode"){
							$payment = false;
						}
					}
				}
			}

			//GMOコンビニ決済を使用する場合
			if($cvs == true){
				
				//GMOorder_idが存在しない場合は取引登録を行い、GMOorder_idをセットする
				if(!$postArr['GMOorder_id']){
					//決済用オーダーIDを作成
					$arrTime = explode('.',microtime(true));
					//$GMOorder_id = hash("sha512",date("iYsdHm").$arrTime[0]);
					$GMOorder_id = time().$arrTime[0];
					//パラメータを整形
					$data = array(
						"ShopID" => $ShopID,
						"ShopPass" => $ShopPW,
						"OrderID" => $GMOorder_id,
						"Amount" => floor($postArr['total'])
					);
					$data = http_build_query($data, "", "&");
					//POST送信用ヘッダー
					$header = array(
						"Content-Type: application/x-www-form-urlencoded",
						"Content-Length: ".strlen($data)
					);
					// インスタンスを作成する 
					$context = array(
						"http" => array(
							"method"  => "POST",
							"header"  => implode("\r\n", $header),
							"content" => $data
						)
					);
					//取引登録実行
					$url = "https://p01.mul-pay.jp/payment/EntryTranCvs.idPass";
					$httpResponse = file_get_contents($url, false, stream_context_create($context));
					$httpResponse = explode("&",$httpResponse);
					$arrayParam = array();
					foreach($httpResponse as $v){
						$aa = explode("=",$v);
						//GMOペイメントでコンビニ決済を行う場合
						$postArr[$aa[0]] = $aa[1];
					}
					if($postArr['ErrCode']){
						//取引登録のエラー発生時
						$registration = false;
						echo "取引登録ができなかった";
					}else{
						//取引登録が完了できた場合
						$registration = true;
						$postArr["GMOorder_id"] = $GMOorder_id;
					}
				}
				
				if($registration == false){
					//何らかの理由で取引登録ができなかった場合（クレジット、コンビニの場合のみ）
					$this->view->errMsgRegistrationGMO = "取引登録ができなかった";
					echo $this->view->errMsgRegistrationGMO;
				}else{
					//決済を実行
					//パラメータを整形
					$data2 = array(
						"AccessID" => $postArr["AccessID"],
						"AccessPass" => $postArr["AccessPass"],
						"OrderID" => $postArr["GMOorder_id"],
						"Convenience" => $postArr["Convenience"],
						"CustomerName" => mb_convert_encoding($postArr["cus_name"].$postArr["cus_name2"],"SJIS","utf-8"),
						"CustomerKana" => mb_convert_encoding($postArr["cus_kana"].$postArr["cus_kana2"],"SJIS","utf-8"),
						"MailAddress" => $postArr["cus_mail"],
						"ReceiptsDisp11" => mb_convert_encoding($this->_setting['company']['company'],"SJIS","utf-8"),
						"ReceiptsDisp12" => $this->_setting['company']['tel'],
						"ReceiptsDisp13" => "09:00-18:00",
						"TelNo" => $postArr["cus_tel"].$postArr["cus_tel2"].$postArr["cus_tel3"]
					);
					$data2 = http_build_query($data2, "", "&");
					//POST送信用ヘッダー
					$header2 = array(
						"Content-Type: application/x-www-form-urlencoded",
						"Content-Length: ".strlen($data2)
					);
					// インスタンスを作成する 
					$context2 = array(
						"http" => array(
							"method"  => "POST",
							"header"  => implode("\r\n", $header2),
							"content" => $data2
						)
					);
					//取引登録を実行
					$url2 = "https://p01.mul-pay.jp/payment/ExecTranCvs.idPass";
					$httpResponse2 = file_get_contents($url2, false, stream_context_create($context2));
					$httpResponse2 = explode("&",$httpResponse2);
					foreach($httpResponse2 as $v){
						$aa = explode("=",$v);
						//GMOペイメントでコンビニ決済を行う場合
						if($aa[0] == "ErrCode"){
							$payment = false;
						}
					}
				}
			}
			if($payment == true){
				//決済が完了した場合、もしくはGMO決済を使用しない場合
				
				
				//マーチャント番号を付与
				$postArr['parent'] = 88;
				//不要なデータを注文データから削除
				$postArr['use_point'] = $postArr['poinAmount'];
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
					->where("parent=?",88)
					->limit(1)
				);
				$newId = $id[0]['no']+1;
				$this->_db->update("orderNo",array("no"=>$newId),$this->_db->quoteInto("parent=?",88));
				
				$order_id = $newId.date("msYidH");
				$postArr['order_id'] = $order_id ;
				$postArr['orderDatetime'] = date("Y-m-d H:i:s");
				$postArr['order_date'] = date("Y-m-d H:i:s");
				$postArr['seikyu_cost'] = $postArr['total'];
				$postArr['item_total'] = $postArr['t'];
				$postArr['shipping_cost'] = $postArr['shipping'];
				$postArr['tax_total'] = $postArr['tax'];
				if($postArr['pw']){
					$pw = $this->_db-> pwHash($postArr['pw']);
				}
				$this->view->c = $postArr['c'];
				
				unset($postArr['tax']);
				unset($postArr['total']);
				unset($postArr['shipping']);
				unset($postArr['cus_mail2']);
				unset($postArr['t']);
				unset($postArr['c']);
				unset($postArr['pw']);
				unset($postArr['pwConfurm']);
				//GMO決済関連パラメータ
				unset($postArr['CardNo']);
				unset($postArr['ExpireMM']);
				unset($postArr['ExpireYY']);
				unset($postArr['SecurityCode']);
				unset($postArr['AccessID']);
				unset($postArr['AccessPass']);
				unset($postArr['Convenience']);
				
				
				//会員登録を希望の場合
				if($pw){
					$arr = array(
						"company"=>$postArr['cus_campany'],
						"name"=>$postArr['cus_name'],
						"name2"=>$postArr['cus_nameｗ2'],
						"kana"=>$postArr['cus_kana'],
						"kana2"=>$postArr['cus_kana2'],
						"mail"=>$postArr['cus_mail'],
						"tel"=>$postArr['cus_tel'],
						"tel2"=>$postArr['cus_tel2'],
						"tel3"=>$postArr['cus_tel3'],
						"mtel"=>$postArr['cus_mtel'],
						"mtel2"=>$postArr['cus_mtel2'],
						"mtel3"=>$postArr['cus_mtel3'],
						"pw"=>$pw,
						"kind"=>1,
						"active"=>1,
						"parent"=>88,
						"zip"=>$postArr['cus_zip'],
						"pref"=>$postArr['cus_pref'],
						"addr"=>$postArr['cus_addr'],
						"addr2"=>$postArr['cus_addr2'],
						"division"=>$postArr['cus_div'],
						"fax"=>$postArr['cus_fax'],
						"created"=>date("Y-m-d H:i:s"),
					);
					if($m_lastId = $this->_db->insertAndGetLastId("ImageUser",$arr)){
						$this->view->memberRegist = true;
						$postArr['member_no'] = $m_lastId;
						$mem_no = $postArr['member_no'];
					}
				}else{
					$mem_no = $postArr['member_no'];
				}
				//DBに登録
				if($lastId = $this->_db->insertAndGetLastId("order",$postArr)){
					//商品データをDBに挿入
					foreach($itemArr as $v){
						$v['imagen'] = $v['image'][0]['url'];
						$items = array(
							"itemId"=>$v['id'],
							"image"=>$v['imagen'],
							"name"=>$v['name'],
							"price"=>$v['price'],
							"quantity"=>$v['q'],
							"cost"=>$v['cost'],
							"category"=>$v['category'],
							"unit"=>$v['unit'],
							"number"=>$v['number'],
							"orderId"=>$lastId,
							"tax"=>$v['tax'],
							"parent"=>88
						);
						$this->_db->insert("orderItem",$items);
					}
					//ポイントの入出力
					if($this->_setting['point']['pointuse'] ==0){
						if(!empty($mem_no)){
							$pin = ceil(($postArr['item_total']-$postArr['use_point'])*($this->_setting['point']['pointrate']/100));
							if($pw){
								$pin = $pin+$this->_setting['point']['pointfirst'];
							}
							$customerPointArr = array(
								"customerId" =>$mem_no,
								"created" =>date("Y-m-d H:i:s"),
								"pin" =>$pin,
								"pout" =>$postArr['use_point'],
								"parent" =>88,
								"orderId" =>$lastId,
								"limit_date" =>date("Y-m-d H:i:s",time()+60*60*24*$this->_setting['point']['pointlimit'])
							);
							$this->_db->insert("cutomerPoint",$customerPointArr);
							//セッションのポイント情報も変更
							if(!$pw && $mem_no){
								$_SESSION['Zend_Auth']['storage']->point =$_SESSION['Zend_Auth']['storage']->point+$pin-$postArr['use_point'];
							}
						}
					}
					//メールを送付
					$this->sendmail(88,$itemArr,$postArr);
					//カートの中の商品を削除
					$myNamespace->items = NULL;
					//2重送信防止の為にリダイレクト2016.11.25
					//header("location:".BASEURL."/order/end/");
				}
			}else{
				//GMO決済を使用して、かつ決済が完了出来なかった場合
				$this->view->paymentErr =true;
				unset($postArr['CardNo']);
				unset($postArr['ExpireMM']);
				unset($postArr['ExpireYY']);
				unset($postArr['SecurityCode']);
			}
			$this->view->detail = $postArr;
		}
		
	}
	/*
	public function endAction(){
		$myNamespace = new Zend_Session_Namespace('items');
		$this->view->item = $myNamespace->items;
		var_dump($myNamespace);
		//カートの中の商品を削除
		$myNamespace->items = NULL;
	}
	*/
	
	private function getItem($n,$q) {
		$itemModel = new Model_Indexitem;
		$item = $itemModel->getItemDetailOfNumber($n);
		$item['q'] = intval($q);
		//画像を取得して、配列に格納
		$item['image'] = $this->_db->fetchAll(
			$this->_db->select()
			->from("image")
			->where("item=?",$item['id'])
			->limit(1)
		);
		$item['price'] = $item['price'];
		/*
		if($item['tax'] == 0){
			$item['price'] = $item['price']*(1+$this->_setting['tax']['ratio']/100);
		}else{
			$item['price'] = $item['price'];
		}
		*/
		return $item;
	}
	
	
	
	private function sendmail($p,$item,$data) {
		$this->data = $data;
		//メールデータの取得
		$result = $this->_db->fetchAll(
			$this->_db->select()
			->from("mail_template")
			->where("name='autores' AND parent=?",$p)
			->limit(1)
		);
		//&amp;を元に戻す。　20161003
		//$result[0] = str_replace("&map;","&",$result[0]);
		
		$mail = array();
		$detailStr = "ーーーーーーーーーーーー\n";;
		$detailStr.= "ご注文情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "ご注文番号：".$this->data['order_id']."\n";
		$detailStr.= "ご注文日時：".date("Y年m月d日 H：i")."\n";
		$detailStr = "ーーーーーーーーーーーー\n";;
		$detailStr.= "ご注文商品情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($item as $v){
			$taxRatio = 1/100*$this->setting['tax']['ratio'];
			if($v['tax']==0){
				//外税の場合
				$zei = $v['price']*$taxRatio;
				if($this->setting['tax']['rule'] ==0){
					$zei = round($zei);
				}elseif($this->setting['tax']['rule'] ==1){
					$zei = ceil($zei);
				}else{
					$zei = floor($zei);
				}
				$price = $v['price']+$zei;
			  }else{
				//内税の場合
				$price = $v['price'];
			  }
			$detailStr.= $v['name']."　".number_format($price)."円×".$v['q'].$v['unit']."=".number_format($price*$v['q'])."円\n";
		}
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "小計：".number_format($this->data['item_total'])."円\n";
		$detailStr.= "消費税：".number_format($this->data['tax_total'])."円\n";
		$detailStr.= "送料：".number_format($this->data['shipping_cost'])."円\n";
		if($this->setting['point']['pointuse'] ==0 && $this->data['use_point']){
			$detailStr.= "ポイント：-".number_format($this->data['use_point']).$this->setting['point']['pointname'] ."\n";
		}
		if($this->setting['discount']['discountUse'] ==0 && $this->data['off_price']){
			$detailStr.= "割引：".number_format($this->data['off_price'])."\n";
		}
		if($this->data['collect_cost']>0){
			$detailStr.= "代引き手数料：".number_format($this->data['collect_cost'])."円\n";
		}
		$detailStr.= "合計：".number_format($this->data['seikyu_cost'])."円\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "決済方法\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($this->_setting['payment'] as $v ){
			if($v['id'] == $this->data['payment_method']){
				$detailStr.= "決済方法：".$v['name']."\n\n".$v['description']."\n";
			}
		}
		$detailStr.= "\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "ご注文主様情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お名前：".$this->data['cus_name']." ".$this->data['cus_name2']."\n";
		$detailStr.= "フリガナ：".$this->data['cus_kana']." ".$this->data['cus_kana2']."\n";
		$detailStr.= "電話番号：".$this->data['cus_tel']."-".$this->data['cus_tel2']."-".$this->data['cus_tel3']."\n";
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
		if($this->setting['global']['messageCardUse']){
			$detailStr.= "ーーーーーーーーーーーー\n";
			$detailStr.= "メッセージカードの指定\n";
			$detailStr.= "ーーーーーーーーーーーー\n";
			$detailStr.= "メッセージカード種類：".$this->data['noshi_kind']."\n";
			$detailStr.= "メッセージカード内容：".$this->data['message']."\n";
			foreach($this->setting['youto'] as $v){
				if($v['id'] == $this->data['youto']){
					$youtoName = $v['name'];
				}
			}
			$detailStr.= "ご用途：".$youtoName."\n";
		}
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "その他備考\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= $this->data['order_comment']."\n";
		$detailStr.= "\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($result[0] as $k=>$vv){
			$patterns[0] = '/%NAME%/';
			$patterns[1] = '/%MAIL%/';
			$patterns[2] = '/%ORDER%/';
			$patterns[3] = '/%SHOP%/';
			$patterns[4] = '/%SHOPDETAIL%/';
			$patterns[5] = '/%DELIVERY_PAPER%/';
			$replacements[0] = $this->data['cus_campany'].$this->data['cus_name'];
			$replacements[1] = $this->data['cus_mail'];
			$replacements[2] = $detailStr;
			$replacements[3] = $this->_setting['global']['SiteName'];
			$replacements[4] = 'ショップ詳細';
			$replacements[5] = $this->data['delivery_paper_no'];
			$result[0][$k] = preg_replace($patterns, $replacements, str_replace("&amp;","&",$vv));
		}
		
		$smtp = array();
		foreach($this->_setting['global'] as $k=>$v){
			if($k == "SMTPhost" || $k == "SMTPuser" || $k == "SMTPpass" || $k == "SMTPport" || $k == "SMTPsecur" || $k == "SMTPcertificate"){
				$smtp[$k] = $v;
			}
		}
		$this->_db->sendmail(
			true,
			$result[0]['subject'],
			$result[0]['body'],
			$this->_setting['global']['infoMail'],
			$this->_setting['global']['SiteName'],
			$this->data['cus_mail'],
			$this->data['cus_name'],
			$smtp
		);
	}
}
?>