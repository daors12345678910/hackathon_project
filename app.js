const seedNeeds = [
  {id:1, org:"St. Anne’s Shelter", initials:"SA", logo:"coral", location:"Easton · 1.2 miles", category:"Winter coats", sizes:["M","L","XL"], quantity:24, pledged:16, priority:"Urgent", needed:"by 28 Nov", details:"Warm coats needed for the winter cold."},
  {id:2, org:"Care & Share Bristol", initials:"CS", logo:"", location:"Redfield · 2.8 miles", category:"Adult jumpers", sizes:["S","M","L"], quantity:40, pledged:12, priority:"Winter", needed:"by 3 Dec", details:"Good-condition jumpers for adults."},
  {id:3, org:"The Family Table", initials:"FT", logo:"blue", location:"St Paul’s · 3.1 miles", category:"Children’s trousers", sizes:["4–6 yrs","7–9 yrs"], quantity:30, pledged:23, priority:"Standard", needed:"by 12 Dec", details:"Everyday trousers for growing families."},
  {id:4, org:"Northside Refuge", initials:"NR", logo:"", location:"Kingswood · 4.6 miles", category:"Women’s shoes", sizes:["UK 4–7"], quantity:18, pledged:5, priority:"Urgent", needed:"by 25 Nov", details:"Clean, sturdy shoes in good condition."},
  {id:5, org:"The Welcome Centre", initials:"WC", logo:"blue", location:"Montpelier · 5.2 miles", category:"T-shirts", sizes:["M","L","XL"], quantity:50, pledged:31, priority:"Standard", needed:"by 18 Dec", details:"Plain or patterned everyday tops."},
  {id:6, org:"Bristol Youth Space", initials:"BY", logo:"coral", location:"Southmead · 7.4 miles", category:"Winter jackets", sizes:["YS","YM","YL"], quantity:20, pledged:7, priority:"Winter", needed:"by 30 Nov", details:"Youth jackets for outdoor activities."}
];

let needs = JSON.parse(localStorage.getItem("threadkind_needs") || "null") || seedNeeds;
let pledges = JSON.parse(localStorage.getItem("threadkind_pledges") || "[]");
let charityMode = false;
let activeFilter = "all";
let activeNeed = null;

const $ = (id) => document.getElementById(id);

function save() {
  localStorage.setItem("threadkind_needs", JSON.stringify(needs));
  localStorage.setItem("threadkind_pledges", JSON.stringify(pledges));
}

function renderNeeds() {
  const query = $("searchInput").value.trim().toLowerCase();
  const filtered = needs.filter(n => {
    const matchesFilter = activeFilter === "all" || n.priority === activeFilter;
    const matchesSearch = !query || `${n.org} ${n.category} ${n.details}`.toLowerCase().includes(query);
    return matchesFilter && matchesSearch;
  });

  $("neededCount").textContent = filtered.length;
  $("needsGrid").innerHTML = filtered.length ? filtered.map(n => {
    const remaining = Math.max(0, n.quantity - n.pledged);
    const percent = Math.min(100, Math.round(n.pledged / n.quantity * 100));
    return `<article class="need-card">
      <div class="card-top">
        <div class="org-logo ${n.logo || ""}">${n.initials}</div>
        <div class="org-info"><strong>${n.org}</strong><span>${n.location}</span></div>
        <span class="urgency ${n.priority.toLowerCase()}">${n.priority}</span>
      </div>
      <div class="card-body">
        <h3>${n.category}</h3>
        <div class="need-meta">${n.sizes.map(s => `<span class="pill">Size ${s}</span>`).join("")}<span class="pill">Qty ${n.quantity}</span></div>
        <div class="progress-wrap"><div class="progress-label"><span>${n.pledged} pledged</span><span>${remaining} still needed</span></div><div class="progress"><span style="width:${percent}%"></span></div></div>
      </div>
      <div class="card-action"><span>Needed ${n.needed}</span><button class="pledge-btn" data-pledge="${n.id}">${charityMode ? "View request" : "Pledge item →"}</button></div>
    </article>`;
  }).join("") : `<div class="empty-state"><strong>No matching requests</strong><p>Try another search or filter.</p></div>`;

  document.querySelectorAll("[data-pledge]").forEach(button => {
    button.addEventListener("click", () => openPledge(Number(button.dataset.pledge)));
  });
}

function openPledge(id) {
  activeNeed = needs.find(n => n.id === id);
  if (!activeNeed) return;
  $("modalNeedTitle").textContent = `Pledge ${activeNeed.category.toLowerCase()}`;
  $("modalNeedOrg").textContent = `${activeNeed.org} · ${activeNeed.location}`;
  $("pledgeCategory").value = activeNeed.category.includes("coat") ? "Coats" : activeNeed.category.includes("jumper") ? "Jumpers" : activeNeed.category.includes("trouser") ? "Trousers" : activeNeed.category.includes("shoe") ? "Shoes" : "T-shirts";
  $("dropoffDate").value = new Date(Date.now() + 86400000).toISOString().slice(0,10);
  $("dropoffDate").min = new Date().toISOString().slice(0,10);
  updateMatchSummary();
  $("pledgeModal").showModal();
}

function updateMatchSummary() {
  $("matchSummary").textContent = `${$("pledgeQuantity").value} item${$("pledgeQuantity").value === "1" ? "" : "s"} · ${$("pledgeSize").value} · ${$("pledgeCondition").value} condition`;
}

