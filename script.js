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

function showConf() {

    function getVal(val, id) {
        let cookie = Cookies.get(val);
        if (cookie === undefined) return;
        document.getElementById(id).value = cookie;
    }

    // General Configurations
    getVal('gcolorset', 'gcolorSelector');

    // Novel Reader Configurations
    getVal('readermaxwid', 'readerMaxWidth');
    getVal('colorset', 'colorSelector');

    // Advanced Configurations
    getVal('cookieexp', 'cookieExpire');

    document.getElementById('loadscr').classList.add('d-none');
    document.getElementById('confscr').classList.remove('d-none');
}

function saveConf() {
    document.getElementById('loadscr').classList.remove('d-none');
    document.getElementById('confscr').classList.add('d-none');

    function setVal(cname, id, exp) {
        Cookies.set(cname, document.getElementById(id).value, { expires: exp });
    }

    // Save Cookie Expire
    let cookieexp = parseInt(document.getElementById('cookieExpire').value);
    setVal('cookieexp', 'cookieExpire', cookieexp);

    // General Configurations
    setVal('gcolorset', 'gcolorSelector', cookieexp);

    // Novel Reader Configurations
    setVal('readermaxwid', 'readerMaxWidth', cookieexp);
    setVal('colorset', 'colorSelector', cookieexp);

    document.getElementById('loadscr').classList.add('d-none');
    document.getElementById('confscr').classList.remove('d-none');
    alert('保存しました');
}

function importConf() {
    document.getElementById('importconf').classList.remove('d-none');
    document.getElementById('importexportMenu').remove();
}

function doImportConf() {
    document.getElementById('loadscr').classList.remove('d-none');
    document.getElementById('confscr').classList.add('d-none');

    let conf = document.getElementById('iconfbox').value;
    conf = JSON.parse(conf);

    for (var c in conf) {
        Cookies.set(c, conf[c]);
    }

    location.reload();
}

function exportConf() {
    document.getElementById('econfbox').value = JSON.stringify(Cookies.get());
    document.getElementById('exportconf').classList.remove('d-none');
}

function applyGeneralSettings() {
    setGColor(parseInt(Cookies.get('gcolorset') ?? '0'));
}

function applyReaderConfig() {
    document.getElementById('reader-container').style.maxWidth = (Cookies.get('readermaxwid') ?? '700') + 'px';
    setColor(parseInt(Cookies.get('colorset') ?? '0'));
}