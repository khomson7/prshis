    <?php
        const VERSION = 20;
    ?>

    <!-- <link href="picture/favicon/icon10.svg" rel="shortcut icon" type="image/svg+xml" /> -->
    <!-- <link href="picture/favicon/icon2.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" /> -->
    <link href="picture/favicon/icon17.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
    <!-- <link href="picture/favicon/icon12.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" /> -->
    <!-- <link href="picture/favicon/icon6.png" rel="shortcut icon" type="image/png" /> -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css?v=<?=VERSION?>">
    <link rel="stylesheet" href="project/themestr.app/theme_1574508549375_edited.css?v=<?=VERSION?>">


    <link href=".\vendor\select2\select2\dist\css\select2.min.css?v=<?=VERSION?>" rel="stylesheet" />
    <link href=".\node_modules\@ttskch\select2-bootstrap4-theme\dist\select2-bootstrap4.min.css?v=<?=VERSION?>" rel="stylesheet" />
    <link rel="stylesheet" href=".\vendor\fortawesome\font-awesome\css\all.min.css?v=<?=VERSION?>"><!--  ICON -->
    <link rel="stylesheet" href=".\project\project.css?v=<?=VERSION?>">

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="vendor/components/jquery/jquery.min.js?v=<?=VERSION?>"></script>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js?v=<?=VERSION?>"></script>
    <script src="vendor/select2/select2/dist/js/select2.min.js?v=<?=VERSION?>"></script>
    <!-- <script src="vendor/fortawesome/font-awesome/js/all.min.js?v=<?=VERSION?>"></script> -->

    <script type="text/javascript" src=".\vendor\datatables\datatables\media\js\jquery.dataTables.min.js?v=<?=VERSION?>"></script>
    <link rel="stylesheet" type="text/css" href=".\vendor\datatables\datatables\media\css\dataTables.bootstrap4.min.css?v=<?=VERSION?>"/>
    <script type="text/javascript" src=".\vendor\datatables\datatables\media\js\dataTables.bootstrap4.min.js?v=<?=VERSION?>"></script>

    <script src="vendor/moment/moment/min/moment-with-locales.min.js?v=<?=VERSION?>"></script>
    <script src="vendor/nnnick/chartjs/dist/Chart.min.js?v=<?=VERSION?>"></script>
    <script src="vendor/nnnick/chartjs/samples/utils.js?v=<?=VERSION?>"></script>
    <script src="node_modules/chartjs-plugin-annotation/chartjs-plugin-annotation.min.js?v=<?=VERSION?>"></script>
    <script src="node_modules/hammerjs/hammer.min.js?v=<?=VERSION?>"></script>
    <script src="node_modules/chartjs-plugin-zoom/chartjs-plugin-zoom.min.js?v=<?=VERSION?>"></script>

    <script src="node_modules\rtf.js\dist\jquery.svg.min.js?v=<?=VERSION?>"></script>
    <script src="node_modules\rtf.js\dist\jquery.svgfilter.min.js?v=<?=VERSION?>"></script>
    <script src="node_modules\rtf.js\dist\WMFJS.bundle.min.js?v=<?=VERSION?>"></script>
    <script src="node_modules\rtf.js\dist\EMFJS.bundle.min.js?v=<?=VERSION?>"></script>
    <script src="node_modules\rtf.js\dist\RTFJS.bundle.min.js?v=<?=VERSION?>"></script>

    <script src="project/project.js?v=<?=VERSION?>"></script>
    <script src="project/mews-score.js?v=<?=VERSION?>"></script>

    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        $.fn.select2.defaults.set("theme", "bootstrap4");

        /*
        * Hacky fix for a bug in select2 with jQuery 3.6.0's new nested-focus "protection"
        * see: https://github.com/select2/select2/issues/5993
        * see: https://github.com/jquery/jquery/issues/4382
        *
        * TODO: Recheck with the select2 GH issue and remove once this is fixed on their side
        */

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        session_keep_alive();

        const KPHIS_HOSPITAL_NAME = <?=json_encode(KphisConstant::KPHIS_HOSPITAL_NAME, JSON_UNESCAPED_UNICODE)?>;
        const KPHIS_HOSPITAL_SHORT_NAME = <?=json_encode(KphisConstant::KPHIS_HOSPITAL_SHORT_NAME, JSON_UNESCAPED_UNICODE)?>;
        const DRUG_NOTIFY_USE = <?=json_encode(KphisConstant::DRUG_NOTIFY_USE, JSON_UNESCAPED_UNICODE)?>;
        const DRUG_NOTIFY_START_END_MARKER_USE = <?=json_encode(KphisConstant::DRUG_NOTIFY_START_END_MARKER_USE, JSON_UNESCAPED_UNICODE)?>;
        const DRUG_NOTIFY_START_MARKER = <?=json_encode(KphisConstant::DRUG_NOTIFY_START_MARKER, JSON_UNESCAPED_UNICODE)?>;
        const DRUG_NOTIFY_END_MARKER = <?=json_encode(KphisConstant::DRUG_NOTIFY_END_MARKER, JSON_UNESCAPED_UNICODE)?>;
    </script>