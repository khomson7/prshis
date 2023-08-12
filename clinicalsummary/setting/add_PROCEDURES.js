$(document).ready(function(){
    var maxField5= 5; //Input fields increment limitation
    var addButton5 = $('.add_button2'); //Add button selector
    var wrapper5 = $('.field_wrapper2');
    var fieldHTML5 = "<tr><td><input type='text' id='comortext' name='text2[]'></td><td><input type='text' id='comortext' name='text2[]'></td></tr>"; //New input field html 
    var x = 1; //Initial field counter is 1
    //Once add button is clicked
    $(addButton5).click(function(){
        //Check maximum number of input fields
        if(x < maxField5){ 
            x++; //Increment field counter
            $(wrapper5).append(fieldHTML5); //Add field html
        }
    });
    
    //Once remove button is clicked
    $(wrapper5).on('click', '.remove_button2', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});
$(document).ready(function(){
    var maxField5= 5; //Input fields increment limitation
    var addButton5 = $('.add_button3'); //Add button selector
    var wrapper5 = $('.field_wrapper3');
    var fieldHTML5 = "<tr><td><input type='text' id='complitext' name='text3[]'></td><td><input type='text' id='complitext' name='text3[]'></td></tr>"; //New input field html 
    var x = 1; //Initial field counter is 1
    //Once add button is clicked
    $(addButton5).click(function(){
        //Check maximum number of input fields
        if(x < maxField5){ 
            x++; //Increment field counter
            $(wrapper5).append(fieldHTML5); //Add field html
        }
    });
    
    //Once remove button is clicked
    $(wrapper5).on('click', '.remove_button3', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});
$(document).ready(function(){
    var maxField5= 4; //Input fields increment limitation
    var addButton5 = $('.add_button4'); //Add button selector
    var wrapper5 = $('.field_wrapper4');
    var fieldHTML5 = " <tr><td><input type=text name='txt[]'></td><td width='20'><input type=date name='text8[]'></td><td width='20'><input type=time name='text9[]'></td><td width='20'><input type=time name='text10[]'></td><td style='width:200 !important;'><input type=text name='text101[]'></td></tr>"; //New input field html 
    var x = 1; //Initial field counter is 1
    //Once add button is clicked
    $(addButton5).click(function(){
        //Check maximum number of input fields
        if(x < maxField5){ 
            x++; //Increment field counter
            $(wrapper5).append(fieldHTML5); //Add field html
        }
    });
    
    //Once remove button is clicked
    $(wrapper5).on('click', '.remove_button4', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});