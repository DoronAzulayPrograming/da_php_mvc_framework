<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>דוד צור</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-aFq/bzH65dt+w6FI2ooMVUpc+21e0SRygnTpmBvdBgSdnuTN7QbdgL+OapgHtvPp" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= _Frm_core\Application::$app->basePath;?>/css/bootstrap.min.css" />

    <style>
        tr.border-top{
            border-width: 1px !important;
        }
    </style>
</head>

<body dir="rtl" lang="he">

    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 shadow">
        <div class="container">
            <?php echo '<a class="navbar-brand" href="/">דוד צור</a>' ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <?php echo '<a class="nav-link active" aria-current="page" href="/">בית</a>' ?>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=_Frm_core\Application::$app->basePath.'/products' ?>">מוצרים</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=_Frm_core\Application::$app->basePath.'/orders' ?>">הזמנות</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        {{content}}
    </main>
    <!-- JS dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.min.js" integrity="sha384-heAjqF+bCxXpCWLa6Zhcp4fu20XoNIA98ecBC1YkdXhszjoejr5y9Q77hIrv8R9i" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js" integrity="sha512-oVbWSv2O4y1UzvExJMHaHcaib4wsBMS5tEP3/YkMP6GmkwRJAa79Jwsv+Y/w7w2Vb/98/Xhvck10LyJweB8Jsw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    {{scripts}}
</body>

</html>