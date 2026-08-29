<?php
// IndexController
class Api_UserController extends Common_ApiController {
	private $_db;
	private $_user;
	private $_orign;
	public function init() {
		require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
		$this->_db = new Model_Apiimageuser();

		//セッションを取得
		$auth = Zend_Auth::getInstance();
		$this->_user = $auth->getIdentity();
		$this->origin = array(
			"name"=>array("origin"=>"法人名・事業所名・事業名"),
			"name2"=>array("origin"=>"事業所名（サブ：法人名と別の場合）"),
			"number"=>array("origin"=>"事業所番号"),
			"doctor"=>array("origin"=>"嘱託医・協力医・医師名・薬剤師名"),
			"addr"=>array("origin"=>"住所・開催場所・所在地"),
			"tel"=>array("origin"=>"TEL"),
			"desk"=>array("origin"=>"お問い合わせ窓口"),
			"fax"=>array("origin"=>"FAX"),
			"url"=>array("origin"=>"HPアドレス"),
			"mail"=>array("origin"=>"メールアドレス"),
			"capacity"=>array("origin"=>"規模・定員"),
			"shortUse"=>array("origin"=>"実施事業[短期利用共同生活介護の提供]"),
			"dementia"=>array("origin"=>"実施事業[共同型指定認知症対応型通所介護の提供]"),
			"cost"=>array("origin"=>"利用以外にかかる費用"),
			"condition"=>array("origin"=>"入所対象"),
			"room"=>array("origin"=>"居室"),
			"area"=>array("origin"=>"対応可能地域"),
			"admin"=>array("origin"=>"管理者"),
			"buisinessHour"=>array("origin"=>"利用時間・営業時間・サービス提供時間"),
			"buisinessDay"=>array("origin"=>"営業日"),
			"holiday"=>array("origin"=>"定休日・休日"),
			"occuparion"=>array("origin"=>"スタッフ職種・職員配置（職種）"),
			"contact"=>array("origin"=>"スタッフとの連絡手段"),
			"contactHour"=>array("origin"=>"スタッフとの連絡時間帯"),
			"equipment"=>array("origin"=>"福祉器具・福祉用具"),
			"kind"=>array("origin"=>"介護の種類"),
			"features"=>array("origin"=>"特長・セールスポイント"),
			"addition"=>array("origin"=>"算定加算"),
			"effect"=>array("origin"=>"内容（趣旨）"),
			"eventDate"=>array("origin"=>"開催日"),
			"curriculum"=>array("origin"=>"内容（カリキュラム）"),
			"flow"=>array("origin"=>"利用までの流れ"),
			"fee"=>array("origin"=>"利用料"),
			"eventHour"=>array("origin"=>"開催時間"),
			"consultation"=>array("origin"=>"障害者福祉相談支援"),
			"other"=>array("origin"=>"その他"),
			"kindOfCar"=>array("origin"=>"車両の種類"),
			"NightimeSupport"=>array("origin"=>"早朝・夜間対応"),
			"possiblePhlegmSuction"=>array("origin"=>"痰吸引ができる職員の有無"),
			"gettingOnAndOff"=>array("origin"=>"通院など乗降介助の実施"),
			"BarrierFree"=>array("origin"=>"バリアフリー状況"),
			"parking"=>array("origin"=>"駐車場"),
			"medicalDepartment"=>array("origin"=>"診療科目"),
			"visit"=>array("origin"=>"往診"),
			"overtimeResponse"=>array("origin"=>"日・祝・時間外対応"),
			"visitClinic"=>array("origin"=>"訪問診療・訪問指導"),
			"bed"=>array("origin"=>"有床・無床"),
			"assistantDoctor"=>array("origin"=>"副主治医体制"),
			"rehabilitation"=>array("origin"=>"リハビリテーションの実施"),
			"label"=>array("origin"=>"在宅療養者への対応・対象者"),
			"gastrostomy"=>array("origin"=>"経管栄養・胃瘻","sel"=>1),
			"insulin"=>array("origin"=>"インスリン注射","sel"=>1),
			"centralParenteral"=>array("origin"=>"中心静脈栄養","sel"=>1),
			"endOfLife"=>array("origin"=>"終末期患者・終末期・緩和ケア","sel"=>1),
			"oxygen"=>array("origin"=>"在宅酸素療法","sel"=>1),
			"pressureUlcer"=>array("origin"=>"じょくそう、創処置","sel"=>1),
			"colostomy"=>array("origin"=>"人工肛門管理","sel"=>1),
			"tracheostomy"=>array("origin"=>"気管切開","sel"=>1),
			"catheter"=>array("origin"=>"留置カテーテル","sel"=>1),
			"possiblePhlegm"=>array("origin"=>"痰の吸引","sel"=>1),
			"ventilator"=>array("origin"=>"人工呼吸器","sel"=>1),
			"nursing"=>array("origin"=>"看取り・看取りケア","sel"=>1),
			"visualImpairment"=>array("origin"=>"視力障害","sel"=>1),
			"dialysis"=>array("origin"=>"人工透析","sel"=>1),
			"dementia2"=>array("origin"=>"認知症","sel"=>1),
			"psychosis"=>array("origin"=>"精神","sel"=>1),
			"rehabilitation2"=>array("origin"=>"リハビリ","sel"=>1),
			"bedridden"=>array("origin"=>"寝たきり","sel"=>1),
			"disability"=>array("origin"=>"身体障害","sel"=>1),
			"nightVisit"=>array("origin"=>"定期的な夜間訪問","sel"=>1),
			"holidayVisit"=>array("origin"=>"定期的な日祝日訪問","sel"=>1),
			"nervousIntractableDisease"=>array("origin"=>"神経難病","sel"=>1),
			"pediatrics"=>array("origin"=>"小児科","sel"=>1),
			"stoma"=>array("origin"=>"人工肛門管理","sel"=>1),
			//"stoma"=>array("origin"=>"ストーマ","sel"=>1),//人工肛門管理に統一 2020.11.24 edit
			"oxygenTherapy"=>array("origin"=>"酸素療法","sel"=>1),
			"cerebrovascularDisease"=>array("origin"=>"脳血管疾患","sel"=>1),
			"anesthesia"=>array("origin"=>"麻酔による疼痛管理","sel"=>1),
			"epodural"=>array("origin"=>"硬膜外ブロック管理","sel"=>1),
			"transfusion"=>array("origin"=>"輸血","sel"=>1),
			"oralSurgery"=>array("origin"=>"口腔外科処置","sel"=>1),
			"toothDecay"=>array("origin"=>"むし歯治療","sel"=>1),
			"denture"=>array("origin"=>"義歯調整","sel"=>1),
			"toothExtraction"=>array("origin"=>"抜歯","sel"=>1),
			"swallowing"=>array("origin"=>"接触嚥下訓練","sel"=>1),
			"preiodontal"=>array("origin"=>"歯周炎治療","sel"=>1),
			"oralCare"=>array("origin"=>"口腔ケア","sel"=>1),
			"handicapped"=>array("origin"=>"障がい児対応","sel"=>1),
			"dentalHygiene"=>array("origin"=>"歯科衛生指導","sel"=>1),
			"roentgen"=>array("origin"=>"レントゲン","sel"=>1),
			"infusion"=>array("origin"=>"輸液ルート・カテーテルの提供","sel"=>1),
			"grindingIronAgent"=>array("origin"=>"鉄剤の粉砕","sel"=>1),
			"drug"=>array("origin"=>"麻薬の取扱","sel"=>1),
			"aseptic"=>array("origin"=>"無菌調剤の可否","sel"=>1),
			"sanitary"=>array("origin"=>"衛生材料の供給","sel"=>1),
			"other2"=>array("origin"=>"その他","sel"=>1),
			"packaged"=>array("origin"=>"完全一包化","sel"=>1),
			"Calendar"=>array("origin"=>"服薬カレンダー","sel"=>1),
			"recreation"=>array("origin"=>"レクレーション","sel"=>1)
		);
	}

