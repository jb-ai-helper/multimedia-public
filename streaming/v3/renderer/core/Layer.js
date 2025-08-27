export class Layer {
  static fromDefinition(def, assets){
    switch(def.kind){
      case "image": return new ImageLayer(def, assets);
      case "text" : return new TextLayer(def, assets);
      case "group": return new GroupLayer(def, assets);
      default: return new Layer(def, assets);
    }
  }
  constructor(def, assets){
    this.def = def;
    this.assets = assets;
    this.opacity = def.opacity ?? 1;
    this.visible = def.visible ?? true;
    this.x = def.x ?? 0; this.y = def.y ?? 0;
    this.w = def.w ?? 0; this.h = def.h ?? 0;
    this.state = { t: 0, ...def.state };
    this.children = [];
  }
  update(dt){ this.state.t += dt; for(const c of this.children) c.update(dt); }
  draw(ctx){ if(!this.visible) return; for(const c of this.children) c.draw(ctx); }
  setData(path, value){} // à spécialiser si besoin
  trigger(name, payload){} // idem
}

class GroupLayer extends Layer {
  constructor(def, assets){
    super(def, assets);
    this.children = (def.children||[]).map(d => Layer.fromDefinition(d, assets));
  }
}

class ImageLayer extends Layer {
  draw(ctx){
    if(!this.visible) return;
    const img = this.assets.getImage(this.def.key);
    if(!img) return;
    ctx.save();
    ctx.globalAlpha = this.opacity;
    ctx.drawImage(img, this.x, this.y, this.w || img.naturalWidth, this.h || img.naturalHeight);
    ctx.restore();
    super.draw(ctx);
  }
}

class TextLayer extends Layer {
  draw(ctx){
    if(!this.visible) return;
    const { text = "", font = "600 42px Inter, system-ui, sans-serif", color = "#fff", align="left" } = this.def;
    ctx.save();
    ctx.globalAlpha = this.opacity;
    ctx.font = font;
    ctx.textAlign = align;
    ctx.fillStyle = color;
    ctx.fillText(text, this.x, this.y);
    ctx.restore();
    super.draw(ctx);
  }
}
