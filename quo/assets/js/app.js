$(document).ready(function() {
    // Hanya jalankan kode ini jika kita berada di halaman form penawaran
    if ($('#quotation-form').length === 0) {
        return; 
    }

    // ======================================================
    // FUNGSI-FUNGSI UTAMA (Helpers)
    // ======================================================
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    };

    function calculateAll() {
        let subtotal = 0;
        $('#quotation-items-table tbody tr').each(function() {
            const row = $(this);
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const price = parseFloat(row.find('.price').val()) || 0;
            const discountValue = parseFloat(row.find('.discount-value').val()) || 0;
            const discountType = row.find('.discount-type').val();
            let itemDiscount = (discountType === 'PERCENT') ? (price * quantity) * (discountValue / 100) : (discountValue * quantity);
            const rowTotal = (price * quantity) - itemDiscount;
            row.find('.row-total').text(formatRupiah(rowTotal));
            subtotal += rowTotal;
        });

        const overallDiscountValue = parseFloat($('#overall-discount-value').val()) || 0;
        const overallDiscountType = $('#overall-discount-type').val();
        let overallDiscountAmount = (overallDiscountType === 'PERCENT') ? subtotal * (overallDiscountValue / 100) : overallDiscountValue;
        const totalAfterDiscount = subtotal - overallDiscountAmount;
        let ppnAmount = ($('#issuer').val() === 'CV') ? totalAfterDiscount * 0.11 : 0;
        const grandTotal = totalAfterDiscount + ppnAmount;

        $('#summary-subtotal').text(formatRupiah(subtotal));
        $('#summary-ppn').text(formatRupiah(ppnAmount));
        $('#summary-grandtotal').text(formatRupiah(grandTotal));
    }
    
    function collectFormData() {
        const formData = {
            quotation_id: $('#quotation_id').val() || null,
            customer_id: $('#customer').val(),
            issuer: $('#issuer').val(),
            notes: $('#notes').val(),
            overall_discount_type: $('#overall-discount-type').val(),
            overall_discount_value: $('#overall-discount-value').val(),
            items: []
        };
        $('#quotation-items-table tbody tr').each(function() {
            const row = $(this);
            formData.items.push({
                barang_id: row.data('item-id'),
                name: row.find('.item-name').val(),
                desc: row.find('.item-desc').val(),
                quantity: row.find('.quantity').val(),
                price: row.find('.price').val(),
                discount_value: row.find('.discount-value').val(),
                discount_type: row.find('.discount-type').val()
            });
        });
        return formData;
    }

    // ======================================================
    // EVENT LISTENERS (Pemicu Aksi)
    // ======================================================
    
    
    // <td><input type="text" class="form-control form-control-sm item-name" value="${data.name}"><textarea class="form-control form-control-sm mt-1 item-desc">${data.desc || ''}</textarea></td>
    
    $('#quotation-form').on('input change', 'input, select, textarea', calculateAll);

    // Fungsi untuk re-number baris tabel
    function renumberRows() {
        $('#quotation-items-table tbody tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });
    }

    $('#add-item-btn').on('click', function() {
        const selected = $('#item-selector').find('option:selected');
        if (!selected.val()) { alert('Silakan pilih barang.'); return; }
        const data = selected.data();
        
        const rowNum = $('#quotation-items-table tbody tr').length + 1;
        const rowHtml = `
            <tr data-item-id="${selected.val()}">
                <td class="text-center drag-handle" style="cursor: grab; vertical-align: middle;"><i class="bi bi-grip-vertical" style="font-size: 16px; color: #94a3b8;"></i></td>
                <td class="text-center row-number">${rowNum}</td>
                <td><input type="text" class="form-control form-control-sm item-name" value="${data.name}"><textarea class="form-control form-control-sm mt-1 item-desc" style="font-size:10px;" disabled>${data.desc || ''}</textarea></td>
                <td class="text-center"><input type="number" class="form-control form-control-sm quantity text-center" value="1" min="1"></td>
                <td class="text-center"><input type="number" class="form-control form-control-sm price text-center" value="${data.price}" min="0"></td>
                <td><div class="input-group input-group-sm"><input type="number" class="form-control discount-value text-center" value="0" min="0"><select class="form-select discount-type" style="flex-grow:0; width:44px;"><option value="AMOUNT">Rp</option><option value="PERCENT">%</option></select></div></td>
                <td class="text-center row-total fw-bold"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button></td>
            </tr>`;
            
        $('#quotation-items-table tbody').append(rowHtml);
        $('#addItemModal').modal('hide');
        $('#item-selector').val('');
        calculateAll();
    });

    $('#quotation-items-table').on('click', '.remove-item-btn', function() {
        $(this).closest('tr').remove();
        renumberRows();
        calculateAll();
    });

    // Listener untuk tombol simpan manual (DRAFT & FINAL)
    $('#save-draft-btn, #save-final-btn').on('click', function() {
        const dataToSave = collectFormData();
        if (!dataToSave.customer_id) {
            alert('Customer harus dipilih!');
            return;
        }
        if (dataToSave.items.length === 0) {
            alert('Harus ada minimal 1 barang dalam penawaran.');
            return;
        }

        const button = $(this);
        const originalText = button.text();
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
        
        dataToSave.action = 'save_quotation';
        dataToSave.status = button.attr('id') === 'save-draft-btn' ? 'DRAFT' : 'FINAL';
        
        $.ajax({
            url: 'actions.php',
            method: 'POST',
            data: JSON.stringify(dataToSave),
            contentType: 'application/json',
            success: function(res) {
                if (res.success) {
                    alert('Penawaran berhasil disimpan! Kode: ' + (res.quotation_code || 'DRAFT'));
                    window.location.href = '../../dashboard.php';
                } else {
                    alert('Gagal menyimpan: ' + (res.message || 'Error tidak diketahui.'));
                    button.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('Terjadi kesalahan koneksi.');
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // ======================================================
    // INISIALISASI
    // ======================================================
    calculateAll();

    // ======================================================
    // DRAG & DROP — SortableJS untuk reorder baris tabel
    // ======================================================
    const tableBody = document.querySelector('#quotation-items-table tbody');
    if (tableBody && typeof Sortable !== 'undefined') {
        Sortable.create(tableBody, {
            handle: '.drag-handle',
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function() {
                renumberRows();
                calculateAll();
            }
        });
    }

    // Inisialisasi Select2 pada dropdown "Pilih Barang" di modal
    // agar bisa diketik/search, bukan hanya scroll
    $('#addItemModal').on('shown.bs.modal', function () {
        // Destroy dulu kalau sudah ada instance sebelumnya
        if ($('#item-selector').hasClass('select2-hidden-accessible')) {
            $('#item-selector').select2('destroy');
        }

        $('#item-selector').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Ketik untuk cari barang --',
            allowClear: true,
            minimumInputLength: 2,
            width: '100%',
            dropdownParent: $('#addItemModal'),
            ajax: {
                url: '/quo/pages/quotation/ajax_search_item.php',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { term: params.term }; },
                processResults: function (data) {
                    return {
                        results: $.map(data.results, function (item) {
                            return {
                                text: item.text, id: item.id, price: item.price,
                                name: item.name, desc: item.desc, unit: item.unit
                            }
                        })
                    };
                },
                cache: true
            },
            language: {
                noResults: function() {
                    return 'Barang tidak ditemukan';
                },
                searching: function() {
                    return 'Mencari...';
                },
                inputTooShort: function() {
                    return 'Ketik minimal 2 huruf untuk mencari...';
                }
            }
        });

        // Auto-focus ke search box saat modal terbuka
        setTimeout(function() {
            $('#item-selector').select2('open');
        }, 300);
    });

    // Reset Select2 saat modal ditutup
    $('#addItemModal').on('hidden.bs.modal', function () {
        $('#item-selector').val('').trigger('change');
        // Reset posisi modal saat ditutup
        const dialog = document.querySelector('#addItemModal .draggable-modal');
        if (dialog) {
            dialog.style.transform = '';
            dialog.classList.remove('is-dragging');
        }
    });

    // ================================================
    // DRAG & DROP Modal — Smooth 60fps
    // ================================================
    (function() {
        const handle = document.getElementById('modal-drag-handle');
        if (!handle) return;

        const dialog = handle.closest('.draggable-modal');
        if (!dialog) return;

        let isDragging = false;
        let startX, startY, currentX = 0, currentY = 0;
        let rafId = null;

        // Prevent touch scroll on the handle
        handle.style.touchAction = 'none';

        function updatePosition() {
            dialog.style.transform = `translate3d(${currentX}px, ${currentY}px, 0)`;
            rafId = null;
        }

        function scheduleUpdate() {
            if (rafId === null) {
                rafId = requestAnimationFrame(updatePosition);
            }
        }

        handle.addEventListener('pointerdown', function(e) {
            if (e.target.closest('.btn-close')) return;
            
            isDragging = true;
            startX = e.clientX - currentX;
            startY = e.clientY - currentY;

            dialog.classList.add('is-dragging');
            dialog.style.willChange = 'transform';
            handle.setPointerCapture(e.pointerId);
            e.preventDefault();
        });

        handle.addEventListener('pointermove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
            currentX = e.clientX - startX;
            currentY = e.clientY - startY;
            scheduleUpdate();
        });

        handle.addEventListener('pointerup', function() {
            if (!isDragging) return;
            isDragging = false;
            dialog.classList.remove('is-dragging');
            dialog.style.willChange = '';
        });

        handle.addEventListener('pointercancel', function() {
            isDragging = false;
            dialog.classList.remove('is-dragging');
            dialog.style.willChange = '';
        });

        // Reset saat modal buka/tutup
        $('#addItemModal').on('show.bs.modal', function() {
            currentX = 0;
            currentY = 0;
            dialog.style.transform = '';
        });

        $('#addItemModal').on('hidden.bs.modal', function() {
            currentX = 0;
            currentY = 0;
            dialog.style.transform = '';
            dialog.style.willChange = '';
        });
    })();
});