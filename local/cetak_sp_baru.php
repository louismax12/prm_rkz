<?php
session_start();
require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/includes/auth.php';

// Pastikan user loginx
if (!sp_is_logged_in()) {
    header("Location: ../index.html");
    exit;
}

$po_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($po_id <= 0) {
    die("ID Surat Pesanan tidak valid.");
}

$selected_po = db_get_purchase_order_by_id($po_id);
if (!$selected_po) {
    die("Surat Pesanan tidak ditemukan.");
}

$selected_po_items = db_get_purchase_order_items($po_id);
$selected_po_logs = db_get_approval_logs($po_id);

/* â”€â”€ Pre-compute variables â”€â”€ */
$sp_no_sp        = isset($selected_po['no_sp'])            ? htmlspecialchars((string)$selected_po['no_sp'])          : '-';
$sp_status       = isset($selected_po['status'])           ? $selected_po['status']                                   : '';
$sp_tgl_sp       = (isset($selected_po['tgl_sp']) && $selected_po['tgl_sp'] != '' && $selected_po['tgl_sp'] != '0000-00-00') ? format_date($selected_po['tgl_sp']) : '-';
$sp_vendor       = isset($selected_po['nama_vendor'])      ? htmlspecialchars((string)$selected_po['nama_vendor'])    : '-';

// Fetch Alamat1 and Kota1 from m_supplier
$sp_alamat_vendor = '';
$sp_kota_vendor = '';
if (isset($GLOBALS['askes_conn']) && $sp_vendor !== '-') {
    $vendor_esc = mysqli_real_escape_string($GLOBALS['askes_conn'], $selected_po['nama_vendor']);
    $res_sup = mysqli_query($GLOBALS['askes_conn'], "SELECT Alamat1, Kota1 FROM m_supplier WHERE NamaSupplier = '$vendor_esc' LIMIT 1");
    if ($res_sup && $rsup = mysqli_fetch_assoc($res_sup)) {
        $sp_alamat_vendor = htmlspecialchars($rsup['Alamat1']);
        $sp_kota_vendor = htmlspecialchars($rsup['Kota1']);
    }
}

$sp_pembuat      = isset($selected_po['pembuat_nama'])     ? htmlspecialchars((string)$selected_po['pembuat_nama'])   : '-';
$sp_no_tawar     = (isset($selected_po['no_tawar'])        && $selected_po['no_tawar'] !== '')        ? htmlspecialchars((string)$selected_po['no_tawar'])        : '-';
$sp_tgl_tawar    = (isset($selected_po['tgl_tawar'])       && $selected_po['tgl_tawar'] !== '' && $selected_po['tgl_tawar'] != '0000-00-00' && $selected_po['tgl_tawar'] != '1900-01-01') ? format_date($selected_po['tgl_tawar']) : '-';
$sp_pembayaran   = (isset($selected_po['pembayaran'])      && $selected_po['pembayaran'] !== '')      ? htmlspecialchars((string)$selected_po['pembayaran'])      : '-';
$sp_pembayaran1  = (isset($selected_po['pembayaran1'])     && $selected_po['pembayaran1'] !== '')     ? htmlspecialchars((string)$selected_po['pembayaran1'])     : '-';
$sp_noteout      = (isset($selected_po['noteout'])         && $selected_po['noteout'] !== '')         ? nl2br(htmlspecialchars((string)$selected_po['noteout']))  : '-';
$sp_ppn_pct      = (isset($selected_po['ppn'])             && (float)$selected_po['ppn'] > 0)        ? (float)$selected_po['ppn']                                : 0;
$sp_total_net    = isset($selected_po['total_setelah_diskon']) ? (float)$selected_po['total_setelah_diskon'] : 0;
$sp_ppn_nominal  = ($sp_ppn_pct > 0) ? ($sp_total_net * $sp_ppn_pct / 100) : (isset($selected_po['ppn_nominal']) ? (float)$selected_po['ppn_nominal'] : 0);
$sp_grand_total  = isset($selected_po['grand_total'])          ? (float)$selected_po['grand_total']          : ($sp_total_net + $sp_ppn_nominal);

$bayar_parts = array();
if (!empty($selected_po['pembayaran']))  { $bayar_parts[] = (string)$selected_po['pembayaran']; }
if (!empty($selected_po['pembayaran1'])) { $bayar_parts[] = (string)$selected_po['pembayaran1']; }
$bayar_str_print = empty($bayar_parts) ? '-' : implode(' ', $bayar_parts);

$has_model = false; $has_merk = false; $has_spec = false; $has_disc = false;
$p_subtotal_kotor = 0;
$p_total_diskon = 0;
$p_ppn = $sp_ppn_nominal;
$p_gtotal = $sp_grand_total;

foreach ($selected_po_items as $itm) {
    if (trim($itm['model']) !== '') $has_model = true;
    if (trim($itm['merk']) !== '')  $has_merk = true;
    if (trim($itm['spec']) !== '')  $has_spec = true;
    if ((float)$itm['diskon_item'] > 0) $has_disc = true;
    
    $qty = (float)$itm['jumlah'];
    $harga = (float)$itm['harga_satuan'];
    $disc_pct = (float)$itm['diskon_item'];
    
    $gross = $qty * $harga;
    $nom_disc = $gross * ($disc_pct / 100);
    
    $p_subtotal_kotor += $gross;
    $p_total_diskon += $nom_disc;
}

