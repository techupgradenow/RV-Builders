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

    // Video Player Functionality
    initVideoPlayer();
});

// Video Player Initialization
function initVideoPlayer() {
    const video = document.getElementById('intro-video');
    const videoOverlay = document.getElementById('video-overlay');
    const playBtn = document.getElementById('play-btn');
    const playPauseBtn = document.getElementById('video-play-pause');
    const muteBtn = document.getElementById('video-mute');
    const fullscreenBtn = document.getElementById('video-fullscreen');
    const progressBar = document.getElementById('video-progress-bar');
    const videoProgress = document.querySelector('.video-progress');
    const videoContainer = document.querySelector('.video-container');

    if (!video) return;

    // Play button click (overlay)
    if (playBtn) {
        playBtn.addEventListener('click', function() {
            playVideo();
        });
    }

    // Overlay click
    if (videoOverlay) {
        videoOverlay.addEventListener('click', function() {
            playVideo();
        });
    }

    // Play/Pause button in controls
    if (playPauseBtn) {
        playPauseBtn.addEventListener('click', function() {
            togglePlayPause();
        });
    }

    // Video click to toggle play/pause
    video.addEventListener('click', function() {
        togglePlayPause();
    });

    // Mute button
    if (muteBtn) {
        muteBtn.addEventListener('click', function() {
            video.muted = !video.muted;
            updateMuteIcon();
        });
    }

    // Fullscreen button
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            toggleFullscreen();
        });
    }

    // Progress bar click
    if (videoProgress) {
        videoProgress.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            video.currentTime = percent * video.duration;
        });
    }

    // Update progress bar
    video.addEventListener('timeupdate', function() {
        if (progressBar && video.duration) {
            const percent = (video.currentTime / video.duration) * 100;
            progressBar.style.width = percent + '%';
        }
    });

    // Video ended
    video.addEventListener('ended', function() {
        if (videoOverlay) {
            videoOverlay.classList.remove('hidden');
        }
        if (videoContainer) {
            videoContainer.classList.remove('playing');
        }
        updatePlayPauseIcon();
    });

    // Functions
    function playVideo() {
        video.play();
        if (videoOverlay) {
            videoOverlay.classList.add('hidden');
        }
        if (videoContainer) {
            videoContainer.classList.add('playing');
        }
        updatePlayPauseIcon();
    }

    function togglePlayPause() {
        if (video.paused) {
            video.play();
            if (videoOverlay) {
                videoOverlay.classList.add('hidden');
            }
            if (videoContainer) {
                videoContainer.classList.add('playing');
            }
        } else {
            video.pause();
        }
        updatePlayPauseIcon();
    }

    function updatePlayPauseIcon() {
        if (playPauseBtn) {
            const icon = playPauseBtn.querySelector('i');
            if (icon) {
                if (video.paused) {
                    icon.classList.remove('fa-pause');
                    icon.classList.add('fa-play');
                } else {
                    icon.classList.remove('fa-play');
                    icon.classList.add('fa-pause');
                }
            }
        }
    }

    function updateMuteIcon() {
        if (muteBtn) {
            const icon = muteBtn.querySelector('i');
            if (icon) {
                if (video.muted) {
                    icon.classList.remove('fa-volume-up');
                    icon.classList.add('fa-volume-mute');
                } else {
                    icon.classList.remove('fa-volume-mute');
                    icon.classList.add('fa-volume-up');
                }
            }
        }
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            if (videoContainer.requestFullscreen) {
                videoContainer.requestFullscreen();
            } else if (videoContainer.webkitRequestFullscreen) {
                videoContainer.webkitRequestFullscreen();
            } else if (videoContainer.msRequestFullscreen) {
                videoContainer.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }

    // Handle autoplay - hide overlay and update icons if video is autoplaying
    if (video.autoplay) {
        if (videoOverlay) {
            videoOverlay.classList.add('hidden');
        }
        if (videoContainer) {
            videoContainer.classList.add('playing');
        }
        updatePlayPauseIcon();
        updateMuteIcon();
    }
}

