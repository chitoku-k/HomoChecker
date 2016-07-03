<homo-item>
    <div class="subdomain">
        <h2>
            <a href={ `https://twitter.com/${homo.screen_name}` } target="_blank">
                <img src={ homo.icon } width="48" height="48">
            </a>
            <a href={ homo.url } target="_blank">{ homo.display_url }</a>
            <i class={ fa: true, fa-lock: homo.secure }></i>
        </h2>
    </div>
    <div class="duration">{ duration.toFixed(2) } s</div>
    <i class={
        status: true,
        fa: true,
        fa-check: status === "OK",
        fa-times: status === "WRONG",
        fa-ban: status === "ERROR",
        fa-exclamation-triangle: status === "CONTAINS"
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

                a:hover {
                    text-decoration: underline;
                }

                h2 {
                    color: #444;
                    font-size: 36px;
                    font-weight: 700;
                    margin: 0;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    line-height: 1.1;

                    .fa-lock {
                        font-size: 22px;
                        padding: 0 6px;
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

                &.fa-check {
                    color: #449d44;
                }
                &.fa-times {
                    color: #c9302c;
                    margin-right: 10px;
                }
                &.fa-ban {
                    color: #c9302c;
                    margin-right: 7px;
                }
                &.fa-exclamation-triangle {
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
        }
    </style>
</homo-item>
