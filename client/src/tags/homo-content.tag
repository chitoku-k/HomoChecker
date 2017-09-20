<homo-content>
    <div class="wrapper">
        <homo-item each={ opts.items } data-duration={ status === "ERROR" ? Infinity : duration } />
    </div>
    <style type="text/scss">
        homo-content {
            display: block;
            padding: 20px 0;

            .wrapper {
                margin-left: auto;
                margin-right: auto;
                overflow: visible !important;

                @media (min-width: 768px) {
                    width: 700px;
                }
                @media (min-width: 992px) {
                    width: 930px;
                }
                @media (min-width: 1200px) {
                    width: 1130px;
                }
            }

            @media (max-width: 767px), (max-width: 992px) and (orientation: landscape) {
                padding: 12px 12px 0;
            }
        }
    </style>
    <script type="es6">
        import Shuffle from "shufflejs";

        this.on("mount", () => {
            this.shuffle = new Shuffle(document.querySelector(".wrapper"), {
                itemSelector: "homo-item",
                speed: 150,
                easing: "easeOutElastic",
                initialSort: {
                    by: elm => +elm.getAttribute("data-duration"),
                },
            });
        });

        this.on("updated", () => {
            if (!opts.items.length) {
                return;
            }
            this.shuffle.add([ this.root.querySelector("homo-item:last-child") ]);
        });

        const source = new EventSource("/check/");
        source.addEventListener("initialize", event => {
            opts.progress.max = JSON.parse(event.data).count;
            opts.progress.trigger("update");
        });
        source.addEventListener("response", event => {
            opts.items.push(JSON.parse(event.data));
            this.update();

            opts.progress.length = opts.items.length;
            opts.progress.trigger("update");
        });
        source.addEventListener("close", event => {
            source.close();
        });
    </script>
</homo-content>
