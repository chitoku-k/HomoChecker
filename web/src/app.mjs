import { component } from "riot";
import "@fortawesome/fontawesome-free/scss/fontawesome.scss";
import "@fortawesome/fontawesome-free/scss/solid.scss";
import "./styles.scss";
import HomoApp from "./tags/homo-app.riot";

const app = document.createElement("homo-app");
document.body.appendChild(app);
component(HomoApp)(app);
