<?php
	class Model_Adminpaper extends Model_Admingeneral {
		public function printPdf($kind = 1,$date = 0,$data=array(),$item=array(),$user=array(),$company=array()){
			if($date ==0){
				$date = date("Y-m-d");
			}
			/*
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
			*/
			echo 123;
		}










		public function print2Pdf($kind = 1,$date = 0,$arr,$user,$company){

			if($date ==0){
				$date = date("Y-m-d");
			}

			//FPDFライブリの読み込み
			require_once 'PEAR/fpdf/japanese.php';
			mb_language("ja");
			mb_internal_encoding('SJIS');
			define(SC_CHAR, "UTF-8");

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

			foreach($arr as $vvvv){
				//1枚ずつ改ページ
				if($kind == 4 || $kind == 5 || $kind == 6 || $kind == 7){
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
					/*
					//印鑑画像の表示
					if($kind != 6){
						if($company['stamp']){
							$pdf->Image(BASEURL.$company['stamp'], $x, $y, 25,25);
						}
					}
					*/
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
					$oy_post = "〒".$vvvv['data']['cus_zip']."-".$vvvv['data']['cus_zip2'];
					$pdf->Text(28,22, sjis_conv($oy_post));// 注文主郵便番号
					$pdf->SetXY(28, 24);
					$pdf->MultiCell(70, 4, sjis_conv($vvvv['data']['cus_prefName'].$vvvv['data']['cus_addr']."\n".$vvvv['data']['cus_addr2']));// 注文主住所
					$pdf->SetFont('SJIS', '', 12);
					$c_name = $vvvv['data']['cus_name']." ".$vvvv['data']['cus_name2']."様";
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
					$pdf->Text(10,74, sjis_conv("お買上げ金額：".number_format($vvvv['data']['seikyu_cost'])."円"));// 請求金額


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
					foreach($vvvv['item'] as $v){
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
					$arr_size =count($vvvv['item']);

					if($kind == 5 || $kind == 6){
							$c = 10;
					}else{
						if($arr['data']['check_payment_end'] !=""){
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
					$pdf->Cell($w4,7,sjis_conv(spacePadding($vvvv['data']['item_total'],12)."円"),1,"",'RB',0);
					$pdf->Ln();
					$pdf->Cell($w6,7,sjis_conv("消費税"),1,'L',0);
					$pdf->Cell($w4,7,sjis_conv(spacePadding($vvvv['data']['tax_total'],12)."円"),1,"",'RB',0);
					$pdf->Ln();
					$pdf->Cell($w6,7,sjis_conv("送料"),1,'L',0);
					$pdf->Cell($w4,7,sjis_conv(spacePadding($vvvv['data']['shipping_cost'],12)."円"),1,"",'RB',0);
					$pdf->Ln();
					if(!empty($vvvv['data']['collect_cost'])){
						$pdf->Cell($w6,7,sjis_conv("代引手数料"),1,'L',0);
						$pdf->Cell($w4,7,sjis_conv(spacePadding($vvvv['data']['collect_cost'],12)."円"),1,"",'RB',0);
						$pdf->Ln();
					}
					if(!empty($vvvv['data']['use_point'])){
						$data['use_point'] = "-".$vvvv['use_point'];
						$pdf->Cell($w6,7,sjis_conv("ポイント使用"),1,'L',0);
						$pdf->Cell($w4,7,sjis_conv(spacePadding($vvvv['data']['use_point'],12)."円"),1,"",'RB',0);
						$pdf->Ln();
					}
					if(!empty($vvvv['data']['off_price'])){
						$data['off_price'] = "-".$vvvv['off_price'];
						$pdf->Cell($w6,7,sjis_conv("割引"),1,'L',0);
						$pdf->Cell($w4,7,sjis_conv(spacePadding($vvvv['data']['off_price'],12)."円"),1,"",'RB',0);
						$pdf->Ln();
					}
					$pdf->Cell($w6,7,sjis_conv("合計"),1,'L',0);
					$pdf->Cell($w4,7,sjis_conv(spacePadding($vvvv['data']['seikyu_cost'],12)."円"),1,"",'RB',0);
					$pdf->Ln();
					$pdf->Ln();

					$y = $pdf->GetY();
					$pdf->SetXY(10, $y);
					$otodoke = "備考";
					$otodoke = "お届け先様："
					.$vvvv['data']['delivery_name']
					."　".$vvvv['data']['delivery_name2']
					."(".$vvvv['data']['delivery_kana'].$vvvv['data']['delivery_kana2'].")様\n〒"
					.$vvvv['data']['delivery_zip']."-".$vvvv['data']['delivery_zip2']." "
					.$vvvv['data']['delivery_prefName']
					.$vvvv['data']['delivery_addr']
					.$vvvv['data']['delivery_addr2']."\n";
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
					$pdf->AddPage();
					$pdf->SetFont('SJIS', 'B', 12);

					//商品詳細を出力
					$pdf->Image(BASEURL.'/img/logo.jpg', 80, 20, 50,11.7);
					//帳票タイトルの表示
					$headding = "お買上げ明細書";
					$pdf->Text(91, 40, sjis_conv($headding));//headding
					$pdf->SetFont('SJIS', '', 10);
					$c_name = $vvvv['data']['cus_name']." ".$vvvv['data']['cus_name2']."様";
					$pdf->SetXY(20, 45);
					$pdf->MultiCell(70, 4.5, sjis_conv($c_name));
					$pdf->SetFont('SJIS', '', 8);
					$pdf->SetXY(20, 53);
					$str = "このたびはジョン&マリーでお買上げいただき、誠にありがとうございます。\n丁寧に作られたオーガニック製品の心地よさを存分にお楽しみください。";
					if($vvvv['data']['c']==1 && $vvvv['data']['member_no']){
						$pdf->Text(21, 70, sjis_conv( "新規会員登録ありがとうございます。"));// 会社名
					}
					$pdf->MultiCell(105, 4.5, sjis_conv($str));
					//$pdf->MultiCell(105, 4.5, sjis_conv("このたびはジョン&マリーでお買上げいただき、誠にありがとうございます。\n丁寧に作られたオーガニック製品の心地よさを存分にお楽しみください。"));
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
					."　　　".$vvvv['data']['delivery_name']
					."　".$vvvv['data']['delivery_name2']
					."(".$vvvv['data']['delivery_kana'].$vvvv['data']['delivery_kana2'].")様\n"
					."　　　〒".$vvvv['data']['delivery_zip']."-".$vvvv['data']['delivery_zip2']."\n"
					."　　　".$vvvv['data']['delivery_prefName']
					.$vvvv['data']['delivery_addr']
					.$vvvv['data']['delivery_addr2']."\n";
					$y = $pdf->GetY()-1;
					$pdf->SetXY(20, $y);
					$pdf->MultiCell(170, 4, sjis_conv("$otodoke"),0,"LT");

					//お届け日時などの表示
					$pdf->SetFont('SJIS', '', 8);
					$otodoke2 = "■ご注文日：".date("Y年m月d日",strtotime($vvvv['data']['orderDatetime']))."\n";
					$otodoke2.= "■受注番号：".$vvvv['data']['order_id']."\n";
					$otodoke2.= "■決済方法：".$vvvv['data']['payment_methodName']."\n";
					if($vvvv['data']['member_no']){
						$otodoke2.= "■会員区分：会員　ポイント残高：".$vvvv['data']['point']."pt\n";
					}
					if($vvvv['data']['delivery_date'] !="0000-00-00"){
						$otodoke2.= "■お届け日：".date("Y年m月d日",strtotime($vvvv['data']['delivery_date']))."\n";
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
					foreach($vvvv['item'] as $v){
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
					$arr_size =count($vvvv['item']);

					if($kind == 5 || $kind == 6){
							$c = 5;
					}else{
						if($arr['data']['check_payment_end'] !=""){
							$c = 5;
						}else{
							$c = 5;
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
					if($vvvv['data']['subscriptionsTurn'] >0){
						$pdf->Ln();
						$pdf->Ln();
						$pdf->SetX(20);
						$pdf->SetFont('SJIS', '', 12);
						$pdf->SetTextColor(103, 72, 24);
						$pdf->SetDrawColor(103, 72, 24);
						$pdf->MultiCell(105, 10, sjis_conv("　".$vvvv['data']['course'].":".$vvvv['data']['subscriptionsTurn']."回目"),1,"LT");
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
					$pdf->Cell(60, 5, sjis_conv($vvvv['data']['item_total']."円"), 1,"", 'C', 0);
					$pdf->Ln();

					$y = $pdf->GetY();
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
					$pdf->SetXY(130,$y);
					$pdf->Cell(60, 5, sjis_conv('消費税'), 1,"", 'C', 0);
					$pdf->Ln();
					$pdf->SetX(130);
					$pdf->Cell(60, 5, sjis_conv($vvvv['data']['tax_total']."円"), 1,"", 'C', 0);
					$pdf->Ln();

					$y = $pdf->GetY();
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
					$pdf->SetXY(130,$y);
					$pdf->Cell(60, 5, sjis_conv('送料'), 1,"", 'C', 0);
					$pdf->Ln();
					$pdf->SetX(130);
					$pdf->Cell(60, 5, sjis_conv($vvvv['data']['shipping_cost']."円"), 1,"", 'C', 0);
					$pdf->Ln();

					if(!empty($vvvv['data']['collect_cost'])){
						$y = $pdf->GetY();
						$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
						$pdf->SetXY(130,$y);
						$pdf->Cell(60, 5, sjis_conv('決済手数料'), 1,"", 'C', 0);
						$pdf->Ln();
						$pdf->SetX(130);
						$pdf->Cell(60, 5, sjis_conv($vvvv['data']['collect_cost']."円"), 1,"", 'C', 0);
						$pdf->Ln();
					}

					if(!empty($vvvv['data']['use_point'])){
						$y = $pdf->GetY();
						$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
						$pdf->SetXY(130,$y);
						$pdf->Cell(60, 5, sjis_conv('ポイント利用額'), 1,"", 'C', 0);
						$pdf->Ln();
						$pdf->SetX(130);
						$pdf->Cell(60, 5, sjis_conv("-".$vvvv['data']['use_point']."円"), 1,"", 'C', 0);
						$pdf->Ln();
					}
					if(!empty($vvvv['data']['off_price'])){
						$y = $pdf->GetY();
						$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y), 60,5);
						$pdf->SetXY(130,$y);
						$pdf->Cell(60, 5, sjis_conv('割引'), 1,"", 'C', 0);
						$pdf->Ln();
						$pdf->SetX(130);
						$pdf->Cell(60, 5, sjis_conv($vvvv['data']['off_price']."円"), 1,"", 'C', 0);
						$pdf->Ln();
					}
					$y = $pdf->GetY();
					$pdf->Image(BASEURL.'/uploads/obi.jpg', 130, ($y+7), 60,5);
					$pdf->SetXY(130,$y+7);
					$pdf->Cell(60, 5, sjis_conv('お買上金額'), 1,"", 'C', 0);
					$pdf->Ln();
					$pdf->SetX(130);
					$pdf->SetFont('SJIS', 'B', 10);
					$pdf->Cell(60, 6, sjis_conv($vvvv['data']['seikyu_cost']."円"), 1,"", 'C', 0);
					$pdf->Ln();

					$pdf->Ln();
				}
				if($kind == 5 || $kind == 6){
				}else{
					if($vvvv['data']['check_payment_end'] !=""){
						//領収書部
						$x = 173;
						$y = 27;
						//$pdf->Ln();
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
						$pdf->Cell(150, 7, sjis_conv(date("Y",strtotime($vvvv['data']['check_payment_end']))."年".date("m月d日",strtotime($vvvv['data']['check_payment_end']))), 0, '', 0);
						$pdf->Ln();
						$y = $pdf->GetY();
						$pdf->Line(10,$y,90,$y);
						$pdf->Ln();
						$y = $pdf->GetY();
						$pdf->SetFont('SJIS', 'B', 14);
						$pdf->Image(BASEURL.'/uploads/obi.jpg', 10, $y, 180,10);
						$pdf->Cell(180, 10, sjis_conv('     ¥').number_format($vvvv['data']['seikyu_cost']), 1,20,'LTRB', 0);
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
			}
			// PDFをブラウザに送信
			ob_end_clean();
			$pdf->Output('/home/kmjc/john-mary.com/public_html/shop2/sample.pdf', "F");
		}
	}

			public function printPdf4($kind = 1,$date = 0,$data,$item,$user,$company,$sougi){

				if($date ==0){
					$date = date("Y-m-d");
				}
				//array_multisort($kk,SORT_DESC,$itemArr);
				//$item = array_merge($itemArr,$noCategrory);

				//FPDFライブリの読み込み
				require_once 'PEAR/fpdf/japanese.php';
				mb_language("ja");
				mb_internal_encoding('SJIS');
				define(SC_CHAR, "UTF-8");
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
				$pdf->Text(170, 12, sjis_conv("発注書"));//headding
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
				if($kind != 5){
					if($company['stamp']){
						$pdf->Image(BASEURL.$company['stamp'], $x, $y, 25,25);
					}
				}
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
				$oy_post = "〒".$data['trader_zip']."-".$data['trader_zip2'];
				$pdf->Text(28,22, sjis_conv($oy_post));// 注文主郵便番号
				$pdf->SetXY(28, 24);
				$pdf->MultiCell(70, 4, sjis_conv($data['trader_prefName'].$data['trader_addr']."\n".$data['trader_addr2']));// 注文主住所

				/***/
				$pdf->SetFont('SJIS', '', 12);
				$c_name = $data['trader_name']."御中";
				$pdf->SetXY(28, 35);
				$pdf->MultiCell(70, 4.5, sjis_conv($c_name));

				$pdf->SetFont('SJIS', '',9);
				$pdf->Text(10,63, sjis_conv("平素よりお世話になります。下記の通り発注させていただきます。"));

				$pdf->SetY(75);
				//商品詳細を出力
				$pdf->Image(BASEURL.'/uploads/obi.jpg', 10, 70, 190,7);
				$pdf->SetFont('SJIS', '',12);
				$w1 = 75;
				$w2 = 45;
				$w3 = 10;
				$w4 = 60;
				$w6 = 130;
				$pdf->SetY(70);
				$pdf->Cell($w1,7,sjis_conv("商品名"),1,'L',"L");
				$pdf->Cell($w2,7,sjis_conv("単価"),1,"",'L',0);
				$pdf->Cell($w3,7,sjis_conv("数量"),1,"",'L',0);
				$pdf->Cell($w4,7,sjis_conv("価格"),1,"",'L',0);
				$pdf->Ln();
				//$item = array_reverse($item);
				//価格を税抜きに変更
				foreach($item as $v){
					$price = spacePadding($v['price'],4);
					$pdf->Cell($w1, 7, sjis_conv($v['name']), 1, '',"RB");
					$pdf->Cell($w2, 7, sjis_conv($price."円"), 1, "",'RB', 0);
					$pdf->Cell($w3, 7, sjis_conv($v['quantity']/* .$v['unit']*/ ), 1,"", 'RB', 0);
					$kakaku =$v['price'] *$v['quantity'] ;
					$kakaku = spacePadding($kakaku,7);
					$pdf->Cell($w4, 7, sjis_conv($kakaku."円"), 1, "",'RB', 0);
					$pdf->Ln();
				}
				//空白行を挿入
				$arr_size =count($item);
				$c =5;
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
				$pdf->Cell($w6,7,sjis_conv("小計"),1,'L',"L");
				$pdf->Cell($w4,7,sjis_conv(spacePadding($data['item_total'],7)."円"),1,"",'L',0);
				$pdf->Ln();
				$pdf->Cell($w6,7,sjis_conv("消費税"),1,'L',"L");
				$pdf->Cell($w4,7,sjis_conv(spacePadding($data['tax_total'],7)."円"),1,"",'L',0);
				$pdf->Ln();
				$pdf->Cell($w6,7,sjis_conv("合計"),1,'L',"L");
				$pdf->Cell($w4,7,sjis_conv(spacePadding($data['seikyu_cost'],7)."円"),1,"",'L',0);
				$pdf->Ln();



				$pdf->Ln();
				$pdf->SetFont('SJIS', '',10);
				$pdf->SetFillColor(225);
				$w1 = 30;
				$w2 = 65;
				$w3 = 30;
				$w4 = 65;
				$w6 = 160;
				if(!empty($sougi)){
					//葬儀情報
					$y = $pdf->GetY();
					$pdf->Text(10,$y, sjis_conv("■ご葬儀情報"));
					$y = $pdf->GetY()+3;
					$pdf->SetY($y);
					$pdf->Cell($w1,7,sjis_conv("喪家名"),1,"",'L',1);
					$pdf->Cell($w2,7,sjis_conv($sougi['kojin_name']."（".$sougi['kojin_kana']."）家"),1,"",'L',0);
					$pdf->Cell($w3,7,sjis_conv("出棺日時"),1,"",'L',1);
					$pdf->Cell($w4,7,sjis_conv($sougi['souY']."年".$sougi['souM']."月".$sougi['souD']."日".($sougi['souH']+1)."時".$sougi['souI']."分"),1,"",'L',0);
					$pdf->Ln();
					$pdf->Cell($w1,7,sjis_conv("通夜"),1,"",'L',1);
					$pdf->Cell($w4,7,sjis_conv($sougi['tuyaY']."年".$sougi['tuyaM']."月".$sougi['tuyaD']."日".$sougi['tuyaH']."時".$sougi['tuyaI']."分"),1,"",'L',0);
					$pdf->Cell($w3,7,sjis_conv("葬儀"),1,"",'L',1);
					$pdf->Cell($w4,7,sjis_conv($sougi['souY']."年".$sougi['souM']."月".$sougi['souD']."日".$sougi['souH']."時".$sougi['souI']."分"),1,"",'L',0);
					$pdf->Ln();
					$pdf->Cell($w1,7,sjis_conv("会場"),1,"",'L',1);
					$pdf->Cell($w6,7,sjis_conv($sougi['kaijou_name'].$sougi['kaijou_room']." ".$sougi['kaijou_addr']),1,"",'L',0);
					$pdf->Ln();
					$pdf->Cell($w3,7,sjis_conv("火葬場"),1,"",'L',1);
					$pdf->Cell($w6,7,sjis_conv($sougi['kasou_name']." ".$sougi['kasou_addr']),1,"",'L',0);
				}
				$pdf->Ln();
				//備考
				$y = $pdf->GetY()+10;
				$pdf->Text(10,$y, sjis_conv("■備考"));
				$y = $pdf->GetY()+13;
				$pdf->SetY($y);
				$pdf->MultiCell(190, 8, sjis_conv($data['bikou']), 1, 'L', 0);
				$pdf->Ln();

				$y = $pdf->GetY();
				$pdf->Line(10.0, $y, 200.0, $y);
				//確認欄
				$y = $pdf->GetY()+15;
				$pdf->Text(10,$y, sjis_conv("■確認欄"));
				$y = $pdf->GetY()+18;
				$pdf->SetY($y);

				$pdf->MultiCell(190, 8, sjis_conv("お手数ですが、内容をご確認いただきましたら確認欄をご記入の上、下記のところまでご連絡をお願い致します。\nFAX：092-575-4187\nE-mail:support@nanpuku.co.jp"), 1, 'L', 0);
				$pdf->Cell($w1,12,sjis_conv("確認日"),1,"",'L',1);
				$pdf->Cell($w2,12,sjis_conv("　　　　　　月　　　　　　　日"),1,"",'L',0);
				$pdf->Cell($w3,12,sjis_conv("確認者"),1,"",'L',1);
				$pdf->Cell($w4,12,sjis_conv(""),1,"",'L',0);
				$pdf->Ln();
				//
				// PDFをブラウザに送信
				ob_end_clean();
				$pdf->Output('catch/hattyuu.pdf', 'F');
			}
		}

	function sjis_conv($conv_str) {
		return (mb_convert_kana(mb_convert_encoding($conv_str, "SJIS", SC_CHAR),"RNA") );
	}
	function spacePadding($n,$c) {
		$cnt = $c+5-strlen($n);
		$sp="";
		for($i=0;$i<$cnt;$i++){
			$sp.=" ";
		}
		return $sp.$n;
	}
?>
