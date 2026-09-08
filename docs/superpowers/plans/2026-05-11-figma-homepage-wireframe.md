# Figma Homepage Wireframe — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Crear el wireframe en escala de grises de la homepage de Hidral en Figma, con copy real y módulos reutilizables, en el archivo Test-AI de la cuenta Pro.

**Architecture:** Un frame `/ — Homepage` de 1440px de ancho en una página "UX", construido como un auto-layout vertical con 8 secciones independientes. Cada sección es un auto-layout con padding y estructura propios. Sin colores de marca — solo escala de grises.

**Tech Stack:** Figma Plugin API (use_figma), DM Sans font, fileKey: `RbFIEoZG6eLxYsE1ufFP8Z`

---

## Contexto crítico (NO re-investigar)

### Archivo Figma
- **File:** Test-AI — `RbFIEoZG6eLxYsE1ufFP8Z`
- **Cuenta:** Pro (cuenta secundaria del usuario)
- **Autenticación:** Re-autenticar Figma MCP con la cuenta Pro al inicio del chat

### Referencia visual
El wireframe HTML completo ya existe en:
`/Users/andres/Documents/Personal/dev/personal/hidral/wp-content/themes/hidral-wp-2026/.superpowers/brainstorm/66662-1778532968/content/wireframe-homepage.html`

Abrirlo en el navegador como referencia visual durante la implementación.

### Paleta de grises (valores 0-1)
```
bg-white:    {r:1,    g:1,    b:1   }
bg-light:    {r:0.96, g:0.96, b:0.96}
bg-dark:     {r:0.07, g:0.08, b:0.16}   ← hero y contacto
bg-mid-dark: {r:0.12, g:0.13, b:0.20}   ← abono
text-dark:   {r:0.12, g:0.12, b:0.12}
text-mid:    {r:0.45, g:0.45, b:0.45}
text-light:  {r:0.72, g:0.72, b:0.78}   ← texto sobre dark bg
border:      {r:0.90, g:0.90, b:0.90}
placeholder: {r:0.86, g:0.86, b:0.86}
```

### Copy aprobado — Hero
- **H1:** `Tu edificio en manos que lo conocen.`
- **Bajada:** `Visitas mensuales, equipo propio y respuesta rápida para bombas, sanitaria, tanques y contraincendio. Más de dos décadas manteniendo edificios residenciales y de oficinas.`
- **CTA 1:** `Ver servicios`
- **CTA 2:** `Trabajos puntuales`

### Estructura de 8 secciones (aprobada por el usuario)
1. Navbar
2. Hero (dark bg)
3. Servicios — 4 cards
4. Trabajos puntuales — 3 items
5. Confianza — logos placeholder
6. Abono — módulo activable (dark mid bg)
7. Por qué Hidral — 3 columnas
8. Blog — 3 cards
9. Contacto (dark bg)
10. Footer

---

## Helper functions reutilizables

Incluir en **cada script** de use_figma:

```js
// SIEMPRE cargar estos fonts primero
await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];

const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const makeBtn = (label, bgR, bgG, bgB, textR = 1, textG = 1, textB = 1, outlined = false) => {
  const btn = figma.createAutoLayout('HORIZONTAL');
  btn.paddingLeft = 28; btn.paddingRight = 28;
  btn.paddingTop = 12; btn.paddingBottom = 12;
  btn.cornerRadius = 4;
  if (outlined) {
    btn.fills = fill(bgR, bgG, bgB, 0);
    btn.strokes = [{ type: 'SOLID', color: { r: 0.55, g: 0.57, b: 0.7 } }];
    btn.strokeWeight = 1.5;
  } else {
    btn.fills = fill(bgR, bgG, bgB);
  }
  btn.appendChild(makeText(label, 14, 'Medium', textR, textG, textB));
  return btn;
};
```

---

## Task 1: Setup — Autenticación y página UX

**Pre-condición:** Reiniciar Claude Code, autenticar Figma MCP con la **cuenta Pro** cuando aparezca el prompt de auth.

- [ ] **1.1 Verificar acceso al archivo**

```js
// fileKey: RbFIEoZG6eLxYsE1ufFP8Z
const pages = figma.root.children.map(p => ({ name: p.name, id: p.id }));
return { fileName: figma.root.name, pages };
```
Esperado: `{ fileName: "Test-AI", pages: [...] }`

- [ ] **1.2 Crear página "UX" si no existe**

```js
let uxPage = figma.root.children.find(p => p.name === 'UX');
if (!uxPage) {
  uxPage = figma.createPage();
  uxPage.name = 'UX';
}
await figma.setCurrentPageAsync(uxPage);

// Limpiar si tiene contenido previo
const existing = [...uxPage.children];
for (const n of existing) n.remove();

return { pageId: uxPage.id, msg: 'UX page ready' };
```

- [ ] **1.3 Crear el wrapper principal**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });

