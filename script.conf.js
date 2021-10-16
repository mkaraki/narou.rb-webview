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