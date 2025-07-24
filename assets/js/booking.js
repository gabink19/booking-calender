// Helper: get 7 days from today
    function getNext7Days() {
        const days = [];
        const today = new Date();
        const hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        for (let i = 0; i < 7; i++) {
            const d = new Date(today);
            d.setDate(today.getDate() + i);
            const label = `${hari[d.getDay()]}<br> ${d.getDate().toString().padStart(2,'0')}`;
            days.push({
                date: d,
                label: label
            });
        }
        return days;
    }

    // Render calendar with custom style
    function renderBookingCalendar() {
        const days = getNext7Days();
        let html = `
        <div class="booking-calendar-responsive">
        <table class="table table-bordered text-center align-middle booking-table" style="width:100%; margin-bottom:0; table-layout:auto;">`;
        // Header
        html += '</tr></thead><tbody>';
        // Second header row for labels
        html += '<tr>';
        html += '<th class="sticky-col"></th>';
        days.forEach((day) => {
            html += `<th>${day.label}</th>`;
        });
        html += '</tr>';
        // Rows for each hour
        const now = new Date();
        for (let hour = 0; hour < 24; hour++) {
            const hourLabel = hour.toString().padStart(2, '0') + ':00';
            html += `<tr>`;
            html += `<td class="sticky-col" style="background:#f8f9fa; font-weight:600; color:#555;">${hourLabel}</td>`;
            days.forEach(day => {
                const cellDate = new Date(day.date.getFullYear(), day.date.getMonth(), day.date.getDate(), hour, 0, 0, 0);
                const cellId = `${day.date.getFullYear()}-${(day.date.getMonth()+1).toString().padStart(2,'0')}-${day.date.getDate().toString().padStart(2,'0')}_${hour.toString().padStart(2,'0')}`;
                let cellClass = 'booking-cell';
                if (cellDate < now) {
                    cellClass += ' disabled-cell';
                }
                // Untuk tabel booking-calendar (bukan tabel jadwal hari ini)
                html += `<td class="${cellClass}" data-date="${cellId}"></td>`;
            });
            html += '</tr>';
        }
        html += '</tbody></table></div>';
        document.getElementById('booking-calendar').innerHTML = html;
        // After rendering, fetch and display bookings
        fetchBookings();
    }

    // Handle cell click
    function handleBookingCellClick(e) {
        if (
            e.target.classList.contains('booking-cell') &&
            !e.target.classList.contains('disabled-cell') &&
            !e.target.classList.contains('past') &&
            !e.target.classList.contains('booked')
        ) {
            const cellKey = e.target.getAttribute('data-date'); // format: YYYY-MM-DD_HH
            if (!cellKey) return;
            const [date, hour] = cellKey.split('_');
            openBookingModal({date, hour});
        }
    }

    // Hover effect for booking cells
    function addBookingCellHover() {
        document.getElementById('booking-calendar').addEventListener('mouseover', function(e) {
            if (e.target.classList.contains('booking-cell')) {
                if (e.target.classList.contains('disabled-cell')) {
                    e.target.style.background = '#e9ecef';
                } else if (!e.target.textContent) {
                    e.target.style.background = '#f1f3f4';
                }
            }
        });
        document.getElementById('booking-calendar').addEventListener('mouseout', function(e) {
            if (e.target.classList.contains('booking-cell')) {
                // Kembalikan warna sesuai status cell
                if (e.target.textContent) {
                    // Sudah booking: merah
                    e.target.style.background = '#f8d7da';
                    e.target.style.color = '#721c24';
                    e.target.style.fontWeight = '600';
                    e.target.style.border = '2px solid #dc3545';
                } else {
                    // Cek waktu cell
                    const cellKey = e.target.getAttribute('data-date');
                    const [dateStr, hourStr] = cellKey.split('_');
                    const cellDate = new Date(dateStr + 'T' + hourStr.padStart(2,'0') + ':00:00');
                    const now = new Date();
                    if (cellDate < now) {
                        // Sudah lewat dan tidak ada booking: abu-abu
                        e.target.style.background = '#e9ecef';
                        e.target.style.color = '#aaa';
                        e.target.style.fontWeight = '400';
                        e.target.style.border = '1px solid #dee2e6';
                    } else {
                        // Belum lewat dan kosong: hijau
                        e.target.style.background = '#e3fcec';
                        e.target.style.color = '#218838';
                        e.target.style.fontWeight = '600';
                        e.target.style.border = '2px solid #28a745';
                    }
                }
            }
        });
    }

    // Init calendar on About Us slide show
    document.addEventListener('DOMContentLoaded', function() {
        renderBookingCalendar();
        document.getElementById('booking-calendar').addEventListener('click', handleBookingCellClick);
        addBookingCellHover();
    });

