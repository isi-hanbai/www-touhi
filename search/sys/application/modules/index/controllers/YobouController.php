<?php
//コンポーネントのロード
class YobouController extends Common_IndexController {
	public $_setting;
	public $_db;
	private $_orign;
	//初期化メソッドの定義
	public function init(){
		//設定の読み込み
		$setting = new Model_Indexsettings;
		$this->_db = new Model_Indexgeneral;
		$this->_setting = $setting->setting("88");
		$this->view->setting = $this->_setting;
		$this->site_title = $this->_setting['global']['homeTitle'];
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()){
			$this->view->loginout = '<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin">ログイン</a>';
		}else{
			$this->view->loginout =
				'<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin/index/logout/">ログアウト</a>
				<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/member/">マイページ</a>';
		}

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
			"consultation"=>array("origin"=>"指定相談支援","sel"=>3),
			"other"=>array("origin"=>"その他"),
			"kindOfCar"=>array("origin"=>"車両の種類"),
			"NightimeSupport"=>array("origin"=>"早朝・夜間対応","sel"=>3),
			"possiblePhlegmSuction"=>array("origin"=>"痰吸引ができる職員の有無","sel"=>3),
			"gettingOnAndOff"=>array("origin"=>"通院など乗降介助の実施","sel"=>3),
			"BarrierFree"=>array("origin"=>"バリアフリー状況","sel"=>3),
			"parking"=>array("origin"=>"駐車場","sel"=>3),
			"medicalDepartment"=>array("origin"=>"診療科目"),
			"visit"=>array("origin"=>"往診","sel"=>3),
			"overtimeResponse"=>array("origin"=>"日・祝・時間外対応","sel"=>3),
			"visitClinic"=>array("origin"=>"訪問診療・訪問指導","sel"=>3),
			"bed"=>array("origin"=>"有床・無床"),
			"assistantDoctor"=>array("origin"=>"副主治医体制"),
			"rehabilitation"=>array("origin"=>"リハビリテーションの実施","sel"=>3),
			"label"=>array("origin"=>"在宅療養者への対応・対象者","sel"=>3),
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
			"stoma"=>array("origin"=>"ストーマ","sel"=>1),
			"oxygenTherapy"=>array("origin"=>"酸素療法","sel"=>1),
			"cerebrovascularDisease"=>array("origin"=>"脳血管疾患","sel"=>1),
			"anesthesia"=>array("origin"=>"麻酔に寄る疼痛管理","sel"=>1),
			"epodural"=>array("origin"=>"硬膜外ブロック管理","sel"=>1),
			"transfusion"=>array("origin"=>"輸血","sel"=>1),
			"oralSurgery"=>array("origin"=>"口腔外科処置","sel"=>1),
			"toothDecay"=>array("origin"=>"虫歯治療","sel"=>1),
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
			"other2"=>array("origin"=>"その他","sel"=>1)
		);
		$this->view->origin = $this->origin;
		$class = $this->_db->fetchAll(
			$this->_db->select()
			->from("userClassification")
		);
		$this->view->class = $class;
	}
	public function indexAction() {
		//header("location:".BASEURL."/index/index/list");
			$this->view->title ="介護予防事業を探す";
			$this->view->bread = '<li><a href="https://www.touhi-ishikai.jp/"><i class="fa fa-tachometer"></i>HOME</a></li>
														<li>'.$this->view->title.'</li>';
	}
	public function listAction() {
		$this->view->title ="介護予防事業の検索結果";
		$this->view->bread = '<li><a href="https://www.touhi-ishikai.jp/"><i class="fa fa-tachometer"></i>HOME</a></li>
													<li><a href="'.BASEURL.'/yobou/">介護予防事業を探す</li>
													<li>'.$this->view->title.'</li>';
		//GETパラメータを取得
		$kind = $this->_request->getParam("kind");
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		//検索クエリ
		$keyword = $this->_request->getParam("keyword");
		$class = $this->_request->getParam("class");
		$procedure = $this->_request->getParam("procedure");
		$service = $this->_request->getParam("service");
		$area = $this->_request->getParam("area");



		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$this->start = $start;
		$this->end = $start+$limit;
		$parent = "";
		//ユーザーリストの読み込み


		//検索クエリを作成
		$whereArr = array("i.kind=3","i.yobou=1");
		//検索キーワードが指定された場合
		if(!empty($keyword)){
			$key = mb_convert_kana($keyword,"s","UTF-8");
			$keyArr = split(" ",$key);
			foreach($keyArr as $v){
				$whereArr[] = "concat(i.*) LIKE '%".$v."%'";
			}
		}
		if(!empty($class)){
			$classArr = array();
			foreach($class as $v){
				$classArr[] = "concat(i.*) LIKE '%".$v."%'";
			}
			$whereArr[] = "(".implode(" OR ",$areaArr).")";
		}
		if(!empty($procedure)){
			foreach($procedure as $v){
				$whereArr[] = $v." > 0";
			}
		}
		if(!empty($service)){
			foreach($service as $v){
				$whereArr[] = $v." !=''";
			}
		}
		if(!empty($area)){
			/**/
			$areaArr = array();
			foreach($area as $v){
				$areaArr[] = "concat(i.addr, i.addr2) LIKE '%".$v."%'";
			}
			$whereArr[] = "(".implode(" OR ",$areaArr).")";
		}
		//WHERE句を生成
		if(!empty($whereArr)){
			$where = " WHERE ".implode(" AND ",$whereArr);
		}
		//SQLコマンドを作成
		$sql = "SELECT DISTINCT
		 					i.*,
							u.name AS className,
							p.name AS prefName,
							(
								SELECT COUNT(*)
								FROM ImageUser as i
								LEFT JOIN facilityInfomation AS fi ON i.id = fi.user".$where.") AS c
					FROM ImageUser i
					LEFT JOIN facilityInfomation AS fi ON i.id = fi.user
					LEFT JOIN userClassification AS u ON u.id = i.class
					LEFT JOIN pref AS p ON p.id = i.pref".$where;
		//LIMIT句を生成
		$start = $p*$limit;
		$sql.= " GROUP BY u.id";
		$sql.= " ORDER BY `id` DESC";
		$sql.= " LIMIT {$start} , {$limit}";
		$result = $this->_db->fetchAll($sql);

		if($result[0]['c'] < $limit){
			$this->end = $result[0]['c'];
		}
		$this->view->start = $this->start+1;
		$this->view->end = $this->end;
		$this->view->list = $result;

		$this->view->keyword = $keyword;
		$this->view->classArr = $class;
		$this->view->procedureArr = $procedure;
		$this->view->serviceArr = $service;
		$this->view->areaArr = $area;

		$this->userpager($keyword,$class,$procedure,$service,$area,$user[1],$limit,$p,"/index/index/list/");

	}
	public function detailAction() {
		$getArr = $this->_db->getArray();
		$detail = $this->_db->fetchAll(
			$this->_db->select()
			->from(array("i"=>"ImageUser"),"i.*")
			->where("i.kind=3")
			->where("i.id=?",$getArr['id'])
			->limit(1)
		);
		//追加情報
		$facility = $this->_db->fetchAll(
			$this->_db->select()
			->from(array("f"=>"facilityOfUserClass"))
			->where("f.userClass=?",$detail[0]['class'])
		);
		$arr3 = array();
		$arr = array();
		foreach($facility as $v){
			$arr[] = $v["facilty"]." AS added_".$v["facilty"];
			$arr3[$v["facilty"]] = $v["name"];
		}
		$added = $this->_db->fetchAll(
			$this->_db->select()
			->from("facilityInfomation",$arr)
			->where("user=?",$getArr['id'])
			->limit(1)
		);
		$arr2 = array();
		foreach($added[0] as $k=>$v){
			foreach($this->origin as $kkk=>$vvv){
				if($k == "added_".$kkk){
					if($vvv['sel'] ==1){
						$arr2[$kkk] = array("v"=>$v,"sel"=>1,"label"=>$arr3[$kkk]);
						break;
					}else{
						$arr2[$kkk] = array("v"=>$v,"sel"=>0,"label"=>$arr3[$kkk]);
						break;
					}
				}
			}
		}
		//$data	= array_merge($detail[0],$added[0]);
		$data	= $arr2;
		$this->view->detail = $data;
		$this->view->title =$detail[0]['name'];
	}




	/*
	public function mailAction() {
		$prefModel = new Model_Indexpref();
		$item = $prefModel->getSokuItem();
		foreach($item as $k=>$v){
			if($v['tax'] ==0){
				//外税の場合
				$item[$k]['price'] = $v['price']*(1+$this->_setting['tax']['ratio']/100);
			}
		}
		$this->view->soku =$item;
		$html = $this->_db->fetchAll(
			$this->_db->select()
			->from("html2")
			->where("parent=?",88)
			->where("page=?","infomation")
		);
		$this->view->html =$html;
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$this->view->data = $postArr;
		}else{
			if($this->view->user){
				$data = (array)$this->view->user;
				$this->view->data =$data;
			}
		}
		$this->view->title = '<i class="fa fa-tachometer"></i> お問い合わせ';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i>　'.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
	}

	public function mailconfurmAction() {
		$prefModel = new Model_Indexpref();
		$item = $prefModel->getSokuItem();
		foreach($item as $k=>$v){
			if($v['tax'] ==0){
				//外税の場合
				$item[$k]['price'] = $v['price']*(1+$this->_setting['tax']['ratio']/100);
			}
		}
		$this->view->soku =$item;
		$this->view->title = '<i class="fa fa-tachometer"></i> お問い合わせ内容の確認';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i>　'.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
		if($this->_request->isPost()){
			$posArr = $this->_db->postArray();
			$this->view->data = $posArr;
		}else{
			header("location:".BASEURL."/index/index/mail/");
		}
	}
	public function mailfinishAction() {
		$prefModel = new Model_Indexpref();
		$item = $prefModel->getSokuItem();
		foreach($item as $k=>$v){
			if($v['tax'] ==0){
				//外税の場合
				$item[$k]['price'] = $v['price']*(1+$this->_setting['tax']['ratio']/100);
			}
		}
		$this->view->soku =$item;
		$this->view->title = '<i class="fa fa-tachometer"></i> お問い合わせ送信完了';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i>　'.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$this->view->data = $postArr;

			//メールの送信
			$postArr['created'] = date("Y-m-d H:i:s");
			$postArr['parent'] = 88;
			//var_dump($posArr);
			$this->sendmail(88,$postArr);
			//DB保存

		}else{
			header("location:".BASEURL."/index/index/mail/");
		}
	}
	private function sendmail($p,$data) {
		$this->data = $data;
		//メールデータの取得
		$result = $this->_db->fetchAll(
			$this->_db->select()
			->from("mail_template")
			->where("name='infomation' AND parent=?",$p)
			->limit(1)
		);
		$mail = array();
		$detailStr = "ーーーーーーーーーーーー\n";
		$detailStr.= "お客様情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "会社名：".$this->data['company']."\n";
		$detailStr.= "部署名：".$this->data['div']."\n";
		$detailStr.= "お名前：".$this->data['name']."\n";
		$detailStr.= "フリガナ：".$this->data['kana']."\n";
		$detailStr.= "メールアドレス：".$this->data['mail']."\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "内容\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= $this->data['body']."\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		foreach($result[0] as $k=>$vv){
			$patterns[0] = '/%NAME%/';
			$patterns[1] = '/%MAIL%/';
			$patterns[2] = '/%ORDER%/';
			$patterns[3] = '/%SHOP%/';
			$patterns[4] = '/%SHOPDETAIL%/';
			$patterns[5] = '/%DELIVERY_PAPER%/';
			$replacements[0] = $this->data['company'].$this->data['name'];
			$replacements[1] = $this->data['mail'];
			$replacements[2] = $detailStr;
			$replacements[3] = $this->_setting['global']['SiteName'];
			$replacements[4] = 'ショップ詳細';
			$replacements[5] = $this->data['delivery_paper_no'];
			$result[0][$k] = preg_replace($patterns, $replacements, $vv);
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
			$this->data['mail'],
			$this->data['name'],
			$smtp
		);
		$this->_db->sendmail(
			true,
			$result[0]['subject'],
			$result[0]['body'],
			$this->data['mail'],
			$this->data['name'],
			$this->_setting['global']['infoMail'],
			$this->_setting['global']['SiteName'],
			$smtp
		);
	}
	$this->userpager($keyword,$class,$procedure,$service,$area,$user[1],$limit,$p,"/index/index/list/");
	*/
	private function userpager($keyword="",$class,$procedure,$service,$area,$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<div class=\"text-center\"><nav><ul class=\"pagination pagination-lg hover-style-2 justify-content-center margin-top-70\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a class=\"page-link\" href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\"><span aria-hidden=\"true\">&laquo; 前へ</span></a></li>";
			}
			if($p > 2){
				$pager.= "<li class=\"page-item{$avtive}\" ><a class=\"page-link\" href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=0\">1</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+2 || $i < $p-2){
				}else{
					if($i == $p){
						$avtive = " active";
					}else{
						$avtive = "";
					}
					$pager.= "<li class=\"page-item{$avtive}\" ><a class=\"page-link\" href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li class=\"page-item{$avtive}\" ><a class=\"page-link\" href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li><a class=\"page-link\" href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$next."\" aria-label=\"Next\"><span aria-hidden=\"true\">次&raquo;</span></a></li>";
			}
			$pager.= "</ul></nav></div>";
			$this->view->pager = $pager;
		}
	}
}
?>
