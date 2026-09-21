/**
 * JavaScript Animasi Pop-Up Opsi Saat di-Scroll (Tidak Kaku)
 * Bagas Laundry Express
 */

document.addEventListener('DOMContentLoaded', () => {

    // ============================================================
    // 1. ANIMASI POP-UP PADA KARTU & OPSI SAAT DI-SCROLL
    // ============================================================
    // Pasang kelas .scroll-popup pada semua opsi layanan, fitur, kalkulator, dan kartu preview
    const popTargets = document.querySelectorAll(
        '.card-service, .feature-box, .preview-option-card, .section-header, #kalkulator'
    );

    popTargets.forEach((el) => {
        el.classList.add('scroll-popup');
    });

    // Observer untuk mendeteksi saat kartu masuk ke layar
    const popupObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                
                // Cari index urutan dalam grid/kelompok untuk efek pop-up bertahap (stagger)
                let delay = 0;
                if (el.parentElement) {
                    const siblings = Array.from(el.parentElement.children);
                    const index = siblings.indexOf(el);
                    if (index >= 0) {
                        delay = (index % 4) * 110; // pop-up berurutan: 0ms, 110ms, 220ms, 330ms
                    }
                }

                setTimeout(() => {
                    el.classList.add('is-visible');
                }, delay);

                popupObserver.unobserve(el);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: '0px 0px -30px 0px'
    });

    popTargets.forEach(el => popupObserver.observe(el));

    // ============================================================
    // 2. ANIMASI SMOOTH NUMBER COUNTER PADA STATISTIK HERO
    // ============================================================
    const statCounters = document.querySelectorAll('.stat-item h4');
    let hasCounted = false;

    function animateCounters() {
        if (hasCounted) return;
        statCounters.forEach(counter => {
            const rawText = counter.innerText.trim();
            const match = rawText.match(/([0-9.]+)/);
            if (match) {
                const target = parseFloat(match[1].replace('.', ''));
                const suffix = rawText.replace(match[1], '');
                let current = 0;
                const totalSteps = 40;
                const stepVal = target / totalSteps;

                const timer = setInterval(() => {
                    current += stepVal;
                    if (current >= target) {
                        counter.innerText = target.toLocaleString('id-ID') + suffix;
                        clearInterval(timer);
                    } else {
                        counter.innerText = Math.floor(current).toLocaleString('id-ID') + suffix;
                    }
                }, 25);
            }
        });
        hasCounted = true;
    }

    const heroStats = document.querySelector('.hero-stats');
    if (heroStats) {
        const statsObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                animateCounters();
                statsObserver.unobserve(heroStats);
            }
        }, { threshold: 0.3 });
        statsObserver.observe(heroStats);
    }

    // ============================================================
    // 3. KALKULATOR ESTIMASI BIAYA
    // ============================================================
    const calcLayanan = document.getElementById('calc-layanan');
    const calcJumlah = document.getElementById('calc-jumlah');
    const calcTotal = document.getElementById('calc-total');
    const calcSatuanLabel = document.getElementById('calc-satuan-label');

    function updateCalculator() {
        if (!calcLayanan || !calcJumlah || !calcTotal) return;

        const selectedOption = calcLayanan.options[calcLayanan.selectedIndex];
        const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
        const jenis = selectedOption.getAttribute('data-jenis') || 'kiloan';
        const jumlah = parseFloat(calcJumlah.value) || 0;

        if (calcSatuanLabel) {
            calcSatuanLabel.textContent = jenis === 'kiloan' ? 'Kg' : 'Item / Lembar';
        }

        const total = Math.round(harga * jumlah);
        
        calcTotal.style.transform = 'scale(1.08)';
        calcTotal.style.transition = 'transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1)';
        calcTotal.textContent = 'Rp ' + total.toLocaleString('id-ID');
        
        setTimeout(() => {
            calcTotal.style.transform = 'scale(1)';
        }, 200);
    }

    if (calcLayanan && calcJumlah) {
        calcLayanan.addEventListener('change', updateCalculator);
        calcJumlah.addEventListener('input', updateCalculator);
        updateCalculator();
    }
});
