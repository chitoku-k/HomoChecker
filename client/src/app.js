import "core-js/features/array/from";
import "core-js/features/array/find";
import "core-js/features/array/includes";
import "core-js/features/object/assign";
import "core-js/features/set";
import "core-js/features/string/includes";

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
