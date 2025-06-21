





@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Manajemen User
    </h4>

    <!-- Filter & Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Cari user..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="role" class="form-select">
                                <option value="">Semua Role</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                <option value="kepala_sekolah" {{ request('role') == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Cari</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bx bx-upload me-1"></i> Import
                    </button>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nomor Induk</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Telegram ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->nomor_induk }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-label-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'guru' ? 'primary' : ($user->role === 'siswa' ? 'success' : 'info')) }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->telegram_chat_id ?? '-' }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="dropdown-item" 
                                                onclick="return confirm('Reset password untuk user ini?')">
                                            <i class="bx bx-reset me-1"></i> Reset Password
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('Yakin ingin menghapus user ini?')">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data user.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
     <!-- Improved Pagination -->
<div class="card-footer d-flex justify-content-between align-items-center">
    <div class="text-muted small">
        Menampilkan {{ $users->firstItem() ?? 0 }} sampai {{ $users->lastItem() ?? 0 }} 
        dari {{ $users->total() }} entri
    </div>
    
    @if($users->hasPages())
    <nav aria-label="Table pagination">
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous Page Link --}}
            @if ($users->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bx bx-chevron-left"></i> Previous
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $users->appends(request()->query())->previousPageUrl() }}">
                        <i class="bx bx-chevron-left"></i> Previous
                    </a>
                </li>
            @endif

            {{-- Pagination Elements with Limited Display --}}
            @php
                $start = 1;
                $end = $users->lastPage();
                $current = $users->currentPage();
                $last = $users->lastPage();
                
                // Calculate visible page numbers (max 5)
                if ($last <= 7) {
                    // If 7 or fewer pages, show all
                    $showPages = range(1, $last);
                } else {
                    // Show first, last, current, and 2 around current
                    $showPages = [];
                    
                    // Always show first page
                    $showPages[] = 1;
                    
                    // Calculate range around current page
                    $rangeStart = max(2, $current - 1);
                    $rangeEnd = min($last - 1, $current + 1);
                    
                    // Adjust range if at the beginning or end
                    if ($current <= 3) {
                        $rangeEnd = 4;
                    } elseif ($current >= $last - 2) {
                        $rangeStart = $last - 3;
                    }
                    
                    // Add pages in range
                    for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                        $showPages[] = $i;
                    }
                    
                    // Always show last page
                    $showPages[] = $last;
                    
                    // Remove duplicates and sort
                    $showPages = array_unique($showPages);
                    sort($showPages);
                }
                
                $previousPage = 0;
            @endphp
            
            @foreach ($showPages as $page)
                {{-- Add ellipsis if there's a gap --}}
                @if ($previousPage > 0 && $page > $previousPage + 1)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
                
                {{-- Page number --}}
                @if ($page == $current)
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $users->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
                
                @php $previousPage = $page; @endphp
            @endforeach

            {{-- Next Page Link --}}
            @if ($users->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $users->appends(request()->query())->nextPageUrl() }}">
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
</div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel/CSV</label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Format file: Excel (.xlsx, .xls) atau CSV</div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Format Column:</strong>
                        <ul class="mb-0">
                            <li>nomor_induk</li>
                            <li>name</li>
                            <li>email</li>
                            <li>role (admin/guru/siswa/kepala_sekolah)</li>
                            <li>password</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection