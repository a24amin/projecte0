// ELEMENTS PRINCIPALS // 
// Contenidor on es carregarà el contingut dinàmic del joc
const mainContent = document.getElementById('main-content')

// Botó per iniciar el quiz
const btnQuiz = document.getElementById('btnQuiz')

// Botó per accedir a la pàgina d'administració
const btnAdmin = document.getElementById('btnAdmin')

// Contenidor del landing (pantalla inicial)
const landing = document.getElementById('landing')

// FUNCIONS AUXILIARS // 

// Funció per crear el header dinàmic amb botons "Comença el Quiz" i "Admin"
function crearHeader() {
  const header = document.createElement('div')
  header.classList.add('d-flex', 'justify-content-center', 'gap-2', 'mb-4', 'flex-wrap')

  // HTML dels botons del header
  header.innerHTML = `
    <button id="btnHeaderQuiz" class="btn btn-primary flex-fill flex-md-auto">Comença el Quiz</button>
    <button id="btnHeaderAdmin" class="btn btn-secondary flex-fill flex-md-auto">Admin</button>
  `
  mainContent.prepend(header)

  // Captura dels botons per assignar-los els esdeveniments
  const btnHeaderQuiz = document.getElementById('btnHeaderQuiz')
  const btnHeaderAdmin = document.getElementById('btnHeaderAdmin')

  // Quan es prem el botó del quiz, es carrega la plantilla i s'inicia el quiz
  btnHeaderQuiz.addEventListener('click', () => {
    mainContent.innerHTML = ''
    crearHeader()
    const template = document.getElementById('quiz-template').content.cloneNode(true)
    mainContent.appendChild(template)
    window.scrollTo({ top: 0, behavior: 'smooth' })
    iniciarQuiz()
  })

  // Quan es prem el botó d'administració, es mostra l'iframe de l'admin
  btnHeaderAdmin.addEventListener('click', () => {
    mainContent.innerHTML = `<iframe src="/projecte0/php/admin/admin.php" style="width:100%;height:80vh;border:none;"></iframe>`
    crearHeader()
    window.scrollTo({ top: 0, behavior: 'smooth' })
  })
}

// ESDEVENIMENTS DEL LANDING// 
// Quan es prem el botó de Quiz al landing
btnQuiz.addEventListener('click', () => {
  landing.classList.add('hidden') // Oculta el landing
  mainContent.style.display = 'block' // Mostra el main-content
  mainContent.innerHTML = ''
  crearHeader()
  const template = document.getElementById('quiz-template').content.cloneNode(true)
  mainContent.appendChild(template)
  window.scrollTo({ top: 0, behavior: 'smooth' })
  iniciarQuiz()
})

// Quan es prem el botó d'Admin al landing
btnAdmin.addEventListener('click', () => {
  landing.classList.add('hidden') // Oculta el landing
  mainContent.style.display = 'block' // Mostra el main-content
  mainContent.innerHTML = ''
  crearHeader()
  mainContent.innerHTML += `<iframe src="/projecte0/php/admin/admin.php" style="width:100%;height:80vh;border:none;"></iframe>`
  window.scrollTo({ top: 0, behavior: 'smooth' })
})

