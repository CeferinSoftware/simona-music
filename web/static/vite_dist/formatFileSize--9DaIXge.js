function a(o){const t=Math.floor(Math.log(o)/Math.log(1024));return(o/Math.pow(1024,t)).toFixed(2)+" "+["B","kB","MB","GB","TB"][t]}export{a as f};
