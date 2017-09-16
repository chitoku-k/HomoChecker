<homo-item>
    <div class="container">
        <a href={ "https://twitter.com/" + homo.screen_name } target="_blank">
            <img src={ homo.icon } width="64" height="64">
        </a>
        <div class="subdomain">
            <i class="fa fa-lock secure" if={ homo.secure }>HTTPS</i>
            <h2>
                <a class="url" href={ homo.url } target="_blank">{ homo.display_url }</a>
            </h2>
        </div>
        <div class="result">
            <div if={ status !== "ERROR" } class="duration">{ Math.round(duration * 1000) } ms</div>
            <i class={
                status: true,
                fa: true,
                fa-check: status === "OK",
                fa-ban: status === "WRONG",
                fa-times: status === "ERROR",
                fa-exclamation-triangle: status === "CONTAINS"
            }></i>
        </div>
    </div>
    <style type="text/scss">
        homo-item {
            color: #111;
            width: 100%;

            .container {
                position: relative;
                display: flex;
                align-items: center;
                background: #fff;
                box-shadow: 0px 2px 4px 0px #d2d2d2;
                border-radius: 3px;
                padding: 10px;
                margin-bottom: 20px;
            }

            a {
                display: inline-block;
                color: #000;
                text-decoration: none;
                -webkit-tap-highlight-color: initial;
                margin: 0 12px 0 0;
                line-height: 1.2;
            }

            img {
                vertical-align: middle;
                border: none;
                border-radius: 3px;
            }

            .subdomain {
                margin-right: 6px;
                display: flex;
                flex-direction: column;
                align-self: stretch;
                justify-content: space-around;

                .secure {
                    display: block;
                    color: #5c9a4f;
                    font-family: FontAwesome, Atlan;
                    font-size: 14px;

                    &:before {
                        padding-right: .3em;
                    }
                }

                .url {
                    border-bottom: 2px solid transparent;

                    span {
                        display: inline-block;
                        margin-bottom: -2px;
                        padding-bottom: 2px;
                        border-bottom: 2px solid #af9369;
                    }

                    &:hover {
                        border-bottom-color: #666;
                    }
                }

                h2 {
                    color: #444;
                    font-size: 32px;
                    font-weight: normal;
                    margin: 0;
                    word-break: break-all;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            }

            .result {
                display: flex;
                align-items: center;
                margin-left: auto;

                .duration {
                    font-size: 22px;
                    color: #666;
                    white-space: nowrap;
                }

                .status {
                    font-size: 42px;
                    padding: 0 16px;
                    min-width: 40px;
                    text-align: center;

                    &.fa-check {
                        color: #449d44;
                    }
                    &.fa-times {
                        color: #c9302c;
                    }
                    &.fa-ban {
                        color: #c9302c;
                    }
                    &.fa-exclamation-triangle {
                        color: #ec971f;
                    }
                }
            }

            @media (max-width: 767px), (max-width: 992px) and (orientation: landscape) {
                .container {
                    padding: 8px;
                    margin-bottom: 12px;
                }

                a {
                    margin-right: 8px;
                }

                img {
                    width: 40px;
                    height: 40px;
                }

                .subdomain {
                    h2 {
                        font-size: 18px;
                        margin-right: 0;
                    }
                }

                .result {
                    flex-direction: column-reverse;
                    min-width: 50px;

                    .duration {
                        font-size: 15px;
                    }

                    .status {
                        padding: 0 0 0 5px;
                        min-width: 30px;
                        font-size: 24px;
                    }
                }
            }

            @media (max-width: 450px) {
                .result {
                    .duration {
                        font-size: 14px;
                    }

                    .status {
                        min-width: 20px;
                        font-size: 20px;
                    }
                }
            }
        }
    </style>
    <script type="es6">
        this.regex = /homo|ホモ/;

        this.on("mount", () => {
            const [ elm ] = this.root.getElementsByClassName("url");
            if (!elm) {
                return;
            }

            let match;
            while ((match = this.regex.exec(elm.lastChild.textContent))) {
                if (match.index < 0 || elm.lastChild.nodeType !== Node.TEXT_NODE) {
                    continue;
                }

                // Get the matched string
                const [ target ] = match;

                // Create range and surround the keyword, then lastChild points another node
                const range = document.createRange();
                range.setStart(elm.lastChild, match.index);
                range.setEnd(elm.lastChild, match.index + target.length);
                range.surroundContents(document.createElement("span"));
            }
        });
    </script>
</homo-item>
