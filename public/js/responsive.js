// JavaScript para el menú responsive
document.addEventListener('DOMContentLoaded', function() {
    // Crear el botón hamburguesa si no existe
    const nav = document.querySelector('.nav');
    const navMenu = document.querySelector('.nav-menu');
    const searchBox = document.querySelector('.search-box');
    const authButtons = document.querySelectorAll('.a2');
    
    // Crear wrapper si no existe
    let navWrapper = document.querySelector('.nav-wrapper');
    if (!navWrapper) {
        navWrapper = document.createElement('div');
        navWrapper.className = 'nav-wrapper';
        navWrapper.id = 'navWrapper';
        
        // Mover elementos al wrapper
        if (navMenu) navWrapper.appendChild(navMenu);
        if (searchBox) navWrapper.appendChild(searchBox);
        
        // Crear contenedor para botones de auth
        const authContainer = document.createElement('div');
        authContainer.className = 'auth-buttons';
        authButtons.forEach(btn => {
            authContainer.appendChild(btn.cloneNode(true));
            btn.remove();
        });
        navWrapper.appendChild(authContainer);
        
        nav.appendChild(navWrapper);
    }
    
    // Crear botón hamburguesa si no existe
    let hamburger = document.querySelector('.hamburger');
    if (!hamburger) {
        hamburger = document.createElement('button');
        hamburger.className = 'hamburger';
        hamburger.id = 'hamburger';
        hamburger.innerHTML = '<span></span><span></span><span></span>';
        nav.appendChild(hamburger);
    }
    
    // Crear overlay
    let overlay = document.querySelector('.nav-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'nav-overlay';
        document.body.appendChild(overlay);
    }

    // Toggle menú móvil
    hamburger.addEventListener('click', function() {
        this.classList.toggle('active');
        navWrapper.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = navWrapper.classList.contains('active') ? 'hidden' : '';
    });

    // Cerrar menú al hacer click en overlay
    overlay.addEventListener('click', function() {
        hamburger.classList.remove('active');
        navWrapper.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    });

    // Dropdown en móvil
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('a');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth <= 991) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        }
    });

    // Cerrar menú al hacer click en un enlace (móvil)
    const navLinks = document.querySelectorAll('.nav-menu a:not(.dropdown > a)');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 991) {
                hamburger.classList.remove('active');
                navWrapper.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Cerrar dropdowns y menú al cambiar tamaño de ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991) {
            hamburger.classList.remove('active');
            navWrapper.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            dropdowns.forEach(dropdown => dropdown.classList.remove('active'));
        }
    });
});