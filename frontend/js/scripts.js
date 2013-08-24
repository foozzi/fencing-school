$(function(){
    $('#adm').submit(function(e){
        //отменяем стандартное действие при отправке формы
        e.preventDefault();
        //берем из формы метод передачи данных
        var m_method=$(this).attr('method');
        //получаем адрес скрипта на сервере, куда нужно отправить форму
        var m_action=$(this).attr('action');
        //получаем данные, введенные пользователем в формате input1=value1&input2=value2...,
        //то есть в стандартном формате передачи данных формы
        var m_data=$(this).serialize();
        $.ajax({
            type: m_method,
            url: m_action,
            data: m_data,
            dataType: "json",
            success: function(result){ 
                $('#res').html(result.mess);
            }
        });
    });

	$('a.login-window').click(function() {

    //Getting the variable's value from a link 
    var loginBox = $(this).attr('href');

    //Fade in the Popup
    $(loginBox).fadeIn(300);
    
    //Set the center alignment padding + border see css style
    var popMargTop = ($(loginBox).height() + 24) / 2; 
    var popMargLeft = ($(loginBox).width() + 24) / 2; 
    
    $(loginBox).css({ 
        'margin-top' : -popMargTop,
        'margin-left' : -popMargLeft
    });
	    
    // Add the mask to body
    $('body').append('<div id="mask"></div>');
    $('#mask').fadeIn(300);
    
    return false;

	});

	// When clicking on the button close or the mask layer the popup closed
	$('a.close, #mask').bind('click', function() { 
	  	$('#mask , .login-popup').fadeOut(300 , function() {
	    	$('#mask').remove();  
		}); 
		return false;
	});

    $(document).on('click', '#pollSlider-button, #mask', function () {
        if ($('#pollSlider-button').css("margin-right") == "300px") {
            $('#mask').remove();
            $('.pollSlider').animate({
                "margin-right": '-=300',
            });

            $('#pollSlider-button').animate({
                "margin-right": '-=300'
            });
        } else {
            // Add the mask to body            
            $('body').append('<div id="mask"></div>');
            $('#mask').fadeIn(300);
            $('.pollSlider').animate({
                "margin-right": '+=300',
                    "width": '=300'
            });
            $('#pollSlider-button').animate({
                "margin-right": '+=300',
                    "width": '=300'
            });
        }
    });
});

$.date = function(){
    return new Date().toLocaleString();
};

