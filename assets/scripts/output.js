
jQuery(document).ready(function($){

    var all_drinks;

    function get_all_cocktails() {
        
        jQuery.ajax({
            
            type: "POST",
            dataType: "html",
            url: the_ajax_script.ajaxurl,
            data : {
                action: "get_letter_cocktails", 
            },
            success: function (data) {
                if (data.length) {

                    all_drinks = JSON.parse(data);

                    if(typeof all_drinks['errors'] === 'undefined' && typeof all_drinks['error_data'] === 'undefined') {
                    
                        console.log('success!!!');
                        console.log(all_drinks);

                    }
                    else {
                        
                        console.log(all_drinks);
                        $('.wn_ckt_error_message').css('opacity', '1');

                    }
                    
                } 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
            }
    
        });
        
    }

    get_all_cocktails();

    function get_all_cocktails_by_letter(letter) {

        jQuery.ajax({
            
            type: "POST",
            dataType: "html",
            url: the_ajax_script.ajaxurl,
            data : {
                action: "get_single_letter_cocktails", 
                letter: letter,
            },
            success: function (data) {
                if (data.length) {

                    data = JSON.parse(data);

                    console.log(data);

                    if(typeof data['errors'] === 'undefined' && typeof data['error_data'] === 'undefined') {
                    
                        console.log('success!!!');
                        show_cocktails(data);

                    }
                    else {
                        
                        $('.wn_ckt_error_message').css('opacity', '1');

                    }

                } 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
            }
    
        });

    }

    //Creates the click Event on single letter box
    $('.wn_cocktails_wrapper').find('.wn_ckt_letter_box').each(function(){

        let letter_pressed;
        let letter_cocktails;

        $(this).on('click', function(e){

            e.preventDefault();
            letter_pressed = $(this).children('a').html();
            letter_cocktails = [];

            $('.wn_ckt_letter_box').removeClass('wn_ckt_letter_box__active');
            $(this).addClass('wn_ckt_letter_box__active');

            //Checks if all drinks info object is already available, otherwise call Ajax for that specific letter
            if(all_drinks) {

                $.each(all_drinks[ letter_pressed ], function(i) {

                    letter_cocktails.push( all_drinks[ letter_pressed ][ i ] );  

                });

                show_cocktails(letter_cocktails);

            } 
            else {

                console.log('ajax not done yet');
                get_all_cocktails_by_letter(letter_pressed);

            }
        });

    });

    //Draws the cocktails in the document
    function show_cocktails(cocktails) {

        let backgrund_img = '';
        let overlay = '';
        let details;
        let front;
        let back;
        let inner;
        let wrapper;
        const load_images = $('.wn_cocktails_wrapper').attr('display-iimages');

        $('.wn_ckt_drinks_list').empty();
        $.each(cocktails, function(i) {

            if(load_images == 'yes') {
                backgrund_img = cocktails[i]['strDrinkThumb'];

                //Creates a dark overlay to make the text more
                overlay = '<div class="wn_ckt_dark_overlay"></div>';
            }
            
            details = '<label>Type: ' + cocktails[i]['strAlcoholic'] + '</label>';
            details += '<label>Category: ' + cocktails[i]['strCategory'] + '</label>';

            front = '<div class="wn_ckt_cocktail_front"><h2>' + cocktails[i]['strDrink'] + '</h2></div>';
            back = '<div style="background-image: url(' + backgrund_img + ')" class="wn_ckt_cocktail_back">' + overlay + details + '</div>';
            inner = '<div class="wn_ckt_cocktail_inner">' + front + back + '</div>';
            wrapper = '<div class="wn_ckt_cocktail_box_wrapper">' + inner + '</div>';

            $('.wn_ckt_drinks_list').append(wrapper);

        });

    }

});
