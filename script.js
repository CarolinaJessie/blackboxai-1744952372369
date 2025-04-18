/**
 * E-Sim Store - Main JavaScript File
 * 
 * File ini berisi semua interaksi dan animasi untuk website E-Sim Store
 */

// Tunggu hingga DOM sepenuhnya dimuat
document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scrolling untuk navigasi
    const navLinks = document.querySelectorAll('nav a');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            window.scrollTo({
                top: targetSection.offsetTop - 70, // Offset untuk header
                behavior: 'smooth'
            });
        });
    });
    
    // Animasi untuk tombol checkout
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        checkoutBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Tambahkan efek klik
        checkoutBtn.addEventListener('click', function() {
            // Animasi klik sederhana
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 100);
            
            // Buka link dalam tab baru
            window.open(this.getAttribute('href'), '_blank');
        });
    }
    
    // Animasi untuk semua tombol
    const allButtons = document.querySelectorAll('.btn');
    
    allButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.1)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
        });
        
        // Tambahkan efek klik
        button.addEventListener('click', function(e) {
            // Jika bukan tombol checkout utama, cegah default behavior
            if (this.id !== 'checkoutBtn') {
                e.preventDefault();
                
                // Animasi klik sederhana
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
                
                // Buka link dalam tab baru
                window.open(this.getAttribute('href'), '_blank');
            }
        });
    });
    
    // Animasi scroll untuk elemen-elemen
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.feature, .product-card, .contact-item');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (elementPosition < screenPosition) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Set initial state untuk elemen yang akan dianimasikan
    const elementsToAnimate = document.querySelectorAll('.feature, .product-card, .contact-item');
    elementsToAnimate.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'all 0.5s ease-out';
    });
    
    // Jalankan animasi saat halaman dimuat dan saat scroll
    window.addEventListener('load', animateOnScroll);
    window.addEventListener('scroll', animateOnScroll);
    
    // Validasi form kontak (jika ada)
    const contactForm = document.querySelector('.contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Logika validasi form bisa ditambahkan di sini
            
            // Tampilkan pesan sukses
            alert('Pesan Anda telah terkirim. Kami akan menghubungi Anda segera!');
            this.reset();
        });
    }
});
