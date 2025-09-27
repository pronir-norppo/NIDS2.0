<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>NIDS 2.0 - Fixed</title>
  <!-- Leaflet & PapaParse -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    :root{--bg:#121212; --panel:#1f1f1f; --muted:#777; --accent:#00bfff; --gold:#ffcc00; --card:#222;}
    html,body{height:100%;margin:0;background:var(--bg);color:#eee;font-family:Inter,Arial,Helvetica,sans-serif}
    .app{display:flex;height:100vh}
    .sidebar {width: 28%; padding: 16px; background: var(--panel); box-sizing: border-box; overflow-y: auto; scrollbar-width: none;}
    .sidebar::-webkit-scrollbar {display: none;}
    h1{margin:0 0 10px 0;color:var(--gold);font-size:16px}
    h2{margin:0 0 10px 0;color:var(--gold);font-size:12px}
    label{display:block;font-size:13px;color:#ddd;margin:10px 0 6px}
    select{width:100%;padding:8px;border-radius:6px;background:#2b2b2b;border:1px solid #333;color:#fff}
    button{margin-top:12px;padding:6px 12px;border-radius:6px;background:var(--accent);color:#fff;border:none;cursor:pointer}
    table{width:100%;border-collapse:collapse;margin-top:8px}
    th,td{padding:6px;border:1px solid #2a2a2a;font-size:12px}
    th{background:#2a2a2a;color:var(--gold);text-align:left}
    .crime-row{cursor:default}
    .crime-row.muted{opacity:0.35; pointer-events: none;} /* muted rows are unclickable */
    .person-row{cursor:pointer}
    .person-row.muted{opacity:0.45}
    .person-row.selected{background: linear-gradient(90deg, rgba(255,204,0,0.15), rgba(255,204,0,0.05)); color: #000; font-weight:600;}
    #personnelTable td, #personnelTable th{font-size:11px;}
    #crimeTable td, #personnelTable th{font-size:11px;}
    .main{flex:1;display:flex;flex-direction:column;padding:10px;box-sizing:border-box}
    #map{flex:1;border-radius:6px;overflow:hidden;position:relative}
    #loadingOverlay{position:fixed;left:0;top:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center; background: rgba(0,0,0,0.6); z-index:9999;pointer-events:none;opacity:0;transition:opacity .28s;}
    #loadingOverlay.active{pointer-events:all;opacity:1}
    .spinner{width:48px;height:48px;border-radius:50%;border:6px solid #222;border-top-color:var(--gold);animation:spin 1s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    #summaryBox{ position:absolute; bottom:12px; left:12px; background:black; color:yellow; padding:6px; border-radius:4px; font-size:11px; font-weight:500; z-index:500; max-width:360px; max-height:260px; overflow:auto; opacity:0.95;}
    #summaryBox table{border-collapse:collapse;}
    #summaryBox td{padding:2px 6px; border:1px solid #555; font-size:11px;}
    .leaflet-popup-content img {max-width:160px; border-radius:6px; margin-top:6px; display:block;}
    .table-container { max-height: 160px; overflow-y: hidden; border: 1px solid #333; border-radius: 4px; transition: max-height 0.3s ease; }
    .table-container.expanded { max-height: 380px; overflow-y: auto; }
    .toggle-link { color: var(--accent); font-size: 12px; cursor: pointer; margin-top: 4px; display: inline-block; }
    a.viber-link { color: var(--accent); text-decoration: none; font-weight:600; margin-right:8px; }
	
	/* POPUP: force block layout + wrapping for clearer lines */
.leaflet-popup-content { white-space: normal !important; word-break: break-word !important; }
.leaflet-popup-content-wrapper { max-width: 400px !important; }
.popup-personnel { font-size:13px; line-height:1.35; }
.popup-row { margin:4px 0; }

.popup-person { display:block; margin:6px 0; padding-bottom:4px; border-bottom:1px dashed rgba(0,0,0,0.12); }
.popup-person .person-name { display:block; font-weight:600; margin-bottom:2px; }
.popup-person .person-contact { display:block; margin-top:2px; }
.popup-person .person-contact a.viber-link { color: #00bfff; font-weight:700; text-decoration:none; margin-right:6px; }
.popup-person .person-contact a.call-link { margin-left:8px; text-decoration:none; }
.popup-photo { margin-top:8px; }
.popup-photo img { display:block; max-width:160px; border-radius:6px; }

	
	/* Stylish popup card */
.leaflet-popup-content-wrapper.custom-popup {
  background: #1f1f1f;
  border-radius: 12px;
  padding: 0;
  box-shadow: 0 4px 18px rgba(0,0,0,0.5);
  color: #eee;
  font-family: Inter, Arial, sans-serif;
}

.leaflet-popup-content.custom-popup {
  margin: 0;
  padding: 0;
}

.popup-card {
  padding: 12px 14px;
  max-width: 380px;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  
}

.popup-header h3 {
  margin: 0;
  font-size: 15px;
  color: #000000;
  background: rgba(255,204,0,0.15);
}

.popup-callsign {
  font-size: 12px;
  background: rgba(255,204,0,0.15);
  padding: 2px 6px;
  border-radius: 6px;
}

.popup-row {
  font-size: 13px;
  margin: 4px 0;
}

.popup-people {
  margin-top: 6px;
}

.popup-person {
  margin: 6px 0;
  padding: 6px;
  background: rgba(255,255,255,0.05);
  border-radius: 6px;
}

.popup-person .person-name {
  font-weight: 600;
  color: #00bfff;
}

.popup-photo {
  margin-top: 10px;
  text-align: center;
}

.popup-photo img {
  max-width: 100%;
  max-height: 200%;
  display: block;
  border-radius: 8px;
  border: 1px solid #333;
}

/* Force popup images to show properly */
.leaflet-popup-content .popup-photo img {
  max-width: 240px !important;
  max-height: 240px !important;
  display: block;
  border-radius: 8px;
  border: 1px solid #333;
}
	
  </style>
</head>
<body>
  <div class="app">
    <!-- SIDEBAR: Filters, Personnel, Crimes -->
    <div class="sidebar">
      <h1>NIDS 2.0 &nbsp;&nbsp;<button id="refreshBtn">Refresh</button></h1>

      <!-- Station / Shift / Type filters (chained) -->
      <label for="stationFilter">Station</label>
      <select id="stationFilter"><option value="All">All</option></select>

      <label for="shiftFilter">Shift</label>
      <select id="shiftFilter" disabled><option value="All">All</option></select>

      <label for="typeFilter">Deployment Type</label>
      <select id="typeFilter" disabled><option value="All">All</option></select>

      <!-- Personnel -->
      <h2 style="margin-top:16px">Personnel</h2>
      <div id="personnelContainer" class="table-container">
        <!-- Table now shows one row per PERSON (Name / Mobile / CallSign / Station) -->
        <table id="personnelTable">
          <thead><tr><th>Name</th><th>Mobile</th><th>CallSign</th><th>Station</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
      <span id="togglePersonnel" class="toggle-link" style="display:none;">Show more</span>

      <!-- Focus crimes (unclickable rows, greyed when zero) -->
      <h2 style="margin-top:20px">8 Focus Crimes</h2>
      <table id="crimeTable"><thead><tr><th>Crime</th><th>Barangay</th><th><center>Count</center></th></tr></thead><tbody></tbody></table>
    </div>

    <!-- MAIN: Map + Summary -->
    <div class="main">
      <div id="map">
        <div id="summaryBox">Summary...</div>
      </div>
    </div>
  </div>

  <!-- Loading overlay -->
  <div id="loadingOverlay"><div class="spinner"></div></div>

<script>
/* ===========================
   CONFIG / CONSTANTS
   ===========================
*/
const DEPLOYMENT_SHEET_CSV = "https://docs.google.com/spreadsheets/d/1xBoDI6UdfVjGTYCN6DQHoUj2tKJ-3vopgMYuwm_SbrU/gviz/tq?tqx=out:csv&gid=35222349";
const CRIME_SHEET_CSV      = "https://docs.google.com/spreadsheets/d/1ToFBmKsJ9uJDlbvKz1ImMfzrY2H1kIEMQpefxOolG9U/gviz/tq?tqx=out:csv&gid=0";
const PLACEHOLDER_IMG = "https://placehold.co/160x90?text=No+Photo";
const FOCUS_CRIMES = ["Murder","Homicide","Physical Injury","Rape","Robbery","Theft","Carnapping MC","Carnapping MV"];
const defaultCenter = [9.3, 123.2];
const defaultZoom = 9;

/* ===========================
   STATE & DOM REFERENCES
   ===========================
*/
let allData = [];         // parsed deployments (each deployment row)
let crimeData = [];       // parsed crimes
let displayedMarkers = []; // markers on map (one per deployment)
let highlightLayer = null; // golden highlight marker
let selectedRowEl = null;  // currently selected <tr> in personnel table
let personnelExpanded = false;

const stationSelect = document.getElementById("stationFilter");
const shiftSelect   = document.getElementById("shiftFilter");
const typeSelect    = document.getElementById("typeFilter");
const crimeTbody    = document.querySelector("#crimeTable tbody");
const personnelTbody= document.querySelector("#personnelTable tbody");
const loadingOverlay= document.getElementById("loadingOverlay");
const summaryBox    = document.getElementById("summaryBox");
const toggleLink    = document.getElementById("togglePersonnel");
const refreshBtn    = document.getElementById("refreshBtn");

/* ===========================
   MAP INITIALIZATION
   ===========================
*/
const map = L.map('map').setView(defaultCenter, defaultZoom);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution:'&copy; OpenStreetMap contributors' }).addTo(map);

/* Gold highlight style uses a circle marker */
function addHighlight(latlng){
  if(highlightLayer) try{ map.removeLayer(highlightLayer); } catch(e){ console.warn("remove highlight error", e); }
  highlightLayer = L.circleMarker(latlng, { radius:10, color: '#b8860b', weight:3, fillColor:'#ffcc00', fillOpacity:0.9, opacity:1 }).addTo(map);
}

/* ===========================
   HELPERS
   ===========================
*/
function normalize(s){ return s ? String(s).trim().toLowerCase() : ""; }
function showLoading(on){ loadingOverlay.classList.toggle("active", !!on); }

/* Robust lat/lng extraction - supports many column name variants and "combined" columns */
function parseLatLngFromRow(r){
  const tryVals = (keys) => {
    for(const k of keys){
      if(r[k] !== undefined && r[k] !== null && String(r[k]).trim()!=="") return String(r[k]).trim();
    }
    return "";
  };

  // Combined field candidates
  let combined = tryVals(["Lat_Long","LatLong","Lat / Long","Lat_Long ","lat_long","latlong","Lat/Long","Latitude_Longitude","lat,lng"]);
  // Individual fields
  let latVal = tryVals(["Lat","Latitude","lat","latitude","LAT"]);
  let lngVal = tryVals(["Lng","Long","Longitude","lng","long","longitude","LON","Lon"]);

  // If combined present and contains two numbers, split
  if(combined){
    const parts = combined.split(/[,\/\s]+/).map(p=>p.trim()).filter(Boolean);
    if(parts.length >= 2){
      const a = parseFloat(parts[0].replace(/[^\d\.\-]/g,'')), b = parseFloat(parts[1].replace(/[^\d\.\-]/g,''));
      if(!isNaN(a) && !isNaN(b)) return { lat: +a, lng: +b };
    }
  }

  // If latVal contains two numbers (e.g., "9.5, 123.6"), split
  if(latVal && latVal.indexOf(",") !== -1 && (!lngVal)){
    const ps = latVal.split(/[,\/\s]+/).map(p=>p.trim()).filter(Boolean);
    if(ps.length>=2){ const a=parseFloat(ps[0]), b=parseFloat(ps[1]); if(!isNaN(a) && !isNaN(b)) { latVal = ps[0]; lngVal = ps[1]; } }
  }

  let lat = latVal ? parseFloat(latVal.replace(/[^\d\.\-]/g,'')) : NaN;
  let lng = lngVal ? parseFloat(lngVal.replace(/[^\d\.\-]/g,'')) : NaN;

  // Heuristic: if values are swapped (common), swap
  function inPHLat(v){ return !isNaN(v) && v >= 4 && v <= 22; }
  function inPHLng(v){ return !isNaN(v) && ( (v >= 116 && v <= 127) || (v <= -116 && v >= -127) ); }

  if(!isNaN(lat) && !isNaN(lng)){
    if(!inPHLat(lat) && inPHLat(lng) && !inPHLng(lng) && inPHLng(lat)){
      const tmp = lat; lat = lng; lng = tmp;
    }
  }

  return { lat: isNaN(lat) ? null : +lat, lng: isNaN(lng) ? null : +lng };
}

/* Small helper to escape HTML for popups */
function escapeHtml(s){ return (s||"").toString().replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]); }); }

/* Helper to create a viber/tel anchor with visible number */
function makeContactLinks(mobile){
  const cleaned = (mobile||"").toString().replace(/\s+/g,'');
  if(!cleaned) return '';
  // Ensure leading + for tel/viber if local starts with 0 (common PH mobile 09.. -> +639..)
  let plusNum = cleaned;
  if(/^0\d+/.test(cleaned)) plusNum = "+63" + cleaned.slice(1);
  if(/^\d{10,}$/.test(cleaned) && !/^\+/.test(plusNum)) plusNum = "+" + plusNum;
  const vlink = `viber://chat?number=${encodeURIComponent(plusNum)}`;
  const tel = `tel:${plusNum}`;
  return `Mobile: <a class="viber-link" href="${vlink}" title="Open Viber">${escapeHtml(plusNum)}</a> <a href="${tel}" title="Call"></a>`;
}

/* ===========================
   CSV FETCH (with cache-busting)
   ===========================
*/
function fetchCsv(url){
  return fetch(url + (url.includes("?") ? "&" : "?") + "t=" + Date.now(), {cache: "no-store"})
    .then(r => {
      if(!r.ok) throw new Error("Network response not ok");
      return r.text();
    })
    .then(text => Papa.parse(text, { header: true, skipEmptyLines: true }).data);
}

/* ===========================
   LOAD & NORMALIZE DATA
   - Keep raw row for debugging
   - Parse personnel list to objects {name,mobile}
   - Parse lat/lng robustly
   ===========================
*/
async function loadData(){
  showLoading(true);
  console.log("[LOAD] Starting fetch of CSVs...");
  try{
    const [depRows, crimeRows] = await Promise.all([ fetchCsv(DEPLOYMENT_SHEET_CSV), fetchCsv(CRIME_SHEET_CSV) ]);
    console.log("[LOAD] Raw deployment rows sample:", depRows.slice(0,5));
    console.log("[LOAD] Raw crime rows sample:", crimeRows.slice(0,5));

    allData = depRows.map((r, idx) => {
      const { lat, lng } = parseLatLngFromRow(r);

      // Personnel parsing - supports a few formats:
      // - "Rank_Name (mobile)"  OR "Name (0923...)"  OR "Name - 0923..." OR "Name | 0923..."
      const personnelRaw = (r["Personnel (Rank_FName_MI_LName)"] || r.Personnel || r["Personnel"] || "").toString();
      // split by semicolon, newline, or slash/pipe or comma (common)
      const rawPersonnel = personnelRaw.split(/[\n;\/|]+|,(?=[^\)]*(?:\(|$))/).map(p=>p.trim()).filter(Boolean);

      const PersonnelList = rawPersonnel.map(p=>{
        // try to capture mobile in parentheses or trailing digits
        const inPar = p.match(/^(.*?)[\s\-]*\(?((\+?0?\d{9,13}))\)?\s*$/);
        if(inPar){ return { name: inPar[1].trim(), mobile: inPar[2].trim() }; }
        // trailing - 0923...
        const dash = p.match(/^(.*?)[\s\-]+(\+?\d{9,13})$/);
        if(dash) return { name: dash[1].trim(), mobile: dash[2].trim() };
        // if the entry itself is just a number
        if(/^\+?\d{9,13}$/.test(p)) return { name: "", mobile: p };
        // default: no mobile
        return { name: p, mobile: "" };
      });

      return {
        raw: r,
        Station: (r.Station || r["Station -"] || "").toString().trim(),
        Shift: (r.Shift || "").toString().trim(),
        "Deployment Type": (r["Deployment Type"] || r.DeploymentType || "").toString().trim(),
        Name: (r.Name || r["Name (Rank FName MI LName)"] || "").toString().trim(),
        CallSign: (r["Call Sign"] || r.CallSign || "").toString().trim(),
        Communication_Capability: (r["Communication Capability"] || r.Communication || "").toString().trim(),
        
Photo: (function() {
  // Case-insensitive lookup for photo fields
  const keys = ["Photo","photo","PHOTO","Photo URL","Image","Photo Link","Image URL"];
  let url = "";
  for (const k of keys) {
    if (r[k]) { url = r[k]; break; }
  }

  url = url.toString().trim();
  console.log("[PHOTO RAW]", url);



  // Debug log – optional, can remove later
  console.log("[PHOTO RAW]", url);

  // Case 1: Google Drive "file/d/FILEID/view" format
  if (url.includes("drive.google.com/file/d/")) {
    const match = url.match(/\/d\/([a-zA-Z0-9_-]+)/);
    if (match && match[1]) {
      url = `https://drive.google.com/uc?export=view&id=${match[1]}`;
      console.log("[PHOTO FIXED]", url);
    }
  }
  // Case 2: Google Drive "open?id=FILEID" format
  else if (url.includes("drive.google.com/open?id=")) {
    const match = url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (match && match[1]) {
      url = `https://drive.google.com/uc?export=view&id=${match[1]}`;
      console.log("[PHOTO FIXED]", url);
    }
  }

  return url;
})(),
		
        Location: (r.Location || r.PatrolArea || "").toString().trim(),
        PersonnelList,
        Lat: lat, Lng: lng,
        _rowIndex: idx
      };
    }).filter(r => r.Station); // keep rows that have Station

    crimeData = (crimeRows || []).map(r => ({
      raw: r,
      Station: (r.Station || "").toString().trim(),
      "Focus Crimes": (r["Focus Crimes"] || r["Focus Crime"] || r.Crime || "").toString().trim(),
      Barangay: (r.Barangay || r.Brgy || r["Brgy"] || "").toString().trim(),
      CaseStatus: (r["Case Status"] || r.Status || "").toString().trim()
    })).filter(r => r.Station && r["Focus Crimes"]);

    console.log("[LOAD] Parsed deployments sample:", allData.slice(0,6));
    console.log("[LOAD] Parsed crimes sample:", crimeData.slice(0,6));
    refreshUI();
  } catch(err){
    console.error("[LOAD] Error loading CSVs:", err);
  } finally {
    showLoading(false);
  }
}

/* ===========================
   REFRESH UI
   - Rebuild station dropdown
   - Clear markers and reapply filters
   ===========================
*/
function refreshUI(){
  // populate station dropdown
  const stationSet = [...new Set(allData.map(d => d.Station).filter(Boolean))].sort((a,b)=>a.localeCompare(b));
  stationSelect.innerHTML = "<option value='All'>All</option>" + stationSet.map(s=>`<option value="${escapeHtml(s)}">${escapeHtml(s)}</option>`).join("");

  // reset shift/type controls
  shiftSelect.innerHTML = "<option value='All'>All</option>"; shiftSelect.disabled = true;
  typeSelect.innerHTML  = "<option value='All'>All</option>"; typeSelect.disabled = true;

  // reset selection state
  selectedStation = "All"; selectedShift = "All"; selectedType = "All";

  // clear previous markers
  displayedMarkers.forEach(m => { try{ map.removeLayer(m.marker); } catch(e){} });
  displayedMarkers = [];

  applyFilters(); // this builds markers & tables
}

/* ===========================
   APPLY FILTERS
   - Filter allData by selected filters
   - Recreate markers and tables
   ===========================
*/
let selectedStation = "All", selectedShift = "All", selectedType = "All";
function applyFilters(){
  showLoading(true);
  console.log("[FILTER] Applying filters:", { selectedStation, selectedShift, selectedType });

  // filter data
  const filtered = allData.filter(d =>
    (selectedStation === "All" || normalize(d.Station) === normalize(selectedStation)) &&
    (selectedShift === "All"   || normalize(d.Shift) === normalize(selectedShift)) &&
    (selectedType === "All"    || normalize(d["Deployment Type"]) === normalize(selectedType))
  );

  // clear markers
  displayedMarkers.forEach(mset => {
    try{ map.removeLayer(mset.marker); } catch(e){}
  });
  displayedMarkers = [];

  // Add markers (one per deployment); attach person array to marker
  const bounds = [];
  filtered.forEach(r => {
    if(r.Lat != null && r.Lng != null){
      const photoUrl = r.Photo ? r.Photo : PLACEHOLDER_IMG;
      // build popup with per-person links and photo placeholder

	  console.log("[POPUP IMG VALUE]", r.Photo, r);

	  
      let popupHtml = `
	  
  <div class="popup-card">
    <div class="popup-header">
      <h3>${escapeHtml(r.Station || "Unknown Unit")}</h3>
      
    </div>
    ${r.Location ? `<div class="popup-row"><b>Patrol Area:</b> ${escapeHtml(r.Location)}</div>` : ""}
    ${r.Shift ? `<div class="popup-row"><b>Shift:</b> ${escapeHtml(r.Shift)}</div>` : ""}
    ${r["Deployment Type"] ? `<div class="popup-row"><b>Deployment:</b> ${escapeHtml(r["Deployment Type"])}</div>` : ""}
	${r.CallSign ? `<span class="popup-callsign"><b>Callsign</b>: ${escapeHtml(r.CallSign)}</span>` : ""}
    <hr>
    <div class="popup-people">
      ${
        Array.isArray(r.PersonnelList) && r.PersonnelList.length
          ? r.PersonnelList.map((p, idx) => {
              const name = p.name || r.Name || ("Person " + (idx+1));
              const mobile = p.mobile || "";
              const contactHtml = mobile
                ? makeContactLinks(mobile)
                : `<span style="opacity:0.6">no mobile</span>`;
              return `<div class="popup-person">
                        <span class="person-name">${escapeHtml(name)}</span><br>
                        ${contactHtml}
                      </div>`;
            }).join("")
          : `<div class="popup-person"><strong>${escapeHtml(r.Name || "Unnamed")}</strong></div>`
      }
    </div>
    <div class="popup-photo">
    <img src="${r.Photo.replace(/&amp;/g, '&') || PLACEHOLDER_IMG}" 
     alt="photo"
     onerror="this.src='${PLACEHOLDER_IMG}';">
    </div>
  </div>
`;


      // create marker and attach meta
    const marker = L.marker([r.Lat, r.Lng]).bindPopup(popupHtml, {
  maxWidth: 400,
  minWidth: 250,
  className: "custom-popup"
});
      marker.addTo(map);
      displayedMarkers.push({ marker, deployment: r });
      bounds.push([r.Lat, r.Lng]);
    } else {
      console.warn("[MARKER] Missing lat/lng for deployment:", r.Station, r.CallSign, r._rowIndex);
    }
  });

  // Fit map to markers if any
  if(bounds.length){
    try{
      const b = L.latLngBounds(bounds);
      map.fitBounds(b.pad(0.2));
    } catch(e){ console.warn("fitBounds error", e); }
  } else {
    map.setView(defaultCenter, defaultZoom);
  }

  // render personnel table - one row per person
  renderPersonnelTable(filtered);

  // render crimes (muted rows unclickable)
  renderCrimeTable();

  // update summary
  updateSummaryBox(filtered);

  showLoading(false);
}

/* ===========================
   RENDER PERSONNEL (ONE ROW PER PERSON)
   - Each row: Name | Mobile (visible & viber link) | CallSign | Station
   - Clicking a person row:
       -> Highlights table row (gold)
       -> Adds golden marker on the map (circle marker)
       -> Centers/flys to deployment marker & opens popup
   ===========================
*/
function renderPersonnelTable(filteredDeployments){
  personnelTbody.innerHTML = "";

  // Flatten deployments -> person rows
  const personRows = [];
  filteredDeployments.forEach(dep => {
    if(Array.isArray(dep.PersonnelList) && dep.PersonnelList.length){
      dep.PersonnelList.forEach(p => {
        personRows.push({
          name: p.name || dep.Name || "",
          mobile: p.mobile || "",
          CallSign: dep.CallSign || "",
          Station: dep.Station || "",
          lat: dep.Lat, lng: dep.Lng,
          deployment: dep
        });
      });
    } else {
      // fallback: one entry if no PersonnelList
      personRows.push({
        name: dep.Name || "",
        mobile: (dep.raw && (dep.raw.Mobile || dep.raw["Mobile No"] || dep.raw["Mobile#"])) || "",
        CallSign: dep.CallSign || "",
        Station: dep.Station || "",
        lat: dep.Lat, lng: dep.Lng,
        deployment: dep
      });
    }
  });

  if(personRows.length === 0){
    personnelTbody.innerHTML = "<tr><td colspan='4'>No personnel</td></tr>";
    toggleLink.style.display = "none";
    return;
  }

  const visible = personnelExpanded ? personRows : personRows.slice(0, 20); // show up to 20 rows when collapsed
  visible.forEach((p, idx) => {
    const tr = document.createElement("tr");
    tr.className = "person-row";
    const mobileCell = p.mobile ? `<td>${makeContactLinks(p.mobile)}</td>` : `<td><span style="opacity:0.6">no mobile</span></td>`;
    tr.innerHTML = `<td>${escapeHtml(p.name)}</td>${mobileCell}<td>${escapeHtml(p.CallSign)}</td><td>${escapeHtml(p.Station)}</td>`;
    tr.onclick = (ev) => {
      // unselect previous
      if(selectedRowEl) selectedRowEl.classList.remove("selected");
      tr.classList.add("selected");
      selectedRowEl = tr;

      // find the marker for this deployment
      const match = displayedMarkers.find(m => m.deployment === p.deployment);
      if(match){
        // center/fly and open popup
        try{ map.flyTo(match.marker.getLatLng(), 15, {animate:true, duration: 0.9}); } catch(e){}
        match.marker.openPopup();

        // add golden highlight circle
        addHighlight([match.deployment.Lat, match.deployment.Lng]);

        // console debug
        console.log("[PERSON CLICK] Selected person:", p.name, "deployment:", match.deployment.Station);
      } else if(p.lat && p.lng){
        // no marker but coords present — add transient highlight & fly
        try{ map.flyTo([p.lat, p.lng], 15, {animate:true, duration:0.9}); } catch(e){}
        addHighlight([p.lat, p.lng]);
        console.log("[PERSON CLICK] No marker, but lat/lng used:", p);
      } else {
        console.warn("[PERSON CLICK] No location available for person:", p);
      }
    };
    personnelTbody.appendChild(tr);
  });

  toggleLink.style.display = personRows.length > 20 ? "inline-block" : "none";
  toggleLink.textContent = personnelExpanded ? "Show less" : `Show more (${personRows.length})`;
}

/* ===========================
   RENDER CRIME TABLE
   - Rows with zero count are muted (greyed out) and unclickable
   - For "All" station: aggregate counts across available stations
   - For single station: show barangay breakdown
   ===========================
*/
function renderCrimeTable(){
  crimeTbody.innerHTML = "";
  if(!crimeData || !crimeData.length){
    crimeTbody.innerHTML = "<tr><td colspan=3>No crime data</td></tr>";
    return;
  }

  const station = stationSelect.value;
  if(station === "All"){
    // count for focus crimes across stations present in deployments
    const validStations = new Set(allData.map(d => d.Station));
    const counts = {};
    crimeData.forEach(c => {
      if(c["Focus Crimes"] && validStations.has(c.Station)){
        counts[c["Focus Crimes"]] = (counts[c["Focus Crimes"]] || 0) + 1;
      }
    });
    FOCUS_CRIMES.forEach(cr => {
      const cnt = counts[cr] || 0;
      const tr = document.createElement("tr");
      tr.className = "crime-row" + (cnt===0 ? " muted" : "");
      tr.innerHTML = `<td>${escapeHtml(cr)}</td><td>(all barangays)</td><td><center>${cnt}</center></td>`;
      crimeTbody.appendChild(tr);
    });
  } else {
    // station-specific aggregate
    const normalized = normalize(station);
    const exists = allData.some(d => normalize(d.Station) === normalized);
    if(!exists){
      crimeTbody.innerHTML = "<tr><td colspan=3>No crimes (station not in deployment)</td></tr>";
      return;
    }
    const counts = {};
    crimeData.forEach(c => {
      if(normalize(c.Station) === normalized){
        const key = (c["Focus Crimes"] || "(unknown)") + "|" + (c.Barangay || "(unknown)");
        counts[key] = (counts[key] || 0) + 1;
      }
    });
    FOCUS_CRIMES.forEach(cr=>{
      const entries = Object.entries(counts).filter(([k]) => k.startsWith(cr + "|"));
      if(entries.length){
        entries.forEach(([key,cnt])=>{
          const [, brgy] = key.split("|");
          const tr = document.createElement("tr");
          tr.className = "crime-row";
          tr.innerHTML = `<td>${escapeHtml(cr)}</td><td>${escapeHtml(brgy)}</td><td><center>${cnt}</center></td>`;
          crimeTbody.appendChild(tr);
        });
      } else {
        const tr = document.createElement("tr");
        tr.className = "crime-row muted";
        tr.innerHTML = `<td>${escapeHtml(cr)}</td><td>-</td><td><center>0</center></td>`;
        crimeTbody.appendChild(tr);
      }
    });
  }
}

/* ===========================
   SUMMARY BOX
   - If All: display total personnel
   - If station selected: show total personnel + breakdown table (Shift | Type | Count)
   ===========================
*/
function updateSummaryBox(filteredRows){
  if(!filteredRows) filteredRows = [];
  if(filteredRows.length === 0){
    summaryBox.innerHTML = "No personnel";
    return;
  }	

  if(stationSelect.value === "All"){
    const totalPersonnel = filteredRows.reduce((acc,r) => acc + (Array.isArray(r.PersonnelList) ? r.PersonnelList.length : (r.PersonnelList ? 1 : 0)), 0);
    summaryBox.innerHTML = `<b>Total Personnel:</b> ${totalPersonnel}`;
  } else {
    const totalPersonnel = filteredRows.reduce((acc,r) => acc + (Array.isArray(r.PersonnelList) ? r.PersonnelList.length : (r.PersonnelList ? 1 : 0)), 0);
    const groups = {};
    filteredRows.forEach(r=>{
      const key = (r.Shift||"") + "||" + (r["Deployment Type"]||"");
      groups[key] = groups[key] || { shift: r.Shift||"", type: r["Deployment Type"]||"", count:0 };
      groups[key].count += (Array.isArray(r.PersonnelList) ? r.PersonnelList.length : (r.PersonnelList?1:0));
    });
    let rowsHtml = Object.values(groups).map(g => `<tr><td>${escapeHtml(g.shift)}</td><td>${escapeHtml(g.type)}</td><td>${g.count}</td></tr>`).join("");
    if(!rowsHtml) rowsHtml = `<tr><td colspan=3>No deployment details</td></tr>`;
    summaryBox.innerHTML = `<b>${escapeHtml(stationSelect.value)}</b><br>Total Personnel: ${totalPersonnel}<br><table><tr><td>Shift</td><td>Type</td><td>Count</td></tr>${rowsHtml}</table>`;
  }
}

/* ===========================
   FILTER EVENT HANDLERS (Chaining)
   - Station -> populates Shift & Type
   - Shift & Type trigger filtering
   ===========================
*/
stationSelect.onchange = function(){
  selectedStation = stationSelect.value;
  selectedShift = "All";
  selectedType = "All";

  if(selectedStation === "All"){
    shiftSelect.innerHTML = "<option value='All'>All</option>"; shiftSelect.disabled = true;
    typeSelect.innerHTML = "<option value='All'>All</option>"; typeSelect.disabled = true;
  } else {
    const stationRows = allData.filter(d => normalize(d.Station) === normalize(selectedStation));
    const shifts = [...new Set(stationRows.map(r => r.Shift || "Unspecified"))];
    const types  = [...new Set(stationRows.map(r => r["Deployment Type"] || "Unspecified"))];
    shiftSelect.innerHTML = "<option value='All'>All</option>" + shifts.map(s=>`<option value="${escapeHtml(s)}">${escapeHtml(s)}</option>`).join("");
    typeSelect.innerHTML  = "<option value='All'>All</option>" + types.map(t=>`<option value="${escapeHtml(t)}">${escapeHtml(t)}</option>`).join("");
    shiftSelect.disabled = false; typeSelect.disabled = false;
  }

  applyFilters();
};

shiftSelect.onchange = function(){ selectedShift = shiftSelect.value; applyFilters(); };
typeSelect.onchange  = function(){ selectedType = typeSelect.value; applyFilters(); };

/* personnel expand toggle */
toggleLink.onclick = function(){ personnelExpanded = !personnelExpanded; applyFilters(); };

/* refresh button resets filters and reloads data */
refreshBtn.onclick = function(){
  selectedStation="All"; selectedShift="All"; selectedType="All";
  stationSelect.value="All"; shiftSelect.value="All"; typeSelect.value="All";
  map.setView(defaultCenter, defaultZoom);
  loadData();
};

/* ===========================
   AUTO-REFRESH (every 60s)
   - reloads CSVs (cache-busted)
   ===========================
*/

/* ===========================
   INITIAL LOAD
   ===========================
*/
loadData();

/* ===========================
   DEBUG: expose some helpers on window for dev console
   ===========================
*/
window.__nids_debug = {
  allData, crimeData,
  reload: loadData,
  refreshUI,
  applyFilters,
  parseLatLngFromRow,
  displayedMarkers
};
console.log("[INIT] nids debug hooks available at window.__nids_debug");
</script>
</body>
</html>
