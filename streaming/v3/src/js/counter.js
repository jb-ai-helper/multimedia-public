// JavaScript Document

var Time = 0;
var FrameRate = 25;
var Interval = 1000/FrameRate;

var CountUp = window.setInterval(function () {
	var CountBox = document.getElementById("counter");
    var Hours = Math.trunc(Time/60/60/FrameRate);
    var Minutes = Math.trunc(Time/60/FrameRate)-(Hours*60);
    var Seconds = Math.trunc(Time/FrameRate)-(Minutes*60)-(Hours*60*60);
    var Frames = Time-(Seconds*FrameRate)-(Minutes*60*FrameRate)-(Hours*60*60*FrameRate);
    
    if(Hours<10){ Hours = "0"+Hours; }
    if(Minutes<10){ Minutes = "0"+Minutes; }
    if(Seconds<10){ Seconds = "0"+Seconds; }
    if(Frames<10){ Frames = "0"+Frames; }
    
    CountBox.innerHTML = Hours+":"+Minutes+":"+Seconds+":"+Frames;
    Time ++
}, Interval);
