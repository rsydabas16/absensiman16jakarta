<!-- resources/views/layouts/partials/sidebar.blade.php -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Absensi Guru</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left bx-sm d-block d-xl-none align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ Request::is('*/dashboard') ? 'active' : '' }}">
            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        @if(auth()->user()->role === 'admin')
            <!-- Admin Menu -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data</span>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Users">Users</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.guru.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-check"></i>
                    <div data-i18n="Guru">Guru</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.siswa.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-pin"></i>
                    <div data-i18n="Siswa">Siswa</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.kelas.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-door-open"></i>
                    <div data-i18n="Kelas">Kelas</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.mata-pelajaran.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book"></i>
                    <div data-i18n="Mata Pelajaran">Mata Pelajaran</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar"></i>
                    <div data-i18n="Jadwal Pelajaran">Jadwal Pelajaran</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.hari-libur.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-x"></i>
                    <div data-i18n="Hari Libur">Hari Libur</div>
                </a>
            </li>
            
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Laporan</span>
            </li>
            
             <li class="menu-item">
                <a href="{{ route('admin.absensi-siswa.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div data-i18n="Tugas Guru">Laporan Absensi Siswa</div>
                </a>
            </li>

            {{-- <li class="menu-item">
                <a href="{{ route('admin.laporan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Laporan">Laporan</div>
                </a>
            </li> --}}
            
        @elseif(auth()->user()->role === 'guru')
            <!-- Guru Menu -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Menu Guru</span>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('guru.absensi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-qr-scan"></i>
                    <div data-i18n="Absensi">Absensi</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('guru.rekap.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Rekap Absensi">Rekap Absensi</div>
                </a>
            </li>
            
        @elseif(auth()->user()->role === 'siswa')
            <!-- Siswa Menu -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Menu Siswa</span>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('siswa.generate-qr.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-qr"></i>
                    <div data-i18n="Generate QR">Generate QR</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('siswa.materi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-content"></i>
                    <div data-i18n="Materi Pembelajaran">Materi Pembelajaran</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('siswa.materi.tugas-guru') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div data-i18n="Tugas Guru">Tugas Guru</div>
                </a>
            </li>

             <li class="menu-item">
                <a href="{{ route('siswa.absensi-siswa.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div data-i18n="Tugas Guru">Absensi Siswa</div>
                </a>
            </li>
            
        @elseif(auth()->user()->role === 'kepala_sekolah')
            <!-- Kepala Sekolah Menu -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Menu Kepala Sekolah</span>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('kepala_sekolah.laporan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Laporan Absensi">Laporan Absensi Guru</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('kepala_sekolah.statistik.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-pie-chart-alt-2"></i>
                    <div data-i18n="Statistik Guru">Statistik Guru</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('kepala_sekolah.absensi-siswa.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div data-i18n="Tugas Guru">Laporan Absensi Siswa</div>
                </a>
            </li>
        @endif
    </ul>
</aside>






















<!-- resources/views/layouts/partials/sidebar.blade.php -->
{{-- <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Absensi Guru</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ Request::is('*/dashboard') ? 'active' : '' }}">
            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        @if(auth()->user()->role === 'admin')
            <!-- Admin Menu -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data</span>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Users">Users</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.guru.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-check"></i>
                    <div data-i18n="Guru">Guru</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.siswa.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-pin"></i>
                    <div data-i18n="Siswa">Siswa</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.kelas.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-door-open"></i>
                    <div data-i18n="Kelas">Kelas</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.mata-pelajaran.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book"></i>
                    <div data-i18n="Mata Pelajaran">Mata Pelajaran</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar"></i>
                    <div data-i18n="Jadwal Pelajaran">Jadwal Pelajaran</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.hari-libur.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-x"></i>
                    <div data-i18n="Hari Libur">Hari Libur</div>
                </a>
            </li>
            
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Laporan</span>
            </li>
            
             <li class="menu-item">
                <a href="{{ route('admin.absensi-siswa.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div data-i18n="Tugas Guru">Laporan Absensi Siswa</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.laporan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Laporan">Laporan</div>
                </a>
            </li>
            
        @elseif(auth()->user()->role === 'guru')
            <!-- Guru Menu -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Menu Guru</span>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('guru.absensi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-qr-scan"></i>
                    <div data-i18n="Absensi">Absensi</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('guru.rekap.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Rekap Absensi">Rekap Absensi</div>
                </a>
            </li>
            
        @elseif(auth()->user()->role === 'siswa')
            <!-- Siswa Menu -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Menu Siswa</span>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('siswa.generate-qr.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-qr"></i>
                    <div data-i18n="Generate QR">Generate QR</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('siswa.materi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-content"></i>
                    <div data-i18n="Materi Pembelajaran">Materi Pembelajaran</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('siswa.materi.tugas-guru') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div data-i18n="Tugas Guru">Tugas Guru</div>
                </a>
            </li>

             <li class="menu-item">
                <a href="{{ route('siswa.absensi-siswa.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div data-i18n="Tugas Guru">Absensi Siswa</div>
                </a>
            </li>
            
        @elseif(auth()->user()->role === 'kepala_sekolah')
            <!-- Kepala Sekolah Menu -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Menu Kepala Sekolah</span>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('kepala_sekolah.laporan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div data-i18n="Laporan Absensi">Laporan Absensi</div>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('kepala_sekolah.statistik.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-pie-chart-alt-2"></i>
                    <div data-i18n="Statistik Guru">Statistik Guru</div>
                </a>
            </li>
        @endif
    </ul>
</aside> --}}