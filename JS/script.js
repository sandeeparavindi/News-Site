
// Helpers
const $ = (s, el=document) => el.querySelector(s);
const $$ = (s, el=document) => [...el.querySelectorAll(s)];

// Carousel
(function(){
  const track = document.querySelector('.carousel__track');
  const slides = $$('.slide', track);
  const dots = $('#heroDots');
  let i = 0;
  slides.forEach((_, idx)=>{
    const b = document.createElement('button');
    b.setAttribute('aria-label', 'Go to slide ' + (idx+1));
    dots.appendChild(b);
  });
  const dotBtns = $$('#heroDots button');
  const setActive = (n)=>{
    i = (n + slides.length) % slides.length;
    track.style.transform = `translateX(-${i*100}%)`;
    dotBtns.forEach((d,idx)=>d.classList.toggle('is-active', idx===i));
  }
  setInterval(()=> setActive(i+1), 5000);
  $('[data-carousel="prev"]').addEventListener('click', ()=> setActive(i-1));
  $('[data-carousel="next"]').addEventListener('click', ()=> setActive(i+1));
  dotBtns.forEach((d,idx)=> d.addEventListener('click', ()=> setActive(idx)));
  setActive(0);
})();

// Tabs + Popular content
const popular = {
  today: [
    { img:'assets/images/world.jpeg', tag:'World', title:'Summit advances climate finance agenda' },
    { img:'assets/images/tech.jpeg', tag:'Tech', title:'Foldable phones hit mainstream pricing' },
    { img:'assets/images/sports.jpeg', tag:'Sports', title:'Mid-season trades shake the table' },
    { img:'assets/images/culture.jpg', tag:'Culture', title:'Festival of lights returns downtown' },
  ],
  week: [
    { img:'assets/images/gadgets.jpeg', tag:'Gadgets', title:'Tiny PCs pack desktop power' },
    { img:'assets/images/opinion.jpeg', tag:'Opinion', title:'Cities are for walking, not parking' },
    { img:'assets/images/esports.jpeg', tag:'Esports', title:'International finals set for July' },
    { img:'assets/images/travel.jpeg', tag:'Travel', title:'Island escapes on a budget' },
  ],
  month: [
    { img:'assets/images/bussines.jpeg', tag:'Business', title:'Quarterly earnings beat forecasts' },
    { img:'assets/images/science.jpg', tag:'Science', title:'Battery breakthrough lasts 2,000 cycles' },
    { img:'assets/images/health.jpeg', tag:'Health', title:'New guidance for screen time' },
    { img:'assets/images/football.jpeg', tag:'Football', title:'Summer friendlies announced' },
  ]
};

function renderPopular(key='today'){
  const grid = $('#popularGrid');
  grid.innerHTML = '';
  popular[key].forEach(card => {
    const el = document.createElement('article');
    el.className = 'card';
    el.innerHTML = `
      <img src="${card.img}" alt="${card.tag}">
      <div class="card__body">
        <span class="badge">${card.tag}</span>
        <h4>${card.title}</h4>
        <p class="card__meta">2h • 4 min read</p>
      </div>`;
    grid.appendChild(el);
  });
}
renderPopular();
$$('.tab').forEach(t=> t.addEventListener('click', (e)=>{
  $$('.tab').forEach(s=>s.classList.remove('is-active'));
  t.classList.add('is-active');
  renderPopular(t.dataset.tab);
}));

