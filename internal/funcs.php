<?php
function htmlxss(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function generateTd(string $innerText, string $attr = ''): string
{
    return ("<td $attr>" . htmlxss($innerText) . '</td>');
}

function generateTdHtml(string $innerHtml, string $attr = ''): string
{
    return "<td $attr>" . $innerHtml . '</td>';
}

function generateATag(string $href, string $innerHtml, string $attr = ''): string
{
    return '<a href="' . htmlxss($href) . '"' . " $attr>" . "$innerHtml</a>";
}

function getClientLastModified(): int
{
    if (!isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) return 0;
    $epoch = strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"]);
    return $epoch;
}

function putLastModifiedAndEnd(int $epoch, int $current = 0): int
{
    $curEpoch = getClientLastModified();

    if ($curEpoch !== 0 && $curEpoch >= $epoch) {
        http_response_code(304);
        exit(0);
        return $epoch;
    } else if ($epoch > $current) {
        $lastupdt_str = gmdate('D, d M Y H:i:s', $epoch) . ' GMT';
        header('Last-Modified: ' . $lastupdt_str);
        return $epoch;
    } else {
        return $current;
    }
}
