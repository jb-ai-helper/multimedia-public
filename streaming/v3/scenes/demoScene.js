export function createDemoScene(){
  return {
    assets: [
      { type:"image", key:"logo", src:"./assets/logo.png" } // remplace par ton bug
    ],
    layers: [
      { kind:"image", key:"logo", x:1720, y:40, w:140, h:140, opacity:0.9, visible:true },
      { kind:"text",  text:"Jean-Baptiste Wattiaux", x:80, y:980, opacity:1 },
      { kind:"text",  text:"Chargé de l'audiovisuel", x:80, y:1030, opacity:0.75, font:"400 32px Inter, system-ui, sans-serif" }
    ]
  };
}
