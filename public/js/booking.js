// Handle tanggal button selection
document.querySelectorAll('.tanggal-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tanggal-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    const date = btn.getAttribute('data-date');
    // AJAX ambil slot
    fetch(`booking/slots?date=${encodeURIComponent(date)}`)
      .then(res => res.json())
      .then(res => {
        document.querySelector('.slot-grid').outerHTML = res.html;
        // Pasang ulang event listener untuk slot baru
        document.querySelectorAll('.slot').forEach(slot => {
          slot.addEventListener('click', () => {
            const selectedTanggalBtn = document.querySelector('.tanggal-btn.selected');
            const date = selectedTanggalBtn.dataset.date || '';
            const hour = slot.dataset.hour || '';
            const hourVal = slot.dataset.hourval || '';
            openBookingModal({ date, hour, hourVal });
          });
        });
      });
  });
});

// Ganti handle slot button dengan openBookingModal
const slotBtns = document.querySelectorAll('.slot');
slotBtns.forEach(slot => {
  slot.addEventListener('click', () => {
    // Ambil data dari tanggal-btn yang sedang selected
    const selectedTanggalBtn = document.querySelector('.tanggal-btn.selected');
    // Ambil info tanggal dan jam dari data attribute
    const date = selectedTanggalBtn.dataset.date || '';
    const hour = slot.dataset.hour || '';
    const hourVal = slot.dataset.hourval || '';

    openBookingModal({ date, hour, hourVal });
  });
});

// Fungsi modal SweetAlert2 booking
function openBookingModal({date, hour, hourVal}) {
    Swal.fire({
        title: 'Pesan Lapangan',
        html: `
        <div id="bookingFormSwal" style="max-width:370px;">
            <input type="hidden" id="modal-date" value="${date}">
            <input type="hidden" id="modal-hour" value="${hourVal}">
            <input type="hidden" id="modal-hour-end">
            <div style="display:flex;align-items:center;margin-bottom:10px;">
              <label style="flex:0 0 110px;text-align:right;margin-right:8px;">Durasi :</label>
              <div style="flex:1;display:flex;gap:10px;">
                <div>
                  <input type="radio" name="durationRadio" id="duration1" value="1" checked>
                  <label for="duration1">1 Jam</label>
                </div>
                <div>
                  <input type="radio" name="durationRadio" id="duration2" value="2">
                  <label for="duration2" id="labelduration2">2 Jam</label>
                </div>
              </div>
            </div>
            <div style="display:flex;align-items:center;margin-bottom:10px;">
              <label style="flex:0 0 110px;text-align:right;margin-right:8px;">Tanggal :</label>
              <div style="flex:1;">
                <input type="text" id="modal-date-display" value="${date}" disabled style="width:100%;padding:6px 8px;">
              </div>
            </div>
            <div style="display:flex;align-items:center;margin-bottom:10px;">
              <label style="flex:0 0 110px;text-align:right;margin-right:8px;">Jam :</label>
              <div style="flex:1;display:flex;align-items:center;gap:6px;">
                <input type="text" id="modal-hour-display" style="width:80px;" value="${hour.padStart(2,'0')}" disabled>
                <span id="sampai-label" style="margin:0 4px; display:none;">&</span>
                <input type="text" id="modal-hour-end-display" style="width:80px; display:none;" disabled>
              </div>
            </div>
            <div style="display:flex;align-items:center;margin-bottom:10px;">
              <label for="modal-unit" style="flex:0 0 110px;text-align:right;margin-right:8px;">No. Unit :</label>
              <div style="flex:1;">
                <input type="number" id="modal-unit" required style="width:100%;padding:6px 8px;">
              </div>
            </div>
            </div>
        </div>
        `,
        customClass: {
            popup: 'swal2-booking-modal'
        },
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Save Booking',
        cancelButtonText: 'Cancel',
        didOpen: () => {
            // Event radio for end hour
            document.getElementById('duration1').addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('modal-hour-end').value = '';
                    document.getElementById('modal-hour-end-display').style.display = 'none';
                    document.getElementById('sampai-label').style.display = 'none';
                }
            });
            document.getElementById('duration2').addEventListener('change', function() {
                if (this.checked) {
                    const jam = parseInt(hour, 10);
                    if (jam === 23) {
                        document.getElementById('modal-hour-end').value = '';
                        document.getElementById('modal-hour-end-display').style.display = 'none';
                        document.getElementById('sampai-label').style.display = 'none';
                    } else {
                        document.getElementById('modal-hour-end').value = (jam+1).toString().padStart(2,'0');
                        document.getElementById('modal-hour-end-display').value = (jam+1).toString().padStart(2,'0') + ':00 - '+(jam+1).toString().padStart(2,'0') + ':59';
                        document.getElementById('modal-hour-end-display').style.display = '';
                        document.getElementById('sampai-label').style.display = '';
                    }
                }
            });
        },
        didClose:() => {
            // window.scrollTo(0, 0);
        },
        preConfirm: () => {
            const unit = document.getElementById('modal-unit').value.trim();
            const duration1 = document.getElementById('duration1').checked;
            const durationRadio = duration1 ? '1' : '2';
            let hourEnd = '';
            // Ambil CSRF token dari meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            if (!unit) {
                Swal.showValidationMessage('All fields are required!');
                return false;
            }
            if (!duration1) {
                const jam = parseInt(hourVal, 10);
                hourEnd = (jam + 1);
            }
            return { 
                date, 
                hour: parseInt(hourVal, 10),
                hourEnd: parseInt(hourEnd, 10),
                unit, 
                durationRadio,
                _token: csrfToken // tambahkan CSRF token ke data yang dikirim
            };
        }
    }).then((result) => {
        // window.scrollTo(0, 0);
        if (result.isConfirmed && result.value) {
            // AJAX POST ke route booking.store
            fetch('booking', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(result.value)
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    Swal.fire('Sukses', res.message, 'success');
                    fetch(`booking/slots?date=${encodeURIComponent(date)}`)
                    .then(res => res.json())
                    .then(res => {
                      document.querySelector('.slot-grid').outerHTML = res.html;
                      // Pasang ulang event listener untuk slot baru
                      document.querySelectorAll('.slot').forEach(slot => {
                        slot.addEventListener('click', () => {
                          const selectedTanggalBtn = document.querySelector('.tanggal-btn.selected');
                          const date = selectedTanggalBtn.dataset.date || '';
                          const hour = slot.dataset.hour || '';
                          const hourVal = slot.dataset.hourval || '';
                          openBookingModal({ date, hour, hourVal });
                        });
                      });
                    });
                } else {
                    Swal.fire('Gagal', res.error || 'Gagal booking!', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Gagal', 'Terjadi kesalahan. Silakan coba lagi.', 'error');
            });
        }
    });
}

// Bottom nav logic: highlight current
const navBtns = document.querySelectorAll('.nav-btn');
navBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    navBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    // TODO: switch content for each menu if needed
    // For now, only highlight
  });
});

// Optional: Make sure bottom nav is always visible above keyboard on mobile
window.addEventListener('resize', () => {
  document.body.style.paddingBottom = document.querySelector('.bottom-nav').offsetHeight + 6 + 'px';
});