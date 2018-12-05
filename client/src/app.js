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

const app = document.createElement("homo-app");
document.body.appendChild(app);

riot.mount("homo-app", {
    items: [],
    progress: {
        max: 0,
        length: 0,
    },
});
