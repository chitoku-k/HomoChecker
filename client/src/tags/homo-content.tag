<homo-content>
    <div class="wrapper">
        <div class="loading" if={ !items.length }>
            <i class="fa fa-refresh fa-spin"></i>
            ホモを集めています...
        </div>
        <homo-item each={ items } />
    </div>
    <style type="text/scss">
        homo-content {
            display: block;
            padding: 20px 0;
            font-family: Helvetica, "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ Pro W3", Meiryo, sans-serif;

            .loading {
                text-align: center;
                position: absolute;
                top: calc(50% - 2em);
                left: 0;
                width: 100%;
                color: #444444;
                font-size: 18px;

                .fa-spin {
                    vertical-align: -5px;
                    margin-right: 3px;
                    font-size: 36px;
                }
            }

            .wrapper {
                margin-left: auto;
                margin-right: auto;

                @media (min-width: 768px) {
                    width: 750px;
                }
                @media (min-width: 992px) {
                    width: 970px;
                }
                @media (min-width: 1200px) {
                    width: 1170px;
                }
            }

            @media (max-width: 767px) {
                padding: 8px;
            }
        }
    </style>
    <script type="es6">
        this.items = [];

        const source = new EventSource("/check/");
        source.addEventListener("response", event => {
            this.items.push(JSON.parse(event.data));
            this.items.sort((x, y) => {
                if (x.duration === y.duration) {
                    return x.homo.display_url < y.homo.display_url ? -1 : 1;
                }
                return x.duration - y.duration;
            });
            this.update();
        });
        source.addEventListener("close", event => {
            source.close();
        });
    </script>
</homo-content>
