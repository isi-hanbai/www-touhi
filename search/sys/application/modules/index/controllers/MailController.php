<?php
//コンポーネントのロード
class MailController extends Common_IndexController {
	//初期化メソッドの定義
	private $_db;
	public $_setting;
	private $sendmail;
	public function init(){
		$this->_db = new Model_Indexcontent();
		//設定の読み込み
		$setting = new Model_Indexsettings;
//		$this->_setting = $setting->setting("88");
		$this->_setting = $setting->setting("2998");
	}
	public function indexAction() {
		$this->view->title = "お問合せ";
		$this->view->bread = '<li><a href="https://www.touhi-ishikai.jp/"><i class="fa fa-tachometer"></i>HOME</a></li>
													<li>'.$this->view->title.'</li>';
		$this->view->add_script = '<script src="'.BASEURL.'/js/validation.js"></script>';
		$this->view->add_script.= '<script src="'.BASEURL.'/js/index/mail/mail.js"></script>';
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$subject = "お問い合わせありがとうございます。[東彼杵郡医師会]";
			$body = $postArr['name']."様\n";
			$body.= "この度は、東彼杵郡医師会へお問い合わせいただきありがとうございます\n";
			$body.= "下記の通りお問い合わが送信されました。\n";
			$body.= "内容の確認をさせていただき、改めてご連絡をさせていただきますので、\n";
			$body.= "今しばらくお待ち下さい\n";
			$body.= "ーーーーーーーーーーーーーーーーーーーーー\n";
			$body.= "お名前：".$postArr['name']."\n";
			$body.= "ーーーーーーーーーーーーーーーーーーーーー\n";
			$body.= "メールアドレス：".$postArr['mail']."\n";
			$body.= "ーーーーーーーーーーーーーーーーーーーーー\n";
			$body.= "電話番号".$postArr['tel']."\n";
			$body.= "ーーーーーーーーーーーーーーーーーーーーー\n";
			$body.= "お問い合わせ内容\n";
			$body.= "ーーーーーーーーーーーーーーーーーーーーー\n";
			$body.= $postArr['message']."\n";
			$body.= "ーーーーーーーーーーーーーーーーーーーーー\n";
			$body.= "\n";
			$body.= "ーーーーーーーーーーーーーーーーーーーーー\n";
			$body.= "東彼杵郡医師会\n";
			$body.= "〒859-3615\n";
			$body.= "長崎県東彼杵郡川棚町下組郷17番地7\n";
			$body.= "TEL：0956-82-5700\n";
			$body.= "ーーーーーーーーーーーーーーーーーーーーー\n";

			$fromMail = $this->_setting['global']['infoMail'];
			$fromName = $this->_setting['global']['SiteName'];
			$toMail = $postArr['mail'];
			$toName = $postArr['name'];
			$smtp = array();
			foreach($this->_setting['global'] as $k=>$v){
				if($k == "SMTPhost" || $k == "SMTPuser" || $k == "SMTPpass" || $k == "SMTPport" || $k == "SMTPsecur" || $k == "SMTPcertificate"){
					$smtp[$k] = $v;
				}
			}
			$attachfile="";
			###################################################################
			mb_language("Japanese");
			mb_internal_encoding("UTF-8");
			date_default_timezone_set("Asia/Tokyo");
			$finfo = new finfo();
			function mbCnv($string) {
				return mb_convert_encoding($string, 'ISO-2022-JP', 'UTF-8');
			}
			function mbMime($string) {
				return mb_encode_mimeheader(mbCnv($string), 'ISO-2022-JP', 'B');
			}

			$mail = new Zend_Mail('ISO-2022-JP');
			$mail->setSubject(mbCnv($subject));
			$mail->addTo($toMail, mbMime($toName));
			$mail->addBcc($fromMail, mbMime($fromName));
			$mail->addBcc("touhiisi@ruby.ocn.ne.jp");
			$mail->addBcc("tanpopo-zaitaku@road.ocn.ne.jp");
			$mail->setFrom($fromMail, mbMime($fromName));
			$mail->setBodyText(mbCnv($body), null, Zend_Mime::ENCODING_7BIT);
			/*
			if(is_array($attachfile)){
				for($i=0;$i<count($attachfile);$i++){
					$at = new Zend_Mime_Part(IMG_DIR.$attachfile[$i]);
					$at->type        = $finfo->file( IMG_DIR.$attachfile[$i], FILEINFO_MIME_TYPE);
					$at->disposition = Zend_Mime::DISPOSITION_INLINE;
					$at->encoding    = Zend_Mime::ENCODING_BASE64;
					$at->filename    = $attachfile[$i];

					$mail->addAttachment($at);
				}
			}
			*/
			$config = array(
				'port'=>$smtp['SMTPport'],
				'auth' => '',
				'username' => $smtp['SMTPuser'],
				'password' => $smtp['SMTPpass']
			);
			$smtp = new Zend_Mail_Transport_Smtp($smtp['SMTPhost'], $config);
			try {
				if($mail->send($smtp)){
					header("location:".BASEURL."/mail/send/");
				}
			} catch (Zend_Exception $e) {
				//echo $e or die;
				$this->view->msg = "メールを送信できませんでした。".$e;
			}
		###################################################################
		}
	}
	public function sendAction() {
		$this->view->title = "送信完了";
		$this->view->bread = '<li><a href="https://www.touhi-ishikai.jp/"><i class="fa fa-tachometer"></i>HOME</a></li>
													<li><a href="'.BASEURL.'/mail/"><i class="fa fa-tachometer"></i>お問合せ</a></li>
													<li>'.$this->view->title.'</li>';
	}

}
?>
