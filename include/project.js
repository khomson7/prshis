function isBlankOrNull(value){
    return value == null || value == '';
}

function isBlankOrNullOrWhiteSpace(value){
    return value == null || (isString(value) && value.trim() == '');
}

function checkStringValue(value, prefix, subfix, replaceWith, needEscapeHtml){
    if(isBlankOrNull(value)){
        return ((replaceWith !== undefined) ? replaceWith : '');
    } else {
        return ((prefix != null ? prefix : '') +
            (needEscapeHtml ? document.createTextNode(value).textContent : value) +
            (subfix != null ? subfix : ''));
    }
}

function escapeHtmlString(value){
    return value != null ? document.createTextNode(value).textContent : '';
}

function isString(x) {
    return Object.prototype.toString.call(x) === "[object String]";
}

function newLineToBr(str){
    return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
}

function insertAtCursor(myField, myValue, cursorMove, separator, focusObject) {
    if(separator != null && myField.value.trim().length > 0){
        myValue = separator + myValue;
        if(cursorMove != null){
            cursorMove = cursorMove + separator.length;
        }
    }
    cursorMove = (cursorMove == null ? myValue.length : cursorMove);
    //IE support
    if (document.selection) {
        myField.focus();
        let sel = document.selection.createRange();
        sel.text = myValue;
    }
    //MOZILLA and others
    else if (myField.selectionStart || myField.selectionStart == '0') {
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        myField.value = myField.value.substring(0, startPos) +
            myValue +
            myField.value.substring(endPos, myField.value.length);
        myField.setSelectionRange(startPos+cursorMove, startPos+cursorMove);
    } else {
        myField.value += myValue;
        myField.setSelectionRange(myField.value.length, myField.value.length);
    }
    myField.focus();
}
// Auto-Grow-TextArea script.
// Script copyright (C) 2011 www.cryer.co.uk.
// Script is free to use provided this copyright header is included.
// function autoGrowTextArea(textField) {
//     let pageXOffset = window.pageXOffset;
//     let pageYOffset = window.pageYOffset;
//     textField.style.overflow = 'hidden';
// 	textField.style.height = 'inherit';
//     if (textField.clientHeight < textField.scrollHeight) {
//         textField.style.height = textField.scrollHeight + "px";
//         if (textField.clientHeight < textField.scrollHeight) {
//            textField.style.height = ((textField.scrollHeight * 2 - textField.clientHeight)+3) + "px";
//         }
//     }
//     window.scrollTo(pageXOffset,pageYOffset);
// }

function autoGrowTextArea(oField) {
    oField.style.overflow = 'hidden';
    if (oField.scrollHeight > oField.clientHeight) {
        oField.style.height = oField.scrollHeight + "px";
    }
}

// function autoExpand(field) {

// 	// Reset field height
// 	field.style.height = 'inherit';

// 	// Get the computed styles for the element
// 	var computed = window.getComputedStyle(field);

// 	// Calculate the height
//     var height =    parseInt(computed.getPropertyValue('border-top-width'), 10) +
//                     parseInt(computed.getPropertyValue('padding-top'), 10) +
//                     field.scrollHeight +
//                     parseInt(computed.getPropertyValue('padding-bottom'), 10) +
//                     parseInt(computed.getPropertyValue('border-bottom-width'), 10);

// 	field.style.height = height + 'px';

// }

function roundNumber(num, scale) {
    if(!("" + num).includes("e")) {
        return +(Math.round(Number(num + "e+" + scale)) + "e-" + scale);
    } else {
        var arr = ("" + num).split("e");
        var sig = "";
        if(+arr[1] + scale > 0) {
            sig = "+";
        }
        return +(Math.round(Number(+arr[0] + "e" + sig + (+arr[1] + scale))) + "e-" + scale);
    }
}

