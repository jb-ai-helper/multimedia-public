// /trombinoscope/src/js/trombinoscope.js

/* ---------- Global Variables ---------- */
const selG = document.getElementById('groupe'),
	  selV = document.getElementById('vue'),
	  slider = document.getElementById('niveau'),
	  val = document.getElementById('niveau-val'),
	  elems = document.querySelectorAll('.personne, .chef, .agent'),
	  sliderLabel = slider.parentElement,
	  title = document.getElementById('title'),
	  printFlat = document.getElementById('print-flat'),
	  printTree = document.getElementById('print-tree');

// calcule et applique l’échelle pour #hier-view
function adjustHierScale() {
	const hier = document.getElementById('hier-view');
	if (!hier) return;
	// largeur réelle du contenu
	const contentW = hier.scrollWidth;
	// largeur de la zone imprimable (body width:100%)
	const availW   = hier.parentElement.clientWidth;
	// on ne grossit jamais ; max = 1
	const scale    = Math.min(1, availW / contentW);
	// on stocke dans la variable CSS
	hier.style.setProperty('--hier-scale', scale);
}

/* ---------- Functions ---------- */
function togglePrintStyles() {
  if (selV.value === 'flat') {
    printFlat.disabled = false;
    printTree.disabled = true;
  } else {
    printFlat.disabled = true;
    printTree.disabled = false;
  }
}

// Reload avec params
function reload() {
	const params = new URLSearchParams(window.location.search);
	params.set('groupe', selG.value);
	params.set('vue',   selV.value);
	window.location.search = params.toString();
}

// Afficher ou masquer le slider selon la vue
function toggleSlider() {
	if (selV.value === 'flat') {
		sliderLabel.style.display = 'none';
	} else {
		sliderLabel.style.display = '';
	}
}

// Applique la classe "reduit" aux éléments hors seuil
function updateDisplay() {
	const delta = parseInt(slider.value, 10);
	val.textContent = delta;

	// Calcul du niveau min parmi les éléments affichés
	const levels = Array.from(elems).map(el => parseInt(el.dataset.level, 10));
	const minLvl = Math.min(...levels);

	elems.forEach(el => {
		const lvl = parseInt(el.dataset.level, 10);
		if (lvl <= minLvl + delta) {
			el.classList.remove('reduit');
		} else {
			el.classList.add('reduit');
		}
	});
}

/* ---------- Event Listeners ---------- */
document.addEventListener('DOMContentLoaded', function() {
	const txt = selG.options[selG.selectedIndex].text;
	title.innerHTML =  txt.trim();
	
	// appeler au chargement et à chaque changement de vue
	selV.addEventListener('change', togglePrintStyles);
	togglePrintStyles();

	selG.addEventListener('change', reload);
	selV.addEventListener('change', reload);
	selV.addEventListener('change', toggleSlider);
	toggleSlider();

	slider.addEventListener('input', updateDisplay);
	updateDisplay();
	
	// Avant le print (tous les navigateurs modernes)
	window.addEventListener('beforeprint', adjustHierScale);
	// Pour certains qui supportent matchMedia
	window.matchMedia('print').addListener(mq => {
		if (mq.matches) adjustHierScale();
	});
});
