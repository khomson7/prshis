<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) { http_response_code(403); exit('Unauthorized'); }

$id = (int)($_GET['id'] ?? 0);
$an = trim($_GET['an']  ?? '');

if (!$id || !$an) { http_response_code(400); exit('Bad Request'); }

try {
    $conn = DbUtils::get_kphis_log_db_connection();
    $stmt = $conn->prepare("SELECT original_name, file_size, file_data
                              FROM prs_pdf_upload
                             WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt->execute(['id' => $id, 'an' => $an]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || $row['file_data'] === null) {
        http_response_code(404);
        exit('Not Found');
    }

    if (empty($_GET['raw'])) {
        // Output HTML viewer wrapper
        $safe_name = htmlspecialchars($row['original_name'] ?? 'document.pdf');
        $raw_url = 'view.php?id=' . $id . '&an=' . urlencode($an) . '&raw=1#toolbar=0';
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View PDF - <?= $safe_name ?></title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; background-color: #525659; }
        .toolbar {
            position: absolute; top: 0; left: 0; width: 100%; height: 50px; background: #323639;
            display: flex; align-items: center; justify-content: flex-end; padding: 0 20px; box-sizing: border-box;
            z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .btn-print {
            background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px;
            font-size: 14px; cursor: pointer; font-weight: bold;
        }
        .btn-print:hover { background: #0056b3; }
        .pdf-container {
            position: absolute; top: 50px; left: 0; right: 0; bottom: 0;
        }
        iframe { width: 100%; height: 100%; border: none; }
        /* Anti-snapshot overlay trick (optional, minimal effect but requested) */
        .overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 999;
        }
    </style>
    <script>
        // Prevent right-click
        document.addEventListener('contextmenu', event => event.preventDefault());
        // Prevent common shortcuts for saving/printing natively
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P' || e.key === 'c' || e.key === 'C')) {
                e.preventDefault();
            }
            if (e.key === 'PrintScreen') {
                navigator.clipboard.writeText(''); // Attempt to clear clipboard
            }
        });
        function printPdf() {
            var iframe = document.getElementById('pdfFrame');
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    </script>
</head>
<body>
    <div class="toolbar">
        <span style="color: white; margin-right: auto; font-family: sans-serif;"><?= $safe_name ?></span>
        <button class="btn-print" onclick="printPdf()">พิมพ์เอกสาร (Print)</button>
    </div>
    <div class="pdf-container">
        <iframe id="pdfFrame" src="<?= $raw_url ?>"></iframe>
        <div class="overlay"></div>
    </div>
</body>
</html>
        <?php
        exit;
    }

    // Output raw watermarked PDF
    $file_data = is_resource($row['file_data']) ? stream_get_contents($row['file_data']) : $row['file_data'];

    require_once '../vendor/autoload.php';
    
    $watermarkedData = null;
    try {
        $mpdf = new \Mpdf\Mpdf();
        $pageCount = $mpdf->SetSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($file_data));
        for ($i = 1; $i <= $pageCount; $i++) {
            $mpdf->AddPage();
            $tplId = $mpdf->ImportPage($i);
            $mpdf->UseTemplate($tplId);

            // --- Overlay Watermark (On top of scanned images) ---
            $mpdf->SetFont('Garuda');
            $mpdf->SetAlpha(0.2); // ความโปร่งใส 20%
            $mpdf->StartTransform();
            $mpdf->TransformRotate(45, 105, 148); // หมุน 45 องศา ตรงกลางหน้า A4
            $mpdf->WriteFixedPosHTML('<div style="text-align:center; width:210mm; font-size:45pt; font-weight:bold; color:#000000;">' . base64_decode('4LmC4Lij4LiH4Lie4Lii4Liy4Lia4Liy4Lil4Lib4Lij4Liy4Liq4Liy4LiX') . '</div>', 0, 140, 210, 50);
            $mpdf->StopTransform();
            $mpdf->SetAlpha(1);
        }
        
        // Protect PDF: only print allowed
        $mpdf->SetProtection(['print']);
        
        error_log("PRS-HIS-DEBUG: MPDF 1 SUCCESS");
        $watermarkedData = $mpdf->Output('', 'S');
    } catch (\Exception $e) {
        error_log("PRS-HIS-DEBUG EXCEPTION 1: " . $e->getMessage());
        // Fallback: If PDF uses unsupported compression (PDF 1.5+), try using qpdf to decompress
        $tmp_in = sys_get_temp_dir() . '/' . uniqid('pdf_in_') . '.pdf';
        $tmp_out = sys_get_temp_dir() . '/' . uniqid('pdf_out_') . '.pdf';
        
        file_put_contents($tmp_in, $file_data);
        exec("qpdf --stream-data=uncompress --object-streams=disable " . escapeshellarg($tmp_in) . " " . escapeshellarg($tmp_out) . " 2>&1", $output, $return_var);
        
        // --- เพิ่ม Log ชั่วคราวเพื่อหาสาเหตุ ---
        error_log("PRS-HIS-DEBUG QPDF OUTPUT: Return Var: $return_var, Output: " . print_r($output, true));
        
        if ($return_var === 0 && file_exists($tmp_out)) {
            $uncompressed_data = file_get_contents($tmp_out);
            try {
                $mpdf2 = new \Mpdf\Mpdf();
                $pageCount2 = $mpdf2->SetSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($uncompressed_data));
                for ($i = 1; $i <= $pageCount2; $i++) {
                    $mpdf2->AddPage();
                    $tplId = $mpdf2->ImportPage($i);
                    $mpdf2->UseTemplate($tplId);

                    // --- Overlay Watermark (On top of scanned images) ---
                    $mpdf2->SetFont('Garuda');
                    $mpdf2->SetAlpha(0.2); // ความโปร่งใส 20%
                    $mpdf2->StartTransform();
                    $mpdf2->TransformRotate(45, 105, 148);
                    $mpdf2->WriteFixedPosHTML('<div style="text-align:center; width:210mm; font-size:45pt; font-weight:bold; color:#000000;">' . base64_decode('4LmC4Lij4LiH4Lie4Lii4Liy4Lia4Liy4Lil4Lib4Lij4Liy4Liq4Liy4LiX') . '</div>', 0, 140, 210, 50);
                    $mpdf2->StopTransform();
                    $mpdf2->SetAlpha(1);
                }
                $mpdf2->SetProtection(['print']);
                error_log("PRS-HIS-DEBUG: MPDF 2 SUCCESS");
                $watermarkedData = $mpdf2->Output('', 'S');
            } catch (\Exception $e2) {
                error_log("PRS-HIS-DEBUG EXCEPTION 2: " . $e2->getMessage());
                exit("ERROR: mPDF failed to process decompressed PDF: " . $e2->getMessage());
            }
        } else {
            error_log("PRS-HIS-DEBUG QPDF FAILED: return_var=$return_var, tmp_out_exists=" . (file_exists($tmp_out)?'1':'0'));
            exit("ERROR: qpdf is missing or failed to run. Return code: $return_var");
        }
        
        if (file_exists($tmp_in)) @unlink($tmp_in);
        if (file_exists($tmp_out)) @unlink($tmp_out);
    }

    $disposition = 'inline';
    $safe_name   = rawurlencode($row['original_name'] ?? 'document.pdf');

    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . $disposition . '; filename="' . $safe_name . '"');
    header('Content-Length: ' . strlen($watermarkedData));
    header('Cache-Control: private, max-age=0');

    echo $watermarkedData;

} catch (Exception $e) {
    http_response_code(500);
    exit('Error');
}
