<?php
	class Model_Editorgeneral extends Zend_Db_Table_Abstract {
		//データベースアダプタ用メンバー変数を定義
		public $_db;

		public function __construct(){
			//データベースへの接続
			$this->_db = Zend_Registry::get('db');
		}
		/*DB問い合わせ用*/
		public function select(){
			$result = $this->_db->select();
			return $result;
		}
		public function fetchAll($q){
			$result = $this->_db->fetchAll($q);
			return $result;
		}
		public function fetchAssoc($q){
			$result = $this->_db->fetchAssoc($q);
			return $result;
		}
		public function send($q){
			$result = $this->_db->query($q);
			return $result;
		}
		public function insert($table,$q){
			$result = $this->_db->insert($table,$q);
			return $result;
		}
		public function insertAndGetLastId($table,$q){
			$this->_db->insert($table,$q);
			$result = $this->_db->lastInsertId();
			return $result;
		}
		public function delete($table,$where){
			$result = $this->_db->delete($table, $where);
			return $result;
		}
		public function update($table,$params,$where){
			$result = $this->_db->update($table,$params, $where);
			return $result;
		}
		public function quoteInto($col,$num){
			$result = $this->_db->quoteInto($col,$num);
			return $result;
		}
		public function getcommon(){
			$q = "select * from common";
			$result = $this->_db->fetchAll($q);
			return $result;
		}
		public function getNumRows($table,$where){
			$q = 'SELECT COUNT(*) AS count FROM '.$table.$where;
			return $this->_db->fetchOne('SELECT COUNT(*) AS count FROM '.$table.$where);
		}
		public function lastInsertId($table){
			return $this->_db->lastInsertId($table);
		}

		/*コントローラー用の共通関数*/
		public function pwHash($pw){
			return sha1("serialize".md5($pw)."by_kmjcrew");
		}
		public function postArray(){
			$arr = array();
			foreach($_POST as $k=>$v){
				if(is_array($v)){
					$arr2 = array();
					foreach($v as $kk=>$vv){
						$arr2[$kk] = htmlspecialchars(stripslashes($vv), ENT_QUOTES, 'UTF-8');
					}
					$arr[$k] = json_encode($arr2);
				}else{
					$arr[$k] = htmlspecialchars(stripslashes($v), ENT_QUOTES, 'UTF-8');
				}
			}
			return $arr;
		}
		public function getArray(){
			$arr = array();
			foreach($_GET as $k=>$v){
				if(is_array($v)){
					$arr2 = array();
					foreach($v as $kk=>$vv){
						$arr2[$kk] = htmlspecialchars(stripslashes($vv), ENT_QUOTES, 'UTF-8');
					}
					$arr[$k] = json_encode($arr2);
				}else{
					$arr[$k] = htmlspecialchars(stripslashes($v), ENT_QUOTES, 'UTF-8');
				}
			}
			return $arr;
		}
		/*
		public function postArray(){
			$arr = array();
			foreach($_POST as $k=>$v){
				$arr[$k] = htmlspecialchars(stripslashes($v), ENT_QUOTES, 'UTF-8');
			}
			return $arr;
		}
		*/
		public function postArray2($post){
			$arr = array();
			foreach($post as $k=>$v){
				$arr[$k] = $v;
			}
			return $arr;
		}

		public function h($s) {
			return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
		}
		public function sendmail($autoRes = false,$subject,$body,$fromMail,$fromName,$toMail,$toName,$smtp,$attachfile=""){
			###################################################################
			mb_language("Japanese");
			mb_internal_encoding("UTF-8");
			require_once("PHPMailer/class.phpmailer.php");
			date_default_timezone_set("Asia/Tokyo");
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->SMTPAuth   =$smtp['SMTPcertificate'];
			$mail->SMTPSecure = $smtp['SMTPsecur'];
			$mail->Host       =$smtp['SMTPhost'];
			$mail->Port       = $smtp['SMTPport'];
			$mail->Username   = $smtp['SMTPuser'];
			$mail->Password   = $smtp['SMTPpass'];
			$mail->CharSet    = "iso-2022-jp";
			$mail->Encoding   = "7bit";
			$mail->IsHTML(false);
			$mail->From       = $fromMail;//送信者メール
			$mail->FromName   = mb_encode_mimeheader(mb_convert_encoding($fromName, "JIS", "utf-8"));
			$mail->AddReplyTo($fromMail, mb_encode_mimeheader(mb_convert_encoding($fromMail, "JIS", "utf-8")));
			$mail->Subject    = mb_convert_encoding($subject, "JIS", "utf-8");
			$mail->Body       = mb_convert_encoding($body, "JIS", "utf-8");
			$mail->AddAddress($toMail, mb_encode_mimeheader(mb_convert_encoding($toMail, "JIS", "utf-8")));
			$mail->AddBCC($fromMail, $fromName);
			if($attachfile){
				//添付ファイル
				$mail->AddAttachment($attachfile);
			}
			if(!$mail->Send()) {
				$m_result = "E";
			} else {
				$m_result = "T";
			}
			###################################################################
		}
	}
?>
