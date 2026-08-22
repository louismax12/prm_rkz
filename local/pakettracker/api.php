<?php
header('Content-Type: application/json; charset=utf-8');

$file = __DIR__ . '/data.json';
$units = ['tms', 'fisio'];

function load()
{
    global $file;
    $raw = file_get_contents($file);
    $d = json_decode($raw, true);
    if (!is_array($d)) $d = ['tms' => [], 'fisio' => []];
    foreach (['tms', 'fisio'] as $u) if (!isset($d[$u]) || !is_array($d[$u])) $d[$u] = [];
    return $d;
}

function save($d)
{
    global $file;
    file_put_contents($file, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function body()
{
    $b = json_decode(file_get_contents('php://input'), true);
    return is_array($b) ? $b : [];
}

function clean($s)
{
    return trim(strip_tags((string)$s));
}

function statusOf($x)
{
    $today = date('Y-m-d');
    if (!empty($x['exp']) && $x['exp'] < $today) return 'Expired';
    $used = (int)(isset($x['used']) ? $x['used'] : 0);
    $total = max(1, (int)(isset($x['total']) ? $x['total'] : 1));
    if ($used <= 0) return 'Belum Digunakan';
    if ($used >= $total) return 'Selesai';
    if (($total - $used) <= max(1, (int)ceil($total * 0.2))) return 'Hampir Habis';
    return 'Aktif';
}

function fail($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$data = load();

if ($method === 'GET') {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$b = body();
$unit = isset($b['unit']) ? $b['unit'] : (isset($_GET['unit']) ? $_GET['unit'] : '');
if (!in_array($unit, $units, true)) fail('Unit tidak valid. Gunakan tms atau fisio.');

if ($method === 'POST') {
    $item = isset($b['item']) && is_array($b['item']) ? $b['item'] : [];
    $name = clean(isset($item['name']) ? $item['name'] : '');
    $rm = clean(isset($item['rm']) ? $item['rm'] : '');
    $pkg = clean(isset($item['pkg']) ? $item['pkg'] : '');
    $buy = clean(isset($item['buy']) && $item['buy'] !== '' ? $item['buy'] : date('Y-m-d'));
    $exp = clean(isset($item['exp']) ? $item['exp'] : '');
    $total = (int)(isset($item['total']) ? $item['total'] : 0);
    if ($name === '' || $rm === '' || $pkg === '') fail('Nama pasien, No. RM, dan jenis paket wajib diisi.');
    if ($total <= 0) fail('Jumlah hak tindakan harus lebih dari 0.');
    if ($exp !== '' && $exp < $buy) fail('Tanggal kedaluwarsa tidak boleh sebelum tanggal beli.');

    $prefix = 'PKT-' . strtoupper($unit) . '-' . date('ymd') . '-';
    $seq = 1;
    foreach ($data[$unit] as $it) {
        if (strpos((string)(isset($it['id']) ? $it['id'] : ''), $prefix) === 0) {
            $n = (int)substr($it['id'], -3);
            if ($n >= $seq) $seq = $n + 1;
        }
    }
    $new = [
        'id' => $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT),
        'name' => $name,
        'rm' => $rm,
        'pkg' => $pkg,
        'buy' => $buy,
        'exp' => $exp,
        'used' => 0,
        'total' => $total,
        'last' => '-',
        'status' => '',
        'log' => [],
    ];
    if ($unit === 'fisio' && isset($item['comp']) && is_array($item['comp'])) {
        $comp = [];
        foreach ($item['comp'] as $c) {
            $hak = (int)(isset($c[1]) ? $c[1] : 0);
            if ($hak > 0) $comp[] = [clean(isset($c[0]) ? $c[0] : ''), $hak, 0];
        }
        if ($comp) $new['comp'] = $comp;
    }
    $new['status'] = statusOf($new);
    array_unshift($data[$unit], $new);
    save($data);
    echo json_encode(['ok' => true, 'item' => $new]);
    exit;
}

if ($method === 'PUT') {
    $id = clean(isset($b['id']) ? $b['id'] : '');
    $idx = null;
    foreach ($data[$unit] as $i => $it) if ((isset($it['id']) ? $it['id'] : '') === $id) { $idx = $i; break; }
    if ($idx === null) fail('Paket dengan id tersebut tidak ditemukan.', 404);

    $cur = $data[$unit][$idx];
    $in = isset($b['item']) && is_array($b['item']) ? $b['item'] : [];

    if (isset($in['name'])) $cur['name'] = clean($in['name']);
    if (isset($in['rm'])) $cur['rm'] = clean($in['rm']);
    if (isset($in['pkg'])) $cur['pkg'] = clean($in['pkg']);
    if (isset($in['buy'])) $cur['buy'] = clean($in['buy']);
    if (isset($in['exp'])) $cur['exp'] = clean($in['exp']);
    if (array_key_exists('total', $in)) {
        $total = (int)$in['total'];
        if ($total <= 0) fail('Jumlah hak tindakan harus lebih dari 0.');
        if ($total < (int)$cur['used']) fail('Total hak tidak boleh lebih kecil dari yang sudah terpakai (' . $cur['used'] . ').');
        $cur['total'] = $total;
    }
    if (array_key_exists('used', $in)) {
        $used = (int)$in['used'];
        if ($used < 0 || $used > (int)$cur['total']) fail('Nilai terpakai tidak valid.');
        $cur['used'] = $used;
    }
    if (array_key_exists('last', $in)) $cur['last'] = clean($in['last']) !== '' ? clean($in['last']) : '-';
    if (isset($in['log']) && is_array($in['log'])) $cur['log'] = array_values($in['log']);
    if ($unit === 'fisio' && isset($in['comp']) && is_array($in['comp'])) {
        $old = isset($cur['comp']) && is_array($cur['comp']) ? $cur['comp'] : [];
        $comp = [];
        foreach ($in['comp'] as $c) {
            $nm = clean(isset($c[0]) ? $c[0] : '');
            $hak = (int)(isset($c[1]) ? $c[1] : 0);
            if ($nm === '' || $hak <= 0) continue;
            $usedC = 0;
            foreach ($old as $o) if ((isset($o[0]) ? $o[0] : '') === $nm) { $usedC = min((int)$o[2], $hak); break; }
            $comp[] = [$nm, $hak, $usedC];
        }
        $sumComp = 0;
        foreach ($comp as $c) $sumComp += $c[1];
        $cur['comp'] = $comp;
        if ($comp && !array_key_exists('total', $in)) $cur['total'] = max($sumComp, (int)$cur['used']);
    }
    $cur['status'] = statusOf($cur);
    $data[$unit][$idx] = $cur;
    save($data);
    echo json_encode(['ok' => true, 'item' => $cur]);
    exit;
}

if ($method === 'DELETE') {
    $id = clean(isset($_GET['id']) ? $_GET['id'] : '');
    $before = count($data[$unit]);
    $kept = [];
    foreach ($data[$unit] as $it) if ((isset($it['id']) ? $it['id'] : '') !== $id) $kept[] = $it;
    $data[$unit] = $kept;
    if (count($data[$unit]) === $before) fail('Paket dengan id tersebut tidak ditemukan.', 404);
    save($data);
    echo json_encode(['ok' => true]);
    exit;
}

fail('Metode tidak didukung.', 405);
