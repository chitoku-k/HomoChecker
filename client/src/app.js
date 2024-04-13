import riot from "riot";
import "riot-hot-reload";
import "@fortawesome/fontawesome-free/scss/fontawesome.scss";
import "@fortawesome/fontawesome-free/scss/solid.scss";
import "./styles.scss";
import "./tags/homo-anime";
import "./tags/homo-app";
import "./tags/homo-content";
import "./tags/homo-error";
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
