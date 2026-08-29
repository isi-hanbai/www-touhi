<?php
	class Model_Logingeneral extends Zend_Db_Table_Abstract {
		//データベースアダプタ用メンバー変数を定義
		public $_db;
		
		public function __construct(){
			//データベースへの接続
			$this->_db = Zend_Registry::get('db');
		}
		
		//ユーザーの権限に応じてリダイレクト
		public function redirect($authority = 0,$merchant=NULL){
			if($authority == 1){
				//管理者
				header("Location:".BASEURL."/admin/");
			}elseif($authority == 2){
				//画像提供者
				header("Location:".BASEURL."/editor/");
			}elseif($authority == 3){
				//ユーザー
				header("Location:".BASEURL."/member/");
			}elseif($authority == 4){
				//マーチャント
				header("Location:".BASEURL."/merchant/");
			}else{
				//ログイン画面へ
				if($merchant){
					//ユーザーの場合
					header("Location:".BASEURL."/login/?m=".$merchant);
				}else{
					//ユーザー以外の場合
					header("Location:".BASEURL."/login/");
				}
			}
		}
		
		//文字列を暗号化
		public function pwHash($pw){
			return sha1("serialize".md5($pw)."by_kmjcrew");
		}
	}
?>