<homo-item>
    <div class="subdomain">
        <h2>
            <a href={ "https://twitter.com/" + homo.screen_name } target="_blank">
                <img src={ homo.icon } width="48" height="48">
            </a>
            <a class="url" href={ homo.url } target="_blank">{ homo.display_url }</a>
            <i class={ icon-lock: homo.secure }></i>
        </h2>
    </div>
    <div class="duration">{ duration.toFixed(2) } s</div>
    <i class={
        status: true,
        icon-ok: status === "OK",
        icon-cancel: status === "WRONG",
        icon-block: status === "ERROR",
        icon-attention: status === "CONTAINS"
    }></i>
    <div class="clearfix">
    </div>
    <style type="text/scss">
        homo-item {
            display: block;
            border: 1px solid #dfdfdf;
            background: #fff;
            color: #111;
            padding: 20px;
            font-family: Helvetica, "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ Pro W3", Meiryo, sans-serif;

            a {
                color: #000;
                text-decoration: none;
                -webkit-tap-highlight-color: initial;
            }

            .subdomain {
                margin-right: 54px;

                img {
                    margin: 0 8px 0 0;
                    vertical-align: middle;
                }

                .url:hover {
                    text-decoration: underline;
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

                    .icon-lock {
                        font-size: 22px;
                        padding: 0 6px;
                        margin-left: -5px;
                        margin-right: 4px;
                        vertical-align: 3px;
                    }
                }
            }

            .status {
                float: right;
                margin-top: -45px;
                font-size: 42px;
                padding: 0 4px;
                margin-right: 4px;

                &.icon-ok {
                    color: #449d44;
                }
                &.icon-cancel {
                    color: #c9302c;
                    margin-right: 10px;
                }
                &.icon-block {
                    color: #c9302c;
                    margin-right: 5px;
                }
                &.icon-attention {
                    color: #ec971f;
                }
            }

            .duration {
                float: right;
                margin-top: -35px;
                margin-right: 80px;
                font-size: 22px;
                color: #666;
            }

            .clearfix {
                &:before, &:after {
                    content: " ";
                    display: table;
                }
            }

            & + & {
                margin-top: 15px;
            }

            @media (max-width: 767px) {
                padding: 12px;

                .subdomain {
                    img {
                        width: 32px;
                        height: 32px;
                        margin-right: 4px;
                    }

                    h2 {
                        font-size: 18px;
                        margin-right: 30px;

                        .icon-lock {
                            font-size: 18px;
                            vertical-align: 0;
                        }
                    }
                }

                .status {
                    margin-top: -48px;
                    margin-right: -5px;
                    padding: 18px 2px 0;
                    font-size: 24px;

                    &.icon-cancel {
                        margin-right: -4px;
                    }
                    &.icon-block {
                        margin-right: -5px;
                    }
                }

                .duration {
                    font-size: 15px;
                    margin-top: -25px;
                    margin-right: 40px;
                }

                & + & {
                    margin-top: 8px;
                }
            }
        }
    </style>
</homo-item>
