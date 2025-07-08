// js/scripts.js

$(document).ready(function() {
    // تفعيل الـ dropdown لجميع القوائم المنسدلة
    $('.dropdown-toggle').dropdown();

    // وظيفة تشفير/فك تشفير كلمة المرور (خاصة بصفحة الطلاب)
    // نتحقق أولاً ما إذا كان هذا العنصر موجودًا في الصفحة قبل ربط الحدث
    if ($('.toggle-password-icon').length) {
        $('.toggle-password-icon').on('click', function() {
            var passwordTextSpan = $(this).prev('.password-text');
            var originalPassword = $(this).data('original-password');

            if ($(this).hasClass('fa-lock')) {
                // فك التشفير: عرض كلمة المرور الأصلية
                passwordTextSpan.text(originalPassword);
                $(this).removeClass('fa-lock').addClass('fa-lock-open');
            } else {
                // تشفير: استبدال بالنصوص النجمية
                var maskedPassword = '*'.repeat(originalPassword.length);
                passwordTextSpan.text(maskedPassword);
                $(this).removeClass('fa-lock-open').addClass('fa-lock');
            }
        });

        // تشفير كلمات المرور عند تحميل الصفحة (خاصة بصفحة الطلاب)
        $('.toggle-password-icon').each(function() {
            var passwordTextSpan = $(this).prev('.password-text');
            var originalPassword = $(this).data('original-password');
            if (originalPassword) {
                var maskedPassword = '*'.repeat(originalPassword.length);
                passwordTextSpan.text(maskedPassword);
            } else {
                passwordTextSpan.text("");
            }
            $(this).removeClass('fa-lock-open').addClass('fa-lock');
        });
    }
});

// js/scripts.js

$(document).ready(function() {
    // Function to show custom alert message
    function showAlert(message, type) {
        var alertDiv = `
            <div class="alert alert-${type} alert-fixed" role="alert">
                ${message}
            </div>
        `;
        $('body').append(alertDiv);
        setTimeout(function() {
            $('.alert-fixed').fadeOut(500, function() {
                $(this).remove();
            });
        }, 4000); // Alert disappears after 4 seconds
    }

    // Toggle password visibility (if you still need it on other pages)
    $('.toggle-password-icon').on('click', function() {
        var passwordCell = $(this).closest('.password-cell');
        var passwordText = passwordCell.find('.password-text');
        var originalPassword = $(this).data('original-password');

        if (passwordText.text() === '********') {
            passwordText.text(originalPassword);
            $(this).removeClass('fa-eye').addClass('fa-lock');
        } else {
            passwordText.text('********');
            $(this).removeClass('fa-lock').addClass('fa-eye');
        }
    });

    // Handle sidebar active link
    $('.sidebar .nav-link').on('click', function() {
        $('.sidebar .nav-link').removeClass('active');
        $(this).addClass('active');
    });

    // Set active link on page load based on current URL
    var currentPath = window.location.pathname;
    // For local development, often currentPath includes /your_project_folder/
    // We need to get the actual file name, e.g., students.php, index.php
    var fileName = currentPath.substring(currentPath.lastIndexOf('/') + 1);

    // If fileName is empty (e.g., just /your_project_folder/), assume index.php
    if (fileName === '' || fileName.indexOf('inndex.php') !== -1) {
        $('.sidebar .nav-link[href="index.php"]').addClass('active');
    } else {
        $('.sidebar .nav-link').each(function() {
            var linkHref = $(this).attr('href');
            if (linkHref && linkHref.indexOf(fileName) !== -1) {
                $(this).addClass('active');
            }
        });
    }

    /* --- Subjects Page Specific Logic --- */

    var subjectFormModal = $('#subjectFormModal');
    var modalTitle = $('#subjectFormModalLabel');
    var subjectForm = $('#subjectForm');
    var subjectIdInput = $('#subject_id'); // Hidden input for subject ID

    // Open Add Subject Modal
    $('.btn-add-new-subject').on('click', function() {
        subjectForm.trigger('reset'); // Clear form fields
        subjectIdInput.val(''); // Clear hidden ID
        modalTitle.text('إضافة مادة جديدة');
        $('#subject_active').prop('checked', true); // Default active to checked for new
        subjectFormModal.addClass('active');
    });

    // Open Edit Subject Modal
    $(document).on('click', '.btn-edit-subject', function() {
        var subjectId = $(this).data('id');
        modalTitle.text('تعديل بيانات المادة');
        subjectIdInput.val(subjectId); // Set hidden ID for editing

        // Fetch subject data via AJAX
        $.ajax({
            url: 'get_subject_data.php', // A new PHP file to fetch single subject data
            method: 'GET',
            data: { id: subjectId },
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    $('#subject_name').val(data.subject.name);
                    $('#subject_units').val(data.subject.units);
                    $('#subject_active').prop('checked', data.subject.active == 1);
                    subjectFormModal.addClass('active');
                } else {
                    showAlert('خطأ: ' + data.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + error);
                showAlert('حدث خطأ أثناء جلب بيانات المادة.', 'danger');
            }
        });
    });

    // Close Modal
    $('.close-btn, #cancelSubjectBtn').on('click', function() {
        subjectFormModal.removeClass('active');
        subjectForm.trigger('reset'); // Clear form fields on close
    });

    // Submit Subject Form (Add/Edit)
    subjectForm.on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Get form data
        var actionUrl = subjectIdInput.val() ? 'edit_subject.php' : 'add_subject.php'; // Determine action (add/edit)

        $.ajax({
            url: actionUrl,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showAlert(response.message, 'success');
                    subjectFormModal.removeClass('active');
                    // Reload subjects table content
                    loadSubjectsTable();
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + error);
                showAlert('حدث خطأ أثناء حفظ بيانات المادة.', 'danger');
            }
        });
    });


    // Function to load subjects table content (used after add/edit/delete)
    function loadSubjectsTable() {
        // This will fetch the entire table body or refresh the relevant part
        // For simplicity, we'll just reload the whole page, but AJAX is better for a partial update.
        // For a full AJAX refresh: you'd have a separate PHP file that only outputs the table <tbody> content.
        // Then you'd do: $('#subjectsTable tbody').load('get_subjects_table_body.php');
        // For now, a full page reload is simpler for demonstration.
        location.reload();
    }

    // Initial load of active link when page loads
    $(function() {
        var path = window.location.pathname;
        var page = path.split("/").pop(); // Get filename
        if (page === "" || page === "index.php") {
            $('.sidebar .nav-link[href="index.php"]').addClass('active');
        } else {
            $('.sidebar .nav-link[href="' + page + '"]').addClass('active');
        }
    });

});