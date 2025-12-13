<?php
error_reporting(0);

$base = '/home/u241000670/domains/';

$patterns = [
    '/eval\s*\(/i',
    '/base64_decode\s*\(/i',
    '/gzinflate\s*\(/i',
    '/gzuncompress\s*\(/i',
    '/gzdecode\s*\(/i',
    '/str_rot13\s*\(/i',
    '/preg_replace\s*\(\s*[\'"].+\/e[\'"]/i',
    '/\b(shell_exec|exec|system|passthru|popen|proc_open)\s*\(/i',
    '/\bassert\s*\(/i',
    '/\bcreate_function\s*\(/i',

    '/\bfile_put_contents\s*\(/i',
    '/\bfile_get_contents\s*\(/i',
    '/\bfopen\s*\(/i',
    '/\bcurl_exec\s*\(/i',
    '/\bfsockopen\s*\(/i',
    '/\bstream_socket_client\s*\(/i',
    '/\bcopy\s*\(/i',

    '/(include|require|include_once|require_once)[^\n;]*https?:\/\//i',
    '/(include|require|include_once|require_once)\s*\$\w+/i',

    '/(\\\\x[0-9A-Fa-f]{2}){6,}/',
    '/[A-Za-z0-9+\/\s]{120,}={0,2}/',
    '/(?:[0-9A-Fa-f]{2}\s*){200,}/',

    '/\$\$[A-Za-z0-9_]+/',
    '/\$\{\s*[\'"]?[A-Za-z0-9_]+[\'"]?\s*\}/',

    '/chr\s*\(\s*\d{1,3}\s*\)\s*(?:\.\s*chr\s*\(\s*\d{1,3}\s*\)\s*){6,}/i',

    '/<iframe[^>]*src=[\'"]?https?:\/\/[^\'" >]+/i',
    '/<meta[^>]*http-equiv=[\'"]?refresh/i',
    '/<form[^>]*action=[\'"]\s*https?:\/\//i',
    '/data:[^;]+;base64,[A-Za-z0-9+\/=]{50,}/i',

    '/(backdoor|webshell|phpshell|c99|r57|wso|b374k|sux|adminer|phpinfo)/i',
    '/^.{4000,}$/m'
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
