<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo ?? 'Sagras Restaurant') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sagras-orange: #ff6b35; --sagras-green: #1b5e20; --sagras-cream: #fdf6e3; }
        body { background-color: var(--sagras-cream); }
        .navbar-sagras { background-color: var(--sagras-orange); }
        .btn-sagras { background-color: var(--sagras-orange); color: white; }
        .btn-sagras:hover { background-color: #e55a2b; color: white; }
        .card-mesa-libre { border-left: 5px solid var(--sagras-green); }
        .card-mesa-ocupada { border-left: 5px solid #c62828; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-sagras mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/pedidos">Sagras Restaurant</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/pedidos">Pedidos</a></li>
        <li class="nav-item"><a class="nav-link" href="/pedidos/nuevo">Nuevo Pedido</a></li>
        <li class="nav-item"><a class="nav-link" href="/productos">Productos</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