const wrapper = figma.createAutoLayout('VERTICAL');
wrapper.name = '/ — Homepage';
wrapper.resize(1440, 100);
wrapper.primaryAxisSizingMode = 'AUTO';
wrapper.counterAxisSizingMode = 'FIXED';
wrapper.itemSpacing = 0;
wrapper.fills = [{ type: 'SOLID', color: { r: 1, g: 1, b: 1 } }];
wrapper.x = 100;
wrapper.y = 100;

return { wrapperId: wrapper.id, msg: 'Wrapper created' };
```

Guardar `wrapperId` — se usa en todos los tasks siguientes.

---

## Task 2: Navbar

- [ ] **2.1 Crear navbar** (reemplazar `WRAPPER_ID` con el ID del task anterior)

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

const nav = figma.createAutoLayout('HORIZONTAL');
nav.name = 'Navbar';
nav.paddingLeft = 80; nav.paddingRight = 80;
nav.paddingTop = 16; nav.paddingBottom = 16;
nav.primaryAxisAlignItems = 'SPACE_BETWEEN';
nav.counterAxisAlignItems = 'CENTER';
nav.fills = fill(1, 1, 1);
nav.strokes = [{ type: 'SOLID', color: { r: 0.9, g: 0.9, b: 0.9 } }];
nav.strokeBottomWeight = 1; nav.strokeTopWeight = 0;
nav.strokeLeftWeight = 0; nav.strokeRightWeight = 0;
nav.strokeAlign = 'INSIDE';

// Logo
const logo = figma.createFrame();
logo.resize(110, 34); logo.fills = fill(0.86, 0.86, 0.86); logo.cornerRadius = 3;

// Links
const navLinks = figma.createAutoLayout('HORIZONTAL');
navLinks.itemSpacing = 36; navLinks.fills = [];
for (const label of ['Servicios', 'Trabajos', 'Empresa', 'Blog']) {
  navLinks.appendChild(makeText(label, 14, 'Medium', 0.3, 0.3, 0.3));
}

// CTA
const ctaBtn = figma.createAutoLayout('HORIZONTAL');
ctaBtn.paddingLeft = 22; ctaBtn.paddingRight = 22;
ctaBtn.paddingTop = 9; ctaBtn.paddingBottom = 9;
ctaBtn.cornerRadius = 4; ctaBtn.fills = fill(0.12, 0.12, 0.12);
ctaBtn.appendChild(makeText('Contactar', 13, 'Medium', 1, 1, 1));

nav.appendChild(logo); nav.appendChild(navLinks); nav.appendChild(ctaBtn);
wrapper.appendChild(nav);
nav.layoutSizingHorizontal = 'FILL';

const screenshot = await nav.screenshot();
return { navId: nav.id, screenshot };
```

- [ ] **2.2 Verificar screenshot** — debe mostrar: logo placeholder izquierda, 4 links centro, botón "Contactar" derecha.

---

## Task 3: Hero

- [ ] **3.1 Crear hero section**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

const hero = figma.createAutoLayout('HORIZONTAL');
hero.name = 'Hero';
hero.paddingLeft = 80; hero.paddingRight = 80;
hero.paddingTop = 100; hero.paddingBottom = 100;
hero.itemSpacing = 64;
hero.counterAxisAlignItems = 'CENTER';
hero.fills = fill(0.07, 0.08, 0.16);

// Left column
const heroLeft = figma.createAutoLayout('VERTICAL');
heroLeft.name = 'Hero Left';
heroLeft.itemSpacing = 24; heroLeft.fills = [];

// Tag
const tag = figma.createAutoLayout('HORIZONTAL');
tag.paddingLeft = 14; tag.paddingRight = 14;
tag.paddingTop = 5; tag.paddingBottom = 5;
tag.cornerRadius = 20;
tag.fills = fill(0.25, 0.27, 0.38);
tag.strokes = [{ type: 'SOLID', color: { r: 0.35, g: 0.37, b: 0.5 } }];
tag.strokeWeight = 1;
tag.appendChild(makeText('Mantenimiento de edificios en Montevideo y todo el interior', 12, 'Medium', 0.75, 0.78, 0.9));

// Heading
const h1 = makeText('Tu edificio en manos\nque lo conocen.', 52, 'Bold', 1, 1, 1);
h1.lineHeight = { unit: 'PERCENT', value: 112 };
h1.textAutoResize = 'WIDTH_AND_HEIGHT';

// Subtext
const sub = makeText('Visitas mensuales, equipo propio y respuesta rápida para bombas,\nsanitaria, tanques y contraincendio. Más de dos décadas\nmanteniendo edificios residenciales y de oficinas.', 16, 'Regular', 0.72, 0.74, 0.82);
sub.lineHeight = { unit: 'PERCENT', value: 160 };
sub.textAutoResize = 'WIDTH_AND_HEIGHT';

// CTAs
const ctaRow = figma.createAutoLayout('HORIZONTAL');
ctaRow.itemSpacing = 14; ctaRow.fills = [];

