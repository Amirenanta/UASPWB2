document.addEventListener('DOMContentLoaded', function() {
    // Function to confirm delete actions
    function confirmDelete(event) {
        if (!confirm('Are you sure you want to delete this item?')) {
            event.preventDefault();
        }
    }

    // Add event listeners to all delete buttons
    var deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', confirmDelete);
    });

    // Function to preview file uploads
    function previewFile() {
        var preview = document.querySelector('#file-preview');
        var file    = document.querySelector('input[type=file]').files[0];
        var reader  = new FileReader();

        reader.onloadend = function () {
            preview.src = reader.result;
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
        }
    }

    // Add event listener to file input if it exists
    var fileInput = document.querySelector('input[type=file]');
    if (fileInput) {
        fileInput.addEventListener('change', previewFile);
    }

    // Function to validate form inputs
    function validateForm(event) {
        var requiredInputs = event.target.querySelectorAll('[required]');
        var isValid = true;

        requiredInputs.forEach(function(input) {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });

        if (!isValid) {
            event.preventDefault();
            alert('Please fill in all required fields.');
        }
    }

    // Add event listeners to all forms
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', validateForm);
    });

    // Function to show/hide password
    function togglePassword() {
        var passwordInput = document.querySelector('#password');
        var toggleButton = document.querySelector('#toggle-password');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.textContent = 'Hide Password';
        } else {
            passwordInput.type = 'password';
            toggleButton.textContent = 'Show Password';
        }
    }

    // Add event listener to password toggle button if it exists
    var togglePasswordButton = document.querySelector('#toggle-password');
    if (togglePasswordButton) {
        togglePasswordButton.addEventListener('click', togglePassword);
    }
});