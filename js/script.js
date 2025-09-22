fetch("js/data.json")
  .then(r => r.json())
  .then(data => {
    const contenidor = document.getElementById("questionari");
    const marcadorDiv = document.getElementById("marcador");
    const tempsDiv = document.getElementById("temps");
    const btnEnviar = document.getElementById("btnEnviar");

    let estat = { contador: 0, respostes: [] };

    // --- 🕒 Comptador de temps ---
    let segons = 0;
    const interval = setInterval(() => {
      segons++;
      tempsDiv.textContent = `Temps: ${segons}s`;
    }, 1000);

    const renderMarcador = () => {
      marcadorDiv.textContent = `Preguntes respostes: ${estat.contador} de ${data.preguntes.length}`;
    };
    renderMarcador();

    contenidor.innerHTML = data.preguntes.map((p, i) =>
      `<h3>${p.pregunta}</h3>` +
      p.respostes.map((r, j) =>
        `<button class="resposta" data-p="${i}" data-r="${j}">
           <img src="${r.imatge}" alt="${r.etiqueta}" style="height:80px;">
         </button>`).join('') +
      `<hr>`).join('');

    contenidor.addEventListener("click", e => {
      const btn = e.target.closest("button.resposta");
      if (!btn) return;
      const i = parseInt(btn.dataset.p), j = parseInt(btn.dataset.r);

      estat.respostes[i] = j;
      estat.contador = estat.respostes.filter(x => x !== undefined).length;
      renderMarcador();

      if (estat.contador === data.preguntes.length) {
        clearInterval(interval); // ⏸️ Para el temps quan es responen totes les preguntes
        btnEnviar.classList.remove("hidden");
      }

      console.log(`Pregunta ${i+1}, resposta ${j+1}`);
    });
  })
  .catch(e => console.error("Error carregant el JSON:", e));