function renderPledges() {
  $("pledgeCount").textContent = pledges.length;
  $("impactTotal").textContent = pledges.reduce((sum,p) => sum + Number(p.quantity), 0);
  $("impactDelivered").textContent = pledges.filter(p => p.received).reduce((sum,p) => sum + Number(p.quantity), 0);

  $("pledgeList").innerHTML = pledges.length ? pledges.map((p, index) => `
    <article class="pledge-row">
      <div class="org-logo ${p.logo || ""}">${p.initials}</div>
      <div><h3>${p.category} · ${p.quantity} item${p.quantity === 1 ? "" : "s"}</h3><p>${p.org} · Drop-off ${p.date} at ${p.slot}</p></div>
      <span class="status ${p.received ? "received" : ""}">${p.received ? "Arrived & checked" : "Awaiting drop-off"}</span>
      ${charityMode ? `<button class="receive-btn ${p.received ? "done" : ""}" data-receive="${index}">${p.received ? "✓ Received" : "Tick as arrived"}</button>` : `<button class="receive-btn" data-cancel="${index}">${p.received ? "Details" : "Cancel"}</button>`}
    </article>`).join("") : `<div class="empty-state"><strong>Your pledge list is empty</strong><p>Browse local needs and make a pledge to get started.</p></div>`;

  document.querySelectorAll("[data-receive]").forEach(btn => btn.addEventListener("click", () => {
    const p = pledges[Number(btn.dataset.receive)]; p.received = !p.received; save(); renderPledges(); showToast(p.received ? "Donation marked as received" : "Donation moved back to pending");
  }));
  document.querySelectorAll("[data-cancel]").forEach(btn => btn.addEventListener("click", () => {
    if (!pledges[Number(btn.dataset.cancel)].received && confirm("Cancel this pledge?")) { pledges.splice(Number(btn.dataset.cancel),1); save(); renderPledges(); renderNeeds(); showToast("Pledge cancelled"); }
  }));
}

function showToast(message) {
  $("toast").textContent = message; $("toast").classList.add("show");
  setTimeout(() => $("toast").classList.remove("show"), 2600);
}

$("pledgeForm").addEventListener("submit", e => {
  e.preventDefault();
  if (!activeNeed) return;
  const quantity = Number($("pledgeQuantity").value);
  pledges.unshift({org:activeNeed.org, initials:activeNeed.initials, logo:activeNeed.logo, category:`${$("pledgeCategory").value} — ${$("pledgeSize").value}`, quantity, date:$("dropoffDate").value, slot:$("dropoffSlot").value, received:false});
  activeNeed.pledged = Math.min(activeNeed.quantity, activeNeed.pledged + quantity);
  save(); renderNeeds(); renderPledges(); $("pledgeModal").close(); showToast("Pledge confirmed — thank you!");
});

$("needForm").addEventListener("submit", e => {
  e.preventEventDefault?.();
  e.preventDefault();
  const quantity = Number($("needQuantity").value);
  const categoryMap = {Coats:"Coats",Jumpers:"Jumpers",Trousers:"Trousers",Shoes:"Shoes","T-shirts":"T-shirts"};
  const category = categoryMap[$("needCategory").value];
  needs.unshift({id:Date.now(),org:"Leah’s Local Charity",initials:"LC",logo:"",location:"Bristol · 0.8 miles",category, sizes:[$("needSize").value],quantity,pledged:0,priority:$("needPriority").value,needed:`by ${$("needBy").value || "30 Dec"}`});
  save(); renderNeeds(); $("needModal").close(); showToast("Your need is now live");
});

$("pledgeQuantity").addEventListener("input", updateMatchSummary);
$("pledgeSize").addEventListener("change", updateMatchSummary);
$("pledgeCondition").addEventListener("change", updateMatchSummary);
$("searchInput").addEventListener("input", renderNeeds);

document.querySelectorAll(".filter-btn[data-filter]").forEach(btn => btn.addEventListener("click", () => {
  document.querySelectorAll(".filter-btn[data-filter]").forEach(b => b.classList.remove("active")); btn.classList.add("active"); activeFilter = btn.dataset.filter; renderNeeds();
}));

document.querySelectorAll(".nav-item[data-view]").forEach(btn => btn.addEventListener("click", () => {
  document.querySelectorAll(".nav-item").forEach(n => n.classList.remove("active")); btn.classList.add("active");
  document.querySelectorAll(".view").forEach(v => v.classList.remove("active"));
  $(btn.dataset.view + "View").classList.add("active");
  $("pageTitle").textContent = btn.dataset.view === "discover" ? "Find a local need" : btn.dataset.view === "pledges" ? "Your donations" : "Your impact";
  if (btn.dataset.view === "pledges") renderPledges();
}));

$("roleSwitch").addEventListener("click", () => {
  charityMode = !charityMode;
  $("roleLabel").textContent = charityMode ? "Donor view" : "Charity view";
  $("eyebrow").textContent = charityMode ? "CHARITY PORTAL" : "DONOR PORTAL";
  $("postNeedBtn").hidden = !charityMode;
  if (charityMode) $("pageTitle").textContent = "Manage your requests";
  renderNeeds(); renderPledges(); showToast(charityMode ? "Charity view enabled" : "Donor view enabled");
});

$("postNeedBtn").addEventListener("click", () => $("needModal").showModal());
$("locationBtn").addEventListener("click", () => showToast("Location set to Bristol, UK"));

renderNeeds(); renderPledges();
