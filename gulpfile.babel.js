import gulp from "gulp";
import del from "del";

const $ = require("gulp-load-plugins")();

const scripts = [
    "./node_modules/riot/riot.js",
    "./node_modules/event-source-polyfill/eventsource.js",
    "./client/dev/js/*.js",
    "!./**/*.min.js",
];

gulp.task("clean", () =>
    del(["./client/dev/**/*", "./client/dest/**/*"])
);

gulp.task("copy", () =>
    gulp.src("./client/src/*.html")
        .pipe(gulp.dest("./client/dest/"))
);

gulp.task("fonts", () =>
    gulp.src("./config.json")
        .pipe($.fontello())
        .pipe($.if("*.css", gulp.dest("./client/dev/"), gulp.dest("./client/dest/")))
);

gulp.task("styles", () =>
    gulp.src(["./client/dev/css/*.css", "!./**/*ie7*.css"])
        .pipe($.csso())
        .pipe($.concat("styles.css"))
        .pipe(gulp.dest("./client/dest/css/"))
);

gulp.task("build", () =>
    gulp.src("./client/src/tags/*.tag", { since: gulp.lastRun("build") })
        .pipe($.riot({ type: "babel" }))
        .pipe(gulp.dest("./client/dev/js/"))
);

gulp.task("lint", () =>
    gulp.src("./client/src/tags/*.tag")
        .pipe($.eslint())
        .pipe($.eslint.format())
        .pipe($.eslint.failAfterError())
);

gulp.task("minify", () =>
    gulp.src(scripts)
        .pipe($.uglify())
        .pipe($.concat("main.js"))
        .pipe(gulp.dest("./client/dest/"))
);

gulp.task("test", (cb) => {
    console.error("Error: no test specified");
    cb();
});

gulp.task("watch", (cb) =>
    gulp.watch("./client/src/tags/*.tag", gulp.series("build", "minify"))
);

gulp.task("default",
    gulp.series("clean", "build", gulp.parallel("lint", "minify", "copy", "fonts"), "styles")
);
