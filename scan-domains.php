<?php
error_reporting(0);

$root = __DIR__ . '/../../'; // target ke /home/u241000670/domains

echo "<h2 style='font-family:Arial;'>Backdoor Scanner – Semua Domain</h2>";
echo "<p>Target root: <b>$root</b></p>";

$patterns = [
    'eval\s*\(',
    'base64_decode\s*\(',
    'gzinflate\s*\(',
    'str_rot13\s*\(',
    'shell_exec\s*\(',
    'exec\s*\(',
    'system\s*\(',
    'passthru\s*\(',
    'assert\s*\(',
    'preg_replace\s*\(.*?/e',
    'goto\s+[A-Za-z0-9_]+;',
    '\$[a-zA-Z0-9_]+\s*=\s*["\']?[A-Za-z0-9+/]{80,}={0,2}["\']?;',
];

$exclude = [
    'wp-content/uploads',
    'cache',
    '.git',
    'node_modules'
];

echo "
<style>
.result {
    background:#fef4f4;
    border:1px solid #ff6b6b;
    padding:10px;
    margin:10px 0;
    border-radius:6px;
    font-family:Arial;
}
.copy-btn {
    padding:4px 8px;
    background:#0073aa;
    color:white;
    border:none;
    border-radius:4px;
    cursor:pointer;
    font-size:12px;
}
.copy-btn:hover {
    background:#005f87;
}
.code {
    background:#222;
    color:#0f0;
    padding:8px;
    border-radius:5px;
    font-size:13px;
    overflow:auto;
}
</style>

<script>
function copyText(id) {
    const text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
}
</script>
";

function skipPath($path, $exclude) {
    foreach ($exclude as $e) {
        if (stripos($path, $e) !== false) return true;
    }
    return false;
}

function scanFolder($dir, $patterns, $exclude) {
    $list = @scandir($dir);
    if (!$list) return;

    foreach ($list as $item) {
        if ($item === "." || $item === "..") continue;
        $path = $dir . '/' . $item;

        if (skipPath($path, $exclude)) continue;

        if (is_dir($path)) {
            scanFolder($path, $patterns, $exclude);
        } else {
            scanFile($path, $patterns);
        }
    }
}

function scanFile($file, $patterns) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, ['php', 'phtml', 'php7', 'inc'])) return;

    $content = @file_get_contents($file);
    if (!$content) return;

    foreach ($patterns as $pattern) {
        if (preg_match("/$pattern/i", $content)) {
            $id = "copy_" . md5($file);

            echo "<div class='result'>";
            echo "<b>⚠️ Backdoor Ditemukan</b><br>";
            echo "<div id='$id' style='margin:6px 0;'>
                $file
            </div>";
            echo "<button class='copy-btn' onclick=\"copyText('$id')\">Copy Path</button>";

            echo "<div class='code'>";
            $lines = explode("\n", $content);
            foreach ($lines as $num => $line) {
                if (preg_match("/$pattern/i", $line)) {
                    echo "Line " . ($num+1) . ": " . htmlspecialchars(trim($line)) . "<br>";
                }
            }
            echo "</div></div>";
        }
    }
}

scanFolder($root, $patterns, $exclude);

echo "<p><b>Scan selesai!</b></p>";
?>
