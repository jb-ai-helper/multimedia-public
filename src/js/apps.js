// MPDA FUNCTIONS -->

function SetUp(){
	var Bubbles = document.getElementsByClassName("IconApp");
	for (let Bubble of Bubbles){
		var color = Bubble.dataset.color;
		var app = Bubble.dataset.app;
		Bubble.style.backgroundColor = color;
		Bubble.style.backgroundImage = "url('"+app+"favicon.ico')";
		Bubble.onclick = function(){ window.location.href = Bubble.dataset.app; };
	}
    ShowHideChild('IconApp');
}

function SetBubbles(ID)
{
	var Bubbles = document.getElementsByClassName(ID);
	var HomeWrapper = document.getElementById('HomeWrapper').offsetWidth;
	var NB = Bubbles.length;

	for(i=0; i < NB; i++)
		{
		X = 50 + (50 * Math.sin(i * (2*Math.PI/NB)+(1/NB)*Math.PI));
		Y = 50 + (50 * Math.cos(i * (2*Math.PI/NB)+(1/NB)*Math.PI));
		X = ((X/100)*HomeWrapper);
		Y = ((Y/100)*HomeWrapper);
		Bubbles[i].style.top = Y+"px"; Bubbles[i].style.left = X+"px";
		}
}

function ShowHideChild(ID)
{
	SetBubbles(ID);
	var APPS = document.getElementsByClassName(ID);
    
	for(let i=APPS.length-1;i>=0;i--) {
        APPS[i].classList.contains("start") == true ? APPS[i].classList.remove("start") : APPS[i].classList.add("start");
    }
}

function ShowTitle()
{
	Title = document.getElementById("Title");
	Title.style.marginTop = (Title.style.marginTop == "120px") ? "-40px" : "120px";
	
	if(Title.classList.contains("on"))
		{ Title.classList.remove("on"); }
	else { Title.classList.add("on"); }
	
	CentralLogo = document.getElementById("CentralLogo");
	
	if(CentralLogo.classList.contains("on"))
		{ CentralLogo.classList.remove("on"); }
	else { CentralLogo.classList.add("on"); }
}