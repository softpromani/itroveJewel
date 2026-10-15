<!-- Synchronous anti-FOUC theme detector -->
<script>
    (function() {
        const savedTheme = localStorage.getItem('itrove_theme');
        const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = savedTheme ? savedTheme : (systemPrefersDark ? 'dark' : 'light');
        
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    })();
</script>

<style>
    /* CSS Variables for Dynamic Theme Switching */
    :root {
        --bg-main: #07090E;
        --text-main: #F1F5F9;
        --panel-bg: rgba(13, 17, 26, 0.92);
        --panel-border: rgba(234, 179, 8, 0.25);
        --card-bg: rgba(15, 23, 42, 0.7);
        --card-border: rgba(30, 41, 59, 0.8);
        --subtext: #94A3B8;
        --muted: #64748B;
        --input-bg: rgba(15, 23, 42, 0.9);
        --input-border: rgba(51, 65, 85, 0.8);
        --input-text: #FFFFFF;
        --input-placeholder: #64748B;
        --header-bg: rgba(7, 9, 14, 0.85);
        --header-border: rgba(30, 41, 59, 0.8);
        --footer-bg: rgba(7, 9, 14, 0.6);
        --table-header: rgba(15, 23, 42, 0.95);
        --table-hover: rgba(30, 41, 59, 0.4);
        --gold-text-grad: linear-gradient(135deg, #FDE047 0%, #EAB308 50%, #B45309 100%);
        --gold-glow: 0 0 30px rgba(234, 179, 8, 0.08);
        --lockout-bg: rgba(22, 10, 14, 0.94);
        --lockout-border: rgba(239, 68, 68, 0.35);
        --lockout-glow: 0 0 50px rgba(220, 38, 38, 0.2);
    }

    html.light {
        --bg-main: #F8FAFC;
        --text-main: #0F172A;
        --panel-bg: #FFFFFF;
        --panel-border: rgba(217, 119, 6, 0.35);
        --card-bg: #FFFFFF;
        --card-border: #E2E8F0;
        --subtext: #475569;
        --muted: #64748B;
        --input-bg: #FFFFFF;
        --input-border: #CBD5E1;
        --input-text: #0F172A;
        --input-placeholder: #94A3B8;
        --header-bg: rgba(255, 255, 255, 0.92);
        --header-border: #E2E8F0;
        --footer-bg: #F8FAFC;
        --table-header: #F1F5F9;
        --table-hover: #F8FAFC;
        --gold-text-grad: linear-gradient(135deg, #B45309 0%, #D97706 50%, #EAB308 100%);
        --gold-glow: 0 10px 25px -5px rgba(217, 119, 6, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        --lockout-bg: #FFFFFF;
        --lockout-border: rgba(239, 68, 68, 0.4);
        --lockout-glow: 0 10px 30px rgba(220, 38, 38, 0.12);
    }

    /* Base Elements */
    body {
        background-color: var(--bg-main) !important;
        color: var(--text-main) !important;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .glass-panel {
        background: var(--panel-bg) !important;
        border-color: var(--panel-border) !important;
        box-shadow: var(--gold-glow) !important;
        transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }

    .gold-border {
        border: 1px solid var(--panel-border) !important;
        box-shadow: var(--gold-glow) !important;
    }

    .gold-gradient {
        background: var(--gold-text-grad) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
    }

    .lockout-panel {
        background: var(--lockout-bg) !important;
        border: 1px solid var(--lockout-border) !important;
        box-shadow: var(--lockout-glow) !important;
        transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }

    /* Light Mode Overrides for High Contrast Luxury Jewelry Aesthetic */
    html.light .bg-slate-950,
    html.light .bg-slate-950\/90,
    html.light .bg-slate-950\/80,
    html.light .bg-slate-950\/70,
    html.light .bg-slate-950\/60,
    html.light .bg-slate-950\/50,
    html.light .bg-slate-950\/40 {
        background-color: #FFFFFF !important;
    }

    html.light .bg-slate-900,
    html.light .bg-slate-900\/90,
    html.light .bg-slate-900\/80,
    html.light .bg-slate-900\/70,
    html.light .bg-slate-900\/60,
    html.light .bg-slate-900\/50,
    html.light .bg-slate-900\/40 {
        background-color: #F8FAFC !important;
    }

    html.light .bg-slate-800 {
        background-color: #E2E8F0 !important;
        color: #1E293B !important;
    }
    html.light .bg-slate-800:hover {
        background-color: #CBD5E1 !important;
    }

    html.light .border-slate-800,
    html.light .border-slate-800\/80,
    html.light .border-slate-800\/70,
    html.light .border-slate-800\/60,
    html.light .border-slate-700 {
        border-color: #E2E8F0 !important;
    }

    html.light .divide-slate-800\/60 > :not([hidden]) ~ :not([hidden]),
    html.light .divide-slate-800 > :not([hidden]) ~ :not([hidden]) {
        border-color: #F1F5F9 !important;
    }

    /* Text Colors in Light Mode */
    html.light .text-white {
        color: #0F172A !important;
    }
    html.light .text-slate-200 {
        color: #1E293B !important;
    }
    html.light .text-slate-300 {
        color: #334155 !important;
    }
    html.light .text-slate-400 {
        color: #64748B !important;
    }
    html.light .text-slate-500 {
        color: #64748B !important;
    }

    /* Form Fields in Light Mode */
    html.light input[type="text"],
    html.light input[type="email"],
    html.light input[type="password"],
    html.light select,
    html.light textarea {
        background-color: #FFFFFF !important;
        border-color: #CBD5E1 !important;
        color: #0F172A !important;
    }
    html.light input::placeholder,
    html.light textarea::placeholder {
        color: #94A3B8 !important;
    }
    html.light input:focus,
    html.light select:focus,
    html.light textarea:focus {
        border-color: #D97706 !important;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15) !important;
    }

    /* Tables & Navs in Light Mode */
    html.light thead {
        background-color: #F1F5F9 !important;
        color: #475569 !important;
        border-color: #E2E8F0 !important;
    }
    html.light tbody tr:hover {
        background-color: #F8FAFC !important;
    }

    html.light header {
        background-color: var(--header-bg) !important;
        border-color: var(--header-border) !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05) !important;
    }

    html.light footer {
        background-color: var(--footer-bg) !important;
        border-color: var(--header-border) !important;
    }

    /* Gold Accents in Light Mode */
    html.light .text-amber-300 {
        color: #B45309 !important;
    }
    html.light .text-amber-400 {
        color: #D97706 !important;
    }

    /* Jewel Badges in Light Mode */
    html.light .bg-amber-950\/80,
    html.light .bg-amber-950\/60,
    html.light .bg-amber-950\/40,
    html.light .bg-amber-950\/20,
    html.light .bg-amber-500\/10,
    html.light .bg-amber-500\/20 {
        background-color: #FEF3C7 !important;
        border-color: rgba(217, 119, 6, 0.3) !important;
        color: #92400E !important;
    }

    html.light .bg-emerald-950\/80,
    html.light .bg-emerald-950\/70,
    html.light .bg-emerald-950\/60,
    html.light .bg-emerald-950\/40 {
        background-color: #ECFDF5 !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
        color: #065F46 !important;
    }
    html.light .text-emerald-400,
    html.light .text-emerald-300 {
        color: #059669 !important;
    }

    html.light .bg-cyan-950\/80,
    html.light .bg-cyan-950\/70,
    html.light .bg-cyan-950\/60,
    html.light .bg-cyan-950\/40 {
        background-color: #ECFEFF !important;
        border-color: rgba(6, 182, 212, 0.35) !important;
        color: #0E7490 !important;
    }
    html.light .text-cyan-400,
    html.light .text-cyan-300 {
        color: #0891B2 !important;
    }

    html.light .bg-red-950\/80,
    html.light .bg-red-950\/70,
    html.light .bg-red-950\/60,
    html.light .bg-red-950\/40,
    html.light .border-red-950\/80 {
        background-color: #FEF2F2 !important;
        border-color: rgba(239, 68, 68, 0.35) !important;
        color: #991B1B !important;
    }
    html.light .text-red-400,
    html.light .text-red-300 {
        color: #DC2626 !important;
    }

    /* Lockout screen specific adjustments in light mode */
    html.light .bg-red-600 {
        background-color: #DC2626 !important;
    }
</style>
