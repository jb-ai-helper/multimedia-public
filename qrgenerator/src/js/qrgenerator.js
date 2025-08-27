// JavaScript Document

async function Generate(TYPE){
    let format = document.getElementById("format").value;
    let shortlink = document.getElementById('shortlink').checked;

    if(TYPE == "url"){
        let url = prompt("Indiquer l'URL ici : ", "");
        if(shortlink){
            try{
                let shorten_url = await ShortenLink(url);
                let data = encodeURIComponent(shorten_url);
                let QR = "https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&charset-source=UTF-8&ecc=L&margin=0&format="+format+"&data="+data;
                window.open(QR, "_blank");
                }
            catch(error){
                console.error('Error shortening URL:', error);
            }
        }
        else{
            let data = encodeURIComponent(url);
            let QR = "https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&charset-source=UTF-8&ecc=L&margin=0&format="+format+"&data="+data;
            if(url){ window.open(QR,"_blank"); }
        }
    }
    else if(TYPE == "email"){
        let email = encodeURI(document.getElementById("email").value);
        let subject = encodeURI(document.getElementById("subject").value) || "";
        let message = encodeURI(document.getElementById("message").value) || "";
        
        if(email == ""){ alert("L'adresse email du destinataire est obligatoire !") }
        else{
            let QR = "https://api.qrserver.com/v1/create-qr-code/?data=MATMSG%3ATO%3A"+email+"%3BSUB%3A"+subject+"%3BBODY%3A"+message+"%3B%3B&size=1000x1000&charset-source=UTF-8&ecc=L&margin=0&format="+format;
            window.open(QR,"_blank");
        }
    }
    else if(TYPE == "multiple"){
        let list = document.getElementById("multiple");
        let formData = new FormData();
            formData.append("list", list.files[0]);
            formData.append("format", format);
            formData.append("short", shortlink);

        let xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                console.log('Response: ', xhttp.response);
                document.body.classList.remove('wait');
                Download(xhttp.response);
                list.value = null;
            };

        xhttp.open("POST", "src/php/multiple.php", true);
        xhttp.send(formData);
        document.body.classList.add('wait');
    }
}

function Download(FILE){
    var anchor = document.createElement('a');
        anchor.setAttribute('href', "files/"+FILE);
        anchor.setAttribute('download', "MultipleQR.zip");
        anchor.click();
}

function ShortenLink(URL) {
    return new Promise((resolve, reject) => {
        if (URL != null) {
            let xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if (xhttp.status >= 200 && xhttp.status < 300) {
                    resolve(xhttp.response);
                } else {
                    reject(new Error('Request failed with status ' + xhttp.status));
                }
            };
            xhttp.onerror = function() {
                reject(new Error('Network error'));
            };
            const data = { link: URL };
            xhttp.open("POST", "src/php/shorten.php", true);
            xhttp.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhttp.send(JSON.stringify(data));
        } else {
            reject(new Error('Invalid URL'));
        }
    });
}