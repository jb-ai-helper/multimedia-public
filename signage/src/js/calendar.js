// JavaScript Document

function SetDate(){
    var today = new Date();
        today = today.toISOString().split('T')[0];
        today = FormateDate(today,2);
    
    var DateDiv = document.getElementById("Date");
        DateDiv.innerHTML = today;
    
    var day = new Date();
        day = day.getDay();
        day = french_days[day];
    
    var CalendarDiv = document.getElementById("Calendar");
        CalendarDiv.classList.add(day);
}