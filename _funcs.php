<?php
function htmlxss($str)
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function generateTd($innerText, $attr = '')
{
    print("<td $attr>");
    print(htmlxss($innerText));
    print('</td>');
}

function generateTdHtml($innerHtml, $attr = '')
{
    print("<td $attr>");
    print($innerHtml);
    print('</td>');
}