const cta1 = figma.createAutoLayout('HORIZONTAL');
cta1.name = 'CTA Primary'; cta1.paddingLeft = 30; cta1.paddingRight = 30;
cta1.paddingTop = 13; cta1.paddingBottom = 13; cta1.cornerRadius = 4;
cta1.fills = fill(1, 1, 1);
cta1.appendChild(makeText('Ver servicios', 14, 'Medium', 0.07, 0.08, 0.16));

const cta2 = figma.createAutoLayout('HORIZONTAL');
cta2.name = 'CTA Secondary'; cta2.paddingLeft = 30; cta2.paddingRight = 30;
cta2.paddingTop = 13; cta2.paddingBottom = 13; cta2.cornerRadius = 4;
cta2.fills = fill(0.07, 0.08, 0.16);
cta2.strokes = [{ type: 'SOLID', color: { r: 0.5, g: 0.52, b: 0.65 } }];
cta2.strokeWeight = 1.5;
cta2.appendChild(makeText('Trabajos puntuales', 14, 'Medium', 0.82, 0.84, 0.95));

ctaRow.appendChild(cta1); ctaRow.appendChild(cta2);
heroLeft.appendChild(tag); heroLeft.appendChild(h1);
heroLeft.appendChild(sub); heroLeft.appendChild(ctaRow);

// Right image placeholder
const heroImg = figma.createFrame();
heroImg.name = 'Hero Image Placeholder';
heroImg.resize(420, 340);
heroImg.fills = fill(0.18, 0.20, 0.30);
heroImg.cornerRadius = 8;

hero.appendChild(heroLeft); hero.appendChild(heroImg);
wrapper.appendChild(hero);
hero.layoutSizingHorizontal = 'FILL';
heroLeft.layoutSizingHorizontal = 'FILL';

const screenshot = await hero.screenshot();
return { heroId: hero.id, screenshot };
```

- [ ] **3.2 Verificar screenshot** — fondo dark, H1 blanco, bajada gris, 2 CTAs, placeholder de imagen derecha.

---

## Task 4: Sección Servicios

- [ ] **4.1 Crear sección servicios con 4 cards**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

const servicios = figma.createAutoLayout('VERTICAL');
servicios.name = 'Servicios';
servicios.paddingLeft = 80; servicios.paddingRight = 80;
servicios.paddingTop = 88; servicios.paddingBottom = 88;
servicios.itemSpacing = 48;
servicios.fills = fill(1, 1, 1);

// Header
const header = figma.createAutoLayout('VERTICAL');
header.itemSpacing = 12; header.fills = [];
header.appendChild(makeText('LO QUE HACEMOS', 11, 'Bold', 0.6, 0.6, 0.6));
header.appendChild(makeText('Cuatro servicios,\nun solo equipo', 36, 'Bold'));
header.appendChild(makeText('Abonos mensuales para edificios residenciales y comerciales.\nCada servicio se adapta a las instalaciones de tu edificio.', 15, 'Regular', 0.5, 0.5, 0.5));

// Cards grid (horizontal auto-layout)
const grid = figma.createAutoLayout('HORIZONTAL');
grid.name = 'Servicios Grid';
grid.itemSpacing = 20;
grid.fills = [];

const serviciosData = [
  { title: 'Bombas de agua', desc: 'Visita mensual con rotación de equipos, revisión de automatismos y distintos niveles de cobertura.', cta: 'Ver planes' },
  { title: 'Sanitaria', desc: 'Mantenimiento mensual de graseras, cámaras, resumideros e hidrolavado según las instalaciones del edificio.', cta: 'Ver servicio' },
  { title: 'Limpieza de tanques', desc: 'Limpieza anual con análisis de potabilidad incluido. Obligación legal desde 1988.', cta: 'Ver servicio' },
  { title: 'Contraincendio', desc: 'Control mensual de presión, caudal y prueba anual por nicho. Equipos en regla.', cta: 'Ver servicio' },
];

for (const s of serviciosData) {
  const card = figma.createAutoLayout('VERTICAL');
  card.name = `Card — ${s.title}`;
  card.paddingLeft = 28; card.paddingRight = 28;
  card.paddingTop = 28; card.paddingBottom = 28;
  card.itemSpacing = 14;
  card.fills = fill(0.97, 0.97, 0.97);
  card.strokes = [{ type: 'SOLID', color: { r: 0.9, g: 0.9, b: 0.9 } }];
  card.strokeWeight = 1; card.cornerRadius = 8;

  const icon = figma.createFrame();
  icon.resize(44, 44); icon.fills = fill(0.86, 0.86, 0.86); icon.cornerRadius = 8;

  const titleText = makeText(s.title, 17, 'Bold');
  const descText = makeText(s.desc, 13, 'Regular', 0.5, 0.5, 0.5);
  descText.lineHeight = { unit: 'PERCENT', value: 160 };
  descText.textAutoResize = 'WIDTH_AND_HEIGHT';

  const ctaText = makeText(`${s.cta} →`, 13, 'Medium');

  card.appendChild(icon); card.appendChild(titleText);
  card.appendChild(descText); card.appendChild(ctaText);
  grid.appendChild(card);
  card.layoutSizingHorizontal = 'FILL';
}

servicios.appendChild(header); servicios.appendChild(grid);
wrapper.appendChild(servicios);
servicios.layoutSizingHorizontal = 'FILL';
grid.layoutSizingHorizontal = 'FILL';

const screenshot = await servicios.screenshot();
return { serviciosId: servicios.id, screenshot };
```

