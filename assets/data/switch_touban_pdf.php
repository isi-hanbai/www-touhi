<?php
/**
 * PDFスケジュール切り替え & 管理画面
 */

// ▼▼▼ 設定エリア ▼▼▼
// 管理画面のパスワード (必ず変更してください！)
$admin_password = 'password123!20251217'; 

// PDFの実体が置いてあるディレクトリ (このファイルの相対パス)
$pdf_dir = 'touban';

// スケジュール保存用ファイル
$json_file = 'switch_touban_pdf.json';

// タイムゾーン
date_default_timezone_set('Asia/Tokyo');
// ▲▲▲ 設定エリアここまで ▲▲▲


// ==========================================
// モード判定
// ==========================================
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'admin';

// データファイルディレクトリのパス
$base_dir = dirname(__FILE__);
$source_dir = $base_dir . '/' . $pdf_dir;
$json_path  = $base_dir . '/' . $json_file;

// スケジュールデータの読み込み関数
function load_schedule($path) {
    if (file_exists($path)) {
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }
    return [];
}

// ==========================================
// 1. PDF表示モード (ユーザーからのアクセス)
// ==========================================
if ($mode === 'view') {
    $schedule = load_schedule($json_path);
    $current_time = date('Y-m-d H:i:s');
    
    // 日付でソート（新しい日付順）
    usort($schedule, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });

    $file_to_show = '';

    // 現在時刻を過ぎている中で、最も新しい設定を探す
    foreach ($schedule as $item) {
        if ($current_time >= $item['date']) {
            $file_to_show = $item['file'];
            break;
        }
    }

    // 該当ファイルパス
    $full_path = $source_dir . '/' . $file_to_show;

    // ファイルが存在すれば出力、なければ404
    if ($file_to_show && file_exists($full_path)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="touban.pdf"');
        header('Content-Length: ' . filesize($full_path));
        // キャッシュ制御（更新を即反映させるためキャッシュさせない）
        header("Cache-Control: no-cache, must-revalidate");
        readfile($full_path);
        exit;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "PDF File not found.";
        exit;
    }
}

// ==========================================
// 2. 管理画面モード (直接アクセス)
// ==========================================

session_start();

// ログアウト処理
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['is_logged_in']);
    header('Location: ' . basename(__FILE__));
    exit;
}

// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['is_logged_in'] = true;
    } else {
        $error = "パスワードが違います";
    }
}

// 未ログイン時はログインフォームを表示
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>管理ログイン</title></head>
<body style="font-family:sans-serif; padding:20px; text-align:center;">
    <h2>管理画面ログイン</h2>
    <?php if(isset($error)) echo '<p style="color:red;">'.$error.'</p>'; ?>
    <form method="post">
        <input type="password" name="password" placeholder="パスワード" required style="padding:10px;">
        <button type="submit" style="padding:10px;">ログイン</button>
    </form>
</body>
</html>
<?php
    exit;
}

// --- 以下、ログイン後の処理 ---

// アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_upload'])) {
    $upload_err = '';
    $tmp_name = $_FILES['pdf_upload']['tmp_name'];
    $name = basename($_FILES['pdf_upload']['name']);
    
    if (is_uploaded_file($tmp_name)) {
        if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'pdf') {
            $dest = $source_dir . '/' . $name;
            if (move_uploaded_file($tmp_name, $dest)) {
                $message = "ファイル {$name} をアップロードしました。";
            } else {
                $upload_err = "アップロードに失敗しました。";
            }
        } else {
            $upload_err = "PDFファイルのみアップロード可能です。";
        }
    }
}

