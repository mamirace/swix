// DOM Elements
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const navLinks = document.querySelectorAll('.nav-link');

// Mobile Menu Toggle
hamburger?.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu?.classList.toggle('active');
});

// Close mobile menu when link is clicked
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        hamburger?.classList.remove('active');
        navMenu?.classList.remove('active');
    });
});

// Welcome Message Function
function showWelcomeMessage() {
    // SweetAlert benzeri modern alert
    showCustomAlert('Merhaba! 🎉', 'Web sitenize hoşgeldiniz! Bu modern tasarım ile projelerinizi hayata geçirebilirsiniz.', 'success');
    
    // Particle effect
    createParticles();
}

// Custom Alert Function
function showCustomAlert(title, message, type = 'info') {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    `;

    // Create modal
    const modal = document.createElement('div');
    modal.className = 'custom-modal';
    modal.style.cssText = `
        background: white;
        border-radius: 16px;
        padding: 2rem;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        transform: scale(0.7);
        animation: modalPop 0.3s ease forwards;
    `;

    // Icon based on type
    let icon = '🎉';
    let color = '#7367f0';
    
    if (type === 'success') {
        icon = '🎉';
        color = '#28c76f';
    } else if (type === 'warning') {
        icon = '⚠️';
        color = '#ff9f43';
    } else if (type === 'error') {
        icon = '❌';
        color = '#ea5455';
    }

    modal.innerHTML = `
        <div class="modal-icon" style="font-size: 3rem; margin-bottom: 1rem;">${icon}</div>
        <h3 style="color: ${color}; margin-bottom: 1rem; font-size: 1.5rem;">${title}</h3>
        <p style="color: #666; margin-bottom: 2rem; line-height: 1.6;">${message}</p>
        <button class="modal-btn" style="
            background: ${color};
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        ">Tamam</button>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Add animations CSS
    if (!document.getElementById('modal-animations')) {
        const style = document.createElement('style');
        style.id = 'modal-animations';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes modalPop {
                to { transform: scale(1); }
            }
            .modal-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            }
        `;
        document.head.appendChild(style);
    }

    // Close modal
    const closeModal = () => {
        overlay.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => {
            document.body.removeChild(overlay);
        }, 300);
    };

    // Add fadeOut animation
    const fadeOutStyle = document.createElement('style');
    fadeOutStyle.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(fadeOutStyle);

    modal.querySelector('.modal-btn').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });
}

// Particle Effect
function createParticles() {
    for (let i = 0; i < 20; i++) {
        setTimeout(() => {
            createParticle();
        }, i * 100);
    }
}

function createParticle() {
    const particle = document.createElement('div');
    particle.style.cssText = `
        position: fixed;
        width: 10px;
        height: 10px;
        background: linear-gradient(45deg, #667eea, #764ba2);
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        animation: particleFloat 3s ease-out forwards;
    `;

    // Random starting position
    const startX = Math.random() * window.innerWidth;
    const startY = window.innerHeight;
    
    particle.style.left = startX + 'px';
    particle.style.top = startY + 'px';

    document.body.appendChild(particle);

    // Add animation
    if (!document.getElementById('particle-animations')) {
        const style = document.createElement('style');
        style.id = 'particle-animations';
        style.textContent = `
            @keyframes particleFloat {
                0% {
                    transform: translateY(0) rotate(0deg);
                    opacity: 1;
                }
                100% {
                    transform: translateY(-100vh) rotate(720deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Remove particle after animation
    setTimeout(() => {
        if (document.body.contains(particle)) {
            document.body.removeChild(particle);
        }
    }, 3000);
}

// Smooth Scroll for Navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Active Navigation Update on Scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section[id]');
    const scrollPos = window.scrollY + 100;

    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        
        if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
            // Remove active class from all nav links
            navLinks.forEach(link => link.classList.remove('active'));
            
            // Add active class to current section's nav link
            const activeLink = document.querySelector(`a[href="#${sectionId}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }
    });
});

// Loading Animation
document.addEventListener('DOMContentLoaded', () => {
    // Add entrance animations
    const animateElements = document.querySelectorAll('.hero-title, .hero-subtitle, .hero-description, .hero-buttons, .welcome-card');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);

    animateElements.forEach(el => {
        observer.observe(el);
    });
});

// Feature Cards Hover Effect
document.querySelectorAll('.feature-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
        this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.15)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
        this.style.boxShadow = '0 4px 24px rgba(34, 41, 47, 0.1)';
    });
});

// Console Welcome Message
console.log('%c🎉 Merhaba! Web sitenize hoşgeldiniz!', 'color: #7367f0; font-size: 20px; font-weight: bold;');
console.log('%cBu site Hostinger hosting ile güçlendirilmiştir.', 'color: #28c76f; font-size: 14px;');
console.log('%cVuexy temasından ilham alınarak modern tasarım prensiplerine göre geliştirilmiştir.', 'color: #666; font-size: 12px;');

// Page Performance Monitoring
window.addEventListener('load', () => {
    const loadTime = performance.now();
    console.log(`%c⚡ Sayfa ${Math.round(loadTime)}ms'de yüklendi`, 'color: #ff9f43; font-weight: bold;');
    
    // Show loading complete message after a short delay
    setTimeout(() => {
        showWelcomeToast();
    }, 1500);
});

// Welcome Toast Function
function showWelcomeToast() {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        transform: translateX(400px);
        transition: transform 0.5s ease;
        max-width: 300px;
        backdrop-filter: blur(10px);
    `;
    
    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem;">👋</span>
            <div>
                <div style="font-weight: 600; margin-bottom: 2px;">Hoşgeldiniz!</div>
                <div style="font-size: 0.85rem; opacity: 0.9;">Web siteniz hazır ve çalışıyor</div>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 500);
    }, 4000);
    
    // Click to dismiss
    toast.addEventListener('click', () => {
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 500);
    });
}

// Add some Easter Eggs
let clickCount = 0;
document.querySelector('.nav-logo h2')?.addEventListener('click', () => {
    clickCount++;
    if (clickCount === 5) {
        showCustomAlert('Easter Egg! 🥚', 'Logo\'ya 5 kez tıkladınız! Gizli özelliği keşfettiniz!', 'success');
        createParticles();
        clickCount = 0;
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Ctrl + M for welcome message
    if (e.ctrlKey && e.key === 'm') {
        e.preventDefault();
        showWelcomeMessage();
    }
    
    // Ctrl + P for particles
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        createParticles();
    }
});

// Export functions for potential future use
window.siteFeatures = {
    showWelcomeMessage,
    createParticles,
    showCustomAlert
};