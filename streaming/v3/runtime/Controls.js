export class Controls {
  constructor(renderer){ this.r = renderer; }
  showLowerThird({ title, subtitle }){
    this.r.setData("lowerThird.title", title);
    this.r.setData("lowerThird.subtitle", subtitle);
    this.r.trigger("lowerThird:show");
  }
  hideLowerThird(){ this.r.trigger("lowerThird:hide"); }
  setLogo(src){ this.r.trigger("logo:set", { src }); }
}