// 保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
    $dates = $_POST['dates'];
    $files = $_POST['files'];
    $files2 = isset($_POST['files2']) ? $_POST['files2'] : [];
    $new_schedule = [];

    // FPDF and FPDI Autoload
    require_once $base_dir . '/lib/fpdf.php';
    require_once $base_dir . '/lib/autoload.php';

    for ($i = 0; $i < count($dates); $i++) {
        if (!empty($dates[$i]) && !empty($files[$i])) {
            $file1 = $files[$i];
            $file2 = isset($files2[$i]) ? $files2[$i] : '';
            $target_file = $file1;

            if (!empty($file2) && $file1 !== $file2) {
                // 結合済みのファイル名を生成
                $merged_filename = pathinfo($file1, PATHINFO_FILENAME) . '_' . pathinfo($file2, PATHINFO_FILENAME) . '_merged.pdf';
                $merged_path = $source_dir . '/' . $merged_filename;
                $file1_path = $source_dir . '/' . $file1;
                $file2_path = $source_dir . '/' . $file2;
                
                $merged_success = false;
                $exec_enabled = function_exists('exec') && !in_array('exec', array_map('trim', explode(',', strtolower(ini_get('disable_functions')))));

                // 1. Ghostscriptが利用可能かチェックしてマージ (圧縮PDFに対応できるため最優先)
                if ($exec_enabled) {
                    @exec('gs --version', $gs_out, $gs_ret);
                    if (isset($gs_ret) && $gs_ret === 0) {
                        $cmd = sprintf('gs -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=%s -dBATCH %s %s', escapeshellarg($merged_path), escapeshellarg($file1_path), escapeshellarg($file2_path));
                        @exec($cmd, $out, $ret);
                        if (isset($ret) && $ret === 0 && file_exists($merged_path)) {
                            $merged_success = true;
                            $target_file = $merged_filename;
                        }
                    }
                }

                // 2. Ghostscriptが使えない場合は pdftk を試す
                if (!$merged_success && $exec_enabled) {
                    @exec('pdftk --version', $pdftk_out, $pdftk_ret);
                    if (isset($pdftk_ret) && $pdftk_ret === 0) {
                        $cmd = sprintf('pdftk %s %s cat output %s', escapeshellarg($file1_path), escapeshellarg($file2_path), escapeshellarg($merged_path));
                        @exec($cmd, $out, $ret);
                        if (isset($ret) && $ret === 0 && file_exists($merged_path)) {
                            $merged_success = true;
                            $target_file = $merged_filename;
                        }
                    }
                }

                // 3. どちらのコマンドも無い場合は PHP単体(FPDI) で結合を試みる
                if (!$merged_success) {
                    try {
                        $pdf = new \setasign\Fpdi\Fpdi();
                        
                        $pageCount1 = $pdf->setSourceFile($file1_path);
                        for ($pageNo = 1; $pageNo <= $pageCount1; $pageNo++) {
                            $templateId = $pdf->importPage($pageNo);
                            $size = $pdf->getTemplateSize($templateId);
                            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $pdf->useTemplate($templateId);
                        }
                        
                        $pageCount2 = $pdf->setSourceFile($file2_path);
                        for ($pageNo = 1; $pageNo <= $pageCount2; $pageNo++) {
                            $templateId = $pdf->importPage($pageNo);
                            $size = $pdf->getTemplateSize($templateId);
                            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $pdf->useTemplate($templateId);
                        }
                        
                        $pdf->Output('F', $merged_path);
                        $target_file = $merged_filename;
                        $merged_success = true;
                    } catch (\Exception $e) {
                        $is_compression_error = (strpos($e->getMessage(), 'compression technique') !== false || strpos($e->getMessage(), 'CrossReferenceException') !== false);
                        
                        if ($is_compression_error && extension_loaded('imagick')) {
                            // --- Imagickによる画像化→PDF結合フォールバック ---
                            try {
                                $pdf = new FPDF();
                                
                                // ファイル1を画像化して追加
                                $im1 = new Imagick();
                                $im1->setResolution(150, 150);
                                $im1->readImage($file1_path);
                                foreach ($im1 as $page) {
                                    $page->setImageFormat('jpeg');
                                    $tmp_jpg = $source_dir . '/tmp1_' . uniqid() . '.jpg';
                                    $page->writeImage($tmp_jpg);
                                    $pdf->AddPage('P', 'A4');
                                    // A4縦にフィットさせて配置 (210x297mm)
                                    $pdf->Image($tmp_jpg, 0, 0, 210, 297);
                                    @unlink($tmp_jpg);
                                }
                                $im1->clear(); $im1->destroy();
                                
                                // ファイル2を画像化して追加
                                $im2 = new Imagick();
                                $im2->setResolution(150, 150);
                                $im2->readImage($file2_path);
                                foreach ($im2 as $page) {
                                    $page->setImageFormat('jpeg');
                                    $tmp_jpg = $source_dir . '/tmp2_' . uniqid() . '.jpg';
                                    $page->writeImage($tmp_jpg);
                                    $pdf->AddPage('P', 'A4');
                                    $pdf->Image($tmp_jpg, 0, 0, 210, 297);
                                    @unlink($tmp_jpg);
                                }
                                $im2->clear(); $im2->destroy();
                                
                                $pdf->Output('F', $merged_path);
                                $target_file = $merged_filename;
                                $merged_success = true;
                                $upload_err = ""; // エラーが出ず成功したのでリセット
                            } catch (\Exception $ex) {
                                $upload_err = "【PDF結合エラー】画像化による結合も失敗しました: " . htmlspecialchars($ex->getMessage());
                                $target_file = $file1;
                            }
                        } else if ($is_compression_error) {
                            $upload_err = "【PDF結合エラー】選択されたPDFは「バージョン1.5以上（圧縮ストリーム）」です。「PDFとして保存（WindowsのMicrosoft Print to PDF等）」を用いて再作成したファイルをご利用ください。（画像化処理モジュールもサーバーに存在しません）";
                            $target_file = $file1;
                        } else {
                            $upload_err = "PDF結合中にエラーが発生しました: " . htmlspecialchars($e->getMessage());
                            $target_file = $file1;
                        }
                    }
                }
            }

            $new_schedule[] = [
                'date' => $dates[$i],
                'file' => $target_file,
                'original_file1' => $file1,
                'original_file2' => $file2
            ];
        }
    }
    
    // 日付順にソートして保存
    usort($new_schedule, function($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    file_put_contents($json_path, json_encode($new_schedule, JSON_PRETTY_PRINT));
    $message = "設定を保存しました。";
}

// データの準備
$schedule = load_schedule($json_path);
// フォルダ内のPDFファイル一覧を取得
$pdf_files = glob($source_dir . '/*.pdf');
$file_list = [];
foreach ($pdf_files as $f) {
    $basename = basename($f);
    // 結合生成されたファイルはリストから除外する
    if (strpos($basename, '_merged.pdf') === false) {
        $file_list[] = $basename;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>PDFスケジュール管理</title>
<style>
    body { font-family: sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    th { background: #f0f0f0; }
    .row-template { display: none; }
    .btn { padding: 5px 10px; cursor: pointer; }
    .add-btn { background: #e0f7fa; border: 1px solid #00acc1; }
    .del-btn { background: #ffebee; border: 1px solid #e57373; }
    .save-btn { background: #2196F3; color: white; border: none; padding: 10px 20px; font-size: 1.1em; }
    .logout { float: right; font-size: 0.9em; }
</style>
</head>
<body>
    <a href="?action=logout" class="logout">ログアウト</a>
    <h2>PDF公開スケジュール管理</h2>
    <p>指定した日時を過ぎると、そのファイルが <code>touban.pdf</code> として表示されます。<br>
    ※下部フォームから新しいファイルをアップロードできます。</p>

    <div style="margin-bottom:20px; padding:15px; border:1px solid #ccc; background:#fafafa;">
        <h3 style="margin-top:0;">PDFファイルのアップロード</h3>
        <?php if(isset($upload_err) && !empty($upload_err)) echo '<p style="color:red; font-weight:bold;">'.$upload_err.'</p>'; ?>
        <form method="post" enctype="multipart/form-data" style="margin:0;">
            <input type="file" name="pdf_upload" accept=".pdf" required style="margin-right:10px;">
            <button type="submit" class="btn" style="background:#4CAF50; color:white; border:none; padding:5px 15px;">アップロード</button>
        </form>
    </div>

    <?php if(isset($message)) echo '<p style="color:green; font-weight:bold;">'.$message.'</p>'; ?>

    <form method="post">
        <table id="scheduleTable">
            <thead>
                <tr>
                    <th>切り替え日時 (YYYY-MM-DD HH:MM:SS)</th>
                    <th>ファイル1 (メイン)</th>
                    <th>ファイル2 (任意・結合用)</th>
                    <th>削除</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedule as $item): 
                    $file1 = isset($item['original_file1']) ? $item['original_file1'] : $item['file'];
                    $file2 = isset($item['original_file2']) ? $item['original_file2'] : '';
                ?>
                <tr>
                    <td><input type="text" name="dates[]" value="<?php echo htmlspecialchars($item['date']); ?>" placeholder="2025-04-01 00:00:00" style="width:100%;"></td>
                    <td>
                        <select name="files[]" style="width:100%;">
                            <option value="">ファイルを選択</option>
                            <?php foreach ($file_list as $fname): ?>
                                <option value="<?php echo htmlspecialchars($fname); ?>" <?php if($fname === $file1) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($fname); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="files2[]" style="width:100%;">
                            <option value="">(なし)</option>
                            <?php foreach ($file_list as $fname): ?>
                                <option value="<?php echo htmlspecialchars($fname); ?>" <?php if($fname === $file2) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($fname); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td style="text-align:center;"><button type="button" class="btn del-btn" onclick="removeRow(this)">削除</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="button" class="btn add-btn" onclick="addRow()">＋ 行を追加</button>
        <hr>
        <div style="text-align:center;">
            <button type="submit" name="save_schedule" value="1" class="btn save-btn">設定を保存する</button>
        </div>
    </form>

    <!-- 行追加用のテンプレート -->
    <table class="row-template">
        <tr id="templateRow">
            <td><input type="text" name="dates[]" placeholder="<?php echo date('Y-m-d H:i:s'); ?>" style="width:100%;"></td>
            <td>
                <select name="files[]" style="width:100%;">
                    <option value="">ファイルを選択</option>
                    <?php foreach ($file_list as $fname): ?>
                        <option value="<?php echo htmlspecialchars($fname); ?>"><?php echo htmlspecialchars($fname); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="files2[]" style="width:100%;">
                    <option value="">(なし)</option>
                    <?php foreach ($file_list as $fname): ?>
                        <option value="<?php echo htmlspecialchars($fname); ?>"><?php echo htmlspecialchars($fname); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td style="text-align:center;"><button type="button" class="btn del-btn" onclick="removeRow(this)">削除</button></td>
        </tr>
    </table>

    <script>
        function addRow() {
            var template = document.getElementById('templateRow');
            var clone = template.cloneNode(true);
            clone.removeAttribute('id');
            document.querySelector('#scheduleTable tbody').appendChild(clone);
        }
        function removeRow(btn) {
            var row = btn.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
        // 初回、データが空なら1行追加
        if (document.querySelectorAll('#scheduleTable tbody tr').length === 0) {
            addRow();
        }
    </script>
</body>
</html>