- [ ] **4.2 Verificar** — 4 cards en fila, cada una con icon placeholder, título, descripción y link.

---

## Task 5: Trabajos puntuales

- [ ] **5.1 Crear sección trabajos**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

const trabajos = figma.createAutoLayout('HORIZONTAL');
trabajos.name = 'Trabajos Puntuales';
trabajos.paddingLeft = 80; trabajos.paddingRight = 80;
trabajos.paddingTop = 88; trabajos.paddingBottom = 88;
trabajos.itemSpacing = 64;
trabajos.counterAxisAlignItems = 'MIN';
trabajos.fills = fill(0.96, 0.96, 0.96);

// Left
const left = figma.createAutoLayout('VERTICAL');
left.itemSpacing = 16; left.fills = [];
left.resize(300, 100);
left.primaryAxisSizingMode = 'AUTO';

left.appendChild(makeText('INTERVENCIONES ÚNICAS', 11, 'Bold', 0.6, 0.6, 0.6));
const h2 = makeText('¿Necesitás un\ntrabajo específico?', 32, 'Bold');
h2.lineHeight = { unit: 'PERCENT', value: 115 };
h2.textAutoResize = 'WIDTH_AND_HEIGHT';
const desc = makeText('Sin necesidad de abono. Cotizamos e intervenimos para sustitución de tanques, graseras, cañerías y más.', 14, 'Regular', 0.5, 0.5, 0.5);
desc.lineHeight = { unit: 'PERCENT', value: 160 };
desc.textAutoResize = 'WIDTH_AND_HEIGHT';

const ctaBtn = figma.createAutoLayout('HORIZONTAL');
ctaBtn.paddingLeft = 22; ctaBtn.paddingRight = 22;
ctaBtn.paddingTop = 10; ctaBtn.paddingBottom = 10;
ctaBtn.cornerRadius = 4; ctaBtn.fills = fill(1, 1, 1);
ctaBtn.strokes = [{ type: 'SOLID', color: { r: 0.8, g: 0.8, b: 0.8 } }];
ctaBtn.strokeWeight = 1.5;
ctaBtn.appendChild(makeText('Ver todos los trabajos', 13, 'Medium'));

left.appendChild(h2); left.appendChild(desc); left.appendChild(ctaBtn);

// Right — items list
const items = figma.createAutoLayout('VERTICAL');
items.itemSpacing = 12; items.fills = [];

const trabajosData = [
  { title: 'Sustitución de tanques', url: '/trabajos/sustitucion-de-tanques/' },
  { title: 'Sustitución de graseras', url: '/trabajos/sustitucion-de-graseras/' },
  { title: 'Cambio de cañerías', url: '/trabajos/cambio-de-cañerias/' },
];

for (const t of trabajosData) {
  const item = figma.createAutoLayout('HORIZONTAL');
  item.paddingLeft = 22; item.paddingRight = 22;
  item.paddingTop = 18; item.paddingBottom = 18;
  item.primaryAxisAlignItems = 'SPACE_BETWEEN';
  item.counterAxisAlignItems = 'CENTER';
  item.fills = fill(1, 1, 1);
  item.strokes = [{ type: 'SOLID', color: { r: 0.9, g: 0.9, b: 0.9 } }];
  item.strokeWeight = 1; item.cornerRadius = 6;

  const itemLeft = figma.createAutoLayout('VERTICAL');
  itemLeft.itemSpacing = 4; itemLeft.fills = [];
  itemLeft.appendChild(makeText(t.title, 15, 'Medium'));
  itemLeft.appendChild(makeText(t.url, 12, 'Regular', 0.6, 0.6, 0.6));

  item.appendChild(itemLeft);
  item.appendChild(makeText('→', 16, 'Regular', 0.75, 0.75, 0.75));
  items.appendChild(item);
  item.layoutSizingHorizontal = 'FILL';
}

trabajos.appendChild(left); trabajos.appendChild(items);
wrapper.appendChild(trabajos);
trabajos.layoutSizingHorizontal = 'FILL';
items.layoutSizingHorizontal = 'FILL';

