import { OverlayRenderer } from "./renderer/OverlayRenderer.js";
import { createDemoScene } from "./scenes/demoScene.js";
import { Controls } from "./runtime/Controls.js";

const canvas = document.getElementById("overlay");

// DPI scaling (netteté sur 4K/retina)
const dpr = Math.max(1, window.devicePixelRatio || 1);
canvas.width  = 1920 * dpr;
canvas.height = 1080 * dpr;
const ctx = canvas.getContext("2d");
ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

const renderer = new OverlayRenderer(canvas, { fps: 60 });

const scene = createDemoScene(); // à remplacer par tes données runtime
renderer.loadScene(scene);

// Pont de compatibilité : mêmes commandes qu’avant (show/hide/update…)
const controls = new Controls(renderer);
window.overlay = controls; // ex: overlay.showLowerThird({title:"…", name:"…"})
renderer.start();

// Exemples de capture
window.capturePNG = async () =>
  new Promise(r => canvas.toBlob(b => r(URL.createObjectURL(b)), "image/png"));

window.startRecording = () => {
  const stream = canvas.captureStream(60);
  const rec = new MediaRecorder(stream, { mimeType: "video/webm;codecs=vp9" });
  const chunks = [];
  rec.ondataavailable = e => chunks.push(e.data);
  rec.onstop = () => {
    const blob = new Blob(chunks, {type:"video/webm"});
    const url = URL.createObjectURL(blob);
    console.log("Recording URL:", url); // à téléverser ou télécharger
  };
  rec.start();
  window._rec = rec;
};
window.stopRecording = () => window._rec?.stop();
