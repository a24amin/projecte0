// Petición para obtener preguntas desde la API usando ruta absoluta
fetch("/projecte0/php/getPreguntes.php?n=10")
  .then(r => r.json())
  .then(data => {
    console.log("Total de preguntes carregades:", data.preguntes.length);

    const contenidor = document.getElementById("questionari");
    const marcadorDiv = document.getElementById("marcador");
    const tempsDiv = document.getElementById("temps");
    const btnEnviar = document.getElementById("btnEnviar");
    const btnAnterior = document.getElementById("btnAnterior");
    const btnSeguent = document.getElementById("btnSeguent");

    let estat = { contador: 0, respostes: [] };
    let indexActual = 0;

    let segons = 0;
    const interval = setInterval(() => {
      segons++;
      tempsDiv.textContent = `Temps: ${segons}s`;
    }, 1000);

    const renderMarcador = () => {
      marcadorDiv.textContent = 
        `Pregunta ${indexActual + 1} de ${data.preguntes.length} | Respostes: ${estat.contador}/${data.preguntes.length}`;
    };

    contenidor.innerHTML = data.preguntes.map((p, i) => `
      <div class="pregunta ${i === 0 ? "activa" : ""}" data-i="${i}">
        <h3>${p.pregunta}</h3>
        <div class="d-flex flex-wrap gap-2">
          ${p.respostes.map((r, j) => `
            <button class="resposta btn btn-light border" data-p="${i}" data-id="${r.id}">
              <img src="/projecte0/${r.imatge}" alt="${r.etiqueta}" style="height:80px;">
            </button>
          `).join('')}
        </div>
      </div>
    `).join('');

    function mostrarPregunta(i) {
      document.querySelectorAll(".pregunta").forEach(div => div.classList.remove("activa"));
      const activa = document.querySelector(`.pregunta[data-i="${i}"]`);
      if (activa) activa.classList.add("activa");

      indexActual = i;
      btnAnterior.disabled = (indexActual === 0);
      btnSeguent.disabled = (indexActual === data.preguntes.length - 1);

      renderMarcador();
    }

    btnAnterior.addEventListener("click", () => {
      if (indexActual > 0) mostrarPregunta(indexActual - 1);
    });

    btnSeguent.addEventListener("click", () => {
      if (indexActual < data.preguntes.length - 1) mostrarPregunta(indexActual + 1);
    });

    contenidor.addEventListener("click", e => {
      const btn = e.target.closest("button.resposta");
      if (!btn) return;
      const i = parseInt(btn.dataset.p);
      const respostaId = parseInt(btn.dataset.id);
      const preguntaId = parseInt(btn.dataset.i);

      estat.respostes[i] = {
        pregunta_id: preguntaId,
        resposta_id: respostaId
      };
      estat.contador = estat.respostes.filter(x => x !== undefined).length;
      renderMarcador();

      if (estat.contador === data.preguntes.length) {
        clearInterval(interval);
        btnEnviar.classList.remove("hidden");
      }
    });

    btnEnviar.addEventListener("click", () => {
      fetch("/projecte0/php/finalitza.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ respostes: estat.respostes })
      })
      .then(r => r.json())
      .then(res => {
        alert(`Puntuació: ${res.puntuacio} de ${res.total}`);
      })
      .catch(e => console.error("Error en finalitzar:", e));
    });

    mostrarPregunta(0);
  })
  .catch(e => console.error("Error carregant les preguntes:", e));
