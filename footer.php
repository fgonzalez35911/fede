</div> <section id="contact" class="bg-accent-blue-strong py-5">
    <div class="container text-center text-white">
        <h2 class="display-5 mb-4 text-white" data-aos="fade-up">¿Listo para colaborar?</h2>
        <p class="lead text-white mb-5" data-aos="fade-up" data-aos-delay="100">Hablemos de tu proyecto o de mi próximo libro. Envíame un mensaje.</p>
        
        <div id="form-message-wrapper" class="mx-auto mb-3" style="max-width: 600px;"></div>
        
        <form id="contactForm" class="mx-auto" style="max-width: 600px;" data-aos="fade-up" data-aos-delay="200">
            <div class="mb-3 text-white">
                <input type="text" id="name" name="name" class="form-control" placeholder="Tu Nombre" required>
            </div>
            <div class="mb-3">
                <input type="email" id="email" name="email" class="form-control" placeholder="Tu Email" required>
            </div>
            <div class="mb-3">
                <textarea id="message" name="message" class="form-control" rows="4" placeholder="Tu Mensaje" required></textarea>
            </div>

            <div class="honeypot-field" aria-hidden="true">
                <label for="website_url">No llenar este campo</label>
                <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
            </div>
            <button type="submit" id="submitButton" class="btn btn-lg btn-accent-blue w-100">
                <span id="submitButtonText">Enviar Mensaje</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</section>


        <footer class="footer bg-black py-4">
    <div class="container text-center">
        <div class="container text-center">
        
        <div class="prensa-link mb-3">
             <a href="prensa.php" class="text-white">Kit de Prensa / Biografía Completa</a>
        </div>
    </div>
        <div class="social-links mb-3">
            <a href="#" class="text-white mx-3" target="_blank" aria-label="Facebook"><i class="fab fa-facebook fa-2x"></i></a>
            <a href="https://www.instagram.com/fedegonzalez.escritor/" class="text-white mx-3" target="_blank" aria-label="Instagram"><i class="fab fa-instagram fa-2x"></i></a>
            <a href="#" class="text-white mx-3" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin fa-2x"></i></a>
            <a href="https://wa.me/5491166116861" class="text-white mx-3" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp fa-2x"></i></a>
            <a href="#" class="text-white mx-3" target="_blank" aria-label="YouTube"><i class="fab fa-youtube fa-2x"></i></a>
            <a href="https://open.spotify.com/show/0DaNZpCXuYGcR9VvT84Nq6?si=9aad6b9b2c8c401b" class="text-white mx-3" target="_blank" aria-label="Spotify"><i class="fab fa-spotify fa-2x"></i></a>
            <a href="mailto:info@federicogonzalez.net" class="text-white mx-3" aria-label="Gmail"><i class="fas fa-envelope fa-2x"></i></a>
        </div>
        
        <div class="text-white-50 mb-2">
            <i class="fas fa-eye text-accent-blue me-2"></i>Visitas Únicas: 
            <span class="visitor-count"><?php include 'counter.php'; ?></span>
        </div>
        
        <p class="text-white mb-0">© <?php echo date("Y"); ?> Federico Gonzalez. Desarrollado con ❤️ y Código.</p>
    </div>

</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>

<script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>

<script>
    // 1. Inicializar AOS (para el resto de la página)
    AOS.init({
        duration: 800, 
        once: true 
    });

    // 2. Inicializar Typed.js para el Hero
    var options = {
        strings: [
            '<span class="text-accent-blue">Constructor</span> de sistemas',
            '<span class="text-accent-orange">Escritor</span> de latidos'
        ],
        typeSpeed: 50,
        backSpeed: 25,      // Re-activado para el efecto de borrado
        backDelay: 1500,  // Pausa antes de borrar
        loop: true,         // Re-activado para el bucle
        smartBackspace: true, 
        showCursor: true,   
        cursorChar: '|'
    };
    var typed = new Typed('#typed-hero', options);
</script>

<script src="assets/js/main.js"></script>
<a href="https://wa.me/5491166116861" class="whatsapp-float-btn" target="_blank" aria-label="Contactar por WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
</body>
</html>