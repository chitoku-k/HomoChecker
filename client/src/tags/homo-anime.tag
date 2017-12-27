<homo-anime class="start">
    <i class="fa fa-mars part-passive"></i>
    <i class="fa fa-mars part-active"></i>
    <style type="text/scss">
        homo-anime {
            display: inline-block;
            margin-right: -15px;
            margin-left: -3px;
            min-width: 30px;
            transform: translate(0px, -5px);

            @media (max-width: 767px) {
                transform: translate(0px, -3px);
            }

            .fa-mars {
                color: white;
                display: inline-block;
            }

            .part-active {
                position: relative;
                top: 0.6em;
                left: -1.575em;
            }

            &.start {
                @keyframes active-start {
                    25% {
                        transform: rotate(15grad);
                    }

                    75% {
                        transform: rotate(-15grad);
                    }
                }

                .part-active {
                    animation: active-start .5s 8 cubic-bezier(.05, .44, .95, .55);
                }
            }

            &.pako {
                @keyframes passive-pako {
                    25% {
                        transform: translate(1px, -1.33px);
                    }

                    75% {
                        transform: translate(0px, 0px);
                    }
                }

                @keyframes active-pako {
                    25% {
                        transform: translate(3px, -4px);
                    }

                    75% {
                        transform: translate(-5px, 4px);
                    }
                }

                .part-passive {
                    animation: passive-pako .6s 25 cubic-bezier(.05, .44, .95, .55);
                }

                .part-active {
                    animation: active-pako .6s 25 cubic-bezier(.05, .44, .95, .55);
                }

            }

            &.finish {
                @keyframes passive-finish {
                    0% {
                        transform: translate(0px, 0px);
                    }

                    100% {
                        transform: translate(1px, -1.33px);
                    }
                }

                @keyframes active-finish {
                    0% {
                        transform: translate(1px, -1.33px);
                    }

                    100% {
                        transform: translate(3px, -4px);
                    }
                }

                .part-passive {
                    animation: passive-finish .5s 7 alternate;
                }

                .part-active {
                    animation: active-finish .5s 7 alternate;
                }

            }

            &.dopyulicated {
                @keyframes active-dopyulicated {
                    0% {
                        transform: translate(3px, -4px);
                    }

                    100% {
                        transform: translate(3px, -4px) scale(1.1, 1.1);
                    }
                }

                .part-passive {
                    transform: translate(1px, -1.33px);
                }

                .part-active {
                    animation: active-dopyulicated .5s 8 alternate;
                }
            }

            &.end {
                @keyframes passive-end {
                    0% {
                        transform: translate(1px, -1.33px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                @keyframes active-end {
                    0% {
                        transform: translate(3px, -4px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .part-passive {
                    animation: passive-end 5s linear;
                }

                .part-active {
                    animation: active-end 5s linear;
                }
            }
        }
    </style>
    <script type="es6">
        import { Observable } from "rxjs";
        import "rxjs/add/observable/fromEvent";
        import { map } from "rxjs/operators/map";
        import { delayWhen } from "rxjs/operators/delayWhen";
        import "rxjs/add/operator/delay";

        window.addEventListener("DOMContentLoaded", () => {
            const animeElement = document.querySelector("homo-anime");
            const activeElement = document.querySelector("homo-anime .part-active");

            const classes = [ "start", "pako", "finish", "dopyulicated", "end" ];

            Observable.fromEvent(activeElement, "animationend").pipe(
                map(() => classes[(classes.indexOf(animeElement.className) + 1) % classes.length]),
                delayWhen(next => {
                    if (next !== "start") {
                        return Observable.of(next);
                    }
                    return Observable.of(next).delay(5000);
                })
            ).subscribe(next => {
                animeElement.className = next;
            });
        });
    </script>
</homo-anime>