// Untuk membuka modal booking:
function openBookingModal({date, hour}) {
    Swal.fire({
        title: 'Booking',
        html: `
        <div id="bookingFormSwal">
            <input type="hidden" id="modal-date" value="${date}">
            <input type="hidden" id="modal-hour" value="${hour}">
            <input type="hidden" id="modal-hour-end">
            <div class="mb-2 row align-items-center">
              <label class="col-4 col-form-label" style="text-align:right;">Durasi :</label>
              <div class="col-8 d-flex align-items-center gap-2">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="durationRadio" id="duration1" value="1" checked>
                  <label class="form-check-label" for="duration1">1 Jam</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="durationRadio" id="duration2" value="2">
                  <label class="form-check-label" for="duration2" id="labelduration2">2 Jam</label>
                </div>
              </div>
            </div>
            <div class="mb-2 row align-items-center">
              <label class="col-4 col-form-label" style="text-align:right;">Tanggal :</label>
              <div class="col-8">
                <input type="text" class="form-control" id="modal-date-display" value="${date}" disabled>
              </div>
            </div>
            <div class="mb-2 row align-items-center">
              <label class="col-4 col-form-label" style="text-align:right;">Jam :</label>
              <div class="col-8 d-flex align-items-center gap-1">
                <input type="text" class="form-control" id="modal-hour-display" style="width:70px;" value="${hour.padStart(2,'0')}:00" disabled>
                <span id="sampai-label" style="margin:0 4px; display:none;"> dan </span>
                <input type="text" class="form-control" id="modal-hour-end-display" style="width:70px; display:none;" disabled>
              </div>
            </div>
            <div class="mb-2 row align-items-center">
              <label for="modal-name" class="col-4 col-form-label" style="text-align:right;">Nama :</label>
              <div class="col-8">
                <input type="text" class="form-control" id="modal-name" required>
              </div>
            </div>
            <div class="mb-2 row align-items-center">
              <label for="modal-whatsapp" class="col-4 col-form-label" style="text-align:right;">No. Whatsapp :</label>
              <div class="col-8">
                <input type="text" class="form-control" id="modal-whatsapp" required>
              </div>
            </div>
            <div class="mb-2 row align-items-center">
              <label for="modal-unit" class="col-4 col-form-label" style="text-align:right;">No. Unit :</label>
              <div class="col-8">
                <input type="number" class="form-control" id="modal-unit" required>
              </div>
            </div>
        </div>
        `,
        customClass: {
            popup: 'swal2-booking-modal'
        },
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan Booking',
        cancelButtonText: 'Batal',
        didOpen: () => {
            // Event radio untuk jam akhir
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
                        document.getElementById('modal-hour-end-display').value = (jam+1).toString().padStart(2,'0') + ':00';
                        document.getElementById('modal-hour-end-display').style.display = '';
                        document.getElementById('sampai-label').style.display = '';
                    }
                }
            });
        },
        preConfirm: () => {
            const name = document.getElementById('modal-name').value.trim();
            const whatsapp = document.getElementById('modal-whatsapp').value.trim();
            const unit = document.getElementById('modal-unit').value.trim();
            const duration1 = document.getElementById('duration1').checked;
            let hourEnd = '';
            if (!unit || !name || !whatsapp) {
                Swal.showValidationMessage('Semua field harus diisi!');
                return false;
            }
            if (!/^\d+$/.test(whatsapp)) {
                Swal.showValidationMessage('No. Whatsapp harus berupa angka!');
                return false;
            }
            if (!duration1) {
                const jam = parseInt(hour, 10);
                hourEnd = (jam + 1).toString().padStart(2, '0');
            }
            return { 
                date, 
                hour, 
                hourEnd, 
                unit, 
                name, 
                whatsapp 
            };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            saveBooking(result.value);
        }
    });
}


