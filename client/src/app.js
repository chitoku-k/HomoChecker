// polyfill
import "@babel/polyfill";

import riot from "riot";
import "font-awesome/scss/font-awesome.scss";
import "./styles.scss";
import "./tags/homo-anime";
import "./tags/homo-app";
import "./tags/homo-content";
import "./tags/homo-header";
import "./tags/homo-item";
import "./tags/homo-progress";

const viweport = document.createElement("meta");
viweport.name = "viewport";
viweport.content = "initial-scale=1.0, viewport-fit=cover";
document.head.appendChild(viweport);

const color = document.createElement("meta");
color.name = "theme-color";
color.content = "#7a6544";
document.head.appendChild(color);

const app = document.createElement("homo-app");
document.body.appendChild(app);

riot.mount("homo-app", {
    items: [],
    progress: {
        max: 0,
        length: 0,
    },
});
