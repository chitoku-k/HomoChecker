<homo-progress>
    <div class={ body: true, done: opts.progress.done } style="width: { opts.progress.length / opts.progress.max * 100 }%"></div>
    <style type="text/scss">
        homo-progress {
            display: block;
            position: fixed;
            left: 0;
            right: 0;
            margin: auto;
            z-index: 2;

            .body {
                width: 0;
                height: 5px;
                background: #514532;
                transition: .4s width ease-in, 2s opacity ease-out;

                &.done {
                    opacity: 0;
                }
            }

            @media (min-width: 768px) {
                width: 700px;
                top: 39px;
            }
            @media (min-width: 992px) {
                width: 930px;
                top: 55px;
            }
            @media (min-width: 1200px) {
                width: 1130px;
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
