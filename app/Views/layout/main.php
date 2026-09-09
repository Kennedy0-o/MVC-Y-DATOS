<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo ?? 'Sagras Restaurant') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Karla:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:        #2A2118;
            --paprika:    #B33A1B;
            --paprika-dk: #8F2E15;
            --gold:       #C98A2C;
            --sage:       #4F6D4F;
            --sage-dk:    #3B5339;
            --bone:       #F3EEE4;
            --panel:      #FFFFFF;
            --line:       #E4DCCB;
        }

        body {
            background-color: var(--bone);
            color: var(--ink);
            font-family: 'Karla', -apple-system, sans-serif;
        }
        h1, h2, h3, h4, .brand-mark {
            font-family: 'Fraunces', Georgia, serif;
            color: var(--ink);
            letter-spacing: -0.01em;
        }
        h2 { font-weight: 600; font-size: 1.9rem; margin-bottom: 1.25rem; }

        /* Navbar */
        .navbar-sagras {
            background-color: var(--ink);
            border-bottom: 3px solid var(--paprika);
        }
        .navbar-sagras .brand-mark {
            color: #fff;
            font-size: 1.35rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-sagras .brand-mark::before {
            content: "";
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--paprika);
            display: inline-block;
        }
        .navbar-sagras .nav-link {
            color: #E9E2D3 !important;
            font-weight: 500;
            padding: 0.5rem 0.9rem !important;
            border-radius: 6px;
            transition: background-color .15s ease, color .15s ease;
        }
        .navbar-sagras .nav-link:hover { background-color: rgba(255,255,255,0.08); color: #fff !important; }
        .navbar-sagras .nav-link.active-link { color: #fff !important; background-color: var(--paprika); }

        /* Cards / panels */
        .card {
            border: 1px solid var(--line);
            border-radius: 10px;
            box-shadow: none;
        }
        .card-header {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            background-color: var(--panel) !important;
            border-bottom: 1px solid var(--line);
        }

        /* Table */
        .table thead { background-color: var(--ink); }
        .table thead th {
            color: #EFE8D8;
            font-weight: 500;
            font-family: 'Karla', sans-serif;
            border: none;
            padding: 0.85rem 1rem;
        }
        .table tbody td { padding: 0.85rem 1rem; vertical-align: middle; border-color: var(--line); }
        .table tbody tr:hover { background-color: #FAF6EC; }

        /* Buttons */
        .btn-sagras, .btn-primary {
            background-color: var(--paprika);
            border-color: var(--paprika);
            color: #fff;
            font-weight: 500;
        }
        .btn-sagras:hover, .btn-primary:hover {
            background-color: var(--paprika-dk);
            border-color: var(--paprika-dk);
            color: #fff;
        }
        .btn-outline-primary { color: var(--paprika); border-color: var(--paprika); }
        .btn-outline-primary:hover { background-color: var(--paprika); border-color: var(--paprika); }
        .btn-outline-warning { color: var(--gold); border-color: var(--gold); }
        .btn-outline-warning:hover { background-color: var(--gold); border-color: var(--gold); color: #fff; }
        .btn-warning { background-color: var(--gold); border-color: var(--gold); color: #fff; }
        .btn-warning:hover { background-color: #a86f1f; border-color: #a86f1f; color: #fff; }
        .btn-outline-danger:hover { color: #fff; }
        .btn-outline-secondary { color: var(--ink); border-color: var(--line); }
        .btn-outline-secondary:hover { background-color: var(--ink); border-color: var(--ink); }

        /* Status badges */
        .badge { font-weight: 500; font-family: 'Karla', sans-serif; padding: 0.4em 0.7em; border-radius: 20px; }
        .bg-warning { background-color: #E3A73B !important; color: #3A2A08; }
        .bg-success { background-color: var(--sage) !important; }
        .bg-secondary { background-color: #8C8378 !important; }
        .bg-info { background-color: #4F7A8C !important; }

        /* Alerts */
        .alert-success { background-color: #EAF0E8; border: 1px solid var(--sage); color: var(--sage-dk); }
        .alert-danger  { background-color: #F7E6E1; border: 1px solid var(--paprika); color: var(--paprika-dk); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-sagras mb-4">
    <div class="container py-2">
        <a class="navbar-brand brand-mark" href="<?= base_url('pedidos') ?>">Sagras Restaurant</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('pedidos') ?>">Pedidos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('pedidos/nuevo') ?>">Nuevo Pedido</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('productos') ?>">Productos</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container pb-5">
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
