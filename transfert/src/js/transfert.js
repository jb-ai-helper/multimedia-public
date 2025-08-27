document.addEventListener("DOMContentLoaded", () => {
  /* --------- Références DOM --------- */
  const dropZone    = document.getElementById("drop-zone");
  const fileList    = document.getElementById("file-items");
  const downloadBtn = document.getElementById("download-selected");

  /* --------- Sélecteur caché & DnD --- */
  const fileInput = Object.assign(document.createElement("input"), {
    type:"file", multiple:true, style:"display:none"
  });
  document.body.appendChild(fileInput);
  dropZone.addEventListener("click", () => fileInput.click());
  fileInput.addEventListener("change", () => {
    const dt = new DataTransfer();
    for (const f of fileInput.files) dt.items.add(f);
    dropZone.dispatchEvent(new DragEvent("drop",{dataTransfer:dt,bubbles:true}));
  });

  dropZone.addEventListener("dragover",e=>{e.preventDefault();dropZone.classList.add("dragover");});
  dropZone.addEventListener("dragleave",()=>dropZone.classList.remove("dragover"));

  /* --------- Upload via DnD ----------- */
  dropZone.addEventListener("drop", async e => {
    e.preventDefault(); dropZone.classList.remove("dragover");
    const files=[...e.dataTransfer.files]; if(!files.length) return;

    if (files.length > 1) {                        // ZIP groupé
      let zipName=prompt("Nom du fichier ZIP :","transfert.zip")||"transfert.zip";
      if(!zipName.toLowerCase().endsWith(".zip")) zipName=zipName.replace(/\.[^/.]+$/,"")+".zip";
      const pwd = prompt("Mot de passe (laisser vide si aucun) :")||"";
      const zip=new JSZip(); files.forEach(f=>zip.file(f.name,f));
      const blob=await zip.generateAsync({type:"blob"});
      const fd=new FormData();
      fd.append("file",new File([blob],zipName,{type:"application/zip"}));
      fd.append("name",zipName); fd.append("password",pwd);
      const res=await fetch("src/php/upload.php",{method:"POST",body:fd});
      if(!res.ok) alert(await res.text());
    } else {                                       // fichier unique
      const pwd = prompt("Mot de passe (laisser vide si aucun) :")||"";
      for (const f of files){
        const fd=new FormData();
        fd.append("file",f); fd.append("name",f.name); fd.append("password",pwd);
        const res=await fetch("src/php/upload.php",{method:"POST",body:fd});
        if(!res.ok) alert(`Erreur sur ${f.name} : ${await res.text()}`);
      }
    }
    refreshFileList();
  });

  /* --------- Helpers FETCH ----------- */
  function fetchFile(name,pwd=null){
    const fd=new FormData(); if(pwd!==null) fd.append("password",pwd);
    return fetch(`src/php/download.php?file=${encodeURIComponent(name)}`,{
      method:"POST", body:fd, headers:{ "X-Requested-With":"XMLHttpRequest" }
    });
  }
  async function treatResponse(res,fileName,zip=null){
    const isJson = res.headers.get("Content-Type")?.includes("application/json");
    if(res.ok && !isJson){
      const blob = await res.blob();
      if(zip) zip.file(fileName,await blob.arrayBuffer());
      else {
        const url=URL.createObjectURL(blob);
        Object.assign(document.createElement("a"),{href:url,download:fileName}).click();
        URL.revokeObjectURL(url);
      }
      return true;
    }
    if(isJson)  return false;   // password_required
    return null;                // erreur 403/404
  }

  /* --------- Liste des fichiers ------- */
  async function refreshFileList(){
    const res=await fetch("src/php/list.php"); if(!res.ok) return;
    const files=await res.json(); fileList.innerHTML="";
    files.forEach(file=>{
      const li=document.createElement("li");
      const cb=Object.assign(document.createElement("input"),{type:"checkbox"});
      cb.addEventListener("change",()=>downloadBtn.style.display=
        fileList.querySelector("input[type='checkbox']:checked")?"block":"none");

      const link=Object.assign(document.createElement("a"),{
        textContent:file.name, className:"file-name", href:"#"
      });
      link.addEventListener("click",()=>handleDownload(file.name));

      const act=document.createElement("div"); act.className="file-actions";

      const copy=document.createElement("button"); copy.title="Copier le lien";
      copy.innerHTML="&#x1F4CB;";
      copy.addEventListener("click",()=>{
        navigator.clipboard.writeText(file.url);
        alert("Lien copié dans le presse-papier !");
      });

      const del=document.createElement("button"); del.title="Supprimer";
      del.innerHTML="&#x274C;";
      del.addEventListener("click",async()=>{
        if(!confirm("Supprimer ce fichier ?"))return;
        const fd=new FormData(); fd.append("file",file.name);
        await fetch("src/php/delete.php",{method:"POST",body:fd});
        refreshFileList();
      });

      act.append(copy,del); li.append(cb,link,act); fileList.appendChild(li);
    });
    downloadBtn.style.display="none";
  }

  /* --------- Téléchargement individuel - */
  async function handleDownload(fileName){
    let res=await fetchFile(fileName);
    let st = await treatResponse(res,fileName);
    if(st===true) return;
    if(st===null){alert("Erreur serveur");return;}

    for(let i=0;i<3;i++){
      const pwd=prompt(`Mot de passe pour « ${fileName} » :`); if(pwd===null)return;
      res=await fetchFile(fileName,pwd); st=await treatResponse(res,fileName);
      if(st===true) return;
      if(st===null) alert("Mot de passe incorrect !");
    }
    alert("Trop d’échecs.");
  }

  /* --------- Téléchargement multiple ? ZIP - */
  downloadBtn.addEventListener("click",async()=>{
    const zip=new JSZip();
    const selected=[...fileList.querySelectorAll("input[type='checkbox']:checked")];
    let sharedPwd=null;

    for(const box of selected){
      const fileName=box.closest("li").querySelector(".file-name").textContent;
      let ok=false, res, st;

      const attempts = sharedPwd ? [null, sharedPwd] : [null];  // test null d’abord
      for(const pwd of attempts){
        res=await fetchFile(fileName,pwd);
        st=await treatResponse(res,fileName,zip);
        if(st===true){ if(pwd!==null) sharedPwd=pwd; ok=true; break; }
        if(st===null) break;           // mot de passe incorrect
      }
      if(ok) continue;

      for(let i=0;i<3 && !ok;i++){
        const pwd=prompt(`Mot de passe requis pour « ${fileName} » :`); if(pwd===null)return;
        res=await fetchFile(fileName,pwd); st=await treatResponse(res,fileName,zip);
        if(st===true){ sharedPwd=pwd; ok=true; break; }
        alert("Mot de passe incorrect !");
      }
      if(!ok){ alert("Échec de téléchargement."); return; }
    }

    const zipBlob=await zip.generateAsync({type:"blob"});
    const url=URL.createObjectURL(zipBlob);
    Object.assign(document.createElement("a"),{href:url,download:"download.zip"}).click();
    URL.revokeObjectURL(url);
  });

  /* --------- Initialisation ----------- */
  refreshFileList();
});
