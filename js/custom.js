/**
 * FRAME - Custom JavaScript
 * Funciones interactivas y configuraciones
 */

// AOS - Animate On Scroll Init
document.addEventListener("DOMContentLoaded", function () {
  AOS.init({
    duration: 800,
    easing: "ease-out",
    once: true,
    offset: 100,
  });
});

// Particles.js Config
particlesJS("particles-js", {
  particles: {
    number: { value: 30, density: { enable: true, value_area: 800 } },
    color: { value: "#fd8e18" },
    shape: { type: "circle" },
    opacity: { value: 0.3, random: true },
    size: { value: 3, random: true },
    line_linked: {
      enable: true,
      distance: 150,
      color: "#f76008",
      opacity: 0.2,
    },
    move: { enable: true, speed: 2 },
  },
  interactivity: {
    events: { onhover: { enable: true, mode: "grab" } },
    modes: { grab: { distance: 140, line_linked: { opacity: 0.5 } } },
  },
  retina_detect: true,
});

// Alerta de Bienvenida
function mostrarBienvenida() {
  Swal.fire({
    title: "¡Bienvenido a FRAME!",
    text: "Somos tu aliado en diseño y arquitectura de software. ¿Listo para comenzar?",
    icon: "success",
    confirmButtonText: "¡Vamos!",
    confirmButtonColor: "#fd8e18",
    background: "#212529",
    color: "#fff",
  });
}

// Alerta CTA
function mostrarCTA() {
  Swal.fire({
    title: "¿Listo para crecer?",
    html: "Contáctanos hoy y transforma tu presencia digital con estrategias de marketing inteligente.",
    icon: "info",
    showCancelButton: true,
    confirmButtonColor: "#fd8e18",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Contactar ahora",
    cancelButtonText: "Más tarde",
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href =
        "mailto:hola@frame.sv?subject=Quiero%20transformar%20mi%20negocio";
    }
  });
}

// Alerta Footer CTA
function mostrarFooterCTA() {
  Swal.fire({
    title: "¡Comencemos tu proyecto!",
    text: "Llena el formulario o contáctanos directamente por WhatsApp.",
    icon: "success",
    showCancelButton: true,
    confirmButtonColor: "#fd8e18",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "WhatsApp",
    cancelButtonText: "Cerrar",
  }).then((result) => {
    if (result.isConfirmed) {
      window.open("https://wa.me/50370101010", "_blank");
    }
  });
}

// Enviar Formulario
function enviarFormulario(e) {
  e.preventDefault();
  const email = document.getElementById("emailAddress").value;

  if (email) {
    Swal.fire({
      title: "¡Suscripción exitosa!",
      text: "Gracias por suscribirte. Pronto recibirás nuestras últimas novedades.",
      icon: "success",
      confirmButtonColor: "#fd8e18",
      background: "#212529",
      color: "#fff",
    });
    document.getElementById("contactForm").reset();
  } else {
    Swal.fire({
      title: "Error",
      text: "Por favor ingresa un correo electrónico válido.",
      icon: "error",
      confirmButtonColor: "#f76008",
      background: "#212529",
      color: "#fff",
    });
  }
}

// Navbar scroll effect con jQuery
$(window).on("scroll", function () {
  if ($(this).scrollTop() > 100) {
    $("#mainNav").addClass("navbar-shrink");
  } else {
    $("#mainNav").removeClass("navbar-shrink");
  }
});

// Smooth scroll
$("a.nav-link").on("click", function (e) {
  const href = $(this).attr("href");
  if (href.startsWith("#")) {
    e.preventDefault();
    $("html, body").animate(
      {
        scrollTop: $(href).offset().top - 70,
      },
      500,
    );
  }
});

// Console branding
console.log(
  "%c🚀 FRAME - Agencia de Software",
  "font-size: 20px; font-weight: bold; color: #fd8e18;",
);
console.log(
  "%cTransformamos ideas en soluciones digitales",
  "font-size: 14px; color: #f76008;",
);
