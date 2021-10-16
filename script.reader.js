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
            bg = '#ffe1c2';
            fg = 'black';
            navclass += 'navbar-light bg-light'
            break;
    }

    document.querySelector('body').style = `background-color: ${bg} !important; color: ${fg} !important;`;
    document.getElementById('navbar').setAttribute('class', navclass);
}

function applyReaderConfig() {
    setColor(parseInt(Cookies.get('colorset') ?? '0'));
}

const bmarkElem = document.getElementById('addBmark');

function disableBmark() {
    bmarkElem.ariaDisabled = true;
    bmarkElem.removeAttribute('href');
    bmarkElem.innerText = '保存済み';
}

function bookmarkCheck(nid, sid, applybtn = false) {
    if (!checkBookmarked(nid, sid)) return false;
    else if (!applybtn) return true;
    else {
        disableBmark();
        return true;
    }
}

function bookmarkAdd(nid, sid) {
    if (bmarkElem.ariaDisabled === true) return;
    setBookmark(nid, sid);
    disableBmark();
}