function menuMoveApart(){
    (function( $ ) {
        $("#nav").toggleClass( "minimized",400 );
        return false;
    })(jQuery);
}

var jsClockTimesign = true;

function updateClock ( )
    {
    var currentTime = new Date ( );
    currentTime.setTime( currentTime.getTime() - differenceJsClockNowTime);
    
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );
 
    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
 
    // Choose either "AM" or "PM" as appropriate
    var timeOfDay = ( currentHours < 12 ) ? "AM" : "PM";
 
    // Convert the hours component to 12-hour format if needed
    currentHours = ( currentHours > 12 ) ? currentHours - 12 : currentHours;
 
    // Convert an hours component of "0" to "12"
    currentHours = ( currentHours == 0 ) ? 12 : currentHours;
 
    // Compose the string for display
    var currentTimeString = currentHours;
    currentTimeString += jsClockTimesign ? ":" : " ";
    currentTimeString += currentMinutes + " " + timeOfDay;
     
    jsClockTimesign = !jsClockTimesign;
    jQuery("#clock_js").html(currentTimeString);
         
}
 
jQuery(document).ready(function()
{
   setInterval('updateClock()', 1000);
});