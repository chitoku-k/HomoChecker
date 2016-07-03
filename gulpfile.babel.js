import gulp from "gulp";
import del from "del";

const $ = require("gulp-load-plugins")();

gulp.task("copy", () =>
    gulp.src("./node_modules/riot/riot.min.js")
        .pipe(gulp.dest("./client/dest/js/lib/"))
);

gulp.task("clean", () =>
    del(["./client/dev/**/*", "./client/dest/**/*"])
);

gulp.task("build", () =>
    gulp.src("./client/src/tags/*.tag")
        .pipe($.riot())
        .pipe(gulp.dest("./client/dev/js/"))
);

gulp.task("minify", () =>
    gulp.src(["./client/dev/js/*.js", "!./**/*.min.js"])
        .pipe($.uglify())
        .pipe(gulp.dest("./client/dest/js/"))
);

gulp.task("test", (cb) => {
    console.error("Error: no test specified");
    cb();
});

gulp.task("default",
    gulp.series("clean", "build", "minify", "copy")
);
