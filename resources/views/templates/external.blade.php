<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS e css para estilizacao -->
    <script src="/asset/js/jquery-3.4.1.js"></script>
    <link rel="stylesheet" type="text/css" href="/asset/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/asset/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/asset/fontawesome/css/all.css">
    <link rel="stylesheet" type="text/css" href="/asset/css/main.css">
    <script src="/asset/js/sweetalert.js"></script>
    <script src="/asset/js/axios.min.js"></script>
    <title>Suporte DataSafer</title>
  </head>
  <body>
    <!-- Header -->
    <nav id="top" class="navbar navbar-expand-lg navbar-light">
      <a class="navbar-brand" href="/tickets"><img src="/asset/images/Logo_DataSafer.png" width="300em" alt="DATASAFER LOGO"/></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </nav>
    <!-- END OF HEADER -->
    <!-- CONTENT -->
    <div class="col-md-12">
        <hr>
      @yield('content')
    </div>
    <!-- END OF CONTENT -->
    <!-- jquery e js para bootstrap -->
    <script src="/asset/js/popper.js"></script>
    <script src="/asset/js/bootstrap.min.js"></script>
    <script src="/asset/js/jquery-datatable.js"></script>
    <script src="/asset/js/datatable.bootstrap4.min.js"></script>
  </body>
</html>