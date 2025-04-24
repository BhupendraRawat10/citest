<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    
    <style>
        .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #007bff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }

        .form-container {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="file"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background: #0056b3;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }

        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="spinner" id="loadingSpinner"></div>

    <div class="form-container">
        <h2>Register</h2>
        <form id="registerForm" enctype="multipart/form-data" role="form">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
                <small id="emailError" style="color: red; display: none;">Only gmail addresses are allowed.</small>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" minlength="6" required>
                <small id="passwordError" style="color: red; display: none;">Password must be at least 6 characters long.</small>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
            </div>

            <input type="submit" value="Register">
        </form>
        <a class="login-link" href="<?php echo site_url('login'); ?>">Login</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
    $(document).ready(function () {
        $('#registerForm').on('submit', function (e) {
            e.preventDefault();

            var email = $('#email').val();
            var password = $('#password').val();
            var formData = new FormData(this);

           
            $('#emailError').hide();
            $('#passwordError').hide();

            if (!email.endsWith('@gmail.com')) {
                $('#emailError').show();
                return;
            }

            if (password.length < 6) {
                $('#passwordError').show();
                return;
            }

            $('input[type="submit"]').prop('disabled', true);

            $('#loadingSpinner').show();

            $.ajax({
                url: "<?php echo site_url('register'); ?>",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        toastr.success("Registration successful!");

                        // Reload the page after a short delay
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);  // 1 second delay for smooth transition
                    } else {
                        toastr.error("Error: " + response.error);
                    }

                    // Hide loading spinner
                    $('#loadingSpinner').hide();
                    $('input[type="submit"]').prop('disabled', false);
                },
                error: function (xhr) {
                    toastr.error("Registration failed. Please try again.");
                    console.error(xhr.responseText);
                    
                    // Hide loading spinner
                    $('#loadingSpinner').hide();
                    $('input[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>
