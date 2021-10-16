function checkBookmarked(nid, sid) {
    let bmdata = Cookies.get(`bm-${nid}`);
    if (bmdata === undefined) return false;
    else if (parseInt(bmdata) !== sid) return false;
    else return true;
}

function getBookmark(nid) {
    return Cookies.get(`bm-${nid}`);
}

function setBookmark(nid, sid) {
    let ce = parseInt(Cookies.get('cookieexp') ?? '36500');
    Cookies.set(`bm-${nid}`, sid, { expires: ce });
}