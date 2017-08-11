<homo-item>
    <div class="container">
        <a href={ "https://twitter.com/" + homo.screen_name } target="_blank">
            <img src={ homo.icon } width="64" height="64">
        </a>
        <div class="subdomain">
            <h2>
                <a class="url" href={ homo.url } target="_blank">{ homo.display_url }</a>
            </h2>
        </div>
        <div class="result">
            <i class={ fa: true, fa-lock: homo.secure }></i>
            <div class="duration">{ Math.round(duration * 1000) } ms</div>
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
            font-family: Helvetica, "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ Pro W3", Meiryo, sans-serif;
            width: calc(100% - 10px * 2 - 1px * 2);

            .container {
                display: flex;
                align-items: center;
                border: 1px solid #dfdfdf;
                background: #fff;
                padding: 10px;
                margin-bottom: 15px;
                width: 100%;
            }

            a {
                color: #000;
                text-decoration: none;
                -webkit-tap-highlight-color: initial;
            }

            img {
                margin: 0 12px 0 0;
                vertical-align: middle;
                border: none;
            }

            .subdomain {
                overflow: hidden;

                .url {
                    span {
                        border-bottom: 4px solid #af9369;
                        padding-bottom: 2px;
                    }

                    &:hover {
                        text-decoration: underline;
                    }
                }

                h2 {
                    color: #444;
                    font-size: 32px;
                    font-weight: 700;
                    margin: 0;
                    line-height: 2;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            }

            .result {
                display: flex;
                align-items: center;
                margin-left: auto;

                .fa-lock {
                    color: #444;
                    font-size: 22px;
                    margin-right: 16px;
                }

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

            @media (max-width: 767px) {
                .container {
                    margin-bottom: 8px;
                }

                img {
                    width: 40px;
                    height: 40px;
                    margin-right: 8px;
                }

                .subdomain {
                    h2 {
                        font-size: 18px;
                        margin-right: 0;
                    }
                }

                .result {
                    .fa-lock {
                        margin-right: 8px;
                        font-size: 18px;
                    }

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
        }
    </style>
    <script type="es6">
        this.keywords = [ "homo", "ホモ" ];

        this.on("updated", () => {
            const [ elm ] = this.root.getElementsByClassName("url");
            if (!elm) {
                return;
            }

            // Get the index(es) in which contains the keyword
            for (const [ match, index ] of this.keywords.map(x => [ x, elm.lastChild.textContent.indexOf(x) ])) {
                if (index < 0 || elm.lastChild.nodeType !== Node.TEXT_NODE) {
                    continue;
                }
                // Create range and surround the keyword, then lastChild points another node
                const range = document.createRange();
                range.setStart(elm.lastChild, index);
                range.setEnd(elm.lastChild, index + match.length);
                range.surroundContents(document.createElement("span"));
            }
        });
    </script>
</homo-item>
