$(function () {
    "use strict";

    // Set default validation settings
    $.validator.setDefaults({
        highlight: function (element) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function (element) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorPlacement: function (error, element) {}
    });

    // Validate and submit the form
    $("#contact-form").submit(function (e) {
        e.preventDefault(); // Prevent default form submission
    }).validate({
        rules: {
            name: "required", // Name field is required
            email: {
                required: true, // Email is required
                email: true // Must be a valid email
            },
            phone: {
                required: true, // Phone is required
                digits: true, // Must contain only digits
                minlength: 10, // Minimum length 10 digits
                maxlength: 15 // Maximum length 15 digits
            },
            message: "required" // Message is required
        },
        messages: {
            name: "Please enter your name",
            email: "Please enter a valid email address",
            phone: "Please enter a valid phone number (10-15 digits)",
            message: "Please enter your message"
        },
        submitHandler: function (form) {
            $("#js-contact-btn").attr("disabled", true).text('Please wait...');
            var success_msg = $('#js-contact-result').data('success-msg');
            var error_msg = $('#js-contact-result').data('error-msg');
            var dataString = $(form).serialize();

            // reCAPTCHA v3 validation
            grecaptcha.ready(function () {
                grecaptcha.execute('6LctNoMqAAAAAH_k3L9y8Onlsa3_Rrk8vlme2Suw', { action: 'submit' }).then(function (token) {
                    // Add the reCAPTCHA token to the serialized form data
                    dataString += '&g-recaptcha-response=' + token;

                    // Submit the form using AJAX
                    $.ajax({
                        type: "POST",
                        data: dataString,
                        url: "php/contact.php",
                        cache: false,
                        success: function (response) {
                            let result;

                            try {
                                result = JSON.parse(response);
                            } catch (error) {
                                console.error("Error parsing server response:", error);
                                $('#js-contact-result').text("Invalid server response.").css('color', 'red');
                                return;
                            }

                            $(".form-group").removeClass("has-success");
                            if (result.status === 'success') {
                                $("#js-contact-btn").text(success_msg);
                                $('#contact-form')[0].reset();
                                $('#js-contact-result').text(result.message).css('color', 'green');
                            } else {
                                $("#js-contact-btn").text(error_msg);
                                $('#js-contact-result').text(result.message).css('color', 'red');
                            }
                            setTimeout(function () {
                                $("#js-contact-btn").text('Submit Request');
                                $("#js-contact-btn").attr("disabled", false);
                            }, 2000);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.error("AJAX error:", textStatus, errorThrown);
                            $("#js-contact-btn").text('Cannot access Server');
                            $('#js-contact-result').text("An error occurred. Please try again.").css('color', 'red');
                            setTimeout(function () {
                                $("#js-contact-btn").text('Submit Request');
                                $("#js-contact-btn").attr("disabled", false);
                            }, 2000);
                        }
                    });
                });
            });

            return false;
        }
    });
});
