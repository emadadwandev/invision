---
name: Invision Fieldwork
description: A warm, legible field operations interface built around the next store visit.
colors:
  primary: "#173B34"
  primary-container: "#326B5A"
  secondary: "#286C4A"
  tertiary: "#B95732"
  neutral-bg: "#F3F0E8"
  neutral-surface: "#FBFAF6"
  neutral-text: "#172F2B"
  neutral-muted: "#59615D"
  neutral-outline: "#C8C9C0"
typography:
  display:
    fontFamily: "Manrope, Roboto, sans-serif"
    fontSize: "48px"
    fontWeight: 800
    lineHeight: 1.05
  headline:
    fontFamily: "Manrope, Roboto, sans-serif"
    fontSize: "24px"
    fontWeight: 800
  body:
    fontFamily: "Inter, Roboto, sans-serif"
    fontSize: "16px"
    fontWeight: 400
  label:
    fontFamily: "Inter, Roboto, sans-serif"
    fontSize: "12px"
    fontWeight: 700
    letterSpacing: "0.04em"
rounded:
  sm: "8px"
  md: "12px"
  lg: "14px"
  pill: "100px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "12px"
  lg: "16px"
  xl: "24px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#FFFFFF"
    rounded: "{rounded.md}"
    height: "52px"
  button-visit:
    backgroundColor: "{colors.tertiary}"
    textColor: "#FFFFFF"
    rounded: "{rounded.md}"
    height: "54px"
  card:
    backgroundColor: "{colors.neutral-surface}"
    textColor: "{colors.neutral-text}"
    rounded: "{rounded.lg}"
    padding: "18px"
  status-chip:
    backgroundColor: "{colors.primary-container}"
    textColor: "#FFFFFF"
    rounded: "{rounded.pill}"
  navigation:
    backgroundColor: "{colors.neutral-surface}"
    textColor: "{colors.primary}"
    height: "80px"
---

# Design System: Invision Fieldwork

## Overview

**Creative North Star: “The Fieldwork Ledger”**

The interface treats a field day like a clear working ledger: location and visit state are immediately visible, while the next useful action is easy to reach. Evergreen anchors the product, warm paper keeps long route lists comfortable to scan, and terracotta marks a live visit or decisive field action.

Material 3 remains the interaction foundation for Android. Use compact, factual labels, strong store names, and open space between tasks. The design carries across the mobile app through shared theme roles; individual workflows keep their existing data and behavior.

**Key Characteristics:**
- Evergreen header and navigation
- Warm paper surfaces with restrained outlines
- Terracotta reserved for the active visit and its main action
- Route progress shown as a connected, numbered timeline

## Colors

The palette pairs a deep botanical green with paper neutrals and one warm kiln accent.

### Primary
- **Evergreen**: anchors the home header, primary navigation, and routine actions.
- **Field Green**: marks completion, GPS readiness, and positive route states.

### Secondary
- **Leaf**: identifies successful work and completed stops.

### Tertiary
- **Kiln Orange**: draws attention to the current visit and its primary action.

### Neutral
- **Warm Paper**: the app canvas and broad background surface.
- **Chalk**: cards and contained content areas.
- **Ink Green**: primary text and strong store names.
- **Soft Slate**: secondary copy, locations, and supporting metadata.
- **Stone Line**: dividers and subtle card outlines.

**The One Active Accent Rule.** Terracotta marks the active visit or its key action; completed work stays green and pending work stays neutral.

## Typography

**Display Font:** Manrope (Roboto fallback)
**Body Font:** Inter (Roboto fallback)
**Label/Mono Font:** Inter

**Character:** Compact, confident headings make route information scannable. Body copy stays neutral and readable during repeated use in the field.

### Hierarchy
- **Display** (800, 48px, 1.05 line height): the “Today” page title.
- **Headline** (700–800, 20–24px): route headings and store names.
- **Title** (600–700, 15–18px): greeting and secondary headings.
- **Body** (400, 14–16px): locations, explanations, and supporting information.
- **Label** (700, 10–12px, lightly tracked): visit states and compact metadata.

**The Scannable Name Rule.** Store names carry more visual weight than their addresses, timestamps, and status metadata.

## Layout

Screens use Material 3 layouts with 16px page insets and an 8px spacing rhythm. The Today surface gives the identity and work state a full-width header, promotes one active or next visit, then lists all route stops in order. Compact widths keep each stop readable without horizontal scrolling; longer routes continue in the main scroll area above the persistent bottom navigation.

## Elevation & Depth

Surfaces are mostly flat. Tonal contrast separates the page canvas, cards, and header; a fine outline defines cards when their edge needs extra clarity. Avoid decorative shadows on routine work surfaces.

**The Paper Layer Rule.** Use surface tone and spacing to distinguish content before adding depth.

## Shapes

Cards and buttons use gently softened corners (12–14px). Status indicators are compact capsules; numbered route stops are circular because they encode sequence. Outlines stay subtle and are used to define a surface rather than decorate it.

## Components

### Buttons
- **Shape:** softly rounded (12px).
- **Primary:** evergreen fill, white label, minimum height 52px.
- **Visit action:** kiln-orange fill, white label, minimum height 54px.
- **Focus / disabled:** use Material 3 focus and disabled states with the same semantic color roles.

### Chips
- **Style:** compact, filled state labels with strong text contrast and no decorative outline.
- **State:** pending is neutral, checked in is terracotta, completed is green, and skipped is error-colored.

### Cards / Containers
- **Corner Style:** 12–14px.
- **Background:** chalk against warm paper.
- **Shadow Strategy:** flat; a subtle outline can define the card.
- **Internal Padding:** typically 16–18px.

### Inputs / Fields
- **Style:** filled warm-surface fields with softly rounded corners.
- **Focus:** use the shared primary color focus border.
- **Error / Disabled:** use Material 3 semantic states.

### Navigation
- **Style:** Material 3 bottom navigation on compact phones, with evergreen selected states and a warm neutral bar. Preserve Android system Back behavior.

### Route Timeline
Numbered stops connect through a thin vertical line. Completed stops use a check and green state; the active stop uses kiln orange; pending stops remain neutral. The active visit card owns one prominent continuation action, with less frequent visit actions in its overflow menu.

## Do's and Don'ts

### Do:
- **Do** keep duty, connectivity, and sync state visible near the top of Today.
- **Do** place the next actionable store visit before the full route list.
- **Do** show timestamps only when the visit record supplies them.
- **Do** preserve clear 48px touch targets for icon actions and route controls.

### Don't:
- **Don't** use terracotta for completed states; reserve it for live work and key actions.
- **Don't** invent planned time windows, durations, or activities when the visit data does not supply them.
- **Don't** bury the primary visit action among equally weighted buttons.
