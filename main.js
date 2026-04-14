$(document).ready(function() {
    var $preloader = $('#preloader');
    if ($preloader.length) {
        setTimeout(function() {
            $preloader.addClass('hidden');
        }, 3000); 
        setTimeout(function() {
            $preloader.remove();
        }, 3600); 
    }
    
    var $grid = $('#portfolio-grid').isotope({
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows',
        transitionDuration: '0.6s'
    });

    $('#portfolio-filters .btn-filter').on('click', function() {
        $('#portfolio-filters .btn-filter').removeClass('active');
        $(this).addClass('active');
        var filterValue = $(this).attr('data-filter');
        var selector;
        if (filterValue == '*') {
            selector = '*';
        } else {
            selector = '[data-category="' + filterValue + '"]'; 
        }
        $grid.isotope({ filter: selector });
    });

    let lastScrollTop = 0;

    $('.navbar-nav a[href*="#"]:not([href="#"])').on('click', function(e) {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            let target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 72 
                }, 600, function() { 
                    let $target = $(target);
                    $target.focus();
                    if ($target.is(":focus")) { 
                        return false;
                    } else {
                        $target.attr('tabindex', '-1'); 
                        $target.focus(); 
                    };
                });
                var $navbarResponsive = $('#navbarResponsive');
                if ($navbarResponsive.hasClass('show')) {
                    var bsCollapse = new bootstrap.Collapse($navbarResponsive[0], {
                        toggle: false
                    });
                    bsCollapse.hide();
                }
            }
        }
    });

    let navbarCollapse = function() {
        var $mainNav = $("#mainNav");
        var $navbarResponsive = $('#navbarResponsive');
        var st = $(window).scrollTop();
        var windowWidth = $(window).width();

        if (st > 100) {
            $mainNav.addClass("navbar-scrolled");
        } else {
            $mainNav.removeClass("navbar-scrolled");
        }

        if (windowWidth < 992) {
            if ($navbarResponsive.hasClass('show')) {
                return;
            }
            if (st > lastScrollTop && st > 100) {
                $mainNav.addClass('navbar-hidden');
            } else {
                $mainNav.removeClass('navbar-hidden');
            }
        } else {
            $mainNav.removeClass('navbar-hidden');
        }
        lastScrollTop = st <= 0 ? 0 : st; 
    };
    navbarCollapse();
    $(window).scroll(navbarCollapse); 
    $(window).resize(navbarCollapse); 

    $('.hero h1, .hero p').addClass('animate__animated animate__fadeInUp');
    
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        let $form = $(this);
        let $submitButton = $('#submitButton');
        let $submitButtonText = $('#submitButtonText');
        let $spinner = $submitButton.find('.spinner-border');
        let $messageWrapper = $('#form-message-wrapper');

        $submitButton.prop('disabled', true);
        $submitButtonText.text('Enviando...');
        $spinner.removeClass('d-none');
        $messageWrapper.html(''); 

        let formData = {
            name: $('#name').val(),
            email: $('#email').val(),
            message: $('#message').val(),
            website_url: $('#website_url').val() 
        };

        $.ajax({
            type: "POST",
            url: "send_mail.php", 
            data: formData,
            dataType: "json", 
            success: function(response) {
                if (response.success) {
                    $messageWrapper.html('<div class="alert alert-success" role="alert">¡Mensaje enviado con éxito! Gracias por contactarme.</div>');
                    $form[0].reset(); 
                } else {
                    $messageWrapper.html('<div class="alert alert-danger" role="alert">Error: ' + response.message + '</div>');
                }
            },
            error: function() {
                $messageWrapper.html('<div class="alert alert-danger" role="alert">Hubo un error al conectar con el servidor.</div>');
            },
            complete: function() {
                $submitButton.prop('disabled', false);
                $submitButtonText.text('Enviar Mensaje');
                $spinner.addClass('d-none');
            }
        });
    });

    const $whatsappBtn = $('.whatsapp-float-btn');
    const showDelay = 5000;
    const hideDelay = 10000;
    const reappearDelay = 30000;

    function showWhatsappButton() {
        $whatsappBtn.css({ 'opacity': 1, 'visibility': 'visible', 'transform': 'scale(1)' });
    }

    function hideWhatsappButton() {
        $whatsappBtn.css({ 'opacity': 0, 'visibility': 'hidden', 'transform': 'scale(0.8)' });
    }

    let timeoutShow, timeoutHide, timeoutReappear;

    function startWhatsappCycle() {
        clearTimeout(timeoutShow);
        clearTimeout(timeoutHide);
        clearTimeout(timeoutReappear);

        timeoutShow = setTimeout(() => {
            showWhatsappButton();
            timeoutHide = setTimeout(() => {
                hideWhatsappButton();
                timeoutReappear = setTimeout(startWhatsappCycle, reappearDelay);
            }, hideDelay);
        }, showDelay);
    }
    startWhatsappCycle();

    // --- NUEVO: BOTÓN VOLVER ARRIBA ---
    var $btnBackToTop = $('#btnBackToTop');
    
    $(window).scroll(function() {
        if ($(window).scrollTop() > 300) {
            $btnBackToTop.fadeIn();
        } else {
            $btnBackToTop.fadeOut();
        }
    });

    $btnBackToTop.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop:0}, '300');
    });

});