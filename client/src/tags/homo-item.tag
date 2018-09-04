<homo-item>
    <div class="container">
        <a href={ this.getUserUrl(homo) } target="_blank">
            <img src={ homo.icon } width="60" height="60">
        </a>
        <div class="information">
            <div class="subdomain">
                <h2>
                    <a class="url" href={ homo.url } target="_blank">{ homo.display_url }</a>
                </h2>
            </div>
            <div class="result">
                <div class="attributes-container">
                    <span class={
                        connection: true,
                        status: true,
                        ok: status === "OK",
                        wrong: status === "WRONG",
                        error: status === "ERROR",
                        contains: status === "CONTAINS"
                    }>
                        <i class={
                            fa: true,
                            fa-check: status === "OK",
                            fa-ban: status === "WRONG",
                            fa-times: status === "ERROR",
                            fa-exclamation-triangle: status === "CONTAINS"
                        }></i>
                        { status }
                    </span>
                    <span class={
                        connection: true,
                        secure: homo.secure,
                        insecure: !homo.secure,
                    }>
                        <i class={
                            fa: true,
                            fa-lock: homo.secure,
                            fa-unlock-alt: !homo.secure,
                        }></i>
                        { homo.secure ? "HTTPS" : "HTTP" }
                    </span>
                    <span if={ ip } class={
                        connection: true,
                        ipv4: ip.includes("."),
                        ipv6: ip.includes(":"),
                    } title={ ip }>
                        <i class="fa fa-globe"></i>
                        { ip.includes(":") ? "IPv6" : "IPv4" }
                    </span>
                </div>
                <div class="performance-container">
                    <div if={ status !== "ERROR" } class="duration">{ Math.round(duration * 1000) } ms</div>
                </div>
            </div>
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
                margin: 0 10px 0 0;
                line-height: 1.2;
            }

            img {
                vertical-align: middle;
                border: none;
                border-radius: 3px;
                margin: 5px;
            }

            .information {
                width: 100%;

                .subdomain {
                    margin-right: 6px;

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
                        font-size: 22px;
                        font-weight: normal;
                        margin: 0;
                        word-break: break-all;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                }

                .result {
                    display: flex;
                    width: 100%;
                    margin-top: 8px;
                    justify-content: space-between;

                    .attributes-container {
                        display: flex;
                        align-items: center;
                        font-size: 12px;
                        user-select: none;

                        .fa {
                            font-family: FontAwesome, Atlan;
                            font-size: 14px;

                            &:before {
                                padding-right: .4em;
                            }
                        }

                        .connection {
                            white-space: nowrap;
                            border: 1px solid;
                            border-radius: 3px;
                            margin: 0 4px;
                            padding: 4px 8px;

                            &:first-child {
                                margin-left: 0;
                            }
                        }

                        .insecure, .ipv4, .contains {
                            color: #ec971f;
                            border-color: #ec971f;
                        }

                        .secure, .ipv6, .ok {
                            color: #449d44;
                            border-color: #449d44;
                        }

                        .wrong, .error {
                            color: #c9302c;
                            border-color: #c9302c;
                        }
                    }

                    .performance-container {
                        display: flex;
                        align-items: center;
                        margin-left: auto;

                        .duration {
                            margin-right: 3px;
                            color: #666;
                            white-space: nowrap;
                        }
                    }
                }
            }

            @media (min-width: 900px) {
                width: 400px;

                .container {
                    flex-direction: column;
                    justify-content: space-between;
                    height: 200px;
                }

                a {
                    margin: 18px 0 0;
                }

                img {
                    width: 64px;
                    height: 64px;
                }

                .information {
                    .subdomain {
                        text-align: center;
                        margin: 0 auto 30px;

                        h2 {
                            font-size: 24px;
                        }

                        .secure {
                            margin: 3px 0 0;
                            text-align: center;
                        }

                        .url {
                            margin: 0;

                            &:hover {
                                border-bottom-color: transparent;
                                color: #888888;
                            }
                        }
                    }
                }
            }

            @media (max-width: 767px), (max-width: 900px) and (orientation: landscape) {
                .container {
                    padding: 8px;
                    margin-bottom: 12px;
                }

                a {
                    margin-right: 8px;
                }

                img {
                    width: 46px;
                    height: 46px;
                    margin: 0;
                }

                .information {
                    .subdomain {
                        h2 {
                            font-size: 16px;
                            margin-right: 0;
                        }
                    }

                    .result {
                        margin-top: 5px;

                        .attributes-container {
                            font-size: 10px;

                            .fa {
                                font-size: 10px;
                            }

                            .connection {
                                margin: 0 2px;
                                padding: 2px 6px;
                            }
                        }

                        .duration {
                            font-size: 15px;
                        }
                    }
                }
            }

            @media (max-width: 450px) {
                .result {
                    .duration {
                        font-size: 14px;
                    }
                }
            }
        }
    </style>
    <script type="es6">
        this.regex = /homo|ホモ/;

        this.getUserUrl = homo => {
            switch (homo.service) {
                case "twitter": {
                    return `https://twitter.com/${homo.screen_name}`;
                }
                case "mastodon": {
                    const [ , username, instance ] = /@?([^@]*)@(.*)\/*/.exec(homo.screen_name) || [];
                    return `https://${instance}/@${username}`;
                }
                default: {
                    return null;
                }
            }
        };

        this.on("mount", () => {
            const [ elm ] = this.root.getElementsByClassName("url");
            if (!elm) {
                return;
            }

            let match;
            while ((match = this.regex.exec(elm.lastChild.textContent))) {
                if (match.index < 0 || elm.lastChild.nodeType !== Node.TEXT_NODE) {
                    break;
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