const screenshot = await trabajos.screenshot();
return { trabajosId: trabajos.id, screenshot };
```

- [ ] **5.2 Verificar** — 2 columnas: texto izquierda, 3 items con flecha derecha.

---

## Task 6: Confianza

- [ ] **6.1 Crear banner de logos**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

const confianza = figma.createAutoLayout('VERTICAL');
confianza.name = 'Confianza';
confianza.paddingLeft = 80; confianza.paddingRight = 80;
confianza.paddingTop = 52; confianza.paddingBottom = 52;
confianza.itemSpacing = 32;
confianza.counterAxisAlignItems = 'CENTER';
confianza.fills = fill(0.92, 0.92, 0.92);

confianza.appendChild(makeText('EDIFICIOS Y ADMINISTRADORES QUE CONFÍAN EN HIDRAL', 11, 'Bold', 0.65, 0.65, 0.65));

const logosRow = figma.createAutoLayout('HORIZONTAL');
logosRow.name = 'Logos Row';
logosRow.itemSpacing = 40;
logosRow.counterAxisAlignItems = 'CENTER';
logosRow.primaryAxisAlignItems = 'CENTER';
logosRow.fills = [];

for (let i = 0; i < 6; i++) {
  const logo = figma.createFrame();
  logo.resize(110, 36);
  logo.fills = fill(0.78, 0.78, 0.78);
  logo.cornerRadius = 3;
  logosRow.appendChild(logo);
}

confianza.appendChild(logosRow);
wrapper.appendChild(confianza);
confianza.layoutSizingHorizontal = 'FILL';

const screenshot = await confianza.screenshot();
return { confianzaId: confianza.id, screenshot };
```

- [ ] **6.2 Verificar** — fondo gris medio, texto arriba, 6 logo placeholders en fila.

---

## Task 7: Módulo Abono (activable)

- [ ] **7.1 Crear módulo abono**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

const abono = figma.createAutoLayout('HORIZONTAL');
abono.name = 'Abono [ACTIVABLE]';
abono.paddingLeft = 80; abono.paddingRight = 80;
abono.paddingTop = 80; abono.paddingBottom = 80;
abono.itemSpacing = 80;
abono.counterAxisAlignItems = 'CENTER';
abono.fills = fill(0.12, 0.13, 0.20);

// Left content
const abonoLeft = figma.createAutoLayout('VERTICAL');
abonoLeft.itemSpacing = 20; abonoLeft.fills = [];

abonoLeft.appendChild(makeText('ABONADOS HIDRAL', 11, 'Bold', 0.5, 0.52, 0.65));
const abonoH2 = makeText('365 días disponibles\npara vos', 36, 'Bold', 1, 1, 1);
abonoH2.lineHeight = { unit: 'PERCENT', value: 112 };
abonoH2.textAutoResize = 'WIDTH_AND_HEIGHT';
const abonoSub = makeText('Los abonados tienen condiciones preferenciales y prioridad de atención. Ante cualquier problema, tenés un técnico.', 15, 'Regular', 0.6, 0.62, 0.75);
abonoSub.lineHeight = { unit: 'PERCENT', value: 165 };
abonoSub.textAutoResize = 'WIDTH_AND_HEIGHT';

// Points
const points = figma.createAutoLayout('VERTICAL');
points.itemSpacing = 12; points.fills = [];
for (const p of ['Atención preferencial los 365 días del año', 'Respuesta rápida ante urgencias', 'Un solo interlocutor para todos los servicios']) {
  const row = figma.createAutoLayout('HORIZONTAL');
  row.itemSpacing = 12; row.counterAxisAlignItems = 'CENTER'; row.fills = [];
  const dot = figma.createFrame();
  dot.resize(20, 20); dot.fills = fill(0.3, 0.32, 0.45); dot.cornerRadius = 10;
  row.appendChild(dot);
  row.appendChild(makeText(p, 14, 'Regular', 0.72, 0.74, 0.85));
  points.appendChild(row);
}

const abonoCta = figma.createAutoLayout('HORIZONTAL');
abonoCta.paddingLeft = 28; abonoCta.paddingRight = 28;
abonoCta.paddingTop = 12; abonoCta.paddingBottom = 12;
abonoCta.cornerRadius = 4;
abonoCta.fills = fill(0.2, 0.22, 0.35);
abonoCta.strokes = [{ type: 'SOLID', color: { r: 0.45, g: 0.47, b: 0.62 } }];
abonoCta.strokeWeight = 1.5;
abonoCta.appendChild(makeText('Consultar abono', 14, 'Medium', 0.88, 0.9, 1));

abonoLeft.appendChild(abonoH2); abonoLeft.appendChild(abonoSub);
abonoLeft.appendChild(points); abonoLeft.appendChild(abonoCta);

// Right stat visual
const statBox = figma.createAutoLayout('VERTICAL');
statBox.resize(320, 260);
statBox.primaryAxisSizingMode = 'FIXED';
statBox.counterAxisSizingMode = 'FIXED';
statBox.fills = fill(0.2, 0.21, 0.3);
statBox.strokes = [{ type: 'SOLID', color: { r: 0.3, g: 0.32, b: 0.45 } }];
statBox.strokeWeight = 1; statBox.cornerRadius = 8;
statBox.primaryAxisAlignItems = 'CENTER';
statBox.counterAxisAlignItems = 'CENTER';
statBox.itemSpacing = 8;