// FUNCIO PRINCIPAL DEL QUIZ // 
function iniciarQuiz() {
  // Captura dels elements principals del quiz
  const formulariContainer = document.getElementById('formulariNom')
  const formulari = document.getElementById('formulari')
  const inputNom = document.getElementById('nom')
  const salutacio = document.getElementById('salutacio')
  const missatge = document.getElementById('missatge')
  const botoEsborrar = document.getElementById('esborrarNom')
  const contenidor = document.getElementById('questionari')
  const marcadorDiv = document.getElementById('marcador')
  const tempsDiv = document.getElementById('temps')
  const btnEnviar = document.getElementById('btnEnviar')
  const btnAnterior = document.getElementById('btnAnterior')
  const btnSeguent = document.getElementById('btnSeguent')
  const resultatDiv = document.getElementById('resultat')

  // Funció per mostrar la salutació amb el nom de l'usuari
  function mostrarSalutacio(nom) {
    formulariContainer.style.display = 'none' // Oculta el formulari
    salutacio.style.display = 'block' // Mostra la salutació
    missatge.textContent = `Hola, ${nom}!`
  }

  // Si ja hi ha un nom guardat a localStorage, mostrar directament
  const nomGuardat = localStorage.getItem('nom')
  if (nomGuardat) mostrarSalutacio(nomGuardat)

  // Enviament del formulari per guardar el nom
  formulari.addEventListener('submit', e => {
    e.preventDefault()
    const nom = inputNom.value.trim()
    if (nom) {
      localStorage.setItem('nom', nom)
      mostrarSalutacio(nom)
    }
  })

  // Botó per esborrar el nom guardat
  botoEsborrar.addEventListener('click', () => {
    localStorage.removeItem('nom')
    salutacio.style.display = 'none'
    formulariContainer.style.display = 'block'
    inputNom.value = ''
  })

  // FETCH PREGUNTES DES DEL SERVIDOR //
  fetch('/projecte0/php/getPreguntes.php?n=10') // Demana 10 preguntes aleatòries
    .then(res => res.json()) // Converteix la resposta a JSON
    .then(data => {
      const totalPreguntes = data.preguntes.length
      let estat = { respostes: Array(totalPreguntes).fill(null) } // Estat de respostes
      let indexActual = 0
      let segons = 0

      // Temporitzador que s'actualitza cada segon
      const interval = setInterval(() => {
        segons++
        tempsDiv.textContent = `Temps: ${segons}s`
      }, 1000)

      // Funció per actualitzar el marcador de preguntes i respostes
      function renderMarcador() {
        const contestades = estat.respostes.filter(x => x !== null).length
        marcadorDiv.textContent = `Pregunta ${indexActual + 1} de ${totalPreguntes} | Respostes: ${contestades}/${totalPreguntes}`
      }

      // Funció per mostrar una pregunta concreta
      function mostrarPregunta(i) {
        contenidor.innerHTML = ''
        const p = data.preguntes[i]

        const div = document.createElement('div')
        div.classList.add('pregunta', 'activa')
        div.innerHTML = `
          <h3>${p.pregunta}</h3>
          <div class="respuestas-container">
            ${p.respostes.map(r => `
              <button class="resposta btn btn-light border" data-p="${i}" data-id="${r.id}" data-pid="${r.pregunta_id}">
                <img src="/projecte0/${r.imatge}" alt="${r.etiqueta}">
              </button>
            `).join('')}
          </div>
        `
        contenidor.appendChild(div)
        indexActual = i
        btnAnterior.disabled = (indexActual === 0) // Deshabilita botó anterior si és la primera pregunta
        btnSeguent.disabled = (indexActual === totalPreguntes - 1) // Deshabilita botó següent si és l'última
        renderMarcador()
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }

      // Navegació entre preguntes amb botons
      btnAnterior.addEventListener('click', () => {
        if (indexActual > 0) mostrarPregunta(indexActual - 1)
      })
      btnSeguent.addEventListener('click', () => {
        if (indexActual < totalPreguntes - 1) mostrarPregunta(indexActual + 1)
      })

      // Selecció de resposta de l'usuari
      contenidor.addEventListener('click', e => {
        const btn = e.target.closest('button.resposta')
        if (!btn) return
        const i = parseInt(btn.dataset.p)
        const respostaId = parseInt(btn.dataset.id)
        const preguntaId = parseInt(btn.dataset.pid)
        estat.respostes[i] = { pregunta_id: preguntaId, resposta_id: respostaId }
        renderMarcador()

        // Si l'usuari ha respost totes les preguntes, mostra el botó d'enviar
        const contestades = estat.respostes.filter(x => x !== null).length
        if (contestades === totalPreguntes) {
          clearInterval(interval) // Para el temporitzador
          btnEnviar.classList.remove('hidden')
        }
      })

      // Enviament dels resultats al backend
      btnEnviar.addEventListener('click', () => {
        fetch('/projecte0/php/finalitza.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ respostes: estat.respostes })
        })
          .then(res => res.json())
          .then(res => {
            resultatDiv.textContent = `Has obtingut ${res.puntuacio} de ${totalPreguntes} punts!`
            btnEnviar.style.display = 'none'
          })
      })

      mostrarPregunta(0) // Mostra la primera pregunta inicialment
    })
}
