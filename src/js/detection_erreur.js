onerror=error
var txt=""

function error(msg,url,l)
{
txt="Il y a une erreur sur cette page.\n\n"
txt+="Erreur: " + msg + "\n"
txt+="URL: " + url + "\n"
txt+="Ligne: " + l + "\n\n"
txt+="Clickez sur OK pour continuer.\n\n"
alert(txt)
return true
}