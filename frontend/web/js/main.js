const now = new Date();
const currentDay = now.toDateString(); // Fecha en formato humano legible
const lastReloadDay = localStorage.getItem("lastReloadDay");

// Si es un nuevo día, recargamos la página
if (lastReloadDay !== currentDay) {
  localStorage.setItem("lastReloadDay", currentDay);
  location.reload();
} else {
  console.log("La página ya se recargó hoy.");
}

// Calcular cuánto tiempo falta para el próximo día
const nextMidnight = new Date(
  now.getFullYear(),
  now.getMonth(),
  now.getDate() + 1,
  0,
  0,
  0
);
const timeUntilNextDay = nextMidnight - now;

console.log(`Tiempo restante hasta el próximo día: ${timeUntilNextDay} ms`);

// Programar la recarga al llegar a medianoche
setTimeout(() => {
  localStorage.setItem("lastReloadDay", nextMidnight.toDateString());
  location.reload();
}, timeUntilNextDay);
