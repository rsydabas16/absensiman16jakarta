<!-- resources/views/admin/siswa/index.blade.php -->
@extends('layouts.app')

@section('title', 'Manajemen Siswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Manajemen Siswa
    </h4>

    <!-- Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0">Daftar Siswa</h5>
                </div>
                <div>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bx bx-upload me-1"></i> Import
                    </button>
                    <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Siswa
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>NISN</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Jenis Kelamin</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($siswa as $item)
                    <tr>
                        <td><strong>{{ $item->nisn }}</strong></td>
                        <td>{{ $item->nama_lengkap }}</td>
                        <td>{{ $item->kelas->nama_kelas }}</td>
                        <td>{{ $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td>
                            @if($item->is_ketua_kelas)
                                <span class="badge bg-label-primary">Ketua Kelas</span>
                            @elseif($item->is_wakil_ketua)
                                <span class="badge bg-label-info">Wakil Ketua</span>
                            @else
                                <span class="badge bg-label-secondary">Siswa</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.siswa.edit', $item) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.siswa.destroy', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('Yakin ingin menghapus siswa ini?')">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data siswa.</td>
                    </tr>
                    @endforelse
                    
                </tbody>
            </table>
        </div>
  <!-- Improved Pagination -->
<div class="card-footer d-flex justify-content-between align-items-center">
    <div class="text-muted small">
        Menampilkan {{ $siswa->firstItem() ?? 0 }} sampai {{ $siswa->lastItem() ?? 0 }} 
        dari {{ $siswa->total() }} entri
    </div>
    
    @if($siswa->hasPages())
    <nav aria-label="Table pagination">
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous Page Link --}}
            @if ($siswa->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bx bx-chevron-left"></i> Previous
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $siswa->appends(request()->query())->previousPageUrl() }}">
                        <i class="bx bx-chevron-left"></i> Previous
                    </a>
                </li>
            @endif

            {{-- Pagination Elements with Limited Display --}}
            @php
                $start = 1;
                $end = $siswa->lastPage();
                $current = $siswa->currentPage();
                $last = $siswa->lastPage();
                
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
                        <a class="page-link" href="{{ $siswa->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
                
                @php $previousPage = $page; @endphp
            @endforeach

            {{-- Next Page Link --}}
            @if ($siswa->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $siswa->appends(request()->query())->nextPageUrl() }}">
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
        <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Siswa</h5>
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
                            <li>nisn</li>
                            <li>nama_lengkap</li>
                            <li>email</li>
                            <li>password (optional)</li>
                            <li>kelas (nama kelas)</li>
                            <li>jenis_kelamin (L/P)</li>
                            <li>no_hp (optional)</li>
                            <li>alamat (optional)</li>
                            <li>is_ketua_kelas (1/0)</li>
                            <li>is_wakil_ketua (1/0)</li>
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