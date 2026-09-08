# Hidral — Estructura de URLs

## Lógica general

El sitio separa dos universos de negocio que responden a dos tipos de cliente:

- **Servicios**: abonos mensuales recurrentes (relación continua con el edificio)
- **Trabajos**: intervenciones puntuales (evento único, puede ser cliente nuevo o abonado)

Esta separación se refleja directamente en la URL y en la navegación principal.

---

## Servicios (`/servicios/`)

Los servicios recurrentes se organizan en **4 categorías**. Cada categoría tiene su propia página que explica todos los planes y modalidades disponibles dentro de ella. Las modalidades (Abonado, Integral, Mantenimiento, Especial) son secciones dentro de la página, no URLs separadas.

```
/servicios/
/servicios/bombas/
/servicios/sanitaria/
/servicios/limpieza-de-tanques/
/servicios/contraincendio/
```

### Por qué no una URL por plan

El cliente tiene más de 20 planes internos (abonado, integral, integral con 2 limpiezas, etc.). Exponer cada uno como URL propia genera páginas con contenido muy delgado y fragmenta la información sin beneficio real para el usuario. El objetivo de la web es que el usuario identifique si Hidral puede ayudarlo y llame — no que elija un plan por su cuenta.

### Servicios combinados (bombas + sanitaria)

Los paquetes que combinan ambos servicios (ej. Integral General de Bombas y Sanitaria) no tienen URL propia. Se presentan como CTAs o módulos dentro de `/servicios/bombas/` y `/servicios/sanitaria/`, o en el overview `/servicios/`.

---

## Trabajos puntuales (`/trabajos/`)

Intervenciones independientes. Pueden ser para abonados existentes o clientes nuevos.

```
/trabajos/
/trabajos/sustitucion-de-tanques/
/trabajos/sustitucion-de-graseras/
/trabajos/cambio-de-cañerias/
```

---

## Estructura completa del sitio

```
/                                      Homepage
/empresa/                              Quiénes somos, historia, enfoque

/servicios/                            Overview: las 4 categorías
/servicios/bombas/                     Todos los planes de bombas
/servicios/sanitaria/                  Todos los planes sanitarios
/servicios/limpieza-de-tanques/        Limpieza de tanques
/servicios/contraincendio/             Equipos contra incendio

/trabajos/                             Listado de trabajos puntuales
/trabajos/sustitucion-de-tanques/
/trabajos/sustitucion-de-graseras/
/trabajos/cambio-de-cañerias/

/blog/                                 Resource Center / artículos
/blog/[slug]/

/contacto/                             Formulario, WhatsApp, ubicación
```

---

## Implementación en WordPress

| Sección | Tipo WP |
|---|---|
| `/servicios/` | Página WP (archive del CPT `servicios`) |
| `/servicios/[categoria]/` | CPT `servicios` con taxonomía por categoría, o páginas WP hijas |
| `/trabajos/` | Página WP (archive del CPT `trabajos`) |
| `/trabajos/[slug]/` | CPT `trabajos` single |
| `/blog/` | Archive de posts |
| `/empresa/`, `/contacto/` | Páginas WP estándar |

> El CPT `servicios` ya tiene `rewrite slug => servicios`. Para la jerarquía de categorías conviene agregar una taxonomía `servicio-tipo` con los slugs `bombas`, `sanitaria`, `limpieza-de-tanques`, `contraincendio`.
