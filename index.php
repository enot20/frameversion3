<?php
/**
 * FRAME - Agencia de Diseño y Arquitectura de Software
 * Landing Page - Optimizada para escalar
 * 
 * Seguridad implementada:
 * - Headers de protección XSS, Clickjacking, MIME sniffing
 * - Content Security Policy (CSP)
 * - Prepared statements listos para MariaDB (futuro)
 */

// Prevenir cache en páginas sensibles
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Headers de Seguridad XSS y MIME
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Permitted-Cross-Domain-Policies: master-only");

$csp = "default-src 'self'; "
     . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com; "
     . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com; "
     . "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://use.fontawesome.com data:; "
     . "img-src 'self' data: blob:; "
     . "connect-src 'self' https://api.web3forms.com https://wa.me; "
     . "frame-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self' https://wa.me mailto:;";

header("Content-Security-Policy: $csp");

// Referrer Policy - Control de información en referencias
header("Referrer-Policy: strict-origin-when-cross-origin");

// Referrer Policy
header("Referrer-Policy: strict-origin-when-cross-origin");

// Permissions Policy (SIN SALTOS DE LÍNEA)
header("Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()");
// Prevenir MIME type sniffing
header("X-Content-Type-Options: nosniff");

// HSTS - Forzar HTTPS (descomentar cuando tengas SSL activo)
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

// Prevenir clickjacking
header("X-Frame-Options: SAMEORIGIN");

// Configuración de sesión segura (para futuro login)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Descomentar con HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

// Generar token CSRF para formularios futuros
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Funciones de Seguridad para futuro desarrollo
 * Usar cuando conectes a MariaDB
 */

/**
 * Validar token CSRF
 */
function validarCSRF(string $token): bool {
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitizar input para XSS
 */
function sanitizarInput(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email con filtro PHP
 */
function validarEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Prepared statement helper para MariaDB (usar con PDO)
 */
function crearConexionSegura(string $host, string $dbname, string $user, string $pass): PDO {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    return new PDO($dsn, $user, $pass, $opciones);
}

/**
 * Rate limiting simple por IP
 */
function verificarRateLimit(string $ip, int $maxRequests = 10, int $windowSeconds = 60): bool {
    $key = "rate_limit_" . md5($ip);
    $ahora = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'start' => $ahora];
        return true;
    }
    
    $data = $_SESSION[$key];
    
    // Reset si pasó la ventana de tiempo
    if (($ahora - $data['start']) > $windowSeconds) {
        $_SESSION[$key] = ['count' => 1, 'start' => $ahora];
        return true;
    }
    
    // Verificar límite
    if ($data['count'] >= $maxRequests) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}