$acc_sp_name = '( ..................................... )';
$acc_sp_title = '';
$catatan_direktur = '';

if (!empty($selected_po_logs)) {
    foreach ($selected_po_logs as $lg) {
        if ($lg['status'] === 'acc' && $lg['jenis'] !== 'pembayaran') {
            $acc_sp_name = htmlspecialchars($lg['user_nama']);
            $acc_sp_title = strtoupper(htmlspecialchars($lg['user_role']));
            if (isset($lg['catatan']) && trim($lg['catatan']) !== '') {
                $catatan_direktur = htmlspecialchars($lg['catatan']);
            }
            break;
        }
    }
}

// Fallback ke notein (Catatan Internal) jika di log persetujuan kosong
if ($catatan_direktur === '' && isset($selected_po['notein']) && trim($selected_po['notein']) !== '') {
    $catatan_direktur = htmlspecialchars(trim($selected_po['notein']));
}

$tgl_sp_formatted_print = isset($selected_po['tgl_sp']) ? format_date($selected_po['tgl_sp']) : format_date(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print SP - <?php echo $sp_no_sp; ?></title>
    <style>
        /* CSS KHUSUS PRINT */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: 18cm 21.5cm;
                margin-top: 1.0cm; /* Margin atas dikecilkan */
                margin-left: 2.5cm; /* Margin kiri ditambah */
                margin-right: 3.5cm; /* Margin kanan ditambah */
                margin-bottom: 2.5cm; /* Margin bawah ditambah */
            }
        }
        
        /* GENERAL STYLES (Screen & Print) */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 100%;
            max-width: 18cm; /* matches paper width */
            margin: 0 auto;
            background: #fff;
        }
        
        /* Agar di layar browser tetap rapi sebelum diprint */
        @media screen {
            body {
                background: #e2e8f0;
                padding: 2rem;
            }
            .container {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                padding: 2.5cm;
                padding-top: 0.5cm;
                min-height: 21.5cm;
            }
            @page {
                margin-top: 1.0cm;
                margin-left: 2.5cm;
                margin-right: 3.5cm;
                margin-bottom: 2.5cm;
            }
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-weight-bold { font-weight: bold; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 0.2rem; }
        .mt-1 { margin-top: 0.2rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1.5rem; }
        .align-middle { vertical-align: middle; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.5rem;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 3px 5px; /* Kurangi padding agar muat di kertas kecil */
        }
        th {
            border-bottom: 2px solid #000;
        }
        
        .row-signature {
            display: flex;
            justify-content: flex-start;
            margin-top: 1rem;
        }
        .col-signature {
            width: 100%;
            text-align: left;
        }
        
        .btn-print {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-family: sans-serif;
            font-weight: bold;
            margin-bottom: 20px;
            cursor: pointer;
            border: none;
        }
        .btn-print:hover { background-color: #2563eb; }
    </style>
</head>
<body>
    
    <div class="container">
        <!-- Tombol print yang akan tersembunyi saat dicetak -->
        <div class="no-print text-right" style="margin-bottom: 20px;">
            <button class="btn-print" onclick="window.print();">🖨️ Cetak Sekarang</button>
        </div>

        <div class="header-title" style="text-align: center;">
            <h3 class="font-weight-bold" style="font-size:14pt;text-decoration:underline;margin-bottom:0.2rem;margin-top:0;">SURAT PESANAN</h3>
            <p style="font-size:10pt;margin-bottom:0;margin-top:0;">No. <?php echo $sp_no_sp; ?></p>
        </div>

        <!-- Margin atas disesuaikan agar tidak terlalu turun -->
        <div class="content-body" style="margin-top: 0.5cm;">
            <div style="font-size:10pt;margin-bottom:1.5rem;">
                <p class="mb-0" style="margin-top:0;">Kepada Yth :</p>
                <p class="mb-0" style="margin-top:0;"><strong><?php echo $sp_vendor; ?></strong></p>
                <?php if ($sp_alamat_vendor !== ''): ?>
                <p class="mb-0" style="margin-top:0;"><?php echo $sp_alamat_vendor; ?></p>
                <?php endif; ?>
                <?php if ($sp_kota_vendor !== ''): ?>
                <p class="mb-0" style="margin-top:0;"><?php echo $sp_kota_vendor; ?></p>
                <?php endif; ?>
            </div>
            <div style="font-size:10pt;margin-bottom:0.5rem;">
                <p class="mb-1">Berdasarkan Surat Penawaran Saudara No : <?php echo $sp_no_tawar; ?><br>
                tertanggal <?php echo $sp_tgl_tawar; ?> dengan ini kami memesan :</p>
            </div>
            <table style="font-size:9.5pt;">
                <thead>
                    <tr>
                        <th class="text-center align-middle" style="width:25px;">No</th>
                        <th class="text-center align-middle">Barang</th>
                        <?php if ($has_spec):  ?><th class="text-center align-middle">Spesifikasi</th><?php endif; ?>
                        <?php if ($has_merk):  ?><th class="text-center align-middle">Merk</th><?php endif; ?>
                        <?php if ($has_model): ?><th class="text-center align-middle">Tipe</th><?php endif; ?>
                        <th class="text-center align-middle">Satuan</th>
                        <th class="text-center align-middle">Harga Satuan</th>
                        <th class="text-center align-middle" style="width:30px;">Qty</th>
                        <?php if ($has_disc):  ?><th class="text-center align-middle">Diskon</th><?php endif; ?>
                        <th class="text-center align-middle">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($selected_po_items as $itm2): 
                        $qty = (float)$itm2['jumlah'];
                        $harga = (float)$itm2['harga_satuan'];
                        $disc_pct = (float)$itm2['diskon_item'];
                        $gross = $qty * $harga;
                    ?>
                    <tr>
                        <td class="text-center align-middle"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($itm2['nama_barang']); ?></td>
                        <?php if ($has_spec):  ?><td class="align-middle"><?php echo htmlspecialchars($itm2['spec']); ?></td><?php endif; ?>
                        <?php if ($has_merk):  ?><td class="align-middle"><?php echo htmlspecialchars($itm2['merk']); ?></td><?php endif; ?>
                        <?php if ($has_model): ?><td class="align-middle"><?php echo htmlspecialchars($itm2['model']); ?></td><?php endif; ?>
                        <td class="text-center align-middle"><?php echo htmlspecialchars($itm2['satuan']); ?></td>
                        <td class="text-right align-middle"><?php echo number_format($harga,0,',','.'); ?></td>
                        <td class="text-center align-middle"><?php echo (float)$qty; ?></td>
                        <?php if ($has_disc):  ?><td class="text-center align-middle"><?php echo $disc_pct > 0 ? (float)$disc_pct . '%' : '-'; ?></td><?php endif; ?>
                        <td class="text-right align-middle"><?php echo number_format($gross,0,',','.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-right mt-1" style="font-size:10pt;margin-bottom:1.5rem;">
                <table style="width: auto; margin-left: auto; border: none;">
                    <tr>
                        <td style="border:none; text-align:right; padding: 2px 10px;">Sub Total</td>
                        <td style="border:none; text-align:left; padding: 2px;">Rp</td>
                        <td style="border:none; text-align:right; padding: 2px;"><?php echo number_format($p_subtotal_kotor,0,',','.'); ?></td>
                    </tr>
                    <?php if ($p_total_diskon > 0): ?>
                    <tr>
                        <td style="border:none; text-align:right; padding: 2px 10px;">Total Diskon</td>
                        <td style="border:none; text-align:left; padding: 2px;">Rp</td>
                        <td style="border:none; text-align:right; padding: 2px;"><?php echo number_format($p_total_diskon,0,',','.'); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($p_ppn > 0): ?>
                    <tr>
                        <td style="border:none; text-align:right; padding: 2px 10px;">PPN</td>
                        <td style="border:none; text-align:left; padding: 2px;">Rp</td>
                        <td style="border:none; text-align:right; padding: 2px;"><?php echo number_format($p_ppn,0,',','.'); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="border:none; text-align:right; padding: 4px 10px; font-weight:bold; border-top: 1px solid #000;">Grand Total</td>
                        <td style="border:none; text-align:left; padding: 4px 2px; font-weight:bold; border-top: 1px solid #000;">Rp</td>
                        <td style="border:none; text-align:right; padding: 4px 2px; font-weight:bold; border-top: 1px solid #000;"><?php echo number_format($p_gtotal,0,',','.'); ?></td>
                    </tr>
                </table>
            </div>
            
            <div style="font-size:10pt;">
                <p class="mb-1" style="margin-top:0;">Dengan :</p>
                <p class="mb-1" style="margin-top:0;">Cara Pembayaran : <?php echo htmlspecialchars($bayar_str_print); ?><?php if ($catatan_direktur !== '') echo ' - ' . nl2br($catatan_direktur); ?></p>
                <p class="mb-1" style="margin-top:0;">Catatan :<br><?php echo $sp_noteout; ?></p>
            </div>
            <div class="mt-2" style="font-size:10pt;"><p style="margin-top:0;">Terima Kasih atas perhatian dan kerjasamanya.</p></div>
            
            <div class="text-left" style="font-size:10pt;margin-top:1.5rem;margin-bottom:0.5rem;">Surabaya, <?php echo $tgl_sp_formatted_print; ?></div>
            <div class="row-signature text-left" style="font-size:10pt;color:#000;">
                <div class="col-signature">
                    <p style="margin-bottom:50px;">&nbsp;</p>
                    <p class="font-weight-bold mb-0"><u>Sr. Ir. Augusta Surijah, SSpS., MM.</u></p>
                    <p style="margin-top:2px;">Direktur Umum &amp; ADM./Keu.</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Otomatis muncul dialog print saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
