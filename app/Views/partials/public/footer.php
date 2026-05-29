<?php
$appName = $appName ?? setting('App.siteName') ?? 'LabCorner';
?>
<footer id="kontak" class="bg-white border-t border-gray-200 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-white shadow-md">
                        <i class="ph ph-flask text-xl"></i>
                    </div>
                    <span class="font-bold text-xl text-gray-900"><?= esc($appName) ?>.</span>
                </div>
                <h5 class="font-bold text-gray-900 mb-2">Telkom University Purwokerto</h5>
                <p class="text-gray-500 mb-6 text-sm max-w-sm leading-relaxed">
                    Kawasan Pendidikan Telkom Purwokerto<br>
                    Jl. D.I. Panjaitan No.128, Purwokerto Selatan,<br>
                    Kabupaten Banyumas, Jawa Tengah 53147
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition-colors" aria-label="Instagram">
                        <i class="ph ph-instagram-logo text-xl"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition-colors" aria-label="YouTube">
                        <i class="ph ph-youtube-logo text-xl"></i>
                    </a>
                    <a href="https://pwt.telkomuniversity.ac.id" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition-colors" aria-label="Website">
                        <i class="ph ph-globe text-xl"></i>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-6">Tautan Akademik</h4>
                <ul class="space-y-4 text-sm">
                    <li><a href="#" class="text-gray-500 hover:text-brand-600 transition-colors">Portal Akademik (iGrasias)</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand-600 transition-colors">LMS / e-Learning</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand-600 transition-colors">Perpustakaan (Open Library)</a></li>
                    <li><a href="https://pwt.telkomuniversity.ac.id/" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-brand-600 transition-colors">Website Resmi Kampus</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-6">Layanan & Bantuan</h4>
                <ul class="space-y-4 text-sm">
                    <li><a href="#" class="text-gray-500 hover:text-brand-600 transition-colors">Helpdesk IT & Jaringan</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand-600 transition-colors">Panduan Sistem <?= esc($appName) ?></a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand-600 transition-colors">Direktorat Akademik</a></li>
                    <li><a href="mailto:info@pwt.telkomuniversity.ac.id" class="text-brand-600 hover:text-brand-700 transition-colors font-medium">info@pwt.telkomuniversity.ac.id</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-400 text-sm">
                &copy; 2026 <?= esc($appName) ?> - Telkom University Purwokerto. Hak cipta dilindungi undang-undang.
            </p>
            <div class="flex gap-6 text-sm">
                <a href="#" class="text-gray-400 hover:text-gray-600">Kebijakan Privasi</a>
                <a href="#" class="text-gray-400 hover:text-gray-600">Syarat Ketentuan</a>
            </div>
        </div>
    </div>
</footer>