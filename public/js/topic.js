// the new-topic / new-thread boxes ride the leechBox collapse animation,
// cancel is handled by leech.js's generic data-cancel listener
document
  .querySelectorAll("[data-topic-new], [data-thread-new]")
  .forEach((item) => {
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

// follow/unfollow toggle, no reload -- only the feed (a different page)
// changes, the current topic's threads don't
document.querySelectorAll("button[data-affinity]").forEach((btn) => {
  btn.addEventListener("click", async () => {
    const res = await fetch(btn.dataset.url, { method: "POST" });
    if (!res.ok) return;

    const { following } = await res.json();
    btn.textContent = following ? "unfollow" : "follow";
    btn.classList.toggle("set", following);
  });
});
