<?php
// Concatenate all files in storage/app/chatbot-knowledge/plantas-notion
// Writes a verbatim concatenation (no added separators) to storage/app/chatbot-knowledge/plantas-raw.md

$baseDir = realpath(__DIR__ . '/../storage/app/chatbot-knowledge');
$srcDir = $baseDir . '/plantas-notion';
$outFile = $baseDir . '/plantas-raw.md';

if (!is_dir($srcDir)) {
    fwrite(STDERR, "Source directory not found: $srcDir\n");
    exit(1);
}

$entries = scandir($srcDir, SCANDIR_SORT_ASCENDING);
$files = array_filter($entries, function($n) use ($srcDir) {
    $p = $srcDir . DIRECTORY_SEPARATOR . $n;
    return is_file($p) && preg_match('/\.(md|csv|txt)$/i', $n);
});

if (empty($files)) {
    fwrite(STDOUT, "No markdown/text/csv files found in: $srcDir\n");
    exit(0);
}

$out = fopen($outFile, 'w');
if ($out === false) {
    fwrite(STDERR, "Unable to open output file for writing: $outFile\n");
    exit(1);
}

foreach ($files as $f) {
    $path = $srcDir . DIRECTORY_SEPARATOR . $f;
    $content = file_get_contents($path);
    if ($content === false) {
        fwrite(STDERR, "Failed to read: $path\n");
        continue;
    }
    // write verbatim with no extra characters
    fwrite($out, $content);
}

fclose($out);

fwrite(STDOUT, "Created verbatim concatenation: $outFile\nProcessed files:\n");
foreach ($files as $f) {
    fwrite(STDOUT, " - $f\n");
}

return 0;
