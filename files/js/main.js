$(document).ready(function(){
    
    // override jquery validate plugin defaults
    $.validator.setDefaults({
        highlight: function(element) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorElement: 'span',
        errorClass: 'help-block',
        errorPlacement: function(error, element) {
            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        }
    });
    
    /*
 * Translated default messages for the jQuery validation plugin.
 * Locale: FA (Persian; فارسی)
 */
(function ($) {
	$.extend($.validator.messages, {
		required: "تکمیل این فیلد اجباری است.",
		remote: "لطفا این فیلد را تصحیح کنید.",
		email: " لطفا یک ایمیل صحیح وارد کنید. ",
		url: "لطفا آدرس صحیح وارد کنید.",
		date: "لطفا یک تاریخ صحیح وارد کنید",
		dateISO: "لطفا تاریخ صحیح وارد کنید (ISO).",
		number: "لطفا عدد صحیح وارد کنید.",
		digits: "لطفا تنها رقم وارد کنید",
		creditcard: "لطفا کریدیت کارت صحیح وارد کنید.",
		equalTo: "لطفا مقدار برابری وارد کنید",
		accept: "لطفا مقداری وارد کنید که ",
		maxlength: $.validator.format("لطفا بیشتر از {0} حرف وارد نکنید."),
		minlength: $.validator.format("لطفا کمتر از {0} حرف وارد نکنید."),
		rangelength: $.validator.format("لطفا مقداری بین {0} تا {1} حرف وارد کنید."),
		range: $.validator.format("لطفا مقداری بین {0} تا {1} حرف وارد کنید."),
		max: $.validator.format("لطفا مقداری کمتر از {0} حرف وارد کنید."),
		min: $.validator.format("لطفا مقداری بیشتر از {0} حرف وارد کنید.")
	});
}(jQuery));

jQuery.validator.addMethod("amount", function(value, element) {
return value >= 240000;
}, "حداقل مبلغ اشتراک ۲۴۰۰۰۰ ریال است .");
    
    $("#payForm").validate({
         rules: {
            InputEmail: {
            required: true,
            email: true
            },
			phone :{
			digits : true,
			minlength : 11 ,
			maxlength : 11 ,
            },
			amount :{
		        digits: true,
				amount : true
			}
        },
		 messages: {
			phone: {
			minlength: "لطفا تلفن همراهتان را مطابق الگو و به شکل ۱۱ رقمی وارد نمایید",
			maxlength: "لطفا تلفن همراهتان را مطابق الگو و به شکل ۱۱ رقمی وارد نمایید"
			}
		},
        submitHandler: function(form) {
        form.submit();
        }
    }); 
});
