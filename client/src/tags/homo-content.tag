<homo-content>
    <div class="wrapper">
        <homo-item each={ opts.items } data-duration={ status === "ERROR" ? Infinity : duration } />
        <homo-error if={ error } />
    </div>
    <style type="text/dart-sass">
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
                @media (min-width: 880px) {
                    width: 816px;
                }
                @media (min-width: 1270px) {
                    width: 1232px;
                }
            }

            @media (max-width: 767px), (max-width: 900px) and (orientation: landscape) {
                padding: 12px 12px 0;
            }
        }
    </style>
    <script type="es6">
        import Shuffle from "shufflejs";
        import { EventSourcePolyfill } from "event-source-polyfill";

        const EventSource = global.EventSource || EventSourcePolyfill;

        this.on("mount", () => {
            this.shuffle = new Shuffle(document.querySelector(".wrapper"), {
                itemSelector: "homo-item",
                columnWidth: 400,
                gutterWidth: 16,
                speed: 200,
                easing: "cubic-bezier(.89, .08, .62, .94)",
                initialSort: {
                    by: elm => +elm.dataset.duration,
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
            opts.initialized = true;
            opts.progress.max = JSON.parse(event.data).count;
            opts.progress.trigger("update");
        });
        source.addEventListener("response", event => {
            opts.items.push(JSON.parse(event.data));
            this.update();

            opts.progress.length = opts.items.length;
            opts.progress.trigger("update");
        });
        source.addEventListener("error", event => {
            if (!opts.initialized) {
                this.error = new Error("Service Unavailable");
                this.update();
                return;
            }
            if (opts.progress.max === opts.items.length) {
                source.close();
                return;
            }
            opts.items.length = 0;
            opts.progress.trigger("update");
        });
    </script>
</homo-content>