const bigNum = makeText('365', 72, 'Bold', 1, 1, 1);
const bigUnit = makeText('días disponibles', 14, 'Regular', 0.5, 0.52, 0.65);
statBox.appendChild(bigNum); statBox.appendChild(bigUnit);

abono.appendChild(abonoLeft); abono.appendChild(statBox);
wrapper.appendChild(abono);
abono.layoutSizingHorizontal = 'FILL';
abonoLeft.layoutSizingHorizontal = 'FILL';

const screenshot = await abono.screenshot();
return { abonoId: abono.id, screenshot };
```

- [ ] **7.2 Verificar** — fondo dark mid, texto blanco, lista de 3 puntos, stat "365" a la derecha.

---

## Task 8: Por qué Hidral

- [ ] **8.1 Crear sección 3 columnas**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

const porque = figma.createAutoLayout('VERTICAL');
porque.name = 'Por qué Hidral';
porque.paddingLeft = 80; porque.paddingRight = 80;
porque.paddingTop = 88; porque.paddingBottom = 88;
porque.itemSpacing = 52;
porque.fills = fill(1, 1, 1);

const header = figma.createAutoLayout('VERTICAL');
header.itemSpacing = 12; header.fills = [];
header.appendChild(makeText('POR QUÉ ELEGIRNOS', 11, 'Bold', 0.6, 0.6, 0.6));
header.appendChild(makeText('Equipo propio, respuesta real', 36, 'Bold'));

const grid = figma.createAutoLayout('HORIZONTAL');
grid.name = 'Columnas'; grid.itemSpacing = 40; grid.fills = [];
grid.counterAxisAlignItems = 'MIN';

const cols = [
  { num: '01', title: 'Más de 20 años en el rubro', body: 'Conocemos los edificios de Montevideo y del interior. Cada técnico es parte del equipo, no un tercero.' },
  { num: '02', title: 'Sin tercerización', body: 'Los técnicos que atienden tu edificio son empleados de Hidral. Eso garantiza criterio, continuidad y calidad.' },
  { num: '03', title: 'Llegamos al interior', body: 'Somos la única empresa que presta servicios de mantenimiento en todo el Uruguay. Si estás en el interior, llamanos y consultanos.' },
];

for (const col of cols) {
  const c = figma.createAutoLayout('VERTICAL');
  c.itemSpacing = 14; c.fills = [];
  c.paddingTop = 24;
  c.strokes = [{ type: 'SOLID', color: { r: 0.85, g: 0.85, b: 0.85 } }];
  c.strokeTopWeight = 2; c.strokeBottomWeight = 0;
  c.strokeLeftWeight = 0; c.strokeRightWeight = 0;
  c.strokeAlign = 'OUTSIDE';

  c.appendChild(makeText(col.num, 11, 'Bold', 0.75, 0.75, 0.75));
  c.appendChild(makeText(col.title, 19, 'Bold'));
  const bodyText = makeText(col.body, 14, 'Regular', 0.5, 0.5, 0.5);
  bodyText.lineHeight = { unit: 'PERCENT', value: 170 };
  bodyText.textAutoResize = 'WIDTH_AND_HEIGHT';
  c.appendChild(bodyText);
  grid.appendChild(c);
  c.layoutSizingHorizontal = 'FILL';
}

porque.appendChild(header); porque.appendChild(grid);
wrapper.appendChild(porque);
porque.layoutSizingHorizontal = 'FILL';
grid.layoutSizingHorizontal = 'FILL';

const screenshot = await porque.screenshot();
return { porqueId: porque.id, screenshot };
```

- [ ] **8.2 Verificar** — 3 columnas con línea top, número, título y descripción.

---

## Task 9: Blog

- [ ] **9.1 Crear sección blog con 3 cards**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

const blog = figma.createAutoLayout('VERTICAL');
blog.name = 'Blog';
blog.paddingLeft = 80; blog.paddingRight = 80;
blog.paddingTop = 88; blog.paddingBottom = 88;
blog.itemSpacing = 40;
blog.fills = fill(0.96, 0.96, 0.96);

// Header row
const blogHeader = figma.createAutoLayout('HORIZONTAL');
blogHeader.primaryAxisAlignItems = 'SPACE_BETWEEN';
blogHeader.counterAxisAlignItems = 'MIN';
blogHeader.fills = [];

const blogHeadLeft = figma.createAutoLayout('VERTICAL');
blogHeadLeft.itemSpacing = 10; blogHeadLeft.fills = [];
blogHeadLeft.appendChild(makeText('RECURSOS', 11, 'Bold', 0.6, 0.6, 0.6));
blogHeadLeft.appendChild(makeText('Información útil para\nadministradores', 32, 'Bold'));

const blogCta = figma.createAutoLayout('HORIZONTAL');
blogCta.paddingLeft = 20; blogCta.paddingRight = 20;
blogCta.paddingTop = 9; blogCta.paddingBottom = 9;
blogCta.cornerRadius = 4; blogCta.fills = fill(1, 1, 1);
blogCta.strokes = [{ type: 'SOLID', color: { r: 0.82, g: 0.82, b: 0.82 } }];
blogCta.strokeWeight = 1.5;
blogCta.appendChild(makeText('Ver todos los artículos', 13, 'Medium'));