	//選択されたデータの削除
	public function planAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$data = $this->_db->fetchAll(
				$this->_db->select()
				->from("plan")
				->where("id=?",$postArr['plan'])
				->limit(1)
			);
			echo json_encode($data[0]);
		}
	}
	//選択されたデータの削除
	public function specialAction() {
		if($this->_request->isPost()){
			//変更する特権管理者を
			$id = $this->_request->getPost("ids")[0];
			//現在の特権管理者を一般スタッフへ
			$this->_db->update("ImageUser",array("Authority"=>3),$this->_db->quoteInto("Authority=?",1));
			if($this->_db->update("ImageUser",array("Authority"=>1),$this->_db->quoteInto("id=?",$id))){
				echo "OK";
			}

		}
	}
	//選択されたデータの削除
	public function deleteAction() {
		if($this->_request->isPost()){
			$idArr = $this->_request->getPost("ids");
			$whereArr = array();
			$whereArr2 = array();
			$whereArr3 = array();
			if(is_array($idArr)){
				foreach($idArr as $v){
					$whereArr[] =$this->_db->quoteInto("`id`=?",$v);
					$whereArr2[] =$this->_db->quoteInto("`parent`=?",$v);
					$whereArr3[] =$this->_db->quoteInto("`merchant`=?",$v);
					//ディレクトリを削除（一括）
					$dir=HOMEDIR."/image/".$v;
					system("rm -rf {$dir}");
				}
			}else{
				$whereArr[] =$this->_db->quoteInto("`id`=?",$idArr);
				$whereArr2[] =$this->_db->quoteInto("`parent`=?",$idArr);
				$whereArr3[] =$this->_db->quoteInto("`merchant`=?",$idArr);
				//ディレクトリを削除（一括）
				$dir=HOMEDIR."/image/".$idArr;
				system("rm -rf {$dir}");
			}
			$where = implode(" OR ",$whereArr);
			$where2 = implode(" OR ",$whereArr2);
			$where3 = implode(" OR ",$whereArr3);
			//マーチャント情報を削除
			if($this->_db->delete("ImageUser",$where)){
				//スタッフ・外注・顧客を削除
				if($this->_db->delete("ImageUser",$where2)){
					//会社情報
					//割引情報
					//消費税情報
					//オプション情報
					//施工情報
					//日報フォーマット
					//ファイル
					//メモ
					//見積・請求
					//見積・請求商品
					//タスク
					//日報データ
					//報告書
					//アサインメンバー


					//ログデータを削除
					if($this->_db->delete("outsourceActivateLog",$where3)){
						echo "OK";
					}
				}
			}
		}
	}
	public function optAction(){
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();

			//オプションで追加容量がある場合は、最大サイズに追加
			$opt = $this->_db->fetchAll(
			$this->_db->quoteInto(
				"SELECT DISTINCT p.*,
				(
					SELECT sum(oa.score) AS s
					FROM option_acount oa
					WHERE oa.opt = p.id
					AND oa.user = ?
					GROUP BY oa.opt , oa.user
				) AS user,
				(
					SELECT MIN(oa.created)
					FROM option_acount oa
					WHERE oa.opt = p.id
					AND oa.user = ?
					GROUP BY oa.opt , oa.user
				) AS first
				FROM plan p
				WHERE p.kind=0 AND activate=1",$postArr['id'])
			);
			echo json_encode($opt);
		}
	}
	//同一アドレスでの登録確認
	public function sameAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$result = $this->_db->fetchAll(
				$this->_db->select()
				->from("ImageUser","COUNT(0) as n")
				->where("mail=?",$postArr['mail'])
				->where("kind=4")
				->where("parent=88")
			);
			echo $result[0]['n'];
		}
	}

	//CSVエクスポート
	public function exportAction() {
		if($this->_request->isPost()){
			$idArr = $this->_request->getParam("ids");
			if(!empty($idArr)){
				$where = "im.id=".implode(" OR im.id=",$idArr);
				$result = $this->_db->fetchAll(
					$this->_db->select()
					->from(array("im"=>"ImageUser"))
					->joinLeft(array("uc"=>"userclassification"),"uc.id=im.class","uc.name AS ucName")
					->where($where)
				);
			}else{
				$result = $this->_db->fetchAll(
					$this->_db->select()
					->from(array("im"=>"ImageUser"))
					->joinLeft(array("uc"=>"userclassification"),"uc.id=im.class","uc.name AS ucName")
					->where("im.kind=3")
				);
			}
			$str = "施設名,";
			$str.= "分類,";
			$str.= "フリガナ,";
			$str.= "メール,";
			$str.= "電話番号,";
			$str.= "FAX番号,";
			$str.= "郵便番号,";
			$str.= "住所１,";
			$str.= "住所２,";
			$str.= "最終更新日\n";
			foreach($result as $v){
				$str.= $v['name'].",";
				$str.= $v['ucName'].",";
				$str.= $v['kana'].",";
				$str.= $v['mail'].",";
				$str.= $v['tel']."-".$v['tel2']."-".$v['tel3'].",";
				$str.= $v['mtel']."-".$v['mtel2']."-".$v['mtel3'].",";
				$str.= $v['zip']."-".$v['zip2'].",";
				$str.= $v['addr'].",";
				$str.= $v['addr2'].",";
				$str.= $v['updated']."\n";
			}
			$filePath = "catch/userlist.csv";
			$filename = HOMEDIR.$filePath;
			$filename2 = BASEURL."/".$filePath;
			$str = mb_convert_encoding($str,"sjis-win","UTF-8");
			$fp = fopen($filename, 'wb');
			if($fp){
				if(flock($fp,LOCK_EX)){
					if(fwrite($fp,$str) === FALSE){
						echo json_encode(array("status"=>"faild","msg"=>'ファイル書き込みに失敗しました'));
					}else{
						echo json_encode(array("status"=>"success","msg"=>'ファイルに書き込みました',"url"=>$filename2));
					}
					flock($fp,LOCK_UN);
				}else{
					echo json_encode(array("status"=>"faild","msg"=>'ファイルロックに失敗しました'));
				}
			}
			fclose($fp);
		}
	}

	//顧客リスト
	public function getcustomerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　会員管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/merchant/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/merchant/user"　class="btn disabled"><i class="fa fa-cog"></i>　会員管理</a> <span class="divider">/</span>
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
		//会員リストの読み込み
		$user = $this->_db->getcustomers($kind,$keyword,$p,$limit,$this->_user->id);
		echo json_encode($user);
	}
	//顧客リスト
	public function getcustomer2Action() {
		//GETパラメータを取得
		$id = $this->_request->getParam("id");
		$user = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUser")
			->where("`id`=?",$id)
			->limit(0)
		);
		echo json_encode($user[0]);
	}
	//顧客リスト
	public function pointAction() {
		$id = $this->_request->getPost("id");
		$user = $this->_db->getcustomerPoint($id);
		echo json_encode($user);

	}
	//強制停止
	public function stopAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			//強制停止フラグを立てる
			$this->_db->update("ImageUser",array("stop"=>date("Y-m-d H:i:s")),$this->_db->quoteInto("id=?",$postArr['id']));
			//配下のスタッフをログイン出来ないようにする
			$editor = $this->_db->fetchAll(
				$this->_db->select()
				->from("ImageUser",array("id","active"))
				->where("parent=?",$postArr['id'])
			);
			foreach($editor as $v){
				//ユーザーの有効性ログインへデータの挿入
				if($v['active'] == 1){
					$this->_db->insert(
						"outsourceActivateLog",
						array(
							"merchant"=>$postArr['id'],
							"user"=>$v['id'],
							"date"=>date("Y-m-d H:i:s"),
							"outsource"=>0,
							"score"=>-1
						)
					);
				}
			}
			$this->_db->update("ImageUser",array("active"=>0),$this->_db->quoteInto("parent=?",$postArr['id']));
			echo "OK";
		}
	}
	public function restartAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			//強制停止フラグを立てる
			$this->_db->update("ImageUser",array("stop"=>"0000-00-00 00:00:00"),$this->_db->quoteInto("id=?",$postArr['id']));
			//特権管理者のみを有効化
			$editor = $this->_db->fetchAll(
				$this->_db->select()
				->from("ImageUser",array("id","active"))
				->where("parent=?",$postArr['id'])
				->where("Authority=1")
			);
			foreach($editor as $v){
				//ユーザーの有効性データの挿入
				if($v['active'] == 0){
					$this->_db->insert(
						"outsourceActivateLog",
						array(
							"merchant"=>$postArr['id'],
							"user"=>$v['id'],
							"date"=>date("Y-m-d H:i:s"),
							"outsource"=>0,
							"score"=>1
						)
					);
				}
			}
			$this->_db->update("ImageUser",array("active"=>1),$this->_db->quoteInto("Authority = 1 AND parent=?",$postArr['id']));
			echo "OK";

		}
	}
	//郵便番号リスト
	public function zipAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$result = $this->_db->fetchAll(
				$this->_db->select()
				->from("zip","COUNT(0) as n")
				->where("zipcode=?",$postArr['zip'])
			);
			echo $result[0]['n'];
		}
	}
	//墓リスト
	public function hakaAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			/*
			*/
			$result = $this->_db->fetchAll(
				$this->_db->select()
				->from("haka")
				->where("mem_id=?",$postArr['id'])
			);
			echo json_encode($result);
		}
	}


	public function facilityAction() {
		if($this->_user->id){
			if($this->_request->isGet()){
				$getArr = $this->_db->getArray();
				$useFacility = $this->_db->fetchAll(
					$this->_db->select()
					->from("facilityOfUserClass")
					->where("userClass=?",$getArr['id'])
				);
				$arr2 = array();
				foreach($useFacility as $vv){
					foreach($this->origin as $k=>$v){
						if($k == $vv['facilty']){
							if($v['sel'] ==1){
								$arr2[$vv['facilty']] = array("name"=>$vv['name'],"origin"=>$v['origin'],"sel"=>1);
							}else{
								$arr2[$vv['facilty']] = array("name"=>$vv['name'],"origin"=>$v['origin']);
							}
							unset($arr[$k]);
						}
					}
				}
				echo json_encode(array("unuse"=>$this->origin,"use"=>$arr2));
			}else{
				echo json_encode(array("unuse"=>$this->origin));
			}
		}
	}
	public function facilityupdateAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$facility = json_decode(file_get_contents(BASEURL."/api/user/facility/"),ture);
			$userclass = $postArr['userclass'];
			unset($postArr['userclass']);

			//すでに登録されている項目を削除し、ポストデータを登録する。
			$this->_db->delete('facilityOfUserClass',$this->_db->quoteInto("userClass=?",$userclass));
			$arr = array();
			foreach($postArr as $k => $v){
				$arr[] = "('".$v."',".$userclass.",'".$k."',".$this->_user->parent.")";
			}
			$q = "INSERT INTO `facilityOfUserClass` (`name`,`userClass`,`facilty`,`parent`) VALUES".implode(",",$arr);
			if($this->_db->send($q)){
				echo "OK";
			}
		}
	}
	public function useroffacilityAction() {
		if($this->_user->id){
			if($this->_request->isGet()){
				$getArr = $this->_db->getArray();
				$facility = $this->_db->fetchAll(
					$this->_db->select()
					->from("facilityOfUserClass")
					->where("userClass=?",$getArr['id'])
				);
				foreach($facility as $k=>$v){
					foreach($this->origin as $kk=>$vv){
						if($v['facilty'] == $kk && $vv['sel'] == 1){
							$facility[$k]['sel'] = 1;
						}
					}
				}

				if($getArr["user"]){
					$arr = array("id","user");
					foreach($facility as $vv){
						$arr[] = $vv['facilty'];
					}
					//ユーザーに応じたデータを取得
					$userFacility = $this->_db->fetchAll(
						$this->_db->select()
						->from("facilityInfomation",$arr)
						->where("user=?",$getArr['user'])
					);
				}else{
					$userFacility = array();
				}
				echo json_encode(array("facility"=>$facility,"value"=>$userFacility,"origin"=>$facility_orign));
			}
		}
	}








}
?>