var searchBoxHtmlMap = {};
function initSearchBox(){
    $.get('common-searchbox-lab.php',function(html){
        searchBoxHtmlMap['common-searchbox-lab.php'] = html;
    });
    $.get('common-searchbox-xray.php',function(html){
        searchBoxHtmlMap['common-searchbox-xray.php'] = html;
    });
    $.get('common-searchbox-ivfluid.php',function(html){
        searchBoxHtmlMap['common-searchbox-ivfluid.php'] = html;
    });
    $.get('common-searchbox-med.php',function(html){
        searchBoxHtmlMap['common-searchbox-med.php'] = html;
    });
    $.get('common-searchbox-nondrug.php',function(html){
        searchBoxHtmlMap['common-searchbox-nondrug.php'] = html;
    });
    $.get('common-searchbox-patient.php',function(html){
        searchBoxHtmlMap['common-searchbox-patient.php'] = html;
    });
    $.get('common-searchbox-opd-visit.php',function(html){
        searchBoxHtmlMap['common-searchbox-opd-visit.php'] = html;
    });
}

function openSearchBox(event, element, searchBoxFile, searchBoxParameter, callback, attachMode, width){
    // $('.common-searchbox-med-div').remove();
    $('.common-searchbox-div').remove();
    // $.get(searchBoxFile)
    // .done(function(data){
    //     $(data).insertAfter(element);
    //     init_searchbox(event, callback);
    // })
    // .fail(function(data){

    // });
    if(attachMode == 'floatingUnder'){
        let documentDimension = new Dimension(window.document);
        let elementDimension = new Dimension(element);
        let modalWindowWidth = (width != null ? width : elementDimension.w);
        let modalWindowLeft = ((documentDimension.w < (elementDimension.x + modalWindowWidth)) ?
                                ((elementDimension.x + elementDimension.w) - modalWindowWidth) : elementDimension.x);
        // let modalWindow =
        $(searchBoxHtmlMap[searchBoxFile]).insertAfter(element)
                            .css('position','absolute')
                            .css('left', modalWindowLeft)
                            .css('top', elementDimension.y + elementDimension.h + 5)
                            .css('width', modalWindowWidth)
                            // .css('width', width)
                            .css('z-index',1021);
        // console.log(modalWindow);

        // modalWindow.style.position = 'absolute';
        // modalWindow.setAttribute("align", "center");
        // modalWindow.setAttribute("vertical-align", "middle");
        // modalWindow.style.left = elementDimension.x;
        // modalWindow.style.top = elementDimension.y;
        // modalWindow.style.width = elementDimension.w;
        // modalWindow.style.height = elementDimension.h;
        // console.log(modalWindow);

    // } else if(attachMode == 'floatingOver'){
    //     let documentDimension = new Dimension(window.document);
    //     let elementDimension = new Dimension(element);
    //     let modalWindowWidth = (width != null ? width : elementDimension.w);
    //     let modalWindowLeft = ((documentDimension.w < (elementDimension.x + modalWindowWidth)) ?
    //                             ((elementDimension.x + elementDimension.w) - modalWindowWidth) : elementDimension.x);
    //     $(searchBoxHtmlMap[searchBoxFile]).insertAfter(element)
    //                         .css('position','absolute')
    //                         .css('left', modalWindowLeft)
    //                         .css('top', elementDimension.y - (elementDimension.h + 5))
    //                         .css('width', modalWindowWidth)
    //                         .css('z-index',5);
    } else {
        $(searchBoxHtmlMap[searchBoxFile]).insertAfter(element);
    }
    init_searchbox(event, callback, searchBoxParameter);
}

function Dimension(element) {
    this.x = -1;
    this.y = -1;
    this.w = 0;
    this.h = 0;
    if (element == document) {
        this.x = element.body.scrollLeft;
        this.y = element.body.scrollTop;
        this.w = element.body.clientWidth;
        this.h = element.body.clientHeight;
    } else if (element != null) {
        var e = element;
        var left = e.offsetLeft;
        // while ((e = e.offsetParent) != null)
        // {
        //     left += e.offsetLeft;
        // }
        // e = element;
        var top = e.offsetTop;
        // while ((e = e.offsetParent) != null)
        // {
        //     top += e.offsetTop;
        // }
        this.x = left;
        this.y = top;
        this.w = element.offsetWidth;
        this.h = element.offsetHeight;
    }
}


/**
 * Usage:
 * $('#input').keyup(delay(function (e) {
 *    console.log('Time elapsed!', this.value);
 * }, 500));
 */
function delay(callback, ms) {
    var timer = 0;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}

