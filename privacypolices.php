<?php
error_reporting(0);

$base = '/home/u241000670/domains/';
$patterns = [
    'eval\s*\(',
    'assert\s*\(',
    'base64_decode\s*\(',
    'gzinflate\s*\(',
    'str_rot13\s*\(',
    'shell_exec\s*\(',
    'system\s*\(',
    'passthru\s*\(',
];

$results = [];

function scan($dir, $domain) {
    global $patterns, $results;

    $files = @scandir($dir);
    if (!$files) return;

    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;

        if (is_dir($path)) {
            scan($path, $domain);
        } else {
            if (!preg_match('/\.(php|phtml|inc)$/i', $path)) continue;

            $content = @file_get_contents($path);
            if (!$content) continue;

            foreach ($patterns as $p) {
                if (preg_match("/$p/i", $content)) {

                    // bersihkan path
                    $clean = str_replace(
                        $base . $domain . '/public_html/',
                        '',
                        $path
                    );

                    $results[] = $domain . '/' . $clean;
                    break;
                }
            }
        }
    }
}

// scan semua domain
$domains = scandir($base);
foreach ($domains as $d) {
    if ($d === '.' || $d === '..') continue;

    $pub = $base . $d . '/public_html';
    if (is_dir($pub)) {
        scan($pub, $d);
    }
}
?>
<!doctype html>
<html>
<head>
<title>Backdoor Scan Result</title>
<style>
body{font-family:Arial;background:#111;color:#0f0;padding:20px}
textarea{width:100%;height:400px;background:#000;color:#0f0;padding:10px}
button{padding:8px 14px;font-size:14px;cursor:pointer}
</style>
</head>
<body>

<h3>HASIL SCAN (SEMUA DOMAIN)</h3>

<?php if (empty($results)): ?>
<p>✔ Tidak ditemukan file mencurigakan</p>
<?php else: ?>
<textarea id="result"><?php echo implode("\n", array_unique($results)); ?></textarea><br><br>
<button onclick="copy()">COPY SEMUA</button>

<script>
function copy(){
    let t = document.getElementById('result');
    t.select();
    document.execCommand('copy');
    alert('Semua path berhasil di-copy');
}
</script>
<?php endif; ?>

</body>
</html>
