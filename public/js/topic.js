// the new-topic box rides the leechBox collapse animation,
// cancel is handled by leech.js's generic data-cancel listener
document.querySelectorAll("[data-topic-new]").forEach((item) => {
  item.addEventListener("click", () => {
    const box = document.querySelector(".list > .leechBox");
    box.classList.toggle("open");
    if (box.classList.contains("open")) box.querySelector("input").focus();
  });
});

document.querySelectorAll("button[data-topic-del]").forEach((btn) => {
  btn.addEventListener("click", async () => {
    if (!confirm(btn.dataset.confirm)) return;

    const res = await fetch(btn.dataset.url, { method: "POST" });
    if (!res.ok) return;

    location.href = (await res.json()).redirect;
  });
});
