// Считывает GET переменные из URL страницы и возвращает их как ассоциативный массив.
function getUrlVars(href){
    var vars = {}, hash;
    href = decodeURI(href);
    var hashes = href.slice(href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++){
        hash = hashes[i].split('=');
        
        vars[hash[0].replace(/(^\s+|\s+$)/g,'')] = hash[1];
    }

    return vars;
}