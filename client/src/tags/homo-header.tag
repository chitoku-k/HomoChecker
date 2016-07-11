<homo-header>
    <nav>
        <ul>
            <li class="active"><a><homo-anime></homo-anime> HOMO CHECKER</a>
            <li>
            <a href="https://twitter.com/intent/tweet?text=%40java_shit+%E4%BF%BA%E3%82%82%E3%83%9B%E3%83%A2%E3%81%A0%EF%BC%81URL%3A+http%3A%2F%2F" target="_blank">REGISTER</a>
        </ul>
    </nav>
    <style type="text/scss">
        body {
            margin-top: 60px;

            @media (max-width: 767px) {
                margin-top: 44px;
            }
        }

        homo-header {
            position: fixed;
            top: 0;
            display: block;
            background: #7a6544;
            font-family: Helvetica, "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ Pro W3", Meiryo, sans-serif;
            font-size: 14px;
            width: 100%;

            nav {
                margin: 0 auto;

                ul {
                    margin: 0;
                    padding: 0;
                }

                li {
                    float: left;
                    list-style: none;

                    a {
                        text-decoration: none;
                        display: inline-block;
                        padding: 20px 30px;
                        line-height: 20px;
                        box-sizing: border-box;
                        min-height: 60px;
                        color: white;
                        transition: background-color .3s;

                        &:hover {
                            background-color: #5c4c33;
                        }
                    }

                    &.active > a {
                        background: #54452f;
                    }

                    .icon-mars-double {
                        width: 20px;
                    }

                    .loading {
                        -webkit-animation: blink .8s infinite linear;
                        animation: blink .8s infinite linear;
                    }
                }

                &:after {
                    display: block;
                    content: "";
                    clear: both;
                }

                @media (max-width: 767px) {
                    li a {
                        min-height: 0;
                        padding: 12px;

                        &:first-child {
                            padding: 12px 16px 12px 22px;
                        }
                    }
                }
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
        }
    </style>
</homo-header>