function decimalColorToHTMLcolor(number) {
    //converts to a integer
    var intnumber = number - 0;

    // isolate the colors - really not necessary
    var red, green, blue;

    // needed since toString does not zero fill on left
    var template = "#000000";

    // in the MS Windows world RGB colors
    // are 0xBBGGRR because of the way Intel chips store bytes
    red = (intnumber&0x0000ff) << 16;
    green = intnumber&0x00ff00;
    blue = (intnumber&0xff0000) >>> 16;

    // mask out each color and reverse the order
    intnumber = red|green|blue;

    // toString converts a number to a hexstring
    var HTMLcolor = intnumber.toString(16);

    //template adds # for standard HTML #RRGGBB
    HTMLcolor = template.substring(0,7 - HTMLcolor.length) + HTMLcolor;

    return HTMLcolor;
}

function isCharacterKeycode(keycode, includeBackspaceAndDelete, includeEnter){
    var valid =
        (keycode > 47 && keycode < 58)   || // number keys
        keycode == 32                    || // spacebar & return key(s) (if you want to allow carriage returns)
        (keycode > 64 && keycode < 91)   || // letter keys
        (keycode > 95 && keycode < 112)  || // numpad keys
        (keycode > 185 && keycode < 193) || // ;=,-./` (in order)
        (keycode > 218 && keycode < 223) || // [\]' (in order)
        (includeBackspaceAndDelete && (keycode == 8 || keycode == 46)) ||
        (includeEnter && keycode == 13)
        ;

    return valid;
}

function session_keep_alive(){
    $.getJSON('session-keep-alive.php',function(result){
        // console.log('result1' ,result);
        if(result == true){
            // console.log('result2' ,result);
            setTimeout(function(){ session_keep_alive(); }, 10*60*1000);
        }
    });
}

function ipd_discharge_check(an) {
    $.getJSON("./ipd-discharge-check.php", {an: an})
        .done(function(data) {
            if(data != null){
                alert('แจ้งเตือน: รายการนี้ Discharge ไปแล้ว');
            }
        });
}

/**
 * แปลงวันที่ ค.ศ. ให้เป็นข้อความวันที่ พ.ศ.
 * @param string date "YYYY-MM-DD"
 * @returns string
 */
function toThaiDateString(date){
    if(date != null){
        let datetime = moment(date, "YYYY-MM-DD");
        return datetime.format("DD/MM/") + (parseInt(datetime.format("YYYY"),10)+543);
    } else {
        return '';
    }
}

/**
 * แปลงวันที่/เวลา ค.ศ. ให้เป็นข้อความวันที่/เวลา พ.ศ.
 * @param string datetime "YYYY-MM-DD HH:mm"
 * @returns string
 */
function toThaiDateTimeString(datetime){
    if(datetime != null){
        let m = moment(datetime, "YYYY-MM-DD HH:mm");
        return m.format("DD/MM/") + (parseInt(m.format("YYYY"),10)+543) + m.format(", HH:mm");
    } else {
        return '';
    }
}

function toShift(datetime){
    if(datetime != null){
        let m = moment(datetime, "YYYY-MM-DD HH:mm");
        let time = m.format("HH:mm");
        let result = null;
        if(time >= "00:00" && time < "08:00"){
            result = 'ด.';
        } else if(time >= "08:00" && time < "16:00"){
            result = 'ช.';
        } else if(time >= "16:00" && time <= "24:00"){
            result = 'บ.';
        }
        return result;
    } else {
        return '';
    }
}

function setCurrentDate(dateInput){
    if(dateInput != null){
        let now = moment();
        dateInput.value = now.format("YYYY-MM-DD");
    }
}

function setCurrentTime(timeInput){
    if(timeInput != null){
        let now = moment();
        timeInput.value = now.format("HH:mm");
    }
}

function setCurrentDateTime(datetimeInput){
    if(datetimeInput != null){
        let now = moment();
        timeInput.value = now.format("YYYY-MM-DD HH:mm");
    }
}

function setCurrentDateAndTime(dateInput,timeInput){
    let now = moment();
    if(dateInput != null){
        dateInput.value = now.format("YYYY-MM-DD");
    }
    if(timeInput != null){
        timeInput.value = now.format("HH:mm");
    }
}