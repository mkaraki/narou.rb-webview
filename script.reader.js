function setColor(cid) {
    let bg = ''; let fg = ''; navclass = 'navbar navbar-expand-lg '

    switch (cid) {
        default:
        case 0:
            bg = 'white';
            fg = 'black';
            navclass += 'navbar-light bg-light'
            break;

        case 1:
            bg = 'black';
            fg = 'white';
            navclass += 'navbar-dark bg-dark'
            break;

        case 2:
            bg = '#ffb570';
            fg = 'black';
            navclass += 'navbar-light bg-light'
            break;
    }

    document.querySelector('body').style = `background-color: ${bg} !important; color: ${fg} !important;`;
    document.getElementById('navbar').setAttribute('class', navclass);
}

function applyReaderConfig() {
    document.getElementById('reader-container').style.maxWidth = (Cookies.get('readermaxwid') ?? '700') + 'px';
    setColor(parseInt(Cookies.get('colorset') ?? '0'));
}