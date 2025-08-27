export class AssetStore {
  constructor(){ this.images = new Map(); }
  async preload(assets){
    const toLoad = assets?.filter(a => a.type === "image") ?? [];
    await Promise.all(toLoad.map(a => this.loadImage(a.key, a.src)));
  }
  loadImage(key, src){
    return new Promise((res, rej)=>{
      const img = new Image();
      img.onload = () => { this.images.set(key, img); res(img); };
      img.onerror = rej;
      img.src = src;
      img.decoding = "async";
    });
  }
  getImage(key){ return this.images.get(key) || null; }
}