// Regenerar CSRF token después de uso exitoso (en proceso de formulario)
function regenerarCSRF(): void {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="FRAME - Agencia de Diseño y Arquitectura de Software especializada en Marketing Digital" />
    <meta name="author" content="FRAME" />
    <title>FRAME - Agencia de Diseño y Arquitectura de Software</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- AOS - Animate On Scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- Particles.js -->
    <style>
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        .masthead, .about-section, .signup-section, .footer {
            position: relative;
            z-index: 1;
        }
    </style>
    
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body id="page-top">
    <!-- Particles.js -->
    <div id="particles-js"></div>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-custom" id="mainNav">
        <div class="container px-4 px-lg-5">

            <!-- LOGO -->
            <a class="navbar-brand d-flex align-items-center" href="#page-top">
                <i class="fas fa-code me-2"></i>
                <span class="fw-bold">FRAME</span>
            </a>

            <!-- BOTON MOBILE -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
                <i class="fas fa-bars"></i>
            </button>

            <!-- MENU -->
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item"><a class="nav-link" href="#nosotros">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#proyectos">Proyectos</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-gradient" href="#contacto">Contacto</a></li>
                </ul>
            </div>
        </div>
    </nav>

        <!-- Masthead -->
        <header class="masthead">
            <div class="container px-4 px-lg-5 h-100">
                <div class="row align-items-center h-100">

                    <!-- TEXTO IZQUIERDA -->
                    <div class="col-lg-6 text-center text-lg-start" data-aos="fade-right">
                        <h1 class="mx-auto my-0 text-uppercase">FRAME</h1>
        <p class="hero-extra">
            Impulsamos tu marca con soluciones digitales innovadoras.
        </p>
                    </div>

                    <!-- IMAGEN DERECHA -->
                    <div class="col-lg-6 text-center" data-aos="fade-left">
                        <img src="assets/img/marketing.jpeg" class="img-fluid hero-img" alt="Imagen">
                    </div>

                </div>
            </div>
        </header>

    <!-- About -->
    <section class="about-section text-center" id="nosotros">
        <div class="container px-4 px-lg-5">

            <!-- TEXTO PRINCIPAL -->
            <div class="row justify-content-center">
                <div class="col-lg-9 text-center" data-aos="fade-up" data-aos-duration="1000">

                    <h2 class="clean-title mb-4">
                        TRANSFORMAMOS IDEAS EN SOLUCIONES DIGITALES
                    </h2>

                    <p class="clean-text">
                        En FRAME somos una agencia especializada en el diseño y desarrollo de arquitecturas de software personalizadas. 
                        Combinamos creatividad, tecnología de punta y estrategias de marketing digital para crear soluciones que impulsan tu negocio al siguiente nivel.
                    </p>

                </div>
            </div>

            <!-- CARDS -->
            <div class="row mt-5">

                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card custom-card h-100">
                        <div class="card-body">
                            <h4 class="fw-bold">Misión</h4>
                            <p>
                                Desarrollar soluciones digitales innovadoras y personalizadas que ayuden a nuestros clientes a optimizar sus procesos, 
                                mejorar su presencia digital y alcanzar sus objetivos de negocio.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card custom-card h-100">
                        <div class="card-body">
                            <h4 class="fw-bold">Visión</h4>
                            <p>
                                Ser una empresa líder en innovación tecnológica a nivel regional, reconocida por la calidad de nuestras soluciones 
                                y por impulsar la transformación digital de empresas en distintos sectores.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card custom-card h-100">
                        <div class="card-body">
                            <h4 class="fw-bold">Valores</h4>
                            <ul class="text-start ps-3">
                                <li>Innovación constante</li>
                                <li>Compromiso con la calidad</li>
                                <li>Trabajo en equipo</li>
                                <li>Transparencia</li>
                                <li>Orientación al cliente</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

                <!-- EQUIPO -->
                <div class="row mt-5 text-center">

                    <h2 class="section-subtitle mb-5" data-aos="zoom-in">Somos el motor de tu crecimiento</h2>

                    <div class="col-md-4 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="team-card modern-card">
                            <div class="team-img-box">
                                <img src="assets/img/katy.jpeg" class="team-img" alt="team">
                            </div>
                            <div class="team-info">
                                <h5 class="team-name">
                                    Katherine <span>Hernández</span>
                                </h5>
                                <p class="team-role">Estratega de Social Media y Leads</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="team-card modern-card">
                            <div class="team-img-box">
                                <img src="assets/img/marlon1.png" class="team-img" alt="team">
                            </div>
                            <div class="team-info">
                                <h5 class="team-name">
                                    Marlon <span>Martínez</span>
                                </h5>
                                <p class="team-role">Analista de Comunicación y Marca</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="team-card modern-card">
                            <div class="team-img-box">
                                <img src="assets/img/keli2.png" class="team-img" alt="team">
                            </div>
                            <div class="team-info">
                                <h5 class="team-name">
                                    Elisa <span>Jiménez</span>
                                </h5>
                                <p class="team-role">Gestor de WhatsApp</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="team-card modern-card">
                            <div class="team-img-box">
                                <img src="assets/img/dani.png" class="team-img" alt="team">
                            </div>
                            <div class="team-info">
                                <h5 class="team-name">
                                    Daniel <span>Trujillo</span>
                                </h5>
                                <p class="team-role">Coordinador y Analista de Datos</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="500">
                        <div class="team-card modern-card">
                            <div class="team-img-box">
                                <img src="assets/img/elias.png" class="team-img" alt="team">
                            </div>
                            <div class="team-info">
                                <h5 class="team-name">
                                    Elías <span>Carreño</span>
                                </h5>
                                <p class="team-role">Analista de Atribución y Tracking</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="600">
                        <div class="team-card modern-card">
                            <div class="team-img-box">
                                <img src="assets/img/edin.jpeg" class="team-img" alt="team">
                            </div>
                            <div class="team-info">
                                <h5 class="team-name">
                                    Edin <span>Ayala</span>
                                </h5>
                                <p class="team-role">Especialista en CRO y UX</p>
                            </div>
                        </div>
                    </div>

                </div>

    </section>

    <!-- Services -->
    <section class="services-section bg-light" id="servicios">
        <div class="container px-4 px-lg-5">
            <h2 class="text-center mb-5 text-uppercase" data-aos="fade-up">Nuestros Servicios</h2>
            <div class="row gx-4 gx-lg-5">
                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-palette fa-3x text-primary mb-3"></i>
                            <h5>Diseño UI/UX</h5>
                            <p class="text-muted">Creamos interfaces intuitivas y experiencias de usuario memorables</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-code fa-3x text-primary mb-3"></i>
                            <h5>Desarrollo Web</h5>
                            <p class="text-muted">Aplicaciones web modernas, escalables y de alto rendimiento</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                            <h5>Marketing Digital</h5>
                            <p class="text-muted">Estrategias SEO, SEM y redes sociales para maximizar tu alcance</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                            <h5>Apps Móviles</h5>
                            <p class="text-muted">Desarrollo de aplicaciones nativas y multiplataforma</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

        <!-- Projects -->
        <section class="projects-section" id="proyectos">
            <div class="container px-4 px-lg-5">
                <h2 class="text-center mb-5 text-uppercase" data-aos="fade-up">Nuestros Proyectos</h2>
                <div class="row gx-0 mb-4 mb-lg-5 align-items-center" data-aos="fade-right">
                    <div class="col-xl-8 col-lg-7">
                        <img class="img-fluid mb-3 mb-lg-0" src="assets/img/ecomers.webp" alt="Proyecto de E-commerce" />
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="featured-text text-center text-lg-left">
                            <h4>Tiendas Online</h4>
                            <p class="text-black-50 mb-0">
                                Desarrollamos plataformas de comercio electrónico con pasarelas de pago integradas, gestión de inventario y analíticas avanzadas para impulsar tus ventas.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row gx-0 mb-5 mb-lg-0 justify-content-center" data-aos="fade-left">
                    <div class="col-lg-6">
                        <img class="img-fluid" src="assets/img/dash.webp" alt="Proyecto de Dashboard" />
                    </div>
                    <div class="col-lg-6">
                        <div class="project-overlay text-center h-100 project">
                            <div class="d-flex h-100">
                                <div class="project-text w-100 my-auto text-center text-lg-left">
                                    <h4 class="text-white">Dashboards Analíticos</h4>
                                    <p class="mb-0 text-white-50">
                                        Visualiza y analiza datos en tiempo real con tableros interactivos personalizados para tu negocio.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row gx-0 justify-content-center" data-aos="fade-right">
                    <div class="col-lg-6">
                        <img class="img-fluid" src="assets/img/portal.webp" alt="Proyecto de Plataformas Educativas" />
                    </div>
                    <div class="col-lg-6 order-lg-first">
                        <div class="project-overlay text-center h-100 project">
                            <div class="d-flex h-100">
                                <div class="project-text w-100 my-auto text-center text-lg-right">
                                    <h4 class="text-white">Plataformas Educativas</h4>
                                    <p class="mb-0 text-white-50">
                                        Creamos plataformas de e-learning personalizadas que transforman la educación digital. Integración de contenido interactivo, seguimiento de progreso y estrategias de marketing educativo.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section py-5" data-aos="zoom-in">
            <div class="container px-4 px-lg-5 text-center">
                <h2 class="text-white mb-4">¿Listo para impulsar tu negocio?</h2>
                <a class="btn btn-lg btn-outline-light" href="#contacto" onclick="mostrarCTA(); return false;">Contacta con FRAME</a>
            </div>
        </section>

    <!-- Signup -->
    <section class="signup-section" id="signup">
        <div class="container px-4 px-lg-5">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 text-center signup-card" data-aos="fade-up">

                    <i class="far fa-envelope fa-2x mb-3 text-orange icon-mail"></i>

                    <h2 class="signup-title mb-3">
                        Mantente actualizado con nuestras novedades
                    </h2>

                    <p class="signup-text mb-4">
                        Suscríbete para recibir las últimas noticias sobre marketing digital y tecnología
                    </p>

                    <form id="contactForm" onsubmit="enviarFormulario(event)">

                        <!-- Honeypot -->
                        <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">

                        <!-- CSRF -->
                        <input type="hidden" name="csrf_token" id="csrfToken" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="row g-2 justify-content-center">

                            <div class="col-md-8">
                                <input class="form-control input-modern" 
                                    id="emailAddress" 
                                    name="email" 
                                    type="email" 
                                    placeholder="Tu correo electrónico..." 
                                    required />
                            </div>

                            <div class="col-md-4">
                                <button class="btn btn-modern w-100" id="submitButton" type="submit">
                                    Suscribirme
                                </button>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section class="contact-section py-5" id="contacto">
        <div class="container px-4 px-lg-5">
            <h2 class="text-center mb-5 mt-3 text-uppercase" data-aos="fade-up">Contáctanos</h2>
            <div id="contactCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="card mx-auto" style="max-width: 400px;">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                                <h4 class="text-uppercase mb-3">Ubicación</h4>
                                <hr style="width: 60px; border-width: 3px; border-color: #fd8e18;" />
                                <p class="text-muted mb-0">San Salvador, El Salvador</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="card mx-auto" style="max-width: 400px;">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                <h4 class="text-uppercase mb-3">Email</h4>
                                <hr style="width: 60px; border-width: 3px; border-color: #fd8e18;" />
                                <p class="text-muted mb-0"><a href="mailto:hola@frame.sv">hola@frame.sv</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="card mx-auto" style="max-width: 400px;">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                                <h4 class="text-uppercase mb-3">Teléfono</h4>
                                <hr style="width: 60px; border-width: 3px; border-color: #fd8e18;" />
                                <p class="text-muted mb-0">+503 2121-0000</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="card mx-auto" style="max-width: 400px;">
                            <div class="card-body text-center py-5">
                                <i class="fab fa-whatsapp fa-3x text-primary mb-3"></i>
                                <h4 class="text-uppercase mb-3">WhatsApp</h4>
                                <hr style="width: 60px; border-width: 3px; border-color: #fd8e18;" />
                                <p class="text-muted mb-0"><a href="https://wa.me/50370101010">+503 7010-1010</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="card mx-auto" style="max-width: 400px;">
                            <div class="card-body text-center py-5">
                                <i class="fab fa-tiktok fa-3x text-primary mb-3"></i>
                                <h4 class="text-uppercase mb-3">TikTok</h4>
                                <hr style="width: 60px; border-width: 3px; border-color: #fd8e18;" />
                                <p class="text-muted mb-0"><a href="https://tiktok.com/@frame.sv">@frame.sv</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#contactCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#contactCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
            <div class="contact-divider my-5"></div>
            <div class="text-center">
                <h4 class="text-uppercase mb-3">Redes Sociales</h4>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a class="btn btn-lg btn-social btn-whatsapp" href="https://wa.me/50370101010" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <a class="btn btn-lg btn-social btn-instagram" href="https://instagram.com/frame.sv" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a class="btn btn-lg btn-social btn-facebook" href="https://facebook.com/frame.sv" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-lg btn-social btn-tiktok" href="https://tiktok.com/@frame.sv" target="_blank"><i class="fab fa-tiktok"></i></a>
                    <a class="btn btn-lg btn-social btn-email" href="mailto:hola@frame.sv"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <span class="footer-brand"><i class="fas fa-code"></i> FRAME</span>
                    <p class="footer-tagline">
                        Somos una agencia de diseño y arquitectura de software especializada en marketing digital en El Salvador. Transformamos tus ideas en soluciones tecnológicas que impulsan tu negocio al siguiente nivel.
                    </p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Servicios</h5>
                    <ul class="footer-links">
                        <li><a href="#servicios">Diseño UI/UX</a></li>
                        <li><a href="#servicios">Desarrollo Web</a></li>
                        <li><a href="#servicios">Marketing Digital</a></li>
                        <li><a href="#servicios">Apps Móviles</a></li>
                        <li><a href="#servicios">E-commerce</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
                    <h5>Contacto</h5>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i><span>San Salvador, El Salvador</span></li>
                        <li><i class="fas fa-phone"></i><a href="tel:+50321210000">+503 2121-0000</a></li>
                        <li><i class="fas fa-envelope"></i><a href="mailto:hola@frame.sv">hola@frame.sv</a></li>
                        <li><i class="fab fa-whatsapp"></i><a href="https://wa.me/50370101010">+503 7010-1010</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5>Síguenos</h5>
                    <p class="text-white-50 mb-3">Conéctate con nosotros en redes sociales</p>
                    <div class="social-icons">
                        <a href="https://wa.me/50370101010" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://instagram.com/frame.sv" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://facebook.com/frame.sv" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://tiktok.com/@frame.sv" target="_blank" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                        <a href="mailto:hola@frame.sv" aria-label="Email"><i class="fas fa-envelope"></i></a>
                        <a href="https://linkedin.com/company/frame-sv" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p>Copyright &copy; 2026 FRAME El Salvador - Agencia de Diseño y Arquitectura de Software. Todos los derechos reservados.</p>
                    </div>
                    <div class="col-md-6">
                        <div class="footer-legal text-md-end">
                            <a href="#!">Política de Privacidad</a>
                            <a href="#!">Términos de Servicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS - Animate On Scroll -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/scripts.js"></script>
    <script src="js/custom.js"></script>
    
    <!-- Actualizar CSRF Token en JS -->
    <script>
        // Actualizar token CSRF antes de enviar
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            // El token ya está en el input hidden
        });
    </script>
