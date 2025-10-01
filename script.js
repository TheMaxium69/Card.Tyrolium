/* Configuration de Particles.js */

// Récupère la couleur des particules depuis les variables CSS
const particlesColor = getComputedStyle(document.documentElement).getPropertyValue('--particles-color').trim();

particlesJS('particles-js', {
  "particles": {
    "number": {
      "value": 60, // Nombre de particules
      "density": {
        "enable": true,
        "value_area": 800
      }
    },
    "color": {
      "value": particlesColor // Couleur des particules
    },
    "shape": {
      "type": "circle",
      "stroke": {
        "width": 0,
        "color": "#000000"
      }
    },
    "opacity": {
      "value": 0.5,
      "random": true,
      "anim": {
        "enable": true,
        "speed": 1,
        "opacity_min": 0.1,
        "sync": false
      }
    },
    "size": {
      "value": 3,
      "random": true,
      "anim": {
        "enable": false
      }
    },
    "line_linked": {
      "enable": true,
      "distance": 150,
      "color": particlesColor, // Couleur des lignes
      "opacity": 0.4,
      "width": 1
    },
    "move": {
      "enable": true,
      "speed": 2, // Vitesse de déplacement
      "direction": "none",
      "random": false,
      "straight": false,
      "out_mode": "out",
      "bounce": false
    }
  },
  "interactivity": {
    "detect_on": "canvas",
    "events": {
      "onhover": {
        "enable": true,
        "mode": "grab" // Fait un effet de "saisie" au survol
      },
      "onclick": {
        "enable": false
      },
      "resize": true
    },
    "modes": {
      "grab": {
        "distance": 140,
        "line_linked": {
          "opacity": 1
        }
      }
    }
  },
  "retina_detect": true
});
