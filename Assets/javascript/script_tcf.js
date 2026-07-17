// //----------------------- toggle-----------------

// let navbar = document.querySelector(".navLinks");
// let menubar = document.querySelector("#menuBTN");

// menubar.onclick = () => {
//   menubar.classList.toggle("bx-x");
//   navbar.classList.toggle("active");
// };

// // Système de Scroll to Top
// const scrollTopBtn = document.querySelector(".floating-scroll-top");

// window.addEventListener("scroll", () => {
//   scrollTopBtn.classList.toggle("visible", window.scrollY > 300);
// });

// scrollTopBtn.addEventListener("click", () => {
//   window.scrollTo({
//     top: 0,
//     behavior: "smooth",
//   });
// });

// Navbar: lien actif + quickmenu uniquement.
// Le toggle du menu mobile (#menuBTN) est géré dans includes/header.php (source unique).
(function () {
  var navbar = document.querySelector(".navLinks");
  var quickBtn = document.getElementById("headerQuickMenuBtn");
  var quickDrop = document.getElementById("headerQuickMenuDropdown");

  if (quickBtn && quickDrop) {
    quickBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      var open = quickDrop.classList.toggle("is-open");
      quickBtn.setAttribute("aria-expanded", open ? "true" : "false");
    });

    quickDrop.addEventListener("click", function (e) {
      var a = e.target.closest && e.target.closest("a");
      if (!a) return;
      quickDrop.classList.remove("is-open");
      quickBtn.setAttribute("aria-expanded", "false");
    });

    document.addEventListener("click", function (e) {
      if (!quickDrop.classList.contains("is-open")) return;
      if (e.target === quickBtn || quickBtn.contains(e.target)) return;
      if (e.target === quickDrop || quickDrop.contains(e.target)) return;
      quickDrop.classList.remove("is-open");
      quickBtn.setAttribute("aria-expanded", "false");
    });
  }

  // Lien actif selon la page courante
  if (!navbar) return;
  try {
    var path = (window.location.pathname || "").toLowerCase();
    var file = path.split("/").pop() || "";
    if (!file) file = "index.php";

    var links = navbar.querySelectorAll("a[href]");
    links.forEach(function (a) {
      a.classList.remove("active");
    });

    var best = null;
    links.forEach(function (a) {
      var href = (a.getAttribute("href") || "").toLowerCase();
      if (!href || href.indexOf("javascript:") === 0 || href.indexOf("#") === 0) return;
      // comparer sur le nom de fichier uniquement
      var hfile = href.split("?")[0].split("#")[0].split("/").pop();
      if (hfile && hfile === file) best = a;
    });

    if (!best && file === "index.php") {
      links.forEach(function (a) {
        var href = (a.getAttribute("href") || "").toLowerCase();
        var hfile = href.split("?")[0].split("#")[0].split("/").pop();
        if (hfile === "index.php") best = a;
      });
    }

    if (best) best.classList.add("active");
  } catch (e) {}
})();

// // Système de Chat
// const chatBtn = document.querySelector(".floating-chat-btn");
// const chatContainer = document.querySelector(".floating-chat-container");
// const closeChatBtn = document.querySelector(".chat-close-btn");
// const chatMessages = document.querySelector(".chat-messages");
// const chatInput = document.querySelector(".chat-input-area textarea");
// const sendBtn = document.querySelector(".send-btn");

// // Ouvrir/fermer le chat
// chatBtn.addEventListener("click", () => {
//   chatContainer.classList.toggle("visible");
// });

// closeChatBtn.addEventListener("click", () => {
//   chatContainer.classList.remove("visible");
// });

// // Envoyer un message
// function sendMessage() {
//   const message = chatInput.value.trim();
//   if (message) {
//     // Ajouter le message de l'utilisateur
//     addMessage(message, "user");

//     // Réponse automatique (simulation)
//     setTimeout(() => {
//       addMessage(
//         "Nous avons reçu votre message. Un administrateur vous répondra bientôt.",
//         "admin"
//       );
//     }, 1000);

//     // Effacer l'input
//     chatInput.value = "";
//     resetTextareaHeight();
//   }
// }

// function addMessage(content, sender) {
//   const now = new Date();
//   const timeString =
//     now.getHours().toString().padStart(2, "0") +
//     ":" +
//     now.getMinutes().toString().padStart(2, "0");

//   const messageDiv = document.createElement("div");
//   messageDiv.className = `message ${sender}-message`;
//   messageDiv.innerHTML = `
//         <div class="message-content">${content}</div>
//         <div class="message-time">${timeString}</div>
//     `;

//   chatMessages.appendChild(messageDiv);
//   chatMessages.scrollTop = chatMessages.scrollHeight;
// }

// // Gestion de la taille du textarea
// function resetTextareaHeight() {
//   chatInput.style.height = "auto";
//   chatInput.style.height = chatInput.scrollHeight + "px";
// }

// chatInput.addEventListener("input", () => {
//   chatInput.style.height = "auto";
//   chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + "px";
// });

// // Envoyer avec le bouton ou Enter
// sendBtn.addEventListener("click", sendMessage);

// chatInput.addEventListener("keydown", (e) => {
//   if (e.key === "Enter" && !e.shiftKey) {
//     e.preventDefault();
//     sendMessage();
//   }
// });

// // Initialisation
// resetTextareaHeight();
