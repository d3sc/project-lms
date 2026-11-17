# LMS Design System

## Product direction
Professional minimalist SaaS: calm, readable, task-oriented. Admin screens prioritize operational visibility and bulk management; student screens prioritize focus, progress, and safe submission.

## Foundations
- Font: system UI stack; use clear 14–16px body text.
- Colors: slate neutrals, indigo primary, emerald success, amber warning, rose destructive.
- Surfaces: white cards on slate-50 background; dark mode follows Tailwind classes.
- Radius: `rounded-xl` for cards, `rounded-lg` for controls.
- Spacing: 4px base scale; generous section spacing.
- Focus: visible indigo focus ring on every interactive element.

## Interaction rules
- Destructive actions require confirmation.
- Forms show server-side validation beside fields.
- Exam timer is server-authoritative; client timer is only a visual aid.
- Autosave reports a visible saved/pending state.
- Never rely on color alone for status; include text and labels.
- Tables collapse into stacked cards on narrow screens.

## Page hierarchy
- Admin dashboard: KPI summary → active monitoring → pending grading → recent activity.
- Student dashboard: available exams → active attempt → history.
- Exam interface: timer and submission state → question content → navigation; keep distractions minimal.

## Accessibility
- Semantic headings and landmarks.
- Labels for all form controls.
- Keyboard-accessible dialogs and navigation.
- Adequate contrast and readable line-height.
- `aria-live` for autosave, timer expiry, and polling updates.

## Responsive breakpoints
- Mobile-first.
- `md`: two-column forms and compact navigation.
- `lg`: persistent sidebar/navigation and wider question workspace.

## Data integrity UX
- Show freshness timestamps for monitoring.
- Show grading status (`Belum dinilai`, `Sebagian`, `Selesai`) explicitly.
- Hide results when exam configuration disallows visibility.
- Never display credentials or sensitive configuration values.

## Quality gate
Before release, verify: `npm run build`, `php artisan view:cache`, `php artisan route:list`, and PostgreSQL-backed feature tests.

Last updated: 2026-11-13