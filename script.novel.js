const bmark_txt = ' 🔖';

function bookmarkApply(nid) {
    let bmsid = getBookmark(nid);
    if (bmsid === undefined) return;
    bmsid = parseInt(bmsid);

    document.getElementById(`s-${bmsid}`).innerText += bmark_txt;
}