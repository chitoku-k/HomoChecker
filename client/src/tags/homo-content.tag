<homo-content>
    <div class="wrapper">
        <homo-item each={ items } />
    </div>
    <style type="text/scss">
        homo-content {
            display: block;
            padding: 20px 0;

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
    <script>
        this.items = [];

        const source = new EventSource("/app/src/Controller/check");
        source.addEventListener("response", event => {
            this.items.push(JSON.parse(event.data));
            this.items.sort((x, y) => x.duration - y.duration);
            this.update();
        });
        source.addEventListener("close", event => {
            source.close();
        });
    </script>
</homo-content>
