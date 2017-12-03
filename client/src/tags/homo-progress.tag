<homo-progress>
    <div class={ body: true, done: opts.progress.done } style="width: { opts.progress.length / opts.progress.max * 100 }%"></div>
    <style type="text/scss">
        homo-progress {
            display: block;
            position: fixed;
            top: 56px;
            left: 0;
            right: 0;
            margin: auto;
            z-index: 2;

            .body {
                width: 0;
                height: 3px;
                background: white;
                transition: .4s width ease-in, 2s opacity ease-out;

                &.done {
                    opacity: 0;
                }
            }

            @media (max-width: 767px), (max-width: 900px) and (orientation: landscape) {
                top: 40px;
            }
            @media (min-width: 768px) {
                width: 700px;
            }
            @media (min-width: 880px) {
                width: 817px;
            }
            @media (min-width: 1270px) {
                width: 1234px;
            }
        }
    </style>
    <script type="text/es6">
        opts.progress.on("update", () => {
            if (opts.progress.length / opts.progress.max === 1) {
                opts.progress.done = true;
            }
            this.update();
        });
    </script>
</homo-progress>
