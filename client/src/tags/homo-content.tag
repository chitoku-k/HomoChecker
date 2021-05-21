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
        import { merge, fromEvent, using, Subject } from "rxjs";
        import { first, filter, map, publish, retry, tap } from "rxjs/operators";
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

        const stream = new Subject();
        using(
            () => {
                const source = new EventSource("/check/");
                return {
                    source,
                    count: null,
                    currentCount: 0,
                    unsubscribe: () => source.close(),
                };
            },
            resource => merge(
                fromEvent(resource.source, "initialize"),
                fromEvent(resource.source, "response"),
                fromEvent(resource.source, "error"),
            ).pipe(
                map(event => ({ resource, event })),
            ),
        ).pipe(
            publish(multicast => merge(
                multicast.pipe(
                    filter(({ event }) => event.type === "initialize"),
                    map(({ resource, event }) => ({ resource, data: JSON.parse(event.data) })),
                    tap(({ resource, data }) => resource.count = data.count),
                    map(({ resource }) => resource),
                ),
                multicast.pipe(
                    filter(({ event }) => event.type === "response"),
                    map(({ resource, event }) => ({ resource, data: JSON.parse(event.data) })),
                    tap(({ resource }) => ++resource.currentCount),
                    map(({ data }) => data),
                ),
                multicast.pipe(
                    filter(({ event }) => event.type === "error"),
                    filter(({ resource }) => resource.count === null || resource.currentCount < resource.count),
                    tap(() => {
                        throw new Error("Service Unavailable");
                    }),
                ),
                multicast.pipe(
                    filter(({ event }) => event.type === "error"),
                    filter(({ resource }) => resource.count === resource.currentCount),
                    tap(() => stream.complete()),
                    tap(({ resource }) => resource.unsubscribe()),
                ),
            )),
            retry(2),
        ).subscribe(
            data => stream.next(data),
            error => stream.error(error),
        );

        stream.pipe(
            filter(({ count }) => count),
        ).subscribe(({ count }) => {
            opts.items.length = 0;

            opts.progress.max = count;
            opts.progress.trigger("update");
        });

        stream.pipe(
            filter(({ homo }) => homo),
        ).subscribe(
            data => {
                opts.items.push(data);
                this.update();

                opts.progress.length = opts.items.length;
                opts.progress.trigger("update");
            },
            error => {
                opts.items.length = 0;

                opts.progress.length = 0;
                opts.progress.trigger("update");

                this.error = error;
                this.update();
            },
        );
    </script>
</homo-content>
