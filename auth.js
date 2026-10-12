document.addEventListener("DOMContentLoaded", function () {
    const card = document.querySelector(".register-card") || document.querySelector(".login-card");
    const linkToSwitch = document.querySelector(".auth-footer a");
    const form = document.querySelector("form");

    // 1. Animasi Transisi Halus Saat Berpindah Halaman Login <-> Register
    if (linkToSwitch && card) {
        linkToSwitch.addEventListener("click", function (e) {
            e.preventDefault();
            const targetUrl = this.getAttribute("href");

            card.classList.add("page-exit");

            setTimeout(() => {
                window.location.href = targetUrl;
            }, 350);
        });
    }

    // 2. Animasi Tombol saat Form Di-submit
    if (form) {
        form.addEventListener("submit", function () {
            const btn = form.querySelector(".btn-submit");
            if (btn) {
                btn.style.pointerEvents = "none";
                btn.style.opacity = "0.8";
                btn.innerHTML = "Memproses...";
            }
        });
    }

    // 3. Batas Password Max 8 Digit + Toggle Mata + Pesan Peringatan
    const passwordInputs = document.querySelectorAll('input[type="password"]');

    passwordInputs.forEach((input) => {
        // Atur batas maksimal 8 karakter
        input.setAttribute("maxlength", "8");

        // Event saat user mengetik password
        input.addEventListener("input", function () {
            if (this.value.length >= 8) {
                this.value = this.value.slice(0, 8);

                // TAMPILKAN TEKS PERINGATAN MERAH DI BAWAH INPUT
                let hint = this.parentElement.querySelector(".max-pass-hint");
                if (!hint) {
                    hint = document.createElement("small");
                    hint.className = "max-pass-hint";
                    hint.style.color = "#e53e3e";
                    hint.style.fontSize = "12px";
                    hint.style.marginTop = "6px";
                    hint.style.display = "block";
                    hint.style.textAlign = "left";
                    hint.innerText = "Maksimal 8 digit!";
                    this.parentElement.appendChild(hint);
                }
            } else {
                // Sembunyikan pesan jika karakter kurang dari 8
                const hint = this.parentElement.querySelector(".max-pass-hint");
                if (hint) hint.remove();
            }
        });

        // Buat wrapper untuk icon mata (jika belum ada)
        if (!input.parentElement.classList.contains("password-wrapper")) {
            const wrapper = document.createElement("div");
            wrapper.className = "password-wrapper";

            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            const toggleIcon = document.createElement("i");
            toggleIcon.className = "fas fa-eye toggle-password";
            wrapper.appendChild(toggleIcon);

            toggleIcon.addEventListener("click", function () {
                if (input.type === "password") {
                    input.type = "text";
                    this.classList.remove("fa-eye");
                    this.classList.add("fa-eye-slash");
                } else {
                    input.type = "password";
                    this.classList.remove("fa-eye-slash");
                    this.classList.add("fa-eye");
                }
            });
        }
    });
});
        function updateDashboardWidgets() {
            const now = new Date();

            // 1. Format Jam (Contoh: 14:05:09)
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false });

            // 2. Format Hari (Contoh: Kamis)
            const dayString = now.toLocaleDateString('id-ID', { weekday: 'long' });

            // 3. Format Tanggal (Contoh: 8 Oktober 2026)
            const dateString = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

            // Masukkan ke elemen kartu jika ditemukan di halaman
            const timeEl = document.getElementById('liveTimeCard');
            const dayEl = document.getElementById('liveDayCard');
            const dateEl = document.getElementById('liveDateCard');

            if (timeEl) timeEl.innerText = timeString;
            if (dayEl) dayEl.innerText = dayString;
            if (dateEl) dateEl.innerText = dateString;
        }

        // Jalankan update setiap 1 detik
        setInterval(updateDashboardWidgets, 1000);

        // Panggil langsung saat dokumen selesai dimuat
        document.addEventListener('DOMContentLoaded', updateDashboardWidgets);