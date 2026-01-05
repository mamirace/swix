/**
 * Page User List
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor;

  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;

  // Variable declaration for table
  const dt_user_table = document.querySelector('.datatables-users'),
    userView = 'app-user-view-account.html',
    statusObj = {
      'Active': { title: 'Active', class: 'bg-label-success' },
      'Inactive': { title: 'Inactive', class: 'bg-label-secondary' },
      'Pending': { title: 'Pending', class: 'bg-label-warning' }
    };
  var select2 = $('.select2');

  if (select2.length) {
    var $this = select2;
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: 'Select Country',
      dropdownParent: $this.parent()
    });
  }

  // Users datatable
  if (dt_user_table) {
    const dt_user = new DataTable(dt_user_table, {
      ajax: '/api/kullanicilar', // API endpoint'ten veri al
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'full_name' },
        { data: 'role' },
        { data: 'organization' },
        { data: 'username' },
        { data: 'status' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          searchable: false,
          responsivePriority: 4,
          checkboxes: true,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        },
        {
          targets: 2,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            var name = full['full_name'];
            var email = full['email'];

            // Creates full output for row
            var row_output =
              '<div class="d-flex justify-content-start align-items-center user-name">' +
              '<div class="d-flex flex-column">' +
              '<a href="' +
              userView +
              '" class="text-heading text-truncate"><span class="fw-medium">' +
              name +
              '</span></a>' +
              '<small>' +
              email +
              '</small>' +
              '</div>' +
              '</div>';
            return row_output;
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            var role = full['role'];
            var roleBadgeObj = {
              Subscriber: '<i class="icon-base ti tabler-crown icon-md text-primary me-2"></i>',
              Author: '<i class="icon-base ti tabler-edit icon-md text-warning me-2"></i>',
              Maintainer: '<i class="icon-base ti tabler-user icon-md text-success me-2"></i>',
              Editor: '<i class="icon-base ti tabler-chart-pie icon-md text-info me-2"></i>',
              Admin: '<i class="icon-base ti tabler-device-desktop icon-md text-danger me-2"></i>'
            };
            return (
              "<span class='text-truncate d-flex align-items-center text-heading'>" +
              (roleBadgeObj[role] || '') + // Ensures badge exists for the role
              role +
              '</span>'
            );
          }
        },
        {
          // Organization
          targets: 4,
          render: function (data, type, full, meta) {
            const organization = full['organization'];
            return '<span class="text-heading">' + (organization || '-') + '</span>';
          }
        },
        {
          // Username
          targets: 5,
          render: function (data, type, full, meta) {
            const username = full['username'];
            return '<span class="text-heading">' + (username || '-') + '</span>';
          }
        },
        {
          // User Status
          targets: 6,
          render: function (data, type, full, meta) {
            const status = full['status'];
            const statusInfo = statusObj[status] || { title: status, class: 'bg-label-secondary' };

            return (
              '<span class="badge ' +
              statusInfo.class +
              '" text-capitalized>' +
              statusInfo.title +
              '</span>'
            );
          }
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: (data, type, full, meta) => {
            return `
              <div class="d-flex align-items-center">
                <a href="javascript:;" class="btn btn-text-secondary rounded-pill waves-effect btn-icon delete-record">
                  <i class="icon-base ti tabler-trash icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-text-secondary rounded-pill waves-effect btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical icon-22px"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end m-0">
                  <a href="javascript:;" class="dropdown-item edit-user" data-user-id="${full.id}" data-user-data='${JSON.stringify(full)}'>Edit</a>
                  <a href="javascript:;" class="dropdown-item toggle-status" data-user-id="${full.id}" data-current-status="${full.status}">
                    ${full.status === 'Active' ? 'Suspend' : 'Activate'}
                  </a>
                </div>
              </div>
            `;
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [[2, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: '_MENU_'
              }
            }
          ]
        },
        topEnd: {
          features: [
            {
              search: {
                placeholder: 'Search User',
                text: '_INPUT_'
              }
            },
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-secondary dropdown-toggle',
                  text: '<span class="d-flex align-items-center gap-2"><i class="icon-base ti tabler-upload icon-xs"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>`,
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Check if inner is HTML content
                            if (inner.indexOf('<') > -1) {
                              const parser = new DOMParser();
                              const doc = parser.parseFromString(inner, 'text/html');

                              // Get all text content
                              let text = '';

                              // Handle specific elements
                              const userNameElements = doc.querySelectorAll('.user-name');
                              if (userNameElements.length > 0) {
                                userNameElements.forEach(el => {
                                  // Get text from nested structure
                                  const nameText =
                                    el.querySelector('.fw-medium')?.textContent ||
                                    el.querySelector('.d-block')?.textContent ||
                                    el.textContent;
                                  text += nameText.trim() + ' ';
                                });
                              } else {
                                // Get regular text content
                                text = doc.body.textContent || doc.body.innerText;
                              }

                              return text.trim();
                            }

                            return inner;
                          }
                        }
                      },
                      customize: function (win) {
                        win.document.body.style.color = config.colors.headingColor;
                        win.document.body.style.borderColor = config.colors.borderColor;
                        win.document.body.style.backgroundColor = config.colors.bodyBg;
                        const table = win.document.body.querySelector('table');
                        table.classList.add('compact');
                        table.style.color = 'inherit';
                        table.style.borderColor = 'inherit';
                        table.style.backgroundColor = 'inherit';
                      }
                    },
                    {
                      extend: 'csv',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>Csv</span>`,
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle user-name elements specifically
                            const userNameElements = doc.querySelectorAll('.user-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-spreadsheet me-1"></i>Excel</span>`,
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle user-name elements specifically
                            const userNameElements = doc.querySelectorAll('.user-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-description me-1"></i>Pdf</span>`,
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle user-name elements specifically
                            const userNameElements = doc.querySelectorAll('.user-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'copy',
                      text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle user-name elements specifically
                            const userNameElements = doc.querySelectorAll('.user-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    }
                  ]
                },
                {
                  text: '<span class="d-flex align-items-center gap-2"><i class="icon-base ti tabler-plus icon-xs"></i> <span class="d-none d-sm-inline-block">Add New Record</span></span>',
                  className: 'add-new btn btn-primary',
                  attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#offcanvasAddUser'
                  }
                }
              ]
            }
          ]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search User',
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
          first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
          last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
        }
      },
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              div.appendChild(table);
              table.classList.add('table');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              return div;
            }
            return false;
          }
        }
      },
      initComplete: function () {
        const api = this.api();

        // Helper function to create a select dropdown and append options
        const createFilter = (columnIndex, containerClass, selectId, defaultOptionText) => {
          const column = api.column(columnIndex);
          const select = document.createElement('select');
          select.id = selectId;
          select.className = 'form-select text-capitalize';
          select.innerHTML = `<option value="">${defaultOptionText}</option>`;
          document.querySelector(containerClass).appendChild(select);

          // Add event listener for filtering
          select.addEventListener('change', () => {
            const val = select.value ? `^${select.value}$` : '';
            column.search(val, true, false).draw();
          });

          // Populate options based on unique column data
          const uniqueData = Array.from(new Set(column.data().toArray())).sort();
          uniqueData.forEach(d => {
            const option = document.createElement('option');
            option.value = d;
            option.textContent = d;
            select.appendChild(option);
          });
        };

        // Role filter
        createFilter(3, '.user_role', 'UserRole', 'Select Role');

        // Plan filter
        createFilter(4, '.user_plan', 'UserPlan', 'Select Plan');

        // Status filter
        const statusFilter = document.createElement('select');
        statusFilter.id = 'FilterTransaction';
        statusFilter.className = 'form-select text-capitalize';
        statusFilter.innerHTML = '<option value="">Select Status</option>';
        document.querySelector('.user_status').appendChild(statusFilter);
        statusFilter.addEventListener('change', () => {
          const val = statusFilter.value ? `^${statusFilter.value}$` : '';
          api.column(6).search(val, true, false).draw();
        });

        const statusColumn = api.column(6);
        const uniqueStatusData = Array.from(new Set(statusColumn.data().toArray())).sort();
        uniqueStatusData.forEach(d => {
          const option = document.createElement('option');
          option.value = statusObj[d]?.title || d;
          option.textContent = statusObj[d]?.title || d;
          option.className = 'text-capitalize';
          statusFilter.appendChild(option);
        });
      }
    });

    //? The 'delete-record' class is necessary for the functionality of the following code.
    function deleteRecord(event) {
      let row = document.querySelector('.dtr-expanded');
      if (event) {
        row = event.target.parentElement.closest('tr');
      }
      if (row) {
        try {
          // DataTable row nesnesini al
          const dtRow = dt_user.row(row);
          const rowData = dtRow.data();
          const userId = rowData.id;
          
          console.log('Silinecek kullanıcı:', { userId, rowData });
          
          if (!userId) {
            Swal.fire({
              icon: 'error',
              title: 'Hata!',
              text: 'Kullanıcı ID bulunamadı'
            });
            return;
          }
          
          // Silme onayı iste
          Swal.fire({
            title: 'Silmek istediğinizden emin misiniz?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Sil!',
            cancelButtonText: 'İptal'
          }).then((result) => {
            if (result.isConfirmed) {
              // API'ye DELETE isteği gönder
              fetch(`/api/kullanicilar/sil/${userId}`, {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'application/json'
                }
              })
              .then(response => {
                console.log('Delete Response Status:', response.status);
                return response.json();
              })
              .then(data => {
                console.log('Delete API Yanıtı:', data);
                if (data.status === 'success' || data.message) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Kullanıcı silindi',
                    timer: 1500,
                    timerProgressBar: true
                  }).then(() => {
                    // Tabloyu yenile
                    dt_user.ajax.reload();
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.error || 'Silme işlemi başarısız'
                  });
                }
              })
              .catch(error => {
                console.error('Delete Hatası:', error);
                Swal.fire({
                  icon: 'error',
                  title: 'Hata!',
                  text: 'Silme işleminde hata oluştu: ' + error.message
                });
              });
            }
          });
        } catch (error) {
          console.error('deleteRecord Hatası:', error);
          Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Hata oluştu: ' + error.message
          });
        }
      }
    }

    function bindDeleteEvent() {
      const userListTable = document.querySelector('.datatables-users');
      const modal = document.querySelector('.dtr-bs-modal');

      if (userListTable && userListTable.classList.contains('collapsed')) {
        if (modal) {
          modal.addEventListener('click', function (event) {
            if (event.target.parentElement.classList.contains('delete-record')) {
              deleteRecord();
              const closeButton = modal.querySelector('.btn-close');
              if (closeButton) closeButton.click(); // Simulates a click on the close button
            }
          });
        }
      } else {
        const tableBody = userListTable?.querySelector('tbody');
        if (tableBody) {
          tableBody.addEventListener('click', function (event) {
            if (event.target.parentElement.classList.contains('delete-record')) {
              deleteRecord(event);
            }
          });
        }
      }
    }

    // Initial event binding
    bindDeleteEvent();

    // Re-bind events when modal is shown or hidden
    document.addEventListener('show.bs.modal', function (event) {
      if (event.target.classList.contains('dtr-bs-modal')) {
        bindDeleteEvent();
      }
    });

    document.addEventListener('hide.bs.modal', function (event) {
      if (event.target.classList.contains('dtr-bs-modal')) {
        bindDeleteEvent();
      }
    });
  }

  // Filter form control to default size
  // ? setTimeout used for user-list table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm', classToAdd: 'ms-0' },
      { selector: '.dt-length', classToAdd: 'mb-md-6 mb-0' },
      {
        selector: '.dt-layout-end',
        classToRemove: 'justify-content-between',
        classToAdd: 'd-flex gap-md-4 justify-content-md-between justify-content-center gap-2 flex-wrap'
      },
      { selector: '.dt-buttons', classToAdd: 'd-flex gap-4 mb-md-0 mb-4' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

    // Delete record
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);

  // Validation & Phone mask
  const phoneMaskList = document.querySelectorAll('.phone-mask');

  // Phone Number
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      phoneMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        phoneMask.value = formatGeneral(cleanValue, {
          blocks: [3, 3, 4],
          delimiters: [' ', ' ']
        });
      });
      registerCursorTracker({
        input: phoneMask,
        delimiter: ' '
      });
    });
  }
  
  // Add New User Form Setup - DOM hazırlandıktan sonra
  let addNewUserForm = null;
  let fv = null;
  
  function setupAddUserForm() {
    addNewUserForm = document.getElementById('addNewUserForm');
    
    if (!addNewUserForm) {
      console.error('❌ addNewUserForm DOM\'da bulunamadı!');
      return;
    }
    
    console.log('✅ addNewUserForm başarıyla bulundu');
    console.log('✅ addNewUserForm HTML:', addNewUserForm.innerHTML.substring(0, 100));
    
    // Form submit handler'ını ekle
    addNewUserForm.addEventListener('submit', function(e) {
      e.preventDefault();
      console.log('✅ Form submit event tetiklendi');
      
      // Form verilerini manuel olarak al ve doğrula
      const isim = document.getElementById('add-user-isim')?.value.trim();
      const soyisim = document.getElementById('add-user-soyisim')?.value.trim();
      const mail = document.getElementById('add-user-email')?.value.trim();
      const kullanici_adi = document.getElementById('add-user-username')?.value.trim();
      const sifre = document.getElementById('add-user-sifre')?.value.trim();
      const firma = document.getElementById('add-user-firma')?.value.trim();
      const role = document.getElementById('user-role')?.value.trim();
      const organization = document.getElementById('user-organization')?.value.trim();
      
      console.log('📋 Form alanları:', { isim, soyisim, mail, kullanici_adi, sifre, firma, role, organization });
      
      // Doğrulama
      if (!isim || !soyisim || !mail || !kullanici_adi || !sifre || !role || !organization) {
        console.log('❌ Form validation başarısız - eksik alanlar var');
        Swal.fire({
          icon: 'error',
          title: 'Hata!',
          text: 'Lütfen tüm alanları doldurunuz'
        });
        return;
      }
      
      console.log('✔️ Form geçerli - veriler gönderilecek');
      
      // Form verilerini topla
      const formData = {
        isim: isim,
        soyisim: soyisim,
        mail: mail,
        kullanici_adi: kullanici_adi,
        sifre: sifre,
        firma: firma,
        role: role,
        organization: organization
      };
      
      console.log('📤 Form verisi gönderiliyor:', formData);
      
      // API'ye gönder
      fetch('/api/kullanicilar/ekle', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      })
      .then(response => {
        console.log('📨 API Yanıtı Status:', response.status);
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
      })
      .then(data => {
        console.log('📥 API Yanıtı:', data);
        console.log('data.status:', data.status, 'data.message:', data.message);
        
        // Başarı kontrol - status veya message ile kontrol et
        if (data.status === 'success' || data.message) {
          console.log('✅ Kullanıcı başarıyla eklendi!');
          
          // Başarı mesajı
          Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: 'Kullanıcı başarıyla eklendi',
            timer: 2000,
            timerProgressBar: true
          }).then(() => {
            // Swal'ın bitmesini bekle sonra sayfayı yenile
            console.log('🔄 Sayfa yenileniyor...');
            location.reload();
          });
          
          // Formu sıfırla
          addNewUserForm.reset();
          
          // Offcanvas'ı kapat
          const offcanvas = document.getElementById('offcanvasAddUser');
          if (offcanvas) {
            const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
            if (bsOffcanvas) {
              bsOffcanvas.hide();
            }
          }
        } else {
          console.log('❌ API hatası:', data.error || 'Bilinmeyen hata');
          Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: data.error || 'Bilinmeyen hata oluştu'
          });
        }
      })
      .catch(error => {
        console.error('❌ Fetch Hatası:', error);
        Swal.fire({
          icon: 'error',
          title: 'Hata!',
          text: 'Kullanıcı eklenirken hata oluştu: ' + error.message
        });
      });
    });
  }
  
  // Sayfayı yükledikten sonra formu setup et
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupAddUserForm);
  } else {
    setupAddUserForm();
  }

  // Edit kullanıcı - modal aç ve verileri doldur
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-user')) {
      e.preventDefault();
      const userId = e.target.getAttribute('data-user-id');
      const userData = JSON.parse(e.target.getAttribute('data-user-data'));
      
      console.log('Edit kullanıcı:', userId, userData);
      
      // full_name'i isim ve soyisime ayır
      let isim = '';
      let soyisim = '';
      if (userData.full_name) {
        const nameParts = userData.full_name.trim().split(' ');
        isim = nameParts[0] || '';
        soyisim = nameParts.slice(1).join(' ') || '';
      }
      
      // Form alanlarını doldur
      document.getElementById('edit-user-id').value = userId;
      document.getElementById('edit-user-isim').value = isim;
      document.getElementById('edit-user-soyisim').value = soyisim;
      document.getElementById('edit-user-email').value = userData.email || '';
      document.getElementById('edit-user-username').value = userData.username || '';
      document.getElementById('edit-user-role').value = userData.role || 'User';
      document.getElementById('edit-user-organization').value = userData.organization || '';
      
      console.log('Form doldurulan veriler:', { userId, isim, soyisim, email: userData.email, username: userData.username, role: userData.role, organization: userData.organization });
      
      // Modal'ı aç
      const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasEditUser'));
      offcanvas.show();
    }
  });

  // Edit form submit
  const editUserForm = document.getElementById('editUserForm');
  if (editUserForm) {
    editUserForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const userId = document.getElementById('edit-user-id').value;
      const isim = document.getElementById('edit-user-isim').value.trim();
      const soyisim = document.getElementById('edit-user-soyisim').value.trim();
      const email = document.getElementById('edit-user-email').value.trim();
      const username = document.getElementById('edit-user-username').value.trim();
      const role = document.getElementById('edit-user-role').value.trim();
      const organization = document.getElementById('edit-user-organization').value.trim();
      
      if (!userId || !isim || !soyisim || !email || !username || !role || !organization) {
        Swal.fire({
          icon: 'error',
          title: 'Hata!',
          text: 'Lütfen tüm alanları doldurunuz'
        });
        return;
      }
      
      const formData = {
        isim, soyisim, email, username, role, organization
      };
      
      console.log('Güncelleme verisi:', { userId, ...formData });
      
      fetch(`/api/kullanicilar/guncelle/${userId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
      })
      .then(data => {
        console.log('API Yanıtı:', data);
        if (data.status === 'success' || data.message) {
          Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: 'Kullanıcı güncellendi',
            timer: 1500,
            timerProgressBar: true
          }).then(() => {
            // Offcanvas'ı kapat
            const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasEditUser'));
            if (offcanvas) offcanvas.hide();
            // Tabloyu yenile
            dt_user.ajax.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: data.error || 'Güncelleme başarısız'
          });
        }
      })
      .catch(error => {
        console.error('Fetch Hatası:', error);
        Swal.fire({
          icon: 'error',
          title: 'Hata!',
          text: 'Güncelleme işleminde hata: ' + error.message
        });
      });
    });
  }

  // Toggle kullanıcı durumu - Suspend/Activate
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('toggle-status')) {
      e.preventDefault();
      const userId = e.target.getAttribute('data-user-id');
      const currentStatus = e.target.getAttribute('data-current-status');
      
      if (confirm(`Bu kullanıcıyı ${currentStatus === 'Active' ? 'Suspend' : 'Activate'} etmek istediğinize emin misiniz?`)) {
        fetch(`/api/kullanicilar/${userId}/toggle-status`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.status) {
            // Tabloyu yenile
            dt_user.ajax.reload();
            // Başarı mesajı
            const Toast = Swal.mixin({
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000
            });
            Toast.fire({
              icon: 'success',
              title: `Kullanıcı ${data.status} olarak değiştirildi`
            });
          }
        })
        .catch(error => {
          console.error('Hata:', error);
          Swal.fire({
            title: 'Hata!',
            text: 'Durumu değiştirirken hata oluştu',
            icon: 'error'
          });
        });
      }
    }
  });
});
