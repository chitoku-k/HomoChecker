<homo-app>
    <homo-progress progress={ opts.progress }></homo-progress>
    <homo-header></homo-header>
    <homo-content items={ opts.items } progress={ opts.progress }></homo-content>
    <script>
        riot.observable(opts.progress);
    </script>
</homo-app>
