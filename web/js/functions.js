

// --------------------------------------------------------
// Page title
// --------------------------------------------------------
let pageTitleRoot = `${document.title}`

export function setPageTitleRoot (newRoot) {
    pageTitleRoot = newRoot
}

export function setPageTitle (newTitle, delimiter = ' - ') {

    // set document.title based on root and newTitle
    let theTitle = ''
    if (pageTitleRoot && newTitle) {
        theTitle = `${pageTitleRoot}${delimiter}${newTitle}`
    } else if (!pageTitleRoot && newTitle) {
        theTitle = newTitle
    } else if (pageTitleRoot && !newTitle) {
        theTitle = pageTitleRoot
    }
    document.title = theTitle
}

// --------------------------------------------------------
// Configuration
// --------------------------------------------------------
let config = {}

export function setConfig (newConfig) {
    config = newConfig
}

export function getConfig (name, def = null) {
    return (name in config) ? config[name] : def
}
