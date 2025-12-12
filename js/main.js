// Banner Carousel Auto-Scroll
let currentSlide = 0;
let carouselInterval;
const slideDuration = 5000; // 5 seconds per slide

function initCarousel() {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    
    if (slides.length === 0) return;
    
    function showSlide(index) {
        // Remove active class from all slides and dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Add active class to current slide and dot
        if (slides[index]) {
            slides[index].classList.add('active');
        }
        if (dots[index]) {
            dots[index].classList.add('active');
        }
        
        currentSlide = index;
    }
    
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }
    
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prev);
    }
    
    function startAutoSlide() {
        carouselInterval = setInterval(nextSlide, slideDuration);
    }
    
    function stopAutoSlide() {
        clearInterval(carouselInterval);
    }
    
    // Navigation buttons
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            stopAutoSlide();
            startAutoSlide();
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            stopAutoSlide();
            startAutoSlide();
        });
    }
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            stopAutoSlide();
            startAutoSlide();
        });
    });
    
    // Pause on hover
    const carousel = document.querySelector('.banner-carousel');
    if (carousel) {
        carousel.addEventListener('mouseenter', stopAutoSlide);
        carousel.addEventListener('mouseleave', startAutoSlide);
    }
    
    // Start auto-slide
    startAutoSlide();
}

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousel
    initCarousel();
    const mobileMenu = document.querySelector('.mobile-menu');
    const nav = document.querySelector('nav');
    const navUl = document.querySelector('nav ul');
    const menuIcon = mobileMenu ? mobileMenu.querySelector('i') : null;
    
    if (mobileMenu && nav && menuIcon) {
        mobileMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            nav.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            
            // Toggle icon between bars and times
            if (nav.classList.contains('active')) {
                menuIcon.classList.remove('fa-bars');
                menuIcon.classList.add('fa-times');
            } else {
                menuIcon.classList.remove('fa-times');
                menuIcon.classList.add('fa-bars');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (nav && nav.classList.contains('active') && !nav.contains(e.target) && !mobileMenu.contains(e.target)) {
                nav.classList.remove('active');
                mobileMenu.classList.remove('active');
                if (menuIcon) {
                    menuIcon.classList.remove('fa-times');
                    menuIcon.classList.add('fa-bars');
                }
            }
        });
    }

    // Update active navigation link based on current page
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('nav a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === 'index.html' && currentPage === 'index.html') {
            link.classList.add('active');
        } else if (href === 'about.html' && currentPage === 'about.html') {
            link.classList.add('active');
        } else if (href === 'services.html' && currentPage === 'services.html') {
            link.classList.add('active');
        } else if (href === 'projects.html' && currentPage === 'projects.html') {
            link.classList.add('active');
        } else if (href === 'contact.html' && currentPage === 'contact.html') {
            link.classList.add('active');
        }
    });

    // Close mobile menu when clicking a link
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (nav) {
                nav.classList.remove('active');
            }
            if (mobileMenu) {
                mobileMenu.classList.remove('active');
                if (menuIcon) {
                    menuIcon.classList.remove('fa-times');
                    menuIcon.classList.add('fa-bars');
                }
            }
        });
    });

    // Project Popup Functionality
    document.querySelectorAll('.project-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking the view button (it has its own handler)
            if (e.target.closest('.project-view-btn')) {
                return;
            }
            const projectId = this.getAttribute('data-project');
            const popup = document.getElementById(`project${projectId}-popup`);
            if (popup) {
                popup.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Project View Button Click Handler
    document.querySelectorAll('.project-view-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                const popupId = href.substring(1);
                const popup = document.getElementById(popupId);
                if (popup) {
                    popup.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            }
        });
    });

    // Project Filter Functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.professional-project-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            const filterValue = this.getAttribute('data-filter');

            projectCards.forEach(card => {
                if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });

    // Service Popup Functionality
    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('click', function() {
            const serviceId = this.getAttribute('data-service');
            const popup = document.getElementById(`${serviceId}-popup`);
            if (popup) {
                popup.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close Popup
    document.querySelectorAll('.popup-close').forEach(button => {
        button.addEventListener('click', function() {
            const popup = this.closest('.popup');
            if (popup) {
                popup.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Close popup when clicking outside the content
    document.querySelectorAll('.popup').forEach(popup => {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Contact Form Submission
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });
    }
});

