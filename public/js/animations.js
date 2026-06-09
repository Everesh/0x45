const CHARS =
  "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*";

function scramble(el) {
  const original = el.textContent;
  let intervalId = null;
  let timeoutId = null;

  function stop() {
    clearInterval(intervalId);
    clearTimeout(timeoutId);
    intervalId = null;
    timeoutId = null;
    el.textContent = original;
  }

  function start() {
    stop();
    intervalId = setInterval(() => {
      el.textContent = Array.from(
        { length: original.length },
        () => CHARS[Math.floor(Math.random() * CHARS.length)],
      ).join("");
    }, 40);
    timeoutId = setTimeout(stop, 500);
  }

  el.addEventListener("mouseenter", start);
  el.addEventListener("mouseleave", stop);
}

document.querySelectorAll("[hover-data-scramble]").forEach(scramble);
