function asHex(n) {
  const sign = n < 0 ? "-" : "";
  return sign + "0x" + Math.abs(n).toString(16).padStart(3, "0");
}

async function sendVote(box, vote) {
  const res = await fetch(box.dataset.url, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ vote }),
  });
  if (!res.ok) return;

  const { rating, vote: myVote } = await res.json();
  box.querySelector("p").textContent = asHex(rating);
  box.querySelectorAll("button[data-vote]").forEach((btn) => {
    btn.classList.toggle("set", Number(btn.dataset.vote) === myVote);
  });
}

document.querySelectorAll(".endorse").forEach((box) => {
  box.querySelectorAll("button[data-vote]").forEach((btn) => {
    btn.addEventListener("click", () => sendVote(box, btn.dataset.vote));
  });
});