// Fetch latest + trending from PHP
async function fetchJSON(url){
  try{
    const res = await fetch(url);
    return await res.json();
  }catch(e){
    console.warn('Falling back to local seed', e);
    return null;
  }
}
function seedItems(){
  return {
    latest: [
      { 
        title: `Hotel manager beheaded by worker`, 
        excerpt: 'A hotel worker in the US allegedly beheaded his manager and threw the head in a rubbish bin because he wasn’t spoken to directly.',
        time: '3h', img: 'assets/images/hero-3.svg'
      },
      { 
        title: `Britain’s Prince Harry has arrived in Ukraine`, 
        excerpt: 'This is the first time a member of the British royal family has visited the country since Russia invaded in February.',
        time: '3h', img: 'assets/images/hero-1.svg'
      },
      { 
        title: `The art of communicating across borders`, 
        excerpt: 'Bring people who speak different languages and come from different cultures',
        time: '4h', img: 'assets/images/hero-2.svg'
      },
      { 
        title: `Do you live in healthy media eco-system?`, 
        excerpt: 'Media is food for our brain. The media you consume might be nutritious.',
        time: '5h', img: 'assets/images/hero-5.svg'
      },
      { 
        title: `Found some guns and drugs, those are arrested by police`, 
        excerpt: 'Countries are increasingly using Guns, including some drugs inside it, to bring rogue players into line.',
        time: '6h', img: 'assets/images/hero-4.svg'
      },
      { 
        title: `What is QAnon?`, 
        excerpt: 'The fringe conspiracy theory is facing a crackdown in the US and UK.',
        time: '7h', img: 'assets/images/hero-6.svg'
      } 
    ],
    trending: [
      { title:'Analysis: Markets rally into close', by:'Echo Desk', img:'assets/images/card-world.svg' },
      { title:'Interview: CEO on growth plans', by:'Biz Reporter', img:'assets/images/card-biz.svg' },
      { title:'How to spot misleading charts', by:'Data Team', img:'assets/images/card-tech.svg' },
      { title:'Derby preview: form & injuries', by:'Sports Desk', img:'assets/images/card-sports.svg' },
      { title:'Editors pick: long reads', by:'Features', img:'assets/images/card-world1.svg' },
      { title:'Startup raises record seed', by:'Biz Team', img:'assets/images/card-tech1.svg' },
      { title:'City expands bike lanes', by:'Local Desk', img:'assets/images/card-world2.svg' }
    ],
    calendar: ['Mon – 2 fixtures','Tue – 5 fixtures','Wed – 1 fixture','Thu – 3 fixtures','Fri – 4 fixtures']
  }
}

(async function initData(){
  const data = await fetchJSON('php/api.php') || seedItems();

  // calendar
  
  const cal = $('#calendarList');
  data.calendar.forEach(item=>{
    const li = document.createElement('li'); li.textContent = item; cal.appendChild(li);
  });
  

  // latest
  const ll = $('#latestList');
  data.latest.forEach(it=>{
    const el = document.createElement('article');
    el.className = 'latest-item';
    el.innerHTML = `
      <img src="${it.img}" alt=""/>
      
      <div>
        <span class="badge">News</span>
        <h4>${it.title}</h4>
        <p class="muted">${it.excerpt}</p>
        <small class="muted">${it.time} ago</small>
      </div>`;
    ll.appendChild(el);
  });

  // trending
  const tl = $('#trendingList');
  data.trending.forEach(it=>{
    const el = document.createElement('div');
    el.className = 'trending-item';
    el.innerHTML = `
      <img src="${it.img}" alt="">
      <div>
        <h4>${it.title}</h4>
        <small class="muted">by ${it.by}</small>
      </div>`;
    tl.appendChild(el);
  });
})();



// Search demo
$('#searchForm')?.addEventListener('submit', (e)=>{
  e.preventDefault();
  const q = $('#search-box').value.trim();
  if(!q) return;
  alert('Search for: ' + q);
});

// Hide / Show header on scroll
let lastScrollY = window.scrollY;
const header = document.querySelector(".header");

window.addEventListener("scroll", () => {
  if (window.scrollY > lastScrollY) {
    // scrolling down → hide
    header.style.top = "-80px"; // adjust to your header height
  } else {
    // scrolling up → show
    header.style.top = "10px"; // adjust to your header height
  }
  lastScrollY = window.scrollY;
});

