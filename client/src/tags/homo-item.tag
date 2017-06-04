<homo-item>
    <div class="subdomain">
        <h2>
            <a href={ "https://twitter.com/" + homo.screen_name } target="_blank">
                <img src={ homo.icon } width="64" height="64">
            </a>
            <i class={ fa: true, fa-lock: homo.secure }></i>
            <a class="url" href={ homo.url } target="_blank">{ homo.display_url }</a>
        </h2>
    </div>
    <div class="duration">{ Math.round(duration * 1000) } ms</div>
    <i class={
        status: true,
        fa: true,
        fa-check: status === "OK",
        fa-ban: status === "WRONG",
        fa-times: status === "ERROR",
        fa-exclamation-triangle: status === "CONTAINS"
    }></i>
    <style type="text/scss">
        homo-item {
            display: block;
            border: 1px solid #dfdfdf;
            background: #fff;
            color: #111;
            padding: 10px;
            font-family: Helvetica, "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ Pro W3", Meiryo, sans-serif;

            a {
                color: #000;
                text-decoration: none;
                display: inline-block;
                -webkit-tap-highlight-color: initial;
            }

            .subdomain {
                margin-right: 54px;

                img {
                    margin: 0 4px 0 0;
                    vertical-align: middle;
                    border: none;
                }

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
                    font-size: 36px;
                    font-weight: 700;
                    margin: 0 90px 0 0;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    line-height: 1.1;

                    .fa-lock {
                        float: right;
                        font-size: 22px;
                        margin-top: 20px;
                        margin-right: 20px;
                    }

                    &:after {
                        display: block;
                        content: "";
                        clear: both;
                    }
                }
            }

            .status {
                float: right;
                font-size: 42px;
                padding: 0 4px;
                margin-top: -55px;
                margin-right: 10px;
                text-align: center;
                min-width: 40px;

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

            .duration {
                float: right;
                margin-top: -45px;
                margin-right: 80px;
                font-size: 22px;
                color: #666;
            }

            & + & {
                margin-top: 15px;
            }

            @media (max-width: 767px) {
                padding: 8px;

                .subdomain {
                    img {
                        width: 40px;
                        height: 40px;
                        margin-right: 2px;
                    }

                    h2 {
                        font-size: 18px;
                        margin-right: 0;
                        width: calc(100% - 45px);

                        .fa-lock {
                            font-size: 18px;
                            margin-top: 10px;
                            margin-right: 0;
                        }
                    }
                }

                .status {
                    margin-top: -52px;
                    margin-right: -5px;
                    padding: 18px 2px 0;
                    min-width: 35px;
                    font-size: 24px;
                }

                .duration {
                    font-size: 15px;
                    margin-top: -30px;
                    margin-right: 40px;
                }

                & + & {
                    margin-top: 8px;
                }
            }
        }
    </style>
    <script>
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
