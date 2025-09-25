const mainContent = document.getElementById('main-content');
const btnQuiz = document.getElementById('btnQuiz');
const btnAdmin = document.getElementById('btnAdmin');

btnQuiz.addEventListener('click', () => cargarQuiz());
btnAdmin.addEventListener('click', () => cargarAdmin());

function cargarQuiz() {
  mainContent.innerHTML = document.querySelector('#quiz-template').innerHTML;
  iniciarQuiz();
}

function cargarAdmin() {
  mainContent.innerHTML = document.querySelector('#admin-template').innerHTML;
  iniciarAdmin();
}

function iniciarQuiz() {

  const formulariContainer = mainContent.querySelector('#formulariNom');
  const formulari = mainContent.querySelector('#formulari');
  const inputNom = mainContent.querySelector('#nom');
  const salutacio = mainContent.querySelector('#salutacio');
  const missatge = mainContent.querySelector('#missatge');
  const botoEsborrar = mainContent.querySelector('#esborrarNom');

  function mostrarSalutacio(nom) {
    formulariContainer.style.display = 'none';
    salutacio.style.display = 'block';
    missatge.textContent = `Hola, ${nom}!`;
  }

  const nomGuardat = localStorage.getItem('nom');
  if (nomGuardat) mostrarSalutacio(nomGuardat);

  formulari.addEventListener('submit', e => {
    e.preventDefault();
    const nom = inputNom.value.trim();
    if (nom) {
      localStorage.setItem('nom', nom);
      mostrarSalutacio(nom);
    }
  });

  botoEsborrar.addEventListener('click', () => {
    localStorage.removeItem('nom');
    salutacio.style.display = 'none';
    formulariContainer.style.display = 'block';
    inputNom.value = '';
  });

  fetch("/projecte0/php/getPreguntes.php?n=10")
    .then(res => res.json())
    .then(data => {
      const contenidor = mainContent.querySelector("#questionari");
      const marcadorDiv = mainContent.querySelector("#marcador");
      const tempsDiv = mainContent.querySelector("#temps");
      const btnEnviar = mainContent.querySelector("#btnEnviar");
      const btnAnterior = mainContent.querySelector("#btnAnterior");
      const btnSeguent = mainContent.querySelector("#btnSeguent");
      const resultatDiv = mainContent.querySelector("#resultat");

      const totalPreguntes = data.preguntes.length;
      let estat = { contador: 0, respostes: Array(totalPreguntes).fill(null) };
      let indexActual = 0;

      let segons = 0;
      const interval = setInterval(() => {
        segons++;
        tempsDiv.textContent = `Temps: ${segons}s`;
      }, 1000);

      const renderMarcador = () => {
        const contestadas = estat.respostes.filter(x => x !== null).length;
        marcadorDiv.textContent =
          `Pregunta ${indexActual + 1} de ${totalPreguntes} | Respostes: ${contestadas}/${totalPreguntes}`;
      };

      function mostrarPregunta(i) {
        contenidor.innerHTML = '';

        const p = data.preguntes[i];
        const div = document.createElement('div');
        div.classList.add('pregunta', 'activa');

        div.innerHTML = `
          <h3>${p.pregunta}</h3>
          <div class="d-flex flex-wrap gap-2">
            ${p.respostes.map(r => `
              <button class="resposta btn btn-light border" data-p="${i}" data-id="${r.id}" data-pid="${r.idPregunta}">
                <img src="/projecte0/${r.imatge}" alt="${r.etiqueta}" style="height:80px;">
              </button>
            `).join('')}
          </div>
        `;

        contenidor.appendChild(div);

        indexActual = i;
        btnAnterior.disabled = (indexActual === 0);
        btnSeguent.disabled = (indexActual === totalPreguntes - 1);

        renderMarcador();
      }

      btnAnterior.addEventListener('click', () => {
        if (indexActual > 0) mostrarPregunta(indexActual - 1);
      });
      btnSeguent.addEventListener('click', () => {
        if (indexActual < totalPreguntes - 1) mostrarPregunta(indexActual + 1);
      });

      contenidor.addEventListener('click', e => {
        const btn = e.target.closest("button.resposta");
        if (!btn) return;

        const i = parseInt(btn.dataset.p);
        const respostaId = parseInt(btn.dataset.id);
        const preguntaId = parseInt(btn.dataset.pid);

        estat.respostes[i] = { pregunta_id: preguntaId, resposta_id: respostaId };
        renderMarcador();

        const contestadas = estat.respostes.filter(x => x !== null).length;
        if (contestadas === totalPreguntes) {
          clearInterval(interval);
          btnEnviar.classList.remove("hidden");
        }
      });

      btnEnviar.addEventListener('click', () => {
        fetch("/projecte0/php/finalitza.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ respostes: estat.respostes })
        })
        .then(res => res.json())
        .then(res => {
          resultatDiv.textContent = `Has obtingut ${res.puntuacio} de ${res.total} punts!`;
          btnEnviar.style.display = 'none';
        })
        .catch(e => console.error("Error al enviar resultados:", e));
      });

      mostrarPregunta(0);
    })
    .catch(e => console.error("Error cargando preguntas:", e));
}

