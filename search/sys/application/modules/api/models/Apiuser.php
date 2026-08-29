<?php
	require_once(APPLICATION_PATH."/modules/api/models/Apigeneral.php");
	class Model_Apiuser extends Model_Apigeneral {
		public function setToken($name="",$user){
			$token = md5(time().$user);
			if($name==""){
				$name = "未設定のデバイス";
			}
			$arr = array(
				"name"=>$name,
				"user"=>$user,
				"token"=>$token,
				"created"=>date("Y-m-d H:i:s")
			);
			if($this->_db->insert("deviceAuth",$arr)){
				return $token;
			}else{
				return "NG";
			}
		}
		public function getTokenAuth($token){
			$cnt = $this->_db->fetchAll(
				$this->_db->select()
				->from("deviceAuth",array("COUNT(*) AS n","user"))
				->where("token=?",$token)
			);
			if($cnt[0]["n"]==0){
				return false;
			}else{
				return $cnt[0]["user"];
			}
		}
	}
?>