// AJAX: Save booking
    function saveBooking({ date, hour, hourEnd, unit, name, whatsapp }, cellEl) {
      fetch('booking-api.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'save', date, hour, hourEnd, unit, name, whatsapp })
      })
      .then(res => res.json())
      .then(function(data) {
          if (data.success === true) {
                fetchBookings();
                Swal.fire('Data tersimpan', 'Booking berhasil disimpan!', 'success');
          }
          if (!data.success) {
                fetchBookings();
                Swal.fire('Data Tidak Tersimpan', data.error, 'warning');
          }
      })
      .catch((err) => {
          console.error('Fetch error:', err);
          Swal.fire('Terjadi kesalahan', 'Terjadi kesalahan saat menyimpan booking!', 'error');
      });
  }

    // AJAX: Fetch bookings
    function fetchBookings() {
      fetch('booking-api.php?action=load')
      .then(res => res.json())
      .then(data => {
        const now = new Date();
        if (data.success && data.bookings) {
          // Set warna untuk cell yang ada booking (selalu merah)
          const bookedCells = new Set();
          for (const key in data.bookings) {
            const booking = data.bookings[key];
            const cellKey = key.split('_').slice(0,2).join('_');
            const cell = document.querySelector('.booking-cell[data-date="' + cellKey + '"]');
            if (cell) {
              cell.textContent = booking.unit;
              cell.classList.add('booked');
              cell.classList.remove('disabled-cell');
              cell.style.background = '';
              cell.style.color = '';
              cell.style.fontWeight = '';
              cell.style.border = '';
              bookedCells.add(cellKey);
            }
          }
          // Warnai cell kosong yang masih bisa diisi dengan hijau, dan yang sudah lewat tanpa booking jadi abu-abu
          document.querySelectorAll('.booking-cell').forEach(cell => {
            const cellKey = cell.getAttribute('data-date');
            if (!cell.textContent) {
                // Cek waktu cell
                const [dateStr, hourStr] = cellKey.split('_');
                const cellDate = new Date(dateStr + 'T' + hourStr.padStart(2,'0') + ':00:00');
                // Ubah: booking masih bisa jika jam sama dengan jam sekarang
                if (
                    cellDate.getFullYear() < now.getFullYear() ||
                    (cellDate.getFullYear() === now.getFullYear() && cellDate.getMonth() < now.getMonth()) ||
                    (cellDate.getFullYear() === now.getFullYear() && cellDate.getMonth() === now.getMonth() && cellDate.getDate() < now.getDate()) ||
                    (cellDate.getFullYear() === now.getFullYear() && cellDate.getMonth() === now.getMonth() && cellDate.getDate() === now.getDate() && cellDate.getHours() < now.getHours())
                ) {
                    // Sudah lewat dan tidak ada booking: abu-abu
                    cell.classList.add('disabled-cell');
                    cell.style.background = '#e9ecef';
                    cell.style.color = '#aaa';
                    cell.style.fontWeight = '400';
                    cell.style.border = '1px solid #dee2e6';
                } else {
                    // Belum lewat dan kosong: hijau
                    cell.classList.remove('disabled-cell');
                    cell.classList.remove('booked');
                    // reset style
                    cell.style.background = '';
                    cell.style.color = '';
                    cell.style.fontWeight = '';
                    cell.style.border = '';
                }
            } else {
              cell.classList.remove('disabled-cell');
            }
          });
        } else if (data.error) {
          alert('Gagal memuat data booking: ' + data.error);
        }
      })
      .catch(() => {
        alert('Terjadi kesalahan saat memuat data booking!');
      });
    }
    $(document).ready(function() {
    function renderTodayBookingTable() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const dateStr = `${yyyy}-${mm}-${dd}`;
        // Buat array jam 00:00 - 23:00
        const hours = [];
        for (let i = 0; i < 24; i++) {
            hours.push(i.toString().padStart(2, '0') + ':00');
        }
        // Tabel 4 kolom × 6 baris (total 24 jam)
        $.ajax({
            url: 'booking-api.php',
            method: 'GET',
            data: { action: 'load', today: 1 },
            dataType: 'json',
            success: function(response) {
                let bookings = response.bookings || {};
                // Format tanggal: Senin, 01 Januari 2025
                const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const bulan = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];
                const tglObj = new Date(dateStr);
                const hariStr = hari[tglObj.getDay()];
                const tglStr = tglObj.getDate().toString().padStart(2, '0');
                const blnStr = bulan[tglObj.getMonth()];
                const thnStr = tglObj.getFullYear();
                const headerTanggal = `${hariStr}, ${tglStr} ${blnStr} ${thnStr}`;

                let html = '<table class="table table-bordered booking-table" style="min-height:550px">';
                // Header merge dengan format lokal
                html += `<thead>
                    <tr>
                        <th colspan="4" style="text-align:center; font-size:1.1em;">
                            ${headerTanggal}
                        </th>
                    </tr>
                </thead><tbody>`;
                // 6 baris × 4 kolom
                for (let row = 0; row < 6; row++) {
                    html += '<tr>';
                    for (let col = 0; col < 4; col++) {
                        const idx = row * 4 + col;
                        if (idx < hours.length) {
                            const jam = hours[idx];
                            const jamNum = parseInt(jam.split(':')[0], 10);
                            const key = `${dateStr}_${jamNum.toString().padStart(2, '0')}_1`;
                            let cls = 'available', label = 'Kosong';
                            if (bookings[key]) {
                                cls = 'booked';
                                label = 'Booked';
                            } else if (jamNum < today.getHours()) {
                                cls = 'past';
                                label = 'Lewat';
                            }
                            // Hilangkan pointer dan event
                            html += `<td class="booking-cell ${cls}" style="vertical-align:middle; text-align:center; position:relative; min-width:80px; height:60px; cursor:default; pointer-events:none;">
                                <span style="position:absolute; top:4px; left:6px; font-size:0.75em; color:#888;">${jam}</span>
                                <span style="display:inline-block; margin-top:12px;">${label}</span>
                            </td>`;
                        } else {
                            html += '<td></td>';
                        }
                    }
                    html += '</tr>';
                }
                html += '</tbody></table>';
                $('#today-booking-table').html(html);
            }
        });
    }
    renderTodayBookingTable();
});