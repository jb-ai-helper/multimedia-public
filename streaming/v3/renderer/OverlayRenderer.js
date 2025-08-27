import { AssetStore } from "./core/AssetStore.js";
import { Layer } from "./core/Layer.js";

export class OverlayRenderer {
  constructor(canvas, { fps = 60 } = {}) {
    this.canvas = canvas;
    this.ctx = canvas.getContext("2d");
    this.fps = fps;
    this.assetStore = new AssetStore();
    this.layers = [];
    this._running = false;
    this._lastTime = 0;
  }

  async loadScene(scene) {
    // charge assets (images, polices à venir)
    await this.assetStore.preload(scene.assets || []);
    this.layers = (scene.layers || []).map(def => Layer.fromDefinition(def, this.assetStore));
  }

  start() {
    if (this._running) return;
    this._running = true;
    const frame = (t) => {
      if (!this._running) return;
      const dt = (t - this._lastTime) / 1000 || 0;
      this._lastTime = t;

      // update
      for (const l of this.layers) l.update(dt);

      // render
      const ctx = this.ctx;
      ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
      for (const l of this.layers) l.draw(ctx);

      requestAnimationFrame(frame);
    };
    requestAnimationFrame(frame);
  }

  stop() { this._running = false; }

  // API haut niveau
  setData(path, value) {
    // ex: 'lowerThirds.0.title' -> met à jour un layer ciblé
    for (const l of this.layers) l.setData(path, value);
  }
  trigger(name, payload) {
    for (const l of this.layers) l.trigger(name, payload);
  }
}
