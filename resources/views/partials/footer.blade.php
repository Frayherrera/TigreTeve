{{-- Footer Minimalista --}}
<footer class="footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <h3>TigreTeve</h3>
                <p>Tu fuente confiable de información</p>
            </div>

            <div class="footer-links">
                <a href="#">Política</a>
                <a href="#">Deportes</a>
                <a href="#">Economía</a>
                <a href="#">Tecnología</a>
                <a href="#">Cultura</a>
            </div>

            <div class="footer-social">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-info">
                <span><i class="fas fa-map-marker-alt"></i> Aguachica, Cesar</span>
                <span><i class="fas fa-envelope"></i> info@tigreteve.com</span>
                <span><i class="fas fa-phone"></i> +57 (5) 123-4567</span>
            </div>
            <div class="footer-copy">
                <p>&copy; 2025 TigreTeve. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>

<style>
    /* ================================
   FOOTER MINIMALISTA
   ================================ */

    .footer {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: #ecf0f1;
        padding: 40px 0 20px;
        margin-top: 60px;
    }

    .footer-top {
        display: grid;
        grid-template-columns: 2fr 3fr 1fr;
        gap: 40px;
        padding-bottom: 30px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Brand */
    .footer-brand h3 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: #f5ad1d;
    }

    .footer-brand p {
        color: #bdc3c7;
        font-size: 0.95rem;
    }

    /* Links */
    .footer-links {
        display: flex;
        flex-wrap: wrap;
        gap: 15px 25px;
        align-items: center;
    }

    .footer-links a {
        color: #ecf0f1;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .footer-links a::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 0;
        height: 2px;
        background: #f5ad1d;
        transition: width 0.3s ease;
    }

    .footer-links a:hover {
        color: #f5ad1d;
    }

    .footer-links a:hover::after {
        width: 100%;
    }

    /* Social */
    .footer-social {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        align-items: center;
    }

    .footer-social a {
        width: 38px;
        height: 38px;
        background: rgba(255, 255, 255, 0.1);
        color: #ecf0f1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .footer-social a:hover {
        background: #f5ad1d;
        color: #2c3e50;
        transform: translateY(-3px);
    }

    /* Bottom */
    .footer-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 25px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .footer-info {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
    }

    .footer-info span {
        color: #bdc3c7;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .footer-info i {
        color: #f5ad1d;
        font-size: 0.9rem;
    }

    .footer-copy {
        color: #95a5a6;
        font-size: 0.85rem;
    }

    /* ================================
   RESPONSIVE FOOTER
   ================================ */

    @media (max-width: 991px) {
        .footer-top {
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .footer-social {
            grid-column: 1 / -1;
            justify-content: center;
        }

        .footer-links {
            justify-content: flex-start;
        }
    }

    @media (max-width: 767px) {
        .footer {
            padding: 30px 0 15px;
            margin-top: 40px;
        }

        .footer-top {
            grid-template-columns: 1fr;
            gap: 25px;
            text-align: center;
        }

        .footer-brand h3 {
            font-size: 1.5rem;
        }

        .footer-links {
            justify-content: center;
            gap: 12px 20px;
        }

        .footer-links a {
            font-size: 0.9rem;
        }

        .footer-social {
            justify-content: center;
        }

        .footer-bottom {
            flex-direction: column;
            text-align: center;
            padding-top: 20px;
            gap: 15px;
        }

        .footer-info {
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .footer-info span {
            font-size: 0.8rem;
        }

        .footer-copy {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 575px) {
        .footer-brand h3 {
            font-size: 1.3rem;
        }

        .footer-brand p {
            font-size: 0.85rem;
        }

        .footer-links {
            gap: 10px 15px;
        }

        .footer-links a {
            font-size: 0.85rem;
        }

        .footer-social a {
            width: 35px;
            height: 35px;
            font-size: 0.85rem;
        }

        .footer-info span {
            font-size: 0.75rem;
        }

        .footer-copy {
            font-size: 0.75rem;
        }
    }
</style>
