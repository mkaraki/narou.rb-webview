<?php
function htmlxss($str)
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function generateTd($innerText, $attr = '')
{
    return ("<td $attr>" . htmlxss($innerText) . '</td>');
}

function generateTdHtml($innerHtml, $attr = '')
{
    return "<td $attr>" . $innerHtml . '</td>';
}
