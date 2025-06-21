@extends('layouts.app')

@section('title', 'Manajemen Guru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Manajemen Guru</h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Guru</h5>
            <div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bx bx-import"></i> Import
                </button>
                <a href="{{ route('admin.guru.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Guru
                </a>
            </div>
        </div>
        
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('import_errors'))
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <strong>Beberapa data gagal diimpor:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Jenis Kelamin</th>
                            <th>No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guru as $index => $g)
                            <tr>
                                <td>{{ $index + $guru->firstItem() }}</td>
                                <td>{{ $g->nip }}</td>
                                <td>{{ $g->nama_lengkap }}</td>
                                <td>{{ $g->user->email }}</td>
                                <td>{{ $g->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $g->no_hp }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.guru.edit', $g->id) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $g->id }}">
                                                <i class="bx bx-reset me-1"></i> Reset Password
                                            </a>
                                            <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal Reset Password -->
                                    <div class="modal fade" id="resetPasswordModal{{ $g->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reset Password</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Password akan direset ke NIP guru (<strong>{{ $g->nip }}</strong>). Lanjutkan?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('admin.guru.reset-password', $g->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary">Reset Password</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data guru</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
          <!-- Improved Pagination -->
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $guru->firstItem() ?? 0 }} sampai {{ $guru->lastItem() ?? 0 }} 
                dari {{ $guru->total() }} entri
            </div>
            
            @if($guru->hasPages())
            <nav aria-label="Table pagination">
                <ul class="pagination pagination-sm mb-0">
                    {{-- Previous Page Link --}}
                    @if ($guru->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="bx bx-chevron-left"></i> Previous
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $guru->appends(request()->query())->previousPageUrl() }}">
                                <i class="bx bx-chevron-left"></i> Previous
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($guru->appends(request()->query())->getUrlRange(1, $guru->lastPage()) as $page => $url)
                        @if ($page == $guru->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($guru->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $guru->appends(request()->query())->nextPageUrl() }}">
                                Next <i class="bx bx-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">
                                Next <i class="bx bx-chevron-right"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </nav>
            @endif
        </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.guru.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Format yang didukung: .xlsx, .xls, .csv (Maksimal 2MB)</div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Format Excel harus memiliki kolom berikut (dengan header di baris pertama):</strong>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kolom</th>
                                        <th>Wajib/Opsional</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>nip</code></td>
                                        <td><span class="badge bg-danger">Wajib</span></td>
                                        <td>Nomor Induk Pegawai (Unik)</td>
                                    </tr>
                                    <tr>
                                        <td><code>nama_lengkap</code></td>
                                        <td><span class="badge bg-danger">Wajib</span></td>
                                        <td>Nama Lengkap Guru</td>
                                    </tr>
                                    <tr>
                                        <td><code>email</code></td>
                                        <td><span class="badge bg-danger">Wajib</span></td>
                                        <td>Email Guru (Unik)</td>
                                    </tr>
                                    <tr>
                                        <td><code>jenis_kelamin</code></td>
                                        <td><span class="badge bg-danger">Wajib</span></td>
                                        <td>L atau P</td>
                                    </tr>
                                    <tr>
                                        <td><code>no_hp</code></td>
                                        <td><span class="badge bg-secondary">Opsional</span></td>
                                        <td>Nomor HP</td>
                                    </tr>
                                    <tr>
                                        <td><code>alamat</code></td>
                                        <td><span class="badge bg-secondary">Opsional</span></td>
                                        <td>Alamat</td>
                                    </tr>
                                    <tr>
                                        <td><code>password</code></td>
                                        <td><span class="badge bg-secondary">Opsional</span></td>
                                        <td>Password (Default: password123)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-import"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>





{{-- @extends('layouts.app')

@section('title', 'Manajemen Guru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Manajemen Guru</h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Guru</h5>
            <div>
                 <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bx bx-import"></i> Import
                </button>
                <a href="{{ route('admin.guru.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Guru
                </a>
               
            </div>
        </div>
        
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('import_errors'))
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <strong>Beberapa data gagal diimpor:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Jenis Kelamin</th>
                            <th>No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guru as $index => $g)
                            <tr>
                                <td>{{ $index + $guru->firstItem() }}</td>
                                <td>{{ $g->nip }}</td>
                                <td>{{ $g->nama_lengkap }}</td>
                                <td>{{ $g->user->email }}</td>
                                <td>{{ $g->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $g->no_hp }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.guru.edit', $g->id) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $g->id }}">
                                                <i class="bx bx-reset me-1"></i> Reset Password
                                            </a>
                                            <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal Reset Password -->
                                    <div class="modal fade" id="resetPasswordModal{{ $g->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reset Password</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Password akan direset ke NIP guru ({{ $g->nip }}). Lanjutkan?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('admin.guru.reset-password', $g->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary">Reset Password</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data guru</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $guru->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.guru.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Format yang didukung: .xlsx, .xls, .csv</div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Format Excel harus memiliki kolom:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>nip</strong> - Nomor Induk Pegawai</li>
                            <li><strong>nama_lengkap</strong> - Nama Lengkap Guru</li>
                            <li><strong>email</strong> - Email Guru</li>
                            <li><strong>jenis_kelamin</strong> - L atau P</li>
                            <li><strong>no_hp</strong> - Nomor HP (opsional)</li>
                            <li><strong>alamat</strong> - Alamat (opsional)</li>
                            <li><strong>password</strong> - Password (opsional, default: password123)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-import"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}




{{-- @extends('layouts.app')

@section('title', 'Manajemen Guru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Manajemen Guru</h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Guru</h5>
            <div>
                <a href="{{ route('admin.guru.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Guru
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bx bx-import"></i> Import
                </button>
            </div>
        </div>
        
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Jenis Kelamin</th>
                            <th>No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guru as $index => $g)
                            <tr>
                                <td>{{ $index + $guru->firstItem() }}</td>
                                <td>{{ $g->nip }}</td>
                                <td>{{ $g->nama_lengkap }}</td>
                                <td>{{ $g->user->email }}</td>
                                <td>{{ $g->jenis_kelamin }}</td>
                                <td>{{ $g->no_hp }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.guru.edit', $g->id) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $g->id }}">
                                                <i class="bx bx-reset me-1"></i> Reset Password
                                            </a>
                                            <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal Reset Password -->
                                    <div class="modal fade" id="resetPasswordModal{{ $g->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reset Password</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Password akan direset ke NIP guru ({{ $g->nip }}). Lanjutkan?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('admin.guru.reset-password', $g->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary">Reset Password</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data guru</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $guru->links() }}
            </div>
        </div>
    </div>
</div> --}}

<!-- Modal Import -->
{{-- <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.guru.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel/CSV</label>
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted">Format: nip, nama_lengkap, jenis_kelamin, no_hp, alamat, email, password</small>
                    </div>
                    <div class="alert alert-info">
                        <p class="mb-0">Unduh <a href="{{ route('admin.guru.template') }}">template Excel</a> untuk format yang benar.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}
@endsection