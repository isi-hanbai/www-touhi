<?php
define("IMG_DIR", HOMEDIR."catch/");

class Api_MailController extends Common_ApiController {
	private $_db;
	private $_user;
	public function init() {
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()){
			//セッション
			$this->_user = $auth->getIdentity();
			//DBへ接続
			require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
			$this->_db = new Model_Apiimageuser();
		}
	}
	//添付ファイルの削除
	public function deletetmpAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			if(isset($postArr['id'])){
				if($postArr['type'] =="mail"){
					$data = $this->_db->fetchAll(
						$this->_db->select()
						->from($postArr['type'],array("tmp"))
						->where("id=?",$postArr['id'])
						->limit(1)
					);

					$tmp = explode(",",$data[0]["tmp"]);
					foreach ($tmp as $k=>$v) {
						if($v == $postArr['file']){
							unset($tmp[$k]);
						}
					}
					$res = $this->_db->update(
						$postArr['type'],
						array(
							"tmp"=>implode(",",$tmp)
						),
						$this->_db->quoteInto("id=?",$postArr['id'])
					);
				}elseif($postArr['type'] =="ImageUser"){
					$data = $this->_db->fetchAll(
						$this->_db->select()
						->from($postArr['type'],array("tmp"))
						->where("id=?",$postArr['id'])
						->limit(1)
					);
					//$tmp = explode(",",$data[0]["tmp"]);
					$tmp = json_decode($data[0]["tmp"],true);
					foreach ($tmp as $k=>$v) {
						if($v == $postArr['file']){
							unset($tmp[$k]);
						}
					}
					$res = $this->_db->update(
						$postArr['type'],
						array(
							"tmp"=>implode(",",$tmp)
						),
						$this->_db->quoteInto("id=?",$postArr['id'])
					);
				}
					/*
				*/
			}
			if(unlink(IMG_DIR.$postArr['file'])){
				echo "OK";
			}
			/*
			*/
		}
	}
	//選択されたデータの削除
	public function deleteAction() {
		if($this->_request->isPost()){
			$idArr = $this->_request->getPost("ids");
			$whereArr = array();
			if(is_array($idArr)){
				foreach($idArr as $v){
					$whereArr[] ="`id`=".$v;
				}
			}else{
				$whereArr[] ="`id`=".$idArr;
			}
			$where = implode(" OR ",$whereArr);
			$data = $this->_db->fetchAll(
				$this->_db->select()
				->from("mail")
				->where($where)
			);
			//添付ファイルを消す
			foreach($data as $v){
				if($v['tmp']){
					$arr = explode(",",$v['tmp']);
					foreach($arr as $vv){
						unlink(IMG_DIR.$vv);
					}
				}
			}
			//データを消す
			if($this->_db->delete("mail",$where)){
				//データ消去が完了したら以下を出力
				echo "OK";
			}
		}
	}
	public function getmemAction(){
		if($this->_user->id){
			$data = $this->_db->fetchAll(
				$this->_db->select()
				->from("ImageUser",array("id"))
				->where("mail !=''")
				->where("parent =?",$this->_user->parent)
				->where("kind=3")
				->where("id=?",3290)
				->orWhere("id=?",3289)
				/*
				->orWhere("id=?",3280)
				->orWhere("id=?",3279)
				->orWhere("id=?",3278)
				*/
			);
			echo json_encode($data);
		}
	}
	public function sendmail2Action(){
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			if($postArr['id'] !="" && $postArr['mem'] !=""){
				$data = $this->_db->fetchAll(
					$this->_db->select()
					->from("mail")
					->where("id=?",$postArr['id'])
				);
				//smtp情報を取得
				$smtp2 = $this->_db->fetchAll(
					$this->_db->select()
					->from("global")
					->where("parent=?",$this->_user->parent)
				);
				$smtp = array();
				foreach($smtp2[0] as $k=>$v){
					if($k == "SMTPhost" || $k == "SMTPuser" || $k == "SMTPpass" || $k == "SMTPport" || $k == "SMTPsecur" || $k == "SMTPcertificate"){
						$smtp[$k] = $v;
					}
				}
				//会社情報を取得
				$company = $this->_db->fetchAll(
					$this->_db->select()
					->from("company")
					->where("parent=?",$this->_user->parent)
				);
				$subject = $data[0]['subject'];
				$body = $data[0]['body'];
				$fromMail = $smtp2[0]['infoMail'];
				$fromName = $company[0]['company'];
				//添付ファイル
				if($data[0]['tmp']){
					$attachfile = array();
					foreach(explode(",", $data[0]['tmp']) as $v){
						if($v !=""){
							$attachfile[] = $v;
						}
					}
				}else{
					$attachfile="";
				}
				//会員を取得
				$member = $this->_db->fetchAll(
					$this->_db->select()
					->from("ImageUser",array("name","mail"))
					->where("kind=3")
					->where("parent=?",$this->_user->parent)
					->where("id=?",$postArr['mem'])
					->limit(1)
				);
				$toMail = $member[0]['mail'];
				$toName = $member[0]['name'];
				if($this->_db->sendmail($autoRes,$subject,$body,$fromMail,$fromName,$toMail,$toName,$smtp,$attachfile) ==1){
					echo json_encode(array("name"=>$toName,"mail"=>$toMail,"status"=>success));
				}else{
					echo json_encode(array("name"=>$toName,"mail"=>$toMail,"status"=>faild));
				}
			}
		}
	}









	public function sendmailAction(){
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			if($postArr['id'] !=""){
				//メール内容を取得
				$data = $this->_db->fetchAll(
					$this->_db->select()
					->from("mail")
					->where("id=?",$postArr['id'])
				);
				//smtp情報を取得
				$smtp2 = $this->_db->fetchAll(
					$this->_db->select()
					->from("global")
					->where("parent=?",$this->_user->parent)
				);
				$smtp = array();
				foreach($smtp2[0] as $k=>$v){
					if($k == "SMTPhost" || $k == "SMTPuser" || $k == "SMTPpass" || $k == "SMTPport" || $k == "SMTPsecur" || $k == "SMTPcertificate"){
						$smtp[$k] = $v;
					}
				}
				//会社情報を取得
				$company = $this->_db->fetchAll(
					$this->_db->select()
					->from("company")
					->where("parent=?",$this->_user->parent)
				);
				$subject = $data[0]['subject'];
				$body = $data[0]['body'];
				$fromMail = $smtp2[0]['infoMail'];
				$fromName = $company[0]['company'];
				//添付ファイル
				if($data[0]['tmp']){
					$attachfile = array();
					foreach(explode(",", $data[0]['tmp']) as $v){
						if($v !=""){
							$attachfile[] = $v;
						}
					}
				}else{
					$attachfile="";
				}
				$autoRes = false;
				//会員一覧を取得
				$member = $this->_db->fetchAll(
					$this->_db->select()
					->from("ImageUser",array("name","mail"))
					->where("kind=3")
					->where("parent=?",$this->_user->parent)
					->where("id=?",3283)
				);
				foreach($member as $v){
					$toMail = $v['mail'];
					$toName = $v['name'];
					$this->_db->sendmail(
						$autoRes,
						$subject,
						$body,
						$fromMail,
						$fromName,
						$toMail,
						$toName,
						$smtp,
						$attachfile
					);
				}
				echo "OK";
				/*
				var_dump($member);
				*/
			}
		}
	}

}
?>