blogHeader.appendChild(blogHeadLeft); blogHeader.appendChild(blogCta);

// Cards
const cardsRow = figma.createAutoLayout('HORIZONTAL');
cardsRow.itemSpacing = 20; cardsRow.fills = [];

const articles = [
  { tag: 'Bombas', title: '¿Cada cuánto hay que rotar las bombas de agua?' },
  { tag: 'Tanques', title: 'Limpieza de tanques: qué dice la normativa y qué implica para tu edificio' },
  { tag: 'Sanitaria', title: 'Graseras colectivas vs. individuales: diferencias y mantenimiento' },
];

for (const a of articles) {
  const card = figma.createAutoLayout('VERTICAL');
  card.name = `Blog Card — ${a.tag}`;
  card.itemSpacing = 0;
  card.fills = fill(1, 1, 1);
  card.strokes = [{ type: 'SOLID', color: { r: 0.9, g: 0.9, b: 0.9 } }];
  card.strokeWeight = 1; card.cornerRadius = 8;
  card.clipsContent = true;

  const img = figma.createFrame();
  img.resize(360, 160); img.fills = fill(0.86, 0.86, 0.86);

  const body = figma.createAutoLayout('VERTICAL');
  body.paddingLeft = 22; body.paddingRight = 22;
  body.paddingTop = 20; body.paddingBottom = 24;
  body.itemSpacing = 10; body.fills = [];

  const tagFrame = figma.createAutoLayout('HORIZONTAL');
  tagFrame.paddingLeft = 9; tagFrame.paddingRight = 9;
  tagFrame.paddingTop = 3; tagFrame.paddingBottom = 3;
  tagFrame.cornerRadius = 3; tagFrame.fills = fill(0.91, 0.91, 0.91);
  tagFrame.appendChild(makeText(a.tag, 11, 'Bold', 0.5, 0.5, 0.5));

  const titleText = makeText(a.title, 14, 'Medium');
  titleText.lineHeight = { unit: 'PERCENT', value: 148 };
  titleText.textAutoResize = 'WIDTH_AND_HEIGHT';

  body.appendChild(tagFrame); body.appendChild(titleText);
  body.appendChild(makeText('Leer artículo →', 12, 'Medium', 0.6, 0.6, 0.6));
  card.appendChild(img); card.appendChild(body);
  cardsRow.appendChild(card);
  card.layoutSizingHorizontal = 'FILL';
}

blog.appendChild(blogHeader); blog.appendChild(cardsRow);
wrapper.appendChild(blog);
blog.layoutSizingHorizontal = 'FILL';
cardsRow.layoutSizingHorizontal = 'FILL';

