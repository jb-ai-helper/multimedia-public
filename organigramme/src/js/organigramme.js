// src/js/organigramme.js

function SetUp(){
    var Root = document.getElementById('root');
    SetUpElement(Root);
}

function SetUpElement(Parent){
    Parent.addEventListener("click", FowardClick);
    
    //Setup Groups & Agents
    var Groups = Array.from(Parent.children).filter(group => group.classList.contains("group"));
    var Agents = Array.from(Parent.children).filter(agent => agent.classList.contains("agent"));
    var Elements = Groups.concat(Agents);
    var Total = Elements.length;
    var Radius = 35; // in %
    
    Elements.forEach(function(element, index){
		let Angle = (2 * Math.PI * index) / Total;
        element.style.left = (50 + Radius * Math.cos(Angle)) + "%";
        element.style.top  = (50 + Radius * Math.sin(Angle)) + "%";
    });
}

function Open(what){
    if(what.classList.contains('central')){
        what.classList.remove('central');
        what.classList.add('open');
        what.addEventListener("click", ReverseClick);
        currentGroup = what;
        
        //Show Groups
        var Groups = Array.from(what.children).filter(group => group.classList.contains("group"));
        Groups.forEach(function (group){
            group.classList.remove("start");
            group.addEventListener("click", FowardClick);
        });
        
        //Show Agents
        var Agents = Array.from(what.children).filter(agent => agent.classList.contains("agent"));
        Agents.forEach(function (agent){
            agent.classList.remove("start");
            agent.addEventListener("click", InfoClick);
        });
    } else if(!what.classList.contains('open')) {
        what.classList.add('central');
        currentGroup = what;
        var Siblings = Array.from(what.parentElement.children).filter(child => child !== what);
        Siblings.forEach(function (sibling){
            sibling.classList.add("start");
            if(sibling.classList.contains('focus')) sibling.classList.remove("focus");
            if(sibling.classList.contains('group')){
                sibling.removeEventListener("click", FowardClick);
            } else if(sibling.classList.contains('agent')){
                sibling.removeEventListener("click", InfoClick);
            }
        });
        SetUpElement(what);
        setTimeout(Open, 1000, what);
    }
}

function FowardClick(e) {
    e.stopPropagation();
    Open(this);
}

function ReverseClick(e) {
    e.stopPropagation();
    Close(this);
}

function InfoClick(e) {
    e.stopPropagation();
    Info(this);
}

function GoBack(){
    var OpenedGroups = document.getElementsByClassName("open");
    var LastOpenedGroup = OpenedGroups[OpenedGroups.length-1];
    var CurrentElement = LastOpenedGroup;
    
    if (CurrentElement){ Close(CurrentElement); }
}

function Info(what){
    var alreadyFocused = document.getElementsByClassName('focus')[0];
    
    if(alreadyFocused && alreadyFocused != what){
        Info(alreadyFocused);
        setTimeout(() => {
            Info(what);
        },1000)
    } else {
        var link = what.getElementsByTagName('a')[0];
        if(link.href.search("#") < 0){
            if(what.classList.contains('focus')){
                what.classList.remove('focus');
                document.getElementById('focusbkg').classList.remove('ON');
                setTimeout(() => {
                    document.getElementById('focusbkg').remove();
                }, 1000);
            } else {
                what.classList.add('focus');

                let focusbkg = document.createElement('div');
                    focusbkg.setAttribute('id', "focusbkg");
                    focusbkg.setAttribute('class', "");
                    focusbkg.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });

                let focuspdf = document.createElement('iframe');
                    focuspdf.setAttribute('id', "focuspdf");
                    focuspdf.setAttribute('title', "Fiche de poste")
                    focuspdf.setAttribute('src', link.href)

                what.parentNode.insertBefore(focusbkg, what);
                focusbkg.appendChild(focuspdf);
                void focusbkg.offsetWidth;
                focusbkg.classList.add('ON');
            }
        }

    }
}

function Close(what){
    var focusedDiv = document.getElementsByClassName('focus')[0];
    if(focusedDiv){
        Info(focusedDiv);
    } else {
        // Correct Target
        if(what.getElementsByClassName('central')[0]){
            what = what.getElementsByClassName('central')[0];
        }

        //Close depending on target
        if(what.classList.contains('open')){
            what.classList.remove('open');
            what.classList.add('central');
            what.removeEventListener("click", ReverseClick);

            //Hide Groups
            var Groups = Array.from(what.children).filter(group => group.classList.contains("group"));
            Groups.forEach(function (group){
                group.classList.add("start");
                group.removeEventListener("click", FowardClick);
            });

            //Hide Agents
            var Agents = Array.from(what.children).filter(agent => agent.classList.contains("agent"));
            Agents.forEach(function (agent){
                agent.classList.add("start");
                agent.removeEventListener("click", InfoClick);
                if(agent.classList.contains('focus')){
                    agent.classList.remove("focus");
                    document.getElementById('focusbkg').classList.remove('ON');
                    setTimeout(() => {
                        document.getElementById('focusbkg').remove();
                    }, 1000);
                }
            });
            //Check for root
            var OpenedGroups = document.getElementsByClassName("open");
            var LastOpenedGroup = OpenedGroups[OpenedGroups.length-1];
            var CurrentElement = LastOpenedGroup;
            //Continue closing unless root
            if(CurrentElement){ setTimeout(Close, 1000, what); }
        } else if(what.classList.contains('central')){

            what.classList.remove('central');
            var Siblings = Array.from(what.parentElement.children).filter(child => child !== what);

            Siblings.forEach(function (sibling){
                sibling.classList.remove("start");
                if(sibling.classList.contains('group')){
                    sibling.addEventListener("click", FowardClick);
                } else if(sibling.classList.contains('agent')){
                    sibling.addEventListener("click", InfoClick);
                }
            });
        }
        
    }
}