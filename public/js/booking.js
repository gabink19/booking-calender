// Handle tanggal button selection
document.querySelectorAll('.tanggal-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tanggal-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    const date = btn.getAttribute('data-date');
    // Tampilkan loading SweetAlert2
    Swal.fire({
      title: `${window.bookingLang.loading}`,
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
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
        Swal.close(); // Tutup loading setelah selesai
      })
      .catch(() => {
        Swal.fire(`${window.bookingLang.failed}`, `${window.bookingLang.errorGeneral}`, 'error');
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
    // Ambil data user dari localStorage
    let user = { name: '', unit: '', whatsapp: '' , is_admin: ''};
    try {
        user = JSON.parse(localStorage.getItem('user')) || user;
    } catch (e) {}

    let aktifitas = 'No. Unit'
    let style = ''
    let unit = user.unit
    if (user.is_admin=="1") {
      aktifitas = 'Aktifitas'
      unit = `${window.bookingLang.statusMaintenance}`
      style = 'style="display:none;"'
    }
    Swal.fire({
    title: `${window.bookingLang.bookingTitle}`,
    html: `
    <style>
      #bookingFormSwal label {
        flex: 0 0 110px;
        text-align: right;
        margin-right: 8px;
        font-size: 15px;
        min-width: 110px;
        max-width: 110px;
      }
      #bookingFormSwal input[type="text"], 
      #bookingFormSwal input[type="number"] {
        width: 100%;
        padding: 6px 8px;
        font-size: 15px;
        box-sizing: border-box;
      }
      #bookingFormSwal .hour-input {
        width: 100px !important;
        padding: 6px 8px;
        font-size: 13px !important;
      }
      #bookingFormSwal .form-row {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
      }
    </style>
    <div id="bookingFormSwal" style="max-width:370px;">
        <input type="hidden" id="modal-date" value="${date}">
        <input type="hidden" id="modal-hour" value="${hourVal}">
        <input type="hidden" id="modal-hour-end">
        <div class="form-row">
          <label>${window.bookingLang.duration} :</label>
          <div style="flex:1;display:flex;gap:10px;">
            <div>
              <input type="radio" name="durationRadio" id="duration1" value="1" checked>
              <label for="duration1">1 ${window.bookingLang.hour}</label>
            </div>
            <div>
              <input type="radio" name="durationRadio" id="duration2" value="2">
              <label for="duration2" id="labelduration2">2 ${window.bookingLang.hour}</label>
            </div>
          </div>
        </div>
        <div class="form-row">
          <label>${window.bookingLang.date} :</label>
          <div style="flex:1;">
            <input type="text" id="modal-date-display" value="${date}" disabled>
          </div>
        </div>
        <div class="form-row">
          <label>${window.bookingLang.hour} :</label>
          <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
            <input type="text" id="modal-hour-display" class="hour-input" value="${hour.padStart(2,'0')}" disabled>
            <div style="display:flex;align-items:center;gap:6px;">
              <input type="text" id="modal-hour-end-display" class="hour-input" style="display:none;" disabled>
            </div>
          </div>
        </div>
        <div class="form-row" ${style}>
          <label>${window.bookingLang.name} :</label>
          <div style="flex:1;">
            <input type="text" id="modal-name" value="${user.name || ''}" disabled>
          </div>
        </div>
        <div class="form-row">
          <label>${aktifitas} :</label>
          <div style="flex:1;">
            <input type="text" id="modal-unit" value="${unit || ''}" disabled>
          </div>
        </div>
        <div class="form-row" ${style}>
          <label>WhatsApp :</label>
          <div style="flex:1;">
            <input type="text" id="modal-whatsapp" value="${user.whatsapp || ''}" disabled>
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
                }
            });
            document.getElementById('duration2').addEventListener('change', function() {
                if (this.checked) {
                    const jam = parseInt(hour, 10);
                    if (jam === 20) {
                        document.getElementById('modal-hour-end').value = '';
                        document.getElementById('modal-hour-end-display').style.display = 'none';
                        document.getElementById('duration2').disabled = true;
                    } else {
                        document.getElementById('modal-hour-end').value = (jam+1).toString().padStart(2,'0');
                        document.getElementById('modal-hour-end-display').value = (jam+1).toString().padStart(2,'0') + ':00 - '+(jam+2).toString().padStart(2,'0') + ':00';
                        document.getElementById('modal-hour-end-display').style.display = '';
                        document.getElementById('duration2').disabled = false;
                    }
                }
            });
        },
        didClose: () => {
            // Swal.hideLoading();
        },
        preConfirm: () => {
            const name = document.getElementById('modal-name').value.trim();
            const unit = document.getElementById('modal-unit').value.trim();
            const whatsapp = document.getElementById('modal-whatsapp').value.trim();
            const duration1 = document.getElementById('duration1').checked;
            const durationRadio = duration1 ? '1' : '2';
            let hourEnd = '';
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            if (!name || !unit || !whatsapp) {
                Swal.showValidationMessage(`${window.bookingLang.requiredFields}`);
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
                name,
                unit: user.unit,
                whatsapp: user.whatsapp,
                durationRadio,
                _token: csrfToken
            };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
        let UrlParam = "";
        if (typeof user.is_admin === "string") {
            user.is_admin = parseInt(user.is_admin, 10) || 0;
        }
        if (user.is_admin) {
            UrlParam = "/store";
        }

        // Tampilkan loading sebelum AJAX
        Swal.fire({
            title: `Saving...`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // AJAX POST ke route booking.store
        fetch('booking' + UrlParam, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(result.value)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                sendNotif(res.notifId);
                Swal.fire(`${window.bookingLang.success}`, res.message, 'success');
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
                Swal.fire(`${window.bookingLang.failed}`, res.error || `${window.bookingLang.errorBooking}`, 'error');
            }
        })
        .catch(() => {
            Swal.fire(`${window.bookingLang.failed}`, `${window.bookingLang.errorGeneral}`, 'error');
        });
    }
});
}

function sendNotif(idNotif) {
  fetch('send-notification/' + idNotif, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
.then(res => res.json())
.then(res => {
    if(res.success){
        console.log('Notifikasi berhasil dikirim!');
    }else{
        console.log('Gagal: ' + (res.error || ''));
    }
});
}

// Bottom nav logic: highlight current
const navBtns = document.querySelectorAll('.nav-btn');
navBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    navBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    console.log(btn.id);
    // Redirect jika tombol My Booking ditekan
    if (btn.id === 'nav-booking') {
      window.location.href = 'booking'; // Ganti dengan URL tujuan yang diinginkan
    }else if (btn.id === 'nav-mybooking') {
      window.location.href = 'mybooking'; // Ganti dengan URL tujuan yang diinginkan
    }else if (btn.id === 'nav-profile') {
      window.location.href = 'profil'; // Ganti dengan URL tujuan yang diinginkan
    }
    // TODO: switch content for each menu if needed
    // For now, only highlight
  });
});

// Optional: Make sure bottom nav is always visible above keyboard on mobile
window.addEventListener('resize', () => {
  document.body.style.paddingBottom = document.querySelector('.bottom-nav').offsetHeight + 6 + 'px';
});

// Ganti handle slot button dengan openBookingModal
const cancelButtons = document.querySelectorAll('.btn-cancel-booking');
cancelButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    const bookingId = btn.dataset.bookingid || '';
    const datebook = btn.dataset.datebook || '';

    openCancelledModal({ bookingId,datebook });
  });
});

// Fungsi modal SweetAlert2 booking
function openCancelledModal({ bookingId,datebook }) {
    Swal.fire({
      title: `${window.bookingLang.cancelTitle}`,
      html: `${window.bookingLang.cancelText} <b>${datebook}</b>?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: `${window.bookingLang.cancelConfirm}`,
      cancelButtonText: `${window.bookingLang.cancelCancel}`,
      customClass: {
        popup: 'swal2-booking-modal'
      },
      focusConfirm: false
    }).then((result) => {
      if (result.isConfirmed) {
        // AJAX POST ke route pembatalan booking
        fetch(`booking/cancel/${bookingId}`, {
          method: 'POST',
          headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })
        .then(res => res.json())
        .then(res => {
          if(res.success) {
            // Tampilkan loading sebelum AJAX
            Swal.fire({
                title: `Saving...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            sendNotif(res.notifId);
            setTimeout(() => {
              Swal.fire(`${window.bookingLang.success}`, res.message, 'success').then(() => {
                window.location.href = 'mybooking';
              });
            }, 3000);
          } else {
              Swal.fire(`${window.bookingLang.failed}`, res.error || `${window.bookingLang.errorBooking}`, 'error');
            }
        })
        .catch(() => {
            Swal.fire(`${window.bookingLang.failed}`, `${window.bookingLang.errorGeneral}`, 'error');
        });
      }
    });
}