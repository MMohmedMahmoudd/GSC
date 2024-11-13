$(function() {
    "use strict";

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorPlacement: function(error, element) {}
    });

    $("#phpcontactform").submit(function(e) {
        e.preventDefault();
    }).validate({
        rules: {
            name: "required",
            email: {
                required: true,
                email: true
            },
            // subject: "required",
            message: "required",
        },
        messages: {
            name: "Please enter your name",
            email: "Please enter a valid email address",
            subject: "Please enter a subject",
            message: "Please enter your message",
        },
        submitHandler: function(form) {
            $("#js-contact-btn").attr("disabled", true).text('Please wait...');
            var success_msg = $('#js-contact-result').data('success-msg');
            var error_msg = $('#js-contact-result').data('error-msg');
            var dataString = $(form).serialize();

            $.ajax({
                type: "POST",
                data: dataString,
                url: "php/contact.php",
                cache: false,
                success: function(response) {
                    let result = JSON.parse(response);
                    $(".form-group").removeClass("has-success");
                    if (result.status === 'success') {
                        $("#js-contact-btn").text(success_msg);
                        $('#phpcontactform')[0].reset();
                        $('#js-contact-result').text(result.message).css('color', 'green');
                    } else {
                        $("#js-contact-btn").text(error_msg);
                        $('#js-contact-result').text(result.message).css('color', 'red');
                    }
                    setTimeout(function() {
                        $("#js-contact-btn").text('Send Message');
                        $("#js-contact-btn").attr("disabled", false);
                    }, 2000);
                },
                error: function() {
                    $("#js-contact-btn").text('Cannot access Server');
                    $('#js-contact-result').text("An error occurred. Please try again.").css('color', 'red');
                    setTimeout(function() {
                        $("#js-contact-btn").text('Send Message');
                        $("#js-contact-btn").attr("disabled", false);
                    }, 2000);
                }
            });
            return false;
        }
    });
});