const screenshot = await blog.screenshot();
return { blogId: blog.id, screenshot };
```

- [ ] **9.2 Verificar** — 3 cards con imagen placeholder, tag, título y link.

---

## Task 10: Contacto + Footer

- [ ] **10.1 Crear sección contacto y footer**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);

await figma.loadFontAsync({ family: "Inter", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Regular" });
await figma.loadFontAsync({ family: "DM Sans", style: "Medium" });
await figma.loadFontAsync({ family: "DM Sans", style: "Bold" });

const fill = (r, g, b, a = 1) => [{ type: 'SOLID', color: { r, g, b }, opacity: a }];
const makeText = (chars, size, style, r = 0.12, g = 0.12, b = 0.12) => {
  const t = figma.createText();
  t.fontName = { family: 'DM Sans', style };
  t.fontSize = size;
  t.characters = chars;
  t.fills = fill(r, g, b);
  return t;
};

const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");

// ── CONTACTO ──────────────────────────────────────
const contacto = figma.createAutoLayout('VERTICAL');
contacto.name = 'Contacto';
contacto.paddingLeft = 80; contacto.paddingRight = 80;
contacto.paddingTop = 96; contacto.paddingBottom = 96;
contacto.itemSpacing = 48;
contacto.counterAxisAlignItems = 'CENTER';
contacto.fills = fill(0.07, 0.08, 0.16);

const ctHeader = figma.createAutoLayout('VERTICAL');
ctHeader.itemSpacing = 14; ctHeader.fills = [];
ctHeader.counterAxisAlignItems = 'CENTER';

const ctH2 = makeText('¿Hablamos?', 42, 'Bold', 1, 1, 1);
const ctSub = makeText('Contanos qué necesita tu edificio. Te respondemos por\nWhatsApp, teléfono o formulario.', 16, 'Regular', 0.6, 0.62, 0.75);
ctSub.textAlignHorizontal = 'CENTER';
ctSub.lineHeight = { unit: 'PERCENT', value: 160 };
ctSub.textAutoResize = 'WIDTH_AND_HEIGHT';

ctHeader.appendChild(ctH2); ctHeader.appendChild(ctSub);

const optionsRow = figma.createAutoLayout('HORIZONTAL');
optionsRow.itemSpacing = 16; optionsRow.fills = [];

for (const opt of [{ label: 'WhatsApp', sub: 'Respuesta inmediata' }, { label: 'Teléfono', sub: 'Lun–Vie 8–18hs' }, { label: 'Formulario', sub: 'Te respondemos en 24hs' }]) {
  const box = figma.createAutoLayout('VERTICAL');
  box.paddingLeft = 32; box.paddingRight = 32;
  box.paddingTop = 28; box.paddingBottom = 28;
  box.itemSpacing = 10; box.cornerRadius = 8;
  box.counterAxisAlignItems = 'CENTER';
  box.fills = fill(0.15, 0.17, 0.27);
  box.strokes = [{ type: 'SOLID', color: { r: 0.25, g: 0.27, b: 0.4 } }];
  box.strokeWeight = 1;
  box.resize(220, 100); box.primaryAxisSizingMode = 'AUTO';

  const icon = figma.createFrame();
  icon.resize(42, 42); icon.fills = fill(0.25, 0.27, 0.4); icon.cornerRadius = 8;

  box.appendChild(icon);
  box.appendChild(makeText(opt.label, 15, 'Medium', 1, 1, 1));
  box.appendChild(makeText(opt.sub, 12, 'Regular', 0.5, 0.52, 0.65));
  optionsRow.appendChild(box);
}

contacto.appendChild(ctHeader); contacto.appendChild(optionsRow);
wrapper.appendChild(contacto);
contacto.layoutSizingHorizontal = 'FILL';

// ── FOOTER ────────────────────────────────────────
const footer = figma.createAutoLayout('HORIZONTAL');
footer.name = 'Footer';
footer.paddingLeft = 80; footer.paddingRight = 80;
footer.paddingTop = 32; footer.paddingBottom = 32;
footer.primaryAxisAlignItems = 'SPACE_BETWEEN';
footer.counterAxisAlignItems = 'CENTER';
footer.fills = fill(0.04, 0.05, 0.1);

const footerLogo = figma.createFrame();
footerLogo.resize(90, 28); footerLogo.fills = fill(0.25, 0.25, 0.35); footerLogo.cornerRadius = 3;

const footerLinks = figma.createAutoLayout('HORIZONTAL');
footerLinks.itemSpacing = 24; footerLinks.fills = [];
for (const link of ['Servicios', 'Trabajos', 'Empresa', 'Blog', 'Contacto', 'Privacidad']) {
  footerLinks.appendChild(makeText(link, 12, 'Regular', 0.4, 0.4, 0.5));
}

footer.appendChild(footerLogo); footer.appendChild(footerLinks);
footer.appendChild(makeText('© 2026 Hidral. Montevideo, Uruguay.', 12, 'Regular', 0.3, 0.3, 0.4));
wrapper.appendChild(footer);
footer.layoutSizingHorizontal = 'FILL';

// Remove placeholder from wrapper
wrapper.placeholder = false;

const screenshot = await wrapper.screenshot();
return { contactoId: contacto.id, footerId: footer.id, screenshot };
```

- [ ] **10.2 Verificar screenshot final** — página completa visible, todas las secciones conectadas.

---

## Task 11: Screenshot final y validación

- [ ] **11.1 Screenshot completo del frame**

```js
const uxPage = figma.root.children.find(p => p.name === 'UX');
await figma.setCurrentPageAsync(uxPage);
const wrapper = await figma.getNodeByIdAsync("WRAPPER_ID");
const screenshot = await wrapper.screenshot({ scale: 0.3 });
return { width: wrapper.width, height: wrapper.height, screenshot };
```

- [ ] **11.2 Checklist visual**
  - [ ] Navbar: logo + links + CTA
  - [ ] Hero: dark bg, H1 "Tu edificio en manos que lo conocen.", 2 CTAs
  - [ ] Servicios: 4 cards en fila
  - [ ] Trabajos: 2 columnas, 3 items
  - [ ] Confianza: banda gris, 6 logo placeholders
  - [ ] Abono: dark mid bg, "365", marcado como ACTIVABLE
  - [ ] Por qué Hidral: 3 columnas con línea top
  - [ ] Blog: 3 cards con imagen, tag y título
  - [ ] Contacto: dark bg, "¿Hablamos?", 3 opciones
  - [ ] Footer: links + copyright

---

## Notas importantes

- **Cada script** necesita switch a la página UX con `await figma.setCurrentPageAsync(uxPage)` — el contexto de página se resetea entre llamadas
- **`layoutSizingHorizontal = 'FILL'`** siempre DESPUÉS de `wrapper.appendChild(section)`
- **Fonts**: siempre cargar Inter Regular (default) + DM Sans Regular/Medium/Bold antes de cualquier texto
- El módulo Abono está nombrado `'Abono [ACTIVABLE]'` para que sea fácil de identificar y ocultar/mostrar
- Si algún script falla, es **atómico** — no se aplican cambios parciales, se puede reintentar sin limpiar
