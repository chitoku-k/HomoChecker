<homo-header>
    <nav>
        <ul>
            <li class="active"><a><homo-anime></homo-anime> HOMO CHECKER</a>
            <li><a href="https://twitter.com/intent/tweet?text=%40java_shit+%E4%BF%BA%E3%82%82%E3%83%9B%E3%83%A2%E3%81%A0%EF%BC%81URL%3A+http%3A%2F%2F" target="_blank">REGISTER</a>
            <li><a href="https://github.com/chitoku-k/HomoChecker" target="_blank">SOURCE CODE</a>
        </ul>
    </nav>
    <style type="text/scss">
        homo-header {
            position: fixed;
            top: 0;
            display: block;
            background: #7a6544;
            font-size: 14px;
            width: 100%;
            z-index: 1;

            nav {
                margin: 0 auto;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;

                ul {
                    margin: 0;
                    padding: 0;

                    &:after {
                        display: block;
                        content: "";
                        clear: both;
                    }
                }

                li {
                    display: table-cell;
                    list-style: none;
                    white-space: nowrap;

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

                    .fa-mars-double {
                        width: 20px;
                    }

                    .loading {
                        animation: blink .8s infinite linear;
                    }
                }

                @media (max-width: 767px) {
                    li {
                        a {
                            min-height: 0;
                            padding: 12px 14px;

                            homo-anime {
                                position: relative;
                                top: -3px;
                            }
                        }

                        &:first-child a {
                            padding: 12px 14px 12px 22px;
                        }
                    }
                }
                @media (min-width: 768px) {
                    width: 700px;
                }
                @media (min-width: 992px) {
                    width: 930px;
                }
                @media (min-width: 1200px) {
                    width: 1130px;
                }
            }
        }
    </style>
</homo-header>
