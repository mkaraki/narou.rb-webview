function urlGen(endpoint: String, queryParams: Object = {}, baseQueryParams: URLSearchParams|undefined = undefined, keepCommitId: boolean = true) {
    let queryBuilder = new URLSearchParams(typeof baseQueryParams !== 'undefined' ? baseQueryParams : undefined);
    for (const [key, value] of Object.entries(queryParams)) {
        if (queryBuilder.has(key))
            queryBuilder.set(key, value);
        else
            queryBuilder.append(key, value);
    }

    const url = new URL(location.href);

    if (keepCommitId && url.searchParams.has('commit_id')) {
        const commit_id = url.searchParams.get('commit_id') ?? '';
        if (!queryBuilder.has('commit_id'))
            queryBuilder.append('commit_id', commit_id);
    }

    return `${endpoint}?${queryBuilder.toString()}`;
}

function runUrlGen() {
    Array.from(document.getElementsByClassName('urlgen-novel-link')).forEach(function (e) {
        const el = e as HTMLAnchorElement;
        const novelId = el.dataset.novelId;
        el.href = urlGen(`/novels/${novelId}`);
    });
    Array.from(document.getElementsByClassName('urlgen-author-search')).forEach(function (e) {
        const el = e as HTMLAnchorElement;
        const author = el.dataset.author;
        if (author === undefined)
            return;
        el.href = urlGen(`/novels`, {
            'author_exact': author,
        });
    });
    Array.from(document.getElementsByClassName('urlgen-subtitles-link')).forEach(function(e) {
        const el = e as HTMLAnchorElement;
        const novelId = el.dataset.novelId;
        const subtitleId = el.dataset.subtitleId;
        el.href = urlGen(`/novels/${novelId}/subtitles/${subtitleId}`);
    });

    const url = new URL(location.href);

    Array.from(document.getElementsByClassName('urlgen-page')).forEach(function(e) {
        const el = e as HTMLAnchorElement;
        const page = el.dataset.page;
        if (page === undefined) {
            return;
        }
        const pagePath = url.pathname;
        el.href = urlGen(pagePath, {
            p: page,
        }, url.searchParams);
    });
}

function runTimeFmt() {
    Array.from(document.getElementsByClassName('format-time')).forEach(function (e) {
        const el = e as HTMLElement;
        if (!el.dataset.time)
            return;
        const time = parseInt(el.dataset.time);
        if (isNaN(time))
            return;
        el.innerText = new Date(time * 1000).toLocaleString();
    });
}

const performGlobalSearch = () => {
    const searchForm = document.getElementById('global-search') as HTMLFormElement;
    const searchType = document.getElementById('global-search-type') as HTMLSelectElement;
    const searchQuery = document.getElementById('global-search-query') as HTMLInputElement;

    const queryHiddenElem = document.createElement('input');
    queryHiddenElem.type = 'hidden';
    searchForm.appendChild(queryHiddenElem);

    switch (searchType.value) {
        case "title-grep": {
            searchForm.action = '/novels';
            queryHiddenElem.value = searchQuery.value;
            queryHiddenElem.name = 'title_like';
            break;
        }
        case "author-grep": {
            searchForm.action = '/novels';
            queryHiddenElem.value = searchQuery.value;
            queryHiddenElem.name = 'author_like';
            break;
        }
        case "novel-index": {
            searchForm.action = '/search/novel';
            queryHiddenElem.value = searchQuery.value;
            queryHiddenElem.name = 'query';
            break;
        }
        case "story-index": {
            searchForm.action = '/search/story';
            queryHiddenElem.value = searchQuery.value;
            queryHiddenElem.name = 'query';
            break;
        }
    }
};

window.addEventListener('load', function () {
    runUrlGen();
    runTimeFmt();

    const searchForm = document.getElementById('global-search');
    if (searchForm !== null) {
        const searchFormElement = searchForm as HTMLFormElement;
        searchFormElement.setAttribute('onsubmit', '');
        searchFormElement.addEventListener('submit', function (_e) {
            performGlobalSearch();
        });
    }
});
