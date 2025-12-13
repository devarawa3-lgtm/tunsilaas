<?php
error_reporting(0);

$base = '/home/u241000670/domains/';

$patterns = [

    // eksekusi berbahaya
    '/eval\s*\(/i',
    '/assert\s*\(/i',
    '/preg_replace\s*\(\s*[\'"].+\/e[\'"]/i',
    '/\b(shell_exec|exec|system|passthru|popen|proc_open)\s*\(/i',

    // obfuscation berat
    '/base64_decode\s*\(/i',
    '/gzinflate\s*\(/i',
    '/gzdecode\s*\(/i',
    '/gzuncompress\s*\(/i',
    '/str_rot13\s*\(/i',

    // payload besar (ciri shell)
    '/[A-Za-z0-9+\/]{300,}={0,2}/',
    '/(?:[0-9A-Fa-f]{2}\s*){300,}/',

    // variabel dinamis bahaya
    '/\$\$\w+/',
    '/\$\{\s*\w+\s*\}/',

    // chr chain (obfuscation klasik)
    '/(chr\s*\(\s*\d+\s*\)\s*\.){6,}/i',

    // include remote / dynamic
    '/(include|require|include_once|require_once)\s*\$\w+/i',
    '/(include|require|require_once)[^\n;]*https?:\/\//i',

    // tanda webshell terkenal
    '/(c99|r57|wso|b374k|phpshell|webshell|filesman|cmdshell)/i',

    // file abnormal (1 baris super panjang)
    '/^.{5000,}$/m'
];

$results = [];

function scan($dir, $domain) {
    global $patterns, $results, $base;

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
                if (preg_match($p, $content)) {

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
