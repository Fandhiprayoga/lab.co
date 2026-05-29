<?php
$appName = $appName ?? setting('App.siteName') ?? 'LabCorner';
$appNameShort = $appNameShort ?? setting('App.siteNameShort') ?? $appName;
$pageTitle = $pageTitle ?? $appName;
$pageDescription = $pageDescription ?? ($appName . ' - Manajemen laboratorium modern.');
$bodyClass = $bodyClass ?? 'font-sans text-gray-800 antialiased bg-gray-50 selection:bg-brand-500 selection:text-white';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>
    <meta name="description" content="<?= esc($pageDescription) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fdf3f3',
                            100: '#fbe7e7',
                            500: '#cc141c',
                            600: '#b11116',
                            900: '#5a0609',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
    <?= $this->renderSection('css') ?>
</head>
<body class="<?= esc($bodyClass) ?>">
    <?= $this->include('partials/public/navbar') ?>

    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('partials/public/footer') ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const navbar = document.getElementById('navbar');
            const isLandingPage = <?= json_encode(uri_string() === '') ?>;
            const spyLinks = Array.from(document.querySelectorAll('[data-spy-link]'));
            const baseLinkClasses = new Map();

            spyLinks.forEach((link) => {
                baseLinkClasses.set(link, link.className);
            });

            const setActiveLink = (key) => {
                if (!isLandingPage || !key) {
                    return;
                }

                spyLinks.forEach((link) => {
                    const isActive = link.dataset.spyLink === key;
                    link.className = baseLinkClasses.get(link);

                    if (isActive) {
                        link.classList.add('text-brand-600', 'font-semibold');
                        link.classList.remove('text-gray-600', 'text-gray-700');
                    }
                });
            };

            if (isLandingPage && spyLinks.length) {
                const sections = ['beranda', 'layanan', 'laboratorium', 'kontak']
                    .map((id) => document.getElementById(id))
                    .filter(Boolean);

                const observer = new IntersectionObserver((entries) => {
                    const visible = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

                    if (visible && visible.target && visible.target.id) {
                        setActiveLink(visible.target.id);
                    }
                }, {
                    root: null,
                    threshold: [0.25, 0.45, 0.6],
                    rootMargin: '-30% 0px -40% 0px',
                });

                sections.forEach((section) => observer.observe(section));

                const initialSection = sections.find((section) => {
                    const rect = section.getBoundingClientRect();
                    return rect.top <= 120 && rect.bottom > 120;
                }) || sections[0];

                if (initialSection) {
                    setActiveLink(initialSection.id);
                }
            }

            if (mobileBtn && mobileMenu) {
                const mobileIcon = mobileBtn.querySelector('i');

                mobileBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                    if (mobileMenu.classList.contains('hidden')) {
                        mobileIcon.classList.remove('ph-x');
                        mobileIcon.classList.add('ph-list');
                    } else {
                        mobileIcon.classList.remove('ph-list');
                        mobileIcon.classList.add('ph-x');
                    }
                });

                const mobileLinks = mobileMenu.querySelectorAll('a');
                mobileLinks.forEach((link) => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        mobileIcon.classList.remove('ph-x');
                        mobileIcon.classList.add('ph-list');
                    });
                });
            }

            if (navbar) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 20) {
                        navbar.classList.add('shadow-sm');
                        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                    } else {
                        navbar.classList.remove('shadow-sm');
                        navbar.style.background = 'rgba(255, 255, 255, 0.8)';
                    }
                });
            }
        });
    </script>
</body>
</html>