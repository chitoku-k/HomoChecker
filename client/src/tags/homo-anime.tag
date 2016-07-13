<homo-anime class="start">
    <i class="icon-mars part-passive"></i>
    <i class="icon-mars part-active"></i>
    <style type="text/scss">
        homo-anime {
            display: inline-block;
            margin-right: -15px;
            margin-left: -3px;
            transform: translate(0px, -3px);

            .icon-mars {
                color: white;
                display: inline-block;
            }

            .part-active {
                position: relative;
                top: 0.55em;
                left: -2.2em;
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
    <script type="babel">
        window.addEventListener("DOMContentLoaded", event => {
            const animeDOM = document.querySelector("homo-anime");
            const activeDOM = document.querySelector("homo-anime .part-active");

            const classes = [ "start", "pako", "finish", "dopyulicated", "end" ];
            const classMap = classes.reduce((obj, value, key) => {
                [ obj[key], obj[value] ] = [ value, key ];
                return obj;
            }, {});

            activeDOM.addEventListener("animationend", event => {
                const current = animeDOM.className;
                const next = classMap[(classMap[current] + 1) % classes.length];

                if (next !== "start") {
                    animeDOM.className = next;
                } else {
                    setTimeout(() => {
                        animeDOM.className = next;
                    }, 5000);
                }
            });
        });
    </script>
</homo-anime>
