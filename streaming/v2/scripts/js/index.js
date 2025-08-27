// JavaScript Document

function Initialize(){
    // Initialize Camera
    const url = new URL(window.location.href);
    const cam = url.searchParams.get('cam');
    if(cam == 'yes' || cam == '1'){ LoadCam(); } 

    // Initialize Dynamic Preview Resize
    ['WrapControl', 'WrapEditor'].forEach(id => {
      ['dblclick', 'mouseover', 'mouseout'].forEach(event => {
        document.getElementById(id).addEventListener(event, updatePreviewPosition);
      });
    });
}

function LoadCam(){
    var video = document.getElementById('transparent');
    
    navigator.mediaDevices
        .getUserMedia({ video: true })
        .then((stream) => {
            video.srcObject = stream;
            video.onloadedmetadata = (e) => { video.play(); };
    });
}

function updatePreviewScale() {
    const preview = document.getElementById('WrapPreview');
    const margin = 30;
    const availableWidth = window.innerWidth - (470*2) - margin;
    const availableHeight = window.innerHeight - (window.innerHeight / 4 + 10) - margin;
    var scaleY = availableHeight / 1080;
    var scaleX = availableWidth / 1920;
    var scale = scaleX;
    
    if(scaleY < scaleX) scale = scaleY;

    if(scale > 1) scale = 1;

    preview.style.transform = `translate(-50%, -50%) scale(${scale})`;
}

window.addEventListener('resize', updatePreviewScale);
window.addEventListener('load', updatePreviewScale);

function updatePreviewPosition(){
    const preview = document.getElementById('WrapPreview');
    const message = document.getElementById('WrapMessage');
    const control = document.getElementById('WrapControl');
    const editor = document.getElementById('WrapEditor');
    
    const editorActive = editor.matches(':hover') || editor.classList.contains('LOCKED');
    const controllActive = control.matches(':hover') || control.classList.contains('LOCKED');
    
    if(editorActive && controllActive){
        preview.classList.add('centered');
        message.classList.add('minimum');
    } else if (editorActive || controllActive){
        preview.classList.remove('centered');
        message.classList.remove('minimum');
    } else {
        preview.classList.remove('centered');
        message.classList.remove('minimum');
    }
}

