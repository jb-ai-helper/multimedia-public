/* JVR 2023 */

//Videos Object
var videos = {
    Intro: {
        url: root+'videos/in-enpjj.webm',
        opacity: 1,
    },
    Background: {
        url: root+'videos/bkg-enpjj.webm',
        opacity: 1,
},
    Outro: {
        url: root+'videos/out-enpjj.webm',
        opacity: 1,
    },
};
//Sounds Object
var sounds = {
    Notification: {
        url: root+'sounds/notification.mp3'
    },
    Transition: {
        url: root+'sounds/transition.mp3'
    },
};
//Images Object
var images = {
    Marianne: {
        url: root+'images/marianne.svg',
        anchor: 'top right',
        opacity: 1,
        height: gov,
        width: 'auto',
        x: screen.width-(1.5*gov),
        y: gov
    },
    ENPJJ: {
        url: root+'images/enpjj.svg',
        anchor: 'top left',
        opacity: 1,
        height: screen.height,
        width: screen.width,
        x: 0,
        y: 0
    },
};
//FX
var effetcs = {
    shadow_01: {
        hoffset: 0.05*gov,
        voffset: 0.05*gov,
        blur: 0.1*gov,
        color: 'rgba(0,0,0,.25)',
    },
    frame_01: {
        before: {
            width: 1.5*gov,
            height: gov,
            fill: 'rgba(0,0,0,1)',
        },
        around: {
            width: 1.5*gov,
            height: gov,
            fill: 'rgba(0,0,0,1)',
        },
        after: {
            width: 3*gov,
            height: gov,
            fill: {
                type: 'linear',
                angle: 90,
                color:{
                    0: 'rgba(0,0,0,1)',
                    1: 'rgba(0,0,0,0)'
                }
            },
        },
    },
    frame_02: {
        before: {
            width: 3*gov,
            height: gov,
            fill: {
                type: 'linear',
                angle: -90,
                color:{
                    0: 'rgba(0,0,0,0)',
                    1: 'rgba(0,0,0,1)',
                }
            },
        },
        around: {
            width: 1.5*gov,
            height: gov,
            fill: 'rgba(0,0,0,1)',
        },
        after: {
            width: 3*gov,
            height: gov,
            fill: {
                type: 'linear',
                angle: 90,
                color:{
                    0: 'rgba(0,0,0,1)',
                    1: 'rgba(0,0,0,0)'
                }
            },
        },
    },
}
//Allowed Tags
var tags = {
    h1: {
        size: gov,
        weight: 'bold',
        line: 1.25,
        transform: 'uppercase',
    },
    h2: {
        size: 1.5*gov,
        weight: 'bold',
        line: 1.25,
        transform: 'uppercase',
    },
    h3: {
        size: 2*gov,
        weight: 'bold',
        line: 1.25,
        transform: 'uppercase',
    },
}
var variables = {
    StreamInfo: {
        title: 'Titre du Stream',
        subtitle : 'Bienvenue',
        date: 'JJ mois AAAA',
    },
    LowerThird: {
        name: 'Prénom NOM',
        function: 'Fonction complète',
        translation: 'Fonction traduite',
    },
    ScrollingBanner: 'L\'ENPJJ vous informe que cet événement est enregistré et que cet enregistrement est susceptible d\'être mis à disposition en replay.',
    Chapter: 'Exemple :<h3>Titre Grand</h3><h2>Titre Moyen</h2><h1>Titre Petit</h1>',
    CountDown: 'dans quelques instants...',
    Counter: '00:00:00:00',
}

//Default Texts Object
    /*
    Mandatory: txt, opacity, x, y;
    Optional: color, anchor(top/middle/bottom & left/center/right), font, size, weight, line, transform, variant(normal/small-caps), shadow
    */

var texts = {
    //A for Start Screen
    StartScreen:{
        Title: {
            txt: variables['StreamInfo'].title,
            anchor: 'bottom center',
            font: 'Marianne',
            size: gov,
            weight: 'bold',
            color: 'white',
            line: 1.25,
            opacity: 1,
            x: 0.5*screen.width,
            y: 0.40*screen.height,
            shadow: effetcs.shadow_01,
        },
        SubTitle: {
            txt: variables['StreamInfo'].subtitle,
            anchor: 'middle center',
            font: 'Marianne',
            size: 2*gov,
            weight: 'bold',
            transform: 'uppercase',
            color: 'white',
            opacity: 1,
            x: 0.5*screen.width,
            y: 0.5*screen.height,
            shadow: effetcs.shadow_01,
        },
        //Date for Start Scren
        Date: {
            txt: variables['StreamInfo'].date,
            anchor: 'top center',
            font: 'Marianne',
            size: gov/2,
            weight: '',
            style: 'italic',
            color: 'white',
            opacity: 1,
            x: 0.5*screen.width,
            y: 0.6*screen.height,
            shadow: effetcs.shadow_01,
        },
    },
    TitleBanner:{
        Title: {
            txt: variables['StreamInfo'].title,
            anchor: 'top left',
            font: 'Marianne',
            size: 1/2*gov,
            line: 2,
            color: 'white',
            opacity: 1,
            x: 1.5*gov,
            y: gov,
            frame: effetcs.frame_01,
        },
        SubTitle: {
            txt: variables['StreamInfo'].subtitle,
            anchor: 'top left',
            font: 'Marianne',
            size: 1/2*gov,
            line: 2,
            color: 'white',
            opacity: 1,
            x: 1.5*gov,
            y: 2*gov,
            frame: effetcs.frame_01,
        },
    },
    Pause: {
        txt: 'La diffusion en direct va reprendre',
        anchor: 'top left',
        font: 'Marianne',
        size: '',
        weight: '',
        style: '',
        color: '',
        opacity: 1,
        x: 0,
        y: 0
    },
    CountDown: {
        txt: variables['CountDown'],
        anchor: 'top left',
        font: 'Marianne',
        size: '',
        weight: '',
        style: '',
        color: '',
        opacity: 1,
        x: 0,
        y: 0
    },
    Counter: {
        txt: variables['Counter'],
        anchor: 'top left',
        font: 'Marianne',
        size: '',
        weight: '',
        style: '',
        color: '',
        opacity: 1,
        x: 0,
        y: 0
    },
    Chapter: {
        txt: variables['Chapter'],
        anchor: 'middle center',
        font: 'Marianne',
        size: 0.8*gov,
        color: 'white',
        opacity: 1,
        x: 0.5*screen.width,
        y: 0.5*screen.height,
        shadow: effetcs.shadow_01,
    },
    LowerThird:{
        Name: {
            txt: variables['LowerThird'].name,
            anchor: 'top left',
            font: 'Marianne',
            size: '',
            weight: '',
            style: '',
            color: '',
            opacity: 1,
            x: 0,
            y: 0
        },
        Function: {
            txt: variables['LowerThird'].function,
            anchor: 'top left',
            font: 'Marianne',
            size: '',
            weight: '',
            style: '',
            color: '',
            opacity: 1,
            x: 0,
            y: 0
        },
        Translation: {
            txt: variables['LowerThird'].translation,
            anchor: 'top left',
            font: 'Marianne',
            size: '',
            weight: '',
            style: '',
            color: '',
            opacity: 1,
            x: 0,
            y: 0
        },
    },
    ScrollingBanner: {
        txt: variables['ScrollingBanner'],
        anchor: 'top left',
        font: 'Marianne',
        size: '',
        weight: '',
        style: '',
        color: '',
        opacity: 1,
        x: 0,
        y: 0
    },
};