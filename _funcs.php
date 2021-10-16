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
    return '<a href="' . $href . '"' . " $attr>" . "$innerHtml</a>";
}