</body>
</html>

<?php
/**
 * =============================================
 * EJEMPLO: Procesar Formulario Seguro (FUTURO)
 * =============================================
 * Descomentar cuando conectes a MariaDB
 * Ubicar en un archivo separado: api/suscribir.php
 * 
 * <?php
 * require_once 'config/database.php';
 * 
 * header('Content-Type: application/json');
 * 
 * // Verificar método POST
 * if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 *     http_response_code(405);
 *     echo json_encode(['error' => 'Método no permitido']);
 *     exit;
 * }
 * 
 * // Obtener IP del cliente
 * $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
 * 
 * // Rate limiting
 * if (!verificarRateLimit($ip, 5, 60)) {
 *     http_response_code(429);
 *     echo json_encode(['error' => 'Demasiadas solicitudes']);
 *     exit;
 * }
 * 
 * // Validar CSRF
 * $csrfToken = $_POST['csrf_token'] ?? '';
 * if (!validarCSRF($csrfToken)) {
 *     http_response_code(403);
 *     echo json_encode(['error' => 'Token inválido']);
 *     exit;
 * }
 * 
 * // Honeypot check
 * if (!empty($_POST['website'])) {
 *     // Es un bot, ignorar silenciosamente
 *     echo json_encode(['success' => true]);
 *     exit;
 * }
 * 
 * // Sanitizar y validar email
 * $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
 * if (!validarEmail($email)) {
 *     http_response_code(400);
 *     echo json_encode(['error' => 'Email inválido']);
 *     exit;
 * }
 * 
 * try {
 *     $pdo = crearConexionSegura(DB_HOST, DB_NAME, DB_USER, DB_PASS);
 *     
 *     // Prepared statement para prevenir SQL injection
 *     $stmt = $pdo->prepare(
 *         "INSERT INTO suscriptores (email, ip, fecha) VALUES (?, ?, NOW())"
 *     );
 *     $stmt->execute([$email, $ip]);
 *     
 *     regenerarCSRF();
 *     echo json_encode(['success' => true]);
 * } catch (PDOException $e) {
 *     // Log error sin exponer detalles
 *     error_log("DB Error: " . $e->getMessage());
 *     http_response_code(500);
 *     echo json_encode(['error' => 'Error interno']);
 * }
 */
?>
