<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="noindex">

        <title><?= lang('Errors.whoops') ?></title>

        <style>
<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
        </style>
    </head>
    <body>

        <div class="container text-center">

            <h1 class="headline"><?= lang('Errors.whoops') ?></h1>

            <p class="lead"><?= lang('Errors.weHitASnag') ?></p>
            <p class="text-center"><a href="https://risedocs.fairsketch.com/doc/view/59" style="border: 1px solid #222;
                                      border-radius: 5px;
                                      padding: 5px 20px;
                                      text-decoration: none;
                                      color: #222;">Troubleshoot</a></p>

        </div>

    </body>

</html>
