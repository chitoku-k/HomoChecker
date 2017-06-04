import riot from "riot";
import "font-awesome/scss/font-awesome.scss";
import "event-source-polyfill";
import "./styles.css";
import "./tags/homo-anime";
import "./tags/homo-app";
import "./tags/homo-content";
import "./tags/homo-header";
import "./tags/homo-item";

{
    const meta = document.createElement("meta");
    meta.name = "viewport";
    meta.content = "initial-scale=1.0";
    document.head.appendChild(meta);

    const app = document.createElement("homo-app");
    document.body.appendChild(app);

    riot.mount("*");
}
