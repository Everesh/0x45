// resize: none killed the drag grabber, sizing happens here instead,
// the rows attribute acts as the minimum
function fit(textarea) {
  textarea.style.height = "auto";
  textarea.style.height = textarea.scrollHeight + "px";
}

function toggleBox(btn, mode) {
  const post = btn.closest(".reply, .thread-anchor");
  const box = post.querySelector(":scope > .leechBox");
  const form = box.querySelector("form");
  const textarea = form.querySelector("textarea");

  // same button again folds the box back up
  if (box.classList.contains("open") && box.dataset.mode === mode) {
    box.classList.remove("open");
    return;
  }

  form.action = form.dataset[mode];
  textarea.value =
    mode === "edit"
      ? post.querySelector(":scope > .postContent").textContent
      : "";
  fit(textarea);
  box.dataset.mode = mode;
  box.classList.add("open");
  textarea.focus();
}

async function deletePost(btn) {
  if (!confirm(btn.dataset.confirm ?? "delete this leech?")) return;

  const post = btn.closest(".reply, .thread-anchor");
  const anchor = post.querySelector(
    ":scope > .leechBox input[name=anchor]",
  ).value;

  const res = await fetch(btn.dataset.url, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ anchor }),
  });
  if (!res.ok) return;

  location.href = (await res.json()).redirect;
}

document.querySelectorAll("button[data-leech]").forEach((btn) => {
  const mode = btn.dataset.leech;

  btn.addEventListener("click", () =>
    mode === "del" ? deletePost(btn) : toggleBox(btn, mode),
  );
});

document.querySelectorAll(".leechBox textarea").forEach((textarea) => {
  textarea.addEventListener("input", () => fit(textarea));
});

document.querySelectorAll(".leechBox button[data-cancel]").forEach((btn) => {
  btn.addEventListener("click", () =>
    btn.closest(".leechBox").classList.remove("open"),
  );
});
