<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OMNITRIX — Galvanic Interface</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@500;600;700&family=IBM+Plex+Mono:wght@400;600&display=swap');

  :root{
    --bg:#040705;
    --bg-card:rgba(9, 20, 14, 0.85);
    --line:#163826;
    --fg:#EAF5EE;
    --muted:#729482;
    --green:#00FF55;
    --green-glow:#39FF6A;
    --green-dim:#0E6B2E;
    --gold:#FFD700;
    --red:#FF3E3E;
    --display:'Orbitron', sans-serif;
    --sans:'Rajdhani', sans-serif;
    --mono:'IBM Plex Mono', monospace;
  }
  *{box-sizing:border-box;}
  body{
    margin:0; background:var(--bg); color:var(--fg); font-family:var(--sans); font-weight:500;
    -webkit-font-smoothing:antialiased; overflow-x:hidden;
  }

  .scanlines{
    position:fixed; inset:0; z-index:5; pointer-events:none; opacity:0.07;
    background:repeating-linear-gradient(0deg, #fff 0px, transparent 1px, transparent 3px);
  }
  .vignette{
    position:fixed; inset:0; z-index:4; pointer-events:none;
    background:radial-gradient(ellipse 80% 70% at 50% 35%, transparent 35%, rgba(0,0,0,0.85) 100%);
  }
  #flashCanvas{position:fixed; inset:0; z-index:50; pointer-events:none;}

  nav{
    position:relative; z-index:2; display:flex; justify-content:space-between; align-items:center;
    max-width:1200px; margin:0 auto; padding:24px 28px 0; font-family:var(--mono); font-size:12px; color:var(--muted); letter-spacing:.08em;
  }
  nav .brand{color:var(--green); display:flex; align-items:center; gap:10px; font-weight:600;}
  nav .brand::before{
    content:''; width:8px; height:8px; border-radius:50%; background:var(--green); 
    box-shadow:0 0 10px var(--green); animation:pulse 1.4s ease-in-out infinite;
  }
  nav .owner-badge{
    color:var(--gold); border:1px solid rgba(255, 215, 0, 0.35); padding:4px 10px; border-radius:4px;
    background:rgba(255, 215, 0, 0.08); text-shadow:0 0 8px rgba(255, 215, 0, 0.5);
  }
  @keyframes pulse{0%,100%{opacity:1;}50%{opacity:.2;}}

  .wrap{position:relative; z-index:1; max-width:1200px; margin:0 auto; padding:0 28px;}

  /* ---------- Hero & Dial Section ---------- */
  .hero{min-height:94vh; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:40px 0;}
  .eyebrow{font-family:var(--mono); font-size:11.5px; color:var(--green); letter-spacing:.25em; text-transform:uppercase; margin-bottom:10px;}
  .user-lock{
    font-family:var(--mono); font-size:13px; color:var(--gold); letter-spacing:.15em; text-transform:uppercase;
    margin-bottom:14px; text-shadow:0 0 10px rgba(255,215,0,0.6);
  }
  .title{
    font-family:var(--display); font-weight:900; font-size:clamp(42px, 8vw, 78px); letter-spacing:.06em; margin:0 0 6px;
    background:linear-gradient(180deg, #ffffff 20%, var(--green-glow) 100%);
    -webkit-background-clip:text; background-clip:text; color:transparent;
    filter:drop-shadow(0 0 35px rgba(57,255,106,0.3));
  }
  .subtitle{color:var(--muted); font-size:15px; letter-spacing:.04em; margin-bottom:36px; max-width:65ch;}

  /* Dynamic Interactive Dial */
  .dial-stage{position:relative; width:300px; height:300px; margin-bottom:34px; user-select:none;}
  
  .dial-outer{
    position:absolute; inset:0; border-radius:50%;
    background:radial-gradient(circle at 35% 30%, #202b25, #080a09 80%);
    border:3px solid #234734;
    box-shadow:0 0 50px rgba(57,255,106,0.15), inset 0 0 35px rgba(0,0,0,0.8);
    transition:transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
  
  .dial-ring{
    position:absolute; inset:12px; border-radius:50%; border:2px dashed #1B452B;
    animation:spin-slow 35s linear infinite;
  }
  @keyframes spin-slow{to{transform:rotate(360deg);}}
  
  .tick{position:absolute; width:2px; height:12px; background:var(--green-dim); left:50%; top:6px; transform-origin:50% 138px;}

  .dial-core{
    position:absolute; inset:48px; border-radius:50%;
    background-color:#020503;
    border:3px solid var(--green);
    cursor:pointer;
    box-shadow:0 0 35px rgba(0, 255, 85, 0.4), inset 0 0 20px rgba(0, 0, 0, 0.9);
    transition:transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.3s ease;
    display:flex; align-items:center; justify-content:center; overflow:hidden;
  }
  .dial-core:hover{
    box-shadow:0 0 55px rgba(0, 255, 85, 0.7), inset 0 0 10px rgba(0,0,0,0.6);
  }
  .dial-core:active{transform:scale(0.92);}

  .core-display-img{
    width:78%; height:78%; object-fit:contain;
    filter:drop-shadow(0 0 10px rgba(0, 255, 85, 0.8));
    transition:opacity 0.25s ease, transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
  }

  .dial-core.active{animation:core-pop 0.6s cubic-bezier(0.16, 1, 0.3, 1);}
  @keyframes core-pop{
    0%{box-shadow:0 0 20px var(--green);}
    50%{box-shadow:0 0 100px var(--green); transform:scale(1.06);}
    100%{box-shadow:0 0 35px var(--green); transform:scale(1);}
  }

  .readout{
    font-family:var(--mono); font-size:14px; font-weight:600; color:var(--green); letter-spacing:.12em; min-height:24px;
    text-shadow:0 0 10px rgba(57,255,106,0.7); margin-bottom:4px;
  }
  .readout .prompt{color:var(--muted); margin-right:6px;}

  .dial-controls{display:flex; gap:12px; margin-top:16px;}

  .ctrl-btn{
    padding:10px 22px; border-radius:6px; border:1px solid var(--line);
    background:rgba(22, 56, 38, 0.35); color:var(--fg); font-family:var(--mono); font-size:12px;
    letter-spacing:.12em; cursor:pointer; transition:all .2s ease;
  }
  .ctrl-btn:hover{background:rgba(57,255,106,0.12); border-color:var(--green); color:var(--green);}

  .action-controls{display:flex; gap:14px; margin-top:14px; flex-wrap:wrap; justify-content:center;}

  .activate-btn{
    padding:14px 34px; border-radius:6px; border:1px solid var(--green);
    background:rgba(14, 107, 46, 0.2); color:var(--green); font-family:var(--mono); font-size:12.5px; font-weight:600;
    letter-spacing:.18em; cursor:pointer; transition:all .25s ease;
    box-shadow:0 0 20px rgba(57,255,106,0.15);
  }
  .activate-btn:hover{background:rgba(57,255,106,0.15); box-shadow:0 0 30px rgba(57,255,106,0.4); transform:translateY(-2px);}
  .activate-btn:active{transform:scale(0.96) translateY(0);}

  .deactivate-btn{
    padding:14px 28px; border-radius:6px; border:1px solid rgba(255, 62, 62, 0.6);
    background:rgba(255, 62, 62, 0.1); color:var(--red); font-family:var(--mono); font-size:12.5px; font-weight:600;
    letter-spacing:.18em; cursor:pointer; transition:all .25s ease;
    box-shadow:0 0 16px rgba(255, 62, 62, 0.15);
  }
  .deactivate-btn:hover{background:rgba(255, 62, 62, 0.2); border-color:var(--red); box-shadow:0 0 26px rgba(255, 62, 62, 0.4); transform:translateY(-2px);}
  .deactivate-btn:active{transform:scale(0.96) translateY(0);}

  /* ---------- Roster Section ---------- */
  section{padding:90px 0; border-top:1px solid var(--line);}
  .reveal{opacity:0; transform:translateY(24px); transition:opacity .8s cubic-bezier(.16,1,.3,1), transform .8s cubic-bezier(.16,1,.3,1);}
  .reveal.in{opacity:1; transform:translateY(0);}
  .reveal.d1{transition-delay:.1s;} .reveal.d2{transition-delay:.2s;}

  .kicker{font-family:var(--mono); font-size:12px; color:var(--green); letter-spacing:.16em; text-transform:uppercase; margin-bottom:12px;}
  h2{font-family:var(--display); font-weight:700; font-size:clamp(26px,3.5vw,36px); letter-spacing:.02em; margin:0 0 14px; color:var(--fg);}
  .lede{color:var(--muted); font-size:15.5px; line-height:1.6; max-width:58ch; margin:0 0 44px;}

  .agrid{
    display:grid; grid-template-columns:repeat(3, 1fr); gap:20px;
  }
  @media (max-width:960px){.agrid{grid-template-columns:repeat(2, 1fr);}}
  @media (max-width:640px){.agrid{grid-template-columns:1fr; max-width:440px; margin:0 auto;}}

  .acard{
    background:var(--bg-card); border:1px solid var(--line); border-radius:14px;
    padding:24px 20px; display:flex; flex-direction:column; align-items:center; gap:14px;
    cursor:pointer; transition:all .3s ease; text-align:center; position:relative; overflow:hidden;
    backdrop-filter:blur(6px);
  }
  .acard::before{
    content:''; position:absolute; inset:0; opacity:0;
    background:radial-gradient(circle at 50% 30%, var(--card-accent, #39FF6A), transparent 70%);
    mix-blend-mode:screen; transition:opacity .3s ease; pointer-events:none;
  }
  .acard:hover{transform:translateY(-6px); border-color:var(--card-accent, #39FF6A); box-shadow:0 10px 30px rgba(0,0,0,0.5);}
  .acard:hover::before{opacity:0.18;}
  
  .acard.selected{
    border-color:var(--card-accent, #39FF6A);
    background:linear-gradient(180deg, rgba(20,38,28,0.9), rgba(10,20,15,0.95));
    box-shadow:0 0 25px rgba(57,255,106,0.25), inset 0 0 0 1px var(--card-accent, #39FF6A);
  }
  .acard.selected::before{opacity:0.25;}

  .aicon{
    width:150px; height:160px; display:flex; align-items:center; justify-content:center;
    position:relative; z-index:1;
  }
  .aicon img{
    max-width:100%; max-height:100%; object-fit:contain;
    filter:drop-shadow(0 6px 12px rgba(0,0,0,0.8));
    transition:transform .3s ease, filter .3s ease;
  }
  .acard:hover .aicon img{transform:scale(1.08); filter:drop-shadow(0 0 14px var(--card-accent, #39FF6A));}

  .aname{font-family:var(--display); font-size:15px; font-weight:700; letter-spacing:.08em; color:var(--fg); z-index:1;}
  .aspecies{font-family:var(--mono); font-size:11px; color:var(--muted); letter-spacing:.06em; text-transform:uppercase; z-index:1;}
  .adesc{font-size:13px; color:#A7BDB2; line-height:1.45; margin:0; z-index:1;}

  /* ---------- Database Log ---------- */
  .lore-panel{
    border:1px solid var(--line); border-radius:14px; background:var(--bg-card); 
    padding:36px 32px; position:relative; overflow:hidden;
  }
  .lore-panel::before{
    content:''; position:absolute; left:0; top:0; bottom:0; width:4px;
    background:var(--green); box-shadow:0 0 14px var(--green);
  }
  .lore-panel p{color:#C5D8CD; font-size:15px; line-height:1.75; margin:0 0 14px;}
  .lore-panel p:last-child{margin-bottom:0;}
  .lore-panel .tag{font-family:var(--mono); font-size:11.5px; color:var(--green); letter-spacing:.12em; margin-bottom:14px; display:block;}
  .highlight-owner{color:var(--gold); font-weight:600; text-shadow:0 0 8px rgba(255,215,0,0.4);}

  footer{padding:60px 0 80px; text-align:center;}
  footer p{color:var(--muted); font-size:12px; font-family:var(--mono); letter-spacing:.05em;}
</style>
</head>
<body>

<div class="scanlines"></div>
<div class="vignette"></div>
<canvas id="flashCanvas"></canvas>

<nav>
  <div class="brand">OMNITRIX INTERFACE</div>
  <div class="owner-badge">WIELDER: HARSHIT</div>
</nav>

<div class="wrap">
  <section class="hero" style="border:none;">
    <div class="eyebrow">Level 20 Chronosapien Tech</div>
    <div class="user-lock">⚔ COSMIC WEAPON OWNER: HARSHIT ⚔</div>
    <h1 class="title">OMNITRIX</h1>
    <p class="subtitle">The universe's most powerful weapon, calibrated and biometrically bound to <strong>Harshit</strong>. Rotate dial, select codon sample, and slam down to transform.</p>

    <div class="dial-stage" id="dialStage">
      <div class="dial-outer" id="dialOuter">
        <div class="dial-ring" id="tickRing"></div>
      </div>
      <div class="dial-core" id="dialCore" title="Click to rotate dial">
        <img class="core-display-img" id="coreImg" src="assets/images/logo.png" alt="Omnitrix Core">
      </div>
    </div>

    <div class="readout"><span class="prompt">DNA PROFILE:</span><span id="readoutText">— STANDBY [HARSHIT AUTHENTICATED] —</span></div>
    
    <div class="dial-controls">
      <button class="ctrl-btn" id="prevBtn">◀ PREV</button>
      <button class="ctrl-btn" id="nextBtn">NEXT ▶</button>
    </div>

    <div class="action-controls">
      <button class="activate-btn" id="activateBtn">⚡ SLAM TO TRANSFORM</button>
      <button class="deactivate-btn" id="deactivateBtn">⏻ DEACTIVATE</button>
    </div>
  </section>

  <section>
    <div class="kicker reveal">active codon stream</div>
    <h2 class="reveal d1">Active Alien Playlist</h2>
    <p class="lede reveal d2">Master DNA registry locked to Harshit's neural signature. Ordered from frontline assault forms to ultimate cosmic entities.</p>

    <div class="agrid reveal" id="alienGrid"></div>
  </section>

  <section>
    <div class="kicker reveal">classified galactic dossier</div>
    <h2 class="reveal d1">Omnitrix Field Analysis & Wielder Profile</h2>
    <div class="lore-panel reveal d2">
      <span class="tag">SYSTEM_LOG // WIELDER DESIGNATION: HARSHIT</span>
      <p>The Omnitrix stands as the most formidable and sophisticated weapon throughout the known universe. Created with Level 20 Galvanic technology, it houses over one million sapient genetic matrices within its active codon stream.</p>
      <p>The device is permanently biometrically locked to <span class="highlight-owner">Harshit</span>. In Harshit's command, the watch adapts to real-time battlefield conditions, granting instant tactical cellular evolution ranging from plasma fire manipulation to ultimate reality warping.</p>
      <p>Emergency protocols and ultimate powers remain under Harshit's exclusive authority, making him the universe's ultimate defense against cataclysmic threats.</p>
    </div>
  </section>

  <footer>
    <p>UNIVERSAL DEFENSE MATRIX · EXCLUSIVELY WIELDED BY HARSHIT</p>
  </footer>
</div>

<script>
  /* Tick marks around dial */
  const tickRing = document.getElementById('tickRing');
  for(let i=0; i<24; i++){
    const t = document.createElement('div');
    t.className = 'tick';
    t.style.transform = `rotate(${i*15}deg)`;
    tickRing.appendChild(t);
  }

  /* Full Alien Roster: Heatblast first, Alien X last */
  const aliens = [
    {
      name: 'HEATBLAST',
      species: 'Pyronite · Pyros',
      color: '#FF6B00',
      image: 'assets/images/Heatblast.png',
      desc: 'Plasma-based entity capable of generating extreme heat, fire blasts, and propelled magma slabs.'
    },
    {
      name: 'DIAMONDHEAD',
      species: 'Petrosapien · Petropia',
      color: '#4EF5C3',
      image: 'assets/images/diamondhead.png',
      desc: 'Silicon crystal organism impervious to physical attacks, capable of crystal construct manipulation.'
    },
    {
      name: 'FOUR ARMS',
      species: 'Tetramand · Khoros',
      color: '#FF3838',
      image: 'assets/images/forearms-Photoroom.png',
      desc: 'Colossal four-armed juggernaut with superhuman density, immense brute strength, and sonic shockwaves.'
    },
    {
      name: 'GREY MATTER',
      species: 'Galvan · Galvan Prime',
      color: '#89E28B',
      image: 'assets/images/greymatter-Photoroom.png',
      desc: 'Micro-sized super-genius capable of calculating technical schematics and fixing any device instantly.'
    },
    {
      name: 'SHOCKSQUATCH',
      species: 'Gimlinopithecus · Pattersonea',
      color: '#FFE24A',
      image: 'assets/images/shocksquach.png',
      desc: 'Massive durable powerhouse delivering devastating high-voltage electro-kinesis and seismic force.'
    },
    {
      name: 'HUMUNGOSAUR',
      species: 'Vaxasaurian · Terradino',
      color: '#E09C47',
      image: 'assets/images/humangasaur.png',
      desc: 'Massive dinosaurian powerhouse with armored hide and dynamic bio-mass size augmentation.'
    },
    {
      name: 'JETRAY',
      species: 'Aerophibian · Aeropela',
      color: '#FF2E55',
      image: 'assets/images/jetray.png',
      desc: 'High-velocity flyer traveling faster than sound and through hyperspace while firing neuroshock beams.'
    },
    {
      name: 'SWAMPFIRE',
      species: 'Methanosian · Methanos',
      color: '#7FFF00',
      image: 'assets/images/swarmfire-Photoroom.png',
      desc: 'Plant-methane hybrid capable of rapid chlorokinetic cellular regeneration and continuous pyrokinesis.'
    },
    {
      name: 'ECHO ECHO',
      species: 'Sonorosian · Sonorosia',
      color: '#E8F5EE',
      image: 'assets/images/echo_echo.png',
      desc: 'Living sound resonance contained in a suit; creates sonic vibrations and infinite duplicates.'
    },
    {
      name: 'ALIEN X',
      species: 'Celestialsapien · Forge of Creation',
      color: '#A080FF',
      image: 'assets/images/alien-x.png',
      desc: 'Omnipotent reality-warping entity with complete control over space, time, and cosmic matter.'
    }
  ];

  const grid = document.getElementById('alienGrid');
  const readout = document.getElementById('readoutText');
  const dialCore = document.getElementById('dialCore');
  const coreImg = document.getElementById('coreImg');
  const dialOuter = document.getElementById('dialOuter');
  
  let currentIndex = -1;
  let rotationDeg = 0;

  aliens.forEach((alien, i) => {
    const card = document.createElement('div');
    card.className = 'acard';
    card.style.setProperty('--card-accent', alien.color);
    card.innerHTML = `
      <div class="aicon"><img src="${alien.image}" alt="${alien.name}"></div>
      <div class="aname">${alien.name}</div>
      <div class="aspecies">${alien.species}</div>
      <p class="adesc">${alien.desc}</p>
    `;
    card.addEventListener('click', () => selectAlien(i, 1));
    grid.appendChild(card);
  });

  function selectAlien(index, direction = 1) {
    currentIndex = (index + aliens.length) % aliens.length;
    const selected = aliens[currentIndex];

    /* Update Card Highlights */
    const cards = document.querySelectorAll('.acard');
    cards.forEach((c, idx) => c.classList.toggle('selected', idx === currentIndex));

    /* Mechanical Core Rotation */
    rotationDeg += direction * 36;
    dialOuter.style.transform = `rotate(${rotationDeg}deg)`;

    /* Holographic Projection in the Core */
    coreImg.style.opacity = '0';
    coreImg.style.transform = 'scale(0.6) rotate(-20deg)';

    setTimeout(() => {
      coreImg.src = selected.image;
      coreImg.style.filter = `drop-shadow(0 0 12px ${selected.color})`;
      coreImg.style.opacity = '1';
      coreImg.style.transform = 'scale(1) rotate(0deg)';
    }, 150);

    /* Update Core Ring & Readout Glow */
    dialCore.style.borderColor = selected.color;
    dialCore.style.boxShadow = `0 0 45px ${selected.color}`;
    readout.textContent = `${selected.name} [HARSHIT AUTHORIZED]`;
    readout.style.color = selected.color;
    readout.style.textShadow = `0 0 14px ${selected.color}`;
  }

  function deactivateOmnitrix() {
    currentIndex = -1;
    document.querySelectorAll('.acard').forEach(c => c.classList.remove('selected'));

    /* Reset dial rotation */
    rotationDeg = 0;
    dialOuter.style.transform = 'rotate(0deg)';

    /* Reset Core image back to primary logo */
    coreImg.style.opacity = '0';
    coreImg.style.transform = 'scale(0.7)';

    setTimeout(() => {
      coreImg.src = 'assets/images/logo.png';
      coreImg.style.filter = 'drop-shadow(0 0 10px rgba(0, 255, 85, 0.8))';
      coreImg.style.opacity = '1';
      coreImg.style.transform = 'scale(1)';
    }, 150);

    /* Reset Core styling */
    dialCore.classList.remove('active');
    void dialCore.offsetWidth;
    dialCore.classList.add('active');
    dialCore.style.borderColor = 'var(--green)';
    dialCore.style.boxShadow = '0 0 35px rgba(0, 255, 85, 0.4)';

    /* Reset Readout */
    readout.textContent = '— STANDBY [HARSHIT AUTHENTICATED] —';
    readout.style.color = 'var(--green)';
    readout.style.textShadow = '0 0 10px rgba(57,255,106,0.7)';

    triggerBurst('#FF3E3E');
  }

  /* Dial Click and Button Navigation */
  dialCore.addEventListener('click', () => selectAlien(currentIndex + 1, 1));
  document.getElementById('nextBtn').addEventListener('click', () => selectAlien(currentIndex + 1, 1));
  document.getElementById('prevBtn').addEventListener('click', () => selectAlien(currentIndex - 1, -1));
  document.getElementById('deactivateBtn').addEventListener('click', deactivateOmnitrix);

  /* Reveal animations */
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('in'); });
  }, { threshold: 0.15 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  /* Canvas Transformation Flash & Particle FX */
  const canvas = document.getElementById('flashCanvas');
  const ctx = canvas.getContext('2d');
  function resize(){ canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
  resize(); 
  window.addEventListener('resize', resize);

  let particles = [];
  let flashAlpha = 0;
  let flashColor = '#00FF55';

  function triggerBurst(color) {
    flashColor = color;
    const dialRect = document.getElementById('dialStage').getBoundingClientRect();
    const cx = dialRect.left + dialRect.width / 2;
    const cy = dialRect.top + dialRect.height / 2;

    for(let i=0; i<120; i++){
      const angle = Math.random() * Math.PI * 2;
      const speed = 4 + Math.random() * 12;
      particles.push({
        x: cx, y: cy,
        vx: Math.cos(angle) * speed,
        vy: Math.sin(angle) * speed,
        life: 1,
        color: Math.random() > 0.3 ? color : '#FFFFFF'
      });
    }
    flashAlpha = 0.85;
  }

  function fxLoop() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    if(flashAlpha > 0.001){
      ctx.fillStyle = flashColor;
      ctx.globalAlpha = flashAlpha * 0.55;
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      flashAlpha *= 0.84;
    }
    particles.forEach(p => {
      p.x += p.vx;
      p.y += p.vy;
      p.vy += 0.05;
      p.life -= 0.015;
      ctx.globalAlpha = Math.max(p.life, 0);
      ctx.fillStyle = p.color;
      ctx.shadowBlur = 10;
      ctx.shadowColor = p.color;
      ctx.beginPath();
      ctx.arc(p.x, p.y, 3.5, 0, Math.PI * 2);
      ctx.fill();
    });
    ctx.shadowBlur = 0;
    ctx.globalAlpha = 1;
    particles = particles.filter(p => p.life > 0);
    requestAnimationFrame(fxLoop);
  }
  fxLoop();

  document.getElementById('activateBtn').addEventListener('click', () => {
    if (currentIndex < 0) selectAlien(0, 1);
    const selected = aliens[currentIndex];
    
    triggerBurst(selected.color);
    dialCore.classList.remove('active');
    void dialCore.offsetWidth;
    dialCore.classList.add('active');

    readout.textContent = `${selected.name} — HARSHIT TRANSFORMED`;
  });
</script>
</body>
</html>