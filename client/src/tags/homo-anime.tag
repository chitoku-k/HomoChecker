<homo-anime>
    <i class="icon-mars part-passive"></i>
    <i class="icon-mars part-active"></i>
    <style type="text/scss">
        homo-anime {
            display: inline-block;
            margin-right: -15px;
            margin-left: -3px;

            @keyframes pako {
                0% {
                    transform: translate(-5px, 4px);
                }

                100% {
                    transform: translate(3px, -4px);
                }
            }

            .icon-mars {
                color: white;
                display: inline-block;
            }

            .part-active {
                position: relative;
                top: 0.55em;
                left: -2.2em;
                animation: pako .3s infinite alternate;
            }
        }
    </style>
</homo-anime>
