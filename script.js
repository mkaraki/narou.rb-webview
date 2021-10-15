function setColor(cid) {
    $bg = '';
    $fg = '';

    switch (cid) {
        default:
        case 0:
            // bg: white, fg: black
            $bg = 'white';
            $fg = 'black';
            break;

        case 1:
            // bg: black, fg: white
            $bg = 'black';
            $fg = 'white';
            break;

        case 2:
            // bg: warm. fg: black
            $bg = '#ffb570';
            $fg = 'black';
            break;
    }

    document.querySelector('body').style = `background-color: ${$bg} !important; color: ${$fg} !important;`;
}