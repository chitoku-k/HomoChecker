<homo-item>
    <div class="subdomain">
        <h2>
            <a href={ `https://twitter.com/${homo.screen_name}` } target="_blank">
                <img src={ homo.icon } width="48" height="48">
            </a>
            <a href={ homo.url } target="_blank">{ homo.display_url }</a>
        </h2>
    </div>
    <div class="duration">{ duration.toFixed(2) } s</div>
    <div class="verify-icon">
    </div>
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
                }
            }

            .duration {
                float: right;
                margin-top: -35px;
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
