function setGColor(cid) {
    let bg = ''; let fg = ''; tclass = ''; navclass = 'navbar navbar-expand-lg ';

    switch (cid) {
        default:
        case 0:
            // bg: white, fg: black
            bg = 'white';
            fg = 'black';
            navclass += 'navbar-light bg-light'
            break;

        case 1:
            bg = '#111';
            fg = 'white';
            tclass = 'table-dark';
            navclass += 'navbar-dark bg-dark'
            break;
    }

    document.querySelector('body').style = `background-color: ${bg} !important; color: ${fg} !important;`;
    if (tclass !== '')
        Array.prototype.forEach.call(document.getElementsByClassName('table'), function (e) { e.classList.add(tclass); });
    document.getElementById('navbar').setAttribute('class', navclass);
}

function applyGeneralColorSettings() {
    setGColor(parseInt(Cookies.get('gcolorset') ?? '0'));
}

function applyGeneralSettings() {
}